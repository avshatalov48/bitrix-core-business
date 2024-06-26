<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


/**
 * Class CompanyGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CompanyGroup_Query query()
 * @method static EO_CompanyGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CompanyGroup_Result getById($id)
 * @method static EO_CompanyGroup_Result getList(array $parameters = [])
 * @method static EO_CompanyGroup_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CompanyGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CompanyGroup_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CompanyGroup wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CompanyGroup_Collection wakeUpCollection($rows)
 */
class CompanyGroupTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_company_group';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'COMPANY_ID' => [
				'data_type' => 'integer',
			],
			'GROUP_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
		];
	}

	public static function deleteByCompanyId($id)
	{
		$id = intval($id);
		if ($id <= 0)
		{
			throw new Main\ArgumentNullException('id');
		}

		$itemsList = static::getList(
			[
				'filter' => ['COMPANY_ID' => $id],
				'select' => ['ID'],
			]
		);
		while ($item = $itemsList->fetch())
		{
			static::delete($item['ID']);
		}
	}
}
