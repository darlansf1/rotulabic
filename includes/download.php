<?php
include_once 'db_connect.php';
include_once 'functions.php';
include_once 'lpbhn.php';
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
		
		$fileName = adjustFileName(utf8_decode($doc[1]));
		if(!file_exists($fileName)){
			file_put_contents($fileName, utf8_decode($doc[2]));	//Creating and adding content to the file
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
		$fileName = adjustFileName( $doc[0] . "_" . utf8_decode($doc[1] ));
		file_put_contents($fileName, utf8_decode($doc[2]));	//Creating and adding content to the file
		$zip->addFile($fileName);				//Inserting file on zip archive
    }
	
    $zip->close();
	download($zipFileName);		//Adjust header and download zip file
    exit;

}

function getAnnotationsPerDocSpecial($mysqli, $include_sentiment_words){
	$documents = array();
	$query = "";

	$info = "";
	
	$word = array(); $start = array(); $polarity = array(); $document = array(); $type = array(); $id = array();
	
	$query = "SELECT aspect_aspect, aspect_start, aspect_polarity, aspect_doc, aspect_type, aspect_number
					FROM tbl_aspect 
					WHERE aspect_lp = ? ORDER BY aspect_doc"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_POST['lpID'] );
	
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			array_push($word, $data[0]);
			array_push($start, $data[1]);
			array_push($polarity, $data[2]);
			array_push($document, $data[3]);
			array_push($type, $data[4]);
			array_push($id, $data[5]);
		}
	}else{
		setAlert("Erro ao acessar banco de dados");
	}
	$stmt->close();
	
	//Grouping by document
	$docAspects = array(); $docStarts = array(); $docPolarities = array(); $docIds = array(); $k = -1; $docAspectIds = array(); $docTypes = array();
	for($i = 0; $i < count($word); $i++){
		if($i == 0 || ($i != 0 && $document[$i-1] != $document[$i])){
			array_push($docAspects, array());
			array_push($docStarts, array());
			array_push($docPolarities, array());
			array_push($docAspectIds, array());
			array_push($docIds, $document[$i]);
			array_push($docTypes, array());
			
			$k++;
		}
		
		//if($type[$i] == "normal")
		array_push($docAspects[$k], $word[$i]);
		//else
			//array_push($docAspects[$k], $type[$i]);
		array_push($docAspectIds[$k], $id[$i]);
		array_push($docStarts[$k], $start[$i]);
		array_push($docPolarities[$k], $polarity[$i]);
		array_push($docTypes[$k], $type[$i]);
	}
	
	//arrays used to keep track of the aspects with the min agreement rate per doc
	$agreedCountPol = array();
	
	//counts docs that have all aspects with the min agreement rate
	$agreedDocPol = 0;
	
	$agreementRate = 0;
	$minAgreementRate = $_POST['agreementRate'];
	$aspectsPerDoc = array();
	$docName;
	$docText;
	
	//sentence polarity vector
	$sentence_polarities = array();
	array_push($sentence_polarities, array());
	array_push($sentence_polarities[0], "class.txt");
	$classes_str = "";
	
	$docsWithNoAspects = array();
	$query = "SELECT document_id, document_name
					FROM tbl_document
					WHERE document_process = ?"; 
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i',$_POST['lpID']);
		
	if($stmt->execute()){
		$result = $stmt->get_result();
		while($data = mysqli_fetch_row($result)){
			if((array_search($data[0], $docIds)) === false)
				array_push($docsWithNoAspects, $data[1]);
		}
	}else{
		setAlert("Erro ao recuperar dados do banco de dados");
	}
	$stmt->close();
	
	for($i = 0; $i < count($docIds); $i++){

		$query = "SELECT document_name, document_text
					FROM tbl_document
					WHERE document_id = ?"; 
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('i',$docIds[$i] );
		
		if($stmt->execute()){
			$stmt->bind_result($docName, $docText);
			$stmt->fetch();
			$docName =utf8_decode($docName);
		}else{
			setAlert("Erro ao recuperar dados do banco de dados");
		}
		$stmt->close();
	
		$agreedPol = 0;
		array_push($documents, array());
		array_push($documents[$i], $docName);
		array_push($documents[$i], "");
		
		//arrays to keep track of the verified aspects
		$countedPol = array();
		array_push($agreedCountPol, 0);
		array_push($aspectsPerDoc, 0);
		$sentimentWords = array();
		
		/* sentence polarity calculation*/
		$docText = removeSpecialChars(stripcslashes(stripslashes(utf8_decode($docText))));
		$startIndex = 0;
		$docEnd = strlen($docText)-1;
		
		$index1 = strpos($docText, ".", $startIndex);
		if($index1 === false) $index1 = $docEnd;
		
		$index2 = strpos($docText, "?", $startIndex);
		if($index2 === false) $index2 = $docEnd;
		
		$index3 = strpos($docText, "!", $startIndex);
		if($index3 === false) $index3 = $docEnd;
		
		$endIndex = min($index1, $index2, $index3);
		$count = 0;
		while($startIndex < $docEnd){
			$polarity_count = array("POSITIVE"=>0, "NEGATIVE"=>0, "NEUTRAL"=>0);
			for($m = 0; $m < count($docAspects[$i]); $m++){
				if($docStarts[$i][$m] >= $startIndex && $docStarts[$i][$m] < $endIndex){
					$polarity_count[strtoupper($docPolarities[$i][$m])]++;
				}
			}
			if($polarity_count["POSITIVE"] == $polarity_count["NEGATIVE"])
				$s_polarity = "NEUTRAL";
			else
				$s_polarity = array_keys($polarity_count, max($polarity_count))[0];
			$startIndex = $endIndex+1;
			
			$index1 = strpos($docText, ".", $startIndex);
			if($index1 === false) $index1 = $docEnd;
			
			$index2 = strpos($docText, "?", $startIndex);
			if($index2 === false) $index2 = $docEnd;
			
			$index3 = strpos($docText, "!", $startIndex);
			if($index3 === false) $index3 = $docEnd;
			
			$endIndex = min($index1, $index2, $index3);
			$classes_str = $classes_str.substr($docName, 0, strlen($docName)-4)."_transaction".$count.".txt ".$s_polarity."\n";
			$count++;
		}

		/*end of sentence polarity calculation*/
		
		for($j = 0; $j < count($docAspects[$i]); $j++){
			$k = $j;
			
			$currentWord; $currentStart; 
			$beginning = $k;
			
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
					$currentWord = $docAspects[$i][$k];
					$currentStart = $docStarts[$i][$k];
					$currentPolarity = $docPolarities[$i][$k];
					$currentType = $docTypes[$i][$k];
					$agreementRate = 0;
					$sentimentWords = array();
				}
				
				if(count($countedPol) == $k)
					array_push($countedPol, 0);
				if($docAspects[$i][$k] == $currentWord && $docStarts[$i][$k] == $currentStart 
														&& $docPolarities[$i][$k] == $currentPolarity){
					$agreementRate++;
					$countedPol[$k] = 1;
					if($include_sentiment_words == 1){
						$query = "SELECT *
										FROM tbl_sentiment_indication 
										WHERE si_aspect_number = ?"; 
						$stmt = $mysqli->prepare($query);
						$stmt->bind_param('i',$docAspectIds[$i][$k]);
						
						if($stmt->execute()){
							$result = $stmt->get_result();
							while($data = mysqli_fetch_row($result)){
								$l = 0;
								for(;$l < count($sentimentWords); $l++){
									if($data[0] == $sentimentWords[$l][0] && $data[2] == $sentimentWords[$l][2])
										break;
								} 
								if($l == count($sentimentWords))
									array_push($sentimentWords, $data);
							}
						}else{
							setAlert("Erro ao acessar banco de dados");
						}
						$stmt->close();
					}
				}
			    
				$k++;
				if($k == count($docAspects[$i]) && $agreementRate >= $minAgreementRate){
					$currentPolarity =($currentPolarity);
					$currentEnd = $currentStart+strlen($currentWord);
					if($currentType != 'normal')
						$currentEnd = $currentStart;
					
					$documents[$i][1] = $documents[$i][1]."\r\n\t<aspectTerm term=\"".$currentWord."\" polarity=\"".$currentPolarity."\" from=\"".$currentStart."\" to=\"".$currentEnd."\"/>";
					if($include_sentiment_words == 1){
						for($l = 0; $l < count($sentimentWords); $l++){
							$sentimentTerm =($sentimentWords[$l][0]);
							$sentimentStart = $sentimentWords[$l][2];
							$sentimentEnd = $sentimentWords[$l][3];
							$documents[$i][1] = $documents[$i][1]."\r\n\t<sentimentTerm term=\"".$sentimentTerm."\" from=\"".$sentimentStart."\" to=\"".$sentimentEnd."\"/>";
						}
					}
					$agreedCountPol[$i]++;
				}
			}
		}

		if($agreedCountPol[$i] == $aspectsPerDoc[$i])
			$agreedDocPol++;
		
		//phpAlert($documents[$i][1]);
		$documents[$i][1] = "<aspectTerms>".$documents[$i][1]."\r\n</aspectTerms>";
		//phpAlert("aspects per doc (".$i."): ".$aspectsPerDoc[$i]);
	}
	
	foreach($docsWithNoAspects as $doc){
		$index = count($documents);
		array_push($documents, array());
		array_push($documents[$index], $doc);
		array_push($documents[$index], "<aspectTerms>\r\n</aspectTerms>");
	}
	
	array_push($sentence_polarities[0], $classes_str);
	array_push($documents, $sentence_polarities[0]);
	return $documents;
	///return $sentence_polarities;
}


