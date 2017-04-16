<?php include_once 'includes/newLabelingProcess.inc.php'; ?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia">
		<title>Create Labeling Process</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery.min.js"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<script src="js/jquery.colorPicker.js" type="text/javascript"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		<link rel="stylesheet" href="styles/colorPicker.css" />		
    
		<script type="text/Javascript">
			$(document).ready(function(){
				$('#color').colorPicker();
				$('#chosencolor').colorPicker();
				
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
					else alert("Duplicate Label!");
				});
				
				//If the suggestion algorithm is frequence or PMI based
				//then show extra options
				$("#lpAspectSuggestionAlgorithm").change(function() {
					var polarityAlgorithm = $("#lpSuggestionAlgorithm option:selected").val();
					var algorithm = $("#lpAspectSuggestionAlgorithm option:selected").val();
					if (algorithm == 'frequenceBased'){
						showFrequenceInput();
					}/*else if(algorithm == 'PMIBased'){
						hideFrequenceInput();
						showPMIInput();
					}else if(polarityAlgorithm == "PMIBased"){
						hideFrequenceInput();
						showPMIInput();
					}*/else{
						hideFrequenceInput();
					}
					if(algorithm != 'none'){
						showPolaritySuggestionRow();
					}else{
						hidePolaritySuggestionRow();
					}
				});
				
				$("#lpSuggestionAlgorithm").change(function() {
					var aspectAlgorithm = $("#lpAspectSuggestionAlgorithm option:selected").val();
					var algorithm = $("#lpSuggestionAlgorithm option:selected").val();
					/*if (algorithm == 'PMIBased'){
						showPMIInput();
					}else if(aspectAlgorithm != 'frequenceBased' && aspectAlgorithm != 'PMIBased'){
						hidePMIInput();
					}*/
				});
			});
			
			function showPolaritySuggestionRow(){
				//Showing language row
				$("#polaritySuggestionRow").css("display","");
			}
			
			function hidePolaritySuggestionRow(){
				$("#polaritySuggestionRow").css("display","none");
			}
			
			function showPMIInput(){
				//Showing language row
				$("#languageRow").css("display","");
				
				//Showing translator use row
				$("#translatorUseRow").css("display","");
			}
			
			function hidePMIInput(){
				//Hiding language row
				$("#languageRow").css("display","none");
				
				//Hiding translator use row
				$("#translatorUseRow").css("display","none");
			}
			
			function showFrequenceInput(){
				//Showing language row
				$("#languageRow").css("display","");
				
				//Showing translator use row
				$("#translatorUseRow").css("display","");
				
				//Showing min frequence row
				$("#minFrequenceRow").css("display","");
			}
			
			function hideFrequenceInput(){
				//Hiding language row
				$("#languageRow").css("display","none");
				
				//Hiding translator use row
				$("#translatorUseRow").css("display","none");
				
				//Hiding min frequence row
				$("#minFrequenceRow").css("display","none");
			}
			
			function appendLabel(){
				//Append a new row to the table of labels
				$("#tblLabelList").append(
					"<tr name='newRows[]'>" + 
						"<td  style='padding-top:3px;'><input style='background-color:"+$("#color").val()+"; type='text' name='lpLabels[]' readonly value='" + $("#txtLabel").val() + "'></td>" +
						"<td><input type='hidden' name='colors[]' readonly value='" + $("#color").val() + "'></td>" +
						"<td onClick='removeRow($(this))' style='padding-left:10px;'><img src='images/ic_delete.png' ></td>" + 
					"</tr>"
				);
				$("#txtLabel").val("");
			};
			function removeRow(delete_icon){
				var rowName = delete_icon.closest('tr').get(0).getAttributeNode('name').value;
				
				if(rowName == 'negativeLabel' || rowName == 'neutralLabel' || rowName == 'positiveLabel')
					alert('The algorithms we provide use the labels "NEGATIVE", "POSITIVE" and "NEUTRAL" to assign polarity to the aspects. If you intend to use any of them, you must keep these labels or create new labels with these same names.');
				
				delete_icon.closest('tr').remove(); //Removing the row
			};
			function validadeForm(){
				//Checking if settings for frequence-based algorithm are valid
				if($("#lpAspectSuggestionAlgorithm option:selected").val() == "frequenceBased"){
					if($("#min_frequency").val() < 1){
						alert("Minimum Frequence must be greater than 0");
						return false;
					}
				}
				//Checking if settings for frequence-based and PMI-Based algorithm are valid
				if($("#lpAspectSuggestionAlgorithm option:selected").val() == "frequenceBased"
					|| $("#lpAspectSuggestionAlgorithm option:selected").val() == "PMIBased"
					|| $("#lpSuggestionAlgorithm option:selected").val() == "frequenceBased"){
					if($("#lpIdiom option:selected").val() != "pt" && $("#lpIdiom option:selected").val() != "en" 
							&& $("#tNegative").get(0).checked){
						alert("You must use the translator when the documents are not in Portuguese or English");
						return false;
					}
				}
				
				//Checking if the instruction file was sent
				if(($("#lpInstructions").val()=="")){
					alert("You must upload the instructions for the taggers\n(Use a .txt file)")
					return false;
				}

				//Checking if the documents(to be labeled) were sent
				if(($("#lpDocs").val()=="")){
					alert("You must upload at least one document")
					return false;
				}

				//Checking if there are at least two labels
				if($('input[name^="lpLabels"]').size() < 2 ){
					alert("There should be at least two labels.");
					return false;
				}

				//If everything is all right, then we submit the form 
				$("#newLPForm").submit(); 
				alert("RotuLabic is going to process the input data. This might take a while.");
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
							<a class="navbar-brand" href="index.php">RotuLabic</a>
						</div>
						<p class="navbar-text">
							--  Hello, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
								<li><a href="profile.php">Profile</a></li>
								<?php if (($_SESSION['user_role'] == 'processAdmin')  ){
										echo 	'<li><a href="helpAdmin.php">Admin Help</a></li>
												<li><a href="help.php">User Help</a></li>';
									}else{
										echo '<li><a href="help.php">Help</a></li>';
									}
								?>
								<li><a href="includes/logout.php">Logout</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
			
			<h1 align="center">Create new Labeling Process</h1>
			<div class="container" style= "border:1px solid #ddd;padding-top:30px;">
				<form 	action = "<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
						id="newLPForm" 
						name="newLPForm" 
						method="post" 
						enctype="multipart/form-data"
						class = "form-horizontal "> 
						
						<div class="form-group">
							<label for="lpName" class="col-sm-6 control-label">Process Name</label>
							<div class="col-sm-3">
								<input required type="text" name="lpName" id="lpName" class="form-control input-sm">
							</div>
						</div>
						
						<div class="form-group">
							<label for="lpInstructions" class="col-sm-6 control-label">
								<abbr title=".txt Only">
									Upload labeling instructions
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input required type="file" id="lpInstructions" name="lpInstructions" accept=".txt" >
							</div>
						</div>	

						<div class="form-group">
							<label for="lpDocs" class="col-sm-6 control-label">
								<abbr title=".txt Only">
									Upload documents to be labeled
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input required multiple type="file" id="lpDocs" name="lpDocs[]" accept=".txt" >
							</div>
						</div>						
						
						<div class="form-group">
							<label for="lpHiddenAspect" class="col-sm-6 control-label">
								<abbr title="Allow taggers to include annotations on implicit/hidden aspects">
									Hidden Aspect
								</abbr>	
							</label>
							<div class="col-sm-2">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="lpHiddenAspect" id="haPositive" value="true"> Yes
								</label>
								<label class="radio-inline">
									<input type="radio" name="lpHiddenAspect" id="haNegative" value="false"> No
								</label>
							</div>
						</div>
						
						<div class="form-group">
							<label for="lpGenericAspect" class="col-sm-6 control-label">
								<abbr title="Allow taggers to include annotations on opinions about the entity as a whole">
									General Aspect
								</abbr>	
							</label>
							<div class="col-sm-2">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="lpGenericAspect" id="gaPositive" value="true"> Yes
								</label>
								<label class="radio-inline">
									<input type="radio" name="lpGenericAspect" id="gaNegative" value="false"> No
								</label>
							</div>
						</div>
						
						<!--<div class="form-group">
							<label for="lpLabelAcceptanceRate" class="col-sm-6 control-label">
								<abbr title="Min number of taggers that need to agree before a document is considered all done">
									Min agreement rate
								</abbr>						
							</label>
							<div class="col-sm-3">
								<input type="number" id="lpLabelAcceptanceRate" name="lpLabelAcceptanceRate" 
									min="2" value='2' class="form-control input-sm">
							</div>
						</div>-->
						
						<input type='hidden' id='labelingType' name='labelingType' value='annotation'>
						
						<div class="form-group">
							<label for="txtLabel" class="col-sm-6 control-label">
								Add Labels
							</label>
							<div class="col-sm-3">
								<table id="tblLabelList">
									<tr>
										<td><input id="txtLabel" type="text" name="txtLabel" class="form-control input-sm"></td>
										<td><input id="color" name="color" type="text" readonly value="#333399" /></td>
										<td style="padding-left:10px;"><button id="btnAddLabel" type="button" class="btn btn-default btn-sm" > Adicionar</button></td>
									</tr>
									<tr name='negativeLabel'>
										<td  style='padding-top:3px;'><input style='background-color:#FF0000;' type='text' name='lpLabels[]' readonly value='NEGATIVE'></td>
										<td><input type='hidden' name='colors[]' readonly value='#FF0000'></td>
										<td onClick='removeRow($(this))' style='padding-left:10px;'><img src='images/ic_delete.png' ></td>
									</tr>
									<tr name='positiveLabel'>
										<td  style='padding-top:3px;'><input style='background-color:#00FF00;' type='text' name='lpLabels[]' readonly value='POSITIVE'></td>
										<td><input type='hidden' name='colors[]' readonly value='#00FF00'></td>
										<td onClick='removeRow($(this))' style='padding-left:10px;'><img src='images/ic_delete.png' ></td>
									</tr>
									<tr name='neutralLabel'>
										<td  style='padding-top:3px;'><input style='background-color:#3366FF;' type='text' name='lpLabels[]' readonly value='NEUTRAL'></td>
										<td><input type='hidden' name='colors[]' readonly value='#3366FF'></td>
										<td onClick='removeRow($(this))' style='padding-left:10px;'><img src='images/ic_delete.png' ></td>
									</tr>
								</table>
							</div>
						</div>					
						
						<div class="form-group" id="aspecSuggestionRow">
							<label for="lpAspectSuggestionAlgorithm" class="col-sm-6 control-label">Aspect Suggestion Algorithm</label>
							<div class="col-sm-3">
								<select name="lpAspectSuggestionAlgorithm" id="lpAspectSuggestionAlgorithm" class="form-control input-sm">
								  <option value="none">No Suggestions</option>
								  <option value="frequenceBased">Frequence-Based</option>
								</select>
							</div>
						</div>
						
						<div class="form-group" id="polaritySuggestionRow" style="display:none;">
							<label for="lpSuggestionAlgorithm" class="col-sm-6 control-label">Polarity Suggestion Algorithm</label>
							<div class="col-sm-3">
								<select name="lpSuggestionAlgorithm" id="lpSuggestionAlgorithm" class="form-control input-sm">
								  <option value="none">No suggestions</option>
								  <!--Microsoft's search API has become too restrictive 
								  for free tier users. We're commenting this portion of the code
								  util we find a suitable alternative, but nothing keeps you
								  from uncommenting it and using it as you like
								  <option value="PMIBased">PMI-Based</option>-->
								  <option value="lexiconBased">Lexicon-Based</option>
								  <!--<option value="random">Sugestão aleatória</option>
								  <option value="transductive">Classificação transdutiva</option>
								  <option value="testMode">Modo de teste</option>-->
								</select>
							</div>
						</div>
						
						<div class="form-group" id="languageRow" style="display:none;">
							<label for="lpIdiom" class="col-sm-6 control-label">Language of the documents</label>
							<div class="col-sm-2">
								<select name="language" id="lpIdiom" class="form-control input-sm">
								  <option id='optionPT' selected value="pt">Portuguese</option>
								  <option id='optionEN'value="en">English</option>
								  <option id='optionES'value="es">Spanish</option>
								  <option id='optionDE'value="de">German</option>
								  <option id='optionFR'value="fr">French</option>
								  <option id='optionIT'value="it">Italian</option>
								  <option id='optionBS'value="bs-Latn">Bosnian (Latin)</option>
								  <option id='optionCA'value="ca">Catalan</option>
								  <option id='optionHR'value="hr">Croatian</option>
								  <option id='optionCS'value="cs">Czech</option>
								  <option id='optionCS'value="da">Danish</option>
								  <option id='optionNL'value="nl">Dutch</option>
								  <option id='optionET'value="et">Estonian</option>
								  <option id='optionFI'value="fi">Finnish</option>
								  <option id='optionHT'value="ht">Haitian Creole</option>
								  <option id='optionHU'value="hu">Hungarian</option>
								  <option id='optionID'value="id">Indonesian</option>
								  <option id='optionLV'value="lv">Latvian</option>
								  <option id='optionLT'value="lt">Lithuanian</option>
								  <option id='optionMS'value="ms">Malay</option>
								  <option id='optionMT'value="mt">Maltese</option>
								  <option id='optionNO'value="no">Norwegian</option>
								  <option id='optionPL'value="pl">Polish</option>
								  <option id='optionRO'value="ro">Romanian</option>
								  <option id='optionSR'value="sr-Latn">Serbian (Latin)</option>
								  <option id='optionSK'value="sk">Slovak</option>
								  <option id='optionSV'value="sv">Swedish</option>
								  <option id='optionTR'value="tr">Turkish</option>
								  <option id='optionVI'value="vi">Vietnamese</option>
								  <option id='optionCY'value="cy">Welsh</option>
								</select>
							</div>
						</div>  
						
						<div class="form-group" id="translatorUseRow" style="display:none;">
							<label for="translator" class="col-sm-6 control-label">
								<abbr title="Use automatic translator to execute the method in English">
									Use Automatic Translator
								</abbr>	
							</label>
							<div class="col-sm-2">
								<label class="radio-inline">
									<input type="radio" checked="checked" name="translator" id="tNegative" value="false"> No
								</label>
								<label class="radio-inline">
									<input type="radio" name="translator" id="tPositive" value="true"> Yes
								</label>
							</div>
						</div>
						
						<div class="form-group" id="minFrequenceRow" style="display:none;">
							<label for="min_frequency" class="col-sm-6 control-label">
								<abbr title="Minimum number of occurences of a word to be considered by the algorithm">
									Min Frequence
								</abbr>						
							</label>
							<div class="col-sm-2">
								<input type="number" id="min_frequency" name="min_frequency" 
									min="1" value='5' class="form-control input-sm">
							</div>
						</div>
						
				</form>
			</div>
			
			<div align="center" >
					<button type="button" 
					class="btn btn-default" style="margin:20px;"
					id="submitButton" onClick="validadeForm()" >Create</button>
			</div>
			
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				<a href="index.php">Return</a>
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This work a is from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>		
    </body>
</html>
