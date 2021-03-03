<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Main;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\ErrorCollection;
use Bitrix\Seo\Retargeting\IService;
use Bitrix\Seo\BusinessSuite\Service;
use Bitrix\Seo\BusinessSuite\Internals;
use Bitrix\Seo\BusinessSuite\IInternalService;
use Bitrix\Seo\Retargeting\IMultiClientService;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;

final class QueueEventHandler
{

	/** @var ErrorCollection $errorCollection Error collection.  */
	private $errorCollection;

	/**@var IService*/
	private $service;

	/**@var int|null $clientId*/
	private $clientId;

	/**@var string|null $engineCode*/
	private $engineCode;

	/**@var string|null $type*/
	private $type;


	private static function getInstance() : self
	{
		return new self;
	}

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection();
	}

	private function setClientId($clientId) : self
	{
		if(!$this->hasErrors())
		{
			if(isset($clientId,$this->service,$this->type) && is_int($clientId))
			{
				$authAdapter = $this->service::getAuthAdapter($this->type);
				if($this->service instanceof IMultiClientService && $authAdapter->canUseMultipleClients())
				{
					$this->service->setClientId($clientId);
				}
				if($authAdapter->hasAuth())
				{
					$this->clientId = $clientId;
					return $this;
				}
			}
			$this->errorCollection->setError(new Main\Error("client has no auth"));
		}
		return $this;
	}
	private function setEngineCode($code) : self
	{
		if(!$this->hasErrors())
		{
			try
			{
				if(is_string($code) && $this->service = ServiceFactory::getServiceByEngineCode($code))
				{
					if($this->service instanceof IInternalService && $this->service::canUseAsInternal())
					{
						$this->engineCode = $code;
						return $this->setType($this->service::getTypeByEngine($this->engineCode));
					}
				}
			}
			catch (\Throwable $exception)
			{
				$this->errorCollection->setError(new Main\Error($exception->getMessage()));
			}
		}
		return $this;
	}
	private function setType($type) : self
	{
		if(!$this->hasErrors())
		{
			if(isset($type) && in_array($type,$this->service::getTypes()) && in_array($type,Service::getTypes()))
			{
				$this->type = $type;
				return $this;
			}
			$this->errorCollection->setError(new Main\Error("service not support type"));
		}
		return $this;
	}
	private function run() : self
	{
		if(!$this->hasErrors())
		{
			$result = Internals\ServiceQueueTable::add([
				'CLIENT_ID' => $this->clientId,
				'SERVICE_TYPE' => $this->engineCode,
				'TYPE' => $this->type
			]);
			$result->isSuccess()?:$this->errorCollection->add($result->getErrors());
		}
		return $this;
	}
	private function clearCache()
	{
		if(!$this->hasErrors())
		{
			Facebook\Config::clearCache();
			Facebook\Installs::clearCache();
		}
		return $this;
	}
	private function sendEvent() : self
	{
		if(!$this->hasErrors())
		{
			Main\EventManager::getInstance()->send(
				$event = new Event('seo','onExtensionInstall',[])
			);
			foreach ($event->getResults() as $result)
			{
				if($result->getType() === EventResult::ERROR)
				{
					$this->errors()->add($errors = $result->getParameters()['ERROR_COLLECTION'] ?? []);
				}
			}
		}
		return $this;
	}
	private function hasErrors() : bool
	{
		return $this->errorCollection->count() > 0;
	}
	private function getEventStatus() : int
	{
		return $this->hasErrors() ? EventResult::ERROR : EventResult::SUCCESS;
	}
	private function errors() : ErrorCollection
	{
		return $this->errorCollection;
	}
	private function result() : EventResult
	{
		return new EventResult($this->getEventStatus(), ['ERROR_COLLECTION' => $this->errors()]);
	}

	public static function handleEvent($clientId,$engineCode)
	{
		return
			static::getInstance()
				->setEngineCode($engineCode)
				->setClientId($clientId)
				->run()
				->clearCache()
				->sendEvent();
	}
}