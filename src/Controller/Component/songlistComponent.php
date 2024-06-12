<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use App\Controller\StaticFunctionController;
use App\Model\Entity\Song;
use App\Model\Entity\SongTag;
use App\Model\Entity\Tag;
use App\Model\Entity\SetSong;
use App\Model\Entity\Performer;
use Cake\View\View;

class songlistComponent extends Component {
    private $event = null;
    private $paginate = false;
    private $blank_filter_definition = [
        'sortby' => 'title',
        'direction' => 'ASC',
        'search_string' => '',
        'performers'  => [],
        'performers_exclude_all' => false,
        'tags' => [],
        'tag_exclude_all' => false,
        'exclude_tag_array' => [],
        'selected_venue' => '',
        'paginate' => false,
    ];
    
    function setEvent($event) {
        $this->event = $event;
    }
    
    function setPagination($on_or_off) {
        if($on_or_off == 'on') {
            $this->paginate = true;
        } else {
            $this->paginate = false;
        }
    }
    
    function setSortBy($param = 'title', $direction = 'ASC') {
        $this->filter_definition = [
            'sortby' => $param, 
            'direction' => $direction
        ];
    }
    
    function is_filter_on ($f) {
        //debug($f);
        //debug($this->blank_filter_definition);
        //return $this->blank_filter_definition != $f;
        $filter_on = false;
        foreach($this->blank_filter_definition as $key => $value) {
            if(array_key_exists($key, $f)) {
                if($f[$key] !== $value)  {
                    //debug("mismatch [" . $key . "]: " . $f[$key] . " vs " . $value);
                    $filter_on = true;
                }
            } else {
                
            }
        }
        
        return $filter_on;
    }
    
    public function get_filters_from_queryparams() {

        $controller = $this->_registry->getController();
        $f = $this->blank_filter_definition;

        $filter_on = false;
        if ($controller->getRequest()->is(array('post', 'put', 'get'))) {
            if ($controller->getRequest()->is(array('get'))) {
                $q = $controller->getRequest()->getQuery();
            } else {
                $q = $controller->getRequest()->getData();
            }
            
            // Title: Song Title text search
            if (array_key_exists('text_search', $q) && $q['text_search']) {
                $filter_on = true;
                $f['search_string'] = $q['text_search'];
            }
            
            // Tags: Limit the result to songs that are associated with any of the passed array of tags
            if (
                array_key_exists('filter_tag_id', $q) //legacy - should be "tags" now
                && $q['filter_tag_id']
                && $q['filter_tag_id'] != 'Tag...'
            ) {
                $filter_on = true;
                $f['tags'] = $q['filter_tag_id'];
            } 
            if (
                array_key_exists('tags', $q)
                && $q['tags']
                && $q['tags'] != 'Tag...'
           ) {
                $filter_on = true;
                $f['tags'] = $q['tags'];
           }
            
            // Performer: Limit the result to songs associated with a specific performer
            if (array_key_exists('performers', $q) && $q['performers']) {
                $filter_on = true;
                if(is_array($q['performers'])) {
                    $f['performers'] = $q['performers'];
                } else {
                    $f['performers'] = [$q['performers']];
                }
            }
            if($f['performers'] == ['']) {
                //this is an error due to the way the filter form works.
                $f['performers'] = [];
            }
            
            //Exclude tags: $f['performers']do NOT contain ALL of the selected tags here will be displayed
            $f['exclude_all'] = false; // there's no interface to set this yet, and it seems that it would be more effective to exclude all songs that contain _any_ of the selected tage (smaller list)
            if (
                array_key_exists('exclude_tag_id', $q)
                && $q['exclude_tag_id']
                && $q['exclude_tag_id'] != 'Tag...'
            ) {
                $filter_on = true;
                foreach($q['exclude_tag_id'] as $this_id) {
                    //array_push($f['tags'], array_map(function($n) { return ($n * -1); }, $q['exclude_tag_id']));
                    $f['tags'][] = -1 * $this_id;
                }
            }
            
            // Venue :  limit the result to songs that were placed within an event at the specified venue
            if (array_key_exists('venue', $q) && $q['venue']) {
                $filter_on = true;
                $f['selected_venue'] = $q['venue'];
            }
        } else {
            throw ('No Query paramters available');
        }
        if(is_array($this->filter_definition)) {
            $f['sortby'] = $this->filter_definition['sortby'];
            $f['direction'] = $this->filter_definition['direction'];
        } else if(array_key_exists('sort', $q) && $q['sort'] === 'title') {
            $f['sortby'] = 'title';
            $f['direction'] = 'ASC';
        }
        
        $paginate = true;
        if($filter_on == true) {
            $f['paginate'] = false;
        } elseif(array_key_exists("unpaginated", $q)) {
            $f['paginate'] = false;
        } elseif ($this->paginate == false) {
            $f['paginate'] = false;
        }
        
        return $f;
    }
    
