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

/**
 * Class StatusTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Status_Query query()
 * @method static EO_Status_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Status_Result getById($id)
 * @method static EO_Status_Result getList(array $parameters = array())
 * @method static EO_Status_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_Status createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_Status_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_Status wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_Status_Collection wakeUpCollection($rows)
 */
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
				'title'         => Loc::getMessage('B_SALE_STATUS_COLOR'),
			)),

			new Main\Entity\StringField('XML_ID', array(
				'title' => Loc::getMessage('B_SALE_STATUS_XML_ID'),
			)),

			new Main\ORM\Fields\Relations\Reference(
				'STATUS_LANG',
				StatusLangTable::class,
				Main\ORM\Query\Join::on('this.ID', 'ref.STATUS_ID'),
				array('join_type' => 'left')
			)
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

	/**
	 * @return string
	 */
	public static function generateXmlId()
	{
		return uniqid('bx_');
	}
}
