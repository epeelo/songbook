<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use App\Controller\StaticFunctionController;
use App\Model\Entity\Song;
use App\Model\Entity\SongTag;
use App\Model\Entity\Tag;
use App\Model\Entity\SetSong;
use App\Model\Entity\Performer;

class songlistComponent extends Component {

	public function filterAllSongs($event = Null) {
	
		$controller = $this->_registry->getController();
	
		$controller->loadModel('Songs');
		$controller->loadModel('Events');
		$controller->loadModel('SongPerformances');
		$controller->loadModel('SongVotes');
	
		$filtered_list_query = $controller->Songs->find();
		$filtered_list_query->select(['id', 'title', 'written_by', 'performed_by', 'base_key', 'content'], false);
		$filtered_list_query->contain(['SongTags'=>['Tags']]);
		$filtered_list_query->contain(['SetSongs.Performers']);
	
		// start song performances -------------------
		if(is_null($event)) {
			//find all of the songs played in the last 3 hours (supposed to be current event)
			$performance_conditions = [];
			$performance_query = $controller->SongPerformances->find();
			$performance_query->Where("`SongPerformances`.`timestamp` BETWEEN \"" . date("Y-m-d H:i:s",  strtotime('-3 hours'))."\" AND \"".date("Y-m-d H:i:s")."\"");
			$performance_query->distinct('song_id');
			$performance_list = $performance_query->extract('song_id');
		
			$song_id_list = [];
			foreach($performance_list as $id => $song_id) {
				array_push($song_id_list, $song_id);
			}
		
			if(sizeof($song_id_list) > 0) {
				$song_id_string = str_replace(['[', ']'], ['(', ')'], json_encode($song_id_list));
				$filtered_list_query->select(['played' => '(`Songs`.`id` IN ' . $song_id_string . ')']);
			}
		} else {
			//find all of the songs played during the event - assume event time +/- event duration
			if(isset($event->duration_hours) && $event->duration_hours > 0) {
				$event_duration_seconds = $event->duration_hours * 60 * 60;
			} else {
				$event_duration_seconds = 3 * 60 * 60;
			}
			$performance_conditions = [];
			$performance_query = $controller->SongPerformances->find();
			$performance_query->Where("`SongPerformances`.`timestamp` BETWEEN \"".date("Y-m-d H:i:s", strtotime($event->timestamp) - $event_duration_seconds)."\" AND \"".date("Y-m-d H:i:s", strtotime($event->timestamp) + $event_duration_seconds)."\"");
			$performance_query->distinct('song_id');
			$performance_list = $performance_query->extract('song_id');
		
			$song_id_list = [];
			foreach($performance_list as $id => $song_id) {
				array_push($song_id_list, $song_id);
			}
		
			if(sizeof($song_id_list) > 0) {
				$song_id_string = str_replace(['[', ']'], ['(', ')'], json_encode($song_id_list));
				//restrict the results to songs played during the event
				$filtered_list_query->andWhere(['`Songs`.`id` IN' => $song_id_list]);
			}
		}
		// end song performances -------------------
	
		// start song votes -------------------
		//similary, find all of the songs voted in the last 3 hours (supposed to be current event)
		$vote_conditions = [];
		$vote_query = $controller->SongVotes->find();
		$vote_query->Where("`SongVotes`.`timestamp` BETWEEN \"" . date("Y-m-d H:i:s",  strtotime('-3 hours'))."\" AND \"".date("Y-m-d H:i:s")."\"");
		$vote_query->distinct('song_id');
		$vote_list = $vote_query->extract('song_id');
	
		$song_id_list = [];
		foreach($vote_list as $id => $song_id) {
			array_push($song_id_list, $song_id);
		}
	
		if(sizeof($song_id_list) > 0) {
			$song_id_string = str_replace(['[', ']'], ['(', ')'], json_encode($song_id_list));
			$filtered_list_query->select(['voted' => '(`Songs`.`id` IN ' . $song_id_string . ')']);
		}
		// end song votes -------------------
	
		if ($controller->request->is(array('post', 'put'))) {
			if(array_key_exists('text_search', $controller->request->data) && $controller->request->data['text_search']) {
				$search_string = $controller->request->data['text_search'];
				$filtered_list_query->where(['Songs.title LIKE' => '%'.$search_string.'%']);
			} else {
				$search_string = '';
			}
			if(array_key_exists('performer_id', $controller->request->data) && $controller->request->data['performer_id']) {
				$selected_performer = $controller->request->data['performer_id'];
				$filtered_list_query->matching(
						'SetSongs.Performers', function ($q) use($selected_performer)  {
						return $q->where(['Performers.id' => $selected_performer]);
						}
						);
			} else {
				$selected_performer = '';
			}
	
			if (array_key_exists('tag_id', $controller->request->data) && $controller->request->data['tag_id'] && $controller->request->data['tag_id'] != 'Tag...') {
				$selected_tag_array = $controller->request->data['tag_id'];
				$filtered_list_query->matching(
						'SongTags.Tags', function ($q) use($selected_tag_array)  {
						$q->where(['Tags.id IN' => $selected_tag_array]);
						return $q;
						}
						);
				$filtered_list_query->group('Songs.id');
				$filtered_list_query->having(['COUNT(Songs.id) = ' => sizeof($selected_tag_array)]);
			} else {
				$selected_tag_array = [];
			}
	
			if (array_key_exists('venue', $controller->request->data) && $controller->request->data['venue']) {
				$selected_venue = $controller->request->data['venue'];
				//find all of the events that were at this venue
				$venue_query = $controller->Events->findAllByVenue($selected_venue);
				$event_times = $venue_query->toArray();
	
				//find all of the songs played during the times of the events at that venue
				$performance_conditions = [];
				$performance_query = $controller->SongPerformances->find();
				foreach($venue_query->toArray() as $key => $event) {
					$performance_query->orWhere("`SongPerformances`.`timestamp` BETWEEN \"".date("Y-m-d H:i:s", strtotime($event->timestamp) - $event->duration_hours * 60 * 60)."\" AND \"".date("Y-m-d H:i:s", strtotime($event->timestamp) + $event->duration_hours * 60 * 60)."\"");
				}
				$performance_query->distinct('song_id');
				$performance_list = $performance_query->extract('song_id');
				$song_id_list = [];
				foreach($performance_list as $id => $song_id) {
					array_push($song_id_list, $song_id);
				}
	
				$filtered_list_query->andWhere(['`Songs`.`id` IN' => $song_id_list]);
				//echo debug($filtered_list_query); die();
	
			} else {
				$selected_venue = '';
			}
		} else {
			$search_string = '';
			$selected_performer  = '';
			$selected_tag_array = [];
		}
	
		$filtered_list_query->distinct(['Songs.id']);
		$filtered_list_query->order(['Songs.id' =>'DESC']);
	
		//pass the list of all tags to the view
		$controller->loadModel('Tags');
		$all_tags = $controller->Tags->find('list');
		$controller->set('all_tags', $all_tags);
	
		//pass the list of all Performers to the view
		$controller->loadModel('Performers');
		$performers = $controller->Performers->find('all');
		$controller->set('performers', $performers);
	
		//pass the list of all known venues to the view
		$controller->loadModel('Events');
		$venues = $controller->Events->find('list', [
				'keyField' => 'venue',
				'valueField' => 'venue'
		])->distinct(['venue']);
		$controller->set('venues', $venues);
	
		//a setSong object is required in order to set up the key form on each song row
		$setSong = new SetSong();
		$controller->set('setSong', $setSong);
	
		$controller->set('search_string', $search_string);
		$controller->set('selected_tags', $selected_tag_array);
		$controller->set('filtered_list', $controller->paginate($filtered_list_query));
	
		$controller->loadModel('Performers');
		$controller->set('performers', $controller->Performers->find('list', [
				'keyField' => 'id',
				'valueField' => 'nickname'
			])
		);
	}
}
?>