    function extend_filter_definition($f) {
        $return_array = [];
        foreach ($this->blank_filter_definition as $key => $default_value) {
            if(array_key_exists($key, $f)) {
                $return_array[$key] = $f[$key];
            } else {
                $return_array[$key] = $default_value;
            }
        }
        return $return_array;
    }
    
    /**
     * 
     * @param array $f
     * @return CakePHP 4 Query object
     * 
     * $f is like:
     * [
    	            'sortby' => '',                // name of a parameter (column) to sort by
    	            'direction' => '',            // sort direction
    	            'search_string' => '',        // a piece of text to search all parameters for
    	            'performers'  => [1, -4],     // An array of performer IDs, all positive ones must be included in the filters, any negative ones must be excluded
    	            'performer_exclude_all' => false, 
    	            'tags' => [ 2,15, -13, -20,],
    	            'tag_exclude_all' => false, 
    	            'selected_venue' => '',       // A venue ID
    	            'paginate' => false,
    	    ];
     * 
     */
    
    public function filterAllSongs($f) {

        $f = $this->extend_filter_definition($f); //$f may be a partial definition. Set all undefined options to their defaults

		$controller = $this->_registry->getController();
		
		$controller->loadModel('Songs');
		$controller->loadModel('Events');
		$controller->loadModel('SongPerformances');
		$controller->loadModel('SongVotes');
	
		//basic query
		$filtered_list_query = $controller->Songs->find();

		//Specify which fields to include in the Query, and join to tags and performers
		$filtered_list_query->select(['id', 'title', 'written_by', 'performed_by', 'base_key', 'content'], false);
		$filtered_list_query->contain(['SongTags'=>['Tags']]);
		$filtered_list_query->contain(['SetSongs.Performers']);
	
		// start song performances -------------------
		//If an event has been specified, restrict the filtered list to songs played during that event.
		//If an event is not specified, don't restrict the list - but mark recently played songs.
            		if (is_null($this->event)) {
            		    //if no specific event has been identified,
            			//find all of the songs played in the last 3 hours (supposed to be current event) and add a parameter that identifies them as played
            			$event_start = strtotime('-3 hours');
            			$event_end = strtotime('-0 hours');
            		} else {
            		    //if a specific event has been identified,
            			//find all of the songs played during the event - assume event time +/- event duration - and restrict the final list to just those songs
            		    if(isset($this->event->duration_hours) && $this->event->duration_hours > 0) {
            		        $event_duration_seconds = $this->event->duration_hours * 60 * 60;
            			} else {
            				$event_duration_seconds = 3 * 60 * 60;
            			}
            			$event_start = strtotime($this->event->timestamp) - $event_duration_seconds;
            			$event_end = strtotime($this->event->timestamp) + $event_duration_seconds;
            		}
            
            		//identify a list of song ids representing songs that were part of the perfomance identified by the specified event
            		$performance_conditions = [];
            		$performance_query = $controller->SongPerformances->find();
            		$performance_query->Where("`SongPerformances`.`timestamp` BETWEEN \"" . date("Y-m-d H:i:s", $event_start)."\" AND \"".date("Y-m-d H:i:s", $event_end)."\"");
            		$performance_query->distinct('song_id');
            		$performance_list = $performance_query->all()->extract('song_id');
            		
            		$song_id_list = [];
            		foreach($performance_list as $id => $song_id) {
            			array_push($song_id_list, $song_id);
            		}
            
            		if (sizeof($song_id_list) > 0) {
            			$song_id_string = str_replace(['[', ']'], ['(', ')'], json_encode($song_id_list));
            			if(is_null($this->event)) {
            				$filtered_list_query->select(['played' => '(`Songs`.`id` IN ' . $song_id_string . ')']); //mark matching songs as having been "played" during the event 
            			} else {
            				$filtered_list_query->andWhere(['`Songs`.`id` IN' => $song_id_list]); //restrict the results to only songs played during the event
            			}
            		}
		// end song performances -------------------
	
		// start song votes -------------------
		//similary, find all of the songs voted in the last 3 hours (supposed to be current event) and add a parameter that identifies them as voted
            		$event_start = strtotime('-3 hours');
            		$event_end = strtotime('-0 hours');
            		$vote_conditions = [];
            		$vote_query = $controller->SongVotes->find();
            		$vote_query->Where("`SongVotes`.`timestamp` BETWEEN \"" . date("Y-m-d H:i:s", $event_start)."\" AND \"".date("Y-m-d H:i:s", $event_end)."\"");
            		$vote_query->distinct('song_id');
            		$vote_list = $vote_query->all()->extract('song_id');
            	
            		$song_id_list = [];
            		foreach($vote_list as $id => $song_id) {
            			array_push($song_id_list, $song_id);
            		}
            	
            		if(sizeof($song_id_list) > 0) {
            			$song_id_string = str_replace(['[', ']'], ['(', ')'], json_encode($song_id_list));
            			$filtered_list_query->select(['voted' => '(`Songs`.`id` IN ' . $song_id_string . ')']); //mark matching songs as having been "voted" during the event 
            		}
		// end song votes -------------------


			/*
			 * allow sorting by
			 *              songbook/dashboard/print-lyric-sheets?unpaginated&sort=title
			 *              songbook/dashboard?sort=title
			 * etc.
			 */ 
		if($f['sortby'] !== '' && $f['direction'] !== '') {
		    $filtered_list_query->order([$f['sortby'] => $f['direction']]);
		}
		
		//===========================================================================
		$filter_on = false;
		// enable filtering by: title, tags, performer------------------------------------
		
		// FILTER BY: [Title]: Song Title text search
		if ($f['search_string'] !== '') {
			$filter_on = true;
			$filtered_list_query->where(['Songs.title LIKE' => '%'.$f['search_string'].'%']);
		}

		//-------------------
		// FILTER BY: [Tags]: Limit the result to songs that are associated with ALL of the passed array of tags
		/*
		 * This has to be done as a subquery, because a HAVING COUNT() must be used to ensure that only songs that are associated with _all_ of the specified tags will be displayed.
		 * That statement interferes with larger more complex queries - e.g. filtered byt Tag _and_ Performer, and can filter out any records that have multiple entries in the final query.
		 *                 $filtered_list_query->having(['COUNT(Songs.id) = ' => sizeof($f['tags'])]);
		 * Keeping the HAVING COUNT() inside a subquery seems to avoid that problem
		 * 
		 * 
		 * Tags array can be a list of positive and negative tag IDs, any returned song must contain all positive entries and no negative entries
		 * e.g. [ 2,15, -13, -20,]
		 */
		$selected_tag_array = [];
		$exclude_tag_array = [];
		if (sizeof($f['tags']) > 0) {
		    $selected_tag_array = array_filter( $f['tags'], function( $val ) { return   (0<$val); } );
    		$exclude_tag_array = array_map(
        	    function($n) { return ($n * -1); },
        	    array_filter(
        	        $f['tags'],
        	        function( $val ) { return   (0>$val); }
        	    )
    	    );
    		
    		// a) FILTER BY: [Selected Tags]: Limit the result to songs that are associated with ALL of the passed array of tags
    		if (sizeof($selected_tag_array) > 0) {
    		    //you want to return only songs that have all of the selected tags.
    		    
    		    //now, start with a query that would get all songs in the database.
    			$subquery_SongWithAllTags = $controller->Songs->find();
    			
    			//Extend that query with a WHERE clause, 
    			$subquery_SongWithAllTags->matching(
    			    'SongTags.Tags', function ($q) use ($selected_tag_array)  {
        			    $q->where(['Tags.id IN' => $selected_tag_array]);
        				return $q;
    				}
    			);
    			
    			//That query will return a different entry each time a song arises that contains any one of the selected tags. 
    			// I think "group by" changes this so that only one entry is returned for songs ... but this seems to exclude songs that only have one or two of the tags? how does that work? 
    			$subquery_SongWithAllTags->group('Songs.id');
    			//Oh wait - the next bit only returns songs that had enough grouped entries to match the number of tags. that's where you only get songs that match _all_ selected tags
    			$subquery_SongWithAllTags->having(['COUNT(Songs.id) = ' => sizeof($selected_tag_array)]);
    			$filtered_list_query->Join([
    				'table' => $subquery_SongWithAllTags,
    				'alias' => 'subquery_SongWithAllTags',
    				'type' => 'INNER',
    				'conditions' => '`subquery_SongWithAllTags`.`Songs__id` = `Songs`.`id`'
    			]);
    		} 
    		
    		// b) FILTER BY: [Exclude Tags] Limit the result of previous queries to only those songs that are NOT tagged with any of these tags
    		if (sizeof($exclude_tag_array) > 0) {
    		    //You wasn to DELETE any songs already returned that are associated with ANY of the tags in the exclude_tag_array
    		    //So, start with a query that would get all songs in the database.
    		    $subquery_SongWithAnyExcludeTags = $controller->Songs->find();
    		    //and restrict it to songs that have ANY of the "exclude" tags
    		    //.e. Extend that query with a WHERE clause,
    		    $subquery_SongWithAnyExcludeTags->matching(
    		        'SongTags.Tags', function ($q) use ($exclude_tag_array)  {
        		        $q->where(['Tags.id IN' => $exclude_tag_array]);
        		        return $q;
    		        }
    		    );
    		    //That query will return a different entry each time a song arises that contains any one of the selected tags.
    		    // I think "group by" changes this so that only one entry is returned for songs
    		    $subquery_SongWithAnyExcludeTags->group('Songs.id');
    		    //Most of the time, I think you will want to exclude all saongs that contain ANY of the exclude tags.
    		    //But there could be a situation when you might want to exclude only songs that contain ALL of the exclude tags. In that case....
    		    if($f['tag_exclude_all'] === true) {
    		      $subquery_SongWithAnyExcludeTags->having(['COUNT(Songs.id) = ' => sizeof($exclude_tag_array)]);
    		    }
    		    //OK, I think that's a list of songs you DON'T want
    		    //Join this query to the main one, excluding any of these songs from the main query
    		    //
    		    $filtered_list_query->Join([
    		        'table' => $subquery_SongWithAnyExcludeTags,
    		        'alias' => 'subquery_SongWithAnyExcludeTags',
    		        'type' => 'LEFT OUTER',
    		        'conditions' => '`subquery_SongWithAnyExcludeTags`.`Songs__id` = `Songs`.`id`'
    		    ])->where(["`subquery_SongWithAnyExcludeTags`.`Songs__id` IS null"]);
    		}
		}
		
		//-------------------
		// FILTER BY: [Performer]: Limit the result to songs associated with a specific performer		
		if (sizeof($f['performers']) > 0) {
		    
		    $selected_performer_array = array_filter( $f['performers'], function( $val ) { return   (0<$val); } );
		    $exclude_performer_array = array_map(
		        function($n) { return ($n * -1); },
		        array_filter(
		            $f['performers'],
		            function( $val ) { return   (0>$val); }
		            )
		        );
		    //debug($selected_performer_array);
		    //debug($exclude_performer_array);
		    if (sizeof($selected_performer_array) > 0) {
    		    //you want to return only songs that are associated with _all_ of the selected performers.
    		    //start with a query that would get all songs in the database.
    		    $subquery_SongWithAllperformers = $controller->Songs->find();
    		    
    		    //Extend that query with a WHERE clause,
    		    $subquery_SongWithAllperformers->matching(
    		        'SetSongs.Performers', function ($q) use ($selected_performer_array)  {
        		        $q->where(['Performers.id IN' => $selected_performer_array]);
        	          return $q;
        	        }
        	    );
    		    //That query will return a different entry each time a song arises that contains any one of the selected tags.
    		    // I think "group by" changes this so that only one entry is returned for each song
    		    //$subquery_SongWithAllperformers->group('Songs.id');
    		    // the next bit  returns only songs that had enough grouped entries to match the number of performers selected. that's where you only get songs that match _all_ selected performers
    		    //NOTE:!!! THIS DOESN'T WORK satisfactorily! it eliminates songs that are requrned multiple times but are still valid.
    		    //I think the duplications may be due to a song being in multiple SETS, not having multiple performers.
    		    //Actually, multiple performrs = multiple sets, but you can have multiple sets with the same performer.
    		    //So should it be group by perforemer and set?
    		    $subquery_SongWithAllperformers->group('Songs.id, SetSongs.id');
    		    $subquery_SongWithAllperformers->having(['COUNT(Songs.id) = ' => sizeof($selected_performer_array)]);
    		    
    		    /*
    		     foreach($subquery_SongWithAllperformers as $data) {
    		     debug($data['title']);
    		     }
    		    // */
    		    
    		    
    		    
    		    
//////////////////////Alternative 2 - aame pattern as  as tags so that excludes can be handled - but doesn't seem to return the correct number of songs (17) list = 17 entries - but all unique
///HAVING COUNT(`Songs`.`id`) = 1 removes duplicates!!
//*
    		    $filtered_list_query->Join([
    		        'table' => $subquery_SongWithAllperformers,
    		        'alias' => 'subquery_SongWithAllperformers',
    		        'type' => 'INNER',
    		        'conditions' => '`subquery_SongWithAllperformers`.`Songs__id` = `Songs`.`id`'
    		    ]);
    		    /*
    		    e.g. (Note: song content and metadata removed to simplify the query)
    		    SELECT `Songs`.`id` AS `Songs__id`, `Songs`.`title` AS `Songs__title`
                FROM `songs` `Songs` 
                INNER JOIN (
                            SELECT `Songs`.`id` AS `Songs__id`, `Songs`.`title` AS `Songs__title`, `SongTags`.`id` AS `SongTags__id`, `SongTags`.`song_id` AS `SongTags__song_id`, `SongTags`.`tag_id` AS `SongTags__tag_id`, `Tags`.`id` AS `Tags__id`, `Tags`.`title` AS `Tags__title` 
                            FROM `songs` `Songs` 
                            INNER JOIN `song_tags` `SongTags` ON `Songs`.`id` = `SongTags`.`song_id` 
                            INNER JOIN `tags` `Tags` ON (`Tags`.`id` in (13,15) AND `Tags`.`id` = `SongTags`.`tag_id`) GROUP BY `Songs`.`id`  HAVING COUNT(`Songs`.`id`) = 2 
                )`subquery_SongWithAllTags` ON `subquery_SongWithAllTags`.`Songs__id` = `Songs`.`id` 
                
                
                INNER JOIN (
                    SELECT `Songs`.`id` AS `Songs__id`, `Songs`.`title` AS `Songs__title`, `SetSongs`.`id` AS `SetSongs__id`, `SetSongs`.`set_id` AS `SetSongs__set_id`, `SetSongs`.`song_id` AS `SetSongs__song_id`, `SetSongs`.`order` AS `SetSongs__order`, `SetSongs`.`performer_id` AS `SetSongs__performer_id`, `SetSongs`.`key` AS `SetSongs__key`, `SetSongs`.`capo` AS `SetSongs__capo`, `Performers`.`id` AS `Performers__id`, `Performers`.`name` AS `Performers__name`, `Performers`.`nickname` AS `Performers__nickname` 
                    FROM `songs` `Songs` 
                    INNER JOIN `set_songs` `SetSongs` ON `Songs`.`id` = `SetSongs`.`song_id` 
                    INNER JOIN `performers` `Performers` ON (
                        `Performers`.`id` in (1) AND `Performers`.`id` = `SetSongs`.`performer_id`
                    ) GROUP BY `Songs`.`id`  HAVING COUNT(`Songs`.`id`) = 1 
                ) `subquery_SongWithAllperformers` ON `subquery_SongWithAllperformers`.`Songs__id` = `Songs`.`id`
    		     
    		     note: subquery_SongWithAllperformers here returns 259 songs
    		     
    		     */
    		    
    		    
    		    
//////////////////////Alternative 1 - this seems to return the right number of songs (44), but can't be adapted to exlude performers in the same way as tags. List = 29 entries. (44 in total but many dduplicates)
/*
    		    $filtered_list_query->matching(
    		        'SetSongs.Performers', function ($q) use($selected_performer_array)  {
    		          return $q->where(['Performers.id in' => $selected_performer_array]);
    		        }
    		    );
    		    
    		    /*
    		     e.g. (Note: song content and metadata removed to simplify the query)
    		     SELECT `Songs`.`id` AS `Songs__id`, `Songs`.`title` AS `Songs__title` 
    		     FROM  `songs` `Songs`
    		     INNER JOIN (
                		     SELECT `Songs`.`id` AS `Songs__id`, `Songs`.`title` AS `Songs__title`, `SongTags`.`id` AS `SongTags__id`, `SongTags`.`song_id` AS `SongTags__song_id`, `SongTags`.`tag_id` AS `SongTags__tag_id`, `Tags`.`id` AS `Tags__id`, `Tags`.`title` AS `Tags__title`
                		     FROM `songs` `Songs`
                		     INNER JOIN `song_tags` `SongTags` ON `Songs`.`id` = `SongTags`.`song_id`
                		     INNER JOIN `tags` `Tags` ON (`Tags`.`id` in (13,15)  AND `Tags`.`id` = `SongTags`.`tag_id`) GROUP BY `Songs`.`id` HAVING COUNT(`Songs`.`id`) = 2
    		     ) `subquery_SongWithAllTags` ON `subquery_SongWithAllTags`.`Songs__id` = `Songs`.`id`
    		     
    		     
    		     INNER JOIN `set_songs` `SetSongs` ON `Songs`.`id` = `SetSongs`.`song_id`
    		     INNER JOIN `performers` `Performers` ON (
    		          `Performers`.`id` in (1)  AND  `Performers`.`id` = `SetSongs`.`performer_id`
    		     )
    		     
    		     
    		     
    		     */
    		    
		    }
		    //debug($filtered_list_query);
		    
		    
	    
		    // b) FILTER BY: [Exclude Performer] Limit the result of previous queries to only those songs that are NOT associated any of these performers
		    if (sizeof($exclude_performer_array) > 0) {
		        //You wasn to DELETE any songs already returned that are associated with ANY of the tags in the exclude_tag_array
		        //So, start with a query that would get all songs in the database.
		        $subquery_SongWithAnyExcludePerformers = $controller->Songs->find();
		        //and restrict it to songs that have ANY of the "exclude" tags
		        //.e. Extend that query with a WHERE clause,
		        $subquery_SongWithAnyExcludePerformers->matching(
		            'SetSongs.Performers', function ($q) use ($exclude_performer_array)  {
    		            $q->where(['Performers.id IN' => $exclude_performer_array]);
    		            return $q;
		            }
		        );
		        //That query will return a different entry each time a song arises that contains any one of the selected tags.
		        // I think "group by" changes this so that only one entry is returned for songs
		        $subquery_SongWithAnyExcludePerformers->group('Songs.id, SetSongs.id');
		        //Most of the time, I think you will want to exclude all songs that contain ANY of the exclude performers.
		        //But there could be a situation when you might want to exclude only songs that contain ALL of the exclude tags. In that case....
		        if($f['performers_exclude_all'] === true) {
		            $subquery_SongWithAnyExcludePerformers->having(['COUNT(Songs.id) = ' => sizeof($exclude_performer_array)]);
		        }
		        //OK, I think that's a list of songs you DON'T want
		        //Join this query to the main one, excluding any of these songs from the main query
		        //
		        $filtered_list_query->Join([
		            'table' => $subquery_SongWithAnyExcludePerformers,
		            'alias' => 'subquery_SongWithAnyExcludePerformers',
		            'type' => 'LEFT OUTER',
		            'conditions' => '`subquery_SongWithAnyExcludePerformers`.`Songs__id` = `Songs`.`id`'
		        ])->where(["`subquery_SongWithAnyExcludePerformers`.`Songs__id` IS null"]);
		    } else { /*debug('no exclude performers'); */}
		    
		} 
		
		

		//-------------------
        // FILTER BY: [Venue] :  limit the result to songs that were placed withtin an event at the specified venue
		if ($f['selected_venue'] !== '') {

			//find all of the events that were at this venue
			$venue_query = $controller->Events->findAllByVenue($f['selected_venue']);
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
			foreach ($performance_list as $id => $song_id) {
				array_push($song_id_list, $song_id);
			}

			$filtered_list_query->andWhere(['`Songs`.`id` IN' => $song_id_list]);

		}
		 

		//end of [title, tags, performer] filtering -------------------------
		//===========================================================================
		
		// Avoid multiples of a specific song
		$filtered_list_query->distinct(['Songs.id']);
        if ($filter_on) {
            $filtered_list_query->order(['Songs.title' =>'ASC']);
        } else {
            $filtered_list_query->order(['Songs.id' =>'DESC']);
        }
        //echo debug($filtered_list_query); die();
        
        
        //------------------------------------------------------------
        //Pass data to the View, separate from the list
        

		//pass the list of all tags to the view
		$controller->loadModel('Tags');
		$all_tags = $controller->Tags->find('list');
		$controller->set('all_tags', $all_tags);
	
		//pass the list of all Performers to the view
		$controller->loadModel('Performers');
		$all_performers = $controller->Performers->find('list', [
			'keyField' => 'id',
			'valueField' => 'nickname'
		]);
		$controller->set('performers', $all_performers);
	
		//pass the list of all known venues to the view
		$controller->loadModel('Events');
		$all_venues = $controller->Events->find('list', [
				'keyField' => 'venue',
				'valueField' => 'venue'
		])->distinct(['venue']);
		$controller->set('venues', $all_venues);
	
		//a setSong object is required in order to set up the key form on each song row
		$setSong = new SetSong();
		$controller->set('setSong', $setSong);

		
		$controller->set('filter_tag_id', $f['tags']);
		$controller->set('performers', $all_performers);
		
		$controller->set('search_string', $f['search_string']);
		$controller->set('selected_performer', $f['performers']);
		
		$controller->set('selected_tags', $selected_tag_array);
		$controller->set('selected_exclude_tags', $exclude_tag_array);
		
		$this->filter_on = $this->is_filter_on($f);
		$controller->set('filter_on', $this->filter_on);
		
		//for the print title
		$controller->set('exclude_all', $f['tag_exclude_all']);
		
		//for the print title
		$this->print_title = $this->print_title($f, $all_performers, $all_venues, $all_tags);
		$controller->set('print_title', $this->print_title);
		$this->page_title = $this->page_title($f, $all_performers, $all_venues, $all_tags);
		$controller->page_title = $this->page_title;
		$this->selected_performer= $f['performers'];
		
		//End of passing data to the view ------------------------------------------------------------------
		
		//Should the list be paginated?
		if($f['paginate'] == false) {
		    $controller->set('filtered_list', $filtered_list_query);
		    $controller->set('filter_on', TRUE); //prevents the footer having 'next' & 'previous' buttons
		} else {
		    $controller->set('filtered_list', $controller->paginate($filtered_list_query));
		    $controller->set('filter_on', FALSE);
		}
		
		$this->filtered_list = $filtered_list_query; //so that the calling class itself can access this list, not just the view template
		//maybe just should return rather than setting $this->  ?
		return $filtered_list_query;
	}

