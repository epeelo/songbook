<?php
  /*
  The calling view must set the following variables:
  $return_point = [controller, method, id]
  $current_song = [id, title, written_by, performed_by]
  $this_set_songs = distinct array of [performer ([name, nickname]), key, capo]
  $set_song_object =  a setSong object - required to set up the key form
  $performers_list = list of all available performers for drop-down list in key form.
  $tags = list of all avilable tags
  */
?>
<tr class="song-row <?php if($current_song->played) {echo 'played';} ?>">
    <td class="song-id"><?= h($current_song->id) ?></td>
    <td class="performers">
        <?php 
        if(sizeof($this_set_songs) > 0) {
            $primary_key = $this_set_songs[0]['key'];
        } else {
            $primary_key = false;
        }
        $existing_performer_keys = [];
        foreach ($this_set_songs as $set_song) {
            $performer_key = $set_song['performer']['nickname'].$set_song['key'];
            if (!in_array($performer_key, $existing_performer_keys)) {
                array_push($existing_performer_keys, $performer_key);
                echo '<span class="performer">';
                echo '<span class="nickname">'.$set_song['performer']['nickname'].'</span>';
                echo '<span class="key">'.$set_song['key'].'</span>';
                echo '<span class="capo">'.$set_song['capo'].'</span>';
                echo '</span>';
            }
        } ?>
    </td>
    <td class="song-main"><span class="song-title"><?= h($current_song->title) ?></span> 
    <?php if ($current_song->performed_by === '') {
            echo '<span class="written-by">('.$current_song->written_by.')</span>';
        } else {
            echo '<span class="performed-by">('.$current_song->performed_by.')</span>';
    } ?>
    <span class="tags">
    <?php 
    if($current_song->song_tags) {
        $list_of_tags = '';
        foreach ($current_song->song_tags as $this_tag) {
            $list_of_tags = $list_of_tags . '<span class="tag">' . $this_tag->tag->title . '</span>';
        }
        echo $list_of_tags;
    }
    ?>
    </span>
    <span class="actions">
        <?= $this->element('ajax-button-form-view', ['current_song' => $current_song, 'primary_key' => $primary_key]); ?>
        <?= $this->element('ajax-button-form-played', ['current_song' => $current_song]); ?>
        <?= $this->element('ajax-button-form-vote', ['current_song' => $current_song]); ?>
        <?php
        if(isset($performers_list)) { ?>
        <span class="key-form">
            <?= $this->Form->create($set_song_object, ['url' => ['controller' => 'SetSongs', 'action' => 'addAjax']]) ?>
            <fieldset>
                <?php
                    echo $this->Form->hidden('set_id', ['value' => 0]);
                    echo $this->Form->hidden('song_id', ['value' => $current_song->id]);
                    echo $this->Form->input('performer_id', ['empty' => 'Please select ...', 'options' => $performers_list]);
                    echo $this->Form->input('key');
                    //echo $this->Form->input('capo');
                ?>
            <span class="button"><?= $this->Form->button(__('Add Key'), ['type' => 'button', 'onclick' => 'SBK.CakeUI.form.ajaxify(this, SBK.CakeUI.ajaxcallback.song_row.add_key);']) ?></span>
            </fieldset>
            <?= $this->Form->end() ?>
        </span>
        <?php
        }

        if(isset($tags)) {?>
        <span class="tag-form">
            <?php
                $selected_tags = [];
                foreach ($current_song->song_tags as $this_tag) {
                    array_push($selected_tags, $this_tag['tag_id']);
                }
            ?>
            <?= $this->Form->create(null, ['url' => ['controller' => 'SongTags', 'action' => 'matchListAjax']]) ?>
            <fieldset>
                <?php
                    echo '<span class="tag-id">'.$this->Form->input('tag_id', ['label' => '', 'options' => $tags, 'multiple' => true, 'default' => $selected_tags]).'</span>';
                    echo $this->Form->hidden('song_id', ['value' => $current_song->id]);
                ?>
            </fieldset>
            <span class="tag-add-submit button"><?= $this->Form->button(__('Update tags'), ['type' => 'button', 'onclick' => 'SBK.CakeUI.form.ajaxify(this, SBK.CakeUI.ajaxcallback.song_row.set_tags);']) ?></span>
            <?= $this->Form->end() ?>
        </span>
        <?php
        }
        ?>
    </span>
    </td>
</tr>