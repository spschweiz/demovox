<?php

namespace Demovox;

use ReflectionClass;
use ReflectionProperty;

abstract class Dto
{
	/** @var array */
	protected array $availableFields;

	/**
	 * @param array $parameters
	 */
	public function __construct(array $parameters = [])
	{
		$class = new ReflectionClass(static::class);

		foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
			$property = $reflectionProperty->getName();
			if (!isset($parameters[$property])) {
				continue;
			}
			$this->{$property} = $parameters[$property];
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