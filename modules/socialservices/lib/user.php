<?
namespace Bitrix\Socialservices;

use \Bitrix\Main\Entity;
use Bitrix\Main\ORM\Event;
use Bitrix\Socialservices\EncryptedToken\FieldValue;


/**
 * Class UserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_User_Query query()
 * @method static EO_User_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_User_Result getById($id)
 * @method static EO_User_Result getList(array $parameters = array())
 * @method static EO_User_Entity getEntity()
 * @method static \Bitrix\Socialservices\EO_User createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialservices\EO_User_Collection createCollection()
 * @method static \Bitrix\Socialservices\EO_User wakeUpObject($row)
 * @method static \Bitrix\Socialservices\EO_User_Collection wakeUpCollection($rows)
 */
class UserTable extends Entity\DataManager
{
	const ALLOW = 'Y';
	const DISALLOW = 'N';

	const INITIALIZED = 'Y';
	const NOT_INITIALIZED = 'N';

	private static $deletedList = array();

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_socialservices_user';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOGIN' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'LAST_NAME' => array(
				'data_type' => 'string',
			),
			'EMAIL' => array(
				'data_type' => 'string',
			),
			'PERSONAL_PHOTO' => array(
				'data_type' => 'string',
			),
			'EXTERNAL_AUTH_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CAN_DELETE' => array(
				'data_type' => 'boolean',
				'values' => array(self::DISALLOW, self::ALLOW)
			),
			'PERSONAL_WWW' => array(
				'data_type' => 'string',
			),
			'PERMISSIONS' => array(
				'data_type' => 'string',
				'serizalized' => true,
			),
			'OATOKEN' => array(
				'data_type' => '\\Bitrix\\Socialservices\\EncryptedToken\\CryptoField',
				'encryption_complete' => static::cryptoEnabled('OATOKEN')
			),
			'OATOKEN_EXPIRES' => array(
				'data_type' => 'integer',
			),
			'OASECRET' => array(
				'data_type' => '\\Bitrix\\Socialservices\\EncryptedToken\\CryptoField',
				'encryption_complete' => static::cryptoEnabled('OASECRET')
			),
			'REFRESH_TOKEN' => array(
				'data_type' => '\\Bitrix\\Socialservices\\EncryptedToken\\CryptoField',
				'encryption_complete' => static::cryptoEnabled('REFRESH_TOKEN')
			),
			'SEND_ACTIVITY' => array(
				'data_type' => 'boolean',
				'values' => array(self::DISALLOW, self::ALLOW)
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'INITIALIZED' => array(
				'data_type' => 'boolean',
				'values' => array(self::NOT_INITIALIZED, self::INITIALIZED)
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);

		return $fieldsMap;
	}

	public static function filterFields($fields, $oldValue = null)
	{
		$map = static::getMap();
		foreach($fields as $key => $value)
		{
			if(!array_key_exists($key, $map))
			{
				unset($fields[$key]);
			}
			elseif($map[$key]['required'] && empty($fields[$key]))
			{
				unset($fields[$key]);
			}
		}

		if(array_key_exists('PERSONAL_PHOTO', $fields) && is_array($fields['PERSONAL_PHOTO']))
		{
			$needUpdatePersonalPhoto = true;
			$fields['PERSONAL_PHOTO']['MODULE_ID'] = 'socialservices';
			$fields['PERSONAL_PHOTO']['external_id'] = md5_file($fields['PERSONAL_PHOTO']['tmp_name']);
			if ($oldValue['PERSONAL_PHOTO'])
			{
				$oldPersonalPhoto = \CFile::GetByID($oldValue['PERSONAL_PHOTO'])->Fetch();
				if ($oldPersonalPhoto['EXTERNAL_ID'] == $fields['PERSONAL_PHOTO']['external_id'])
				{
					$needUpdatePersonalPhoto = false;
				}
				$fields['PERSONAL_PHOTO']['del'] = 'Y';
				$fields['PERSONAL_PHOTO']['old_file'] = $oldValue['PERSONAL_PHOTO'];
			}
			if ($needUpdatePersonalPhoto)
			{
				$fields['PERSONAL_PHOTO'] = \CFile::SaveFile($fields['PERSONAL_PHOTO'], 'socialservices');
			}
			else
			{
				unset($fields['PERSONAL_PHOTO']);
			}
		}

		return $fields;
	}

	public static function onBeforeDelete(Event $event)
	{
		$primary = $event->getParameter("primary");
		$ID = $primary["ID"];
		$dbRes = static::getByPrimary($ID);
		self::$deletedList[$ID] = $dbRes->fetch();
	}

	public static function onAfterDelete(Event $event)
	{
		$primary = $event->getParameter("primary");
		$ID = $primary["ID"];
		$userInfo = self::$deletedList[$ID];
		if($userInfo)
		{
			UserLinkTable::deleteBySocserv($userInfo["USER_ID"], $userInfo["ID"]);

			if($userInfo["EXTERNAL_AUTH_ID"] === \CSocServBitrix24Net::ID)
			{
				$interface = new \CBitrix24NetOAuthInterface();
				$interface->setToken($userInfo["OATOKEN"]);
				$interface->setAccessTokenExpires($userInfo["OATOKEN_EXPIRES"]);
				$interface->setRefreshToken($userInfo["REFRESH_TOKEN"]);

				if($interface->checkAccessToken() || $interface->getNewAccessToken())
				{
					$interface->RevokeAuth();
				}
			}

			if($userInfo["PERSONAL_PHOTO"])
			{
				\CFile::Delete($userInfo["PERSONAL_PHOTO"]);
			}
		}
	}
}
