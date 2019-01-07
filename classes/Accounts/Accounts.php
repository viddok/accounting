<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 05.01.19
 * Time: 19:45
 */

namespace classes\Accounts;

use classes\Accounts\Account\Account;
use classes\Connect\ConnectDB;

class Accounts {
	private $collection;

	private function __construct( array $accounts ) {
		foreach ( $accounts as $account ) {
			$this->collection[ $account['id'] ] = Account::get_account( $account['id'] );
		}
	}

	public static function create_collection() {
		$connect = ConnectDB::connect();

		if ( false === $connect ) {
			return false;
		} else {
			$query = 'SELECT id FROM accounts ORDER BY id';

			$accnt   = $connect->query( $query );
			$accounts = $accnt->fetchAll( \PDO::FETCH_ASSOC );
			unset( $connect );
			if ( ! empty( $accounts ) ) {
				return new self( $accounts );
			}

			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public function getCollection() {
		return $this->collection;
	}
}