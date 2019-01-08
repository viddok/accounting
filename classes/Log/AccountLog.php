<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 08.01.19
 * Time: 5:38
 */

namespace classes\Log;

use classes\Connect\ConnectDB;

class AccountLog {
	/**
	 * @var array mixed
	 */
	private $log;

	public function __construct() {
		$connect = ConnectDB::connect();
		$query =   'SELECT accounts_log.id, operation, sum, description, date, accounts.name as account, users.name as owner
					FROM accounts_log
					LEFT JOIN accounts ON accounts_log.account_id = accounts.id
					LEFT JOIN users ON accounts_log.user_id = users.id
					WHERE date LIKE \'%01-2019\'
					ORDER BY accounts_log.id';
		$tmp = $connect->query($query);
		$this->log = $tmp->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * @param array mixed $action
	 */
	public static function addLog( $action ) {
		$connect = ConnectDB::connect();
		$query =   'INSERT INTO accounts_log (account_id, user_id, operation, sum, description, date)
					VALUES (:account_id, :user_id, :operation, :sum, :description, :date)';
		$tmp = $connect->prepare($query);
		$tmp->execute([
			'account_id' => $action['account_id'],
			'user_id' => $action['user_id'],
			'operation' => $action['operation'],
			'sum' => $action['sum'],
			'description' => htmlspecialchars( $action['description'] ),
			'date' => $action['date'],
		]);
	}

	/**
	 * @return mixed
	 */
	public function getLog() {
		return $this->log;
	}

	/** @return string */
	public function render(): string {
		$table ='';
		foreach ($this->log as $action) {
			$table .= '<tr>';
			$table .= '<td>' . $action['id'] . '</td>';
			$table .= '<td>' . $action['date'] . '</td>';
			$table .= '<td>' . $action['owner'] . '</td>';
			$table .= '<td>' . $action['account'] . '</td>';
			$table .= '<td>' . $action['operation'] . '</td>';
			$table .= '<td>' . $action['sum'] . '</td>';
			$table .= '<td>' . $action['description'] . '</td>';
			$table .= '</tr>';
		}
		return $table;
	}
}