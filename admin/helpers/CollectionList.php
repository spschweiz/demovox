<?php

namespace Demovox;

require_once Infos::getPluginDir() . 'admin/helpers/List.php';

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
	protected $columns = [
		'ID',
		'name',
		'end_date',
		'shortcode_form',
		'shortcode_count',
	];

	protected function get_db_model(): DtoCollections
	{
		return new DtoCollections();
	}

	/**
	 * @param string|null $where
	 * @param int $perPage
	 * @param int $pageNumber
	 *
	 * @return DtoCollections[]
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
		_e('No collections available.', 'demovox');
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param DtoCollections $item
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
			case 'shortcode_form':
				return '<code>[demovox_form collection=' . $item->ID . ']</code>';
			case 'shortcode_count':
				return '<code>[demovox_count collection=' . $item->ID . ']</code>';
			default:
				return $item->{$column_name};
		}
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() : array
	{
		return [
			'name' =>          ['name', false],
			'end_date' =>      ['end_date', true],
			'creation_date' => ['creation_date', true],
		];
	}
}