<?php include_once 'includes/home.inc.php'; ?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia">
		<title>Home</title>
		
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
		<?php if (login_check($mysqli) == true) : ?>	
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
                            <br>
							<img src="images/logo.png" alt="LABIC - Laboratório de Inteligência Computacional" height="50" width="93.5">
						</div>						
						<div class="navbar-header navbar-left">
							<br> <br>
							<a class="navbar-brand" href="index.php">RotuLabic</a>
						</div>
						<p class="navbar-text">
							<br> <br>
							--  Hello, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<br> <br>
							<ul class="nav navbar-nav">
																<li><a href="profile.php">Profile</a></li>
								<?php if (($_SESSION['user_role'] == 'processAdmin')  ){
										echo 	'<li><a href="registerAdmin.php">Register an Admin</a></li>
												<li><a href="helpAdmin.php">Admin Help</a></li>
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
			
			<div align = "center">
				<table id="homeTable">
					<thead>
						<tr>
							<th>Processes to label</th>
							<?php if (($_SESSION['user_role'] == 'processAdmin')  )
								echo "<th>Processes managed by you</th>";
							?>
						</tr>
					</thead>
					<tr>
						<td><table id="tblLP"><?php getTaggerLPs($mysqli) ?></table></td>
						<?php if (($_SESSION['user_role'] == 'processAdmin')  ) : ?>
							<td><table id="tblAdmLP" ><?php getAdminLPs($mysqli) ?></table></td>
						<?php endif; ?>
					</tr>
				</table>
			</div><br>
			<?php if (($_SESSION['user_role'] == 'processAdmin')  ) : ?>
				<div align="center">
					<label for="lpType" style="width:300px;" class="container">Create new labeling process</label>
					<div align="center">
						<select name="lpType" id="lpType" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
							<option value=""></option>
							<option value="newLabelingProcess.php">Document Labeling</option>
							<option value="newABLabelingProcess.php">Aspect-Based Labeling</option>
						</select>
					</div>
				</div>
			<?php endif; ?>
		<?php else : ?>
            <p>
                <span class="error">Acess Denied.</span> 
				Try <a href="index.php">logging in</a>.
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This work is from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>
    </body>
</html>
