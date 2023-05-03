<?php
namespace App\Model\Table;

use App\Model\Entity\Song;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Songs Model
 *
 * @property \Cake\ORM\Association\HasMany $SongInstances
 * @property \Cake\ORM\Association\HasMany $SongTags
 */
class SongsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('songs'); //matches the default - no need to specify
        $this->setDisplayField('title'); //matches default??
        $this->setPrimaryKey('id'); //matches the default - no need to specify

        $this->hasMany('SetSongs', [
            'foreignKey' => 'song_id'
        ]);

        $this->hasMany('SongTags', [
            'foreignKey' => 'song_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->allowEmpty('written_by');

        $validator
            ->allowEmpty('performed_by');

        $validator
            ->allowEmpty('base_key');

        $validator
            ->allowEmpty('content');

        $validator
            ->allowEmpty('original_filename');

        $validator
            ->allowEmpty('meta_tags');

        return $validator;
    }
}
