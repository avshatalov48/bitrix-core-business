<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Authentication;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class ApplicationPasswordTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ApplicationPassword_Query query()
 * @method static EO_ApplicationPassword_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ApplicationPassword_Result getById($id)
 * @method static EO_ApplicationPassword_Result getList(array $parameters = [])
 * @method static EO_ApplicationPassword_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\EO_ApplicationPassword_Collection wakeUpCollection($rows)
 */
class ApplicationPasswordTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	protected const PASSWORD_ALPHABET = "qwertyuiopasdfghjklzxcvbnm";
	protected const PASSWORD_LENGTH = 16;

	public static function getTableName()
	{
		return "b_app_password";
	}

	public static function getMap()
	{
		return array(
			new Fields\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Fields\IntegerField('USER_ID', array(
				'required' => true,
				'validation' => '\Bitrix\Main\Authentication\ApplicationPasswordTable::getUserValidators',
			)),
			new Fields\StringField('APPLICATION_ID', array(
				'required' => true,
			)),
			new Fields\StringField('PASSWORD', array(
				'required' => true,
			)),
			new Fields\StringField('DIGEST_PASSWORD'),
			new Fields\DatetimeField('DATE_CREATE'),
			new Fields\DatetimeField('DATE_LOGIN'),
			new Fields\StringField('LAST_IP'),
			new Fields\StringField('COMMENT'),
			new Fields\StringField('SYSCOMMENT'),
			new Fields\StringField('CODE'),
			new Fields\Relations\Reference(
				'USER',
				'Bitrix\Main\User',
				array('=this.USER_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
		);
	}

	public static function getUserValidators()
	{
		return array(
			new Fields\Validators\ForeignValidator(Main\UserTable::getEntity()->getField('ID')),
		);
	}

	public static function onBeforeAdd(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$data = $event->getParameter("fields");

		if(isset($data["USER_ID"]) && isset($data['PASSWORD']))
		{
			$modified = [
				'PASSWORD' => Main\Security\Password::hash($data['PASSWORD']),
			];

			$user = Main\UserTable::getRowById($data["USER_ID"]);
			if($user !== null)
			{
				$realm = (defined('BX_HTTP_AUTH_REALM')? BX_HTTP_AUTH_REALM : "Bitrix Site Manager");
				$digest = md5($user["LOGIN"].':'.$realm.':'.$data['PASSWORD']);
				$modified['DIGEST_PASSWORD'] = $digest;
			}

			$result->modifyFields($modified);
		}
		return $result;
	}

	public static function onDelete(ORM\Event $event)
	{
		$id = $event->getParameter("id");

		$row = static::getRowById($id);
		if($row)
		{
			Main\UserAuthActionTable::addLogoutAction($row["USER_ID"], $row["APPLICATION_ID"]);
		}
	}

	/**
	 * Generates a random password.
	 * @return string
	 */
	public static function generatePassword()
	{
		return Main\Security\Random::getStringByCharsets(static::PASSWORD_LENGTH, static::PASSWORD_ALPHABET);
	}

	/**
	 * Checks if the string is similar to a password by its structure.
	 * @param string $password
	 * @return bool
	 */
	public static function isPassword($password)
	{
		if (is_string($password))
		{
			$password = str_replace(' ', '', $password);

			if(strlen($password) === static::PASSWORD_LENGTH)
			{
				return (!preg_match("/[^".static::PASSWORD_ALPHABET."]/", $password));
			}
		}
		return false;
	}

	/**
	 * Finds the application by the user's password.
	 *
	 * @param int $userId
	 * @param string $password
	 * @param bool $passwordOriginal
	 * @return array|false
	 */
	public static function findPassword($userId, $password, $passwordOriginal = true)
	{
		if($passwordOriginal)
		{
			$password = str_replace(' ', '', $password);
		}

		$appPasswords = static::getList(array(
			'select' => array('ID', 'PASSWORD', 'APPLICATION_ID'),
			'filter' => array('=USER_ID' => $userId),
		));
		while(($appPassword = $appPasswords->fetch()))
		{
			if(Main\Security\Password::equals($appPassword["PASSWORD"], $password, $passwordOriginal))
			{
				//bingo, application password
				return $appPassword;
			}
		}
		return false;
	}

	/**
	 * Finds the application by the user's digest authentication.
	 *
	 * @param int $userId
	 * @param array $digest See CHTTP::ParseDigest() for the array structure.
	 * @return array|false
	 */
	public static function findDigestPassword($userId, array $digest)
	{
		$appPasswords = static::getList(array(
			'select' => array('PASSWORD', 'DIGEST_PASSWORD', 'APPLICATION_ID'),
			'filter' => array('=USER_ID' => $userId),
		));

		$server = Main\Context::getCurrent()->getServer();
		$method = ($server['REDIRECT_REQUEST_METHOD'] !== null? $server['REDIRECT_REQUEST_METHOD'] : $server['REQUEST_METHOD']);
		$HA2 = md5($method.':'.$digest['uri']);

		while(($appPassword = $appPasswords->fetch()))
		{
			$HA1 = $appPassword["DIGEST_PASSWORD"];
			$valid_response = md5($HA1.':'.$digest['nonce'].':'.$HA2);

			if($digest["response"] === $valid_response)
			{
				//application password
				return $appPassword;
			}
		}
		return false;
	}
}
