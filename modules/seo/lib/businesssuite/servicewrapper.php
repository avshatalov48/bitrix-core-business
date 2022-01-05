<?php

namespace Bitrix\Seo\BusinessSuite;


use Bitrix\Main\NotImplementedException;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\BusinessSuite\AuthAdapter\Facebook\BusinessAuthAdapter;

class ServiceWrapper implements  Retargeting\IService, Retargeting\IMultiClientService, IInternalService
{
	/** @var ServiceMetaData $meta*/
	protected $metaData;

	/** @var BusinessAuthAdapter[] $authAdapterPool*/
	protected static $authAdapterPool = [];

	/** @var static $instance*/
	protected static $instance = [];

	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * Get instance of service Wrapper
	 * @return static
	 */
	public static function getInstance(): ServiceWrapper
	{
		if (!static::$instance[$key = get_called_class()])
		{
			static::$instance[$key] = new static();
		}

		return static::$instance[$key];
	}

	/**
	 * @return bool|null
	 */
	public static function canUseMultipleClients() : ?bool
	{
		$internal = static::getInstance()->getMetaData()->getService();
		return $internal instanceof Retargeting\IMultiClientService && $internal::canUseMultipleClients();
	}

	/**
	 * @return string
	 */
	public function getClientId(): string
	{
		return $this->metaData->getClientId();
	}

	/**
	 * @param string $clientId
	 *
	 * @return mixed|void
	 * @throws NotImplementedException
	 */
	public function setClientId($clientId)
	{
		throw new NotImplementedException("method not implement");
	}

	/**
	 * @param string $type
	 *
	 * @return string|null
	 */
	public static function getEngineCode($type) : ?string
	{
		if($service = static::getInstance()->getMetaData()->getService())
		{

			return $service::getEngineCode($type);
		}

		return null;
	}

	/**
	 * @return array|null
	 */
	public static function getTypes() : ?array
	{
		if($service = static::getInstance()->getMetaData()->getService())
		{

			return $service::getTypes();
		}

		return null;
	}

	/**
	 * @param string $type
	 *
	 * @return BusinessAuthAdapter
	 * @throws \Bitrix\Main\SystemException
	 */
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
		if($service = static::getInstance()->getMetaData()->getService())
		{

			return $service::getTypeByEngine($engineCode);
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public static function canUseAsInternal(): bool
	{
		if($service = static::getInstance()->getMetaData()->getService())
		{

			return $service::canUseAsInternal();
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public static function getMethodPrefix(): string
	{
		if(($meta = static::getInstance()->getMetaData()) && $service = $meta->getService())
		{
			return $service::getMethodPrefix();
		}

		return '';
	}

	/**
	 * set service meta
	 *
	 * @param ServiceMetaData|null $metaData
	 *
	 * @return ServiceWrapper
	 */
	public function setMeta(?ServiceMetaData $metaData): ServiceWrapper
	{
		$this->metaData = $metaData;

		return $this;
	}

	/**
	 * get service meta
	 * @return ServiceMetaData|null
	 */
	public function getMetaData() : ?ServiceMetaData
	{
		return $this->metaData;
	}
};