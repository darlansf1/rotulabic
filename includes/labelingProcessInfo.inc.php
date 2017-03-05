<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';

sec_session_start();
unsetLabelingProcessData();

function printPostSetData($mysqli,$lpID){
	$rate = 0;
	$query = "SELECT postset_suggestion_acceptance_rate 
				FROM tbl_labeling_process_postset
				WHERE postset_process = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($rate);
		$stmt->fetch();
		echo				"<tr>
								<td>Min number of taggers that need to agree to accept a new label</td>
								<td>". $rate."</td>
							</tr>";
	}else{
		setAlert("There was an error when trying to retrieve information about the process");
	}
	$stmt->close();
}

function printTransductiveData($mysqli,$lpID){
	$idiom = "";
	$rate = 0;
	$query = "SELECT transductive_idiom, transductive_reset_rate
				FROM tbl_labeling_process_transductive
				WHERE transductive_process = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($idiom,$rate);
		$stmt->fetch();
		echo				"<tr>
								<td>Language of the documents</td>
								<td>". getEnglishIdiom($idiom)."</td>
							</tr>";
		echo				"<tr>
								<td>'reset' rate for the transductive algorithm</td>
								<td>". $rate."</td>
							</tr>";

	}else{
		setAlert("There was an error when trying to retrieve information about the process");
	}
	$stmt->close();
}

function printLPInfo ( $mysqli, $lpInfo ) {
	$lpID = $lpInfo['process_id'];
	$_SESSION['cur_lpAccRate']	= $lpInfo['process_label_acceptance_rate'];
	$_SESSION['cur_lpLabelingType'] = $lpInfo['process_labeling_type'];
	
	$isPreset  = ($lpInfo['process_type']=="preSet");
	
	$alg = $lpInfo['process_suggestion_algorithm'];
	$isTransductive = ($alg == 'transductive' || $alg == 'testMode');
	$lpInfo['process_name'] =($lpInfo['process_name']);
	
	$isAspectBased = ($_SESSION['cur_lpLabelingType'] != 'normal');
	
	echo "	<div class='row' align='center'  >
				<div class='col-md-6' style= 'border:1px solid #ddd;padding:20px;'>
					<h2>Labeling Process Information</h2>
					<div class='panel panel-primary'>
						<div class='panel-heading text-center'>
							<h1 class='panel-title'>Labeling Process: ".$lpInfo['process_name']."</h1>
						</div>
					
						<table class='table table-hover table-bordered  table-condensed' >";
						if($isAspectBased)
						echo "<tr>
								<td>Min agreement rate</td>
								<td>".$lpInfo['process_label_acceptance_rate']."</td>
							</tr>";
						else
						echo "<tr>
								<td>Min agreement to consider a label as final</td>
								<td>".$lpInfo['process_label_acceptance_rate']."</td>
							</tr>";
						echo "<tr>
								<td>Status</td>
								<td>".getEnglishStatus($lpInfo['process_status'])."</td>
							</tr>";
						if(!$isAspectBased)
						echo "	<tr>
								<td>Multilabel</td>
								<td>". ($lpInfo['process_multilabel']==1?"Yes":"No") ."</td>
							</tr>
							<tr>
								<td>Can taggers suggest new label options?</td>
								<td>". ($isPreset?"No":"Yes") ."</td>
							</tr>";
						echo "<tr>
								<td>Number of documents</td>
								<td>". $lpInfo['numberOfDocs'] ."</td>
							</tr>
							<tr>
								<td>Number of taggers</td>
								<td>". $lpInfo['numberOfTaggers'] ."</td>
							</tr>";
					if(!$isPreset) printPostSetData($mysqli,$lpID);	
					if($isAspectBased)
					echo 	"<tr>
								<td>Aspect Identification Algorithm</td>
								<td>". getEnglishAlgorithm($lpInfo['process_aspect_suggestion_algorithm'])."</td>
							</tr>
							<tr>
								<td>Polarity Classification Algorithm</td>
								<td>". getEnglishAlgorithm($lpInfo['process_suggestion_algorithm'])."</td>
							</tr>
							<tr>
								<td>Using Automated Translator</td>
								<td>".(($lpInfo['process_translator'] == 1)? "Yes":"No")."</td>
							</tr>
							<tr>
								<td>Language of the documents</td>
								<td>".getEnglishIdiom($lpInfo['process_language'])."</td>
							</tr>";
					else
					echo	"<tr>
								<td>Label Suggestion Algorithm</td>
								<td>". getEnglishAlgorithm($lpInfo['process_suggestion_algorithm'])."</td>
							</tr>";
					if($isTransductive) printTransductiveData($mysqli,$lpID);			
	
	echo	"			</table>
					</div>
					<div class = 'row'>	
						<div class='col-md-8 col-centered'>
							<form action='Taggers.php' method='get'>
								<input type='hidden' name='lpID' value=".$lpID. "/>
								<input type='submit' class='btn btn-default btn-block'  value='Add Taggers'/>
							</form>
						</div>
					</div>
				</div>
			</div>";
	
	if($isAspectBased){
		showStatistics($lpInfo, $mysqli);
	}
}

