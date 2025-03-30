<?php

namespace Bitrix\Rest\APAuth;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\Security\Random;
use Bitrix\Main\ORM;
use Bitrix\Rest\Preset\EventController;
use Bitrix\Rest\Enum;
use Bitrix\Rest\Service\ServiceContainer;

Loc::loadMessages(__FILE__);

/**
 * Class ApTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> AP string(50) mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> TITLE string(255) optional
 * <li> COMMENT string(255) optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_LOGIN datetime optional
 * <li> LAST_IP string(255) optional
 * </ul>
 *
 * @package Bitrix\Rest
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Password_Query query()
 * @method static EO_Password_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Password_Result getById($id)
 * @method static EO_Password_Result getList(array $parameters = [])
 * @method static EO_Password_Entity getEntity()
 * @method static \Bitrix\Rest\APAuth\EO_Password createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\APAuth\EO_Password_Collection createCollection()
 * @method static \Bitrix\Rest\APAuth\EO_Password wakeUpObject($row)
 * @method static \Bitrix\Rest\APAuth\EO_Password_Collection wakeUpCollection($rows)
 */
class PasswordTable extends ORM\Data\DataManager
{
	use DeleteByFilterTrait;

	const ACTIVE = 'Y';
	const INACTIVE = 'N';

	const DEFAULT_LENGTH = 16;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_ap';
	}

	/**
	 * Returns entity map definition.
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
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'PASSWORD' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array(static::INACTIVE, static::ACTIVE),
			),
			(new EnumField('TYPE'))
				->configureTitle('Type')
				->configureValues(Enum\APAuth\PasswordType::getValues())
				->configureDefaultValue(Enum\APAuth\PasswordType::User->value),
			'TITLE' => array(
				'data_type' => 'string',
			),
			'COMMENT' => array(
				'data_type' => 'string',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
			),
			'DATE_LOGIN' => array(
				'data_type' => 'datetime',
			),
			'LAST_IP' => array(
				'data_type' => 'string',
			),
		);
	}

	public static function generatePassword($length = self::DEFAULT_LENGTH)
	{
		return Random::getString($length);
	}


	/**
	 * Generates AP for REST access.
	 *
	 * @param string $siteTitle Site title for AP description.
	 *
	 * @return bool|string password or false
	 * @throws \Exception
	 */
	public static function createPassword($userId, array $scopeList, $siteTitle, $returnArray = false)
	{
		$password = static::generatePassword();
		$passwordData = [
			'USER_ID' => $userId,
			'PASSWORD' => $password,
			'DATE_CREATE' => new Main\Type\DateTime(),
			'TITLE' => Loc::getMessage('REST_APP_SYSCOMMENT', array(
				'#TITLE#' => $siteTitle,
			)),
			'COMMENT' => Loc::getMessage('REST_APP_COMMENT'),
		];
		$res = static::add($passwordData);

		if($res->isSuccess())
		{
			$scopeList = array_unique($scopeList);
			foreach($scopeList as $scope)
			{
				PermissionTable::add(array(
					'PASSWORD_ID' => $res->getId(),
					'PERM' => $scope,
				));
			}

			$passwordData['ID'] = $res->getId();
			if(!$returnArray)
			{
				$return = $password;
			}
			else
			{
				$return = $passwordData;
			}

			return $return;
		}

		return false;
	}

	public static function onAfterAdd(Main\Entity\Event $event)
	{
		EventController::onAfterAddAp($event);
	}

	public static function onAfterDelete(ORM\Event $event)
	{
		self::clearServiceCache((int)$event->getParameter('id'));
	}

	public static function onAfterUpdate(ORM\Event $event)
	{
		self::clearServiceCache((int)$event->getParameter('id'));
	}

	private static function clearServiceCache(int $id): void
	{
		ServiceContainer::getInstance()
			->getAPAuthPasswordService()
			->clearCacheById($id)
		;
	}
}
