<?php

namespace Bitrix\Iblock\Grid;

class RowType
{
	public const ELEMENT = 'E';
	public const SECTION = 'S';

	/**
	 * @param string $type
	 * @param string|int $id
	 * @return string
	 */
	public static function getIndex(string $type, string|int $id): string
	{
		return ($type === self::SECTION ? self::SECTION : self::ELEMENT) . $id;
	}

	/**
	 * @param string $index
	 *
	 * @return null|array in format `[type, id]`
	 */
	public static function parseIndex(string $index): ?array
	{
		$re = '/^(E|S|)(\d+)$/';
		if (preg_match($re, $index, $m))
		{
			$type = ($m[1] ?: RowType::ELEMENT);
			$index = (int)$m[2];

			return [$type, $index];
		}
		elseif (is_numeric($index))
		{
			return [RowType::ELEMENT, (int)$index];
		}

		return null;
	}

	/**
	 * Parses identifiers and groups them by type.
	 *
	 * @param string[] $ids
	 *
	 * @return array in format `[$elementIds, $sectionIds]`
	 */
	public static function parseIndexList(array $ids): array
	{
		$elementIds = [];
		$sectionIds = [];

		foreach ($ids as $id)
		{
			$index = self::parseIndex($id);
			if ($index === null)
			{
				continue;
			}
			[$type, $id] = $index;

			if ($type === self::ELEMENT)
			{
				$elementIds[] = $id;
			}
			else
			{
				$sectionIds[] = $id;
			}
		}

		return [$elementIds, $sectionIds];
	}
}
