<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Internals;

use	Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class StatusTable extends Main\Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_status';
	}

	public static function getMap()
	{
		return array(

			new Main\Entity\StringField('ID', array(
				'primary'    => true,
				'validation' => function()
				{
					return array(
						new Main\Entity\Validator\RegExp('/^[A-Za-z]{1,2}$/'),
						new Main\Entity\Validator\Unique,
					);
				},
				'title'      => Loc::getMessage('B_SALE_STATUS_ID'),
			)),

			new Main\Entity\BooleanField('TYPE', array(
				'default_value' => 'O',
				'values'        => array('O', 'D'),
				'title'         => Loc::getMessage('B_SALE_STATUS_TYPE'),
			)),

			new Main\Entity\IntegerField('SORT', array(
				'default_value' => 100,
				'format'        => '/^[0-9]{1,11}$/',
				'title'         => Loc::getMessage('B_SALE_STATUS_SORT'),
			)),

			new Main\Entity\BooleanField('NOTIFY', array(
				'default_value' => 'Y',
				'values'        => array('N', 'Y'),
				'title'         => Loc::getMessage('B_SALE_STATUS_NOTIFY'),
			)),

			new Main\Entity\StringField('COLOR', array(
				'default_value' => 'Y',
				'title'         => Loc::getMessage('B_SALE_STATUS_COLOR'),
			)),

		);
	}

	/**
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return Main\Entity\UpdateResult
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public static function update($primary, array $data)
	{
		$result = parent::update($primary, $data);
		if (Main\Config\Option::get('sale', 'expiration_processing_events', 'N') === 'Y')
		{
			foreach (GetModuleEvents("sale", "OnStatusUpdate", true) as $event)
			{
				ExecuteModuleEventEx($event, array($primary, $data));
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 *
	 * @return Main\Entity\AddResult
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public static function add(array $data)
	{
		$result = parent::add($data);
		if (Main\Config\Option::get('sale', 'expiration_processing_events', 'N') === 'Y')
		{
			$id = $result->getId();
			foreach (GetModuleEvents("sale", "OnStatusAdd", true) as $event)
			{
				ExecuteModuleEventEx($event, array($id, $data));
			}
		}

		return $result;
	}
}
