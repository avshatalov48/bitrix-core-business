<?php

namespace Bitrix\Rest\Engine\Access;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\APAuth\PasswordTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\Marketplace\Notification;
use Bitrix\Rest\Preset\IntegrationTable;

/**
 * Class HoldEntity
 * @package Bitrix\Rest\Engine\Access
 */
class HoldEntity
{
	public const TYPE_APP = 'A';
	public const TYPE_WEBHOOK = 'W';
	private const NOTIFICATION_CODE = 'HOLD_REST_OVERLOAD';
	private const OPTION_CODE = 'hold_access_entity';
	private const MODULE_ID = 'rest';

	/**
	 * Adds entity to hold list
	 * @param string $type
	 * @param string $code
	 *
	 * @return array
	 */
	public static function add(string $type, string $code) : array
	{
		$result = [
			'success' => false
		];

		$data = static::get();
		if (!is_array($data[$type]) || !in_array($code, $data[$type]))
		{
			$data[$type][] = $code;
			$result['success'] = static::set($data);
			if ($result['success'])
			{
				$url = static::getUrl($type, $code);
				Notification::set(static::NOTIFICATION_CODE, $url);
			}
		}

		return $result;
	}

	private static function getUrl(string $type, string $code) : string
	{
		$url = '';
		$filter = [];
		if ($type === static::TYPE_APP)
		{
			$app = AppTable::getByClientId($code);
			if ($app)
			{
				if ($app['STATUS'] !== AppTable::STATUS_LOCAL)
				{
					$url = \Bitrix\Rest\Marketplace\Url::getApplicationDetailUrl($app['CODE']);
				}
				else
				{
					$filter['=APP_ID'] = $app['ID'];
				}
			}
		}
		elseif ($type === static::TYPE_WEBHOOK)
		{
			$res = PasswordTable::getList(
				[
					'filter' => [
						'=PASSWORD' => $code,
					],
					'select' => [
						'ID',
					],
					'limit' => 1,
				]
			);
			if ($password = $res->fetch())
			{
				$filter['=PASSWORD_ID'] = $password['ID'];
			}
		}
		if (!empty($filter))
		{
			$res = IntegrationTable::getList(
				[
					'filter' => $filter,
					'select' => [
						'ID',
						'ELEMENT_CODE',
					],
					'limit' => 1,
				]
			);
			if ($item = $res->fetch())
			{
				$url = \Bitrix\Rest\Url\DevOps::getInstance()->getIntegrationEditUrl($item['ID'], $item['ELEMENT_CODE']);
			}
		}

		return $url;
	}

	/**
	 * Delete entity from hold list
	 * @param string $type
	 * @param string $code
	 *
	 * @return array
	 */
	public static function delete(string $type, string $code) : array
	{
		$result = [
			'success' => false
		];
		$data = static::get();
		if (is_array($data[$type]))
		{
			$key = array_search($code, $data[$type]);
			if ($key !== false)
			{
				if (count($data[$type]) === 1)
				{
					unset($data[$type]);
				}
				else
				{
					unset($data[$type][$key]);
				}
				$result['success'] = static::set($data);
			}
		}

		return $result;
	}

	/**
	 * Checks entity in hold list
	 * @param string $type
	 * @param string $code
	 *
	 * @return bool
	 */
	public static function is(string $type, string $code) : bool
	{
		$list = static::get();
		return isset($list[$type]) && in_array($code, $list[$type]);
	}

	/**
	 * Resets all hold entity
	 * @return bool
	 */
	public static function reset() : bool
	{
		return static::set([]);
	}

	/**
	 * Returns list of hold entity
	 * @return array
	 */
	public static function get() : array
	{
		$option = Option::get(static::MODULE_ID, static::OPTION_CODE, false);

		return $option ? Json::decode($option) : [];
	}

	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	private static function set(array $data) : bool
	{
		if (!empty($data))
		{
			Option::set(static::MODULE_ID, static::OPTION_CODE, Json::encode($data));
		}
		else
		{
			Option::delete(static::MODULE_ID, ['name' => static::OPTION_CODE]);
			Notification::reset();
		}

		return true;
	}
}