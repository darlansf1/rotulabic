<?php
include_once 'db_connect.php';
include_once 'functions.php';
sec_session_start();

function getLabeledDocuments($mysqli){
	$documents = array();
	$query = "";
	if(isMultiLabel($mysqli)){
		$query = "	SELECT label_label, document_name, document_text
						FROM tbl_chosen_label JOIN tbl_document 
							ON (document_id=label_document AND document_process= ? )
						GROUP BY label_document, label_label
						HAVING COUNT(*) >= ?
						ORDER BY label_label";
	}else{
		$query = "	SELECT label_label, document_name, document_text FROM ( 
						SELECT label_label, document_name , document_text, COUNT(*) as cnt 
							FROM tbl_chosen_label JOIN tbl_document 
								ON (document_id=label_document AND document_process= ? ) 
							GROUP BY label_document, label_label HAVING cnt >= ? 
							ORDER BY document_name, cnt desc) slc 
					GROUP BY document_name";
	}
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('ii',$_POST['lpID'],$_POST['agreementRate']);
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			$documents[] = $data;
		}
	}else{
		setAlert("Erro ao recuperar documentos do banco de dados");
	}
	$stmt->close();
	return $documents;
}

function adjustFileName($fileName){
	$fileName = iconv("UTF-8","CP860//IGNORE", $fileName); //Char-set problem -- converting to Portuguese language
	$fileName = stripslashes($fileName);	//Removing slashes, which causes problem when we create the file
	return $fileName;
}

function isMultiLabel($mysqli){
	$asw = 0;
	$query = "SELECT process_multilabel 
					FROM tbl_labeling_process 
					WHERE process_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_POST['lpID']);
	
	if($stmt->execute()){
		$stmt->bind_result($asw);
		$stmt->fetch();
	}else{
		setAlert("Erro ao recuperar dados do processo de rotulação");
	}
	
	$stmt->close();
	return $asw == 1;
}

function download($zipFileName){
	$zipped_size = filesize($zipFileName);
	header("Content-Description: File Transfer");
	header("Content-type: application/zip"); 
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=$zipFileName");
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header("Content-Length:". " $zipped_size");
	ob_clean();
	flush();
	readfile("$zipFileName");			
	array_map('unlink', glob("*.txt")); //Deleting documents on server
	unlink("$zipFileName"); 			//Deleting zip file on server
}

function firstDownload($mysqli){
    $zip = new ZipArchive();
	$zipFileName = "labeledDocs.zip";
    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE )!== TRUE) {
		setAlert("Erro ao criar o arquivo zip");
		return;
    }
	
	$docs = getLabeledDocuments($mysqli);
	$currentDir = $dir = "";
	
    foreach($docs as $doc) {
		if($currentDir != $doc[0]){		//Creating directory on zip archive
			$currentDir = $doc[0];
			$dir = preg_replace('/[\/]{2,}/', '/', $currentDir."/");
			$zip->addEmptyDir($dir);
		}
		
		$fileName = adjustFileName($doc[1]);
		if(!file_exists($fileName)){
			file_put_contents($fileName, $doc[2]);	//Creating and adding content to the file
		}
		$zip->addFile($fileName,$dir.$fileName);	//Inserting file on zip archive(on the corresponding directory)
    }
	
    $zip->close();
	download($zipFileName);		//Adjust header and download zip file
    exit;
}

function secondDownload($mysqli){
    $zip = new ZipArchive();
	$zipFileName = "labeledDocs.zip";
	
    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE )!== TRUE) {
		setAlert("Erro ao criar o arquivo zip");
		return;
    }
    
	$docs = getLabeledDocuments($mysqli);
    foreach($docs as $doc) {
		//Adding text files(labeled documents) to zip archive
		$fileName = adjustFileName( $doc[0] . "_" . $doc[1] );
		file_put_contents($fileName, $doc[2]);	//Creating and adding content to the file
		$zip->addFile($fileName);				//Inserting file on zip archive
    }
	
    $zip->close();
	download($zipFileName);		//Adjust header and download zip file
    exit;

}


if(	(login_check($mysqli)) && 
	($_SESSION['user_role'] == 'processAdmin') &&
	(!empty($_POST['lpID'])) &&
	(!empty($_POST['agreementRate'])) &&
	(!empty($_POST['action']))){
	
	if($_POST['action']=='firstDownload') firstDownload($mysqli);
	else if ($_POST['action']=='secondDownload') secondDownload($mysqli);
	
}else {
	setAlert("Erro ao realizar o download");
	header('Location: ./index.php');
	exit();
}