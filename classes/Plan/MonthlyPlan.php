<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 08.01.19
 * Time: 21:00
 */

namespace classes\Plan;

use classes\Connect\ConnectDB;


class MonthlyPlan {

	protected $categories;
	protected $date;

	/** @var  \PDO */
	private $connect;

	public function __construct() { // Конструктор
		$this->date = date( 'm-Y' );

		$this->connect = ConnectDB::connect();

		try {
			$query = 'SELECT * FROM plan WHERE date = :date';
			$tmp = $this->connect->prepare( $query );
			$tmp->execute( [':date' => $this->date] );
			$this->categories = $tmp->fetchAll( \PDO::FETCH_ASSOC );
		} catch( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();
		}
	}

	public function add_category( string $title, float $sum ): bool { // Добавляет новую категорию
		$id   = null;
		$copy = false;

		foreach ( $this->categories as $category ) { // Проверка на добавление дубликата
			if ( $category['category'] === $title && $category['sum'] == $sum && $category['date'] === $this->date ) {
				$copy = true;
			}
		}

		if ( ! $copy ) {
			$query = 'INSERT INTO plan ( category, sum, date ) VALUES ( :category, :sum, :date ) RETURNING id';// Добавление категории в базу данных и получаем id
			try {
				$tmp  = $this->connect->prepare( $query );
				$args = array(
					':category' => htmlspecialchars( $title ),
					':sum'      => $sum,
					':date'     => $this->date,
				);
				if ( ! $tmp->execute( $args ) ) {
					return false;
				}
				$arr = $tmp->fetchAll( \PDO::FETCH_ASSOC );
				$id  = $arr[0]['id'];
			} catch ( \PDOException $e ) {
				echo 'Ошибка выполнения запроса ' . $e->getMessage();

				return false;
			}

			$number                      = count( $this->categories );
			$this->categories[ $number ] = array(
				'id'       => $id,
				'category' => $title,
				'sum'      => $sum,
				'date'     => $this->date,
			);

			return true;
		}

		return false;
	}

	public function delete_category( int $id ): bool { // Удаляет выбранную категорию
		$true_id      = false; //статус наличия удаляемой категории
		$category_key = null; // ключ удаляемой категории

		foreach ( $this->categories as $key => $category ) { // Проверка на добавление дубликата
			if ( $category['id'] == $id ) {
				$true_id      = true;
				$category_key = $key;
			}
		}

		if ( $true_id ) {
			$query = 'DELETE FROM plan WHERE id = :id'; // Удаление категории из базы данных
			try {
				$tmp  = $this->connect->prepare( $query );
				$args = array(
					':id' => $id,
				);
				if ( ! $tmp->execute( $args ) ) {
					return false;
				}
			} catch ( \PDOException $e ) {
				echo 'Ошибка выполнения запроса ' . $e->getMessage();

				return false;
			}

			unset( $this->categories[ $category_key ] ); // Удаление категории из поля объекта

			return true;
		}

		return false;
	}

	public function set_sum(){ // Назначает сумму указанной категории

	}

	public function copy_previous_plan(){ // Копирует план с предыдущего месяца

	}
}