<nav class="large-2 medium-2 columns" id="actions-sidebar">
<?= $this->Form->create(Null, ['url' => ['controller' => 'dashboard', 'action' => 'index']]) ?>
<!-- fieldset>
	<span class="text-search">
	<?php 
	// echo $this->Form->control('text_search', ['label'=>'Text Search (name only)']); 
	?>
	</span>
	
	<span class="search button"><?= $this->Form->button(__('Search')) ?></span>
</fieldset -->
<?= $this->Form->end() ?>

<?php if(isset($delete_id)) {?>
<ul class="side-nav">
    <li class="heading"><?= $this->Form->postLink(
    __('Delete'),
    ['action' => 'delete', $delete_id],
    ['confirm' => __('Are you sure you want to delete # {0}?', $delete_id)]
) ?></li></ul>
<?php  }?>
<ul class="side-nav">
	<li class="heading"><?= __('Main Menu') ?></li>
    <li><?= $this->Html->link(__('Viewer'), ['controller' => 'Viewer', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index']) ?></li>
	<li><?= $this->Html->link(__('New Song'), ['controller' => 'Songs', 'action' => 'add']) ?></li>
	<li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
	<li><?= $this->Html->link(__('Songs'), ['controller' => 'Songs', 'action' => 'index']) ?></li>
	<li><?= $this->Html->link(__('Playlists'), ['controller' => 'Playlists', 'action' => 'index']) ?></li>
	<li><?= $this->Html->link(__('Performers'), ['controller' => 'Performers', 'action' => 'index']) ?></li>
	<li><?= $this->Html->link(__('Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
</ul>
<?php  if(isset($controller_name)){ ?>
<ul class="side-nav">
	<li class="heading"><?= __($controller_name . ' Actions') ?></li>
	<li><?= $this->Html->link(__('New ' . $controller_name), ['action' => 'add']) ?></li>
	<li><?= $this->Html->link(__('List ' . $controller_name . 's'), ['controller' => $controller_name.'s', 'action' => 'index']) ?></li>
	<li><?= $this->Html->link(__('New Song Performance'), ['controller' =>  $controller_name.'s', 'action' => 'add']) ?></li>
</ul>
<?php  }?>
</nav>