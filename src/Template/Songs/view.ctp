<?php /* Template/Songs/view.php */ ?>

<div class="songs view content lyrics-display">
    <span class="button float-right"><?= $this->Html->link(__('List Playlists'), ['controller' => 'Playlists', 'action' => 'index']) ?></span>
    <span class="search-form float-right"><?= $this->Form->create('search songs', ['url' => ['controller' => 'Songs', 'action' => 'index']]) ?>
        <fieldset><?= $this->Form->input('Search', ['label' => false]) ?></fieldset>
        <span class="button"><?= $this->Form->button(__('Search for Songs')) ?></span>
        <?= $this->Form->end() ?>
    </span>
    <h3><?= h($song->title) ?></h3>
    <table class="vertical-table">
        <tr class="title">
            <th><?= __('Title') ?></th>
            <td><?= h($song->title) ?></td>
        </tr>
        <tr class="written-by">
            <th><?= __('Written By') ?></th>
            <td><?= h($song->written_by) ?></td>
        </tr>
        <tr class="performed-by">
            <th><?= __('Performed By') ?></th>
            <td><?= h($song->performed_by) ?></td>
        </tr>
        <tr class="base-key">
            <th><?= __('Base Key') ?></th>
            <td><?= h($song->base_key) ?>
            <form class="key" action="" method="get">
                <span class="target-key">
                    <label>key: </label>
                    <select class="data" onchange="this.form.submit()" name="key">
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
                    <select class="data" onchange="this.form.submit()" name="capo">
                    <?php for($capo_index = 0; $capo_index < 9; $capo_index = $capo_index + 1){
                        echo '<option value="' . $capo_index . '"';
                        if($capo_index == $capo) {
                            echo ' selected';
                        }
                        echo '>' . $capo_index . '</option>';
                    }
                    ?>
                    </select>
                    </span>
            </form>
            </td>
        </tr>
        <tr class="original-filename">
            <th><?= __('Original Filename') ?></th>
            <td><?= h($song->original_filename) ?></td>
        </tr>
        <tr class="id">
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($song->id) ?></td>
        </tr>
        <tr class="meta-tags">
            <th><?= __('Meta Tags') ?></th>
            <td><?= h($song->meta_tags) ?></td>
        </tr>
    </table>
    <?= $song->content; ?>
    <div class="related">
        <h4><?= __('Related Song Instances') ?></h4>
        <?php if (!empty($song->song_instances)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Song Id') ?></th>
                <th><?= __('Performer Id') ?></th>
                <th><?= __('Key') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($song->song_instances as $songInstances): ?>
            <tr>
                <td><?= h($songInstances->id) ?></td>
                <td><?= h($songInstances->song_id) ?></td>
                <td><?= h($songInstances->performer_id) ?></td>
                <td><?= h($songInstances->key) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'SongInstances', 'action' => 'view', $songInstances->id]) ?>

                    <?= $this->Html->link(__('Edit'), ['controller' => 'SongInstances', 'action' => 'edit', $songInstances->id]) ?>

                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'SongInstances', 'action' => 'delete', $songInstances->id], ['confirm' => __('Are you sure you want to delete # {0}?', $songInstances->id)]) ?>

                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Song Tags') ?></h4>
        <?php if (!empty($song->song_tags)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Tag Id') ?></th>
                <th><?= __('Song Id') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($song->song_tags as $songTags): ?>
            <tr>
                <td><?= h($songTags->id) ?></td>
                <td><?= h($songTags->tag_id) ?></td>
                <td><?= h($songTags->song_id) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'SongTags', 'action' => 'view', $songTags->id]) ?>

                    <?= $this->Html->link(__('Edit'), ['controller' => 'SongTags', 'action' => 'edit', $songTags->id]) ?>

                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'SongTags', 'action' => 'delete', $songTags->id], ['confirm' => __('Are you sure you want to delete # {0}?', $songTags->id)]) ?>

                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    </div>
</div>
