<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 01.01.19
 * Time: 20:09
 */

namespace classes\Users;

use classes\Connect\ConnectDB;
use classes\Users\User\User;

class Users {
	private $collection;

	private function __construct( array $users ) {
		foreach ( $users as $user ) {
			$this->collection[ $user['id'] ] = User::create_user( $user['id'] );
		}
	}

	public static function create_collection() {
		$connect = ConnectDB::connect();

		if ( false === $connect ) {
			return false;
		} else {
			$query = 'SELECT id FROM users';

			$usr   = $connect->query( $query );
			$users = $usr->fetchAll( \PDO::FETCH_ASSOC );
			unset( $connect );
			if ( ! empty( $users ) ) {
				return new self( $users );
			}

			return false;
		}
	}
}