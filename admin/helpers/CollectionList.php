<?php

namespace Demovox;

require_once Infos::getPluginDir() . 'admin/helpers/ListTable.php';

class CollectionList extends ListTable
{
	/**
	 * CollectionList constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'singular' => __('Collection', 'demovox'), //singular name of the listed records
			'plural'   => __('Collections', 'demovox'), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		]);
	}

	/** @var array */
	protected array $columns = [
		'name',
		'end_date',
		'shortcode' => 'Shortcode',
		'show'      => 'Show',
	];

	/** @var array Table columns */
	protected array $sortableColumns = [
		'name'          => ['name', false],
		'end_date'      => ['end_date', true],
		'creation_date' => ['creation_date', true],
	];

	protected function get_db_model(): DbCollections
	{
		return new DbCollections();
	}

	protected function get_dto(): CollectionsDto
	{
		return new CollectionsDto();
	}

	/**
	 * @param string|null $where
	 * @param int $perPage
	 * @param int $pageNumber
	 *
	 * @return CollectionsDto[]
	 */
	public function get_results($where, $perPage = 25, $pageNumber = 1) : array
	{
		return parent::get_results($where, $perPage, $pageNumber);
	}

	/**
	 * Text displayed when no signature data is available
	 */
	public function no_items()
	{
		echo Strings::__('No collections available.');
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param CollectionsDto $item
	 * @param string         $column_name
	 *
	 * @return string
	 */
	public function column_default($item, $column_name): string
	{
		switch ($column_name) {
			case 'end_date':
				return $item->{$column_name} ?: Strings::__('- no end date -');
			case 'shortcode':
				return '<code>[demovox_form cln=' . $item->ID . ']</code>'
					. ' <code>[demovox_count cln=' . $item->ID . ']</code>';
			case 'show':
				$ret = Strings::getAdminLink('admin.php?page=demovoxOverview&cln=' . $item->ID, 'Overview') . ' | ';
				if (current_user_can('demovox_data'))
					$ret .= Strings::getAdminLink('admin.php?page=demovoxData&cln=' . $item->ID, 'Data') . ' | ';
				if (current_user_can('demovox_import'))
					$ret .= Strings::getAdminLink('admin.php?page=demovoxImport&cln=' . $item->ID, 'Import') . ' | ';
				if (current_user_can('manage_options'))
					$ret .= Strings::getAdminLink('admin.php?page=demovoxSettings&cln=' . $item->ID, 'Settings');
				return $ret;
			case 'name':
				return '<a href="' . Strings::getAdminUrl('admin.php?page=demovoxOverview') . '">'
					. $item->{$column_name} . '</a>';
			default:
				if (!isset($item->{$column_name})) {
					return '';
				}
				return $item->{$column_name};
		}
	}
}