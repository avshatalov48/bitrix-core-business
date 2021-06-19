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
	 * @var int
	 */
	private $size;

	/**
	 * @param string $link
	 * @param string $name
	 * @param int $size
	 * @return Attach
	 */
	public static function createInstance(string $link, string $name, int $size): Attach
	{
		return new self($link, $name, $size);
	}

	/**
	 * Attach constructor.
	 * @param string $link
	 * @param string $name
	 * @param int $size
	 */
	public function __construct(string $link, string $name,  int $size)
	{
		$this->link = $link;
		$this->name = $name;
		$this->size = $size;
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

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	public function getFormatSize($precision = 2): string
	{
		$suffix = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
		$pos = 0;
		$size = $this->size;
		while($size >= 1024 && $pos < 4)
		{
			$size /= 1024;
			$pos++;
		}

		return round($size, $precision) . ' ' . $suffix[$pos];
	}
}