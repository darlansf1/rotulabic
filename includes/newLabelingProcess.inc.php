<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';
include_once 'frequence.php';
include_once 'syntacticPatterns.php';

sec_session_start(); 

function isFormValid($mysqli) {
	if(
		(login_check($mysqli) == true) && 
		($_SESSION['user_role'] == 'processAdmin') &&  
		isset($_SESSION['user_id']) &&
		isset($_POST['lpName']) && 
		(($_POST['labelingType'] == 'normal' && isset($_POST['lpLabelAcceptanceRate']) && 
		isset($_POST['lpSuggestionAlgorithm'])) || $_POST['labelingType'] == 'annotation')
	)return true;
	return false;
}

function getLPInstructionContent () {
	$lpInst_tmpName  	= $_FILES['lpInstructions']['tmp_name'];
	$lpInst_fp      	= fopen($lpInst_tmpName, 'r');
	$lpInst_content 	= addslashes(fread($lpInst_fp, filesize($lpInst_tmpName)));
	fclose($lpInst_fp);
	return $lpInst_content;
}

function insertTransductiveData ( $mysqli , $lpID) {
	$query = "INSERT INTO tbl_labeling_process_transductive VALUES (?,?,?)"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('isi', $lpID, $_POST['lpIdiom'], $_POST['lpTransductiveResetRate']);
	if(!$stmt->execute()) {
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();
		setAlert("Erro ao inserir os dados do algoritmo transdutivo no banco de dados");
	}
	$stmt->close();
	return ;
}

function insertPostSetData ( $mysqli , $lpID) {
	$query = "INSERT INTO tbl_labeling_process_postset VALUES (?,?)"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ii', $lpID, $_POST['lpSuggestionAcceptanceRate']);

	if(!$stmt->execute()) {
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();
		setAlert("Erro ao inserir no banco de dados a taxa de aceitação das sugestões dos rotuladores");
	}
	
	$stmt->close();
	return ;
}

//Inserting a new Labeling Process on database
function insertLP ($mysqli) {		
	
	$lpName 					= $_POST['lpName'];	
	$uID 						= $_SESSION['user_id'];
	$lpInst_content 			= getLPInstructionContent ();	//Instructions file
	$lpSuggestionAlgorithm 		= $_POST['lpSuggestionAlgorithm'];
	$labelingType 				= $_POST['labelingType'];

	/*setting default values*/
	$lpMultilabel				= 0 ;
	$lpAspectSuggestionAlgorithm= 'none';
	$lpHiddenAspect = 1;
	$lpGenericAspect = 1;
	$lpType = "preSet";
	$lpTranslatorUse = 0;
	$lpLanguage = "xx";
	$lpLabelAcceptanceRate = 2;
		
	$query = "";
	
	if($labelingType == 'normal'){
		$lpLabelAcceptanceRate 		= $_POST['lpLabelAcceptanceRate'];
		$lpMultilabel				= $_POST['lpMultiLabel'] == 'true' ? 1 : 0 ;
		$lpType						= $_POST['lpType'];
	}else{
		$lpAspectSuggestionAlgorithm = $_POST['lpAspectSuggestionAlgorithm'];
		$lpHiddenAspect = $_POST['lpHiddenAspect'] == 'true' ? 1 : 0;
		$lpGenericAspect = $_POST['lpGenericAspect'] == 'true' ? 1 : 0;
		$lpTranslatorUse = $_POST['translator'] == 'true' ? 1 : 0;
		$lpLanguage = $_POST['language'];
	}
	
	$query = "INSERT INTO tbl_labeling_process
		(`process_name`, `process_admin`,`process_label_acceptance_rate`, `process_multilabel` , 
		`process_type`, `process_instructions` ,`process_suggestion_algorithm`, `process_labeling_type`,
		`process_aspect_suggestion_algorithm`, `process_hidden_aspect`, `process_generic_aspect`, 
		`process_translator`, `process_language`) 
		VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?, ? , ?)";
		
	$stmt = $mysqli->prepare($query);
	
	$lpName =($lpName); $lpInst_content =($lpInst_content);
	$lpInst_content = utf8_encode($lpInst_content);
	$stmt->bind_param('siiisssssiiis',$lpName,$uID,$lpLabelAcceptanceRate,
			$lpMultilabel,$lpType,$lpInst_content,$lpSuggestionAlgorithm, 
			$labelingType, $lpAspectSuggestionAlgorithm, $lpHiddenAspect,
			$lpGenericAspect, $lpTranslatorUse, $lpLanguage);
			
	if(!$stmt->execute()){
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();
		setAlert("Erro ao inserir o novo Processo de Rotulação no banco de dados");
		return;
	}
	$lpID = $stmt->insert_id;
	
	if( ($lpSuggestionAlgorithm == 'transductive' || $lpSuggestionAlgorithm == 'testMode')){
		insertTransductiveData($mysqli,$lpID);
	}else if($lpType == "postSet"){
		insertPostSetData($mysqli,$lpID);
	}
	
	$stmt->close();
	return $lpID;
}

