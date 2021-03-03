<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\AuthAdapter\Facebook\BusinessAuthAdapter;

class ServiceWrapper implements  Retargeting\IService, Retargeting\IMultiClientService, IInternalService
{

	/** @var BusinessAuthAdapter[] $authAdapterPool*/
	protected static $authAdapterPool = [];

	/** @var Retargeting\IService|IInternalService|null $internalService */
	protected $internalService;

	/** @var $clientId*/
	protected $clientId;

	/** @var static $instance*/
	protected static $instance = [];

	public static function getInstance()
	{
		if (!static::$instance[$key = get_called_class()])
		{
			static::$instance[$key] = new static();
		}
		return static::$instance[$key];
	}

	public static function canUseMultipleClients() : ?bool
	{
		$internal = static::getInstance()->internalService;
		return $internal instanceof Retargeting\IMultiClientService && $internal::canUseMultipleClients();
	}

	public function getClientId()
	{
		return $this->clientId;
	}

	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
		return $this;
	}

	public static function getEngineCode($type) : ?string
	{
		if($service = static::getInstance()->internalService)
		{
			return $service::getEngineCode($type);
		}
		return null;
	}

	public static function getTypes() : ?array
	{
		if($service = static::getInstance()->internalService)
		{
			return $service::getTypes();
		}
		return null;
	}

	public function setService(?Retargeting\IService $service) : self
	{
		$this->internalService = $service;
		return $this;
	}

	public static function getAuthAdapter($type) : BusinessAuthAdapter
	{
		static::$authAdapterPool[$key] =  static::$authAdapterPool[$key = get_called_class()] ?? [];
		if (!array_key_exists($type,static::$authAdapterPool[$key]))
		{
			static::$authAdapterPool[$key][$type] = BusinessAuthAdapter::create($type)->setService(static::getInstance());
		}
		return static::$authAdapterPool[$key][$type];
	}

	/**
	 * @inheritDoc
	 */
	public static function getTypeByEngine(string $engineCode): ?string
	{
		if($service = static::getInstance()->internalService)
		{
			return $service->getTypeByEngine($engineCode);
		}
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function canUseAsInternal(): bool
	{
		if($service = static::getInstance()->internalService)
		{
			return $service->canUseAsInternal();
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		if($service = static::getInstance()->internalService)
		{
			return $service->getMethodPrefix();
		}
		return '';
	}
};