function showStatistics($lpInfo, $mysqli){	
	$totalDocs 	= $lpInfo['numberOfDocs']*$lpInfo['numberOfTaggers'];
	$completed 	= $lpInfo['totalLabeledDocs'];
	$progress	= (int) ($completed * 100 / $totalDocs) ;
	
	echo "	<hr>
			<div class='row' align='center'  >
				<div class='col-md-6' style= 'border:1px solid #ddd;padding:20px;'>
					<h2>Labeling Process Statistics</h2>".
						"<abbr title=\"sum of the number of documents annoted by each tagger / number of documents in the process times number of taggers\">
							Total Progress"."(".$completed."/".$totalDocs."):"."
					<div class='progress col-xs-6 col-sm-4 col-centered' style='padding:0'>".
						"<div class='progress-bar' role='progressbar'".
							" aria-valuenow='" . $completed . "' " . 
							" aria-valuemin='" . 0 . "' " . 
							" aria-valuemax='" . $totalDocs . "' " . 
							" style='width:" .$progress. "%'>".
								$progress. "% complete". 
						'</div>'.
					'</div>'."
					<br>
					<div class='panel panel-primary'>
						<div class='panel-heading text-center' data-toggle='collapse' data-target='#accordion'>
							<h1 class='panel-title'>Progress by Tagger</h1>
							hide/show
						</div>
						<div id='accordion' class='collapse in'>
							<table class='table table-hover table-bordered  table-condensed' >
								".showProgressByTagger($lpInfo, $mysqli)."          
							</table>								
						</div>
					</div>
					".showAgreementInfo($lpInfo, $mysqli)."
				</div>
			</div>";
}

