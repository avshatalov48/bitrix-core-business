<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\V2\Analytics\Event\AudioMessageEvent;
use Bitrix\Im\V2\Analytics\Event\FileMessageEvent;
use Bitrix\Im\V2\Integration\AI\Transcription\Result\TranscribeResult;
use Bitrix\Im\V2\Message;

class FileAnalytics extends AbstractAnalytics
{
	protected const ATTACH_FILE = 'attach_file';
	protected const START_TRANSCRIPT = 'start_transcript';
	protected const FINISH_TRANSCRIPT = 'finish_transcript';

	public function addStartTranscript(TranscribeResult $result): void
	{
		$statusCode = $this->getTranscriptStatusCode($result);

		$this->async(function () use ($statusCode) {
			$this
				->createAudioMessageEvent(self::START_TRANSCRIPT)
				?->setStatus($statusCode)
				?->send()
			;
		});
	}

	public function addFinishTranscript(TranscribeResult $result): void
	{
		$statusCode = $this->getTranscriptStatusCode($result);

		$this->async(function () use ($statusCode) {
			$this
				->createAudioMessageEvent(self::FINISH_TRANSCRIPT)
				?->setStatus($statusCode)
				?->send()
			;
		});
	}

	public function addAttachFilesEvent(Message $message): void
	{
		$files = $message->getFiles();
		$fileCount = $files->count();
		if ($fileCount < 1)
		{
			return;
		}

		$this
			->createFileMessageEvent(self::ATTACH_FILE)
			?->setFilesType($files)
			?->setFileP3($fileCount)
			?->send()
		;
	}

	protected function getTranscriptStatusCode(TranscribeResult $result): string
	{
		$statusCode = 'success';
		if (!$result->isSuccess())
		{
			$statusCode = 'error_' . mb_strtolower($result->getError()?->getCode());
		}

		return $statusCode;
	}

	protected function createAudioMessageEvent(
		string $eventName,
	): ?AudioMessageEvent
	{
		if (!$this->isChatTypeAllowed($this->chat))
		{
			return null;
		}

		return (new AudioMessageEvent($eventName, $this->chat, $this->userId));
	}

	protected function createFileMessageEvent(
		string $eventName,
	): ?FileMessageEvent
	{
		if (!$this->isChatTypeAllowed($this->chat))
		{
			return null;
		}

		return (new FileMessageEvent($eventName, $this->chat, $this->userId));
	}
}
