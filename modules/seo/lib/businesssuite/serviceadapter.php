<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Main\Security\Random;
use Bitrix\Seo\BusinessSuite\Utils;

class ServiceAdapter
{
	private const CLASS_PREFIX = 'generatedWrapper';
	private const CLASS_KEY_LENGTH = 16;
	/**
	 * @var ServiceWrapper
	 */
	protected $wrapper;


	private static function generateServiceWrapperContainerName() : string
	{
		return static::CLASS_PREFIX . Random::getStringByAlphabet(
				static::CLASS_KEY_LENGTH,
				Random::ALPHABET_ALPHALOWER
			);
	}

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



	public static function load($type)
	{
		if($serviceWrapper = Utils\ServicePool::getInstance($type)->getService())
		{
			$result = new static();
			return $result->setWrapper($serviceWrapper);
		}
		return null;
	}
	/**
	 * has auth
	 * @param $type
	 *
	 * @return bool
	 */
	public function canUse() : bool
	{
		if(isset($this->type,$this->wrapper))
		{
			return $this->getWrapper()::getAuthAdapter(Service::FACEBOOK_TYPE)->hasAuth();
		}
		return false;
	}

	/**
	 *
	 * Wrapper setter
	 * @param ServiceWrapper $wrapper
	 *
	 * @return ServiceAdapter
	 */
	public function setWrapper(?ServiceWrapper $wrapper)
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
	 * @param $type
	 *
	 * @return Config
	 */
	public function getConfig() : Config
	{
		return Config::create(Service::FACEBOOK_TYPE)->setService($this->wrapper);
	}

	/**
	 * get extension base api
	 * @return Extension
	 */
	public function getExtension() : Extension
	{
		return Extension::create(Service::FACEBOOK_TYPE)->setService($this->wrapper);
	}

}