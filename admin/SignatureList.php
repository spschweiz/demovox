<?php

namespace Demovox;
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SignatureList extends \WP_List_Table
{
	/**
	 * SignatureList constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'singular' => __('Signature', 'demovox'), //singular name of the listed records
			'plural'   => __('Signatures', 'demovox'), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		]);
	}

	/** @var array */
	static $columns = [
		'ID',
		'first_name',
		'last_name',
		'birth_date',
		'street',
		'street_no',
		'gde_zip',
		'gde_name',
		'gde_canton',
		'is_optin',
		'is_step2_done',
		'is_sheet_received',
		'creation_date',
		'sheet_received_date',
		'serial',
	];

	/**
	 * Retrieve signatures data from the database
	 *
	 * @param int $perPage
	 * @param int $pageNumber
	 *
	 * @return mixed
	 */
	public function get_signatures($perPage = 25, $pageNumber = 1)
	{
		$select = self::$columns;
		array_unshift($select, 'ID');
		$where = 'is_deleted = 0 AND is_step2_done = 1';
		if (!empty($_REQUEST['s'])) {
			$s = esc_sql(trim($_REQUEST['s']));
			if (!empty($s)) {
				$where .= ' AND (';
				foreach (self::$columns as $col) {
					if (in_array($col, $this->get_hidden_columns())) {
						continue;
					}
					$where .= $col . ' LIKE \'%' . $s . '%\' OR ';
				}
				$where = substr($where, 0, -4) . ')';
			}
		}
		$sqlAppend = '';
		if (!empty($_REQUEST['orderby'])) {
			$sqlAppend .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sqlAppend .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		}

		$sqlAppend .= ' LIMIT ' . $perPage;
		$sqlAppend .= ' OFFSET ' . ($pageNumber - 1) * $perPage;

		try {
			$result = DB::getResults($select, $where, null, $sqlAppend);
		} catch (\Exception $e) {
			$result = [];
		}

		return $result;
	}

	/**
	 * Delete a signature record.
	 *
	 * @param int $id signature ID
	 */
	public function delete_signature($id)
	{
		DB::delete(['ID' => $id]);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count()
	{
		return DB::count();
	}

	/**
	 * Text displayed when no signature data is available
	 */
	public function no_items()
	{
		_e('No signatures available.', 'demovox');
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name($item)
	{
		// create a nonce
		$delete_nonce = wp_create_nonce('sp_delete_signature');

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf('<a href="?page=%s&action=%s&signature=%s&_wpnonce=%s">Delete</a>',
				esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
		];

		return $title . $this->row_actions($actions);
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name)
	{
		if (!isset($item->{$column_name})) {
			return '';
		}

		switch ($column_name) {
			case 'is_optin':
			case 'is_step2_done':
				return $item->{$column_name} ? 'Yes' : 'No';
			case 'is_sheet_received':
				return $item->{$column_name} ?: 'None';
			case 'serial':
				return '<code>' . $item->{$column_name} . '</code>';
			default:
				return $item->{$column_name};
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->ID
		);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns()
	{
		$fields = DB::getExportFields();
		$columns = [];
		foreach (self::$columns as $val) {
			$columns[$val] = isset($fields[$val]) ? $fields[$val] : $val;
		}
		$columns = ['cb' => '<input type="checkbox" />',] + $columns;

		return $columns;
	}

	public function get_hidden_columns()
	{
		// Setup Hidden columns and return them
		return ['ID'];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns()
	{
		$sortable_columns = [
			'is_optin'            => ['is_optin', false],
			'is_sheet_received'   => ['is_sheet_received', false],
			'creation_date'       => ['creation_date', true],
			'sheet_received_date' => ['sheet_received_date', false],
		];
		if (DB::isEncryptionEnabled()) {
			return $sortable_columns;
		}
		$sortable_columns += [
			'first_name' => ['first_name', false],
			'last_name'  => ['last_name', false],
			'gde_zip'    => ['gde_zip', false],
			'gde_name'   => ['gde_name', false],
			'gde_canton' => ['gde_canton', false],
		];

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
		$actions = ['bulk-delete' => 'Delete',];

		return $actions;
	}

	/**
	 * Prepare the items for the table to process
	 * @return Void
	 */
	public function prepare_items()
	{
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		];
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page('records_per_page', 25);
		$current_page = $this->get_pagenum();
		$total_items = $this->record_count();
		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]);
		$this->items = $this->get_signatures($per_page, $current_page);
	}

	public function process_bulk_action()
	{
		// delete action
		if ('delete' === $this->current_action()) {
			$nonce = esc_attr($_REQUEST['_wpnonce']);
			if (!wp_verify_nonce($nonce, 'sp_delete_signature')) {
				die('nonce check failed');
			}
			self::delete_signature(absint($_GET['signature']));
			wp_redirect(esc_url(add_query_arg()));
			exit;
		}

		// bulk delete action
		if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
			|| (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
		) {
			$delete_ids = esc_sql($_POST['bulk-delete']);

			// loop over the array of record IDs and delete them
			foreach ($delete_ids as $id) {
				self::delete_signature($id);
			}

			//wp_redirect(esc_url(add_query_arg()));
			exit;
		}
	}
}