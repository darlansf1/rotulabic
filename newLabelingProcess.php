<?php include_once 'includes/newLabelingProcess.inc.php'; ?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia">
		<title>Criar Processo de Rotulação</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />	
    
		<script type="text/Javascript">
			$(document).ready(function(){
				//A new label was suggested
				$("#btnAddLabel").click(function(){
					var append = true;
					$('input[name^="lpLabels"]').each(function() {	
						//Checking if the value was already input
						if( $(this).val() === $("#txtLabel").val() ){
							append = false;
							return false;
						}
					});
					if(append)appendLabel();
					else alert("Rótulo duplicado !");
				});
				
				//If the type of labelling process is 'preset', then
				//no suggestions are accepted. Thus, the minimum acceptance rate
				//is unset
				$("input[name='lpType']").change(function() {
					lpTypeChange();
				});
				
				//If the suggestion algorithm is transductive or in the test mode,
				//then the labelling process type should be preset
				$("#lpSuggestionAlgorithm").change(function() {
					$("#postSet").prop("disabled","");
					var algorithm = $("#lpSuggestionAlgorithm option:selected").val();
					if (algorithm == 'transductive' || algorithm == 'testMode'){
						$("#postSet").prop("disabled","disabled");
						$("#preSet").prop('checked',true);
						showTransductiveInput();
					}else{
						hideTransductiveInput();
					}
					lpTypeChange();
				});
			});
			
			function showTransductiveInput(){
				//Showing idiom row
				$("#idiomRow").css("display","");
				$("#optionPT").prop("selected",true);
				$("#optionUN").prop("selected",false);
				
				//Showing reset rate row
				$("#resetRateRow").css("display","");
				$("#lpTransductiveResetRate").val("1");
			}
			
			function hideTransductiveInput(){
				//Hiding idiom row
				$("#idiomRow").css("display","none");
				$("#optionPT").prop("selected",false);
				$("#optionUN").prop("selected",true);
				
				//Hiding reset rate row
				$("#resetRateRow").css("display","none");
				$("#lpTransductiveResetRate").val("");
			}
			
			function lpTypeChange(){
				if ($("input[name='lpType']:checked").val() == 'preSet'){
					$("#lpSuggestionAcceptanceRate").val("");
					$("#lpSuggestionAcceptanceRate").prop("readonly",true);
					$("#sugAccRateRow").css("display","none");
					
				}else{
					$("#lpSuggestionAcceptanceRate").prop("readonly",false);
					$("#lpSuggestionAcceptanceRate").val("1");
					$("#sugAccRateRow").css("display","");
				}
			}
			
			function appendLabel(){
				//Append a new row to the table of labels
				$("#tblLabelList").append(
					"<tr>" + 
						"<td  style='padding-top:3px;'><input type='text' name='lpLabels[]' readonly value='" + $("#txtLabel").val() + "'></td>" +
						"<td onClick='removeRow($(this))' style='padding-left:10px;'><img src='images/ic_delete.png' ></td>" + 
					"</tr>"
				);
				$("#txtLabel").val("");
			};
			function removeRow(delete_icon){
				delete_icon.closest('tr').remove(); //Removing the row
			};
			function validadeForm(){
				var lpType = $('input:radio[name=lpType]:checked').val();
				if(	($("#lpName").val()=="") || 
					( ($("#lpSuggestionAcceptanceRate").val()=="") && lpType === "postSet" )){
					alert("Todos os campos devem ser preenchidos");
					return false;
				}
				
				//Checking if the instruction file was sent
				if(($("#lpInstructions").val()=="")){
					alert("Você deve selecionar o guia para este processo de rotulação\n(arquivo em formato .txt)")
					return false;
				}
				
				//Checking if the documents(to be labelled) were sent
				if(($("#lpDocs").val()=="")){
					alert("Você deve selecionar pelo menos um documento a ser rotulado")
					return false;
				}
				
				//Checking if there are at least two files in case the labelling process is 'preSet'
				if( (lpType === "preSet") && ($('input[name^="lpLabels"]').size() < 2) ){
					alert("Deve haver pelo menos duas opções de rótulos caso estes sejam fixados");
					return false;
				}
				//If everything is all right, then we submit the form 
				$("#newLPForm").submit(); 
				return true;
			};
		</script>
        
    </head>
    <body>
		<?php showAlert(); ?>
		<?php if ((login_check($mysqli) == true) && ($_SESSION['user_role'] == 'processAdmin')  ) : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">Sistema de Apoio à Rotulação Manual de Textos</a>
						</div>
						<p class="navbar-text">
							--  Olá, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
								<li><a href="profile.php">Perfil</a></li>
								<?php if (($_SESSION['user_role'] == 'processAdmin')  ){
										echo 	'<li><a href="helpAdmin.php">Manual do administrador</a></li>
												<li><a href="help.php">Manual do usuário</a></li>';
									}else{
										echo '<li><a href="help.php">Manual</a></li>';
									}
								?>
								<li><a href="#about">Sobre</a></li>
								<li><a href="includes/logout.php">Sair</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
			
			<h1 align="center">Criar Novo Processo de Rotulação</h1>
			<div class="container" style= "border:1px solid #ddd;padding-top:30px;">
				<form 	action = "<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
						id="newLPForm" 
						name="newLPForm" 
						method="post" 
						enctype="multipart/form-data"
						class = "form-horizontal "> 
						
						<div class="form-group">
							<label for="lpName" class="col-sm-6 control-label">Nome do Processo de Rotulação</label>
							<div class="col-sm-3">
								<input required type="text" name="lpName" id="lpName" class="form-control input-sm">
							</div>
						</div>
						
						<div class="form-group">
							<label for="lpInstructions" class="col-sm-6 control-label">
								<abbr title="Apenas formato .txt">
									Anexar instruções para os rotuladores
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input required type="file" id="lpInstructions" name="lpInstructions" accept=".txt" >
							</div>
						</div>	

						<div class="form-group">
							<label for="lpDocs" class="col-sm-6 control-label">
								<abbr title="Apenas formato .txt">
									Anexar documentos a serem rotulados
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input required multiple type="file" id="lpDocs" name="lpDocs[]" accept=".txt" >
							</div>
						</div>						
						
						<div class="form-group">
							<label for="lpMultiLabel" class="col-sm-6 control-label">
								Multirótulo
							</label>
							<div class="col-sm-2">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="lpMultiLabel" id="multiLabel" value="true"> Sim
								</label>
								<label class="radio-inline">
									<input type="radio" name="lpMultiLabel" id="monoLabel" value="false"> Não
								</label>
							</div>
						</div>		
					
						<div class="form-group">
							<label for="lpType" class="col-sm-6 control-label">
								Tipo de rótulos
							</label>
							<div class="col-sm-3">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="lpType" id="preSet" value="preSet"> Fixados
								</label>
								<label class="radio-inline">
									<input type="radio" name="lpType" id="postSet" value="postSet">Permitir Sugestões
								</label>
								
							</div>
						</div>	
						
						<div class="form-group" id="sugAccRateRow" style="display:none;">
							<label for="lpSuggestionAcceptanceRate" class="col-sm-6 control-label">
								<abbr title="Número mínimo de rotuladores em concordância para que a sugestão de rótulo seja aceita pelo processo de rotulação">
									Taxa mínima para aceitar rótulos (sugestões)
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input readonly type="number" id="lpSuggestionAcceptanceRate" name="lpSuggestionAcceptanceRate" 
									min="1" value='0' class="form-control input-sm">
							</div>
						</div>
						
						<div class="form-group">
							<label for="lpLabelAcceptanceRate" class="col-sm-6 control-label">
								<abbr title="Número mínimo de rotuladores em concordância para que um documento deixe de ser apresentado">
									Taxa mínima de concordância de rótulos (documentos)
								</abbr>						
							</label>
							<div class="col-sm-3">
								<input type="number" id="lpLabelAcceptanceRate" name="lpLabelAcceptanceRate" 
									min="2" value='2' class="form-control input-sm">
							</div>
						</div>
						
						<div class="form-group">
							<label for="txtLabel" class="col-sm-6 control-label">
								Adicionar opções de rótulos
							</label>
							<div class="col-sm-3">
								<table id="tblLabelList">
									<tr>
										<td><input id="txtLabel" type="text" name="txtLabel" class="form-control input-sm"></td>
										<td style="padding-left:10px;"><button id="btnAddLabel" type="button" class="btn btn-default btn-sm" > Adicionar</button></td>
									</tr>
								</table>
							</div>
						</div>					
						
						<div class="form-group">
							<label for="lpSuggestionAlgorithm" class="col-sm-6 control-label">Algoritmo para sugestão de rótulos</label>
							<div class="col-sm-3">
								<select name="lpSuggestionAlgorithm" id="lpSuggestionAlgorithm" class="form-control input-sm">
								  <option value="mostVoted">Sugerir o mais votado</option>
								  <option value="random">Sugestão aleatória</option>
								  <option value="transductive">Classificação transdutiva</option>
								  <option value="testMode">Modo de teste</option>
								</select>
							</div>
						</div>	
						
						<div class="form-group" id="idiomRow" style="display:none;">
							<label for="lpIdiom" class="col-sm-6 control-label">Idioma dos documentos</label>
							<div class="col-sm-2">
								<select name="lpIdiom" id="lpIdiom" class="form-control input-sm">
								  <option id='optionPT' selected value="pt">Português</option>
								  <option value="en">Inglês</option>
								  <option id='optionUN' value="un" hidden>Indefinido</option>
								</select>
							</div>
						</div>  
						
						<div class="form-group" id="resetRateRow" style="display:none;">
							<label for="lpTransductiveResetRate" class="col-sm-6 control-label">
								<abbr title="A cada quantos documentos rotulados o passo 'reset' do algoritmo deve ser realizado">
									Taxa para reset (algoritmo transdutivo)
								</abbr>						
							</label>
							<div class="col-sm-2" >
								<input type="number" id="lpTransductiveResetRate" name="lpTransductiveResetRate" 
									min="1" value='0' class="form-control input-sm" >
							</div>
						</div>
						
				</form>
			</div>
			<div align="center" >
					<button type="button" 
					class="btn btn-default" style="margin:20px;"
					id="submitButton" onClick="validadeForm()" >Criar</button>
			</div>
			
		<?php else : ?>
            <p>
                <span class="error">Você não está autorizado a visualizar esta página.</span> 
				<a href="index.php">Voltar</a>
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					Esta obra de <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					está licenciado com uma Licença <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>		
    </body>
</html>
