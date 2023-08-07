<?php

namespace Bitrix\UI\FileUploader;

class CanUploadResult extends \Bitrix\Main\Result implements \JsonSerializable
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function reject(): self
	{
		$canUploadResult = new self();
		$canUploadResult->addError(new UploaderError(UploaderError::FILE_UPLOAD_ACCESS_DENIED));

		return $canUploadResult;
	}

	public function jsonSerialize(): array
	{
		return [
			'errors' => $this->getErrors(),
			'success' => $this->isSuccess(),
		];
	}
}