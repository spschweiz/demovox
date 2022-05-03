<?php

namespace Demovox;

trait CollectionTrait
{

	/** @var int */
	protected int $collectionId;

	/** @var string */
	protected int $collectionName;

	protected function getDefaultCollection(): int
	{
		return Infos::getDefaultCollectionId();
	}

	protected function setCollectionId(int $collectionId): void
	{
		$this->collectionId = $collectionId;
		Infos::setCollectionId($collectionId);
	}

	protected function setCollectionIdByReq(): void
	{
		if (isset($_REQUEST['cln']) && is_numeric($_REQUEST['cln'])) {
			$collectionId = intval($_REQUEST['cln']);
		} else {
			$collectionId = $this->getDefaultCollection();
		}
		$this->setCollectionId($collectionId);
	}

	protected function getCollectionId(): int
	{
		return $this->collectionId;
	}

	protected function getCollectionName(): string
	{
		if (isset($this->collectionName)) {
			return $this->collectionName;
		}
		$collectionId = $this->getCollectionId();
		$collections  = new DbCollections();
		$collection   = $collections->getRow(['name'], "ID='$collectionId'");

		return $collection->name;
	}

}