function downloadWithSentiment($mysqli){
    $zip = new ZipArchive();
	$zipFileName = "annotationsAndSentimentIndications.zip";
	
    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE )!== TRUE) {
		setAlert("Erro ao criar o arquivo zip");
		return;
    }
    
	$docs = getAnnotationsPerDocSpecial($mysqli, 1);
	
    foreach($docs as $doc) {
		//Adding text files(labeled documents) to zip archive
		$fileName = adjustFileName( $doc[0]);
		$fileName = substr($fileName, 0, strlen($fileName)-4).".xml";
		file_put_contents($fileName, $doc[1]);	//Creating and adding content to the file
		$zip->addFile($fileName);				//Inserting file on zip archive
    }
	
    $zip->close();
	download($zipFileName);		//Adjust header and download zip file
    exit;

}

function downloadWithoutSentiment($mysqli){
    $zip = new ZipArchive();
	$zipFileName = "anottations.zip";
	
    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE )!== TRUE) {
		setAlert("Erro ao criar o arquivo zip");
		return;
    }
    
	$docs = getAnnotationsPerDocSpecial($mysqli, 0);
	
    foreach($docs as $doc) {
		//Adding text files(labeled documents) to zip archive
		$fileName = adjustFileName( $doc[0]);
		$fileName = substr($fileName, 0, strlen($fileName)-4).".xml";
		file_put_contents($fileName, $doc[1]);	//Creating and adding content to the file
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
	(!empty($_POST['action'])) &&
	(!empty($_POST['cur_lpLabelingType']))){
	
	if($_POST['cur_lpLabelingType'] == 'normal'){
		if($_POST['action']=='firstDownload') firstDownload($mysqli);
		else if ($_POST['action']=='secondDownload') secondDownload($mysqli);
	}else{
		if($_POST['action']=='firstDownload')
			downloadWithSentiment($mysqli);
		else
			downloadWithoutSentiment($mysqli);
	}
	
}else {
	setAlert("Erro ao realizar o download");
	header('Location: ./index.php');
	exit();
}