	private function print_title($f, $all_performers = [], $all_venues = [], $all_tags = []) {
	    
		$print_title = '';
		
		if ($f['search_string']) {
		    $print_title = $print_title . $f['search_string'];
			$print_title = $print_title . " | ";
		}
		
		$all_performers_array = []; //$all_performers is a query object, not an array
		foreach($all_performers as $id => $value) {
		    $all_performers_array[$id] = $value;
		}
		
		foreach($f['performers'] as $this_performer_id) {
		    $print_title = $print_title . '<span class="performer ';
		    if($this_performer_id > 0)  {
		        $print_title = $print_title . 'include';
		    } else {
		        $print_title = $print_title . 'exclude';
		    }
		    $print_title = $print_title . '">';
		    
		    $print_title = $print_title . '<label>Performers:</label>';
		    
		    $print_title = $print_title . $all_performers_array[abs($this_performer_id)];
		    $print_title = $print_title . '</span>';
		}
		
		if($f['selected_venue'] !== '') {
    		$all_venues_array = []; //$all_tags is a query object, not an array
    		foreach($all_venues as $id => $value) {
    		    $all_venues_array[$id] = $value;
    		}
    		
    		$print_title = $print_title . '<span class="venue"><label>Venue:</label>' . $all_venues_array[$f['selected_venue']] . '</span>';
		}

		$all_tags_array = []; //$all_tags is a query object, not an array
		foreach($all_tags as $id => $value) {
		    $all_tags_array[$id] = $value;
		}
		
		$print_title = $print_title . '<label>Tags:</label>';
		foreach($f['tags'] as $this_tag_id) {
		    $print_title = $print_title . '<span class="tag ';
		    if($this_tag_id > 0) {
		        $print_title = $print_title . 'include';
		    } else {
		        $print_title = $print_title . 'exclude';
		    }
		        $print_title = $print_title . '">';
		        
		    
		        $print_title = $print_title . $all_tags_array[abs($this_tag_id)];
		        $print_title = $print_title . '</span>';
		}

		//return substr($print_title, 0, -2);
		return $print_title;
	}

