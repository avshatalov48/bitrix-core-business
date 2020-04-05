<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Im\Bot;

class Token
{
	const CACHE_TOKEN_TTL = 86400;
	const CACHE_TOKEN_PATH = '/bx/im/token/';
	
	public static function isActive($botId, $dialogId)
	{
		if ($botId == $dialogId)
			return true;

		$date = new \Bitrix\Main\Type\DateTime();

		$result = self::getFromCache($botId);
		return $result && $result[$dialogId] && $result[$dialogId]['DATE_EXPIRE'] >= $date->getTimestamp();
	}

	public static function add($botId, $dialogId)
	{
		return self::get($botId, $dialogId, true);
	}

	public static function get($botId, $dialogId, $prolong = false)
	{
		if ($botId == $dialogId)
			return false;

		$result = self::getFromCache($botId);

		$date = new \Bitrix\Main\Type\DateTime();
		if (!$result[$dialogId] || $result[$dialogId]['DATE_EXPIRE'] < $date->getTimestamp())
		{
			$cache = \Bitrix\Main\Data\Cache::createInstance();
			$cache->clean('token_'.$botId, self::CACHE_TOKEN_PATH);

			$orm = \Bitrix\Im\Model\BotTokenTable::add(Array(
				'DATE_EXPIRE' => $date->add('10 MINUTES'),
				'BOT_ID' => $botId,
				'DIALOG_ID' => $dialogId
			));
			if ($orm->getId() <= 0)
			{
				return false;
			}
			$addResult = $orm->getData();

			$result[$dialogId] = Array(
				'ID' => $orm->getId(),
				'TOKEN' => '',
				'DIALOG_ID' => $addResult['DIALOG_ID'],
				'DATE_EXPIRE' => $addResult['DATE_EXPIRE']->getTimestamp()
			);
		}
		else if ($prolong)
		{
			$date = new \Bitrix\Main\Type\DateTime();
			$orm = \Bitrix\Im\Model\BotTokenTable::update($result[$dialogId]['ID'], Array(
				'DATE_EXPIRE' => $date->add('10 MINUTES')
			));
			if ($orm->isSuccess())
			{
				$addResult = $orm->getData();
				$result[$dialogId]['DATE_EXPIRE'] = $addResult['DATE_EXPIRE']->getTimestamp();

				$cache = \Bitrix\Main\Data\Cache::createInstance();
				$cache->initCache(self::CACHE_TOKEN_TTL, 'token_'.$botId, self::CACHE_TOKEN_PATH);
				$cache->startDataCache();
				$cache->endDataCache($result);
			}
		}

		return $result[$dialogId];
	}

	private static function getFromCache($botId)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TOKEN_TTL, 'token_'.$botId, self::CACHE_TOKEN_PATH))
		{
			$result = $cache->getVars();
		}
		else
		{
			$result = Array();
			$orm = \Bitrix\Im\Model\BotTokenTable::getList(Array(
				'filter' => array(
					'>DATE_EXPIRE' => new \Bitrix\Main\Type\DateTime(),
					'=BOT_ID' => $botId
				),
			));
			while ($token = $orm->fetch())
			{
				$result[$token['DIALOG_ID']] = Array(
					'ID' => $token['ID'],
					'TOKEN' => $token['TOKEN'],
					'DIALOG_ID' => $token['DIALOG_ID'],
					'DATE_EXPIRE' => is_object($token['DATE_EXPIRE'])? $token['DATE_EXPIRE']->getTimestamp(): 0
				);
				if ($token['TOKEN'])
				{
					$result[$token['TOKEN']] = Array(
						'ID' => $token['ID'],
						'TOKEN' => $token['TOKEN'],
						'DIALOG_ID' => $token['DIALOG_ID'],
						'DATE_EXPIRE' => is_object($token['DATE_EXPIRE'])? $token['DATE_EXPIRE']->getTimestamp(): 0
					);
				}
			}

			$cache->startDataCache();
			$cache->endDataCache($result);
		}

		return $result;
	}
}