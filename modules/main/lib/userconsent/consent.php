<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Consent
 * @package Bitrix\Main\UserConsent
 */
class Consent
{
	const EVENT_NAME_LIST = 'OnUserConsentProviderList';

	/**
	 * Add user consent by context data.
	 *
	 * @param integer $id Agreement ID.
	 * @param integer|null $originatorId Originator ID.
	 * @param integer|null $originId Origin ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	public static function addByContext($id, $originatorId = null, $originId = null, array $data = array())
	{
		$agreement = new Agreement($id);
		if (!$agreement->isExist() || !$agreement->isActive())
		{
			return null;
		}

		$request = Context::getCurrent()->getRequest();
		$parameters = array(
			'AGREEMENT_ID' => $id
		);

		/**@var \CAllUser */
		if (isset($GLOBALS['USER']) && is_object($GLOBALS['USER']) && $GLOBALS['USER']->GetID())
		{
			$parameters['USER_ID'] = $GLOBALS['USER']->GetID();
		}

		$parameters['IP'] = (isset($data['IP']) && $data['IP']) ? $data['IP'] : $request->getRemoteAddress();
		if (isset($data['URL']) && $data['URL'])
		{
			$parameters['URL'] = $data['URL'];
		}
		else
		{
			$parameters['URL'] = $request->getHttpHost() . $request->getRequestUri();
		}

		if ($originatorId && $originId)
		{
			$parameters['ORIGINATOR_ID'] = $originatorId;
			$parameters['ORIGIN_ID'] = $originId;
		}
		$addResult = Internals\ConsentTable::add($parameters);
		if ($addResult->isSuccess())
		{
			return $addResult->getId();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get origin data.
	 *
	 * @param string $originatorId Originator ID.
	 * @param string|integer|null $originId Origin ID.
	 * @return array|null
	 */
	public static function getOriginData($originatorId, $originId = null)
	{
		$list = self::getList();
		foreach ($list as $provider)
		{
			if ($provider['CODE'] != $originatorId)
			{
				continue;
			}
			$name = null;
			$url = null;
			if ($originId)
			{
				$data = $provider['DATA']($originId);
				if (!is_array($data))
				{
					return null;
				}

				if (isset($data['NAME']))
				{
					$name = $data['NAME'];
				}
				else
				{
					return null;
				}

				if (isset($data['URL']))
				{
					$url = $data['URL'];
				}
			}
			else
			{
				if (isset($provider['NAME']))
				{
					$name = $provider['NAME'];
				}
				else
				{
					return null;
				}
			}

			return array(
				'NAME' => $name,
				'URL' => $url
			);
		}

		return null;
	}

	/**
	 * Get list.
	 *
	 * @return static[]
	 */
	protected static function getList()
	{
		$data = array();
		$event = new Event('main', self::EVENT_NAME_LIST, array($data));
		$event->send();

		static $list = null;
		if ($list !== null)
		{
			return $list;
		}

		$list = array();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == EventResult::ERROR)
			{
				continue;
			}

			$params = $eventResult->getParameters();
			if(!$params || !is_array($params))
			{
				continue;
			}

			foreach ($params as $item)
			{
				if (!is_array($item) || !isset($item['CODE']) || !isset($item['NAME']))
				{
					continue;
				}

				if (!isset($item['DATA']) || !is_callable($item['DATA']))
				{
					continue;
				}

				$list[] = $item;
			}
		}

		return $list;
	}
}