function showAgreementInfo($lpInfo, $mysqli){
	$info = "";
	
	$word = array(); $start = array(); $polarity = array(); $document = array(); $type = array(); $tagger = array();
	
	$query = "SELECT aspect_aspect, aspect_start, aspect_polarity, aspect_doc, aspect_type, aspect_tagger
					FROM tbl_aspect 
					WHERE aspect_lp = ? ORDER BY aspect_doc"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpInfo['process_id'] );
	
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($word, $data[0]);
			array_push($start, $data[1]);
			array_push($polarity, $data[2]);
			array_push($document, $data[3]);
			array_push($type, $data[4]);
			array_push($tagger, $data[5]);
		}
	}else{
		setAlert("Error retrieving data from the database");
	}
	$stmt->close();
	
	//Grouping by document
	$docAspects = array(); $docStarts = array(); $docPolarities = array(); $docIds = array(); $k = -1; $docTagger = array();
	for($i = 0; $i < count($word); $i++){
		if($i == 0 || ($i != 0 && $document[$i-1] != $document[$i])){
			array_push($docAspects, array());
			array_push($docStarts, array());
			array_push($docTagger, array());
			array_push($docPolarities, array());
			array_push($docIds, $document[$i]);
			$k++;
		}
		
		if($type[$i] == "normal")
			array_push($docAspects[$k], $word[$i]);
		else
			array_push($docAspects[$k], $type[$i]);
		
		array_push($docTagger[$k], $tagger[$i]);
		array_push($docStarts[$k], $start[$i]);
		array_push($docPolarities[$k], $polarity[$i]);
	}
	
	//arrays used to keep track of the number of aspects with the min agreement rate per doc
	$agreedCountPol = array();
	$totalAgreedPol = 0;
	$agreedCountNoPol = array();
	$totalAgreedNoPol = 0;
	
	//counts docs that have all aspects with the min agreement rate
	$agreedDocNoPol = 0;
	$agreedDocPol = 0;
	
	$agreementRate = 0;
	$minAgreementRate = $lpInfo['process_label_acceptance_rate'];
	$totalDifferentAspects = 0;
	$aspectsPerDoc = array();
	
	for($i = 0; $i < count($docIds); $i++){
		$agreedPol = 0;
		$agreedNoPol = 0;
		
		//arrays to keep track of the verified aspects
		$countedPol = array();
		$countedNoPol = array();
		array_push($agreedCountPol, 0);
		array_push($agreedCountNoPol, 0);
		array_push($aspectsPerDoc, 0);
		
		for($j = 0; $j < count($docAspects[$i]); $j++){
			$k = $j;
			
			//phpAlert("before i: ".$i.", k: ".$k.", count(docAspects): ".count($docAspects[$i]));
			while(count($countedNoPol) > $k && $countedNoPol[$k] == 1)
				$k++;
			//phpAlert("after i: ".$i.", k: ".$k);
			
			$currentWord; $currentStart; $currentTagger; 
			$beginning = $k;
			
			//counting agreement without taking polarity into consideration
			while($k < count($docAspects[$i])){	
				if(count($countedNoPol) > $k && $countedNoPol[$k] == 1){
					$k++;
					continue;
				}
				if($k == $beginning){
					$currentTagger = $docTagger[$i][$k];
					$currentWord = $docAspects[$i][$k];
					$currentStart = $docStarts[$i][$k];
					$totalDifferentAspects++;
					$aspectsPerDoc[$i]++;
					$agreementRate = 0;
				}
				
				if(count($countedNoPol) == $k)
					array_push($countedNoPol, 0);
				if($docAspects[$i][$k] == $currentWord && $docStarts[$i][$k] == $currentStart && ($currentTagger != $docTagger[$i][$k] || $k == $beginning)){
					$agreementRate++;
					$countedNoPol[$k] = 1;
				}
					
				$k++;
				if($k == count($docAspects[$i]) && $agreementRate >= $minAgreementRate){
					$agreedCountNoPol[$i]++;
				}
			}
			
			$k = $j;
			while(count($countedPol) > $k && $countedPol[$k] == 1)
				$k++;
			
			$currentPolarity;
			$beginning = $k;
			
			//counting agreement considering polarity
			while($k < count($docAspects[$i])){			
				if(count($countedPol) > $k && $countedPol[$k] == 1){
					$k++;
					continue;
				}
				if($k == $beginning){
					$currentTagger = $docTagger[$i][$k];
					$currentWord = $docAspects[$i][$k];
					$currentStart = $docStarts[$i][$k];
					$currentPolarity = $docPolarities[$i][$k];
					$agreementRate = 0;
				}
				
				if(count($countedPol) == $k)
					array_push($countedPol, 0);
				if($docAspects[$i][$k] == $currentWord && $docStarts[$i][$k] == $currentStart 
														&& $docPolarities[$i][$k] == $currentPolarity
														&& ($currentTagger != $docTagger[$i][$k] || $k == $beginning)){
					$agreementRate++;
					$countedPol[$k] = 1;
				}
					
				$k++;
				if($k == count($docAspects[$i]) && $agreementRate >= $minAgreementRate){
					$agreedCountPol[$i]++;
				}
			}
		}
		
		$totalAgreedNoPol += $agreedCountNoPol[$i];
		$totalAgreedPol += $agreedCountPol[$i];
		if($agreedCountNoPol[$i] == $aspectsPerDoc[$i])
			$agreedDocNoPol++;
		if($agreedCountPol[$i] == $aspectsPerDoc[$i])
			$agreedDocPol++;
		
		//phpAlert("aspects per doc (".$i."): ".$aspectsPerDoc[$i]);
	}
	
	$totalDocs = count($docIds);
	$progressDoc = (int) ($agreedDocPol * 100 / max($totalDocs, 1)); 
	$progressNoPol = (int) ($totalAgreedNoPol * 100 / max($totalDifferentAspects, 1)) ;
	$progressPol = (int) ($totalAgreedPol * 100 / max($totalDifferentAspects, 1)) ;
	
	//TODO create string with the HTML code to be printed
	$info = "<abbr title=\"number of diferent aspects that reached min agreement when polarity is ignored  / total of different aspects from all taggers\"> 
				Aspect Agreement, not considering polarity "."(".$totalAgreedNoPol."/".$totalDifferentAspects."): "."
					<div class='progress col-xs-6 col-sm-4 col-centered' style='padding:0'>".
						"<div class='progress-bar' role='progressbar'".
							" aria-valuenow='" . $totalAgreedNoPol . "' " . 
							" aria-valuemin='" . 0 . "' " . 
							" aria-valuemax='" . $totalDifferentAspects . "' " . 
							" style='width:" .$progressNoPol. "%'>".
								$progressNoPol. "% complete". 
						'</div>'.
					'</div>'.
					"<br>
					<abbr title=\"number of diferent aspects that reached min agreement when polarity is considered  / total of different aspects from all taggers\"> 
					Agreement on Aspect and Polarity "."(".$totalAgreedPol."/".$totalDifferentAspects."): "."
					<div class='progress col-xs-6 col-sm-4 col-centered' style='padding:0'>".
						"<div class='progress-bar' role='progressbar'".
							" aria-valuenow='" . $totalAgreedPol . "' " . 
							" aria-valuemin='" . 0 . "' " . 
							" aria-valuemax='" . $totalDifferentAspects . "' " . 
							" style='width:" .$progressPol. "%'>".
								$progressPol. "% complete". 
						'</div>'.
					'</div>'.
					"<abbr title=\"number of documents to which all taggers made the exact same annotations on aspects and polarity / number of docs of the process\"> 
					<br>All-agreed Documents"."(".$agreedDocPol."/".$totalDocs."): "."
					<div class='progress col-xs-6 col-sm-4 col-centered' style='padding:0'>".
						"<div class='progress-bar' role='progressbar'".
							" aria-valuenow='" . $agreedDocPol . "' " . 
							" aria-valuemin='" . 0 . "' " . 
							" aria-valuemax='" . $totalDocs . "' " . 
							" style='width:" .$progressDoc. "%'>".
								$progressDoc. "% complete". 
						'</div>'.
					'</div>'.
					"<br>
					<div class='panel panel-primary'>
						<div class='panel-heading text-center' data-toggle='collapse' data-target='#accordion2'>
							<h1 class='panel-title'>Agreement by Document</h1>
							show/hide
						</div>
						<div id='accordion2' class='collapse out'>
							<table class='table table-hover table-bordered  table-condensed' >
								".showAgreementByDoc($mysqli, $docIds, $aspectsPerDoc, $agreedCountPol, $agreedCountNoPol)."          
							</table>								
						</div>
					</div>";
	
	return $info;
}

