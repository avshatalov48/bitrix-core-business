<?php

namespace Bitrix\Seo\Checkout;

/**
 * Class Service
 * @package Bitrix\Seo\Checkout
 */
class Service implements IService
{
	public const GROUP = 'checkout';
	public const TYPE_YANDEX = 'yandex';
	public const TYPE_YOOKASSA = 'yookassa';
	public const TYPE_TBANK_BUSINESS = 'tbankbusiness';

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function getInstance(): static
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
	public static function getEngineCode($type): string
	{
		return static::GROUP . '.' . $type;
	}

	/**
	 * @return array
	 */
	public static function getTypes(): array
	{
		return [
			static::TYPE_YANDEX,
			static::TYPE_YOOKASSA,
			static::TYPE_TBANK_BUSINESS,
		];
	}

	/**
	 * Get auth adapter.
	 *
	 * @param string $type Type.
	 * @return AuthAdapter
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getAuthAdapter($type): AuthAdapter
	{
		return AuthAdapter::create($type)->setService(static::getInstance());
	}
}