	private function page_title($f, $all_performers = [], $all_venues = [], $all_tags = []) {
	    
	    $page_title = '';
		foreach($all_performers as $performer_id => $performer_title) {
			if($performer_id == $f['performers'] && $performer_id != 0) {
				$page_title = $page_title . substr($performer_title, 0, 1);
			}
		}
		foreach($all_venues as $venue_id => $venue_title) {
			if($venue_id == $f['selected_venue']) {
				$page_title = $page_title . substr($venue_title, 0, 1);
			}
		}
		foreach($all_tags as $tag_id => $tag_title) {
			if(in_array($tag_id, $f['tags'])) {
				$page_title = $page_title . substr($tag_title, 0, 1);
			}
		}
		if ($f['search_string']) {
		    $page_title = $page_title . substr($f['search_string'], 0, 1);
		}
		if($page_title == '') {
			$page_title = 'Home';
		}

		return $page_title;
	}
	
	public function filtered_songlist_html($f = []) {
	    $html = '';
	    $query = $this->filterAllSongs($f);
	    $performer_filter_on = sizeof($f['performers']) > 0 ?  true: false;

	    //debug($filter_on);
	    
	    $html = $html . '<div class="filtered-songlist">';
	    if($this->print_title !== '') {
	        $html = $html . '<span class="filter-title">';
	        $html = $html . $this->print_title;
	        $html = $html . '<span class="query-count">';
	        $html = $html . '<label>Number of songs</label>';
	        $html = $html . $query->count();
	        $html = $html . '</span>';
	        $html = $html . '</span>';
	    }
	    $html = $html . '<ul id="songlist">';
	    
	    foreach ($query as $song) {
	        $primary_key = "";
	        $primary_capo = "";
	        $performers_html='';
	        $existing_performer_keys = [];
	        foreach ($song['set_songs'] as $set_song) {
	            $performer_key = $set_song['performer']['nickname'].$set_song['key'];
	            if (!in_array($performer_key, $existing_performer_keys)) {
	                array_push($existing_performer_keys, $performer_key);
	                if($performer_filter_on == false || in_array($set_song['performer']['id'], $f['performers']) == true) {
	                    $performers_html = $performers_html . '<span class="performer short-form">';
	                    $performers_html = $performers_html . '<span class="nickname">' . strtolower(substr($set_song['performer']['nickname'], 0, 1)) . '</span>';
	                    $performers_html = $performers_html . '<span class="key">' . $set_song['key'] . '</span>';
	                    $performers_html = $performers_html . '</span>';
	                    
	                    $primary_key = $set_song['key'];
	                    $primary_capo = $set_song['capo'];
	                }
	            }
	        }
	        $html = $html . '<li data-id="' . $song['id'] . '" data-key="' . $primary_key . '" data-capo="' . $primary_capo . '">';
	        $html = $html . '<span class="song-title">' . $song['title'] . "</span>";
	        $html = $html . $performers_html;
	        $html = $html . '</li>';
	    }
	    $html = $html . '</ul>';
	    $html = $html . '</div>';
	    
	    if($query->count() == 0) {
	        $html = '';
	    }
	    
	    return $html;
	}
}
?>
