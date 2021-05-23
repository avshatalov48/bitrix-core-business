<?php


namespace Bitrix\Calendar\ICal\Builder;


use Bitrix\Calendar\SerializeObject;
use Serializable;

class Attach implements Serializable
{
	use SerializeObject;
	/**
	 * @var string
	 */
	private $link;
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param string $link
	 * @param string $name
	 * @return Attach
	 */
	public static function createInstance(string $link, string $name): Attach
	{
		return new self($link, $name);
	}

	/**
	 * Attach constructor.
	 * @param string $link
	 * @param string $name
	 */
	public function __construct(string $link, string $name)
	{
		$this->link = $link;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getLink(): string
	{
		return $this->link;
	}
}