//Inserting labels on database
function insertLabels ($mysqli, $lpID) {
	$query = "INSERT IGNORE INTO `tbl_label`(`label_label`) VALUES (?)";
	$stmt = $mysqli->prepare($query);

	$stmt->bind_param("s", $lbl);

	$query2 = 	"INSERT INTO `tbl_labeling_process_label_option`
					(`lpLabelOpt_lp` , `lpLabelOpt_label` ) VALUES (?,?)";
					
	if(isset($_POST['colors'])){
		$query2 = "INSERT INTO `tbl_labeling_process_label_option`
					(`lpLabelOpt_lp` , `lpLabelOpt_label`, `lpLabelOpt_color` ) VALUES (?,?,?)";
	}
	
	$stmt2 = $mysqli->prepare($query2);

	if(!isset($_POST['colors'])){
		$stmt2->bind_param("is",$lpID, $lbl);
	}else{
		$stmt2->bind_param("iss",$lpID, $lbl, $color);
	}
	
	foreach ($_POST['lpLabels'] as $lbl){
		$lbl =($lbl);
		if(isset($_POST['colors'])){
			$color = current($_POST['colors']);
			next($_POST['colors']);
		}
		
		//Inserting on table tbl_label
		if(!$stmt->execute()){
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Erro ao inserir o novo rótulo " .$lbl. " no banco de dados");
			break;
		}	
		
		//Now, inserting on table tbl_labeling_process_label_option
		if(!$stmt2->execute()){
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Erro ao inserir o novo rótulo " .$lbl. " no banco de dados");
			break;
		}
	}
	$stmt->close();
	$stmt2->close();
}

function insertRankedLabels ( $mysqli, $docID) {
	if(isset($_POST['lpLabels'])){
		$accuracy = 0.0;
		$query = "INSERT INTO tbl_ranked_label VALUES (?, ?, ?)";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param("isd", $docID, $lbl, $accuracy);
		foreach ($_POST['lpLabels'] as $lbl){
			$lbl =($lbl);
			if(!$stmt->execute()){
				echo $mysqli->error;
				$mysqli->rollback();
				setAlert("Erro ao inserir o rótulo " .$lbl. " no banco de dados");
				break;				
			}
		}
		$stmt->close();
	}	
}

//Inserting a new term on the table of terms
function insertTerm ( $mysqli , $term ) {
	$query = "INSERT IGNORE INTO tbl_term VALUES (DEFAULT , ?)";
	$stmt = $mysqli->prepare($query);
	$term = "".$term;
	$stmt->bind_param('s',($term));
	//phpAlert("bound term on insertTerm");
	if(!$stmt->execute()){
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();   
		setAlert("Error inserting term " .$term. " in the database");
		return 0;
	}
	$termID = $stmt->insert_id;
	$stmt->close();
	return $termID;
}

function getTermID ( $mysqli , $term) {
	$termID = 0;
	$query = "	SELECT term_id FROM tbl_term WHERE term_term = ? LIMIT 1";
	$stmt = $mysqli->prepare($query);
	$term = "".$term;
	$stmt->bind_param('s', $term);
	if($stmt->execute()){
		$stmt->bind_result($termID);
		$stmt->fetch();	
	}else{
		echo $mysqli->error;
		$mysqli->rollback();
		setAlert("Erro ao recuperar o ID do termo: " . $term);		
	}
	$stmt->close();
	return $termID;
}

//Inserting terms (from documents) on database
function insertDocumentTerms ( $mysqli , $text, $docID ) {

	$query = "	INSERT INTO tbl_document_term
					VALUES (?,?,1)
					ON DUPLICATE KEY UPDATE term_count = term_count + 1"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ii',$termID,$docID);
	//phpAlert("bound parameters on insertDocumentTerms");
	$term = strtok ($text, " ");
	while (isAlertEmpty() && $term !== false){
		//phpAlert("going to insert term");
		$termID = insertTerm ( $mysqli , $term );
		//phpAlert("term inserted");
		if(isAlertEmpty() && $termID == 0) $termID = getTermID ( $mysqli , $term );
		//phpAlert("passou if1");
		if(!$stmt->execute()){
			echo $term . " " . $termID . " ";
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Error inserting term " .$term. " in the database");			
		}
		//phpAlert("passou if2");
		$term = strtok (" ");
	}
	$stmt->close();
	return ;	
}

