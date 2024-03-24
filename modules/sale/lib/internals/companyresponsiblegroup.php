<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


/**
 * Class CompanyResponsibleGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CompanyResponsibleGroup_Query query()
 * @method static EO_CompanyResponsibleGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CompanyResponsibleGroup_Result getById($id)
 * @method static EO_CompanyResponsibleGroup_Result getList(array $parameters = [])
 * @method static EO_CompanyResponsibleGroup_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CompanyResponsibleGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CompanyResponsibleGroup_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CompanyResponsibleGroup wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CompanyResponsibleGroup_Collection wakeUpCollection($rows)
 */
class CompanyResponsibleGroupTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_company_resp_grp';
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
				'required' => true
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
			array(
				'filter' => array('COMPANY_ID' => $id),
				'select' => array('ID')
			)
		);
		while ($item = $itemsList->fetch())
		{
			static::delete($item['ID']);
		}
	}


	public static function getCompanyGroups($id)
	{
		$list = [];
		$res = static::getList([
			'filter' => [
				'=COMPANY_ID' => $id
			],
			'select' => ['GROUP_ID'],
		]);
		while($data = $res->fetch())
		{
			$list[] = $data['GROUP_ID'];
		}

		return $list;
	}
}
