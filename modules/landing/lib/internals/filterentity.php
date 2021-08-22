<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class FilterEntityTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FilterEntity_Query query()
 * @method static EO_FilterEntity_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FilterEntity_Result getById($id)
 * @method static EO_FilterEntity_Result getList(array $parameters = array())
 * @method static EO_FilterEntity_Entity getEntity()
 * @method static \Bitrix\Landing\Internals\EO_FilterEntity createObject($setDefaultValues = true)
 * @method static \Bitrix\Landing\Internals\EO_FilterEntity_Collection createCollection()
 * @method static \Bitrix\Landing\Internals\EO_FilterEntity wakeUpObject($row)
 * @method static \Bitrix\Landing\Internals\EO_FilterEntity_Collection wakeUpCollection($rows)
 */
class FilterEntityTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_landing_filter_entity';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => 'ID'
			)),
			'SOURCE_ID' => new Entity\StringField('SOURCE_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_SOURCE_ID'),
				'required' => true
			)),
			'FILTER_HASH' => new Entity\StringField('FILTER_HASH', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FILTER_HASH'),
				'required' => true
			)),
			'FILTER' => new Entity\StringField('FILTER', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_FILTER'),
				'serialized' => true,
				'required' => true
			)),
			'CREATED_BY_ID' => new Entity\IntegerField('CREATED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_CREATED_BY_ID'),
				'required' => true
			)),
			'MODIFIED_BY_ID' => new Entity\IntegerField('MODIFIED_BY_ID', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_MODIFIED_BY_ID'),
				'required' => true
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_CREATE'),
				'required' => true
			)),
			'DATE_MODIFY' => new Entity\DatetimeField('DATE_MODIFY', array(
				'title' => Loc::getMessage('LANDING_TABLE_FIELD_DATE_MODIFY'),
				'required' => true
			))
		);
	}

	/**
	 * Apply the block to the filter.
	 * @param int $filterId Filter id.
	 * @param int $blockId Block id.
	 * @return void
	 */
	public static function applyBlock($filterId, $blockId)
	{
		$res = FilterBlockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'FILTER_ID' => $filterId,
				'BLOCK_ID' => $blockId
			]
		]);
		if (!$res->fetch())
		{
			FilterBlockTable::add([
				'FILTER_ID' => $filterId,
				'BLOCK_ID' => $blockId
	 		]);
		}
		unset($res);
		self::actualFilters();
	}

	/**
	 * Remove the block from all filters.
	 * @param int $blockId Block id.
	 * @return void
	 */
	public static function removeBlock($blockId)
	{
		$res = FilterBlockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'BLOCK_ID' => $blockId
			]
		]);
		while ($row = $res->fetch())
		{
			FilterBlockTable::delete($row['ID']);
		}
		unset($res, $row);
		self::actualFilters();
	}

	/**
	 * Remove not used filters.
	 * @return void
	 */
	protected static function actualFilters()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$connection->query('
			delete F from 
				b_landing_filter_entity F
			left join 
				b_landing_filter_block B on B.FILTER_ID = F.ID
			where 
				B.ID is null;
		');
		unset($connection);
	}
}