<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


class CompanyGroupTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_company_group';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'COMPANY_ID' => array(
				'data_type' => 'integer',
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required'   => true
			),
		);
	}

	public static function deleteByCompanyId($id)
	{
		$id = intval($id);
		if ($id <= 0)
			throw new Main\ArgumentNullException("id");

		$itemsList = static::getList(
			array(
				"filter" => array("COMPANY_ID" => $id),
				"select" => array("ID")
			)
		);
		while ($item = $itemsList->fetch())
			static::delete($item["ID"]);
	}
}