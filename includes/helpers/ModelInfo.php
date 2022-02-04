<?php

namespace Demovox;

class ModelInfo
{
	public const AVAILABLE_TABLES = ['Signatures', 'Mails'];

	/**
	 * @return Dto[]
	 */
	public static function getDataTransferObjects()
	{
		$dto = [];
		foreach (self::AVAILABLE_TABLES as $dtoName) {
			$dtoName = 'Demovox\Dto' . $dtoName;
			/** @var Dto $dto */
			$dto[] = new $dtoName;
		}
		return $dto;
	}

	/**
	 * @return Db[]
	 */
	public static function getDbServices()
	{
		$dto = [];
		foreach (ModelInfo::AVAILABLE_TABLES as $dtoName) {
			$dtoName = 'Demovox\Db' . $dtoName;
			/** @var Db $dto */
			$dto[] = new $dtoName;
		}
		return $dto;
	}

}