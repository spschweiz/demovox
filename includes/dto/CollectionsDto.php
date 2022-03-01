<?php

namespace Demovox;

/**
 * Dto for @DbCollections
 */
class CollectionsDto extends Dto
{
	public int $ID;
	public ?string $name;
	public ?string $end_date;
	public ?string $end_message;
}