<?php

namespace Bitrix\Seo\Checkout;

/**
 * Class Service
 * @package Bitrix\Seo\Checkout
 */
class Service implements IService
{
	const GROUP = 'checkout';
	const TYPE_YANDEX = 'yandex';
	const TYPE_YOOKASSA = 'yookassa';

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		static $instance = null;
		if ($instance === null)
		{
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function getEngineCode($type)
	{
		return static::GROUP.'.'.$type;
	}

	/**
	 * @return array
	 */
	public static function getTypes()
	{
		return [
			static::TYPE_YANDEX,
			static::TYPE_YOOKASSA,
		];
	}

	/**
	 * Get auth adapter.
	 *
	 * @param string $type Type.
	 * @return AuthAdapter
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getAuthAdapter($type)
	{
		return AuthAdapter::create($type)->setService(static::getInstance());
	}
}