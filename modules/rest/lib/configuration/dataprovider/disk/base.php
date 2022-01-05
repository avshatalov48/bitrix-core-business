<?php

namespace Bitrix\Rest\Configuration\DataProvider\Disk;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Application;
use Bitrix\Disk\SystemUser;
use Bitrix\Disk\Internals\FolderTable;
use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Disk\Driver;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\DataProvider\ProviderBase;

/**
 * Class Base
 *
 * @package Bitrix\Rest\Configuration\DataProvider\Disk
 */
class Base extends ProviderBase
{
	private $storage;
	private $folderFilter = [];

	/**
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function __construct(array $setting)
	{
		parent::__construct($setting);
		if (!Loader::includeModule('disk'))
		{
			throw new SystemException(
				'Can\'t include module disk',
				'ERROR_INCLUDE_DISK'
			);
		}

		if (!$this->loadStorage())
		{
			throw new SystemException(
				'Can\'t load storage',
				'ERROR_LOAD_STORAGE'
			);
		}
	}

	/**
	 * Adds content as file to configuration folder.
	 *
	 * @param $code string name of file
	 * @param $content string|array saving configuration data
	 * @param $type mixed type of configuration data
	 *
	 * @return bool
	 */
	public function addContent(string $code, $content, $type = false): bool
	{
		$result = false;

		$content = $this->packageContent($content);
		if (is_null($content))
		{
			return $result;
		}

		$name = $code . Helper::CONFIGURATION_FILE_EXTENSION;

		$folder = $this->getFolder(true);
		if (!$folder)
		{
			if ($type !== false)
			{
				$subFolder = $folder->getChild(
					[
						'=NAME' => $type,
						'=TYPE' => FolderTable::TYPE_FOLDER,
					]
				);
				if (!$subFolder)
				{
					$folder = $folder->addSubFolder(
						[
							'NAME' => $type,
							'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
						]
					);
				}
				else
				{
					$folder = $subFolder;
				}
			}

			if ($folder)
			{
				$file = $folder->uploadFile(
					[
						'name' => $name,
						'content' => $content,
						'type' => 'application/json',
					],
					[
						'NAME' => $name,
						'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
					]
				);
				if ($file)
				{
					$result = true;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns files content.
	 *
	 * @param string $path
	 * @param int $step
	 *
	 * @return array|null
	 */
	public function get(string $path, int $step): ?array
	{
		$result = null;
		$folder = $this->getFolder();
		if ($folder)
		{
			$folder = $folder->getChild(
				[
					'=NAME' => $path,
				]
			);
		}

		if ($folder)
		{
			$fakeSecurityContext = Driver::getInstance()->getFakeSecurityContext();
			$fileList = $folder->getChildren(
				$fakeSecurityContext,
				[
					'filter' => [
						'=TYPE' => ObjectTable::TYPE_FILE,
					]
				]
			);
			$i = 0;
			foreach ($fileList as $child)
			{
				if ($i == $step && $child instanceof \Bitrix\Disk\File)
				{
					$server = Application::getInstance()->getContext()->getServer();
					$documentRoot = $server->getDocumentRoot();
					$filePath = $documentRoot . \CFile::GetPath(
							$child->getFileId()
					);
					$file = new File($filePath);
					try
					{
						$result = [
							'DATA' => $file->getContents(),
							'FILE_NAME' => $child->getName(),
						];
					}
					catch (FileNotFoundException $exception)
					{
						$result = null;
					}

					break;
				}
				$i++;
			}
			$result['COUNT'] = count($fileList);
		}

		return $result;
	}

	/**
	 * Adds files to configurations files folder.
	 * @param array $files array files list
	 *
	 * @return array
	 */
	public function addFiles(array $files): array
	{
		$result = [
			'success' => false,
			'result' => [],
		];

		$structure = new Structure($this->getUserContext());

		$storage = $this->getStorage();
		if ($storage)
		{
			$folder = $this->getFolder();
			if (!$folder)
			{
				$folder = $storage->addFolder(
					[
						'NAME' => $this->getContext(),
						'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
					]
				);
			}

			$subFolder = $folder->getChild(
				[
					'=NAME' => Helper::STRUCTURE_FILES_NAME,
					'=TYPE' => FolderTable::TYPE_FOLDER,
				]
			);
			if (!$subFolder)
			{
				$subFolder = $folder->addSubFolder(
					[
						'NAME' => Helper::STRUCTURE_FILES_NAME,
						'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
					]
				);
			}

			if ($subFolder)
			{
				foreach ($files as $file)
				{
					if ($file['ID'])
					{
						$id = (int) $file['ID'];
						$structure->saveFile($id, $file);

						$fileData = \CFile::MakeFileArray($id);
						$res = $subFolder->uploadFile(
							$fileData,
							[
								'NAME' => $id,
								'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
							]
						);
						if (!is_null($res))
						{
							$result['success'] = true;
							$result['result'][$id] = true;
						}
						else
						{
							$result['result'][$id] = false;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Deletes folder by context
	 *
	 * @return bool
	 */
	public function deleteFolder()
	{
		$folder = $this->getFolder();
		if ($folder)
		{
			global $USER;
			if (!$folder->deleteTree($USER->GetID()))
			{
				//$this->setErrors($folder->getErrors());//todo: add errors
			}
		}

		return true;
	}

	/**
	 * Sets filter for current folder.
	 *
	 * @param array $filter
	 */
	public function setFolderFilter(array $filter)
	{
		$this->folderFilter = $filter;
	}

	/**
	 * Returns current folder.
	 * @param bool $autoCreate
	 *
	 * @return mixed
	 */
	public function getFolder(bool $autoCreate = false)
	{
		$filter = $this->folderFilter;
		$folderName = $this->getContext();
		if (empty($filter))
		{
			$filter = [
				'=NAME' => $folderName,
				'=TYPE' => FolderTable::TYPE_FOLDER
			];
		}

		$folder = $this->getStorage()->getChild($filter);
		if (!$folder && $autoCreate)
		{
			$folder = $this->getStorage()->addFolder(
				[
					'NAME' => $folderName,
					'CREATED_BY' => SystemUser::SYSTEM_USER_ID,
				]
			);
		}

		return $folder;
	}

	private function loadStorage(): bool
	{
		$this->storage = Driver::getInstance()->addStorageIfNotExist(
			Helper::getInstance()->getStorageBackupParam()
		);

		return !is_null($this->storage);
	}

	/**
	 * Returns using storage
	 *
	 * @return mixed
	 */
	public function getStorage()
	{
		return $this->storage;
	}
}
