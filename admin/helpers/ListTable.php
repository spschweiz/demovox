<?php

namespace Demovox;

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

abstract class ListTable extends \WP_List_Table
{
	/** @var array */
	protected $columns = [];


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name($item): string
	{
		return '<strong>' . $item['name'] . '</strong>';
	}

	/**
	 * @return Db
	 */
	protected function get_db_model()
	{
		Core::errorDie('Not implemented', 500);
	}

	protected function get_hidden_columns(): array
	{
		return ['ID'];
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns(): array
	{
		$dtoSign = new SignaturesDto();
		$fields = $dtoSign->getAvailableFields();
		$columns = [];
		foreach ($this->columns as $val) {
			$columns[$val] = isset($fields[$val]) ? $fields[$val] : $val;
		}
		$columns = ['cb' => '<input type="checkbox" />',] + $columns;

		return $columns;
	}

	protected function process_bulk_action(): void
	{
	}

	/**
	 * @return string
	 */
	protected function getWhere(): string
	{
		return '';
	}

	/**
	 * Prepare the items for the table to process
	 * @return Void
	 */
	public function prepare_items(): void
	{
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		];
		$this->process_bulk_action();

		$where = $this->getWhere();
		$per_page = $this->get_items_per_page('records_per_page', 25);
		$current_page = $this->get_pagenum();
		$total_items = $this->record_count($where);
		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page' => $per_page,
			]);
		$this->items = $this->get_results($where, $per_page, $current_page);
	}


	/**
	 * Retrieve records from the database
	 *
	 * @param string|null $where
	 * @param int         $perPage
	 * @param int         $pageNumber
	 *
	 * @return Dto[]
	 */
	public function get_results($where, $perPage = 25, $pageNumber = 1): array
	{
		$select = $this->columns;
		array_unshift($select, 'ID');
		$sqlAppend = '';
		if (!empty($_REQUEST['orderby'])) {
			$sqlAppend .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sqlAppend .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		}

		$sqlAppend .= ' LIMIT ' . $perPage;
		$sqlAppend .= ' OFFSET ' . ($pageNumber - 1) * $perPage;

		try {
			$model = $this->get_db_model();
			$result = $model->getResults($select, $where, $sqlAppend);
		} catch (\Exception $e) {
			$result = [];
		}

		return $result;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @param $where
	 * @return int
	 */
	public function record_count($where): int
	{
		$dbSign = $this->get_db_model();
		return $dbSign->count($where);
	}
}