<?php
include_once 'db_connect.php';
include_once 'includes/functions.php';
sec_session_start();
unsetLabelingProcessData();

/**
* Connects to database and retrieves the users that are not
* already taggers of the current labeling process. Then, adds them to
* datalist (as options)
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Current Labeling Process' ID	
*/
function printUsers($mysqli,$lpID) {
	$query = "	SELECT user_name 
					FROM tbl_user 
					WHERE user_id NOT IN ( 
						SELECT process_tagger_tagger
						FROM tbl_labeling_process_tagger
						WHERE process_tagger_process = ? )
					ORDER BY user_name ASC";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID );
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			$user = $data[0];
			echo '<option value="' .$user.'" data-tg="'.$user.'">' ;
		}
	}else{
		setAlert("Failed to retrieve users data");
	}
	$stmt->close();
}

function ProcessName($mysqli,$lpID) {
	$query = "SELECT process_name FROM tbl_labeling_process WHERE process_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID );
	if($stmt->execute()){
		$result = $stmt->get_result();
		return mysqli_fetch_row($result)[0];
	}else{
		setAlert("Failed to retrieve process name.");
		return 'UNKNOWN';
	}
	$stmt->close();	
}


/**
* Connects to database and retrieves the users that are
* already taggers of the current labeling process. Then, adds them to
* table of taggers (as options)
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Current Labeling Process' ID	
*/
function printCurrentTaggers($mysqli,$lpID) {
	$query = "	SELECT user_name 
					FROM tbl_user JOIN tbl_labeling_process_tagger
					ON process_tagger_tagger = user_id
					WHERE process_tagger_process = ? ";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID );
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			$tg = $data[0];
			echo 	'<tr class="info" ><td colspan="3" >'.$tg.'</td></tr>';
		}
	}else{
		setAlert("Failed to retrieve taggers data");
	}
	$stmt->close();
}

/**
* Connects to database and retrieves documents that are linked to
* this labeling process 
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Current Labeling Process' ID	
* @return ($docs) 	- Array with the documents' ID for this labeling process
*/
function getDocuments ($mysqli , $lpID) {
	$docs = array();
	$query = "SELECT document_id FROM tbl_document WHERE document_process = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($docs, $data[0]);
		}
	}else {
		$mysqli->rollback();
		setAlert("Failed to retrieve documents");	
	}
	$stmt->close();
	return $docs;
}

/**
* Connects to database and sets the chosen users to be taggers
* of the current labeling process  
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($docs) 	- Array with the documents' ID for this labeling process
* @param ($lpID) 	- Current Labeling Process' ID	
*/
function insertTaggers ($mysqli, $docs, $lpID){
	$tgID = 0;
	
	//Query to get tagger ID
	$query1 = "SELECT user_id FROM tbl_user WHERE user_name = ? LIMIT 1" ;
	$stmt1 = $mysqli->prepare($query1);
	$stmt1->bind_param('s', $tg);
	
	//Query to link the tagger to the current labeling process
	$query2 = "INSERT INTO tbl_labeling_process_tagger
			(`process_tagger_process`,`process_tagger_tagger`) VALUES (?,?)";
	$stmt2 = $mysqli->prepare($query2);
	$stmt2->bind_param("ii",$lpID,$tgID);
	
	//Query to link the tagger to the documents of the current labeling process
	$query3 = "INSERT INTO `tbl_document_labeling`
				(`labeling_document`,`labeling_tagger`) VALUES (?,?)";
	$stmt3 = $mysqli->prepare($query3);			
	$stmt3->bind_param("ii",$docID,$tgID);
	
	foreach ($_POST['lpTaggers'] as $tg){	
		if($stmt1->execute()){
			$stmt1->store_result();
			$stmt1->bind_result($tgID);
			$stmt1->fetch();
			if(!$stmt2->execute()){
				setAlert($stmt2->error);
				//setAlert("Erro ao inserir rotulador " .$tg. " no banco de dados");
				$mysqli->rollback();
				$stmt1->close();$stmt2->close();$stmt3->close();
				return;
			}
		}else{
			setAlert("Failed to retrieve id of the user " .$tg);
			$mysqli->rollback();
			$stmt1->close();$stmt2->close();$stmt3->close();
			return;			
		}
			
		foreach ($docs as $docID){
			if(!$stmt3->execute()){
				setAlert("Failed to insert tagger " .$tg. " in the database");
				$mysqli->rollback();
				$stmt1->close();$stmt2->close();$stmt3->close();
				return;
			}
		}	
	}
	$stmt1->close();$stmt2->close();$stmt3->close();
}

/**
* Connects to database and sets the labeling process' status to 'in_progress'
*
* @param ($mysqli) 	- mysqli object (MYSQL database connection) 	
* @param ($lpID) 	- Current Labeling Process' ID	
*/
function updateLPStatus ($mysqli, $lpID){
	//Updating labeling progress status --> in_progress
	$query = "	UPDATE tbl_labeling_process 
						SET process_status = 'in_progress' 
						WHERE process_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$lpID );
	if(!$stmt->execute()){
		$mysqli->rollback();
		setAlert("Failed to update labeling process status");			
	}
	$stmt->close();
}

$lpIDstr = "";
$lpID = 0; 

if (isset($_GET['lpID'])){		//Checks if the labeling process is set
	$lpID = $_GET['lpID'];
	$lpIDstr = "?lpID=" . $lpID;
}else{							//Return to index otherwise
	header('Location: ./index.php');
	exit();
}


if ((login_check($mysqli) == true) && 
	($_SESSION['user_role'] == 'processAdmin') &&
	isset($_POST['lpTaggers'])) {
	//Form was submitted 
	//Updates database
	
	$mysqli->autocommit(FALSE);
	$mysqli->commit();
	
	$docs = getDocuments ($mysqli , $lpID);
	
	if(isAlertEmpty())	insertTaggers ($mysqli, $docs, $lpID);
	if(isAlertEmpty())	updateLPStatus ($mysqli, $lpID);

	$mysqli->commit();
	$mysqli->autocommit(TRUE);
	if(isAlertEmpty()) 
		header('Location: ./index.php');
}



