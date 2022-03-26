<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\UserConsent\Agreement;

Loc::loadMessages(__FILE__);

/**
 * Class AgreementTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Agreement_Query query()
 * @method static EO_Agreement_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Agreement_Result getById($id)
 * @method static EO_Agreement_Result getList(array $parameters = [])
 * @method static EO_Agreement_Entity getEntity()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection createCollection()
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement wakeUpObject($row)
 * @method static \Bitrix\Main\UserConsent\Internals\EO_Agreement_Collection wakeUpCollection($rows)
 */
class AgreementTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_consent_agreement';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'required' => true,
				'default_value' => Agreement::ACTIVE,
				'values' => array(Agreement::NOT_ACTIVE, Agreement::ACTIVE)
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('MAIN_USER_CONSENT_TBL_AGREEMENT_FIELD_TITLE_NAME'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => Agreement::TYPE_STANDARD,
				'values' => array(Agreement::TYPE_CUSTOM, Agreement::TYPE_STANDARD)
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
			),
			'DATA_PROVIDER' => array(
				'data_type' => 'string',
			),
			'AGREEMENT_TEXT' => array(
				'data_type' => 'text',
			),
			'LABEL_TEXT' => array(
				'data_type' => 'string',
			),
			'SECURITY_CODE' => array(
				'data_type' => 'string',
				'default_value' => function()
				{
					return Random::getString(6);
				}
			),
			'USE_URL' => [
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => ['Y', 'N']
			],
			'URL' => [
				'data_type' => 'string',
			],
			'IS_AGREEMENT_TEXT_HTML' => [
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => ['Y', 'N']
			],
		);
	}

	/**
	 * After delete event handler.
	 *
	 * @param Entity\Event $event Event object.
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$sql = /** @lang MySQL */ "DELETE FROM " . ConsentTable::getTableName() . " WHERE AGREEMENT_ID = " . intval($data['primary']['ID']);
		Application::getConnection()->query($sql);

		return $result;
	}
}
