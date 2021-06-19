<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Seo;
use Bitrix\Seo\BusinessSuite\Exception;

final class ServiceFactory
{
	/** @var Seo\BusinessSuite\IInternalService[] $enginePool*/
	private static $enginePool;

	/**
	 * @return Seo\BusinessSuite\IInternalService[]
	 */
	private static function getServices() : array
	{
		return [
			Seo\BusinessSuite\Service::getInstance(),
			Seo\LeadAds\Service::getInstance(),
			Seo\Analytics\Service::getInstance(),
			Seo\Retargeting\Service::getInstance(),
			Seo\Marketing\Service::getInstance()
		];
	}

	/**
	 * @return Seo\BusinessSuite\IInternalService[]
	 */
	private static function getEnginePool() : array
	{
		if(!static::$enginePool)
		{
			static::$enginePool = [];
			foreach (self::getServices() as $service)
			{
				foreach ($service::getTypes() as $type)
				{
					static::$enginePool[$service::getEngineCode($type)] = $service;
				}
			}
		}
		return static::$enginePool;
	}

	/**
	 * Return service instance by engine code
	 * @param string $engineCode
	 *
	 * @return Seo\BusinessSuite\IInternalService
	 * @throws Exception\ServiceLoadException
	 */
	public static function getServiceByEngineCode(string $engineCode) : Seo\BusinessSuite\IInternalService
	{
		if(array_key_exists($engineCode,$pool = static::getEnginePool()))
		{
			return $pool[$engineCode];
		}
		throw new Exception\ServiceLoadException('EngineCode');
	}
}