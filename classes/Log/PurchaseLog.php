<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 11.01.19
 * Time: 5:55
 */

namespace classes\Log;

use classes\Connect\ConnectDB;
use classes\Accounts\Account\Account;

class PurchaseLog {

	/**
	 * @param int $account_id
	 * @param int $user_id
	 * @param int $category_id
	 * @param float $sum
	 * @param string $description
	 *
	 * @return bool
	 */
	public static function add_log( int $account_id, int $user_id, int $category_id, float $sum, string $description ): bool {
		$connect = ConnectDB::connect();

		$query = 'INSERT INTO purchases_log (account_id, user_id, category_id, sum, description, date)
					VALUES (:account_id, :user_id, :category_id, :sum, :description, :date)';

		$args = array(
			':account_id'  => $account_id,
			':user_id'     => $user_id,
			':category_id' => $category_id,
			':sum'         => $sum,
			':description' => $description,
			':date'        => date( 'd-m-Y' ),
		);

		try {
			$tmp = $connect->prepare( $query );
			$tmp->execute( $args );
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}

		return true;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function del_log( int $id ): bool {
		$connect = ConnectDB::connect();

		/* Возвращение средств на счёт */
		$query = 'SELECT account_id, category_id, sum FROM purchases_log WHERE id = :id';

		try {
			$tmp = $connect->prepare( $query );
			if ( ! $tmp->execute( [ ':id' => $id ] ) ) {
				return false;
			}
			$log = $tmp->fetch( \PDO::FETCH_ASSOC );
			if ( ! $log ) {
				return false;
			}
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}


		$account = Account::get_account( $log['account_id'] );
		$account->top_up_account( $log['sum'], 'Отмена покупки' );
		/* /Возвращение средств на счёт */

		/* Корректировка потраченной суммы в расходах */
		$query_select = 'SELECT amount_spent FROM expenses WHERE id = :id'; // Запрос для получения текущей суммы потраченных денег
		$query_update = 'UPDATE expenses SET amount_spent = :amount_spent WHERE id = :id'; // Запрос для обновления суммы потраченных денег

		try {
			$tmp = $connect->prepare( $query_select );
			if ( ! $tmp->execute( [ ':id' => $log['category_id'] ] ) ) {
				return false;
			}
			$category = $tmp->fetch( \PDO::FETCH_ASSOC );
			if ( ! $category ) {
				return false;
			}

			$amount_spent = $category['amount_spent'] - $log['sum']; // Корректировка суммы потраченных денег

			$tmp = $connect->prepare( $query_update );
			if ( ! $tmp->execute( [ ':id' => $log['category_id'], ':amount_spent' => $amount_spent ] ) ) {
				return false;
			}
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}
		/* /Корректировка потраченной суммы в расходах */

		/* Удаление лога из базы данных */
		$query = 'DELETE FROM purchases_log WHERE id = :id';

		try {
			$tmp = $connect->prepare( $query );
			if ( ! $tmp->execute( [ ':id' => $id ] ) ) {
				return false;
			}
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}

		/* /Удаление лога из базы данных */

		return true;
	}

	/**
	 * @param string $category
	 * @param string $date
	 * @param int $quantity
	 *
	 * @return mixed
	 */
	public static function get_log( string $date = 'current_month', string $category = '', int $quantity = 30 ) {
		$connect = ConnectDB::connect();

		$query = 'SELECT
						purchases_log.id AS id,
						purchases_log.date,
						users.name AS user_name,
						accounts.name AS account_title,
						expenses.title AS category_title,
						sum,
						description
					FROM purchases_log
					INNER JOIN users ON purchases_log.user_id = users.id
					INNER JOIN accounts ON purchases_log.account_id = accounts.id
					INNER JOIN expenses ON purchases_log.category_id = expenses.id';

		if ( 'current_month' === $date ) {
			$date  = date( 'm-Y' );
			$where = ' WHERE purchases_log.date LIKE \'__-' . $date . '\'';
		} else {
			$pattern = '~[0-3][0-9]-20[1-2][0-9]~';
			if ( 1 != preg_match( $pattern, $date ) ) {
				return false;
			}
			$where = ' WHERE purchases_log.date LIKE \'__-' . $date . '\'';
		}

		if ( '' !== $category && is_numeric( $category ) ) {
			$where .= ' AND category_id = ' . $category;
		}

		$limit = '';
		if ( is_numeric( $quantity ) ) {
			$limit = ' LIMIT ' . $quantity;
		}

		$query .= $where . 'ORDER BY purchases_log.id DESC' . $limit;

		try {
			$tmp = $connect->query( $query );
			$log = $tmp->fetchAll( \PDO::FETCH_ASSOC );
			if ( ! $log ) {
				return false;
			}
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}

		return $log;
	}
}