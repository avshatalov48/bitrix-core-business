<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class Cashbox1C
 * @package Bitrix\Sale\Cashbox
 */
class Cashbox1C extends Cashbox
{
	const CACHE_ID = 'BITRIX_CASHBOX_1C_ID';
	const TTL = 31536000;


	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		return array();
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function buildZReportQuery($id)
	{
		return array();
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_1C_TITLE');
	}

	/**
	 * @return int
	 */
	public static function getId()
	{
		$id = 0;
		$cacheManager = Main\Application::getInstance()->getManagedCache();

		if ($cacheManager->read(self::TTL, self::CACHE_ID))
			$id = $cacheManager->get(self::CACHE_ID);

		if ($id <= 0)
		{
			$data = CashboxTable::getRow(
				array(
					'select' => array('ID'),
					'filter' => array('=HANDLER' => '\Bitrix\Sale\Cashbox\Cashbox1C')
				)
			);
			if (is_array($data) && $data['ID'] > 0)
			{
				$id = $data['ID'];
				$cacheManager->set(self::CACHE_ID, $id);
			}
		}

		return $id;
	}

	/**
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return array
	 */
	protected static function extractCheckData(array $data)
	{
		return array(
			'ID' => $data['ID'],
			'LINK_PARAMS' => array(
				Check::PARAM_FISCAL_DOC_ATTR => $data['LINK_PARAMS']['FISCAL_SIGN'],
				Check::PARAM_REG_NUMBER_KKT => $data['LINK_PARAMS']['REG_NUMBER_KKT']
			)
		);
	}
}
