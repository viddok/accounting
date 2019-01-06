<?php
session_start();

require_once 'func.php';
use classes\Users\Users;

$users = Users::create_collection();

$error = '';

if ( isset( $_SESSION['current_user'] ) ) {
	header( "location: index.php" );
	exit();
}

if ( isset($_POST['user_id']) ) {
	if ( is_numeric( $_POST['user_id'] ) ) {
		foreach ($users->getCollection() as $user) {
			if ( $_POST['user_id'] == $user->id ) {
				$current_user = array(
					'id' => $user->id,
					'name' => $user->name,
					'role' => $user->role,
				);

				$_SESSION['current_user'] = $current_user;
				header( "location: index.php" );
				exit();
			}
		}
	} else {
		$error = 'Ошибка авторизации.';
	}
}

?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Авторизация пользователя</title>
	<style>
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
			overflow: hidden;
		}
		body {
			overflow: auto; /* добавить полосу прокрутки */
			background-color: cornsilk;
			display: flex;
			align-items: center;
			justify-content: center
		}
		select {
			min-width: 200px;
		}
	</style>
</head>
<body>
	<?php echo $error; ?>
	<form method="post">
		<label>
			Выберите пользователя: <br />
			<select name='user_id'>
				<?php
				foreach ($users->getCollection() as $user) {
					echo "<option value='$user->id'>$user->name</option>";
				}
				?>
			</select>
		</label>
		<input type="submit" value="Авторизоваться">
	</form>
</body>
</html>
