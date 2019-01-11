<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 11.01.19
 * Time: 5:55
 */

namespace classes\Log;

use classes\Connect\ConnectDB;

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
}