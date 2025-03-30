<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckDiskFileAccess extends Base
{
	public function onBeforeAction(Event $event)
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof FileCollection)
			{
				return $this->checkFileCollectionAccess($argument);
			}
		}

		return null;
	}

	private function checkFileCollectionAccess(FileCollection $files): ?EventResult
	{
		foreach ($files as $file)
		{
			$diskFile = $file->getDiskFile();
			$storage = $diskFile?->getStorage();

			if (
				!isset($diskFile, $storage)
				|| !$diskFile->canRead($storage->getCurrentUserSecurityContext())
			)
			{
				$this->addError(new FileError(FileError::ACCESS_ERROR));
				return new EventResult(EventResult::ERROR, null, null, $this);
			}
		}

		return null;
	}
}