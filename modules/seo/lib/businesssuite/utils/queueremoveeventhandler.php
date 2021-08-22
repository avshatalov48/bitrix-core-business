<?php

namespace Bitrix\Seo\BusinessSuite\Utils;

use Bitrix\Main;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Seo\BusinessSuite\Internals;

final class QueueRemoveEventHandler
{

	/** @var ErrorCollection $errorCollection Error collection.  */
	private $errorCollection;

	private $code;

	private $clientId;

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection();
	}

	private static function createHandler() : self
	{
		return new self;
	}

	private function result() : EventResult
	{
		return new EventResult($this->getEventStatus(), ['ERROR_COLLECTION' => $this->errors()]);
	}

	private function getEventStatus() : int
	{
		return $this->hasErrors() ? EventResult::ERROR : EventResult::SUCCESS;
	}

	private function errors() : ErrorCollection
	{
		return $this->errorCollection;
	}

	private function hasErrors() : bool
	{
		return $this->errorCollection->count() > 0;
	}

	private function setClientId($clientId) : self
	{
		if(!$this->hasErrors())
		{
			if (!isset($clientId))
			{
				$this->errorCollection->setError(new Main\Error("client is null"));
			}
			$this->clientId = $clientId;

		}
		return $this;
	}

	private function setEngineCode($code) : self
	{
		if(!$this->hasErrors())
		{
			if(!is_string($code))
			{
				$this->errorCollection->setError(new Main\Error('Engine code is not string'));
			}
			$this->code = $code;
		}
		return $this;
	}
	private function run() : self
	{
		if(!$this->hasErrors())
		{
			$row = Internals\ServiceQueueTable::getRow([
				'select' => ['ID'],
				'filter' => [
					'CLIENT_ID' => $this->clientId,
					'=SERVICE_TYPE' => $this->code,
				]
			]);
			if ($row)
			{
				$result = Internals\ServiceQueueTable::delete($row['ID']);
				if(!$result->isSuccess())
				{
					$this->errors()->add($result->getErrors());
				}
			}
		}
		return $this;
	}

	/**
	 * handle event
	 * @param $clientId
	 * @param $engineCode
	 *
	 * @return QueueRemoveEventHandler
	 */
	public static function handleEvent($clientId,$engineCode)
	{
		return
			static::createHandler()
				->setClientId($clientId)
				->setEngineCode($engineCode)
				->run();
	}
}