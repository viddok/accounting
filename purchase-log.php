<?php
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Expenses\Expenses;
use classes\Log\PurchaseLog;

$expenses = new Expenses();
$date     = 'current_month';
$cat_id   = '';

if ( isset( $_POST['operation'] ) ) {
	if ( 'filter' === $_POST['operation'] ) {
		$date   = $_POST['month'] . '-' . $_POST['year'];
		$cat_id = $_POST['category_id'];
	}

	if ( 'del-log' === $_POST['operation'] ) {
		if ( is_numeric( $_POST['log-id'] ) ) {
			PurchaseLog::del_log( $_POST['log-id'] );

			header( 'location: purchase-log.php' );
			exit();
		}
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
    <title>Логи</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .filter {
            text-align: center;
            padding: 2px;
        }

        .filter label {
            display: inline-block;
            margin-left: 10px;
        }

        .filter select {
            min-width: 60px;
        }

        .filter input:last-child {
            margin-left: 10px;
        }
    </style>
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
                <h4>Отменить покупку</h4>
                <form method="post">
                    <input type="text" name="log-id" placeholder="Введите ID покупки" size="20">
                    <input type="hidden" name="operation" value="del-log">
                    <input type="submit" value="Отменить">
                </form>
            </div>

            <div class="block">
                <div class="filter row">
                    <form method="post">
                        <label>Категория:
                            <select name="category_id">
                                <option value="">Все</option>
						        <?php
						        foreach ( $expenses->getCategories() as $category ) {
							        $selected = '';
							        if ( $category['id'] == $cat_id ) {
								        $selected = ' selected';
							        }
							        echo "<option value='{$category['id']}' $selected>{$category['title']}</option>";
						        }
						        ?>
                            </select>
                        </label>
                        <label>Месяц
                            <select name="month">
						        <?php
						        $val = null;
						        for ( $i = 1; $i < 13; $i ++ ) {
							        if ( $i < 10 ) {
								        $val = '0' . $i;
							        } else {
								        $val = $i;
							        }
							        $selected = '';
							        if ( $i == $_POST['month'] ) {
								        $selected = 'selected';
							        }
							        echo "<option value='$val' $selected>$val</option>";
						        }
						        ?>
                            </select>
                        </label>
                        <label>Год:
                            <select name="year">
						        <?php
						        for ( $i = date( 'Y' ); $i > ( date( 'Y' ) - 4 ); $i -- ) {
							        $selected = '';
							        if ( $i == $_POST['year'] ) {
								        $selected = 'selected';
							        }
							        echo "<option value='$i' $selected>$i</option>";
						        }
						        ?>
                            </select>
                        </label>
                        <input type="hidden" name="operation" value="filter">
                        <input type="submit" value="Применить">
                    </form>
                </div>
		        <?php
		        $log = PurchaseLog::get_log( $date, $cat_id );
				if ( true == $log ) {
					$html = '<table border="1" cellspacing="0" cellpadding="2px" style="text-align:center" width="100%">' . PHP_EOL;
					$html .= '<tbody>' . PHP_EOL;
					$html .= '<tr style="font-weight: bold">' . PHP_EOL;
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