<?php include_once 'includes/labelingProcessInfo.inc.php'; ?>
<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Labeling Process</title>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />	
    </head>
    <body>
		<?php showAlert(); ?>
		<?php if ((login_check($mysqli) == true) && ($_SESSION['user_role'] == 'processAdmin')  ) : ?>	
			<?php showAlert(); ?>
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
			<div class="container">
				<?php getLPInfo($mysqli,$lpID); ?>
				<hr>
				<section>
					<div class="row">
						<div class="col-md-6" style= "border:1px solid #ddd">
							<form action='includes/download.php' class="form-horizontal" method='POST'>
							<?php if ($_SESSION['cur_lpLabelingType'] == 'normal') {	
								echo '<h2>Download Labeled Docs</h2>
								<input type="hidden" name="lpID" value='.$lpID.'>
								<input type="hidden" name="cur_lpLabelingType" value='.$_SESSION['cur_lpLabelingType'].'>
								
								<div class="form-group ">
									<label for="action" class="col-sm-4 control-label" >
										Format:
									</label>
									<div class="col-md-6 " >
										<label class="radio" >
											<input type="radio" name="action" value="firstDownload" checked="checked">
											Labels as folders
										</label>
										<label class="radio">
											<input type="radio" name="action" value="secondDownload">
											Labels in the document name
										</label>
									</div>
								</div>';	
							}else{
								echo '<h2>Download of Annotations, SemEval-like XML files</h2>
								<input type="hidden" name="lpID" value='.$lpID.'>
								<input type="hidden" name="cur_lpLabelingType" value='.$_SESSION['cur_lpLabelingType'].'>
								
								<div class="form-group ">
									<label for="action" class="col-sm-4 control-label" >
									Include sentiment words?
									</label>
									<div class="col-md-6 " >
										<label class="radio" >
											<input type="radio" name="action" value="firstDownload" checked="checked">
											Yes
										</label>
										<label class="radio">
											<input type="radio" name="action" value="secondDownload">
											No
										</label>
									</div>
								</div>';
							}?>
								<div class="form-group">
									<label for="lpSuggestionAcceptanceRate" class="col-sm-4 control-label">
										Agreement Rate
									</label>
									<div class="col-sm-2">
										<input type='number' name='agreementRate' min='1' class="form-control input-sm"
											<?php echo "max='". $_SESSION['cur_lpAccRate'] . "'";	 ?>
											<?php unset ( $_SESSION['cur_lpAccRate'] );	 ?>
											value='1' />
									</div>
									<input type="Submit" class='btn btn-default ' value="Download"/>
								</div>
								
								
							</form>
						<div>
					</div>
				</section>
			</div>
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				Try <a href="index.php">logging in</a> first.
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This is a project from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed with the <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
	</body>
</html>
