<?php

namespace Demovox;

require_once Infos::getPluginDir() . 'admin/helpers/ListTable.php';

class SignatureList extends ListTable
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
	protected $columns = [
		'ID',
		'first_name',
		'last_name',
		'birth_date',
		'street',
		'street_no',
		'zip',
		'gde_name',
		'gde_zip',
		'gde_canton',
		'is_optin',
		'is_outside_scope',
		'is_sheet_received',
		'creation_date',
		'sheet_received_date',
		'serial',
	];

	protected function get_db_model(): DbSignatures
	{
		return new DbSignatures();
	}

	/**
	 * @param string|null $where
	 * @param int $perPage
	 * @param int $pageNumber
	 *
	 * @return DtoSignatures[]
	 */
	public function get_results($where, $perPage = 25, $pageNumber = 1) : array
	{
		return parent::get_results($where, $perPage, $pageNumber);
	}

	/**
	 * Delete a record
	 *
	 * @param int $id record ID
	 */
	public function delete_signature(int $id)
	{
		$dbSign = $this->get_db_model();
		$dbSign->delete(['ID' => $id]);
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
	function column_name($item) : string
	{
		$title = parent::column_name($item);

		// create a nonce
		$delete_nonce = wp_create_nonce('sp_delete_signature');

		$actions = [
			'delete' => sprintf('<a href="?page=%s&action=%s&signature=%s&_wpnonce=%s">Delete</a>',
				esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
		];

		return $title . $this->row_actions($actions);
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param DtoSignatures $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default($item, $column_name): string
	{
		if (!isset($item->{$column_name})) {
			return '';
		}

		switch ($column_name) {
			case 'is_optin':
			case 'is_step2_done':
			case 'is_outside_scope':
				return $item->{$column_name} ? 'Yes' : 'No';
			case 'is_sheet_received':
				return $item->{$column_name} ?: 'None';
			case 'gde_canton':
				return strtoupper($item->{$column_name});
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
	function column_cb($item): string
	{
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->ID
		);
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() : array
	{
		$sortable_columns = [
			'is_optin'            => ['is_optin', false],
			'is_step2_done'       => ['is_step2_done', false],
			'is_sheet_received'   => ['is_sheet_received', false],
			'is_outside_scope'    => ['is_outside_scope', false],
			'creation_date'       => ['creation_date', true],
			'sheet_received_date' => ['sheet_received_date', false],
		];
		if (Crypt::isEncryptionEnabled()) {
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
	public function get_bulk_actions(): array
	{
		return ['bulk-delete' => 'Delete',];
	}

	public function process_bulk_action(): void
	{
		// delete action
		if ('delete' === $this->current_action()) {
			$nonce = esc_attr($_REQUEST['_wpnonce']);
			if (!wp_verify_nonce($nonce, 'sp_delete_signature')) {
				Core::errorDie('nonce check failed', 401);
			}
			$this->delete_signature(absint($_GET['signature']));
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
				$this->delete_signature($id);
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getWhere(): string
	{
		$where = 'is_deleted = 0 AND is_step2_done <> 0';
		if (!empty($_REQUEST['s'])) {
			$s = esc_sql(trim($_REQUEST['s']));
			if (!empty($s)) {
				$whereLike = '';
				foreach ($this->columns as $col) {
					if (in_array($col, $this->get_hidden_columns())) {
						continue;
					}
					$whereLike .= $col . ' LIKE \'%' . $s . '%\' OR ';
				}
				$whereLike = substr($whereLike, 0, -4);
				$where .= ' AND (';
				$where .= ' (is_encrypted = 0 AND (' . $whereLike . '))';
				$where .= ' OR (is_encrypted = 1 AND serial = \'' . $s . '\')';
				$where .= ' )';
			}
		}
		return $where;
	}
}