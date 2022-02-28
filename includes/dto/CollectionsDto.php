<?php

namespace Demovox;

/**
 * Dto for @DbCollections
 */
class CollectionsDto extends Dto
{
	public int $ID, $collection_ID;
	public ?string $name;
	public ?string $end_date;
}