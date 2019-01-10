<?php

namespace classes\Expenses\Category;

use classes\Connect\ConnectDB;

class Category {
	protected $id;
	protected $title;
	protected $estimated_amount; // Запланированные расходы
	protected $allocated_amount; // Выделенные деньги
	protected $amount_spent; // Потраченные деньги

	public function __construct() {

	}

	/**
	 * @param int $sum
	 * @param string $type
	 * @return boolean
	 */
	public function set_allocated_amount( int $sum, string $type ): bool {

	}

	/**
	 * @param float $sum
	 * @return boolean
	 */
	public function add_purchase( float $sum): bool {

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
	public function getEstimatedAmount() {
		return $this->estimated_amount;
	}

	/**
	 * @return mixed
	 */
	public function getAllocatedAmount() {
		return $this->allocated_amount;
	}
}