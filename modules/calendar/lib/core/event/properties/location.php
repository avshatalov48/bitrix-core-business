<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\BaseProperty;

class Location extends BaseProperty
{
	/**
	 * @var string|null
	 */
	private ?string $actualLocation;
	/**
	 * @var string|null
	 */
	private ?string $originalLocation;

	/**
	 * @param string|null $actualLocation
	 * @param string|null $originalLocation
	 */
	public function __construct(?string $actualLocation, ?string $originalLocation = '')
	{
		$this->actualLocation = $actualLocation;
		$this->originalLocation = $originalLocation;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'NEW' => $this->actualLocation,
			'OLD' => $this->originalLocation,
		];
	}

	/**
	 * @param string $actualLocation
	 * @return Location
	 */
	public function setActualLocation(string $actualLocation): Location
	{
		$this->actualLocation = $actualLocation;

		return $this;
	}

	/**
	 * @param string $originalLocation
	 * @return Location
	 */
	public function setOriginalLocation(string $originalLocation): Location
	{
		$this->originalLocation = $originalLocation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getActualLocation(): string
	{
		return $this->actualLocation;
	}

	/**
	 * @return string
	 */
	public function getOriginalLocation(): string
	{
		return $this->originalLocation;
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->actualLocation;
	}
}
