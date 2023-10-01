<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\ClientType;

Loc::loadMessages(__FILE__);

/**
 * Class PaySystemActionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PaySystemAction_Query query()
 * @method static EO_PaySystemAction_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PaySystemAction_Result getById($id)
 * @method static EO_PaySystemAction_Result getList(array $parameters = [])
 * @method static EO_PaySystemAction_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemAction createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemAction_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PaySystemAction wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PaySystemAction_Collection wakeUpCollection($rows)
 */
class PaySystemActionTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_pay_system_action';
	}

	public static function getMap()
	{
		return [
			'ID' => new IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			'PAY_SYSTEM_ID' => new IntegerField('PAY_SYSTEM_ID'),
			'PERSON_TYPE_ID' => new IntegerField('PERSON_TYPE_ID'),
			'NAME' => new StringField('NAME'),
			'PSA_NAME' => new StringField('PSA_NAME'),
			'CODE' => new StringField('CODE'),
			'SORT' => new IntegerField('SORT'),
			'ACTION_FILE' => new StringField('ACTION_FILE'),
			'RESULT_FILE' => new StringField('RESULT_FILE'),
			'DESCRIPTION' => new StringField('DESCRIPTION'),
			'NEW_WINDOW' => new BooleanField('NEW_WINDOW', [
				'values' => ['N', 'Y'],
			]),
			'PARAMS' => new StringField('PARAMS'),
			'TARIF' => new StringField('TARIF'),
			'PS_MODE' => new StringField('PS_MODE'),
			'PS_CLIENT_TYPE' => new EnumField('PS_CLIENT_TYPE', [
				'values' => [
					ClientType::B2C,
					ClientType::B2B,
				],
			]),
			'HAVE_PAYMENT' => new BooleanField('HAVE_PAYMENT', [
				'values' => ['N', 'Y'],
			]),
			'HAVE_ACTION' => new BooleanField('HAVE_ACTION', [
				'values' => ['N', 'Y'],
			]),
			'HAVE_RESULT' => new BooleanField('HAVE_RESULT', [
				'values' => ['N', 'Y'],
			]),
			'HAVE_PREPAY' => new BooleanField('HAVE_PREPAY', [
				'values' => ['N', 'Y'],
			]),
			'HAVE_PRICE' => new BooleanField('HAVE_PRICE', [
				'values' => ['N', 'Y'],
			]),
			'HAVE_RESULT_RECEIVE' => new BooleanField('HAVE_RESULT_RECEIVE', [
				'values' => ['N', 'Y'],
			]),
			'ENCODING' => new StringField('ENCODING'),
			'LOGOTIP' => new IntegerField('LOGOTIP'),
			'ACTIVE' => new BooleanField('ACTIVE', [
				'values' => ['N', 'Y'],
			]),
			'ALLOW_EDIT_PAYMENT' => new BooleanField('ALLOW_EDIT_PAYMENT', [
				'values' => ['N', 'Y'],
			]),
			'IS_CASH' => new StringField('IS_CASH'),
			'AUTO_CHANGE_1C' => new BooleanField('AUTO_CHANGE_1C', [
				'values' => ['N', 'Y'],
			]),
			'CAN_PRINT_CHECK' => new BooleanField('CAN_PRINT_CHECK', [
				'values' => ['N', 'Y'],
			]),
			'ENTITY_REGISTRY_TYPE' => new StringField('ENTITY_REGISTRY_TYPE'),
			'XML_ID' => new StringField('XML_ID'),
		];
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		if ($primary == PaySystem\Manager::getInnerPaySystemId())
		{
			$cacheManager = Application::getInstance()->getManagedCache();
			$cacheManager->clean(PaySystem\Manager::CACHE_ID);
		}

		return parent::delete($primary);
	}
}
