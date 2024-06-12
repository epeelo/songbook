<?php /* Template/Dashboard/index.ctp */  
    $controller_name = 'Dashboard';
?>

<nav class="large-2 medium-2 columns" id="actions-sidebar">
<?= $this->Form->create($filtered_list, ['type' => 'get', 'url' => ['controller' => 'dashboard', 'action' => 'index']]) ?>
    <fieldset>
        <span class="text-search">
        <?= $this->Form->control('text_search', ['label'=>'Name Search', 'default' => $search_string]); ?>
        <span class="clear-filters button"><button type="button" onclick="SBK.CakeUI.form.clear_text_search_field(this)">X</button></span>
        </span>
        <span class="performer-id">                          
        <?= $this->Form->control('performer_id', ['empty' => 'Please select ...', 'options' => $performers, 'default' => $selected_performer]); ?>
        </span>
        <span class="tag-id"><label for="tag-id">Tags</label>
        <?= $this->Form->control('filter_tag_id', ['label' => '', 'options' => $all_tags, 'multiple' => true, 'default' => $selected_tags]); ?>
        </span>
        <span class="venue">
        <?= $this->Form->control('venue', ['empty' => 'Please select ...', 'options' => $venues]); ?>
        </span>
        <span class="selected-tags-and-performer button"><?= $this->Form->button(__('Filter the list')) ?></span>
        <span class="clear-filters button"><button type="button" onclick="SBK.CakeUI.form.clear_filters(this)">X</button></span>
        <span class="tag-id-exclude"><label for="exclude-tag-id">Exclude songs with any of these Tags</label>
        <?= $this->Form->control('exclude_tag_id', ['label' => '', 'options' => $all_tags, 'multiple' => true, 'default' => $selected_exclude_tags]); ?>
        </span>
    </fieldset>
    <?= $this->Form->end() ?>

    <ul class="side-nav">
        <li><?= $this->Html->link(__('Viewer'    ), ['controller' => 'viewer']) ?></li>
        <li><?= $this->Html->link(__('New Song'  ), ['controller' => 'Songs',      'action' => 'add'  ]) ?></li>
        <li><?= $this->Html->link(__('New Event' ), ['controller' => 'Events',     'action' => 'add'  ]) ?></li>
        <li><?= $this->Html->link(__('Songs'     ), ['controller' => 'Songs',      'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Playlists' ), ['controller' => 'Playlists',  'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Performers'), ['controller' => 'Performers', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Events'    ), ['controller' => 'Events',     'action' => 'index']) ?></li>
        <hr>
        <?= $this->Html->link(__('Printable'), ['controller' => 'dashboard', 'action' => 'printable', '?'=>['text_search'=>'', 'performer_id'=>$selected_performer, 'filter_tag_id'=>$filter_tag_id]], ['target'=>'_blank']) ?>
        <br>
        <?= $this->Html->link(__('Print All Lyrics'), ['controller' => 'dashboard', 'action' => 'printLyricSheets', '?'=>['text_search'=>'', 'performer_id'=>$selected_performer, 'filter_tag_id'=>$filter_tag_id]], ['target'=>'_blank']) ?>
        <hr>
        <li><?= $this->Html->link(__('Filters'), ['controller' => 'Bookmarkurls', 'action' => 'index'], ['target' => '_blank']) ?></li>
        <li><!--  ?= debug($performers) ? -->
	        <span class="add-filter ajax-button-form">
			    <?= $this->Form->create(null, ['url' => ['controller' => 'BookmarkurlsController', 'action' => 'addAjax']]) ?>
			    <?= $this->Form->hidden('query_string', ['value' => $_SERVER['QUERY_STRING']]);    ?>
                <?= $this->Form->control('title', ['label' => 'save current filter as ', 'value' => $print_title]);    ?>
			    <span class="performance-register-submit button">
			        <?= $this->Form->button(__('Save Current Filter'), ['type' => 'button', 'onclick' => 'SBK.CakeUI.form.ajaxify(this);']) ?>
			    </span>
			    <?= $this->Form->end() ?>
			</span>
        </li>
        <hr>
        <li><?= $this->Html->link(__('E-AMU'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('M-AMU'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'3', 'filter_tag_id'=>[15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('E-Irish'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[2]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('E-Lively-AMU'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'performer_id'=>'1', 'filter_tag_id'=>[13, 15]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('Christmas-AMU'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'filter_tag_id'=>[15, 21]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('Piano'), ['controller' => 'dashboard', 'action' => 'index', '?'=>['text_search'=>'', 'filter_tag_id'=>[1]]], ['target'=>'_blank']) ?></li>
        <li><?= $this->Html->link(__('Keys'), ['controller' => 'SetSongs', 'action' => 'index']) ?></li>
    </ul>
    
</nav>

<div class="print_title"><?= $print_title ?></div>
<div class="dashboard index large-10 medium-10 columns content">   
	<div id="tag_multieditor" style="display: none;">
		<span class="tag-form">
		    <?= $this->Form->create(null, ['url' => ['controller' => 'SongTags', 'action' => 'AddTagMultiAjax']]);  ?>
		    <fieldset>
		        <?php
		        echo '<span class="tag-id">'.$this->Form->control('tag_id', ['label' => '', 'style'=>'width: 80%', 'options' => $all_tags, 'multiple' => true, 'default' => $selected_tags]).'</span>';
		        ?>
            <span class="song_id_input_holder"></span>
		    </fieldset>
		    <span class="tag-add-submit button">
		        <?php echo $this->Form->button(__('Tag <br> selected <br> songs'), ['type' => 'button', 'onclick' => 'SBK.CakeUI.form.ajaxify(this, SBK.CakeUI.ajaxcallback.multitag);']); ?>
		    </span>
		    <?= $this->Form->end() ?>
		</span>
	</div>
    <table cellpadding="0" cellspacing="0">
        <tbody>
            <?php 
            foreach ($filtered_list as $song){
                if ($search_string == "") {
                    $return_port_id = " ";
                } else {
                    $return_port_id = $search_string;
                }

                $display_row = true;
                /* Not needed? Now that I've added a clause to SQL in songlist component
                if(sizeof($selected_exclude_tags) > 0) {
                    $no_of_excludables = 0;
                    foreach($selected_exclude_tags as $this_exclude_tag) {
                        foreach($song->song_tags as $this_tag) {
                            if($this_tag->tag->id ==  (int) $this_exclude_tag) {
                                $no_of_excludables = $no_of_excludables + 1;
                            }
                        }
                    }
                    if($exclude_all) {
	                    if($no_of_excludables == sizeof($selected_exclude_tags)) {
	                        $display_row = false;
	                    }
					} else {
	                    if($no_of_excludables > 0) {
	                        $display_row = false;
	                    }
					}
                }
                */
                if($display_row) {
                    echo $this->element('song_row', [
                        'current_song' => $song,
                        'this_set_songs' => $song->set_songs,
                        'performers_list' => $performers
                    ]);
                }
             } ?>  
        </tbody>
    </table>
    <?php if(!$filter_on) { ?>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
    <?php } ?>
</div>
