<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Performers'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Song Instances'), ['controller' => 'SongInstances', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Song Instance'), ['controller' => 'SongInstances', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="performers form large-9 medium-8 columns content">
    <?= $this->Form->create($performer) ?>
    <fieldset>
        <legend><?= __('Add Performer') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('nickname');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
