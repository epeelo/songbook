SBK.SongbookApplication = SBK.Class.extend({
	init: function (container) {
		var self = this;

		self.container = container;       
        self.song = null;
        self.set = null;
        self.playlist = null;
        self.pleasewait = new SBK.PleaseWait(self.container);
	},
	
	render: function (callback) {
		var self = this;
		
		self.container.html('');
		jQuery.getJSON('js/playlists.json', function (data){
            self.all_playlists = data;
            jQuery.getJSON('js/songs.json', function (data){
                self.all_songs = data;

                self.api = new SBK.Api(self);
                // Set the initial application state
                self.application_state = new SBK.ApplicationState();
                self.application_state.register_callback(function (changed_parameters) {
                    self.on_application_state_change(changed_parameters);
                });
                self.application_state.startup(self);
            });
        });
	},
    
    on_application_state_change: function (changed_parameters) {
        var self = this;
        
        if(typeof(changed_parameters.tab) !== 'undefined') {
            if (typeof(changed_parameters.tab) !== 'undefined') {
                self.container.html('');
                delete self.content_container;
                self.tab = self.application_state.tab;
                switch (self.tab) {
                    case 'playlist_list':
                        self._display_playlist_list();
                        break;
                    case 'edit_playlist':
                        self._display_playlist(self.application_state.playlist_filename);
                        break;
                    case 'playlist_print':
                        self._playlist_print();
                        break;
                    case 'playlist_book':
                        self._playlist_book();
                        break;
                    case 'song_lyrics':
                        self._display_song(self.application_state.id, self.application_state.key, self.application_state.capo);
                        break;
                    case 'edit_song':
                        self._edit_song(self.application_state.id);
                        break;
                    case 'add_new_song':
                        self._edit_song();
                        break;
                    case 'list_all_songs':
                        self._list_all_songs();
                        break;
                }
            }
        }
    },

    list_all_songs: function () {
        var self = this;

        self.application_state.set({
            tab: 'list_all_songs',
            playlist_filename: null,
            id: null,
            key: null,
            capo: null
        }, false);
    },

    display_playlist_list: function () {
        var self = this;

        self.application_state.set({
            tab: 'playlist_list',
            playlist_filename: null,
            id: null,
            key: null,
            capo: null
        }, false);
    },

    display_playlist: function (playlist_filename) {
        var self = this;

        self.application_state.set({
            tab: 'edit_playlist',
            playlist_filename: playlist_filename,
            id: null,
            key: null,
            capo: null
        }, false);
        
    },

    playlist_print: function (playlist_filename) {
        var self = this;

        self.application_state.set({
            tab: 'playlist_print',
            playlist_filename: playlist_filename,
            id: null,
            key: null,
            capo: null
        }, false);
        
    },

    playlist_book: function (playlist_filename) {
        var self = this;

        self.application_state.set({
            tab: 'playlist_book',
            playlist_filename: playlist_filename,
            id: null,
            key: null,
            capo: null
        }, false);
        
    },
    
    _display_song: function (id, key, capo) {
        var self = this, navigation_panel, previous_button, previous_song, next_button, next_song, lyrics_pane, song_lyrics;

        self.content_container = jQuery('<div class="lyrics-panel"></div>').appendTo(self.container);
        
        song_lyrics = new SBK.SongLyricsDisplay(
            self.content_container,
            id,
            self,
            key, 
            capo,
            function () {
                self.list_all_songs();
            }
        );
        song_lyrics.render();
    },

    display_song: function (song_list_item) {
        var self = this;

        if (typeof(new_window) !== 'undefined' && new_window === true) {
            new_window = true;
        } else {
            new_window = false;
        }

        self.application_state.set({
            tab: 'song_lyrics',
            playlist_filename: null,
            id: song_list_item.id,
            key: song_list_item.key,
            capo: song_list_item.capo
        }, new_window);
    },

    edit_song: function (song_id) {
        var self = this;

        self.application_state.set({
            tab: 'edit_song',
            playlist_filename: null,
            id: song_id,
            key: null,
            capo: null
        }, false);
    },

    back: function () {
        var self = this;

        self.application_state.back();
    },

    _list_all_songs: function () {
        var self = this;

        self.container.html('');
        self.content_container = jQuery('<div id="all-songs-list"></div>').appendTo(self.container);

        self.all_songs = new SBK.SongFilterList.Lyrics(
            self.content_container, 
            self, 
            null, 
            {
                display_song: function (song_list_item) { 
                    self.display_song(song_list_item);
                }
            }
        ).render();
    },

    _display_playlist_list: function () {
        var self = this;

        self.container.html('');
        self.content_container = jQuery('<div id="playlists-list"></div>').appendTo(self.container);
        button_bar = jQuery('<span class="button-bar"></span>').appendTo(self.container);

        //Buttons
        new SBK.Button(button_bar, 'all-songs', 'List all songs', function () {self.list_all_songs();});
        new SBK.Button(button_bar, 'create-new-playlist', 'Create a new playlist', function () {self.display_playlist(null);});

        self.all_playlists = new SBK.AllPlaylists(self.content_container, self).render();
    },

    _display_playlist: function (playlist_filename) {
        var self = this;

        self.content_container = jQuery('<div id="playlist-display"></div>').appendTo(self.container);
        self.playlist = new SBK.PlayList(playlist_filename, self.content_container, null, self);
        self.playlist.render();
    },

    _playlist_print: function (playlist_filename) {
        var self = this;

        self.content_container = jQuery('<div id="playlist-print"></div>').appendTo(self.container);
        self.playlist = new SBK.PlayListPrint(playlist_filename, self.content_container, null, self);
        self.playlist.render();
    },

    _playlist_book: function (playlist_filename) {
        var self = this;

        self.content_container = jQuery('<div id="playlist-book"></div>').appendTo(self.container);
        self.playlist = new SBK.PlayListBook(playlist_filename, self.content_container, null, self);
        self.playlist.render();
    },

    _display_lyrics: function () {
        var self = this;

        self.content_container = jQuery('<div id="lyrics-display"></div>').appendTo(self.container);
        self.song = new SBK.SongLyricsDisplay(
            self.content_container, 
            self.application_state.id,
            self,
            self.application_state.key, 
            self.application_state.capo
        );
        self.song.render();
    },

    _edit_song: function (id) {
        var self = this;

        if (typeof(id) === 'undefined') {
            id = null;
        }
        self.content_container = jQuery('<div id="lyrics-edit"></div>').appendTo(self.container);
        self.song = new SBK.SongLyricsEdit(
            self.content_container, 
            id,
            self
        );
        self.song.render();
    },

    
    value_or_blank: function (value) {
        var self = this, result;
        
        if (typeof(value) === 'string') {
            result = value;
        } else {
            result = '';
        }
        
        return result;
    }
});