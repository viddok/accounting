<?php
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Log\PurchaseLog;


?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Логи</title>
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
            <h2>История покупок</h2>
            <div class="block">
				<?php
				$log = PurchaseLog::get_log( '01-2019' );
				if ( true == $log ) {
					$html = '<table border="1" cellspacing="0" cellpadding="2px" style="text-align: center" width="100%"' . PHP_EOL . '<tbody>' . PHP_EOL;
					$html .= '<tr>' . PHP_EOL;
					$html .= '<td>ID</td>' . PHP_EOL;
					$html .= '<td>Дата</td>' . PHP_EOL;
					$html .= '<td>Пользователь</td>' . PHP_EOL;
					$html .= '<td>Счёт</td>' . PHP_EOL;
					$html .= '<td>Категория</td>' . PHP_EOL;
					$html .= '<td>Сумма</td>' . PHP_EOL;
					$html .= '<td>Список покупок</td>' . PHP_EOL;
					$html .= '</tr>' . PHP_EOL;

					foreach ( $log as $category ) {
						$html .= '<tr>' . PHP_EOL;
						$html .= '<td>' . $category['id'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['date'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['user_name'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['account_title'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['category_title'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['sum'] . '</td>' . PHP_EOL;
						$html .= '<td>' . $category['description'] . '</td>' . PHP_EOL;
						$html .= '</tr>' . PHP_EOL;
					}
					$html .= '</tbody>' . PHP_EOL . '</table>' . PHP_EOL;
					echo $html;
				}
				?>
            </div>
        </div>
    </div>
</body>
</html>