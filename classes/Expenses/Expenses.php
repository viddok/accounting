<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 10.01.19
 * Time: 7:47
 */

namespace classes\Expenses;

use classes\Connect\ConnectDB;

class Expenses {
	protected $categories;
	protected $date;

	/** @var  \PDO */
	private $connect;

	public function __construct() { // Конструктор
		$this->date = date( 'm-Y' );

		$this->connect = ConnectDB::connect();

		try {
			$query = 'SELECT * FROM expenses WHERE date = :date ORDER BY id';
			$tmp = $this->connect->prepare( $query );
			$tmp->execute( [':date' => $this->date] );
			$this->categories = $tmp->fetchAll( \PDO::FETCH_ASSOC );
		} catch( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();
		}
	}

	public function set_allocated_amount(int $id, int $allocated_amount, string $type_operation = 'add'): bool{
		foreach ( $this->categories as $key => $category ) {
			if ( $category['id'] == $id ) { // Проверка Наличия категории
				if ( 'add' === $type_operation ) {
					$allocated_amount = $this->categories[ $key ]['allocated_amount'] + $allocated_amount;
				} elseif ( 'change' !== $type_operation ) {
					return false;
				}

				$query = 'UPDATE expenses SET allocated_amount = :allocated_amount WHERE id = :id';
				$args  = array(
					':id'  => $id,
					':allocated_amount' => $allocated_amount,
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

				$this->categories[ $key ]['allocated_amount'] = $allocated_amount; // Обновление суммы в объекте

				return true;
			}
		}

		return false;
	}

	public function add_purchase(int $id, int $cost): bool{
		foreach ( $this->categories as $key => $category ) {
			if ( $category['id'] == $id ) { // Проверка Наличия категории

				$query = 'UPDATE expenses SET amount_spent = :amount_spent WHERE id = :id';
				$amount_spent = $this->categories[ $key ]['amount_spent'] + $cost;
				$args  = array(
					':id'  => $id,
					':amount_spent' => $amount_spent,
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

				$this->categories[ $key ]['amount_spent'] = $amount_spent; // Обновление суммы в объекте

				return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return $this->categories;
	}

	/* Форматирование данных для вывода в виде таблицы */
	public function render() {
		$count = 1;
		$html  = '<table border="1" cellspacing="0" width="800px">' . PHP_EOL . '<tbody>' . PHP_EOL;
		$html .= '<tr>' . PHP_EOL;
		$html .= '<td>№</td>' . PHP_EOL;
		$html .= '<td>Категория расходов</td>' . PHP_EOL;
		$html .= '<td>Планируемая сумма</td>' . PHP_EOL;
		$html .= '<td>Выделенная сумма</td>' . PHP_EOL;
		$html .= '<td>Потрачено</td>' . PHP_EOL;
		$html .= '<td>Остаток</td>' . PHP_EOL;

		$html .= '</tr>' . PHP_EOL;

		foreach ( $this->categories as $key => $category ) {
			$html .= '<tr>' . PHP_EOL;
			$html .= '<td>' . $count++ . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['title'] . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['estimated_amount'] . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['allocated_amount'] . '</td>' . PHP_EOL;
			$html .= '<td>' . $category['amount_spent'] . '</td>' . PHP_EOL;
			$html .= '<td>' . ($category['allocated_amount'] - $category['amount_spent']) . '</td>' . PHP_EOL;
			$html .= '</tr>' . PHP_EOL;
		}
		$html .= '</tbody>' . PHP_EOL . '</table>' . PHP_EOL;

		return $html;
	}

}