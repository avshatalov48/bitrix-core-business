<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\UI;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class TileView
 * @package Bitrix\Sender\UI
 */
class TileView
{
	public const MAX_COUNT = 4;
	public const COLOR_GREY = '#eef2f4';

	public const SECTION_ALL = 'all';
	public const SECTION_LAST = 'last';
	public const SECTION_FREQ = 'freq';
	public const SECTION_SYS = 'system';
	public const SECTION_MY = 'my';

	/** @var array $tiles List of tiles. */
	protected $tiles = [];

	/** @var array $sections List of sections. */
	protected $sections = [];

	/**
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * Add tile.
	 *
	 * @param string|null $id ID.
	 * @param string $name Name.
	 * @param array $data Data.
	 * @param string $bgColor Background color.
	 * @param string $color Color.
	 *
	 * @return $this
	 */
	public function addTile($id, $name, $data = [], $bgColor = null, $color = null)
	{
		$this->tiles[] = $this->getTile($id, $name, $data, $bgColor, $color);
		return $this;
	}

	/**
	 * Get tile.
	 *
	 * @param string|null $id ID.
	 * @param string $name Name.
	 * @param array $data Data.
	 * @param string $bgColor Background color.
	 * @param string $color Color.
	 *
	 * @return array
	 */
	public function getTile($id, $name, $data = [], $bgColor = null, $color = null)
	{
		return [
			'id' => $id,
			'name' => $name,
			'data' => $data,
			'bgcolor' => $bgColor,
			'color' => $color
		];
	}

	/**
	 * Get tiles.
	 *
	 * @return array
	 */
	public function getTiles()
	{
		return $this->tiles;
	}

	/**
	 * Remove tiles.
	 *
	 * @return $this
	 */
	public function removeTiles()
	{
		$this->tiles = [];
		return $this;
	}

	/**
	 * Add section.
	 *
	 * @param string $id ID.
	 * @param string $name Name.
	 *
	 * @return $this
	 */
	public function addSection($id, $name = null)
	{
		$name = $name ?: Loc::getMessage('SENDER_UI_TILEVIEW_SECTION_'.mb_strtoupper($id));
		$name = $name ?: $id;

		$this->sections[$id] = [
			'id' => $id,
			'name' => $name,
			'items' => [],
		];
		return $this;
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Remove sections.
	 *
	 * @return $this
	 */
	public function removeSections()
	{
		$this->sections = [];
		return $this;
	}

	/**
	 * Get data for view.
	 *
	 * @return array
	 */
	public function get()
	{
		$list = $this->sections;
		$sections = array_keys($list);

		if (in_array(self::SECTION_ALL, $sections))
		{
			$list[self::SECTION_ALL]['items'] = $this->tiles;
		}

		foreach ($this->tiles as $tile)
		{
			foreach ($sections as $section)
			{
				if ($section === self::SECTION_ALL)
				{
					continue;
				}

				if (empty($tile['data'][$section]))
				{
					continue;
				}

				self::prepareTileForSorting($tile, $section);
				$list[$section]['items'][] = $tile;
			}
		}

		self::sortTiles($list);
		return array_values($list);
	}

	protected static function prepareTileForSorting(&$tile, $section)
	{
		if ($section === self::SECTION_LAST)
		{
			$last = $tile['data'][self::SECTION_LAST];
			if ($last instanceof DateTime)
			{
				$tile['data'][self::SECTION_LAST] = $last->getTimestamp();
			}
		}
	}

	protected static function sortTiles(&$list)
	{
		foreach ([self::SECTION_LAST, self::SECTION_FREQ] as $section)
		{
			if (!isset($list[$section]))
			{
				continue;
			}

			// sort & cut
			usort(
				$list[$section]['items'],
				function ($a, $b) use ($section)
				{
					return ($a['data'][$section] > $b['data'][$section]) ? -1 : 1;
				}
			);
			$list[$section]['items'] = array_slice($list[$section]['items'], 0, self::MAX_COUNT);
		}
	}
}