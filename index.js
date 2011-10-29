$(document).ready(function() {
	
	display_playlist_editor ();
	/*
	jQuery('#playlist-holder ul ul').sortable({
			connectWith: '.playlist ul'
		});

	jQuery('#playlist-holder>ul').sortable();

	jQuery('#playlist-holder li li').contextMenu('context-menu', {
	    'show lyrics': {
	        click: function(element){ window.open('?action=displaySong&id=' + element.attr('id').replace('id_', '')); }
	    },
	    'edit song': {
	        click: function(element){window.open( '?action=editSong&id=' + element.attr('id').replace('id_', '')); }
	    },
	    'remove from playlist': {
	        click: function(element){ element.remove(); }
	    }
	});
	*/
	
	jQuery('.song-index .song').contextMenu('context-menu', {
	    'show lyrics': {
	        click: function(element){ window.open('?action=displaySong&id=' + element.attr('id').replace('id_', '')); }
	    },
	    'edit song': {
	        click: function(element){ window.open('?action=editSong&id=' + element.attr('id').replace('id_', '')); }
	    }
	});
	
	jQuery('#playlist-holder .playlist').contextMenu('context-menu', {
	    'remove from playlist': {
	        click: function(element){ element.remove(); }
	    }
	});

	jQuery('#add-new-set').click(function () {
		add_new_setlist(jQuery('#playlist-holder>ul'))
	});


	jQuery('#savePlaylist').click(function() {
		save_playlist();
	});
	create_filter_list(jQuery('#available-songs'));

	jQuery('.song-index .song').click(function () {
		location.href = '?action=displaySong&id=' + jQuery(this).attr('id');
	});
	jQuery('a#remove_linebreaks').click(function (){
		jQuery('textarea#content').html(jQuery('textarea#content').html().replace(/\n\n/gm, "\n"));
	});
	
	jQuery('.playlist-chooser').change(function () {
		var value = jQuery(this).val(), container = jQuery('#available-songs');
		
		if (value === 'all') {
			create_filter_list(container);
		} else {
			display_songpicker_from_playlist(container, value);
		}
	});
});

function display_playlist_editor () {
	console.log('display_playist_editor');
	jQuery('#playlist-holder').each(function () {
		var self = jQuery(this), playlist;
		
		playlist = self.attr('filename');
		jQuery.get(
		    '/songbook/display_playlist.php',
		    {playlist: playlist},
		    function (data) {
		    	self.html(data);
        		jQuery('ul', self).sortable();
        		jQuery('ul ul', self).sortable({
        			connectWith: '.playlist ul'
        		});
        		jQuery('li li', self).contextMenu('context-menu', {
        		    'show lyrics': {
        		        click: function(element){ window.open('?action=displaySong&id=' + element.attr('id').replace('id_', '')); }
        		    },
        		    'edit song': {
        		        click: function(element){ window.open('?action=editSong&id=' + element.attr('id').replace('id_', '')); }
        		    },
        		    'remove from playlist': {
        		        click: function(element){ element.remove(); }
        		    }
        		});
    		}
		);
	});
}

function add_new_setlist(container) {
	var newSet = jQuery('<li class="set playlist"><textarea class="set-title" type="text">New Set</textarea></li>');
	var newList = jQuery('<ul class="ui-sortable"><li class=dummy>&nbsp;</li></ul>').sortable({
			connectWith: '.playlist ul'
	});
	container.append(newSet);
	newSet.append(newList);
}

function search_allsongs() {
	jQuery.get(
	    '/songbook/allsongs_filterlist.php',
	    {search_string: jQuery('#search_string').val()},
	    function (data) {
	    	jQuery('div#all-song-list').html(data);
	    	jQuery('#allsongsearch .number-of-records').html(jQuery('div#all-song-list .numberofrecords').html());
	    	if(jQuery('.displayPlaylist').length) {
        		jQuery('#playlist-holder ul ul, #allsongs ul').sortable({
        			connectWith: '.playlist ul'
        		});
    		}
    		if(jQuery('#allsongs').length) {
        		jQuery('#allsongs li').contextMenu('context-menu', {
        		    'show lyrics': {
        		        click: function(element){ window.open('?action=displaySong&id=' + element.attr('id').replace('id_', '')); }
        		    },
        		    'edit song': {
        		        click: function(element){ window.open('?action=editSong&id=' + element.attr('id').replace('id_', '')); }
        		    }
        		});
    		}
	    }
	);
}

function save_playlist() {
	playlist = jQuery('#playlist-holder');
	playlist_html = playlist.html();
	playlist_json = convert_playlist_to_json (playlist_html);
	//playlist_json = {"test": "one", "test2": 2, "test3": [1,2,3]};

	jQuery.post(
	    '/songbook/update_playlist.php',
	    {
	    	filename: playlist.attr('filename'),
	    	data: JSON.stringify(playlist_json)
	    },
	    function (response) {
	    	data = JSON.parse(response);
    		if(data.success) {
    			console.log('playlist saved to ' + data.destination);
    			display_playlist_editor ();
    		}
	    }
	);
}

function convert_playlist_to_json (playlist_html) {
	output_json = {};
	source = jQuery('<div>' +playlist_html + '</div>');
	output_json.title = jQuery('.playlist-title', source).val();
	output_json.sets = [];
	set_count = 0;
	jQuery('.set', source).each(function () {
		var this_set = jQuery(this);

		output_json.sets[set_count] = {
			"label": jQuery('.set-title', this_set).val(),
			"songs": []
		};
		song_count = 0;
		jQuery('.song', this_set).each(function () {
			var self = jQuery(this);
			
			output_json.sets[set_count].songs[song_count] = {
				"id": self.attr('id'),
				"key": jQuery('.key', self).val(),
				"singer": jQuery('.singer', self).val(),
				"duration": jQuery('.duration', self).val()
			};
			song_count = song_count + 1;
		});
		set_count = set_count +1;
	});
	return output_json;
}

function create_filter_list(container) {
	var html = '<form id="allsongsearch">' +
               '<span class="label">Filter: </span><input type="test" id="search_string" value="" />' + 
               '<span class="label">Number of songs displayed: </span><span class="number-of-records"></span>' + 
               '</form>' +
               '<div id="all-song-list"><span class="pleasewait">please wait...</span></div>' +
               '</div>';
	container.html(html);
	search_allsongs();	
	jQuery('form#allsongsearch').submit(function () {
		search_allsongs();
		return false;
    });
}


function display_songpicker_from_playlist(parent_container, playlist) {
	var container = jQuery('<div id="all-song-list"></div>');
	parent_container.html('');
	parent_container.append(container);
	jQuery.get(
		    '/songbook/display_playlist.php',
		    {playlist: playlist},
		    function (data) {
		    	container.html(data);
		    	if(jQuery('.displayPlaylist').length) {
	        		jQuery('ul', container).sortable({
	        			connectWith: '.playlist ul'
	        		});
	    		}
	    		if(jQuery('li', container).length) {
	        		jQuery('li', container).contextMenu('context-menu', {
	        		    'show lyrics': {
	        		        click: function(element){ window.open('?action=displaySong&id=' + element.attr('id').replace('id_', '')); }
	        		    },
	        		    'edit song': {
	        		        click: function(element){ window.open('?action=editSong&id=' + element.attr('id').replace('id_', '')); }
	        		    },
	        		    'remove from playlist': {
	        		        click: function(element){ element.remove(); }
	        		    }
	        		});
	    		}
		    }
		);
}

