<?php /* Template/Songs/view.php */ ?>

<? /* ------------------------
    Get viewport size, make it available to the PHP system               */ ?>
               
    <script>
        $(document).ready(function(){
            SBK.StaticFunctions.set_window_size_in_URL();

            SBK.StaticFunctions.make_hideable_panel('.hideable-dashboard', "...", "^");

        });
    </script>
<? /* ----------------------*/ ?>

 <span id="hideable_dashboard_positioning_container">
        <div class="hideable-dashboard hideable-visible song-row" style="visibility: hidden;">
        	<?php /* -----||Button to open the Viewer (in a different tab) */ ?>
            <span class="button float-right top-button-set"><?= $this->Html->link(__('Viewer'), ['controller' => 'Viewer', 'action' => 'index', 'target' => '_blank']) ?></span>
            <?php /* -----||Button to open the dashboard (in a different tab) */ ?>
            <span class="button float-right top-button-set"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index', 'target' => '_blank']) ?></span>
            
        	<?php /* -----||Button to open the current song in a different tab */ ?>
            <span class="button float-right top-button-set"><?= $this->Html->link(__('View'), ['controller' => 'Songs', 'action' => 'view/'.$song['id'], 'target' => '_new'.$song['id']]) ?></span>
            
            <?php /* -----||Search field */ ?>
        
            <span class="search-form float-right top-button-set">
        	    <?= $this->Form->create(null, ['url' => ['controller' => 'dashboard', 'action' => 'index']]) ?>
        	    <fieldset>
        	        <span class="text-search">
        	        <?= $this->Form->control('text_search', ['label'=>False]); ?>
        	        </span>
        	    </fieldset>
        	    <span class="selected-tags-and-performer button"><?= $this->Form->button(__('Search')) ?></span>
        	    <?= $this->Form->end() ?>
            </span> 
           
            
            <?php /* -----||Button to mark this song as 'played' - to record the fact of this song being played, and the time&date */ ?>
            <?= $this->element('ajax-button-form-played', ['current_song' => $song]); ?>
            
            <?php /* -----||Button to 'vote' this song as 'played' - to record the fact that this song got a good reaction when played, to identify successful songs fo future setlists */ ?>
            <?= $this->element('ajax-button-form-vote', ['current_song' => $song]); ?>
            
            <?php /* -----||Button to open a printable version (in a different tab)  */ ?>
            <span class="button float-right top-button-set"><?= $this->Html->link(__('Print'), ['controller' => 'Songs', 'target' => '_blank', 'action' => 'printable', $song->id, '?' => ['key' => $current_key, 'capo' => $capo]]) ?></span>
            
            <?php /* -----||Button to open a edit page (in a different tab)   */ ?>
            <span class="button edit float-right top-button-set" ><?= 
                $this->Html->link(__(
                    'edit'
                ), [
                    'controller' => 'songs',
                    'action' => 'edit', 
                    $song->id
                ], [
                    'target'=>'new'
                ]) ?></span>  
                
            <?php /* -----||Key&Capo picker   */?>
            <span class="key">
        	        <span class="base-key">
        	            <label><?= __('Base Key') ?></label>
        	            <span><?= h($song->base_key) ?>
        	            <form class="key" action="" method="get" id="key_form" style="display: none;">
        	                <input type="text" name="key" value = "<?= $current_key ?>" id="key_input"></input>
        	                <input type="text" name="capo" value = "<?= $capo ?>" id="capo_input"></input>
        	                <input type="hidden" name="vw" value = "<?= $page_width ?>" id="vw"></input>
        	                <input type="hidden" name="vh" value = "<?= $page_height ?>" id="vh"></input>
        	            </form>
        	             </span>
        	        </span>
                    <span class="key-capo-selectors">
                        <span class="target-key">
                            <label>key: </label>
                            <select class="data" onchange="SBK.CakeUI.form.submit_value(jQuery(this).val(), '#key_input')">
                                <option value=""></option>
                                <?php 
                                    foreach (App\Controller\StaticFunctionController::$NOTE_VALUE_ARRAY as $key => $value) {
                                        echo '<option value="' . $key . '"';
                                        if ($key === $current_key){
                                            echo ' selected ';
                                        }
                                        echo '>' . $key . '</option>';
                                    }
                                ?>
                            </select>
                        </span>
                        <span class="capo">
                            <label>capo: </label>
                            <select class="data" onchange="SBK.CakeUI.form.submit_value(jQuery(this).val(), '#capo_input')" name="capo">
                            <?php for($capo_index = 0; $capo_index < 15; $capo_index = $capo_index + 1){
                                echo '<option value="' . $capo_index . '"';
                                if($capo_index == $capo) {
                                    echo ' selected';
                                }
                                echo '>' . $capo_index . '</option>';
                            }
                            ?>
                            </select>
                         </span>
                     </span>
              </span>
        
             <span class="performers">
                <span class="known-keys">
                        <label>Known keys: </label>
                    <?php
                    if (sizeof($song->set_songs) > 0) {
                        foreach ($song->set_songs as $set_song) {
                            $clickaction = "SBK.CakeUI.form.submit_value_json('{&quot;key_input&quot;:&quot;".$set_song['key'].'&quot;, &quot;capo_input&quot;:'.$set_song['capo']."}')";
                            echo '<span class="performer">';
                            echo '<span class="nickname" onclick="'.$clickaction.'">'.$set_song['performer']['nickname'].' : </span>';
                            echo '<span class="key" onclick="'.$clickaction.'">'.$set_song['key'].'</span>';
                            //Button to open in a different tab) the editor for the current 'setsong'
                            echo $this->Html->link(__(
                                'edit'
                                ), [
                                    'controller' => 'SetSongs',
                                    'action' => 'edit',
                                    $set_song->id
                                ], [
                                    'class'=>'button',
                                    'target'=>'new'
                                ]) ;
                            //echo '<span class="capo">'.$set_song['capo'].'</span>'; 
                            echo '</span>';
                        } 
                    } else {
                        echo "(no known keys for specific performers)";
                    }
                    ?>
                </span>
                <span class="add-known-key">
                    
                       
                    <?= $this->Form->create($setSong, ['url' => ['controller' => 'SetSongs', 'action' => 'addret', 'songs', 'view', $song->id]]) ?>
        		    <fieldset>
        		        <label><?= __('add a known key:') ?></label>
        		        <?php
        		            echo $this->Form->hidden('set_id', ['value' => 0]);
        		            echo $this->Form->hidden('song_id', ['value' => $song->id]);
        		            echo $this->Form->control('performer_id', ['empty' => 'Please select ...', 'options' => $performers]);
        		            echo $this->Form->control('key');
        		            //echo $this->Form->control('capo');
        		        ?>
        		    </fieldset>
        		    <span class="button"><?= $this->Form->button(__('Submit')) ?></span>
        		    <?= $this->Form->end() ?>
                </span>
                
             </span>
                   
            <?php /* -----||Tags form   */?>
            <span class="tags">
                <label>
                    <?= __('Tags') ?>
                </label>
                <span class="song-tags">
                <?php 
                    echo $this->element('ajax-form-tags', [
                        'current_song' => $song,
                        'all_tags' => $tags,
                        'ajax_callback' => 'SBK.CakeUI.ajaxcallback.song_view.set_tags'
                    ]);
                ?>
                </span>
            </span>
            <span class="debug-info">
            	<?php  echo $_SERVER['REQUEST_URI']; ?>
            </span>
        </div> 
    </span>

   <?php
      echo $title_block_html;
   ?>
   <?= $song->printable_content; ?>

