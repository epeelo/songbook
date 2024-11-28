<?php /* Template/Viewer/palette.php*/  
//debug($filter_set);




//echo("<h1>Palette</h2><br />");
echo('<div class="filter-palette">');
?>

<script>
$(document).ready(function(){
	/* Make songnames into links, opening the song in the viewer panel */
    jQuery('#songlist>li').on('click', function (event) {
        this_li = jQuery(event.target).closest('li');

		link_url = '/songbook/songs/view/' + this_li.attr('data-id') + '?key=' + this_li.attr('data-key') + '&capo=' + this_li.attr('data-capo') + '';
    	
    	//console.log(this_li, link_url);
    	window.open(link_url, "_blank");
    });


    /* show/hide the tools panel */
    SBK.StaticFunctions.make_hideable_panel('.container-hideable', "...", "^");

    /* allow songlist title to be examined */
    jQuery('.filter-title').on('click', function(){jQuery(this).toggleClass('expanded')});
});
</script>


<?php /* Hideable filter panel --------------------------------- */ ?>
    	



<?php 
foreach ($filtered_lists as $this_songlist) {
    echo $this_songlist;
}

echo('</div>');
?>
<span class="container-hideable"  style="visibility: hidden;">

<a class="button" href = "/songbook/viewer">Viewer</a>
<a class="button" href = "/songbook/dashboard">Dashboard</a>

        	<ul><?php 
        	foreach($filter_definition_sets as $filter_set_name => $filter_set_definition) {
        	    ?>
                <li><?php
                    echo $this->Html->link(__($filter_set_name), ['controller' => 'viewer', 'action' => 'palette', '?'=>['filter_set'=>http_build_query($filter_set_definition)]], ['target'=>'_blank']) 
                ?></li><?php 
        	}
                ?>
            </ul>
            <ul>
                <li><?= $this->Html->link(__('New Song'  ), ['controller' => 'Songs',      'action' => 'add'  ]) ?></li>
                <li><?= $this->Html->link(__('Dashboard' ), ['controller' => 'Dashboard',      'action' => 'index']) ?></li>
            </ul>
        </span>
        <?php /* end of Hideable filter panel --------------------------------- */ ?>




        
