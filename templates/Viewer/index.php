<?php /* Template/Viewer/index.ctp */  
    $controller_name = 'Viewer';
?>
<script>
$(document).ready(function(){
	/* Make songnames into links, opening the song in the viewer panel */
    jQuery('#songlist>li').on('click', function (event) {
        this_li = jQuery(event.target);
        target_iframe = jQuery('#viewer-main>iframe');

    	song_id = this_li.attr('data-id');
    	console.log('/songbook/songs/embedded/'+song_id+'?vw='+target_iframe.width()+'&vh'+target_iframe.height()+'');
    	target_iframe.attr('src', '/songbook/songs/embedded/'+song_id+'?vw='+target_iframe.width()+'&vh'+target_iframe.height()+'');
		
    });

	/* put a filter textbox at the top of the list */
    jQuery('input#songlist_filter').keyup(function(){

        var self = this, lis = jQuery('#songlist > li');
        var searchText = self.value.toUpperCase();

        var matching_lis = lis.filter(function(i, li){
            var li_text = jQuery(li).text().toUpperCase();
            return ~li_text.indexOf(searchText);
        });

        lis.hide();
        matching_lis.show();

    });

    jQuery('span#clear_filter_button').on('click', function (event) {
        jQuery('#songlist_filter').val('');
        jQuery('#songlist > li').show();
    });

    /* show/hide the advanced filter panel */
    SBK.StaticFunctions.make_hideable_panel('#filter_container>.container-hideable', "...", "^");
});
</script>

<div class="" id="viewer-sidebar">
<span id="filter_container">
	<input id="songlist_filter" type="text" /> <span class="clear-filters button" id="clear_filter_button">X</span>
	<span class="container-hideable">
	<a class="button" href = "/songbook/dashboard">Dashboard</a>
	<ul>
        <li><?= $this->Html->link(__('E-AMU'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('M-AMU'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'3', 'filter_tag_id'=>[15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('E-Irish'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[2]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('E-Lively-AMU'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[13, 15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('Christmas-AMU'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'filter_tag_id'=>[15, 21]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('Piano'), ['controller' => 'viewer', 'action' => 'index', '?'=>['text_search'=>'', 'filter_tag_id'=>[1]]], ['target'=>'_blank']) ?></li>
    </ul>
	<?= $this->Form->create($filtered_list, ['type' => 'get', 'url' => ['controller' => 'viewer', 'action' => 'index']]) ?>
    <fieldset>
        <span class="performer-id">                          
        <?= $this->Form->control('performer_id', ['empty' => 'Please select ...', 'options' => $performers, 'default' => $selected_performer]); ?>
        </span>
        <span class="tag-id"><label for="tag-id">Tags</label>
        <?= $this->Form->control('filter_tag_id', ['label' => '', 'options' => $all_tags, 'multiple' => true, 'default' => $selected_tags]); ?>
        </span>
        <span class="selected-tags-and-performer button"><?= $this->Form->button(__('Filter the list')) ?></span>
        <span class="clear-filters button" onclick="SBK.CakeUI.form.clear_filters(this)">X</span>
        <span class="tag-id-exclude"><label for="exclude-tag-id">Exclude songs with any of these Tags</label>
        <?= $this->Form->control('exclude_tag_id', ['label' => '', 'options' => $all_tags, 'multiple' => true, 'default' => $selected_exclude_tags]); ?>
        </span>
    </fieldset>
    <?= $this->Form->end() ?>
    </span>
</span>
    <ul id="songlist">
    <?php
    foreach ($filtered_list as $song){
        /* $song: id, title, written_by, performed_by, base_key, content */
        echo '<li data-id="' . $song['id'] . '" data-key="' . $song['base_key'] . '">' . $song['title'] . '</li>';
    }
    ?>
    </ul>
    
</div>

<div id="viewer-main" >
    <iframe src="">   
    
    </iframe>
</div>
        
        
