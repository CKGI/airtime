<?php

	// get config variables
	require_once('config.php');

	// get number of shows to grab
	if (isset($_GET['numberOfShows'])) {
	
		if ($_GET['numberOfShows'] < 101) {
		
			$numberOfShowsToGet = $_GET['numberOfShows'];
		
		} else {
		
			die("ERROR: You can't get more than 100 shows.");
		
		}
	
	} else {
	
		$numberOfShowsToGet = 20;
	
	}

	// open database connection	
	$dbConnection = pg_pconnect("host=localhost dbname=" . $dbName . " user=" . $dbUser . " password=" . $dbPassword) or die('Could not connect to Airtime database: ' . pg_last_error());
	
	// query cc_playout_history
	$playoutHistoryQuery = "SELECT * FROM cc_playout_history WHERE instance_id IS NOT NULL ORDER BY starts, instance_id LIMIT " . $numberOfShowsToGet;
	$playoutHistoryResult = pg_query($playoutHistoryQuery);
	
	$numberOfPlayoutHistoryResults = count($playoutHistoryResult);
	
	if ($numberOfPlayoutHistoryResults == 0) {
	
		die("ERROR: No shows are available.");
	
	}

	while ($row = pg_fetch_assoc($playoutHistoryResult)) {

		$playoutHistoryInstance[$row['instance_id']][] = $row;

	}
	
	$numberOfPlayouts = count($playoutHistoryInstance);
	
	$a = 0;
	
	foreach ($playoutHistoryInstance as $phInstance) {
		
		// start filling array to be JSONized here

		// get show data
		$showDataQuery = "SELECT starts, ends FROM cc_show_instances WHERE id=" . $phInstance[0]['instance_id'];
		$showDataResult = pg_query($showDataQuery);
		$finalResult['showData'] = pg_fetch_assoc($showDataResult); 

		// get show metadata
		// cc_show for show information
		$showMetadataQuery = "SELECT name, url, genre, description FROM cc_show WHErE  id=" . $phInstance[0]['instance_id'];
		$showMetadataResult = pg_query($showMetadataQuery);
		$finalResult['showMetadata'] = pg_fetch_assoc($showMetadataResult);
		
		// get track data
		$numberOfTracks = count($phInstance); 
		
		$b = 0;
		
		do {
			$trackQuery = "SELECT track_title, album_title, artist_name, genre, year, track_number FROM cc_files WHERE id=" . $phInstance[$b]['file_id'];
			$trackResult = pg_query($trackQuery);
			$track[$b] = pg_fetch_assoc($trackResult);
			$finalResult['tracks'][$b]['show'] = $phInstance[$b]['instance_id']; 
			$finalResult['tracks'][$b]['starts'] = $phInstance[$b]['starts']; 
			$finalResult['tracks'][$b]['ends'] = $phInstance[$b]['ends']; 
			$finalResult['tracks'][$b]['track_data'] = $track[$b];
			$b++;
		} while ($b < $numberOfTracks);		
		
		$a++;
			
	}
	
	// JSON encode resultant array
	$JSONOutput = json_encode($finalResult);
	
	echo $JSONOutput
	
?>
