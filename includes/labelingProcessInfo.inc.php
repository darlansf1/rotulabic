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
								<td>Número de rotuladores em acordo para aceitar sugestão</td>
								<td>". $rate."</td>
							</tr>";
	}else{
		setAlert("Houve erro ao recuperar dados do processo de rotulação");
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
								<td>Idioma dos documentos</td>
								<td>". getPortugueseIdiom($idiom)."</td>
							</tr>";
		echo				"<tr>
								<td>Taxa para passo 'reset' do algoritmo transdutivo</td>
								<td>". $rate."</td>
							</tr>";

	}else{
		setAlert("Houve erro ao recuperar dados do processo de rotulação");
	}
	$stmt->close();
}

function printLPInfo ( $mysqli, $lpInfo ) {
	$lpID = $lpInfo['process_id'];
	$_SESSION['cur_lpAccRate']	= $lpInfo['process_label_acceptance_rate'];
	
	$isPreset  = ($lpInfo['process_type']=="preSet");
	
	$alg = $lpInfo['process_suggestion_algorithm'];
	$isTransductive = ($alg == 'transductive' || $alg == 'testMode');
	
	echo "	<div class='row' align='center'  >
				<div class='col-md-6' style= 'border:1px solid #ddd;padding:20px;'>
					<h2>Dados do Processo de Rotulação </h2>
					<div class='panel panel-primary'>
						<div class='panel-heading text-center'>
							<h1 class='panel-title'>Processo de Rotulação: ".$lpInfo['process_name']."</h1>
						</div>
					
						<table class='table table-hover table-bordered  table-condensed' >
							<tr>
								<td>Taxa para rótulo final</td>
								<td>".$lpInfo['process_label_acceptance_rate']."</td>
							</tr>
							<tr>
								<td>Status</td>
								<td>".getPortugueseStatus($lpInfo['process_status'])."</td>
							</tr>
							<tr>
								<td>Multilabel</td>
								<td>". ($lpInfo['process_multilabel']==1?"Sim":"Não") ."</td>
							</tr>
							<tr>
								<td>Rotuladores podem sugerir rótulos</td>
								<td>". ($isPreset?"Não":"Sim") ."</td>
							</tr>";
					if(!$isPreset) printPostSetData($mysqli,$lpID);	
					echo	"<tr>
								<td>Algoritmo para sugestão de rótulos</td>
								<td>". getPortugueseAlgorithm($lpInfo['process_suggestion_algorithm'])."</td>
							</tr>";
					
					if($isTransductive) printTransductiveData($mysqli,$lpID);			
	
	echo	"			</table>
					</div>
					<div class = 'row'>	
						<div class='col-md-8 col-centered'>
							<form action='taggers.php' method='get'>
								<input type='hidden' name='lpID' value=".$lpID. "/>
								<input type='submit' class='btn btn-default btn-block'  value='Adicionar Rotuladores'/>
							</form>
						</div>
					</div>
				</div>
			</div>";
	
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
		setAlert("Erro ao recuperar dados do banco de dados");
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