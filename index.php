<?php
session_start();
require_once 'func.php';
forwarding_auth();
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Домашня бухгалтерия</title>
    <link rel="stylesheet" href="style/style.css">
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
        <nav class="nav top-nav">
            <ul>
                <li><a href="accounts.php">Мои счета</a></li>
                <li><a href="">Пунтк 2</a></li>
                <li><a href="">Пункт 3</a></li>
            </ul>
        </nav>

    </div>
</div>
</body>
</html>
