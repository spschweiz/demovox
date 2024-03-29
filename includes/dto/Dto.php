<?php

namespace Demovox;

use ReflectionClass;
use ReflectionProperty;

abstract class Dto
{
	/** @var array */
	protected array $availableFields;
	/** @var bool */
	protected bool $isNewRecord = true;

	/**
	 * @param \stdClass|array|null $parameters
	 * @param bool                 $isNewRecord
	 */
	public function __construct($parameters = null, bool $isNewRecord = true)
	{
		$this->isNewRecord = $isNewRecord;

		if($parameters === null){
			return;
		}
		$class = new ReflectionClass(static::class);

		if (is_array($parameters)) {
			foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
				$property = $reflectionProperty->getName();
				if (!isset($parameters[$property])) {
					continue;
				}
				$this->{$property} = $parameters[$property];
			}
		} else {
			foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
				$property = $reflectionProperty->getName();
				if (!property_exists($parameters, $property)) {
					continue;
				}
				$this->{$property} = $parameters->{$property};
			}
		}
	}

	/**
	 * @return array
	 */
	public function getAvailableFields(): array
	{
		return $this->availableFields;
	}

	/**
	 * @param $id
	 * @return string|null
	 */
	public function getFieldName($id): ?string
	{
		$fields = $this->getAvailableFields();
		return $fields[$id] ?? null;
	}

	/**
	 * @return bool
	 */
	public function prepareInsert(): bool
	{
		return true;
	}

	/**
	 * Get data as array. May contain unsafe values.
	 * @return array
	 */
	public function getDataArr(): array
	{
		$data = [];
		$class = new ReflectionClass(static::class);

		foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
			$property = $reflectionProperty->getName();
			if (!isset($this->$property)) {
				continue;
			}
			$data[$property] = $this->$property;
		}
		return $data;
	}
}