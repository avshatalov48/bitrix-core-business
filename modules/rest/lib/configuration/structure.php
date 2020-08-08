<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Main\IO\File;
use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\ObjectTable;
use CTempFile;
use CFile;

class Structure
{
	private static $maxAgentTime = 10;
	private static $fileDescriptionDelete = 'configuration_delete';
	private $context = '';
	private $setting = null;
	private $zipMimeType = [
		'application/zip',
		'application/x-zip-compressed'
	];
	/**
	 * @param $context string
	 */
	public function __construct($context)
	{
		$this->context = $context;
		$this->setting = new Setting($context);
	}

	/**
	 * Create and/or return absolute path to temp folder
	 *
	 * @return string|boolean
	 */
	public function getFolder()
	{
		$folder = $this->setting->get('FOLDER');
		if(empty($folder))
		{
			$folder = CTempFile::GetDirectoryName(
				4,
				[
					'rest',
					uniqid($this->context, true)
				]
			);
			if(CheckDirPath($folder))
			{
				$this->setting->set('FOLDER', $folder);
			}
			else
			{
				$folder = false;
			}
		}

		return $folder ?: false;
	}

	/**
	 * Save content to temp folder
	 * @param $type string
	 * @param $code string
	 * @param $content array|string
	 *
	 * @return boolean
	 */
	public function saveContent($type, $code, $content)
	{
		$return = false;

		try
		{
			if(is_array($content))
			{
				$content = Json::encode($content);
			}
			elseif(!is_string($content))
			{
				return $return;
			}

			$path = ($type === false ? '' : $type . '/') . $code . Helper::CONFIGURATION_FILE_EXTENSION;

			$id = CFile::SaveFile(
				[
					'name' => $path,
					'MODULE_ID' => 'rest',
					'content' => $content,
					'description' => self::$fileDescriptionDelete
				],
				'configuration/export'
			);

			if($id > 0)
			{
				$return = $this->saveConfigurationFile($id, $path);
			}
		}
		catch (\Exception $e)
		{
		}

		return $return;
	}

	/**
	 * @param $id integer file id from b_file
	 * @param $name string file name with path in folder
	 *
	 * @return boolean
	 */
	private function saveConfigurationFile($id, $name)
	{
		$id = intVal($id);
		$files = $this->getConfigurationFileList();
		if(!is_array($files))
		{
			$files = [];
		}
		$key = array_search($name, $files);
		if($key !== false)
		{
			unset($files[$key]);
		}

		$files[$id] = $name;

		return $this->setting->set('CONFIGURATION_FILES_LIST', $files);
	}

	/**
	 * All files in current context
	 * @return array|null
	 */
	public function getConfigurationFileList()
	{
		return $this->setting->get('CONFIGURATION_FILES_LIST');
	}

	/**
	 * @param $id integer file id from b_file
	 * @param $additionalData array any short data save with file
	 *
	 * @return boolean
	 */
	public function saveFile($id, $additionalData = [])
	{
		$id = intVal($id);
		$files = $this->getFileList();
		if(!is_array($files))
		{
			$files = [];
		}
		$files[$id] = $additionalData;

		return $this->setting->set('FILES_LIST', $files);
	}

	/**
	 * All files in current context
	 * @return array|null
	 */
	public function getFileList()
	{
		return $this->setting->get('FILES_LIST');
	}

	/**
	 * Unzip the archive to a temporary folder
	 * @param $fileInfo array === $_FILES
	 *
	 * @return boolean
	 */
	public function unpack($fileInfo)
	{
		$result = false;
		$fileContent = File::getFileContents($fileInfo["tmp_name"]);
		if($fileContent)
		{
			$type = (in_array($fileInfo["type"], $this->zipMimeType)) ? 'ZIP' : 'TAR.GZ';
			$folder = $this->getFolder();
			$filePath = $folder.'archive_tmp';

			File::putFileContents($filePath, $fileContent);
			$archive = \CBXArchive::GetArchive($filePath, $type);
			$res = $archive->Unpack($folder);
			if($res)
			{
				$this->initUnpackFilesList();
				$result = true;
			}
			File::deleteFile($filePath);
		}

		return $result;
	}

