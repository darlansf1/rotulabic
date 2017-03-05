<?php
include_once 'functions.php';
include_once 'frequence.php';
include_once 'lexicon.php';
include_once 'PMI.php';

function getAspectSuggestionsJS($mysqli){
	$lpID = $_SESSION['cur_lpID'];
	
	$query = "SELECT process_aspect_suggestion_algorithm 
				FROM tbl_labeling_process
				WHERE process_id = ?";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	
	$algorithm;
	
	if($stmt->execute()){
		$stmt->store_result();
		$stmt->bind_result($algorithm);
		$stmt->fetch();	
	}else{
		phpAlert("Error retrieving process information");
		return;
	}
	$stmt->close();
	
	if($algorithm == 'none'){
		echo "[]";
		return;
	}
	
	$suggestions = array();
	if($algorithm == 'frequenceBased'){
		$suggestions = getAspectSuggestionsFrequence($mysqli, $lpID);
	}
	
	asort($suggestions[1]);
		
	$str = "[";
	
	$j = 0;
	$count = count($suggestions[0]);
	if($count == 0)
		$str .= "]";
	$keys = array_keys($suggestions[1]);
	foreach($keys as $i){
		if($j < $count-1)
			$str = $str."\"".$suggestions[0][$i]."\"".",".$suggestions[1][$i].",";
		else
			$str = $str."\"".$suggestions[0][$i]."\",".$suggestions[1][$i]."]";
		$j++;
	}
	
	//phpAlert($str);
	
	echo $str;
	return $suggestions;
}

function getAspectPolaritiesJS($mysqli, $aspects){
	$lpID = $_SESSION['cur_lpID'];
	$neighborhood = 10;
	$query = "SELECT process_suggestion_algorithm, process_translator, process_language 
				FROM tbl_labeling_process
				WHERE process_id = ?";
				
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	
	$algorithm; $translator; $language;
	
	if($stmt->execute()){
		$stmt->store_result();
		$stmt->bind_result($algorithm, $translator, $language);
		$stmt->fetch();	
	}else{
		phpAlert("Error retrieving process information");
		return;
	}
	$stmt->close();
	
	if($algorithm == 'none'){
		echo "[]";
		return;
	}
	
	$suggestions = array();
	if($algorithm == 'lexiconBased'){
		$suggestions = getPolaritiesFromLexicon($mysqli, $aspects, $translator, $language, $neighborhood);
	}else if($algorithm == 'PMIBased'){
		$suggestions = getPolaritiesFromPMI($mysqli, $aspects, $translator, $language);
	}
	
	
	$str = "[";
	
	$j = 0;
	$count = count($suggestions);
	if($count == 0)
		$str .= "]";
	foreach($suggestions as $suggestion){
		if($j < $count-1)
			$str = $str."\"".$suggestion."\"".",";
		else
			$str = $str."\"".$suggestion."\""."]";
		$j++;
	}
	
	echo $str;
}