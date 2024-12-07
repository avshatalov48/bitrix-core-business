<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class PersonTypeSiteTable
 *
 * Fields:
 * <ul>
 * <li> PERSON_TYPE_ID int mandatory
 * <li> SITE_ID string(2) mandatory
 * </ul>
 *
 * @package Bitrix\Sale
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PersonTypeSite_Query query()
 * @method static EO_PersonTypeSite_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PersonTypeSite_Result getById($id)
 * @method static EO_PersonTypeSite_Result getList(array $parameters = [])
 * @method static EO_PersonTypeSite_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PersonTypeSite createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PersonTypeSite_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PersonTypeSite wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PersonTypeSite_Collection wakeUpCollection($rows)
 */

class PersonTypeSiteTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_person_type_site';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'PERSON_TYPE_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'SITE_ID' => [
				'data_type' => 'string',
				'primary' => true
			],
		];
	}

	public static function deleteByPersonTypeId($personTypeId)
	{
		$result = new Main\ORM\Data\DeleteResult();

		$dbRes = static::getList([
			'select' => ['PERSON_TYPE_ID', 'SITE_ID'],
			'filter' => [
				'=PERSON_TYPE_ID' => $personTypeId
			]
		]);

		while ($item = $dbRes->fetch())
		{
			$r = static::delete([
				'PERSON_TYPE_ID' => $personTypeId,
				'SITE_ID' => $item['SITE_ID'],
			]);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}
}