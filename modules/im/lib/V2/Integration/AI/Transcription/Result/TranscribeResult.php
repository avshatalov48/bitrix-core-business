<?php

namespace Bitrix\Im\V2\Integration\AI\Transcription\Result;

use Bitrix\Im\V2\Integration\AI\Transcription\Item\TranscribeFileItem;
use Bitrix\Im\V2\Result;

class TranscribeResult extends Result
{
	protected TranscribeFileItem $fileItem;

	public function __construct(TranscribeFileItem $fileItem)
	{
		parent::__construct();
		$this->fileItem = $fileItem;
	}

	public function setFileItem(TranscribeFileItem $fileItem): self
	{
		$this->fileItem = $fileItem;

		return $this;
	}

	public function getFileItem(): TranscribeFileItem
	{
		return $this->fileItem;
	}
}
