<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


class ShipmentDocument extends \Bitrix\Sale\Exchange\OneC\ShipmentDocument
{
	static protected function unitFieldsInfo(&$info)
	{
		$info['ITEMS']['FIELDS']['BASE_UNIT'] = array(
			'TYPE' => 'string'
		);
	}
}