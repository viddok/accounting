<?php
session_start();

require_once 'func.php';
use classes\Users\Users;

$users = Users::create_collection();

$error = '';

if ( isset($_POST['user_id']) ) {
	if ( is_numeric( $_POST['user_id'] ) ) {
		foreach ($users->getCollection() as $user) {
			if ( $_POST['user_id'] == $user->getId() ) {
				$current_user = array(
					'id' => $user->getId(),
					'name' => $user->getName(),
					'role' => $user->getRole(),
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
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/authorization.css">
</head>
<body>
<div class="wrap">
    <div class="top-menu">
        <?php
        if ( isset( $_SESSION['current_user'] ) ) {
	        echo 'Привет, <a href="authorization.php" title="Сменить пользователя.">' . $_SESSION['current_user']['name'] . '</a>';
        } else {
            echo '<a href="authorization.php" title="Выберите пользователя.">Авторизуйтесь</a>';
        }
        ?>
    </div>

    <div class="content">
        <?php echo $error; ?>
        <form method="post">
            <label>
                Выберите пользователя: <br />
                <select name='user_id'>
                    <?php
                    foreach ($users->getCollection() as $user) {
                        echo "<option value='{$user->getId()}'>{$user->getName()}</option>";
                    }
                    ?>
                </select>
            </label>
            <input type="submit" value="Авторизоваться">
        </form>
    </div>
</div>
</body>
</html>
