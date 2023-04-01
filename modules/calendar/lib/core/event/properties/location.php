<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\BaseProperty;
use Bitrix\Main\Text\Emoji;

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
		$this->actualLocation = $actualLocation ? Emoji::decode($actualLocation) : $actualLocation;
		$this->originalLocation = $originalLocation ? Emoji::decode($originalLocation) : $originalLocation;
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
		$this->actualLocation = Emoji::decode($actualLocation);

		return $this;
	}

	/**
	 * @param string $originalLocation
	 * @return Location
	 */
	public function setOriginalLocation(string $originalLocation): Location
	{
		$this->originalLocation = Emoji::decode($originalLocation);

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