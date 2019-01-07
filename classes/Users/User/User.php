<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 01.01.19
 * Time: 15:03
 */

namespace classes\Users\User;

use classes\Connect\ConnectDB;

class User {
	private $id;
	private $name;
	private $role;

	private function __construct( $id, string $name, string $role) {
		$this->id = $id;
		$this->name = $name;
		$this->role = $role;
	}

	public static function create_user( $id ) {

		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$connect = ConnectDB::connect();

		if ( false === $connect ) {
			return false;
		} else {
			$query = 'SELECT
							name, role
						FROM
							users
						WHERE
							id = ' . $id;

			$usr   = $connect->query( $query );
			$user  = $usr->fetch( \PDO::FETCH_ASSOC );
			unset($connect);
			if ( ! empty( $user ) ) {
				return new self( $id, $user['name'], $user['role'] );
			}
			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getRole(): string {
		return $this->role;
	}
}