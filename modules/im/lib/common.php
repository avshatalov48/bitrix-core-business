<?php
namespace Bitrix\Im;

use Bitrix\Im\V2\ActionUuid;

class Common
{
	public static function getPublicDomain()
	{
		$schema = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps()? "https" : "http";

		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
		{
			$domain = SITE_SERVER_NAME;
		}
		else
		{
			$domain = \Bitrix\Main\Config\Option::get("main", "server_name", '');
			if (!$domain)
			{
				$domain = $_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
			}
		}

		return $schema."://".$domain;
	}

	public static function objectEncode($params, $pureJson = false)
	{
		if (is_array($params))
		{
			array_walk_recursive($params, function(&$value, $key)
			{
				if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$value = date('c', $value->getTimestamp());
				}
				else if (is_string($key) && in_array($key, ['AVATAR', 'AVATAR_HR']) && is_string($value) && $value && mb_strpos($value, 'http') !== 0)
				{
					$value = \Bitrix\Im\Common::getPublicDomain().$value;
				}
			});
		}

		return $pureJson? self::jsonEncode($params): \CUtil::PhpToJSObject($params);
	}

	public static function jsonEncode($array = [])
	{
		return \Bitrix\Main\Web\Json::encode($array, JSON_UNESCAPED_UNICODE);
	}

	public static function getCacheUserPostfix($id)
	{
		return '/'.mb_substr(md5($id), 2, 2).'/'.intval($id);
	}

	public static function isChatId($id)
	{
		return $id && preg_match('/^(chat|sg|crm)[0-9]{1,}$/i', $id);
	}

	public static function isDialogId($id)
	{
		return $id && preg_match('/^([0-9]{1,}|(chat|sg|crm)[0-9]{1,})$/i', $id);
	}

	public static function getUserId($userId = null)
	{
		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		return $userId;
	}

	public static function toJson($array, $camelCase = true)
	{
		$result = [];
		foreach ($array as $field => $value)
		{
			if (is_array($value))
			{
				$value = self::toJson($value, $camelCase);
			}
			else if ($value instanceof \Bitrix\Main\Type\DateTime)
			{
				$value = date('c', $value->getTimestamp());
			}
			else if (is_string($value) && $value && is_string($field) && in_array($field, Array('AVATAR')) && mb_strpos($value, 'http') !== 0)
			{
				$value = \Bitrix\Im\Common::getPublicDomain().$value;
			}

			if ($camelCase)
			{
				$field = lcfirst(\Bitrix\Main\Text\StringHelper::snake2camel($field));
			}
			else
			{
				$field = mb_strtolower($field);
			}

			$result[$field] = $value;
		}

		return $result;
	}

	public static function getExternalAuthId($skipTypes = [])
	{
		return \Bitrix\Im\Model\UserTable::filterExternalUserTypes($skipTypes);
	}

	public static function getPullExtra()
	{
		$extra = [
			'revision_im_web' => \Bitrix\Im\Revision::getWeb(),
			'revision_im_mobile' => \Bitrix\Im\Revision::getMobile(),
			'revision_im_rest' => \Bitrix\Im\Revision::getRest(),
			// deprecated
			'im_revision' => \Bitrix\Im\Revision::getWeb(),
			'im_revision_mobile' => \Bitrix\Im\Revision::getMobile(),
			'is_shared_event' => false,
		];

		if (ActionUuid::getInstance()->getValue() !== null)
		{
			$extra['action_uuid'] = ActionUuid::getInstance()->getValue();
		}

		return $extra;
	}
}

