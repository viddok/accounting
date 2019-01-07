<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 04.01.19
 * Time: 6:02
 */
session_start();
require_once 'func.php';
forwarding_auth();

use classes\Accounts\Account\Account;
use classes\Accounts\Accounts;
use classes\Users\User\User;

/** @var string */
$errors = null;

if ( isset( $_POST['user_id'] ) && 1 === count( $_POST ) ) {
	$accounts[] = Account::get_account( $_POST['user_id'] );
}

// Обработка денежного перевода
if ( isset( $_POST['account_1st'] ) && isset( $_POST['account_2nd'] ) && isset( $_POST['sum'] ) && is_numeric( $_POST['sum'] ) ) {
	$account = Account::get_account( $_POST['account_1st'] );
	$account->money_order( $_POST['account_2nd'], $_POST['sum'] );

	header( 'location: accounts.php' );
	exit();
}

// Обработчик пополнения счёта
if ( isset( $_POST['operation'])) {
	if ( 'top-up-balance' === $_POST['operation'] ) {
		if ( is_numeric( $_POST['goal-account'] ) && is_numeric( $_POST['sum'] ) ) {
			$account = Account::get_account( $_POST['goal-account'] );
			$account->top_up_account( $_POST['sum'] );

			header( 'location: accounts.php' );
			exit();
		}
	}
}

if ( isset( $_POST['user_id'] ) && isset( $_POST['title'] ) && isset( $_POST['balance'] ) && isset( $_POST['default_balance'] ) ) {
	$result = Account::create_account(
		$_POST['user_id'],
		$_POST['title'],
		$_POST['balance'],
		$_POST['default_balance']
	);

	if ( true === $result ) {
		echo 'Счёт создан';
	} else {
		echo 'ERROR';
	}

	header( 'location: accounts.php' );
	exit();
}

/* Создание таблицы счетов */
function create_accounts_table( Account $account, User $user, int $count ) {
	$accounts_table = '';
	$credit_text    = ( $account->getbalance() - $account->getDefaultBalance() >= 0 ) ? 'Нет задолженности' : abs( $account->getbalance() - $account->getDefaultBalance() );
	$text           = ( 0 === $account->getDefaultBalance() ) ? '-' : $credit_text;

	$accounts_table .= '<tr>';
	$accounts_table .= "<td>$count</td>";
	$accounts_table .= "<td>{$account->getTitle()}</td>";
	if ( is_admin() ) {
		$accounts_table .= "<td>{$user->getName()}</td>";
	}
	$accounts_table .= "<td>{$account->getbalance()}</td>";
	$accounts_table .= "<td>$text</td>";
	$accounts_table .= '</tr>';

	return $accounts_table;
}

$accounts = Accounts::create_collection(); //Объекта коллекции счетов
$count          = 1;
$accounts_table = '';
if ( is_admin() ) {
	foreach ( $accounts->getCollection() as $account ) {
		/* @var $account Account */
		$user           = User::create_user( $account->getUserId() );
		$accounts_table .= create_accounts_table( $account, $user, $count );
		$count ++;
	}
} else {
	foreach ( $accounts->getCollection() as $account ) {
		/* @var $account Account */
		$user = User::create_user( $account->getUserId() );
		if ( $_SESSION['current_user']['id'] === $user->getId() ) {
			$accounts_table .= create_accounts_table( $account, $user, $count );
			$count ++;
		}
	}
}
/* /Создание таблицы счетов */
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

        <div class="container">

            <div class="block">
                <table border="1" cellspacing="0" width="400px">
                    <tbody>
                    <tr>
                        <td>№</td>
                        <td>Название</td>
                        <?php if ( is_admin() ): ?>
                        <td>Владелец</td>
                        <?php endif; ?>
                        <td>Баланс</td>
                        <td>Задолженость</td>
                    </tr>
                    <?php
                    echo $accounts_table;
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="block">
            <h4>Перевод средств</h4>
            <form method="post">
                <label><span>Начальный счёт:</span>
                    <select name="account_1st">
                        <?php
                        foreach ( $accounts->getCollection() as $account ) {
	                        /* @var $account Account */
	                        $user = User::create_user( $account->getUserId() );
	                        if ( is_admin() ) {
		                        echo "<option value='{$account->getId()}'>{$account->getTitle()}</option>";
                            } else {
		                        if ( $_SESSION['current_user']['id'] === $user->getId() ) {
			                        echo "<option value='{$account->getId()}'>{$account->getTitle()}</option>";
		                        }
	                        }
                        }
                        ?>
                    </select>
                </label>
                <label><span>Целевой счёт:</span>
                    <select name="account_2nd">
			            <?php
			            foreach ( $accounts->getCollection() as $account ) {
				            /* @var $account Account */
				            $user = User::create_user( $account->getUserId() );
                            echo "<option value='{$account->getId()}'>{$user->getName()}/{$account->getTitle()}</option>";
			            }
			            ?>
                    </select>
                </label>
                <label><span>Сумма перевода:</span>
                    <input type="text" name="sum" size="28">
                </label>
                <input type="submit" value="Перевести">
            </form>
            </div>

            <div class="block">
                <h4>Пополнить балланс</h4>
                <form method="post">
                    <label><span>Целевой счёт:</span>
                        <select name="goal-account">
				            <?php
				            foreach ( $accounts->getCollection() as $account ) {
					            /* @var $account Account */
					            $user = User::create_user( $account->getUserId() );
					            if ( is_admin() ) {
						            echo "<option value='{$account->getId()}'>{$account->getTitle()}</option>";
					            } else {
						            if ( $_SESSION['current_user']['id'] === $user->getId() ) {
							            echo "<option value='{$account->getId()}'>{$account->getTitle()}</option>";
						            }
					            }
				            }
				            ?>
                        </select>
                    </label>
                    <label><span>Сумма пополнения:</span>
                        <input type="text" name="sum" size="28">
                    </label>
                    <input type="hidden" name="operation" value="top-up-balance">
                    <input type="submit" value="Пополнить">
                </form>
            </div>

	        <?php if ( is_admin() ): ?>
            <div class="block">
                <h4>Создать счёт</h4>
                <form method="post">
                    <label>
                        <input type="text" name="user_id" size="50" placeholder="Введите id пользователя">
                    </label>
                    <label>
                        <input type="text" name="title" size="50" placeholder="Введите название счёта">
                    </label>
                    <label>
                        <input type="text" name="balance" size="50" placeholder="Введите текущий баланс">
                    </label>
                    <label>
                        <input type="text" name="default_balance" size="50" placeholder="Введите стартовый баланс">
                    </label>
                    <input type="submit" value="Добавить">
                </form>
            </div>
	        <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>