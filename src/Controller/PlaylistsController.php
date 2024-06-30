<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Entity\SetSong;
use App\Model\Entity\PlaylistSet;
use App\Model\Entity\Set;
use App\Model\Entity\Song;
use App\Model\Entity\Performer;

/**
 * Playlists Controller
 *
 * @property \App\Model\Table\PlaylistsTable $Playlists
 */
class PlaylistsController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {   
        $playlists = $this->Playlists->find()->contain('Performers');
        $this->set('playlists', $this->paginate($playlists));
        $this->set('_serialize', ['playlists']);
    }
    
    /**
     * View method
     *
     * @param string|null $id Playlist id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $playlist = $this->Playlists->get($id, [
            'contain' => ['Performers', 'PlaylistSets' => ['Sets'=> ['Performers', 'SetSongs'=>['Songs', 'sort' => ['SetSongs.order' => 'ASC'], 'Performers']], 'sort' => ['PlaylistSets.order' => 'ASC']]]
        ]);
        
        $song = new Song();
        $setSong = new SetSong();
        $this->set('setSong', $setSong);
        $this->set(compact('playlist', 'song'));
        $this->set('_serialize', ['playlist']);
        
        $this->loadModel('Performers');
        $this->set('performers', $this->Performers->find('list', [
            'keyField' => 'id',
            'valueField' => 'nickname'
        ]
            )
            );
    }
    
    /**
     * Printable method
     *
     * @param string|null $id Playlist id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function printable($id = null)
    {
        $playlist = $this->Playlists->get($id, [
            'contain' => ['Performers', 'PlaylistSets' => ['Sets'=> ['Performers', 'SetSongs'=>['Songs', 'sort' => ['SetSongs.order' => 'ASC'], 'Performers']], 'sort' => ['PlaylistSets.order' => 'ASC']]]
        ]);

        
        foreach ($playlist->playlist_sets as $playlist_set) {
            foreach ($playlist_set->set->set_songs as $setSong) {
                //debug($setSong);
                $song_parameters["id"] = $setSong->song["id"];
                $song_parameters["title"] = $setSong->song["title"];
                $song_parameters["written_by"] = $setSong->song["written_by"];
                $song_parameters["performed_by"] = $setSong->song["performed_by"];
                $song_parameters["current_key"] = $setSong["key"];
                $song_parameters["capo"] = $setSong["capo"];
                $song_parameters["style_set_or_song"] = "multiple-songs";
                
                $html = StaticFunctionController::convert_song_content_to_HTML(
                    $setSong->song->content,
                    $setSong->song["base_key"],
                    $setSong["key"],
                    $setSong["capo"]
                    );
                
                $pages = StaticFunctionController::convert_content_HTML_to_columns(
                    $html,
                    $song_parameters
                    );
                
                $setSong->song->html_pages = $pages;
            } //foreach
        }
        
        
        $song = new Song();
        $setSong = new SetSong();
        $this->set('setSong', $setSong);
        $this->set(compact('playlist', 'song'));
        $this->set('_serialize', ['playlist']);
        
        $this->loadModel('Performers');
        $this->set('performers', $this->Performers->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'nickname'
                ]
            )
        );
        //after CakePHP 3.4
        //$this->viewBuilder()->setLayout('printable');
        //before CakePHP 3.4
        $this->viewBuilder()->Layout('printable');
    }
    
    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $playlist = $this->Playlists->newEntity([]);
        if ($this->getRequest()->is('post')) {
            $playlist = $this->Playlists->patchEntity($playlist, $this->getRequest()->getData());
            if ($this->Playlists->save($playlist)) {
                $this->Flash->success(__('The playlist has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The playlist could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('playlist'));
        $this->set('_serialize', ['playlist']);
        $this->set('performers', $this->Playlists->Performers->find('list'));
    }

    /**
     * sortSets method
     *
     * @param string|null $id Playlist id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function sortSets($id = null)
    {
        return $this->edit($id);
    }

    /**
     * Edit method
     *
     * @param string|null $id Playlist id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $playlist = $this->Playlists->get($id, [
            'contain' => ['Performers', 'PlaylistSets' => ['Sets'=> ['Performers', 'SetSongs'=>['Songs', 'sort' => ['SetSongs.order' => 'ASC']]], 'sort' => ['PlaylistSets.order' => 'ASC']]]
        ]);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $playlist = $this->Playlists->patchEntity($playlist, $this->getRequest()->data);
            if ($this->Playlists->save($playlist)) {
                $this->Flash->success(__('The playlist has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The playlist could not be saved. Please, try again.'));
            }
        }
        $setSong = new SetSong();
        $songs = $this->Playlists->PlaylistSets->Sets->SetSongs->Songs->find('list', [
        		'keyField' => 'id',
        		'valueField' => function ($e) {
        			return $e['title']."  (".$e['performed_by'].")";
        		}
        	]
        );
        $playlistSet = new PlaylistSet();
        $set = new Set();
        $song = new Song();
        $this->set(compact('playlist', 'setSong', 'songs', 'playlistSet', 'set', 'song'));
        $this->set('_serialize', ['playlist']);
        $this->set('performers', $this->Playlists->Performers->find('list', [
            		'keyField' => 'id',
            		'valueField' => 'nickname'
                ]
            )
        );
        debug($this->Playlists->PlaylistSets);

        $this->set(['sets', $this->Playlists->PlaylistSets->sets->find('list', [
            		'keyField' => 'id',
            		'valueField' =>  function ($e) {
	        			return $e['title']."  (performer ".$e['performer_id'].")";
	        		}
                ]
            )
            ]
        );
    }

    /**
     * Delete method
     *
     * @param string|null $id Playlist id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $playlist = $this->Playlists->get($id);
        if ($this->Playlists->delete($playlist)) {
            $this->Flash->success(__('The playlist has been deleted.'));
        } else {
            $this->Flash->error(__('The playlist could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
