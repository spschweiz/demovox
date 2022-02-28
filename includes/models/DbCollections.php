<?php

namespace Demovox;

class DbCollections extends db
{
	/**
	 * @var string
	 */
	protected string $tableName = 'demovox_collections';

	protected string $tableDefinition = '
          ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          name varchar(200) NULL,
          end_date datetime NULL,
          creation_date datetime NOT NULL DEFAULT NOW(),
          PRIMARY KEY (ID),
          UNIQUE KEY name_index (name),
          INDEX end_date_index (end_date),
          INDEX creation_date_index (creation_date)';

	/**
	 * @param array       $select    Fields to select
	 * @param string|null $where     SQL where statement
	 * @param string|null $sqlAppend Append SQL statements
	 *
	 * @return CollectionsDto[] Database query results
	 */
	public function getResults(array $select, ?string $where = null, ?string $sqlAppend = null): array
	{
		$results = parent::getResultsRaw($select, $where, $sqlAppend);
		foreach ($results as &$row) {
			$row = new CollectionsDto($row, false);
		}
		return $results;
	}
}