function showAgreementByDoc($mysqli, $docIds, $aspectsPerDoc, $agreedCountPol, $agreedCountNoPol){
	$table = 			"<tr>
							<td style='min-width:100px'><b>Document Name</b></td>
							<td><b>NOT Considering Polarity</b></td>
							<td><b>Considering Polarity</b></td>
						</tr>";
	
	$docName; $docNoPolAgreement; $docPolAgreement; $aspectsCount;
	
	for($i = 0; $i < count($docIds); $i++){
		$query = "SELECT document_name 
					FROM tbl_document
					WHERE document_id = ?"; 
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('i',$docIds[$i] );
		
		if($stmt->execute()){
			$stmt->bind_result($docName);
			$stmt->fetch();
			$docName =utf8_decode($docName);
		}else{
			setAlert("Error retrieving data from database");
		}
		$stmt->close();
		
		$aspectsCount = $aspectsPerDoc[$i];
		$docNoPolAgreement = (int) (100 * $agreedCountNoPol[$i]/$aspectsCount);
		$docPolAgreement = (int) (100 * $agreedCountPol[$i]/$aspectsCount);
		
		$table = $table."<tr>
							<td style='min-width:100px'>".$docName."</td>
							<td>".$agreedCountNoPol[$i]."/".$aspectsCount." (".$docNoPolAgreement."%)</td>
							<td>".$agreedCountPol[$i]."/".$aspectsCount." (".$docPolAgreement."%)</td>
						</tr>";
	}
	return $table;
}

