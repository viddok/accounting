<?php
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Accounts\Account\Account;
use classes\Accounts\Accounts;
use classes\Expenses\Expenses;
use classes\Users\User\User;

$expenses = new Expenses();
$accounts = Accounts::create_collection();
$errors   = array(
	'',
	'Ошибка создания покупки, проверьте наличие средств на счету',
	'Ошибка выделения средст, проверьте наличие средств на счету.'
);

if ( isset( $_POST['operation'] ) ) {
	if ( 'set_allocated_amount' === $_POST['operation'] ) { // Добавляю категорию
		if ( '' !== $_POST['sum'] && is_numeric( $_POST['sum'] ) ) {
			$set_allocated_amount_result = $expenses->set_allocated_amount( $_POST['category-id'], $_POST['sum'], $_POST['type_operation'] );
			if ( $set_allocated_amount_result ) {
				header( 'location: expenses.php' );
			} else {
				header( 'location: expenses.php?error_code=2' );
			}
			exit();
		}
	}

	if ( 'add_purchase' === $_POST['operation'] ) { // Добавляю категорию
		if ( '' !== $_POST['sum'] && is_numeric( $_POST['sum'] ) ) {
			$add_purchase_result = $expenses->add_purchase( $_POST['category-id'], $_POST['account_id'], $_POST['sum'], $_POST['description'] );
			if ( $add_purchase_result ) {
				header( 'location: expenses.php' );
			} else {
				header( 'location: expenses.php?error_code=1' );
			}
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
	<title>Расходы</title>
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
			<?php if ( isset( $_GET['error_code'] ) && 0 != $_GET['error_code'] ) {
				echo '<span class="error">' . $errors[ $_GET['error_code'] ] . '.</span>';
			}
			?>
			<h2>Расходы за этот месяц</h2>
			<div class="block">
                <div style="margin-bottom: 1em;"><b>Доступная сумма: <u><?php echo $accounts->get_total_amount(); ?></u></b></div>
                <div style="margin-bottom: 1em;">
                    <b>Нераспределённая сумма:
                        <u><?php echo $accounts->get_total_amount() - ( $expenses->some_total_amount( 'allocated_amount' ) - $expenses->some_total_amount( 'amount_spent' ) ); ?></u>
                    </b>
                </div>
                <h4>Категории расходов</h4>
                <?php echo $expenses->render();	?>
			</div>

            <div class="block">
                <h4>Добавить покупку</h4>
                <form method="post">
                    <label><span style="display: block">Выберите категорию:</span>
                        <select name="category-id">
							<?php
							foreach ( $expenses->getCategories() as $category ) {
								echo "<option value='{$category['id']}'>{$category['title']}</option>";
							}
							?>
                        </select>
                    </label>
                    <label><span style="display: block">Выберите счёт:</span>
                        <select name="account_id">
			                <?php
			                foreach ( $accounts->getCollection() as $account ) {
				                /* @var $account Account */
				                $selected = '';
				                if ( $account->getTitle() === 'Кошелёк' ) {
					                $selected = 'selected';
				                }
				                $user = User::create_user( $account->getUserId() );
				                if ( is_admin() ) {
					                echo "<option value='{$account->getId()}'>{$account->getTitle()}</option>";
				                } else {
					                if ( $_SESSION['current_user']['id'] === $user->getId() ) {
						                echo "<option value='{$account->getId()}' $selected>{$account->getTitle()}</option>";
					                }
				                }
			                }
			                ?>
                        </select>
                    </label>
                    <label>
                        <input type="text" name="sum" size="28" placeholder="Введите сумму">
                    </label>
                    <label>
                        <input type="text" name="description" placeholder="Что купили?" />
                    </label>
                    <input type="hidden" name="operation" value="add_purchase">
                    <input type="submit" value="Выделить">
                </form>
            </div>

            <div class="block">
                <h4>Выделить деньги</h4>
                <form method="post">
                    <label><span style="display: block">Выберите категорию:</span>
                        <select name="category-id">
							<?php
							foreach ( $expenses->getCategories() as $category ) {
								echo "<option value='{$category['id']}'>{$category['title']}</option>";
							}
							?>
                        </select>
                    </label>
                    <label>
                        <input type="text" name="sum" size="28" placeholder="Введите сумму">
                    </label>
                    <label>
                        <input type="radio" name="type_operation" value="add" checked="checked" />
                         Добавить
                    </label>
                    <label>
                        <input type="radio" name="type_operation" value="change" />
                         Заменить
                    </label>
                    <input type="hidden" name="operation" value="set_allocated_amount">
                    <input type="submit" value="Выделить">
                </form>
            </div>
		</div>

	</div>
</div>
</body>
</html>