//Inserting documents on Database
function insertDocuments ( $mysqli , $lpID ) {
	
	$query = 	"INSERT INTO `tbl_document`(`document_process`, `document_text`, 
						`document_name`,`document_size`) VALUES (?,?,?,?)";
	$stmt = $mysqli->prepare($query);	
	$stmt->bind_param("issi",$lpID,$file_content,$file_name,$file_size);
	$counts = array();
	
	//Uploading documents
	foreach($_FILES['lpDocs']['tmp_name'] as $index => $tmp_name){
		$file_name 		= 	($_FILES['lpDocs']['name'][$index]);
		$file_tmpName 	=	$_FILES['lpDocs']['tmp_name'][$index];
		$file_size 		= 	$_FILES['lpDocs']['size'][$index];
		$file_fp      	= 	fopen($file_tmpName, 'r');
		$file_text		=	fread($file_fp, filesize($file_tmpName)+1);
		$file_content	=	utf8_encode(addslashes($file_text));
		fclose($file_fp);
		if(!get_magic_quotes_gpc()) {  $file_name = utf8_encode(addslashes($file_name)); }
		if(!$stmt->execute()){
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Error inserting new document to database: " .$file_name);
			break;
		}
		
		$docID = $stmt->insert_id;
		
		if(isAlertEmpty())	insertRankedLabels ( $mysqli, $docID);
		//phpAlert("going to insertDocumentTerms");
		$alg = $_POST['lpSuggestionAlgorithm'];
		if( ($alg == 'transductive' || $alg == 'testMode') && isAlertEmpty()){
			$idiom = $_POST['lpIdiom'];
			$file_text = preProcess ($file_text, $idiom);
			insertDocumentTerms ($mysqli, $file_text, $docID);
		}
		//phpAlert("done insertDocumentTerms");
	}
	
	$stmt->close();	
}

if ( isFormValid($mysqli) ) {
	$mysqli->autocommit(FALSE);
	$mysqli->commit();
	
	$lpID = insertLP ($mysqli);
	
	if( isAlertEmpty() && isset($_POST['lpLabels']) )	
		insertLabels ( $mysqli , $lpID );	
	//phpAlert("gonna insert documents");
	if(isAlertEmpty()){
		insertDocuments ( $mysqli , $lpID );
	}
	//phpAlert("inserted documents");
	$mysqli->commit();
	
	/*/If this process uses the PMI algorithm to predict polarity, calculate PMIs and word frequence
	if($_POST['lpSuggestionAlgorithm'] == 'PMIBased'){	
		findPatterns($_POST['language'], 'maxent', $_POST['translator'], $lpID, $mysqli, $_POST['min_frequency']);
	}
	//If this process uses the frequence-based algorithm to predict aspects, calculate word frequence
	else if($_POST['lpAspectSuggestionAlgorithm'] == 'frequenceBased'){
		//phpAlert("RotuLabic is going to calculate word frequences. This might take a little while.");
		calculateFrequency($_POST['language'], 'maxent', $_POST['translator'], $lpID, $mysqli, $_POST['min_frequency']);
	}*/
	
	//If this process uses the frequence-based algorithm to predict aspects, calculate word frequence
	if($_POST['lpAspectSuggestionAlgorithm'] == 'frequenceBased'){
		//phpAlert("RotuLabic is going to calculate word frequences. This might take a little while.");
		$result = calculateFrequency($_POST['language'], 'maxent', $_POST['translator'], $lpID, $mysqli, $_POST['min_frequency']);

		//If this process uses the PMI algorithm to predict polarity, calculate PMIs
		if($_POST['lpSuggestionAlgorithm'] == 'PMIBased'){	
			findPatterns($_POST['language'],  $_POST['translator'], $result,$lpID, $mysqli);
		}
	}
	
	$mysqli->commit();
	$mysqli->autocommit(TRUE);
	
	if (isAlertEmpty()) {
		header('Location: ./taggers.php?lpID='.$lpID);
		exit();
	}
	
} 
?>