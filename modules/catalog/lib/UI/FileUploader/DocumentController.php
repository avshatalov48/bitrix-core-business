<?php

namespace Bitrix\Catalog\UI\FileUploader;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\UploaderController;

class DocumentController extends UploaderController
{
	public function __construct(array $options)
	{
		parent::__construct($options);
	}

	public function isAvailable(): bool
	{
		return CurrentUser::get()->canDoOperation('catalog_read');
	}

	public function getConfiguration(): Configuration
	{
		return new Configuration();
	}

	public function canUpload(): bool
	{
		return CurrentUser::get()->canDoOperation('catalog_store');
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
	}

	public function canView(): bool
	{
		return false;
	}

	public function canRemove(): bool
	{
		return false;
	}
}
