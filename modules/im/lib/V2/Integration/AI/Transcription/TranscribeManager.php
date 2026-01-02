<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\AI\Transcription;

use Bitrix\AI\Config;
use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Payload\Audio;
use Bitrix\AI\Tuning\Manager;
use Bitrix\Im\Model\FileTranscriptionTable;
use Bitrix\Im\V2\Analytics\FileAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Integration\AI\Transcription\Item\Status;
use Bitrix\Im\V2\Integration\AI\Transcription\Item\TranscribeFileItem;
use Bitrix\Im\V2\Integration\AI\Transcription\Result\TranscribeResult;
use Bitrix\Im\V2\Integration\AI\Restriction;
use Bitrix\Im\V2\Pull\Event\FileTranscriptionEvent;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;

final class TranscribeManager
{
	use ContextCustomer;

	public const CONTEXT_ID = 'chat_file_transcribe';
	public const MAX_TRANSCRIPTION_CHARS = 20000;
	public const MAX_TRANSCRIBABLE_FILE_SIZE = 26214400;
	private const MODULE_ID = 'im';

	private int $fileId;
	private int $diskFileId;
	private int $chatId;

	public function __construct(int $fileId, int $diskFileId, int $chatId)
	{
		$this->fileId = $fileId;
		$this->diskFileId = $diskFileId;
		$this->chatId = $chatId;
	}

	public function transcribeFile(): TranscribeResult
	{
		$result = new TranscribeResult($this->createErrorFileItem());

		if (!Loader::includeModule('ai'))
		{
			return $result;
		}

		$fileTranscription = $this->getFileTranscription();
		if (isset($fileTranscription))
		{
			$this->sendTranscribeEvent($fileTranscription, $this->getContext()->getUserId());
			$result->setFileItem($fileTranscription);

			return $result;
		}

		if ($this->hasPending())
		{
			$fileTranscription = $this->createPendingFileItem();
			$result->setFileItem($fileTranscription);

			return $result;
		}

		TranscribeQueueManager::getInstance()->add($this->fileId, $this->chatId);
		$result = $this->sendAiQuery();

		if ($result->isSuccess())
		{
			$result->setFileItem($this->createPendingFileItem());
		}
		else
		{
			TranscribeQueueManager::getInstance()->delete($this->fileId, $this->chatId);
		}

		(new FileAnalytics(Chat::getInstance($this->chatId)))->addStartTranscript($result);

		return $result;
	}

	public function handleTranscriptionResponse(TranscribeResult $transcribeResult): void
	{
		$transcribeFileItem = $transcribeResult->getFileItem();

		if ($transcribeFileItem->status === Status::Success)
		{
			try
			{
				$this->saveFileTranscription($transcribeFileItem);
			}
			catch (\Exception $exception)
			{
				$this->sendTranscribeEvent($this->createErrorFileItem());
				throw $exception;
			}
		}

		$queueManager = TranscribeQueueManager::getInstance();
		$chatIds = $queueManager->fetchChatIds($transcribeFileItem->fileId);
		$queueManager->delete($transcribeFileItem->fileId);

		$this->sendTranscribeEvent($transcribeFileItem, null, $chatIds);
		(new FileAnalytics(Chat::getInstance($this->chatId)))->addFinishTranscript($transcribeResult);
	}

	protected function getFileTranscription(): ?TranscribeFileItem
	{
		$query = FileTranscriptionTable::query()
			->setSelect(['TEXT'])
			->where('FILE_ID', $this->fileId)
			->fetch()
		;

		if ($query && !empty($query['TEXT']))
		{
			return $this->createFileItem(Status::Success, $query['TEXT']);
		}

		return null;
	}

	protected function saveFileTranscription(TranscribeFileItem $transcribeFileItem): void
	{
		$fields = [[
			'FILE_ID' => $transcribeFileItem->fileId,
			'TEXT' => $transcribeFileItem->transcriptText
		]];

		FileTranscriptionTable::multiplyInsertWithoutDuplicate(
			$fields,
			['DEADLOCK_SAFE' => true, 'UNIQUE_FIELDS' => ['FILE_ID']]
		);
	}

	protected function hasPending(): bool
	{
		return TranscribeQueueManager::getInstance()->inQueue($this->fileId, $this->chatId);
	}

	protected function sendAiQuery(): TranscribeResult
	{
		$result = new TranscribeResult($this->createErrorFileItem());

		$contextParams = [
			'chatId' => $this->chatId,
			'fileId' => $this->fileId,
			'diskFileId' => $this->diskFileId,
		];

		$fileId = $this->fileId;
		$file = \CFile::GetFileArray($fileId);

		if (empty($file['SRC']))
		{
			return $result;
		}

		$fileUri = new Uri($file['SRC']);
		$fileType = $file['CONTENT_TYPE'] ?? '';

		if (empty($fileUri->getHost()))
		{
			$host = Config::getValue('public_url') ?: UrlManager::getInstance()->getHostUrl();
			$fileUri = (new Uri($host))->setPath($file['SRC']);
		}

		$context = new Context(self::MODULE_ID, self::CONTEXT_ID);
		$context->setParameters($contextParams);

		$engine = $this->getEngine($context);
		if ($engine)
		{
			$engine
				->setPayload((new Audio($fileUri->getUri()))->setMarkers(['type' => $fileType]))
				->onError(function (Error $processingError) use(&$result) {
					$result->addError($processingError);
				})
				->completionsInQueue()
			;
		}

		return $result;
	}

	protected function getEngine(Context $context): ?Engine
	{
		$engineName = (new Manager())->getItem(Restriction::SETTING_TRANSCRIPTION_PROVIDER)?->getValue();
		if (empty($engineName))
		{
			return null;
		}

		return Engine::getByCode((string)$engineName, $context);
	}

	protected function sendTranscribeEvent(
		TranscribeFileItem $transcribeFileItem,
		?int $userId = null,
		?array $chatIds = null
	): void
	{
		if (empty($chatIds))
		{
			$chatIds = [$transcribeFileItem->chatId];
		}

		foreach ($chatIds as $chatId)
		{
			$event = new FileTranscriptionEvent(Chat::getInstance($chatId), $transcribeFileItem, $userId);
			$event->send();
		}
	}

	public function createErrorFileItem(): TranscribeFileItem
	{
		return TranscribeFileItem::createByError(
			$this->fileId,
			$this->diskFileId,
			$this->chatId
		);
	}

	public function createPendingFileItem(): TranscribeFileItem
	{
		return TranscribeFileItem::createByPending(
			$this->fileId,
			$this->diskFileId,
			$this->chatId
		);
	}

	public function createFileItem(Status $status, string $transcriptText): TranscribeFileItem
	{
		return TranscribeFileItem::create(
			$this->fileId,
			$this->diskFileId,
			$this->chatId,
			$status,
			$transcriptText
		);
	}
}
