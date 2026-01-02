<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot;

use Bitrix\AI;
use Bitrix\AI\Tuning;
use Bitrix\AI\Quality;
use Bitrix\AI\Engine;
use Bitrix\AI\Engine\IEngine;
use Bitrix\Landing\Copilot\Generation\Error;
use Bitrix\Landing\Copilot\Generation\Request;
use Bitrix\Landing\Copilot\Generation\Type\Errors;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EventResult;
use Bitrix\Main\Event;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main;
use Exception;

/**
 * Handle external events
 */
class EventHandler
{
	public static function onQueueJobExecute(Event $event): EventResult
	{
		/**
		 * @var AI\Result $result
		 */
		$result = $event->getParameter('result');
		if (!$result instanceof AI\Result)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		$engine = $event->getParameter('engine');
		if (!$engine instanceof IEngine)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		$hash = $event->getParameter('queue');
		$request = Request::getByHash($hash);
		if (!$request)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		$error = null;

		$jsonData = $result->getPrettifiedData();
		if (!$jsonData)
		{
			$error = Error::createError(Errors::requestEmpty);
		}
		else
		{
			try
			{
				$data = Json::decode(Converter\Json::expandJsonString($jsonData));
				if (!$data || isset($data['error']))
				{
					$error = Error::createError(Errors::requestError);
					$error->message .= $data['error'] ? ': ' . $data['error'] : '';
				}
			}
			catch (ArgumentException)
			{
				$error = Error::createError(Errors::requestInvalid);
			}
		}

		if (!isset($data) && !isset($error))
		{
			return new EventResult(EventResult::SUCCESS);
		}

		if (isset($error))
		{
			$request->saveError($error);
		}
		else
		{
			$request->saveResult($data);
		}

		$generation = new Generation();
		$generationId = $request->getGenerationId();
		if ($generation->initById($generationId))
		{
			$generation->execute();
		}

		return new EventResult(EventResult::SUCCESS);
	}

	public static function onQueueJobFail(Event $event): EventResult
	{
		$hash = $event->getParameter('queue');
		$request = Request::getByHash($hash);
		if (!$request)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		$error = Error::createError(Errors::requestFail);
		/**
		 * @var $errorParam Main\Error
		 */
		$errorParam = $event->getParameter('error');
		if ($errorParam)
		{
			$error->message .= ': ' . $errorParam->getCode() . ': ' . $errorParam->getMessage();
		}

		if ($request->getResult() === null)
		{
			$request->saveError($error);
		}

		$generation = new Generation();
		$generationId = $request->getGenerationId();
		if ($generation->initById($generationId))
		{
			$generation->execute();
		}

		return new EventResult(EventResult::SUCCESS);
	}
}