	private function initUnpackFilesList()
	{
		$folder = $this->getFolder();
		if(File::isFileExists($folder.Helper::STRUCTURE_FILES_NAME.Helper::CONFIGURATION_FILE_EXTENSION))
		{
			$content = File::getFileContents($folder.Helper::STRUCTURE_FILES_NAME.Helper::CONFIGURATION_FILE_EXTENSION);
			try
			{
				$files = Json::decode($content);
				if(is_array($files))
				{
					$saveFiles = [];
					foreach ($files as $file)
					{
						$id = intVal($file['ID']);
						if($id > 0 && File::isFileExists($folder.Helper::STRUCTURE_FILES_NAME.'/'.$id))
						{
							$file['PATH'] = $folder.Helper::STRUCTURE_FILES_NAME.'/'.$id;
							$saveFiles[$id] = $file;
						}
					}
					$this->setting->set('UNPACK_FILES', $saveFiles);
				}
			}
			catch (\Exception $e)
			{
			}
		}
	}

	/**
	 * Set Disk work folder with external files
	 * @param $folderId
	 * @param $storageParams
	 *
	 * @return bool
	 */
	public function setUnpackFilesFromDisk($folderId, $storageParams)
	{
		$result = false;
		if(Loader::includeModule('disk'))
		{
			try
			{
				$storage = Driver::getInstance()->addStorageIfNotExist(
					$storageParams
				);
				if($storage)
				{
					$folder = $storage->getChild(
						[
							'=ID' => $folderId
						]
					);
					if ($folder)
					{
						$file = $folder->getChild(
							[
								'=NAME' => Helper::STRUCTURE_FILES_NAME . Helper::CONFIGURATION_FILE_EXTENSION
							]
						);
						if ($file && $file->getFileId() > 0)
						{
							$server = Application::getInstance()->getContext()->getServer();
							$documentRoot = $server->getDocumentRoot();
							$filePath = $documentRoot . CFile::GetPath($file->getFileId());
							if (File::isFileExists($filePath))
							{
								$content = File::getFileContents($filePath);
								$fileList = Json::decode($content);
								if ($fileList)
								{
									$subFolder = $folder->getChild(
										[
											'NAME' => Helper::STRUCTURE_FILES_NAME
										]
									);
									if ($subFolder)
									{
										$fileList = array_column($fileList, null, 'ID');
										$fakeSecurityContext = Driver::getInstance()->getFakeSecurityContext();
										$folderFiles = $subFolder->getChildren(
											$fakeSecurityContext,
											[
												'filter' => [
													'=TYPE' => ObjectTable::TYPE_FILE
												]
											]
										);

										$saveFiles = [];
										foreach ($folderFiles as $file)
										{
											$id = $file->getOriginalName();
											if(!empty($fileList[$id]))
											{
												$path = $documentRoot . CFile::GetPath(
													$file->getFileId()
												);
												if(File::isFileExists($path))
												{
													$saveFiles[$id] = $fileList[$id];
													$saveFiles[$id]['PATH'] = $path;
												}
											}
										}
										$this->setting->set('UNPACK_FILES', $saveFiles);

										$result = true;
									}
								}
							}
						}
					}
				}
			}
			catch (\Exception $e)
			{
			}
		}
		return $result;
	}

	/**
	 * Get file from Disk work folder with external files
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public function getUnpackFile($id)
	{
		$files = $this->setting->get('UNPACK_FILES');
		return !empty($files[$id]) ? $files[$id] : false;
	}

	/**
	 * Set export archive name
	 * @param $name string [a-zA-Z0-9_] new name archive
	 *
	 * @return bool result of set new name
	 */
	public function setArchiveName($name)
	{
		$result = false;
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
		if(!empty($name))
		{
			$result = $this->setting->set(Setting::SETTING_EXPORT_ARCHIVE_NAME, $name);
		}

		return $result;
	}

	/**
	 * Return export name archive
	 * @return string
	 */
	public function getArchiveName()
	{
		$result = $this->setting->get(Setting::SETTING_EXPORT_ARCHIVE_NAME);

		return is_string($result) ? $result : '';
	}

	/**
	 * Agent delete old temp export files
	 * @return string
	 */
	public static function clearContentAgent()
	{
		$deleteDate = new DateTime();
		$deleteDate->add('-2 days');

		$res = CFile::getList(
			[],
			[
				'MODULE_ID' => 'rest'
			]
		);

		$startTime = microtime(true);
		while ($file = $res->fetch())
		{
			if ($file['DESCRIPTION'] == static::$fileDescriptionDelete)
			{
				$creatDate = DateTime::createFromTimestamp(strtotime($file['TIMESTAMP_X']));

				if ($creatDate < $deleteDate)
				{
					CFile::Delete($file['ID']);
				}
			}

			if (microtime(true) - $startTime > static::$maxAgentTime)
			{
				break;
			}
		}

		return '\Bitrix\Rest\Configuration\Structure::clearContentAgent();';
	}
}