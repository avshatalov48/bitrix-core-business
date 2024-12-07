<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Disk\Driver;
use Bitrix\Disk\File;
use Bitrix\Disk\Security\DiskSecurityContext;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\File\Image;

class CheckFileAccess extends Base
{
	private array $path;

	public function __construct(array $path)
	{
		parent::__construct();
		$this->path = $path;
	}

	public function onBeforeAction(Event $event)
	{
		$fileId = $this->extractFileId();

		if (!is_numeric($fileId))
		{
			return null;
		}

		$fileResult = \CFile::GetByID($fileId);
		$file = (isset($fileResult) && $fileResult) ? $fileResult->Fetch() : null;
		$info = (new Image($_SERVER["DOCUMENT_ROOT"] . $file['SRC']))->getInfo();
		if (!$info)
		{
			$this->addError(new Error(
				'Wrong file type',
				ChatError::WRONG_PARAMETER
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		$currentUser = $this->getAction()->getCurrentUser();
		$userId = isset($currentUser) ? $currentUser->getId() : null;
		$securityContext = new DiskSecurityContext((int)$userId);
		$parameters = [
			'filter' => ['FILE_ID' => $fileId],
			'with' => ['CREATE_USER']
		];
		$parameters = Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, ['ID', 'CREATED_BY']);

		$fileCollection = File::getModelList($parameters);
		if (!$fileCollection)
		{
			$this->addError(new Error(
				'File is not accessible',
				ChatError::WRONG_PARAMETER
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function extractFileId()
	{
		$arguments = $this->getAction()->getArguments();

		$value = $arguments;

		foreach ($this->path as $key)
		{
			if (!is_array($value))
			{
				return null;
			}

			$value = $value[$key] ?? null;
		}

		return $value;
	}
}