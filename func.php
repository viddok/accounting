<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 04.01.19
 * Time: 6:26
 */

/** Автозагрузка классов */
function __autoload( $classname ) {
	$classname = str_replace( '\\', '/', $classname );
	require_once( __DIR__ . "/$classname.php" );
}

/** Переадресация пользователя */
function forwarding_auth() {
	if ( ! isset( $_SESSION['current_user'] ) ) {
		header( "location: authorization.php" );
		exit();
	}

	return false;
}

function is_admin(): bool {
	if ( isset( $_SESSION['current_user'] ) ) {
		if ( 'admin' === $_SESSION['current_user']['role'] ) {
			return true;
		}
	}

	return false;
}