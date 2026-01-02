<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Result;
use Bitrix\Im\V2\Integration\AI\Transcription\Item\Status;
use Bitrix\Im\V2\Integration\AI\Transcription\Result\TranscribeResult;
use Bitrix\Im\V2\Integration\AI\Transcription\TranscribeManager;
use Bitrix\Main\Error;
use Bitrix\Main\Event;

class QueueManager
{
	public static function onQueueJobExecute(Event $event): void
	{
		/** @var \Bitrix\AI\Engine\IEngine $engine */
		$engine = $event->getParameter('engine');
		$context = $engine->getContext();

		$moduleId = $context->getModuleId();
		$contextId = $context->getContextId();
		$parameters = $context->getParameters();

		if (
			empty($moduleId)
			|| $moduleId != 'im'
			|| empty($contextId)
			|| $contextId !== TranscribeManager::CONTEXT_ID
			|| empty($parameters)
			|| empty($parameters['fileId'])
			|| empty($parameters['chatId'])
			|| empty($parameters['diskFileId'])
		)
		{
			return;
		}

		$result = $event->getParameter('result');
		if (!($result instanceof Result))
		{
			return;
		}

		$text = $result->getPrettifiedData();
		$fileId = (int)$parameters['fileId'];
		$diskFileId = (int)$parameters['diskFileId'];
		$chatId = (int)$parameters['chatId'];

		$transcribeManager = new TranscribeManager($fileId, $diskFileId, $chatId);

		if (!empty($text) && mb_strlen($text) <= TranscribeManager::MAX_TRANSCRIPTION_CHARS)
		{
			$transcribeFileItem = $transcribeManager->createFileItem(Status::Success, trim($text));
			$result = (new TranscribeResult($transcribeFileItem));
		}
		else
		{
			$transcribeFileItem = $transcribeManager->createErrorFileItem();
			$error = new Error('', 'MAX_TRANSCRIPTION_CHARS');
			$result = (new TranscribeResult($transcribeFileItem))->addError($error);
		}

		$transcribeManager->handleTranscriptionResponse($result);
	}

	public static function onQueueJobFail(Event $event): void
	{
		/** @var \Bitrix\AI\Engine\IEngine $engine */
		$engine = $event->getParameter('engine');
		$context = $engine->getContext();

		$moduleId = $context->getModuleId();
		$contextId = $context->getContextId();
		$parameters = $context->getParameters();

		if (
			empty($moduleId)
			|| $moduleId != 'im'
			|| empty($contextId)
			|| $contextId !== TranscribeManager::CONTEXT_ID
			|| empty($parameters)
			|| empty($parameters['fileId'])
			|| empty($parameters['chatId'])
			|| empty($parameters['diskFileId'])
		)
		{
			return;
		}

		$error = $event->getParameter('error') ?? null;
		if (!($error instanceof Error))
		{
			return;
		}

		$fileId = (int)$parameters['fileId'];
		$diskFileId = (int)$parameters['diskFileId'];
		$chatId = (int)$parameters['chatId'];

		$transcribeManager = new TranscribeManager($fileId, $diskFileId, $chatId);
		$transcribeFileItem = $transcribeManager->createErrorFileItem();

		$transcribeResult = (new TranscribeResult($transcribeFileItem))->addError($error);
		$transcribeManager->handleTranscriptionResponse($transcribeResult);
	}
}
