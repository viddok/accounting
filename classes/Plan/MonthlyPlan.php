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

	/** @var  array int */
	protected $categories;
	protected $date;

	/** @var  \PDO */
	private $connect;

	public function __construct() { // Конструктор
		$this->date = date('m-Y');

		$this->connect = ConnectDB::connect();

		try {
			$query = 'SELECT * FROM plan WHERE date = :date';
			$tmp = $this->connect->prepare($query);
			$tmp->execute(['date' => $this->date]);
			$this->categories = $tmp->fetchAll(\PDO::FETCH_ASSOC);
		} catch( \PDOException $e ) {
			echo 'Ошибка выполнения запроса ' . $e->getMessage();
		}
	}

	public function add_category(){ // Добавляет новую категорию

	}

	public function delete_category(){ // Удаляет выбранную категорию

	}

	public function set_sum(){ // Назначает сумму указанной категории

	}

	public function copy_previous_plan(){ // Копирует план с предыдущего месяца

	}
}