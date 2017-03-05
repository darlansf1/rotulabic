<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
include_once 'includes/lpbhn.php';	//Using functions 'setTransductiveData' and 'unsetTransductiveData'

sec_session_start();
unsetLabelingProcessData();

?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia, Darlan Santana Farias">
		<title>Admin Help</title>
		
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
		<style> .panel-group {margin-bottom: 10px;}	</style>
		
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

			<div class="jumbotron" style="padding-top: 0px;">
				<div class="container">
					<div class="page-header ">
						<h1 class="text-center">Admin FAQ</h1>
					</div>
		  
					<div class="panel-group" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
										What are those settings from the labeling process creation form? 
									</a>
								</h4>
							</div>
							<div id="collapse1" class="panel-collapse collapse ">
								<div class="panel-body">
								<ul>
									<li><strong>Process Name</strong>: the name to be used for the labeling process (LP).</li>
									<li><strong>Upload labeling instruction</strong>: text document containing useful information and/or any protocols the taggers need to follow while labeling documents. The document uploaded in this field is presented to taggers as they start the labeling process. Some ideas of contents that could be useful for taggers include labeling examples and hints. 
									<li><strong>Upload documents to be labeled</strong>: the set of text documents to be labeled. 
									
									<br><br><h3>Document Labeling Specific</h3>
									<li><strong>Multi-label</strong>: indicates whether taggers are allowed to choose more than a single label for any given document. In case the LP is multi-label enabled, the labels are presented in checkboxes. Radio buttons are used otherwise.</li>
									<li><strong>Label settings</strong>: if fixed is chosen, then only the process admin can define label options for the LP. Otherwise, taggers are allowed to suggest new labels while labeling. When this last case is true, the admin needs to set a minimum value so that only labels suggested by at least that many taggers are really added as an option in the LP.</li>
									<li><strong>Min document agreement rate</strong>: indicates how many taggers need to agree to the same label for the document to be considered finished and the label taggers agreed on be final. </li>
									<li><strong>Add label options</strong>: in this field, the admin adds the label options that are to be considered by taggers during the labeling task. As explained before, if labels are not fixed, taggers can add label options to the process.</li>
									<li><strong>Label suggestion algorithm</strong>: as the taggers go on labeling documents, the system will provide them with suggestions obtained using one of the following algorithms:
										<ul>
											<li>Most voted: suggests the most popular label for the current document.</li>
											<li>Random: suggests a ramdonly picked label among those available.</li>
											<li>Transductive Classification: suggests a label using the transductive classification algorithm, which determines the best choice considering the tems in each document (more details in the User Help page). In case this algorithm is chosen, the admin needs to set a parameter called 'reset rate', this parameter defines an interval given in number of documents after which the algorithm is restarted. Also, the admin needs to provide information about the language of the documents, this information is needed for preprocessing the documents (e.g. stemming).</li>
											<li>Test mode: does not show the taggers any suggestions. This option should be chosen when there is interest in comparing the three algorithms above. In this mode, the system calculates which would be the label suggested by each of the algorithms in order to evaluate the precision of each of them against the labels chosen by taggers.</li>
										</ul>
									</li>
									<br><br><h3>Aspect-Based Labeling Specific</h3>
									<li><strong>Hidden Aspect</strong>: indicates whether taggers are allowed to include aspects that are evaluated in the documents implicitly, for example, if a sentence states "The chicken wings were way too expensive.", it is clearly an implicit evaluation of the PRICE aspect.</li>
									<li><strong>General Aspect</strong>: indicates whether taggers are allowed to include annotations on evaluations about the entity itself, as in the sentence "I love this restaurant" which does not evaluate any specific aspect of the restaurant but the restaurant itself.</li>
									<li><strong>Add label</strong>: in this field, the admin adds the label options that are to be considered by taggers during the labeling task. The labels are characterized by their colors and texts, no label can be added later and the algorithms correct functioning is only guaranteed with labels with the text "Positive", "Negative" and "Neutral" with whatever case and color.</li>
									<li><strong>Aspect suggestion algorithm</strong>: as the taggers go on labeling documents, the system will provide them with suggestions of aspects. The user may choose one of the following options:
										<ul>
											<li>No Suggestions: no suggestions are presented to taggers.</li>
											<li>Frequence-Based: identifies frequent nouns as aspects (more details in the User Help section). If this algorithm is chosen, the user needs to provide some parameters, such as the "Min Frequence" a noun has to reach to be considered an aspect, the language in which the documents as writen and whether or not the algorithm should use an Automatic Translator (only texts in Portuguese and English can use suggestion algorithms without translation). Also, the admin needs to choose one of the following methods for sentiment classification (more detailed explanations about the algorithms can be found in the User Help section):</li>
											<ul>
												<li>No suggestions: does not classify sentiments on aspects. All aspects are classified with a default polarity.</li>
												<li>PMI-Based: classifies sentiments using search-engine results.</li>
												<li>Lexicon-Based: classifies sentiments using a collection of informations about words and their classification.</li>
											</ul>
										</ul>
									</li>
								</ul>									
								</div>
							</div>
						</div>
					</div> 
					<div class="panel-group" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
										What are those parameters in the form for downloading the results in the process info page?
										
									</a>
								</h4>
							</div>
							<div id="collapse2" class="panel-collapse collapse ">
								<div class="panel-body">
								<h3>Document Labeling</h3>
								<ul>
									<li><strong>Format</strong>: defines the directory structure in which the labeled documents are going to be organized. For the first format, each document is inserted inside the directory corresponding to its label. For the second format, all documents are put in the same directory and their labels are inserted in the file name.<br>
									&nbsp;&nbsp;&nbsp;<strong>First Format</strong><br>
									&nbsp;&nbsp;&nbsp;----root directory<br>
									&nbsp;&nbsp;&nbsp;--------label1<br>
									&nbsp;&nbsp;&nbsp;------------document1.txt<br>
									&nbsp;&nbsp;&nbsp;------------document2.txt<br>
									&nbsp;&nbsp;&nbsp;--------label2<br>
									&nbsp;&nbsp;&nbsp;------------document3.txt<br><br>
									&nbsp;&nbsp;&nbsp;<strong>Second Format</strong><br>
									&nbsp;&nbsp;&nbsp;----root diretory<br>
									&nbsp;&nbsp;&nbsp;--------label1_document1.txt<br>
									&nbsp;&nbsp;&nbsp;--------label1_document2.txt<br>
									&nbsp;&nbsp;&nbsp;--------label2_document3.txt<br>
									</li><br>
									<li><strong>Agreement Rate</strong>: defines the minimum number of taggers that need to agree to a label for it to be accepted as the document classification.</li>
								</ul>
								<br><h3>Aspect-Based Labeling</h3>
								<ul>
									<li><strong>Include sentiment words?</strong>:
									&nbsp;&nbsp;&nbsp;The results of the Aspect-Based Labeling come in a set of XML files, one for each text file uploaded for processing. The files contain information about the aspects identified by the taggers. When choosing to include sentiment words in the results, any sentiment indications (terms the tagger may indicate when labeling documents and justify their choice of sentiment polarity) associated with the aspects are included in the XML file right after the aspect they are related to. The figure below show an XML without sentiment indications.
									<img src="images/XML.png" width="800" border="1"><br>
									&nbsp;&nbsp;&nbsp;In this format, the tag 'aspectTerms' marks the beginning and the end of the list of aspects identified in a document. Each tag named 'aspectTerm' indicates an aspect, and enclosed within its delimiter there is a set of properties of the aspects that are, in the order they appear, the word identified as aspect, the polarity of the sentiment associated, and the positions where the word starts and ends in the document.
									<br>&nbsp;&nbsp;&nbsp;The figure below shows an XML file with sentiment indications, it is basically the same format as the file without sentiment indications, except for the additional information about the sentiment indications, that are similar to that of the aspects.
									<img src="images/XML_sentiment.png" width="800" border="1"><br>
									</li><br>
									<li><strong>Agreement rate</strong>: defines the minimum number of taggers that need to agree to a term classified as aspect and to its respective polarity for this information to be included in the downloaded XML files. </li>
								</ul>
								</div>

							</div>
						</div>
					</div>				
				</div>
			</div>
		<?php else : ?>
            <p>
                <span class="error">Access denied.</span> 
				<a href="index.php">Return.</a>
            </p>
        <?php endif; ?>

		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This is a work from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
    </body>
</html>
