<?php
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Plan\MonthlyPlan;

$plan = new MonthlyPlan();

if ( isset( $_POST['operation'] ) ) {
    if ( 'set-sum' === $_POST['operation'] ) { // Добавляю категорию
	    if ( '' !== $_POST['sum'] && is_numeric( $_POST['sum'] ) ) {
		    $plan->set_estimated_amount( $_POST['category-id'], $_POST['sum'] );

		    header( 'location: monthly-plan.php' );
		    exit();
	    }
    }

    if ( 'add_cat' === $_POST['operation'] ) { // Добавляю категорию

        if ( '' !== $_POST['category-title'] ) {
	        $title = htmlspecialchars( $_POST['category-title']);
	        if ( '' === $_POST['sum'] ) {
	            $_POST['sum'] = 0;
            }
	        if ( is_numeric( $_POST['sum'] ) ) {
		        $sum = (float) $_POST['sum'];
		        $plan->add_category($title, $sum);

		        header( 'location: monthly-plan.php' );
		        exit();
	        }
        }

    }

    if ( 'del_cat' === $_POST['operation'] ) { // Удаляю категорию
        $plan->delete_category($_POST['category-id']);

	    header( 'location: monthly-plan.php' );
	    exit();
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
            <h2>Планирование на месяц</h2>
            <div class="block">
                <h4>Категории расходов</h4>
				<?php
                echo $plan->render();
                echo '<span style="margin-top: 1em; display: inline-block;"><b>Сумма расходов: <u>' . $plan->get_amount_of_expenses() . '</u></b>';
                ?>
            </div>

            <div class="block">
                <h4>Установить сумму</h4>
                <form method="post">
                    <label><span style="display: block">Выберите категорию:</span>
                        <select name="category-id">
							<?php
							foreach ( $plan->getCategories() as $category ) {
								echo "<option value='{$category['id']}'>{$category['title']}</option>";
							}
							?>
                        </select>
                    </label>
                    <label>
                        <input type="text" name="sum" size="28" placeholder="Введите сумму">
                    </label>
                    <input type="hidden" name="operation" value="set-sum">
                    <input type="submit" value="Назначить">
                </form>
            </div>

            <div class="block">
                <h4>Добавить категорию</h4>
                <form method="post">
                    <label>
                        <input type="text" name="category-title" size="28" placeholder="Введите название категории">
                    </label>
                    <label>
                        <input type="text" name="sum" size="28" placeholder="Введите сумму">
                    </label>
                    <input type="hidden" name="operation" value="add_cat">
                    <input type="submit" value="Добавить">
                </form>
            </div>

            <div class="block">
                <h4>Удалить категорию</h4>
                <form method="post">
                    <label><span style="display: block">Выберите категорию:</span>
                        <select name="category-id">
					        <?php
					        foreach ( $plan->getCategories() as $category ) {
						        echo "<option value='{$category['id']}'>{$category['title']}</option>";
					        }
					        ?>
                        </select>
                    </label>
                    <input type="hidden" name="operation" value="del_cat">
                    <input type="submit" value="Удалить">
                </form>
            </div>

        </div>

    </div>
</div>
</body>
</html>