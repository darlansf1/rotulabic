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
		<title>User Help</title>
		
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
        <?php if (login_check($mysqli) == true) : ?>
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
		<?php else : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">RotuLabic - Sistema de Apoio à Rotulação Manual de Textos</a>
						</div>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
							<li><a href="help.php">Help</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
        <?php endif; ?>
		
		<div class="jumbotron" style="padding-top: 0px;">
			<div class="container">
				<div class="page-header ">
					<h1 class="text-center">FAQ</h1>
				</div>
      
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
									What is this website for?        
								</a>
							</h4>
						</div>
						<div id="collapse1" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;In Text Mining, there is a need for labeled text documents. Those labeled documents are used, for example, in text classification and/or clustering tasks. As an example, your e-mail server needs a database of e-mails known to be or labeled as spam in order to automatically classify incoming e-mails as spam or not spam.
								<br>&nbsp;&nbsp;&nbsp;The task of labeling documents is usually time consuming and very tedious. Hence, this website come as a tool to support and improve the process of manual document labeling, contributing to the development of the research in Text Mining.
							</div>
						</div>
					</div>
				</div> 
		
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
									How does a labeling process work?        
								</a>
							</h4>
						</div>
						<div id="collapse3" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;There are two types of users and two types of labeling processes.<br>
								&nbsp;&nbsp;&nbsp;In order to create a Labeling Process (LP), an admin user needs to provide the set of documents to be labeled, the settings of the LP and some instructions. Once the LP has been created, the admin user must add the LP taggers, the people responsible for labeling the documents. With all that done, the LP taggers added by the admin user (who do not need to have admin-level access to the system) may start labeling the documents.<br>
								&nbsp;&nbsp;&nbsp;The admin user may create a document labeling process or an aspect-based labeling process. In the former, the taggers attribute a label to the entire document. In the latter, however, taggers are supposed to identify characteristics (aspects) of whichever entity the document is about and label the identified characteristics, being also able to identify the piece of text that justify their classification.
							</div>
						</div>
					</div>
				</div> 
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
									May I create a labeling process?        
								</a>
							</h4>
						</div>
						<div id="collapse4" class="panel-collapse collapse ">
							<div class="panel-body">
								&nbsp;&nbsp;&nbsp;Only admin users are able to create labeling processes.<br>
								&nbsp;&nbsp;&nbsp;If you need admin-level access to the system, you should contact one of the users who already have admin-level access.
							</div>
						</div>
					</div>
				</div> 	
				<div class="panel-group" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
									How is Machine Learning used to suggest labels?        
								</a>
							</h4>
						</div>
						<div id="collapse5" class="panel-collapse collapse ">
							<div class="panel-body">
								<b>Document-Level Labeling</b><br>
								&nbsp;&nbsp;&nbsp;In the document-level labeling, the main method used to suggest labels is the transductive classification. 
								Shortly, this method sets 'weights' to the label chosen for a document making it propagate to similar documents (based on the terms in common).<br>
								&nbsp;&nbsp;&nbsp;As an example, imagine a tagger has chosen the label "Sport" to a given document and that the word "soccer" appears several times in this document. When using the transductive classification algorithm, other documents containing the word "soccer" are going to have greater chance of being suggested the label "Sport".<br><br>
								&nbsp;&nbsp;&nbsp;<b>Referência</b>: Rossi, R. G., Lopes, A. A., e Rezende, S. O. (2014). A parameter-free label propagation algorithm using bipartite heterogeneous networks for text classification. In Proc. Symposium on Applied Computing, pages 79-84 <a href="http://dl.acm.org/citation.cfm?id=2554901">[download]</a>
								
								<br><br><b>Aspect-Based Labeling</b><br>
								&nbsp;&nbsp;&nbsp;When it comes to aspect-based labeling, there are two steps the algorithms need to take when presenting the suggestions: the identification of aspects and the classification of the sentiments associated to them.
								<br>&nbsp;&nbsp;&nbsp;The aspect identification algorithm is based on the work of <a href='http://www.aaai.org/Papers/AAAI/2004/AAAI04-119.pdf'>Hu and Liu (2004)</a>, but with some modifications. Basically, what the algorithm does is identify the nouns in the set of documents of the labeling process and count the frequence of each of them throughout the documents. Those nouns whose frequence reaches a minimum value specified by the process admin, are considered as aspects and all of its occurrences are marked as so.
								<br>&nbsp;&nbsp;&nbsp;For sentiment classification, there are two aproaches used, one of them is based on the work of <a href='http://dl.acm.org/citation.cfm?id=1073153'>Turney (2002)</a>, the PMI-Based approach, the other algorithm is the Lexicon-Based approach.
								<br>&nbsp;&nbsp;&nbsp;In the PMI-Based approach, the algorithm identifies phrases of two words within the documents that follow specific patterns, like a noun followed by an adjective. Those sets of words are then searched on a search-engine along with a word with known positive connotation and also with a word with known negative connotation. The idea is that if the search-engine finds more results when the phrase is searched with the word with positive connotation, then that phrase possibly has a positive connotation itself and so is any aspect in the same sentence as that phrase. The same idea applies when there are more results for the negative connotation.
								<br>&nbsp;&nbsp;&nbsp;The Lexicon-Based approach uses an idea similar to that of the PMI-Based approach, but instead of using a search-engine to infer the polarity of the sentiments it uses a collection of information about words and their categories, a lexicon.
								<br>&nbsp;&nbsp;&nbsp;Those algorithms used for aspect-based labeling are better explained in the <a href='https://drive.google.com/file/d/0BwPBOam6RAzkMkxTSHNLdkhTbWM/view'>graduation thesis of Darlan Santana Farias (in Portuguese)</a>.
							</div>
						</div>
					</div>
				</div> 					
			</div>
		</div>
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
