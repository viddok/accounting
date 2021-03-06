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
			$query = 'SELECT * FROM expenses WHERE date = :date ORDER BY title';
			$tmp   = $this->connect->prepare( $query );
			$tmp->execute( [':date' => $this->date] );
			$this->categories = $tmp->fetchAll( \PDO::FETCH_ASSOC );
		} catch( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();
		}
	}

	public function add_category( string $title, float $estimated_amount ): bool { // Добавляет новую категорию
		$id   = null;
		$copy = false;

		foreach ( $this->categories as $category ) { // Проверка на добавление дубликата
			if ( $category['title'] === $title && $category['estimated_amount'] == $estimated_amount && $category['date'] === $this->date ) {
				$copy = true;
			}
		}

		if ( ! $copy ) {
			$query = 'INSERT INTO expenses ( title, estimated_amount, date ) VALUES ( :title, :estimated_amount, :date ) RETURNING id';// Добавление категории в базу данных и получаем id
			try {
				$tmp  = $this->connect->prepare( $query );
				$args = array(
					':title'            => htmlspecialchars( $title ),
					':estimated_amount' => $estimated_amount,
					':date'             => $this->date,
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
				'title' => $title,
				'estimated_amount'      => $estimated_amount,
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
			$query = 'DELETE FROM expenses WHERE id = :id'; // Удаление категории из базы данных
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

	public function set_estimated_amount( int $id, float $estimated_amount ): bool { // Назначает сумму указанной категории

		foreach ( $this->categories as $key => $category ) {
			if ( $category['id'] == $id ) { // Проверка Наличия категории
				$query = 'UPDATE expenses SET estimated_amount = :estimated_amount WHERE id = :id';
				$args  = array(
					':id'  => $id,
					':estimated_amount' => $estimated_amount,
				);

				try {
					$tmp = $this->connect->prepare( $query );
					if ( ! $tmp->execute( $args ) ) { // Обновление суммы в базе данных
						return false;
					}
				} catch ( \PDOException $e ) {
					echo 'Ошибка выполнения запроса ' . $e->getMessage();

					return false;
				}

				$this->categories[ $key ]['estimated_amount'] = $estimated_amount; // Обновление суммы в объекте

				return true;
			}
		}

		return false;
	}

	public function copy_previous_plan() { // Копирует план с предыдущего месяца
		$temp_categories = null;
		$previous_date   = $this->previous_date(); //Получаю предыдущий месяц

		/* Очищаю список категорий этого месяца */
		$query = 'DELETE FROM expenses WHERE date = :date'; // Удаление категории из базы данных
		try {
			$tmp  = $this->connect->prepare( $query );
			$args = array(
				':date' => $this->date,
			);
			if ( ! $tmp->execute( $args ) ) {
				return false;
			}
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();

			return false;
		}
		unset( $this->categories ); // Очищаю список категорий объекта
		/* /Очищаю список категорий этого месяца */

		/* Получаю список категорий прошлого месяца */
		$this->connect = ConnectDB::connect();
		$query         = 'SELECT * FROM expenses WHERE date = :date';

		try {
			$tmp = $this->connect->prepare( $query );
			if ( ! $tmp->execute( [ ':date' => $previous_date ] ) ) {
				return false;
			}
			$temp_categories = $tmp->fetchAll( \PDO::FETCH_ASSOC );
		} catch ( \PDOException $e ) {
			echo 'Ошибка выполнения запроса приполучении списка категорий прошлого месяца ' . $e->getMessage();

			return false;
		}
		/* /Получаю список категорий прошлого месяца */

		/* Создаю копии категорий для этого месяца */
		foreach ( $temp_categories as $key => $category ) {
			$query = 'INSERT INTO expenses ( title, estimated_amount, date ) VALUES ( :title, :estimated_amount, :date ) RETURNING id';
			$args  = array(
				':title' => $category['title'],
				':estimated_amount'      => $category['estimated_amount'],
				':date'     => $this->date,
			);

			try {
				$tmp = $this->connect->prepare( $query );
				if ( ! $tmp->execute( $args ) ) {
					return false;
				}
				$id                 = $tmp->fetchAll( \PDO::FETCH_ASSOC )[0]['id'];
				$this->categories[] = array(
					'id'       => $id,
					'title' => $category['title'],
					'estimated_amount'      => $category['estimated_amount'],
					'date'     => $this->date,
				);
			} catch ( \PDOException $e ) {
				echo 'Ошибка выполнения запроса при копировании категорий ' . $e->getMessage();

				return false;
			}
		}

		/* /Создаю копии категорий для этого месяца */

		return true;
	}

	/* Получение даты предыдущего месяца */
	public function previous_date() {
		$previous_month = null;
		$previous_year  = null;

		$date_arr = explode( '-', $this->date );
		list( $month, $year ) = $date_arr;

		if ( '01' === $month ) {
			return '12-' . -- $year;
		} else {
			if ( $month < 10 ) {
				return '0' . -- $month . '-' . $year;
			}
			return -- $month . '-' . $year;
		}
	}

	/* Подсчёт суммы расходов */
	/**
	 * @return float
	 */
	public function get_amount_of_expenses(): float {
		$all_costs = null;
		foreach ( $this->categories as $category ) {
			$all_costs += $category['estimated_amount'];
		}

		$all_costs = (null === $all_costs) ? 0 : $all_costs;

		return $all_costs;
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return $this->categories;
	}

	/* Форматирование данных для вывода в виде таблицы */
	public function render() {
		if ( empty( $this->categories ) ) {
			$this->copy_previous_plan();
		}
		$count = 1;
		$html  = '<table border="1" cellspacing="0" width="400px">' . PHP_EOL . '<tbody>' . PHP_EOL;
		foreach ( $this->categories as $key => $category ) {
			$html .= '<tr>' . PHP_EOL;
			$html .= '<td>' . $count++ . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['title'] . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['estimated_amount'] . '</td>' . PHP_EOL;
			$html .= '</tr>' . PHP_EOL;
		}
		$html .= '</tbody>' . PHP_EOL . '</table>' . PHP_EOL;

		return $html;
	}
}