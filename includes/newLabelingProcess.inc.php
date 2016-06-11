<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';

sec_session_start(); 

function isFormValid($mysqli) {
	if(
		(login_check($mysqli) == true) && 
		($_SESSION['user_role'] == 'processAdmin') &&  
		isset($_SESSION['user_id']) &&
		isset($_POST['lpName']) && 
		isset($_POST['lpSuggestionAcceptanceRate']) && 
		isset($_POST['lpLabelAcceptanceRate']) && 
		isset($_POST['lpType']) && 
		isset($_POST['lpSuggestionAlgorithm'])
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
	$lpLabelAcceptanceRate 		= $_POST['lpLabelAcceptanceRate'];
	$lpMultilabel				= $_POST['lpMultiLabel'] == 'true' ? 1 : 0 ;
	$lpType 					= $_POST['lpType'];
	$lpInst_content 			= getLPInstructionContent ();	//Instructions file
	$lpSuggestionAlgorithm 		= $_POST['lpSuggestionAlgorithm'];
	
	$query = "INSERT INTO tbl_labeling_process
		(`process_name`, `process_admin`,`process_label_acceptance_rate`, `process_multilabel` , 
		`process_type`, `process_instructions` ,`process_suggestion_algorithm`) 
		VALUES ( ? , ? , ? , ? , ? , ? , ? )";
	$stmt = $mysqli->prepare($query);
	
	$stmt->bind_param('siiisss',$lpName,$uID,$lpLabelAcceptanceRate,
			$lpMultilabel,$lpType,$lpInst_content,$lpSuggestionAlgorithm);
			
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
	$stmt->bind_param("s",$lbl);

	$query2 = 	"INSERT INTO `tbl_labeling_process_label_option`
					(`lpLabelOpt_lp` , `lpLabelOpt_label` ) VALUES (?,?)";
	$stmt2 = $mysqli->prepare($query2);
	$stmt2->bind_param("is",$lpID,$lbl);
	
	foreach ($_POST['lpLabels'] as $lbl){
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
	$stmt->bind_param('s',$term);
	if(!$stmt->execute()){
		echo $mysqli->error;
		$stmt->close();
		$mysqli->rollback();   
		setAlert("Erro ao inserir o termo " .$term. " no banco de dados");
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
	$stmt->bind_param('s', $term );
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
	
	$term = strtok ($text, " ");
	while (isAlertEmpty() && $term !== false){
		$termID = insertTerm ( $mysqli , $term );
		if(isAlertEmpty() && $termID == 0) $termID = getTermID ( $mysqli , $term );
		if(!$stmt->execute()){
			echo $term . " " . $termID . " ";
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Erro ao inserir o termo " .$term. " no banco de dados");			
		}
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
	
	//Uploading documents
	foreach($_FILES['lpDocs']['tmp_name'] as $index => $tmp_name){
		$file_name 		= 	$_FILES['lpDocs']['name'][$index];
		$file_tmpName 	=	$_FILES['lpDocs']['tmp_name'][$index];
		$file_size 		= 	$_FILES['lpDocs']['size'][$index];
		$file_fp      	= 	fopen($file_tmpName, 'r');
		$file_text		=	fread($file_fp, filesize($file_tmpName));
		$file_content	=	addslashes($file_text);
		fclose($file_fp);
		if(!get_magic_quotes_gpc()) {  $file_name = addslashes($file_name); }
		
		if(!$stmt->execute()){
			echo $mysqli->error;
			$mysqli->rollback();
			setAlert("Erro ao inserir novo documento no banco de dados: " .$file_name);
			break;
		}
		
		$docID = $stmt->insert_id;
		if(isAlertEmpty())	insertRankedLabels ( $mysqli, $docID);
		
		$alg = $_POST['lpSuggestionAlgorithm'];
		if( ($alg == 'transductive' || $alg == 'testMode') && isAlertEmpty()){
			$idiom = $_POST['lpIdiom'];
			$file_text = preProcess ($file_text, $idiom);
			insertDocumentTerms ($mysqli, $file_text, $docID);
		}
	}
	$stmt->close();	
}

if ( isFormValid($mysqli) ) {
    
	$mysqli->autocommit(FALSE);
	$mysqli->commit();
	
	$lpID = insertLP ($mysqli);
	
	if( isAlertEmpty() && isset($_POST['lpLabels']) )	
		insertLabels ( $mysqli , $lpID );	
	
	if(isAlertEmpty())
		insertDocuments ( $mysqli , $lpID );
	
	$mysqli->commit();
	$mysqli->autocommit(TRUE);
	
	if (isAlertEmpty()) {
		header('Location: ./taggers.php?lpID='.$lpID);
		exit();
	}
	
} 
?>