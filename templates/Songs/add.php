<?php /* Template/Songs/add.php */ 
    $controller_name = 'Song';
    echo($this->element('standard_menu', ['controller_name' => $controller_name, 'delete_id' => $song->id]) );
?>

<div class="songs songs-edit songs-add form large-9 medium-8 columns content">
    <?= $this->Form->create($song) ?>
    <fieldset>
    <?= '<span class="button vote float-right">'.$this->Form->button(__('Save Song')).'</span>' ?>
        <legend><?= __('Add Song') ?></legend>
        <?php
            echo '<span class="song-input-title">'.$this->Form->control('title').'</span>';
            echo '<span class="song-input-written_by">'.$this->Form->control('written_by').'</span>';
            echo '<span class="song-input-performed_by">'.$this->Form->control('performed_by').'</span>';
            echo '<span class="song-input-base_key">'.$this->Form->control('base_key').'</span>';
            echo '<span class="song-input-content">'.$this->Form->control('content', ['class' => 'sbk-lyrics-panel']).'</span>';
        ?>
    </fieldset>
    <?= $this->Form->end() ?>
</div>
