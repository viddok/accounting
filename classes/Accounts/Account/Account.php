<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 03.01.19
 * Time: 21:40
 */

namespace classes\Accounts\Account;

use classes\Connect\ConnectDB;
use classes\Log\AccountLog;
use classes\Users\User\User;

class Account {
	private $id, $user_id;
	private $title;
	private $balance;
	private $default_balance;

	public function __construct( array $args = null ) {
		$this->id              = $args['id'];
		$this->user_id         = $args['user_id'];
		$this->title           = $args['title'];
		$this->balance         = $args['balance'];
		$this->default_balance = $args['default_balance'];
	}

	public static function create_account( $user_id, $title, $balance, $default_balance ) {
		if ( is_numeric( $user_id ) && is_numeric( $balance ) && ( is_numeric( $default_balance ) || '' === $default_balance ) ) {

			if ( '' === $default_balance ) {
				$default_balance = 0;
			}

			$connect = ConnectDB::connect();

			$query  = "INSERT INTO accounts ( user_id, name, balance, def_balance ) VALUES ( $user_id, '$title', $balance, $default_balance )";
			$result = $connect->exec( $query );

			if ( 1 == $result ) {
				return true;
			}

			return false;
		}

		return false;
	}

	public function money_order( $id, $sum, $commission = 0 ) {
		if ( is_numeric( $id ) && is_numeric( $sum ) && ( $this->balance >= $sum ) ) {
			$account              = self::get_account( $id );
			$user = User::create_user( $this->user_id );
			$description1 = "Перевод со счёта: {$user->getName()}/$this->title.";
			$result_1st_operation = $account->top_up_account( $sum, $description1 );

			$result_2nd_operation = $this->withdraw_from_account( $sum + $commission );
			if ( true === $result_1st_operation && true === $result_2nd_operation ) {
				return true;
			}

			return false;
		}

		return false;
	}

	public static function get_account( $id ) {
		if ( is_numeric( $id ) ) {
			$connect = ConnectDB::connect();

			try {
				$query = 'SELECT * FROM accounts WHERE id = ?';
				$accnt = $connect->prepare( $query );
				$accnt->execute( [ $id ] );
				$account = $accnt->fetchAll( \PDO::FETCH_ASSOC );

				$args = array(
					'id'              => $id,
					'user_id'         => $account[0]['user_id'],
					'title'           => htmlspecialchars( $account[0]['name'] ),
					'balance'         => $account[0]['balance'],
					'default_balance' => $account[0]['def_balance'],
				);

				return new self( $args );
			} catch ( \PDOException $e ) {
				return false;
			}
		}

		return false;
	}

	public function top_up_account( $sum, string $description ) {
		if ( is_numeric( $sum ) && $sum > 0 ) {
			$this->balance += $sum;
			$connect       = ConnectDB::connect();

			$query = 'UPDATE accounts SET balance = ? WHERE id = ?';
			$accnt = $connect->prepare( $query );
			$accnt->execute( [ $this->balance, $this->id ] );

			$action = array(
				'account_id' => $this->id,
				'user_id' => $this->user_id,
				'operation' => 'Пополнение баланса',
				'sum' => $sum,
				'description' => $description,
				'date' => date('d-m-Y'),
			);
			AccountLog::addLog($action);

			return true;
		}

		return false;
	}

	public function withdraw_from_account( $sum ) {
		if ( is_numeric( $sum ) && $sum > 0 && $this->balance >= $sum ) {
				$this->balance -= $sum;
				$connect       = ConnectDB::connect();

				$query = 'UPDATE accounts SET balance = ? WHERE id = ?';
				$accnt = $connect->prepare( $query );
				$accnt->execute( [ $this->balance, $this->id ] );

				return true;
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @return mixed
	 */
	public function getBalance() {
		return $this->balance;
	}

	/**
	 * @return mixed
	 */
	public function getDefaultBalance() {
		return $this->default_balance;
	}

}