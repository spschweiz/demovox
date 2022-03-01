<?php

namespace Demovox;

class CollectionStatsDto extends Dto
{
	public int $count, $countOptin, $countOptout, $countOptNULL, $countUnfinished;
}