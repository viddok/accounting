<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 01.01.19
 * Time: 15:05
 */

namespace classes\Connect;


abstract class ConnectDB {
	public static function connect() {
		try {
			$pdo = new \PDO('pgsql:host=localhost;dbname=accounting','jack','Alxi9mik');
			return $pdo;
		} catch (\PDOException $e) {
			return false;
		}
	}
}