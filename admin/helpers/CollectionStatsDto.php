<?php

namespace Demovox;

class CollectionStatsDto extends Dto
{
	public int $count = 0, $countOptin = 0, $countOptout = 0, $countOptNULL = 0, $countUnfinished = 0;
}