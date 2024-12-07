<?php
namespace Bitrix\Iblock\Model;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;

class Section
{
	private static $entityInstance = [];

	/**
	 * @param int|string|Iblock $iblock Iblock object, or API_CODE, or ID
	 *
	 * @return SectionTable|string|null
	 */
	final public static function compileEntityByIblock($iblock)
	{
		$iblockId = static::resolveIblockId($iblock);

		if ($iblockId <= 0)
		{
			return null;
		}

		if (!isset(self::$entityInstance[$iblockId]))
		{
			$className = 'Section'.$iblockId.'Table';
			$entityName = "\\Bitrix\\Iblock\\".$className;
			$referenceName = 'Bitrix\Iblock\Section'.$iblockId;
			$entity = '
			namespace Bitrix\Iblock;
			class '.$className.' extends \Bitrix\Iblock\SectionTable
			{
				public static function getUfId()
				{
					return "IBLOCK_'.$iblockId.'_SECTION";
				}
				
				public static function getMap(): array
				{
					$fields = parent::getMap();
					$fields["PARENT_SECTION"] = array(
						"data_type" => "'.$referenceName.'",
						"reference" => array("=this.IBLOCK_SECTION_ID" => "ref.ID"),
					);
					return $fields;
				}
				
				public static function setDefaultScope($query)
				{
					return $query->where("IBLOCK_ID", '.$iblockId.');
				}
			}';
			eval($entity);
			self::$entityInstance[$iblockId] = $entityName;
		}

		return self::$entityInstance[$iblockId];
	}

	/**
	 * @param int|string|Iblock $iblock
	 *
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function resolveIblockId($iblock): ?int
	{
		$iblockId = null;

		if ($iblock instanceof Iblock)
		{
			$iblockId = $iblock->getId();
		}
		elseif (is_string($iblock))
		{
			$row = IblockTable::query()
				->addSelect('ID')
				->where('API_CODE', $iblock)
				->fetch();

			if (!empty($row))
			{
				$iblockId = (int)$row['ID'];
			}
		}

		if (empty($iblockId) && is_numeric($iblock))
		{
			$iblockId = (int)$iblock;
		}

		return $iblockId;
	}
}