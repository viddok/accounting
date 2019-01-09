<?php
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Plan\MonthlyPlan;

$plan = new MonthlyPlan();

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Месячный план</title>
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
        <!--Главное меню-->
        <?php require_once 'templates/main-menu.php'; ?>

        <div class="container">
            <div class="block">
                <?php $plan->render(); ?>
            </div>
        </div>

    </div>
</div>
</body>
</html>