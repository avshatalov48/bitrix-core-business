<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Main\Security\Random;
use Bitrix\Seo\BusinessSuite\Utils;

class ServiceAdapter
{
	private const CLASS_PREFIX = 'generatedWrapper';
	private const CLASS_KEY_LENGTH = 16;

	/** @var ServiceWrapper $wrapper*/
	protected $wrapper;

	/**
	 * generate container name
	 * @return string
	 */
	private static function generateServiceWrapperContainerName() : string
	{
		return static::CLASS_PREFIX . Random::getStringByAlphabet(static::CLASS_KEY_LENGTH, Random::ALPHABET_ALPHALOWER);
	}

	/**
	 * Init instance
	 * @return ServiceWrapper
	 */
	public final static function createServiceWrapperContainer() : ServiceWrapper
	{
		while ($serviceWrapperClassName = static::generateServiceWrapperContainerName())
		{
			if(!class_exists($serviceWrapperClassName,false))
			{
				eval("
						class $serviceWrapperClassName extends Bitrix\Seo\BusinessSuite\ServiceWrapper
							implements
								Bitrix\Seo\Retargeting\IService,
								Bitrix\Seo\Retargeting\IMultiClientService,
								Bitrix\Seo\BusinessSuite\IInternalService
						{}
				");
				return $serviceWrapperClassName::getInstance();
			}
		}
	}

	/**
	 * Create Adapter from [instagram, facebook] service types
	 * @return ServiceAdapter|null
	 */
	public static function loadFacebookService(): ?ServiceAdapter
	{
		if($serviceWrapper = Utils\ServicePool::getService([Service::INSTAGRAM_TYPE,Service::FACEBOOK_TYPE]))
		{

			return (new static())->setWrapper($serviceWrapper);
		}

		return null;
	}

	/**
	 * Create Adapter
	 * @param $type
	 *
	 * @return ServiceAdapter|null
	 */
	public static function load($type): ?ServiceAdapter
	{
		if($serviceWrapper = Utils\ServicePool::getService($type))
		{

			return (new static())->setWrapper($serviceWrapper);
		}

		return null;
	}

	/**
	 * has auth
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	public function canUse() : bool
	{
		if(isset($this->wrapper))
		{

			return $this->getWrapper()::getAuthAdapter($this->wrapper->getMetaData()->getType())->hasAuth();
		}

		return false;
	}

	/**
	 *
	 * Wrapper setter
	 *
	 * @param ServiceWrapper|null $wrapper
	 *
	 * @return ServiceAdapter
	 */
	public function setWrapper(?ServiceWrapper $wrapper): ServiceAdapter
	{
		$this->wrapper = $wrapper;
		return $this;
	}

	/**
	 * Wrapper getter
	 * @return ServiceWrapper
	 */
	public function getWrapper() : ?ServiceWrapper
	{
		return $this->wrapper;
	}

	/**
	 * get config base api
	 *
	 * @return Config|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getConfig() : ?Config
	{
		if ($this->canUse())
		{
			return Config::create($this->wrapper->getMetaData()->getType())->setService($this->wrapper);
		}
		return null;
	}

	/**
	 * get extension base api
	 * @return Extension|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getExtension() : ?Extension
	{
		if ($this->canUse())
		{
			return Extension::create($this->wrapper->getMetaData()->getType())->setService($this->wrapper);
		}
		return null;
	}

	/**
	 * get conversion base api
	 * @return Conversion
	 */
	public function getConversion(): Conversion
	{
		return Conversion::create($this->wrapper->getMetaData()->getType())->setService($this->wrapper);
	}
}