function showProgressByTagger($lpInfo, $mysqli){
	$table = "";
	
	$taggerID = array(); $taggerName; $taggerProgress; $completed;
	
	
    $query = "SELECT process_tagger_tagger 
					FROM tbl_labeling_process_tagger 
					WHERE process_tagger_process = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpInfo['process_id'] );
	
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($taggerID, $data[0]);
		}
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	for($i = 0; $i < $lpInfo['numberOfTaggers']; $i++){
		$query = "SELECT user_name 
					FROM tbl_user
					WHERE user_id = ?"; 
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('i',$taggerID[$i] );
		
		if($stmt->execute()){
			$stmt->bind_result($taggerName);
			$stmt->fetch();
		}else{
			setAlert("Error retrieving data from database");
		}
		$stmt->close();
		
		$query = "SELECT count(*)
					FROM tbl_document, tbl_document_labeling
					WHERE document_process = ? 
						AND document_id = labeling_document
						AND labeling_tagger = ?
						AND (labeling_status = 'labeled' OR labeling_status = 'finalized')"; 
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('ii',$lpInfo['process_id'], $taggerID[$i]);
		
		if($stmt->execute()){
			$stmt->bind_result($completed);
			$stmt->fetch();
		}else{
			setAlert("Error retrieving data from database");
		}
		$stmt->close();
		
		$taggerProgress = (int)(100 * $completed/$lpInfo['numberOfDocs']);
		
		$table = $table."<tr>
							<td style='min-width:100px'>".$taggerName."</td>
							<td>".$completed."/".$lpInfo['numberOfDocs']." (".$taggerProgress."%)</td>
						</tr>";
	}
	return $table;
}

function getLPInfo($mysqli,$lpID) {
	$query = "	SELECT *
					FROM tbl_labeling_process 
					WHERE process_id = ? LIMIT 1" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$lpInfo = $result->fetch_assoc();
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	$query = "	SELECT COUNT(*)
					FROM tbl_document 
					WHERE document_process = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($result);
		$stmt->fetch();
		$lpInfo['numberOfDocs'] = $result;
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	$query = "	SELECT COUNT(*)
					FROM tbl_document, tbl_document_labeling
					WHERE document_process = ? 
						AND document_id = labeling_document
						AND (labeling_status = 'labeled' OR labeling_status = 'finalized')" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($result);
		$stmt->fetch();
		$lpInfo['totalLabeledDocs'] = $result;
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	$query = "	SELECT COUNT(*)
					FROM tbl_labeling_process_tagger 
					WHERE process_tagger_process = ?" ;
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$stmt->bind_result($result);
		$stmt->fetch();
		$lpInfo['numberOfTaggers'] = $result;
	}else{
		setAlert("Error retrieving data from database");
	}
	$stmt->close();
	
	if (isAlertEmpty()) {
		printLPInfo ( $mysqli, $lpInfo );
	}
}

if ( !empty($_GET['lpID'])){
	$lpID = $_GET['lpID'];
}else{
	header('Location: ./index.php');
	exit();
}