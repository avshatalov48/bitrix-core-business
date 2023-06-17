<?php
namespace Bitrix\Landing\Connector;

class Disk
{
	public const FILE_PREFIX_HREF = '#diskFile';
	public const FILE_NEW_PREFIX_HREF = 'file:#diskFile';
	public const FILE_MASK_HREF = '(file:)?#diskFile([\d]+)';

	/**
	 * Check disk's files within content for read access. If not, remove file's marks.
	 *
	 * @param string $content Content.
	 * @param string|null $oldContent Old content (if exists, found old files will not be replaced).
	 * @param bool &$replaced True if was replaced.
	 * @return string
	 */
	public static function sanitizeContent(string $content, ?string $oldContent = null, bool &$replaced = false): string
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return $content;
		}

		$existsFiles = [];

		if ($oldContent)
		{
			if (preg_match_all('/' . self::FILE_MASK_HREF . '/i', $oldContent, $matches))
			{
				foreach ($matches[2] as $objectId)
				{
					$existsFiles[] = $objectId;
				}
			}
		}

		if (preg_match_all('/' . self::FILE_MASK_HREF . '/i', $content, $matches))
		{
			foreach ($matches[2] as $i => $objectId)
			{
				if ($oldContent && in_array($objectId, $existsFiles))
				{
					continue;
				}

				$file = \Bitrix\Disk\BaseObject::loadById($objectId);
				if ($file)
				{
					$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
					if ($file->canRead($securityContext))
					{
						continue;
					}
				}

				$replaced = true;
				$content = str_replace($matches[0][$i], '""', $content);
			}
		}

		return $content;
	}

	/**
	 * Returns file's info by Disk's file id.
	 *
	 * @param int $objectId Disk's object if (file id).
	 * @param bool $checkRights True for checking permissions within Disk.
	 * @param bool $copyInLocalStorage If file in external folder then copies file in local user storage.
	 * @return array|null
	 */
	public static function getFileInfo(int $objectId, bool $checkRights = true, bool $copyInLocalStorage = false): ?array
	{
		if ($objectId && \Bitrix\Main\Loader::includeModule('disk'))
		{
			$file = \Bitrix\Disk\BaseObject::loadById($objectId);
			if ($file)
			{
				if ($checkRights)
				{
					$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
					if (!$file->canRead($securityContext))
					{
						return null;
					}
				}

				if ($copyInLocalStorage)
				{
					$currentUserId = \Bitrix\Landing\Manager::getUserId();
					$userStorage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($currentUserId);
					if (!$userStorage)
					{
						return null;
					}

					$folder = $userStorage->getFolderForSavedFiles();
					if (!$folder)
					{
						return null;
					}

					if (
						$file->getStorageId() !== $userStorage->getId() ||
						(int)$file->getCreateUser()->getId() !== $currentUserId
					)
					{
						$newFile = $file->getRealObject()->copyTo($folder, $currentUserId, true);
						if ($file->getRealObject()->getName() != $file->getName())
						{
							$newFile->renameInternal($file->getName(), true);
						}
						if (!$newFile)
						{
							return null;
						}

						return [
							'ID' => $newFile->getRealObject()->getFileId(),
							'OBJECT_ID' => $newFile->getId(),
							'NAME' => $newFile->getName()
						];
					}
				}

				return [
					'ID' => $file->getRealObject()->getFileId(),
					'OBJECT_ID' => $file->getId(),
					'NAME' => $file->getName()
				];
			}
		}

		return null;
	}
}