<?php

namespace Bitrix\Im\V2\Controller\Disk;

use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckDiskFileAccess;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Im\V2\Entity\User\UserError;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

class File extends BaseController
{
	public function configureActions(): array
	{
		return [
			'save' => [
				'+prefilters' => [
					new CheckDiskFileAccess(),
				]
			]
		];
	}

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			FileCollection::class,
			'files',
			function ($className, array $ids) {
				return $this->getFilesByIds($ids);
			}
		);
	}

	public function saveAction(FileCollection $files, CurrentUser $currentUser): ?array
	{
		$userId = $currentUser->getId();
		if (!isset($userId))
		{
			$this->addError(new UserError(UserError::NOT_FOUND));
			return null;
		}

		if (!Loader::includeModule('disk'))
		{
			$this->addError(new FileError(FileError::DISK_NOT_INSTALLED));
			return null;
		}

		$result = $files->copyToOwnSavedFiles();
		if (!$result->hasResult())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return ['result' => true];
	}

	protected function getFilesByIds(array $ids): FileCollection
	{
		$result = [];

		foreach ($ids as $id)
		{
			if (is_numeric($id) && (int)$id > 0)
			{
				$id = (int)$id;
				$result[$id] = $id;
			}
		}

		return FileCollection::initByDiskFilesIds(array_values($result));
	}
}