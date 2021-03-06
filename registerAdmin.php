<?php	
include_once 'includes/register.inc.php';	
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
        <title>Register Admin</title>
		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script>
    </head>
    <body>
		<?php	showAlert();?>
		<?php if (login_check($mysqli) == true and $_SESSION['user_role'] == 'processAdmin') : ?>
		<header>
		    <nav class="navbar navbar-default">
				<div class="container">
					<div class="navbar-header navbar-left">
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
								<li><a href="registerAdmin.php">Register an Admin</a></li>
								<li><a href="helpAdmin.php">Admin Help</a></li>
								<li><a href="help.php">User Help</a></li>			
								<li><a href="includes/logout.php">Logout</a></li>
							</ul>
						</div><!--/.nav-collapse -->
				</div>
			</nav>
		</header>
		<div class="container">
        
			<!-- Registration form to be output if the POST variables are not
			set or if the registration script caused an error. -->
			<form class="form-horizontal" action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="POST" name="registration_form">
				<fieldset>
					<div id="legend">
						<legend >New Admin</legend>
					</div>
					<div class="form-group">
						<label for="username" class="col-sm-2 control-label">User Name</label>
						<div class="col-sm-5">
							<input type="text" id="username" name="username" class="form-control" placeholder="User Name" required autofocus/>
							<input type="hidden" id="user_role" name="user_role" class="form-control" value="processAdmin"/>
						</div>
					</div>
					
					<div class="form-group">
						<label for="email" class="col-sm-2 control-label">Email</label>
						<div class="col-sm-5">
							<input type="email" id="email" name="email" class="form-control" placeholder="Email" required autofocus/>
						</div>
					</div>
					
					<div class="form-group">
						<label for="password" class="col-sm-2 control-label">Password</label>
						<div class="col-sm-5">
							<input type="password"name="password" id="password" class="form-control" placeholder="Password" required/>
						</div>
					</div>
					
					<div class="form-group">
						<label for="confirmpwd" class="col-sm-2 control-label">Confirm Password</label>
						<div class="col-sm-5">
							<input type="password"name="confirmpwd" id="confirmpwd" class="form-control" placeholder="Password" required/>
						</div>
					</div>
					<div class="form-group">	
						<div class="col-sm-offset-2 col-sm-5">
							<button type="button" class="btn btn-primary btn-lg btn-block"
								onclick="return regformhash(this.form,
													   this.form.username,
													   this.form.email,
													   this.form.password,
													   this.form.confirmpwd,
													   this.form.user_role);" > Sign Up
							</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
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