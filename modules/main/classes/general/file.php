<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\IO;
use Bitrix\Main\UI\Viewer;
use Bitrix\Main\File;
use Bitrix\Main\Web;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\File\Image;
use Bitrix\Main\File\Image\Rectangle;
use Bitrix\Main\File\Internal;
use Bitrix\Main\ORM\Query;
use Bitrix\Main\Security;

IncludeModuleLangFile(__FILE__);

class CFile
{
	protected const CACHE_DIR = 'b_file';
	protected const DELETE_NONE = 0x00;
	protected const DELETE_FILE = 0x01;
	protected const DELETE_DB = 0x02;
	protected const DELETE_ALL = 0x03;

	public static function SaveForDB(&$arFields, $field, $strSavePath)
	{
		$arFile = $arFields[$field] ?? null;
		if (isset($arFile) && is_array($arFile))
		{
			if (
				(isset($arFile["name"]) && $arFile["name"] <> '')
				|| (isset($arFile["del"]) && $arFile["del"] <> '')
				|| array_key_exists("description", $arFile)
			)
			{
				$res = static::SaveFile($arFile, $strSavePath);
				if ($res !== false)
				{
					$arFields[$field] = (intval($res) > 0 ? $res : false);
					return true;
				}
			}
		}
		unset($arFields[$field]);
		return false;
	}

	public static function checkForDb($arFields, $field)
	{
		if (isset($arFields[$field]) && is_array($arFields[$field]))
		{
			$arFile = $arFields[$field];

			if ($arFile["name"] == "")
			{
				return "";
			}

			$fileName = self::transformName($arFile["name"]);
			return self::validateFile($fileName, $arFile);
		}
		else
		{
			return "";
		}
	}

	protected static function transformName($name, $forceRandom = false, $bSkipExt = false)
	{
		//safe filename without path
		$fileName = GetFileName($name);

		$originalName = ($forceRandom != true && COption::GetOptionString("main", "save_original_file_name", "N") == "Y");
		if ($originalName)
		{
			//transforming original name:

			//transliteration
			if (COption::GetOptionString("main", "translit_original_file_name", "N") == "Y")
			{
				$fileName = CUtil::translit($fileName, LANGUAGE_ID, [
					"max_len" => 1024,
					"safe_chars" => ".",
					"replace_space" => '-',
					"change_case" => false,
				]);
			}

			//replace invalid characters
			if (COption::GetOptionString("main", "convert_original_file_name", "Y") == "Y")
			{
				$io = CBXVirtualIo::GetInstance();
				$fileName = $io->RandomizeInvalidFilename($fileName);
			}
		}

		//.jpe is not image type on many systems
		if (!$bSkipExt && strtolower(GetFileExtension($fileName)) == "jpe")
		{
			$fileName = substr($fileName, 0, -4) . ".jpg";
		}

		//double extension vulnerability
		$fileName = RemoveScriptExtension($fileName);

		if (!$originalName)
		{
			//name is randomly generated
			$fileName = Security\Random::getString(32) . ($bSkipExt || ($ext = GetFileExtension($fileName)) == '' ? '' : "." . $ext);
		}

		return $fileName;
	}

	protected static function validateFile($strFileName, $arFile)
	{
		if ($strFileName == '')
		{
			return GetMessage("FILE_BAD_FILENAME");
		}

		$io = CBXVirtualIo::GetInstance();
		if (!$io->ValidateFilenameString($strFileName))
		{
			return GetMessage("MAIN_BAD_FILENAME1");
		}

		if (mb_strlen($strFileName) > 255)
		{
			return GetMessage("MAIN_BAD_FILENAME_LEN");
		}

		//check .htaccess etc.
		if (IsFileUnsafe($strFileName))
		{
			return GetMessage("FILE_BAD_TYPE");
		}

		//nginx returns octet-stream for .jpg
		if (GetFileNameWithoutExtension($strFileName) == '')
		{
			return GetMessage("FILE_BAD_FILENAME");
		}

		if (COption::GetOptionInt("main", "disk_space") > 0)
		{
			$quota = new CDiskQuota();
			if (!$quota->checkDiskQuota($arFile))
			{
				return GetMessage("FILE_BAD_QUOTA");
			}
		}

		return "";
	}

	public static function SaveFile($arFile, $strSavePath, $forceRandom = false, $skipExtension = false, $dirAdd = '')
	{
		$strFileName = GetFileName($arFile["name"] ?? '');    /* filename.gif */

		if (isset($arFile["del"]) && $arFile["del"] <> '')
		{
			static::Delete($arFile["old_file"] ?? 0);
			if ($strFileName == '')
			{
				return "NULL";
			}
		}

		if (!isset($arFile["name"]) || $arFile["name"] == '')
		{
			if (isset($arFile["description"]) && isset($arFile["old_file"]) && intval($arFile["old_file"]) > 0)
			{
				static::UpdateDesc($arFile["old_file"], $arFile["description"]);
			}
			return false;
		}

		if (isset($arFile["content"]))
		{
			if (!isset($arFile["size"]))
			{
				$arFile["size"] = strlen($arFile["content"]);
			}
		}
		else
		{
			try
			{
				$file = new IO\File(IO\Path::convertPhysicalToLogical($arFile["tmp_name"]));
				$arFile["size"] = $file->getSize();
			}
			catch (IO\IoException)
			{
				if (!isset($arFile["size"]) || !is_int($arFile["size"]))
				{
					$arFile["size"] = 0;
				}
			}
		}

		$arFile["ORIGINAL_NAME"] = $strFileName;

		//translit, replace unsafe chars, etc.
		$strFileName = self::transformName($strFileName, $forceRandom, $skipExtension);

		//transformed name must be valid, check disk quota, etc.
		if (self::validateFile($strFileName, $arFile) !== "")
		{
			return false;
		}

		$arFile["type"] = Web\MimeType::normalize($arFile["type"]);

		$original = null;

		$connection = \Bitrix\Main\Application::getConnection();
		$connection->lock('b_file', -1);

		$io = CBXVirtualIo::GetInstance();

		$bExternalStorage = false;
		foreach (GetModuleEvents("main", "OnFileSave", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [&$arFile, $strFileName, $strSavePath, $forceRandom, $skipExtension, $dirAdd]))
			{
				$bExternalStorage = true;
				break;
			}
		}

		if (!$bExternalStorage)
		{
			// we should keep number of files in a folder below 10,000
			// three chars from md5 give us 4096 subdirs

			$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");

			if (!$forceRandom && COption::GetOptionString("main", "save_original_file_name", "N") == "Y")
			{
				//original name
				$subdir = $dirAdd;
				if ($subdir == '')
				{
					while (true)
					{
						$random = Security\Random::getString(32);
						$subdir = substr(md5($random), 0, 3) . "/" . $random;

						if (!$io->FileExists($_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/" . $strSavePath . "/" . $subdir . "/" . $strFileName))
						{
							break;
						}
					}
				}
				$strSavePath = rtrim($strSavePath, "/") . "/" . $subdir;
			}
			else
			{
				//random name
				$fileExtension = ($skipExtension || ($ext = GetFileExtension($strFileName)) == '' ? '' : "." . $ext);
				while (true)
				{
					$subdir = substr(md5($strFileName), 0, 3);
					$strSavePath = rtrim($strSavePath, "/") . "/" . $subdir;

					if (!$io->FileExists($_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/" . $strSavePath . "/" . $strFileName))
					{
						break;
					}

					//try the new name
					$strFileName = Security\Random::getString(32) . $fileExtension;
				}
			}

			$arFile["SUBDIR"] = $strSavePath;
			$arFile["FILE_NAME"] = $strFileName;

			$dirName = $_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/" . $strSavePath . "/";
			$physicalFileName = $io->GetPhysicalName($dirName . $strFileName);

			CheckDirPath($dirName);

			if (is_set($arFile, "content"))
			{
				if (file_put_contents($physicalFileName, $arFile["content"]) === false)
				{
					return false;
				}
			}
			else
			{
				if (!copy($arFile["tmp_name"], $physicalFileName) && !move_uploaded_file($arFile["tmp_name"], $physicalFileName))
				{
					return false;
				}
			}

			if (isset($arFile["old_file"]))
			{
				static::Delete($arFile["old_file"]);
			}

			@chmod($physicalFileName, BX_FILE_PERMISSIONS);

			//flash is not an image
			$flashEnabled = !static::IsImage($arFile["ORIGINAL_NAME"], $arFile["type"]);

			$image = new File\Image($physicalFileName);

			$imgInfo = $image->getInfo($flashEnabled);

			if ($imgInfo)
			{
				$arFile["WIDTH"] = $imgInfo->getWidth();
				$arFile["HEIGHT"] = $imgInfo->getHeight();
			}
			else
			{
				$arFile["WIDTH"] = 0;
				$arFile["HEIGHT"] = 0;
			}

			//calculate a hash for the control of duplicates
			$arFile["FILE_HASH"] = static::CalculateHash($physicalFileName, $arFile["size"]);

			//control of duplicates
			if ($arFile["FILE_HASH"] <> '')
			{
				$original = static::FindDuplicate($arFile["size"], $arFile["FILE_HASH"]);

				if ($original !== null)
				{
					//points to the original's physical path
					$arFile["SUBDIR"] = $original->getFile()->getSubdir();
					$arFile["FILE_NAME"] = $original->getFile()->getFileName();

					$originalPath = $_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/" . $arFile["SUBDIR"] . "/" . $arFile["FILE_NAME"];

					if ($physicalFileName <> $io->GetPhysicalName($originalPath))
					{
						unlink($physicalFileName);
						try
						{
							rmdir($io->GetPhysicalName($dirName));
						}
						catch (\ErrorException)
						{
							// Ignore a E_WARNING Error
						}
					}
				}
			}
		}
		else
		{
			//from clouds
			if (isset($arFile["original_file"]) && $arFile["original_file"] instanceof Internal\EO_FileHash)
			{
				$original = $arFile["original_file"];
			}
		}

		if ($arFile["WIDTH"] == 0 || $arFile["HEIGHT"] == 0)
		{
			//mock image because we got false from CFile::GetImageSize()
			if (str_starts_with($arFile["type"], "image/") && $arFile["type"] <> 'image/svg+xml')
			{
				$arFile["type"] = "application/octet-stream";
			}
		}

		/****************************** QUOTA ******************************/
		if (COption::GetOptionInt("main", "disk_space") > 0 && $original === null)
		{
			CDiskQuota::updateDiskQuota("file", $arFile["size"], "insert");
		}
		/****************************** QUOTA ******************************/

		$NEW_IMAGE_ID = static::DoInsert([
			"HEIGHT" => $arFile["HEIGHT"],
			"WIDTH" => $arFile["WIDTH"],
			"FILE_SIZE" => $arFile["size"],
			"CONTENT_TYPE" => $arFile["type"],
			"SUBDIR" => $arFile["SUBDIR"],
			"FILE_NAME" => $arFile["FILE_NAME"],
			"MODULE_ID" => $arFile["MODULE_ID"] ?? '',
			"ORIGINAL_NAME" => $arFile["ORIGINAL_NAME"],
			"DESCRIPTION" => ($arFile["description"] ?? ''),
			"HANDLER_ID" => ($arFile["HANDLER_ID"] ?? ''),
			"EXTERNAL_ID" => ($arFile["external_id"] ?? md5(mt_rand())),
			"FILE_HASH" => ($original === null ? $arFile["FILE_HASH"] : ''),
		]);

		if ($original !== null)
		{
			//save information about the duplicate for future use (on deletion)
			static::AddDuplicate($original->getFileId(), $NEW_IMAGE_ID, false);
		}

		$connection->unlock('b_file');

		static::CleanCache($NEW_IMAGE_ID);

		return $NEW_IMAGE_ID;
	}

	/**
	 * Calculates a hash of the file.
	 * @param string $file Full path to the file.
	 * @param int $size Size of the file.
	 * @return string
	 */
	protected static function CalculateHash($file, $size)
	{
		$hash = '';
		if ($size > 0 && COption::GetOptionString('main', 'control_file_duplicates', 'N') === 'Y')
		{
			$maxSize = (int)COption::GetOptionString('main', 'duplicates_max_size', '100') * 1024 * 1024; //Mbytes
			if ($size <= $maxSize || $maxSize === 0)
			{
				$hash = hash_file("md5", $file);
			}
		}
		return $hash;
	}

	/**
	 * @param int $size
	 * @param string $hash
	 * @param int|null $handlerId
	 * @return Internal\EO_FileHash|null
	 */
	public static function FindDuplicate($size, $hash, $handlerId = null)
	{
		$filter = Query\Query::filter()
			->where("FILE_SIZE", $size)
			->where("FILE_HASH", $hash)
		;

		if ($handlerId !== null)
		{
			$filter->where("FILE.HANDLER_ID", $handlerId);
		}
		else
		{
			$filter->where(Query\Query::filter()
				->logic('or')
				->where('FILE.HANDLER_ID', '')
				->whereNull('FILE.HANDLER_ID')
			);
		}

		return Internal\FileHashTable::query()
			->addSelect("FILE.*")
			->where($filter)
			->addOrder("FILE_ID")
			->setLimit(1)
			->fetchObject()
		;
	}

	/**
	 * Adds information about a duplicate file.
	 * For internal use only.
	 *
	 * @param int $originalId Original file ID.
	 * @param int|null $duplicateId Duplicate file ID (optional if the original and duplicate files are the same).
	 * @param bool $resolvePossibleOriginCycle Check if the desired original file is already in the table and
	 * if it's a duplicate of another file, then use the real original file ID from the table.
	 *
	 * @internal
	 */
	public static function AddDuplicate($originalId, $duplicateId = null, bool $resolvePossibleOriginCycle = true)
	{
		if ($duplicateId === null)
		{
			$duplicateId = $originalId;
		}

		if ($resolvePossibleOriginCycle || $originalId == $duplicateId)
		{
			//possibly there is the original already for the file
			$original = Internal\FileDuplicateTable::query()
				->addSelect("ORIGINAL_ID")
				->where("DUPLICATE_ID", $originalId)
				->fetch()
			;

			if ($original)
			{
				$originalId = $original["ORIGINAL_ID"];
			}
		}

		$updateFields = [
			"COUNTER" => new Main\DB\SqlExpression(Internal\FileDuplicateTable::getTableName() . '.?# + 1', 'COUNTER'),
		];

		$insertFields = [
			"DUPLICATE_ID" => $duplicateId,
			"ORIGINAL_ID" => $originalId,
		];

		Internal\FileDuplicateTable::merge($insertFields, $updateFields);
	}

	/**
	 * Adds information about a duplicate file.
	 * @param int $originalId Original file ID.
	 * @param array $duplicteIds List of file IDs to delete and update their path to the original file path.
	 */
	public static function DeleteDuplicates($originalId, array $duplicteIds)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$original = Internal\FileHashTable::getList([
			'select' => ['FILE_SIZE', 'FILE_HASH', 'FILE.*'],
			'filter' => ['=FILE_ID' => $originalId],
		])->fetchObject();
		if (!$original)
		{
			return;
		}

		$originalPath = '/' . $original->getFile()->getSubdir() . '/' . $original->getFile()->getFileName();

		$io = CBXVirtualIo::GetInstance();
		$uploadDir = COption::GetOptionString("main", "upload_dir", "upload");
		$deleteSize = 0;

		$fileList = \Bitrix\Main\FileTable::getList([
			'select' => ['ID', 'FILE_SIZE', 'SUBDIR', 'FILE_NAME'],
			'filter' => [
				'=ID' => $duplicteIds,
				'=HANDLER_ID' => $original->getFile()->getHandlerId(),
			],
			'order' => [
				'ID' => 'ASC',
			],
		]);
		while ($duplicate = $fileList->fetchObject())
		{
			$connection->lock('b_file', -1);

			Internal\FileHashTable::delete($duplicate->getId());

			$duplicatePath = '/' . $duplicate->getSubdir() . '/' . $duplicate->getFileName();
			if ($originalPath == $duplicatePath)
			{
				$connection->unlock('b_file');
				continue;
			}

			$cancel = false;
			foreach (GetModuleEvents('main', 'OnBeforeFileDeleteDuplicate', true) as $event)
			{
				$cancel = ExecuteModuleEventEx($event, [$original->getFile(), $duplicate]);
				if ($cancel)
				{
					break;
				}
			}
			if ($cancel)
			{
				$connection->unlock('b_file');
				continue;
			}

			static::AddDuplicate($originalId, $duplicate->getId(), false);

			$update = $helper->prepareUpdate('b_file', [
				'SUBDIR' => $original->getFile()->getSubdir(),
				'FILE_NAME' => $original->getFile()->getFileName(),
			]);
			$ddl = 'UPDATE b_file SET ' . $update[0] . 'WHERE ID = ' . $duplicate->getId();
			$connection->queryExecute($ddl);

			static::cleanCache($duplicate->getId());

			$isExternal = false;
			foreach (GetModuleEvents('main', 'OnAfterFileDeleteDuplicate', true) as $event)
			{
				$isExternal = ExecuteModuleEventEx($event, [$original->getFile(), $duplicate]) || $isExternal;
			}

			if (!$isExternal)
			{
				$dname = $_SERVER["DOCUMENT_ROOT"] . '/' . $uploadDir . '/' . $duplicate->getSubdir();
				$fname = $dname . '/' . $duplicate->getFileName();

				$file = $io->GetFile($fname);
				if ($file->isExists() && $file->unlink())
				{
					$deleteSize += $duplicate->getFileSize();
				}

				$directory = $io->GetDirectory($dname);
				if ($directory->isExists() && $directory->isEmpty())
				{
					if ($directory->rmdir())
					{
						$parent = $io->GetDirectory(GetDirPath($dname));
						if ($parent->isExists() && $parent->isEmpty())
						{
							$parent->rmdir();
						}
					}
				}
			}

			$connection->unlock('b_file');
		}

		/****************************** QUOTA ******************************/
		if ($deleteSize > 0 && COption::GetOptionInt("main", "disk_space") > 0)
		{
			CDiskQuota::updateDiskQuota("file", $deleteSize, "delete");
		}
		/****************************** QUOTA ******************************/
	}

	public static function CloneFile(int $fileId): ?int
	{
		$originalFile = static::GetByID($fileId)->Fetch();
		if (!$originalFile)
		{
			return null;
		}

		$originalFile['FILE_HASH'] = '';

		$cloneId = static::DoInsert($originalFile);

		static::AddDuplicate($fileId, $cloneId);
		static::CleanCache($cloneId);

		return $cloneId;
	}

	public static function DoInsert($arFields)
	{
		global $DB;

		$size = round(floatval($arFields["FILE_SIZE"]));

		$strSql =
			"INSERT INTO b_file(
				TIMESTAMP_X
				,MODULE_ID
				,HEIGHT
				,WIDTH
				,FILE_SIZE
				,CONTENT_TYPE
				,SUBDIR
				,FILE_NAME
				,ORIGINAL_NAME
				,DESCRIPTION
				,HANDLER_ID
				,EXTERNAL_ID
			) VALUES (
				" . $DB->GetNowFunction() . "
				,'" . $DB->ForSQL($arFields["MODULE_ID"], 50) . "'
				," . intval($arFields["HEIGHT"]) . "
				," . intval($arFields["WIDTH"]) . "
				," . $size . "
				,'" . $DB->ForSql($arFields["CONTENT_TYPE"], 255) . "'
				,'" . $DB->ForSql($arFields["SUBDIR"], 255) . "'
				,'" . $DB->ForSQL($arFields["FILE_NAME"], 255) . "'
				,'" . $DB->ForSql($arFields["ORIGINAL_NAME"], 255) . "'
				,'" . $DB->ForSQL($arFields["DESCRIPTION"], 255) . "'
				," . ($arFields["HANDLER_ID"] ? "'" . $DB->ForSql($arFields["HANDLER_ID"], 50) . "'" : "null") . "
				," . ($arFields["EXTERNAL_ID"] != "" ? "'" . $DB->ForSql($arFields["EXTERNAL_ID"], 50) . "'" : "null") . "
			)";
		$DB->Query($strSql);
		$fileId = $DB->LastID();

		//store the file hash for duplicates search
		if ($arFields["FILE_HASH"] <> '')
		{
			Internal\FileHashTable::add([
				"FILE_ID" => $fileId,
				"FILE_SIZE" => $size,
				"FILE_HASH" => $arFields["FILE_HASH"],
			]);
		}

		$arFields["ID"] = $fileId;
		foreach (GetModuleEvents("main", "OnAfterFileSave", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$arFields]);
		}

		return $fileId;
	}

	public static function Delete($ID)
	{
		$ID = intval($ID);

		if ($ID <= 0)
		{
			return;
		}

		$connection = Main\Application::getConnection();
		$connection->lock('b_file', -1);

		$res = static::GetByID($ID, true);

		if ($res = $res->Fetch())
		{
			$delete = static::processDuplicates($ID);

			if ($delete === self::DELETE_NONE)
			{
				//can't delete the file - duplicates found
				$connection->unlock('b_file');
				return;
			}

			$delete_size = 0;

			if ($delete & self::DELETE_FILE)
			{
				$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
				$dname = $_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/" . $res["SUBDIR"];
				$fname = $dname . "/" . $res["FILE_NAME"];

				$io = CBXVirtualIo::GetInstance();

				$file = $io->GetFile($fname);
				if ($file->isExists() && $file->unlink())
				{
					$delete_size += $res["FILE_SIZE"];
				}

				$delete_size += static::ResizeImageDelete($res);

				$directory = $io->GetDirectory($dname);
				if ($directory->isExists() && $directory->isEmpty())
				{
					if ($directory->rmdir())
					{
						$parent = $io->GetDirectory(GetDirPath($dname));
						if ($parent->isExists() && $parent->isEmpty())
						{
							$parent->rmdir();
						}
					}
				}

				foreach (GetModuleEvents("main", "OnPhysicalFileDelete", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, [$res]);
				}
			}

			if ($delete & self::DELETE_DB)
			{
				foreach (GetModuleEvents("main", "OnFileDelete", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, [$res]);
				}

				Internal\FileHashTable::delete($ID);

				// recursion inside
				static::processVersions($ID);

				$connection->query("DELETE FROM b_file WHERE ID = {$ID}");

				static::CleanCache($ID);
			}

			/****************************** QUOTA ******************************/
			if ($delete_size > 0 && COption::GetOptionInt("main", "disk_space") > 0)
			{
				CDiskQuota::updateDiskQuota("file", $delete_size, "delete");
			}
			/****************************** QUOTA ******************************/
		}

		$connection->unlock('b_file');
	}

	protected static function processDuplicates($ID)
	{
		$result = self::DELETE_ALL;

		//Part 1: the file is a duplicate of another file, including referenses to itself
		$original = Internal\FileDuplicateTable::query()
			->addSelect("*")
			->where("DUPLICATE_ID", $ID)
			->fetch()
		;

		//Part 2: find duplicates of the file
		$duplicates = Internal\FileDuplicateTable::query()
			->where("ORIGINAL_ID", $ID)
			->setLimit(1)
			->fetch()
		;

		//Part 1
		if ($original)
		{
			if ($original["COUNTER"] > 1)
			{
				//decrease references counter
				Internal\FileDuplicateTable::update(
					[
						"DUPLICATE_ID" => $ID,
						"ORIGINAL_ID" => $original["ORIGINAL_ID"],
					],
					[
						"COUNTER" => new Main\DB\SqlExpression("?# - 1", "COUNTER"),
					]
				);

				//there are references still
				$result = self::DELETE_NONE;
			}
			else
			{
				//delete referense
				Internal\FileDuplicateTable::delete([
					"DUPLICATE_ID" => $ID,
					"ORIGINAL_ID" => $original["ORIGINAL_ID"],
				]);

				//delete only if the file is a duplicate of *another* file
				if ($original["DUPLICATE_ID"] <> $original["ORIGINAL_ID"])
				{
					if ($original["ORIGINAL_DELETED"] === "Y")
					{
						//try and delete the original
						static::Delete($original["ORIGINAL_ID"]);
					}

					//there is the original somewhere, we shouldn't delete its file
					$result = self::DELETE_DB;
				}
			}
		}

		//Part 2
		if ($duplicates)
		{
			//mark the original as deleted for future deletion
			Internal\FileDuplicateTable::markDeleted($ID);

			//duplicates found, should keep the original
			$result = self::DELETE_NONE;
		}

		return $result;
	}

	/**
	 * Adds information about a version of a file.
	 * @param int $originalId Original file ID.
	 * @param int $versionId Version file ID.
	 * @param array $metaData The version peculiarities.
	 */
	public static function AddVersion($originalId, $versionId, $metaData = [])
	{
		$result = Internal\FileVersionTable::add([
				'ORIGINAL_ID' => $originalId,
				'VERSION_ID' => $versionId,
			] + (empty($metaData) ? [] : [
				'META' => $metaData,
			]));

		static::CleanCache($originalId);

		return $result;
	}

	protected static function processVersions($ID)
	{
		// check if the file is something's version
		$original = Internal\FileVersionTable::query()
			->addSelect('*')
			->where('VERSION_ID', $ID)
			->fetch()
		;
		if ($original)
		{
			Internal\FileVersionTable::delete(['ORIGINAL_ID' => $original['ORIGINAL_ID']]);
			static::CleanCache($original['ORIGINAL_ID']);
		}

		// check if the file has versions
		$versions = Internal\FileVersionTable::query()
			->addSelect('*')
			->where('ORIGINAL_ID', $ID)
			->exec()
		;
		while ($version = $versions->fetch())
		{
			static::Delete($version['VERSION_ID']);
		}
	}

	public static function CleanCache($fileId)
	{
		if (CACHED_b_file !== false)
		{
			$bucket_size = (int)CACHED_b_file_bucket_size;
			if ($bucket_size <= 0)
			{
				$bucket_size = 10;
			}

			$bucket = (int)($fileId / $bucket_size);

			$cache = Main\Application::getInstance()->getManagedCache();

			$cache->clean(self::CACHE_DIR . '01' . $bucket, self::CACHE_DIR);
			$cache->clean(self::CACHE_DIR . '11' . $bucket, self::CACHE_DIR);
			$cache->clean(self::CACHE_DIR . '00' . $bucket, self::CACHE_DIR);
			$cache->clean(self::CACHE_DIR . '10' . $bucket, self::CACHE_DIR);
		}
	}

	public static function GetFromCache($fileId, $realId = false)
	{
		global $DB;

		$cache = Main\Application::getInstance()->getManagedCache();

		$bucketSize = (int)CACHED_b_file_bucket_size;
		if ($bucketSize <= 0)
		{
			$bucketSize = 10;
		}

		$bucket = (int)($fileId / $bucketSize);
		$https = (int)Main\Context::getCurrent()->getRequest()->isHttps();
		$cacheId = self::CACHE_DIR . $https . (int)$realId . $bucket;

		if ($cache->read(CACHED_b_file, $cacheId, self::CACHE_DIR))
		{
			$files = $cache->get($cacheId);

			if (!isset($files[$fileId]))
			{
				// the trail of an incomplete bucket
				if (!is_array($files))
				{
					$files = [];
				}

				if ($file = static::GetFromDb($fileId, $realId)->Fetch())
				{
					$files[$fileId] = $file;
					static::CleanCache($fileId);
				}
			}
		}
		else
		{
			$files = [];

			$minId = $bucket * $bucketSize;
			$maxId = ($bucket + 1) * $bucketSize - 1;

			$sql = "
				SELECT f.*, 
					{$DB->DateToCharFunction("f.TIMESTAMP_X")} as TIMESTAMP_X, 
					NULL as VERSION_ORIGINAL_ID, '' as META
				FROM b_file f
				WHERE f.ID >= {$minId} 
					AND f.ID <= {$maxId} 
			";

			if ($realId !== true)
			{
				$sql .= "
					UNION
					SELECT f.*, 
						{$DB->DateToCharFunction("f.TIMESTAMP_X")} as TIMESTAMP_X, 
						fv.ORIGINAL_ID as VERSION_ORIGINAL_ID, fv.META as META
					FROM b_file f
						INNER JOIN b_file_version fv ON fv.VERSION_ID = f.ID 
					WHERE fv.ORIGINAL_ID >= {$minId} 
						AND fv.ORIGINAL_ID <= {$maxId}
					ORDER BY ID
				";
			}

			$rs = $DB->Query($sql);

			while ($file = $rs->fetch())
			{
				$originalId = ($file['VERSION_ORIGINAL_ID'] ?: $file["ID"]);
				$files[$originalId] = $file;
			}

			// store SRC in cache
			foreach ($files as $id => $file)
			{
				$files[$id]['SRC'] = static::GetFileSRC($file);
			}

			$cache->setImmediate($cacheId, $files);
		}
		return $files;
	}

	public static function GetByID($fileId, $realId = false)
	{
		$fileId = (int)$fileId;

		if (CACHED_b_file === false)
		{
			$result = static::GetFromDb($fileId, $realId);
		}
		else
		{
			$files = static::GetFromCache($fileId, $realId);

			$result = new CDBResult;
			$result->InitFromArray(isset($files[$fileId]) ? [$files[$fileId]] : []);
		}
		return $result;
	}

	protected static function GetFromDb($fileId, $realId)
	{
		global $DB;

		$strSql = "
			SELECT f.*, 
				{$DB->DateToCharFunction("f.TIMESTAMP_X")} as TIMESTAMP_X,
				NULL as VERSION_ORIGINAL_ID, '' as META
			FROM b_file f
			WHERE f.ID = {$fileId}
		";

		if ($realId !== true)
		{
			$strSql .= "
				UNION
				SELECT f.*,
					{$DB->DateToCharFunction("f.TIMESTAMP_X")} as TIMESTAMP_X,
					fv.ORIGINAL_ID as VERSION_ORIGINAL_ID, fv.META as META
				FROM b_file f
					INNER JOIN b_file_version fv ON fv.VERSION_ID = f.ID 
				WHERE fv.ORIGINAL_ID = {$fileId} 
				ORDER BY ID DESC
				LIMIT 1
			";
		}

		return $DB->Query($strSql);
	}

	public static function GetList($arOrder = [], $arFilter = [])
	{
		global $DB;
		$arSqlSearch = [];
		$arSqlOrder = [];
		$strSqlSearch = "";

		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				$key = strtoupper($key);

				$strOperation = '';
				if (str_starts_with($key, "@"))
				{
					$key = substr($key, 1);
					$strOperation = "IN";
					$arIn = is_array($val) ? $val : explode(',', $val);
					$val = '';
					foreach ($arIn as $v)
					{
						$val .= ($val <> '' ? ',' : '') . "'" . $DB->ForSql(trim($v)) . "'";
					}
				}
				else
				{
					$val = $DB->ForSql($val);
				}

				if ($val == '')
				{
					continue;
				}

				switch ($key)
				{
					case "MODULE_ID":
					case "ID":
					case "EXTERNAL_ID":
					case "SUBDIR":
					case "FILE_NAME":
					case "ORIGINAL_NAME":
					case "CONTENT_TYPE":
					case "HANDLER_ID":
						if ($strOperation == "IN")
						{
							$arSqlSearch[] = "f." . $key . " IN (" . $val . ")";
						}
						else
						{
							$arSqlSearch[] = "f." . $key . " = '" . $val . "'";
						}
						break;
				}
			}
		}
		if (!empty($arSqlSearch))
		{
			$strSqlSearch = " WHERE (" . implode(") AND (", $arSqlSearch) . ")";
		}

		if (is_array($arOrder))
		{
			static $aCols = [
				"ID" => 1,
				"TIMESTAMP_X" => 1,
				"MODULE_ID" => 1,
				"HEIGHT" => 1,
				"WIDTH" => 1,
				"FILE_SIZE" => 1,
				"CONTENT_TYPE" => 1,
				"SUBDIR" => 1,
				"FILE_NAME" => 1,
				"ORIGINAL_NAME" => 1,
				"EXTERNAL_ID" => 1,
			];
			foreach ($arOrder as $by => $ord)
			{
				$by = strtoupper($by);
				if (array_key_exists($by, $aCols))
				{
					$arSqlOrder[] = "f." . $by . " " . (strtoupper($ord) == "DESC" ? "DESC" : "ASC");
				}
			}
		}
		if (empty($arSqlOrder))
		{
			$arSqlOrder[] = "f.ID ASC";
		}
		$strSqlOrder = " ORDER BY " . implode(", ", $arSqlOrder);

		$strSql =
			"SELECT f.*, " . $DB->DateToCharFunction("f.TIMESTAMP_X") . " as TIMESTAMP_X " .
			"FROM b_file f " .
			$strSqlSearch .
			$strSqlOrder;

		$res = $DB->Query($strSql);

		return $res;
	}

	public static function GetFileSRC($file, $uploadDir = false, $external = true)
	{
		$src = '';
		if ($external)
		{
			foreach (GetModuleEvents('main', 'OnGetFileSRC', true) as $event)
			{
				$src = ExecuteModuleEventEx($event, [$file]);
				if ($src)
				{
					break;
				}
			}
		}

		if (!$src)
		{
			if ($uploadDir === false)
			{
				$uploadDir = COption::GetOptionString('main', 'upload_dir', 'upload');
			}

			$src = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];

			$src = str_replace('//', '/', $src);

			if (defined("BX_IMG_SERVER"))
			{
				$src = BX_IMG_SERVER . $src;
			}
		}

		return $src;
	}

	public static function GetFileArray($fileId, $uploadDir = false)
	{
		if (!is_array($fileId) && intval($fileId) > 0)
		{
			$file = static::GetByID($fileId)->Fetch();

			if ($file)
			{
				if (!isset($file['SRC']) || $uploadDir !== false)
				{
					$file['SRC'] = static::GetFileSRC($file, $uploadDir);
				}

				return $file;
			}
		}
		return false;
	}

	public static function ConvertFilesToPost($source, &$target, $field = false)
	{
		if ($field === false)
		{
			foreach ($source as $field => $sub_source)
			{
				self::ConvertFilesToPost($sub_source, $target, $field);
			}
		}
		else
		{
			foreach ($source as $id => $sub_source)
			{
				if (!array_key_exists($id, $target))
				{
					$target[$id] = [];
				}
				if (is_array($sub_source))
				{
					self::ConvertFilesToPost($sub_source, $target[$id], $field);
				}
				else
				{
					$target[$id][$field] = $sub_source;
				}
			}
		}
	}

	/**
	 * @deprecated Consider using \CFile::CloneFile().
	 * @see CFile::CloneFile()
	 */
	public static function CopyFile($FILE_ID, $bRegister = true, $newPath = "")
	{
		$z = static::GetByID($FILE_ID);
		if ($zr = $z->Fetch())
		{
			/****************************** QUOTA ******************************/
			if (COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota = new CDiskQuota();
				if (!$quota->checkDiskQuota($zr))
				{
					return false;
				}
			}
			/****************************** QUOTA ******************************/

			$strNewFile = '';
			$bSaved = false;
			$bExternalStorage = false;
			foreach (GetModuleEvents("main", "OnFileCopy", true) as $arEvent)
			{
				if ($bSaved = ExecuteModuleEventEx($arEvent, [&$zr, $newPath]))
				{
					$bExternalStorage = true;
					break;
				}
			}

			$io = CBXVirtualIo::GetInstance();

			if (!$bExternalStorage)
			{
				$strDirName = $_SERVER["DOCUMENT_ROOT"] . "/" . (COption::GetOptionString("main", "upload_dir", "upload"));
				$strDirName = rtrim(str_replace("//", "/", $strDirName), "/");

				$zr["SUBDIR"] = trim($zr["SUBDIR"], "/");
				$zr["FILE_NAME"] = ltrim($zr["FILE_NAME"], "/");

				$strOldFile = $strDirName . "/" . $zr["SUBDIR"] . "/" . $zr["FILE_NAME"];

				if ($newPath <> '')
				{
					$strNewFile = $strDirName . "/" . ltrim($newPath, "/");
				}
				else
				{
					$strNewFile = $strDirName . "/" . $zr["SUBDIR"] . "/" . md5(uniqid(mt_rand())) . strrchr($zr["FILE_NAME"], ".");
				}

				$zr["FILE_NAME"] = bx_basename($strNewFile);
				$zr["SUBDIR"] = mb_substr($strNewFile, mb_strlen($strDirName) + 1, -(mb_strlen(bx_basename($strNewFile)) + 1));

				if ($newPath <> '')
				{
					CheckDirPath($strNewFile);
				}

				$bSaved = copy($io->GetPhysicalName($strOldFile), $io->GetPhysicalName($strNewFile));
			}

			if ($bSaved)
			{
				if ($bRegister)
				{
					$NEW_FILE_ID = static::DoInsert($zr);

					if (COption::GetOptionInt("main", "disk_space") > 0)
					{
						CDiskQuota::updateDiskQuota("file", $zr["FILE_SIZE"], "copy");
					}

					static::CleanCache($NEW_FILE_ID);

					return $NEW_FILE_ID;
				}
				else
				{
					if (!$bExternalStorage)
					{
						return mb_substr($strNewFile, mb_strlen(rtrim($_SERVER["DOCUMENT_ROOT"], "/")));
					}
					else
					{
						return $bSaved;
					}
				}
			}
			else
			{
				return false;
			}
		}
		return 0;
	}

	public static function UpdateDesc($ID, $desc)
	{
		global $DB;
		$DB->Query(
			"UPDATE b_file SET
				DESCRIPTION = '" . $DB->ForSql($desc, 255) . "',
				TIMESTAMP_X = " . $DB->GetNowFunction() . "
			WHERE ID=" . intval($ID)
		);
		static::CleanCache($ID);
	}

	public static function UpdateExternalId($ID, $external_id)
	{
		global $DB;
		$external_id = trim($external_id);
		$DB->Query(
			"UPDATE b_file SET
				EXTERNAL_ID = " . ($external_id != "" ? "'" . $DB->ForSql($external_id, 50) . "'" : "null") . ",
				TIMESTAMP_X = " . $DB->GetNowFunction() . "
			WHERE ID=" . intval($ID)
		);
		static::CleanCache($ID);
	}

	public static function InputFile($strFieldName, $int_field_size, $strImageID, $strImageStorePath = false, $int_max_file_size = 0, $strFileType = "IMAGE", $field_file = "class=typefile", $description_size = 0, $field_text = "class=typeinput", $field_checkbox = "", $bShowNotes = true, $bShowFilePath = true)
	{
		$strReturn1 = "";
		if ($int_max_file_size != 0)
		{
			$strReturn1 .= "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"" . $int_max_file_size . "\" /> ";
		}

		$strReturn1 .= ' <input name="' . $strFieldName . '" ' . $field_file . '  size="' . $int_field_size . '" type="file" />';
		$strReturn2 = '<span class="bx-input-file-desc">';
		$strDescription = "";
		$db_img_arr = static::GetFileArray($strImageID, $strImageStorePath);

		if ($db_img_arr)
		{
			$strDescription = $db_img_arr["DESCRIPTION"];

			if (($p = mb_strpos($strFieldName, "[")) > 0)
			{
				$strDelName = mb_substr($strFieldName, 0, $p) . "_del" . mb_substr($strFieldName, $p);
			}
			else
			{
				$strDelName = $strFieldName . "_del";
			}

			if ($bShowNotes)
			{
				if ($bShowFilePath)
				{
					$filePath = $db_img_arr["SRC"];
				}
				else
				{
					$filePath = $db_img_arr['ORIGINAL_NAME'];
				}
				$io = CBXVirtualIo::GetInstance();
				if ($io->FileExists($_SERVER["DOCUMENT_ROOT"] . $db_img_arr["SRC"]) || $db_img_arr["HANDLER_ID"])
				{
					$strReturn2 .= "<br>&nbsp;" . GetMessage("FILE_TEXT") . ": " . htmlspecialcharsEx($filePath);
					if (mb_strtoupper($strFileType) == "IMAGE")
					{
						$intWidth = intval($db_img_arr["WIDTH"]);
						$intHeight = intval($db_img_arr["HEIGHT"]);
						if ($intWidth > 0 && $intHeight > 0)
						{
							$strReturn2 .= "<br>&nbsp;" . GetMessage("FILE_WIDTH") . ": $intWidth";
							$strReturn2 .= "<br>&nbsp;" . GetMessage("FILE_HEIGHT") . ": $intHeight";
						}
					}
					$strReturn2 .= "<br>&nbsp;" . GetMessage("FILE_SIZE") . ": " . static::FormatSize($db_img_arr["FILE_SIZE"]);
				}
				else
				{
					$strReturn2 .= "<br>" . GetMessage("FILE_NOT_FOUND") . ": " . htmlspecialcharsEx($filePath);
				}
			}
			$strReturn2 .= "<br><input " . $field_checkbox . " type=\"checkbox\" name=\"" . $strDelName . "\" value=\"Y\" id=\"" . $strDelName . "\" /> <label for=\"" . $strDelName . "\">" . GetMessage("FILE_DELETE") . "</label>";
		}

		$strReturn2 .= '</span>';

		return $strReturn1 . (
			$description_size > 0 ?
				'<br><input type="text" value="' . htmlspecialcharsbx($strDescription) . '" name="' . $strFieldName . '_descr" ' . $field_text . ' size="' . $description_size . '" title="' . GetMessage("MAIN_FIELD_FILE_DESC") . '" />'
				: ''
			) . $strReturn2;
	}

	/**
	 * @param float $size
	 * @param int $precision
	 * @return string
	 */
	public static function FormatSize($size, $precision = 2)
	{
		static $a = ["b", "Kb", "Mb", "Gb", "Tb"];

		$size = (float)$size;
		$pos = 0;
		while ($size >= 1024 && $pos < 4)
		{
			$size /= 1024;
			$pos++;
		}
		return round($size, $precision) . " " . GetMessage("FILE_SIZE_" . $a[$pos]);
	}

	public static function GetImageExtensions()
	{
		return "jpg,bmp,jpeg,jpe,gif,png,webp";
	}

	public static function GetFlashExtensions()
	{
		return "swf";
	}

	public static function IsImage($filename, $mime_type = false)
	{
		$ext = strtolower(GetFileExtension($filename));
		if ($ext <> '')
		{
			if (in_array($ext, explode(",", static::GetImageExtensions())))
			{
				if ($mime_type === false || Web\MimeType::isImage($mime_type))
				{
					return true;
				}
			}
		}
		return false;
	}

	public static function CheckImageFile($arFile, $iMaxSize = 0, $iMaxWidth = 0, $iMaxHeight = 0, $access_typies = [], $bForceMD5 = false, $bSkipExt = false)
	{
		if (!isset($arFile["name"]) || $arFile["name"] == "")
		{
			return "";
		}

		if (empty($arFile["tmp_name"]))
		{
			return GetMessage("FILE_BAD_FILE_TYPE") . ".<br>";
		}

		if (preg_match("#^(php://|phar://)#i", $arFile["tmp_name"]))
		{
			return GetMessage("FILE_BAD_FILE_TYPE") . ".<br>";
		}

		$file_type = GetFileType($arFile["name"]);

		// IMAGE by default
		$flashEnabled = false;
		if (!in_array($file_type, $access_typies))
		{
			$file_type = "IMAGE";
		}

		if ($file_type == "FLASH")
		{
			$flashEnabled = true;
			static $flashMime = ["application/x-shockwave-flash", "application/vnd.adobe.flash.movie"];
			$res = static::CheckFile($arFile, $iMaxSize, $flashMime, static::GetFlashExtensions(), $bForceMD5, $bSkipExt);
		}
		else
		{
			$res = static::CheckFile($arFile, $iMaxSize, "image/", static::GetImageExtensions(), $bForceMD5, $bSkipExt);
		}

		if ($res <> '')
		{
			return $res;
		}

		$imgInfo = (new File\Image($arFile["tmp_name"]))->getInfo($flashEnabled);

		if ($imgInfo)
		{
			$intWIDTH = $imgInfo->getWidth();
			$intHEIGHT = $imgInfo->getHeight();
		}
		else
		{
			return GetMessage("FILE_BAD_FILE_TYPE") . ".<br>";
		}

		//check for dimensions
		if ($iMaxWidth > 0 && ($intWIDTH > $iMaxWidth || $intWIDTH == 0) || $iMaxHeight > 0 && ($intHEIGHT > $iMaxHeight || $intHEIGHT == 0))
		{
			return GetMessage("FILE_BAD_MAX_RESOLUTION") . " (" . $iMaxWidth . " * " . $iMaxHeight . " " . GetMessage("main_include_dots") . ").<br>";
		}

		return null;
	}

	public static function CheckFile($arFile, $intMaxSize = 0, $mimeType = false, $strExt = false, $bForceMD5 = false, $bSkipExt = false)
	{
		if ($arFile["name"] == "")
		{
			return "";
		}

		//translit, replace unsafe chars, etc.
		$strFileName = self::transformName($arFile["name"], $bForceMD5, $bSkipExt);

		//transformed name must be valid, check disk quota, etc.
		if (($error = self::validateFile($strFileName, $arFile)) <> '')
		{
			return $error;
		}

		if ($intMaxSize > 0 && $arFile["size"] > $intMaxSize)
		{
			return GetMessage("FILE_BAD_SIZE") . " (" . static::FormatSize($intMaxSize) . ").";
		}

		$strFileExt = '';
		if ($strExt)
		{
			$strFileExt = GetFileExtension($strFileName);
			if ($strFileExt == '')
			{
				return GetMessage("FILE_BAD_TYPE");
			}
		}

		//Check mime type
		if ($mimeType !== false)
		{
			if (!is_array($mimeType))
			{
				$mimeType = [$mimeType];
			}
			$goodMime = false;
			foreach ($mimeType as $strMimeType)
			{
				if (str_starts_with($arFile["type"], $strMimeType))
				{
					$goodMime = true;
					break;
				}
			}
			if (!$goodMime)
			{
				return GetMessage("FILE_BAD_TYPE");
			}
		}

		//Check extension
		if ($strExt === false)
		{
			return "";
		}

		$IsExtCorrect = true;
		if ($strExt)
		{
			$IsExtCorrect = false;
			$tok = strtok($strExt, ",");
			while ($tok)
			{
				if (strtolower(trim($tok)) == strtolower($strFileExt))
				{
					$IsExtCorrect = true;
					break;
				}
				$tok = strtok(",");
			}
		}

		if ($IsExtCorrect)
		{
			return "";
		}

		return GetMessage("FILE_BAD_TYPE") . " (" . strip_tags($strFileExt) . ")";
	}

	public static function ShowFile($iFileID, $max_file_size = 0, $iMaxW = 0, $iMaxH = 0, $bPopup = false, $sParams = false, $sPopupTitle = false, $iSizeWHTTP = 0, $iSizeHHTTP = 0)
	{
		$strResult = "";

		$arFile = static::GetFileArray($iFileID);
		if ($arFile)
		{
			$max_file_size = intval($max_file_size);
			if ($max_file_size <= 0)
			{
				$max_file_size = 1000000000;
			}

			$ct = $arFile["CONTENT_TYPE"];
			if ($arFile["FILE_SIZE"] <= $max_file_size && static::IsImage($arFile["SRC"], $ct))
			{
				$strResult = static::ShowImage($arFile, $iMaxW, $iMaxH, $sParams, "", $bPopup, $sPopupTitle, $iSizeWHTTP, $iSizeHHTTP);
			}
			else
			{
				$strResult = '<a href="' . htmlspecialcharsbx($arFile["SRC"]) . '" title="' . GetMessage("FILE_FILE_DOWNLOAD") . '">' . htmlspecialcharsbx($arFile["FILE_NAME"]) . '</a>';
			}
		}
		return $strResult;
	}

	public static function DisableJSFunction($b = true)
	{
		global $SHOWIMAGEFIRST;
		$SHOWIMAGEFIRST = $b;
	}

	public static function OutputJSImgShw()
	{
		global $SHOWIMAGEFIRST;
		if (!defined("ADMIN_SECTION") && $SHOWIMAGEFIRST !== true)
		{
			echo
				'<script>
function ImgShw(ID, width, height, alt)
{
	var scroll = "no";
	var top=0, left=0;
	var w, h;
	if(navigator.userAgent.toLowerCase().indexOf("opera") != -1)
	{
		w = document.body.offsetWidth;
		h = document.body.offsetHeight;
	}
	else
	{
		w = screen.width;
		h = screen.height;
	}
	if(width > w-10 || height > h-28)
		scroll = "yes";
	if(height < h-28)
		top = Math.floor((h - height)/2-14);
	if(width < w-10)
		left = Math.floor((w - width)/2-5);
	width = Math.min(width, w-10);
	height = Math.min(height, h-28);
	var wnd = window.open("","","scrollbars="+scroll+",resizable=yes,width="+width+",height="+height+",left="+left+",top="+top);
	wnd.document.write(
		"<html><head>"+
		"<"+"script>"+
		"function KeyPress(e)"+
		"{"+
		"	if (!e) e = window.event;"+
		"	if(e.keyCode == 27) "+
		"		window.close();"+
		"}"+
		"</"+"script>"+
		"<title>"+(alt == ""? "' . GetMessage("main_js_img_title") . '":alt)+"</title></head>"+
		"<body topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" onKeyDown=\"KeyPress(arguments[0])\">"+
		"<img src=\""+ID+"\" border=\"0\" alt=\""+alt+"\" />"+
		"</body></html>"
	);
	wnd.document.close();
	wnd.focus();
}
</script>';

			$SHOWIMAGEFIRST = true;
		}
	}

	public static function _GetImgParams($strImage, $iSizeWHTTP = 0, $iSizeHHTTP = 0)
	{
		global $arCloudImageSizeCache;

		$io = CBXVirtualIo::GetInstance();

		if ($strImage == '')
		{
			return false;
		}

		$strAlt = '';
		if (intval($strImage) > 0)
		{
			$db_img_arr = static::GetFileArray($strImage);
			if ($db_img_arr)
			{
				$strImage = $db_img_arr["SRC"];
				$intWidth = intval($db_img_arr["WIDTH"]);
				$intHeight = intval($db_img_arr["HEIGHT"]);
				$strAlt = $db_img_arr["DESCRIPTION"];
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (!preg_match("#^https?://#", $strImage))
			{
				$imageInfo = (new File\Image($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"] . $strImage)))->getInfo();
				if ($imageInfo)
				{
					$intWidth = $imageInfo->getWidth();
					$intHeight = $imageInfo->getHeight();
				}
				else
				{
					return false;
				}
			}
			elseif (array_key_exists($strImage, $arCloudImageSizeCache))
			{
				$intWidth = $arCloudImageSizeCache[$strImage][0];
				$intHeight = $arCloudImageSizeCache[$strImage][1];
			}
			else
			{
				$intWidth = intval($iSizeWHTTP);
				$intHeight = intval($iSizeHHTTP);
			}
		}

		return [
			"SRC" => $strImage,
			"WIDTH" => $intWidth,
			"HEIGHT" => $intHeight,
			"ALT" => $strAlt,
		];
	}

	/**
	 * Retuns the path from the root by a file ID.
	 *
	 * @param int $img_id File ID
	 * @return string|null
	 */
	public static function GetPath($img_id)
	{
		$img_id = intval($img_id);
		if ($img_id > 0)
		{
			$res = static::_GetImgParams($img_id);
			return is_array($res) ? $res["SRC"] : null;
		}
		return null;
	}

	public static function ShowImage($strImage, $iMaxW = 0, $iMaxH = 0, $sParams = null, $strImageUrl = "", $bPopup = false, $sPopupTitle = false, $iSizeWHTTP = 0, $iSizeHHTTP = 0, $strImageUrlTemplate = "")
	{
		if (is_array($strImage))
		{
			$arImgParams = $strImage;
			$iImageID = isset($arImgParams['ID']) ? (int)$arImgParams['ID'] : 0;
		}
		else
		{
			$arImgParams = static::_GetImgParams($strImage, $iSizeWHTTP, $iSizeHHTTP);
			$iImageID = (int)$strImage;
		}

		if (!$arImgParams)
		{
			return "";
		}

		$iMaxW = (int)$iMaxW;
		$iMaxH = (int)$iMaxH;
		$intWidth = (int)$arImgParams['WIDTH'];
		$intHeight = (int)$arImgParams['HEIGHT'];
		if (
			$iMaxW > 0
			&& $iMaxH > 0
			&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
		)
		{
			$coeff = ($intWidth / $iMaxW > $intHeight / $iMaxH ? $intWidth / $iMaxW : $intHeight / $iMaxH);
			$iHeight = (int)roundEx($intHeight / $coeff);
			$iWidth = (int)roundEx($intWidth / $coeff);
		}
		else
		{
			$coeff = 1;
			$iHeight = $intHeight;
			$iWidth = $intWidth;
		}

		$strImageUrlTemplate = strval($strImageUrlTemplate);
		if ($strImageUrlTemplate === '' || $iImageID <= 0)
		{
			$strImage = $arImgParams['SRC'];
		}
		else
		{
			$strImage = CComponentEngine::MakePathFromTemplate($strImageUrlTemplate, ['file_id' => $iImageID]);
		}

		$strImage = Uri::urnEncode($strImage);

		if (GetFileType($strImage) == "FLASH")
		{
			$strReturn = '
				<object
					classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000"
					codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
					id="banner"
					WIDTH="' . $iWidth . '"
					HEIGHT="' . $iHeight . '"
					ALIGN="">
						<PARAM NAME="movie" VALUE="' . htmlspecialcharsbx($strImage) . '" />
						<PARAM NAME="quality" VALUE="high" />
						<PARAM NAME="bgcolor" VALUE="#FFFFFF" />
						<embed
							src="' . htmlspecialcharsbx($strImage) . '"
							quality="high"
							bgcolor="#FFFFFF"
							WIDTH="' . $iWidth . '"
							HEIGHT="' . $iHeight . '"
							NAME="banner"
							ALIGN=""
							TYPE="application/x-shockwave-flash"
							PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer" 
						/>
				</object>
			';
		}
		else
		{
			$strAlt = $arImgParams['ALT'] ?? ($arImgParams['DESCRIPTION'] ?? '');

			if ($sParams === null || $sParams === false)
			{
				$sParams = 'border="0" alt="' . htmlspecialcharsEx($strAlt) . '"';
			}
			elseif (!preg_match('/(^|\\s)alt\\s*=\\s*(["\']?)(.*?)(\\2)/is', $sParams))
			{
				$sParams .= ' alt="' . htmlspecialcharsEx($strAlt) . '"';
			}

			if ($coeff === 1 || !$bPopup)
			{
				$strReturn = '<img src="' . htmlspecialcharsbx($strImage) . '" ' . $sParams . ' width="' . $iWidth . '" height="' . $iHeight . '" />';
			}
			else
			{
				if ($sPopupTitle === false)
				{
					$sPopupTitle = GetMessage('FILE_ENLARGE');
				}

				if ($strImageUrl <> '')
				{
					$strReturn =
						'<a href="' . $strImageUrl . '" title="' . htmlspecialcharsEx($sPopupTitle) . '" target="_blank">' .
						'<img src="' . htmlspecialcharsbx($strImage) . '" ' . $sParams . ' width="' . $iWidth . '" height="' . $iHeight . '" title="' . htmlspecialcharsEx($sPopupTitle) . '" />' .
						'</a>';
				}
				else
				{
					static::OutputJSImgShw();

					$strReturn =
						'<a title="' . $sPopupTitle . '" ' .
						'onclick="ImgShw(\'' . htmlspecialcharsbx(CUtil::addslashes($strImage)) . '\', ' . $intWidth . ', ' . $intHeight . ', \'' . CUtil::addslashes(htmlspecialcharsEx(htmlspecialcharsEx($strAlt))) . '\'); return false;" ' .
						'href="' . htmlspecialcharsbx($strImage) . '" ' .
						'target="_blank"' .
						'>' .
						'<img src="' . htmlspecialcharsbx($strImage) . '" ' . $sParams . ' width="' . $iWidth . '" height="' . $iHeight . '" />' .
						'</a>';
				}
			}
		}

		return $bPopup ? $strReturn : print_url($strImageUrl, $strReturn);
	}

	public static function Show2Images($strImage1, $strImage2, $iMaxW = 0, $iMaxH = 0, $sParams = false, $sPopupTitle = false, $iSizeWHTTP = 0, $iSizeHHTTP = 0)
	{
		if (!($arImgParams = static::_GetImgParams($strImage1, $iSizeWHTTP, $iSizeHHTTP)))
		{
			return "";
		}

		$strImage1 = Uri::urnEncode($arImgParams["SRC"]);

		$intWidth = $arImgParams["WIDTH"];
		$intHeight = $arImgParams["HEIGHT"];
		$strAlt = $arImgParams["ALT"];

		if (!$sParams)
		{
			$sParams = 'border="0" alt="' . htmlspecialcharsEx($strAlt) . '"';
		}
		elseif (!preg_match("/(^|\\s)alt\\s*=\\s*([\"']?)(.*?)(\\2)/is", $sParams))
		{
			$sParams .= ' alt="' . htmlspecialcharsEx($strAlt) . '"';
		}

		if (
			$iMaxW > 0 && $iMaxH > 0
			&& ($intWidth > $iMaxW || $intHeight > $iMaxH)
		)
		{
			$coeff = ($intWidth / $iMaxW > $intHeight / $iMaxH ? $intWidth / $iMaxW : $intHeight / $iMaxH);
			$iHeight = intval(roundEx($intHeight / $coeff));
			$iWidth = intval(roundEx($intWidth / $coeff));
		}
		else
		{
			$iHeight = $intHeight;
			$iWidth = $intWidth;
		}

		if ($arImgParams = static::_GetImgParams($strImage2, $iSizeWHTTP, $iSizeHHTTP))
		{
			if ($sPopupTitle === false)
			{
				$sPopupTitle = GetMessage("FILE_ENLARGE");
			}

			$strImage2 = Uri::urnEncode($arImgParams["SRC"]);
			$intWidth2 = $arImgParams["WIDTH"];
			$intHeight2 = $arImgParams["HEIGHT"];
			$strAlt2 = $arImgParams["ALT"];

			static::OutputJSImgShw();

			$strReturn =
				"<a title=\"" . $sPopupTitle . "\" onclick=\"ImgShw('" . CUtil::addslashes($strImage2) . "','" . $intWidth2 . "','" . $intHeight2 . "', '" . CUtil::addslashes(htmlspecialcharsEx(htmlspecialcharsEx($strAlt2))) . "'); return false;\" href=\"" . $strImage2 . "\" target=_blank>" .
				"<img src=\"" . $strImage1 . "\" " . $sParams . " width=" . $iWidth . " height=" . $iHeight . " /></a>";
		}
		else
		{
			$strReturn = "<img src=\"" . $strImage1 . "\" " . $sParams . " width=" . $iWidth . " height=" . $iHeight . " />";
		}

		return $strReturn;
	}

	/**
	 * Returns an array describing file as if it was $_FILES element.
	 *
	 * @param string|int $path May contain ID of the file, absolute path, relative path or an url.
	 * @param string|bool $mimetype Forces type field of the array
	 * @param bool $skipInternal Excludes using ID as $path
	 * @param string $external_id
	 * @return array|bool|null
	 */
	public static function MakeFileArray($path, $mimetype = false, $skipInternal = false, $external_id = "")
	{
		$io = CBXVirtualIo::GetInstance();
		$arFile = [];

		if (intval($path) > 0)
		{
			if ($skipInternal)
			{
				return false;
			}

			$res = static::GetByID($path);
			if ($ar = $res->Fetch())
			{
				$bExternalStorage = false;
				foreach (GetModuleEvents("main", "OnMakeFileArray", true) as $arEvent)
				{
					if (ExecuteModuleEventEx($arEvent, [$ar, &$arFile]))
					{
						$bExternalStorage = true;
						break;
					}
				}

				if (!$bExternalStorage)
				{
					$arFile["name"] = ($ar['ORIGINAL_NAME'] <> '' ? $ar['ORIGINAL_NAME'] : $ar['FILE_NAME']);
					$arFile["size"] = $ar['FILE_SIZE'];
					$arFile["type"] = $ar['CONTENT_TYPE'];
					$arFile["description"] = $ar['DESCRIPTION'];
					$arFile["tmp_name"] = $io->GetPhysicalName(preg_replace("#[\\\\/]+#", "/", $_SERVER['DOCUMENT_ROOT'] . '/' . (COption::GetOptionString('main', 'upload_dir', 'upload')) . '/' . $ar['SUBDIR'] . '/' . $ar['FILE_NAME']));
				}
				if (!isset($arFile["external_id"]))
				{
					$arFile["external_id"] = $external_id != "" ? $external_id : $ar["EXTERNAL_ID"];
				}
				return $arFile;
			}
		}

		$path = preg_replace("#(?<!:)[\\\\/]+#", "/", $path);

		if (!is_scalar($path) || $path == '' || $path == "/")
		{
			return null;
		}

		if (preg_match("#^(php://|phar://)#i", $path) && !preg_match("#^php://input$#i", $path))
		{
			return null;
		}

		if (preg_match("#^https?://#i", $path))
		{
			$temp_path = '';
			$bExternalStorage = false;
			foreach (GetModuleEvents("main", "OnMakeFileArray", true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, [$path, &$temp_path]))
				{
					$bExternalStorage = true;
					break;
				}
			}

			if (!$bExternalStorage)
			{
				$http = new Web\HttpClient();
				$http->setPrivateIp(false);
				$temp_path = static::GetTempName('', 'tmp.' . md5(mt_rand()));
				if ($http->download($path, $temp_path))
				{
					$arFile = static::MakeFileArray($temp_path);
					if ($arFile)
					{
						$urlComponents = parse_url($path);
						if ($urlComponents && $urlComponents["path"] <> '')
						{
							$arFile["name"] = $io->GetLogicalName(bx_basename($urlComponents["path"]));
						}
						else
						{
							$arFile["name"] = $io->GetLogicalName(bx_basename($path));
						}
					}
				}
			}
			elseif ($temp_path)
			{
				$arFile = static::MakeFileArray($temp_path);
			}
		}
		elseif (preg_match("#^(ftps?://|php://input)#i", $path))
		{
			if ($fp = fopen($path, "rb"))
			{
				$content = "";
				while (!feof($fp))
				{
					$content .= fgets($fp, 4096);
				}

				if ($content <> '')
				{
					$temp_path = static::GetTempName('', 'tmp.' . md5(mt_rand()));
					if (RewriteFile($temp_path, $content))
					{
						$arFile = static::MakeFileArray($temp_path);
						if ($arFile)
						{
							$arFile["name"] = $io->GetLogicalName(bx_basename($path));
						}
					}
				}

				fclose($fp);
			}
		}
		else
		{
			if (!file_exists($path))
			{
				if (file_exists($_SERVER["DOCUMENT_ROOT"] . $path))
				{
					$path = $_SERVER["DOCUMENT_ROOT"] . $path;
				}
				else
				{
					return null;
				}
			}

			if (is_dir($path))
			{
				return null;
			}

			$arFile["name"] = $io->GetLogicalName(bx_basename($path));
			$arFile["size"] = filesize($path);
			$arFile["tmp_name"] = $path;
			$arFile["type"] = $mimetype;

			if ($arFile["type"] == '')
			{
				$arFile["type"] = static::GetContentType($path, true);
			}
		}

		if ($arFile["type"] == '')
		{
			$arFile["type"] = "unknown";
		}

		if (!isset($arFile["external_id"]) && ($external_id != ""))
		{
			$arFile["external_id"] = $external_id;
		}

		return $arFile;
	}

	public static function GetTempName($dir_name = false, $file_name = '')
	{
		//accidentally $file_name can contain "?params"
		if (($pos = mb_strpos($file_name, "?")) !== false)
		{
			$file_name = mb_substr($file_name, 0, $pos);
		}
		return CTempFile::GetFileName($file_name);
	}

	public static function ChangeSubDir($module_id, $old_subdir, $new_subdir)
	{
		global $DB;

		if ($old_subdir != $new_subdir)
		{
			$strSql = "
				UPDATE b_file SET
					SUBDIR = REPLACE(SUBDIR,'" . $DB->ForSQL($old_subdir) . "','" . $DB->ForSQL($new_subdir) . "'),
					TIMESTAMP_X = " . $DB->GetNowFunction() . "
				WHERE MODULE_ID='" . $DB->ForSQL($module_id) . "'
			";

			if ($DB->Query($strSql))
			{
				$from = "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/" . $old_subdir;
				$to = "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/" . $new_subdir;
				CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . $from, $_SERVER["DOCUMENT_ROOT"] . $to, true, true, true);

				//Reset All b_file cache
				$cache = Main\Application::getInstance()->getManagedCache();
				$cache->cleanDir(self::CACHE_DIR);
			}
		}
	}

	public static function ResizeImage(&$arFile, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL)
	{
		$io = CBXVirtualIo::GetInstance();

		// $arFile["tmp_name"] should contain physical filename
		$destinationFile = CTempFile::GetFileName(basename($arFile["tmp_name"]));
		$sourceFile = $io->GetLogicalName($arFile["tmp_name"]);

		CheckDirPath($destinationFile);

		if (static::ResizeImageFile($sourceFile, $destinationFile, $arSize, $resizeType))
		{
			$arFile["tmp_name"] = $io->GetPhysicalName($destinationFile);

			$imageInfo = (new File\Image($arFile["tmp_name"]))->getInfo();
			if ($imageInfo)
			{
				$arFile["type"] = $imageInfo->getMime();
			}
			$arFile["size"] = filesize($arFile["tmp_name"]);

			return true;
		}

		return false;
	}

	public static function ResizeImageDeleteCache($arFile)
	{
		$temp_dir = CTempFile::GetAbsoluteRoot() . "/";
		if (mb_strpos($arFile["tmp_name"], $temp_dir) === 0)
		{
			if (file_exists($arFile["tmp_name"]))
			{
				unlink($arFile["tmp_name"]);
			}
		}
	}

	public static function ResizeImageGet($file, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $bInitSizes = false, $arFilters = false, $bImmediate = false, $jpgQuality = false)
	{
		if (!is_array($file) && intval($file) > 0)
		{
			$file = static::GetFileArray($file);
		}

		if (!is_array($file) || !array_key_exists("FILE_NAME", $file) || $file["FILE_NAME"] == '')
		{
			return false;
		}

		if ($resizeType !== BX_RESIZE_IMAGE_EXACT && $resizeType !== BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
		{
			$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
		}

		if (!is_array($arSize))
		{
			$arSize = [];
		}
		if (!array_key_exists("width", $arSize) || intval($arSize["width"]) <= 0)
		{
			$arSize["width"] = 0;
		}
		if (!array_key_exists("height", $arSize) || intval($arSize["height"]) <= 0)
		{
			$arSize["height"] = 0;
		}
		$arSize["width"] = intval($arSize["width"]);
		$arSize["height"] = intval($arSize["height"]);

		$uploadDirName = COption::GetOptionString("main", "upload_dir", "upload");

		$imageFile = "/" . $uploadDirName . "/" . $file["SUBDIR"] . "/" . $file["FILE_NAME"];
		$arImageSize = false;
		$bFilters = is_array($arFilters) && !empty($arFilters);

		if (
			($arSize["width"] <= 0 || $arSize["width"] >= $file["WIDTH"])
			&& ($arSize["height"] <= 0 || $arSize["height"] >= $file["HEIGHT"])
		)
		{
			if ($bFilters)
			{
				//Only filters. Leave size unchanged
				$arSize["width"] = $file["WIDTH"];
				$arSize["height"] = $file["HEIGHT"];
				$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
			}
			else
			{
				if (isset($file["SRC"]))
				{
					global $arCloudImageSizeCache;
					$arCloudImageSizeCache[$file["SRC"]] = [$file["WIDTH"], $file["HEIGHT"]];
				}
				else
				{
					trigger_error("Parameter \$file for CFile::ResizeImageGet does not have SRC element. You'd better pass an b_file.ID as a value for the \$file parameter.", E_USER_WARNING);
				}

				return [
					"src" => $file["SRC"],
					"width" => intval($file["WIDTH"]),
					"height" => intval($file["HEIGHT"]),
					"size" => $file["FILE_SIZE"],
				];
			}
		}

		$io = CBXVirtualIo::GetInstance();

		$cacheImageFile = "/" . $uploadDirName . "/resize_cache/" . $file["SUBDIR"] . "/" . $arSize["width"] . "_" . $arSize["height"] . "_" . $resizeType . (is_array($arFilters) ? md5(serialize($arFilters)) : "") . "/" . $file["FILE_NAME"];
		$cacheImageFileCheck = $cacheImageFile;

		static $cache = [];
		$cache_id = $cacheImageFileCheck;
		if (isset($cache[$cache_id]))
		{
			return $cache[$cache_id];
		}
		elseif (!file_exists($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck)))
		{
			if (!is_array($arFilters))
			{
				$arFilters = [
					["name" => "sharpen", "precision" => 15],
				];
			}

			$sourceImageFile = $_SERVER["DOCUMENT_ROOT"] . $imageFile;
			$cacheImageFileTmp = $_SERVER["DOCUMENT_ROOT"] . $cacheImageFile;
			$bNeedResize = true;
			$callbackData = null;

			foreach (GetModuleEvents("main", "OnBeforeResizeImage", true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, [
					$file,
					[$arSize, $resizeType, [], false, $arFilters, $bImmediate],
					&$callbackData,
					&$bNeedResize,
					&$sourceImageFile,
					&$cacheImageFileTmp,
				]))
				{
					break;
				}
			}

			if ($bNeedResize && static::ResizeImageFile($sourceImageFile, $cacheImageFileTmp, $arSize, $resizeType, [], $jpgQuality, $arFilters))
			{
				$cacheImageFile = mb_substr($cacheImageFileTmp, mb_strlen($_SERVER["DOCUMENT_ROOT"]));

				/****************************** QUOTA ******************************/
				if (COption::GetOptionInt("main", "disk_space") > 0)
				{
					CDiskQuota::updateDiskQuota("file", filesize($io->GetPhysicalName($cacheImageFileTmp)), "insert");
				}
				/****************************** QUOTA ******************************/
			}
			else
			{
				$cacheImageFile = $imageFile;
			}

			foreach (GetModuleEvents("main", "OnAfterResizeImage", true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, [
					$file,
					[$arSize, $resizeType, [], false, $arFilters],
					&$callbackData,
					&$cacheImageFile,
					&$cacheImageFileTmp,
					&$arImageSize,
				]))
				{
					break;
				}
			}

			$cacheImageFileCheck = $cacheImageFile;
		}
		elseif (defined("BX_FILE_USE_FLOCK"))
		{
			$hLock = $io->OpenFile($_SERVER["DOCUMENT_ROOT"] . $imageFile, "r+");
			if ($hLock)
			{
				flock($hLock, LOCK_EX);
				flock($hLock, LOCK_UN);
				fclose($hLock);
			}
		}

		if ($bInitSizes && !is_array($arImageSize))
		{
			$imageInfo = (new File\Image($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck))->getInfo();
			if ($imageInfo)
			{
				$arImageSize[0] = $imageInfo->getWidth();
				$arImageSize[1] = $imageInfo->getHeight();
			}
			else
			{
				$arImageSize = [0, 0];
			}

			$f = $io->GetFile($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck);
			$arImageSize[2] = $f->GetFileSize();
		}

		if (!is_array($arImageSize))
		{
			$arImageSize = [0, 0, 0];
		}

		$cache[$cache_id] = [
			"src" => $cacheImageFileCheck,
			"width" => intval($arImageSize[0]),
			"height" => intval($arImageSize[1]),
			"size" => $arImageSize[2],
		];
		return $cache[$cache_id];
	}

	public static function ResizeImageDelete($arImage)
	{
		$io = CBXVirtualIo::GetInstance();
		$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
		$disk_space = COption::GetOptionInt("main", "disk_space");
		$delete_size = 0;

		$d = $io->GetDirectory($_SERVER["DOCUMENT_ROOT"] . "/" . $upload_dir . "/resize_cache/" . $arImage["SUBDIR"]);

		/** @var CBXVirtualFileFileSystem|CBXVirtualDirectoryFileSystem $dir_entry */
		foreach ($d->GetChildren() as $dir_entry)
		{
			if ($dir_entry->IsDirectory())
			{
				$f = $io->GetFile($dir_entry->GetPathWithName() . "/" . $arImage["FILE_NAME"]);
				if ($f->IsExists())
				{
					if ($disk_space > 0)
					{
						$fileSizeTmp = $f->GetFileSize();
						if ($io->Delete($f->GetPathWithName()))
						{
							$delete_size += $fileSizeTmp;
						}
					}
					else
					{
						$io->Delete($f->GetPathWithName());
					}
				}

				try
				{
					@rmdir($io->GetPhysicalName($dir_entry->GetPathWithName()));
				}
				catch (\ErrorException)
				{
					// Ignore a E_WARNING Error
				}
			}
		}

		try
		{
			@rmdir($io->GetPhysicalName($d->GetPathWithName()));
		}
		catch (\ErrorException)
		{
			// Ignore a E_WARNING Error
		}

		return $delete_size;
	}

	/**
	 * @param $filename
	 * @return false|resource
	 * @deprecated Use imagecreatefrombmp()
	 */
	public static function ImageCreateFromBMP($filename)
	{
		return imagecreatefrombmp($filename);
	}

	/**
	 * @param $sourceImageWidth
	 * @param $sourceImageHeight
	 * @param $arSize
	 * @param $resizeType
	 * @param $bNeedCreatePicture
	 * @param $arSourceSize
	 * @param $arDestinationSize
	 * @deprecated Use \Bitrix\Main\File\Image\Rectangle::resize()
	 */
	public static function ScaleImage($sourceImageWidth, $sourceImageHeight, $arSize, $resizeType, &$bNeedCreatePicture, &$arSourceSize, &$arDestinationSize)
	{
		$source = new Rectangle($sourceImageWidth, $sourceImageHeight);
		$destination = new Rectangle($arSize["width"], $arSize["height"]);

		$bNeedCreatePicture = $source->resize($destination, $resizeType);

		$arSourceSize = [
			"x" => $source->getX(),
			"y" => $source->getY(),
			"width" => $source->getWidth(),
			"height" => $source->getHeight(),
		];
		$arDestinationSize = [
			"x" => $destination->getX(),
			"y" => $destination->getY(),
			"width" => $destination->getWidth(),
			"height" => $destination->getHeight(),
		];
	}

	/**
	 * @return bool
	 * @deprecated Always returns true.
	 */
	public static function IsGD2()
	{
		return true;
	}

	public static function ResizeImageFile($sourceFile, $destinationFile, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $arWaterMark = [], $quality = false, $arFilters = false)
	{
		$io = CBXVirtualIo::GetInstance();

		if (!$io->FileExists($sourceFile))
		{
			return false;
		}

		if ($resizeType !== BX_RESIZE_IMAGE_EXACT && $resizeType !== BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
		{
			$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
		}

		if (!is_array($arSize))
		{
			$arSize = [];
		}
		if (!array_key_exists("width", $arSize) || intval($arSize["width"]) <= 0)
		{
			$arSize["width"] = 0;
		}
		if (!array_key_exists("height", $arSize) || intval($arSize["height"]) <= 0)
		{
			$arSize["height"] = 0;
		}
		$arSize["width"] = intval($arSize["width"]);
		$arSize["height"] = intval($arSize["height"]);

		$sourceImage = new File\Image($io->GetPhysicalName($sourceFile));
		$sourceInfo = $sourceImage->getInfo();

		if ($sourceInfo === null || !$sourceInfo->isSupported())
		{
			return false;
		}

		$fileType = $sourceInfo->getFormat();

		$orientation = 0;
		if ($fileType == File\Image::FORMAT_JPEG)
		{
			$exifData = $sourceImage->getExifData();
			if (isset($exifData['Orientation']))
			{
				$orientation = $exifData['Orientation'];
				//swap width and height
				if ($orientation >= 5 && $orientation <= 8)
				{
					$sourceInfo->swapSides();
				}
			}
		}

		$result = false;

		$sourceRectangle = $sourceInfo->toRectangle();
		$destinationRectangle = new Rectangle($arSize["width"], $arSize["height"]);

		$needResize = $sourceRectangle->resize($destinationRectangle, $resizeType);

		$hLock = $io->OpenFile($sourceFile, "r+");
		$useLock = defined("BX_FILE_USE_FLOCK");

		if ($hLock)
		{
			if ($useLock)
			{
				flock($hLock, LOCK_EX);
			}
			if ($io->FileExists($destinationFile))
			{
				$destinationInfo = (new File\Image($io->GetPhysicalName($destinationFile)))->getInfo();
				if ($destinationInfo)
				{
					if ($destinationInfo->getWidth() == $destinationRectangle->getWidth() && $destinationInfo->getHeight() == $destinationRectangle->getHeight())
					{
						//nothing to do
						$result = true;
					}
				}
			}
		}

		if ($result === false)
		{
			if ($io->Copy($sourceFile, $destinationFile))
			{
				$destinationImage = new File\Image($io->GetPhysicalName($destinationFile));

				if ($destinationImage->load())
				{
					if ($orientation > 1)
					{
						$destinationImage->autoRotate($orientation);
					}

					$modified = false;
					if ($needResize)
					{
						// actual sizes
						$sourceRectangle = $destinationImage->getDimensions();
						$destinationRectangle = new Rectangle($arSize["width"], $arSize["height"]);

						$sourceRectangle->resize($destinationRectangle, $resizeType);

						$modified = $destinationImage->resize($sourceRectangle, $destinationRectangle);
					}

					if (!is_array($arFilters))
					{
						$arFilters = [];
					}

					if (is_array($arWaterMark))
					{
						$arWaterMark["name"] = "watermark";
						$arFilters[] = $arWaterMark;
					}

					foreach ($arFilters as $arFilter)
					{
						if ($arFilter["name"] == "sharpen" && $arFilter["precision"] > 0)
						{
							$modified |= $destinationImage->filter(File\Image\Mask::createSharpen($arFilter["precision"]));
						}
						elseif ($arFilter["name"] == "watermark")
						{
							$watermark = Image\Watermark::createFromArray($arFilter);
							$modified |= $destinationImage->drawWatermark($watermark);
						}
					}

					if ($modified)
					{
						if ($quality === false)
						{
							$quality = COption::GetOptionString('main', 'image_resize_quality');
						}

						$io->Delete($destinationFile);

						if ($fileType == File\Image::FORMAT_BMP)
						{
							$destinationImage->saveAs($io->GetPhysicalName($destinationFile), $quality, File\Image::FORMAT_JPEG);
						}
						else
						{
							$destinationImage->save($quality);
						}

						$destinationImage->clear();
					}
				}

				$result = true;
			}
		}

		if ($hLock)
		{
			if ($useLock)
			{
				flock($hLock, LOCK_UN);
			}
			fclose($hLock);
		}

		return $result;
	}

	/**
	 * @param $picture
	 * @param $arFilter
	 * @return bool
	 * @deprecated Use \Bitrix\Main\File\Image
	 */
	public static function ApplyImageFilter($picture, $arFilter)
	{
		//prevents destroing outside the function
		static $engine;

		switch ($arFilter["name"])
		{
			case "sharpen":
				$precision = intval($arFilter["precision"]);
				if ($precision > 0)
				{
					$engine = new File\Image\Gd();
					$engine->setResource($picture);
					return $engine->filter(File\Image\Mask::createSharpen($precision));
				}
				return false;
			case "watermark":
				return static::WaterMark($picture, $arFilter);
		}
		return false;
	}

	/**
	 * @param $picture
	 * @deprecated Use \Bitrix\Main\File\Image
	 */
	public static function ImageFlipHorizontal($picture)
	{
		//prevents destroing outside the function
		static $engine;

		$engine = new File\Image\Gd();
		$engine->setResource($picture);
		$engine->flipHorizontal();
	}

	/**
	 * @param $orientation
	 * @param $sourceImage
	 * @return false|resource
	 * @deprecated Use \Bitrix\Main\File\Image::autoRotate()
	 */
	public static function ImageHandleOrientation($orientation, $sourceImage)
	{
		if ($orientation <= 1)
		{
			return false;
		}

		if (!is_resource($sourceImage))
		{
			//file
			$image = new File\Image($sourceImage);
			if ($image->load())
			{
				if ($image->autoRotate($orientation))
				{
					$quality = COption::GetOptionString('main', 'image_resize_quality');
					$image->save($quality);
				}
			}
			return false;
		}

		//prevents destroing outside the function
		static $engine;

		//compatibility around GD image resource
		$engine = new File\Image\Gd();
		$engine->setResource($sourceImage);

		$image = new File\Image();
		$image->setEngine($engine);
		$image->autoRotate($orientation);

		return $engine->getResource();
	}

	/**
	 * @param int|array $arFile
	 * @param array $arOptions
	 * @return bool
	 */
	public static function ViewByUser($arFile, $arOptions = [])
	{
		$previewManager = new Viewer\PreviewManager();
		if ($previewManager->isInternalRequest($arFile, $arOptions))
		{
			$previewManager->processViewByUserRequest($arFile, $arOptions);
		}

		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$fastDownload = (COption::GetOptionString('main', 'bx_fast_download', 'N') == 'Y');

		$attachment_name = "";
		$content_type = "";
		$specialchars = false;
		$force_download = false;
		$cache_time = 10800;
		$fromClouds = false;
		$filename = '';
		$fromTemp = false;

		if (is_array($arOptions))
		{
			if (isset($arOptions["content_type"]))
			{
				$content_type = $arOptions["content_type"];
			}
			if (isset($arOptions["specialchars"]))
			{
				$specialchars = $arOptions["specialchars"];
			}
			if (isset($arOptions["force_download"]))
			{
				$force_download = $arOptions["force_download"];
			}
			if (isset($arOptions["cache_time"]))
			{
				$cache_time = intval($arOptions["cache_time"]);
			}
			if (isset($arOptions["attachment_name"]))
			{
				$attachment_name = $arOptions["attachment_name"];
			}
			if (isset($arOptions["fast_download"]))
			{
				$fastDownload = (bool)$arOptions["fast_download"];
			}
		}

		if ($cache_time < 0)
		{
			$cache_time = 0;
		}

		if (is_array($arFile))
		{
			if (isset($arFile["SRC"]))
			{
				$filename = $arFile["SRC"];
			}
			elseif (isset($arFile["tmp_name"]))
			{
				if (mb_strpos($arFile['tmp_name'], $_SERVER['DOCUMENT_ROOT']) === 0)
				{
					$filename = '/' . ltrim(mb_substr($arFile['tmp_name'], mb_strlen($_SERVER['DOCUMENT_ROOT'])), '/');
				}
				elseif (defined('BX_TEMPORARY_FILES_DIRECTORY') && mb_strpos($arFile['tmp_name'], BX_TEMPORARY_FILES_DIRECTORY) === 0)
				{
					$fromTemp = true;
					$tmpPath = COption::GetOptionString('main', 'bx_tmp_download', '/bx_tmp_download/');
					$filename = $tmpPath . ltrim(mb_substr($arFile['tmp_name'], mb_strlen(BX_TEMPORARY_FILES_DIRECTORY)), '/'); //nonexistent path
				}
			}
			else
			{
				$filename = static::GetFileSRC($arFile);
			}
		}
		elseif (($arFile = static::GetFileArray($arFile)))
		{
			$filename = $arFile['SRC'];
		}

		if ($filename == '')
		{
			return false;
		}

		if ($content_type == '' && isset($arFile["CONTENT_TYPE"]))
		{
			$content_type = $arFile["CONTENT_TYPE"];
		}

		//we produce resized jpg for original bmp
		if ($content_type == '' || $content_type == "image/bmp")
		{
			if (isset($arFile["tmp_name"]))
			{
				$content_type = static::GetContentType($arFile["tmp_name"], true);
			}
			else
			{
				$content_type = static::GetContentType($_SERVER["DOCUMENT_ROOT"] . $filename);
			}
		}

		if (isset($arFile["ORIGINAL_NAME"]) && $arFile["ORIGINAL_NAME"] != '')
		{
			$name = $arFile["ORIGINAL_NAME"];
		}
		elseif ($arFile["name"] <> '')
		{
			$name = $arFile["name"];
		}
		else
		{
			$name = $arFile["FILE_NAME"];
		}
		if (isset($arFile["EXTENSION_SUFFIX"]) && $arFile["EXTENSION_SUFFIX"] <> '')
		{
			$name = mb_substr($name, 0, -mb_strlen($arFile["EXTENSION_SUFFIX"]));
		}

		$name = str_replace(["\n", "\r"], '', $name);

		if ($attachment_name)
		{
			$attachment_name = str_replace(["\n", "\r"], '', $attachment_name);
		}
		else
		{
			$attachment_name = $name;
		}

		if (!$force_download)
		{
			if (!static::IsImage($name, $content_type) || $arFile["HEIGHT"] <= 0 || $arFile["WIDTH"] <= 0)
			{
				//only valid images can be downloaded inline
				$force_download = true;
			}
		}

		$content_type = Web\MimeType::normalize($content_type);

		if ($force_download)
		{
			$specialchars = false;
		}

		$src = null;
		$file = null;

		if ((str_starts_with($filename, '/')) && !$fromTemp)
		{
			$file = new IO\File($_SERVER['DOCUMENT_ROOT'] . $filename);
		}
		elseif (isset($arFile['tmp_name']))
		{
			$file = new IO\File($arFile['tmp_name']);
		}

		if ((str_starts_with($filename, '/')) && ($file instanceof IO\File))
		{
			try
			{
				$src = $file->open(IO\FileStreamOpenMode::READ);
			}
			catch (IO\IoException)
			{
				return false;
			}
		}
		else
		{
			if (!$fastDownload)
			{
				$src = new Web\HttpClient();
			}
			elseif (intval($arFile['HANDLER_ID']) > 0)
			{
				$fromClouds = true;
			}
		}

		$APPLICATION->RestartBuffer();
		$APPLICATION->EndBufferContentMan();

		$cur_pos = 0;
		$filesize = (isset($arFile["FILE_SIZE"]) && (int)$arFile["FILE_SIZE"] > 0 ? (int)$arFile["FILE_SIZE"] : (int)($arFile["size"] ?? 0));
		$size = $filesize - 1;
		$p = strpos($_SERVER["HTTP_RANGE"] ?? '', "=");
		if (intval($p) > 0)
		{
			$bytes = substr($_SERVER["HTTP_RANGE"], $p + 1);
			$p = strpos($bytes, "-");
			if ($p !== false)
			{
				$cur_pos = (float)substr($bytes, 0, $p);
				$size = (float)substr($bytes, $p + 1);
				if ($size <= 0)
				{
					$size = $filesize - 1;
				}
				if ($cur_pos > $size)
				{
					$cur_pos = 0;
					$size = $filesize - 1;
				}
			}
		}

		if ($file instanceof IO\File)
		{
			$filetime = $file->getModificationTime();
		}
		elseif (isset($arFile["tmp_name"]) && $arFile["tmp_name"] <> '')
		{
			$tmpFile = new IO\File($arFile["tmp_name"]);
			$filetime = $tmpFile->getModificationTime();
		}
		else
		{
			$filetime = intval(MakeTimeStamp($arFile["TIMESTAMP_X"]));
		}

		$response = \Bitrix\Main\Context::getCurrent()->getResponse();

		if ($_SERVER["REQUEST_METHOD"] == "HEAD")
		{
			$response->setStatus("200 OK")
				->addHeader("Accept-Ranges", "bytes")
				->addHeader("Content-Type", $content_type)
				->addHeader("Content-Length", ($size - $cur_pos + 1))
			;

			if ($filetime > 0)
			{
				$response->addHeader("Last-Modified", date("r", $filetime));
			}
		}
		else
		{
			$lastModified = '';
			if ($cache_time > 0)
			{
				//Handle ETag
				$ETag = md5($filename . $filesize . $filetime);
				if (array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER) && ($_SERVER['HTTP_IF_NONE_MATCH'] === $ETag))
				{
					$response->setStatus("304 Not Modified");
					$response->addHeader("Cache-Control", "private, max-age=" . $cache_time . ", pre-check=" . $cache_time);

					$response->writeHeaders();
					self::terminate();
				}

				$response->addHeader("ETag", $ETag);

				//Handle Last Modified
				if ($filetime > 0)
				{
					$lastModified = gmdate('D, d M Y H:i:s', $filetime) . ' GMT';
					if (array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER) && ($_SERVER['HTTP_IF_MODIFIED_SINCE'] === $lastModified))
					{
						$response->setStatus("304 Not Modified");
						$response->addHeader("Cache-Control", "private, max-age=" . $cache_time . ", pre-check=" . $cache_time);

						$response->writeHeaders();
						self::terminate();
					}
				}
			}

			$utfName = Uri::urnEncode($attachment_name);
			$translitName = CUtil::translit($attachment_name, LANGUAGE_ID, [
				"max_len" => 1024,
				"safe_chars" => ".",
				"replace_space" => '-',
				"change_case" => false,
			]);

			if ($force_download)
			{
				//Disable zlib for old versions of php <= 5.3.0
				//it has broken Content-Length handling
				if (ini_get('zlib.output_compression'))
				{
					ini_set('zlib.output_compression', 'Off');
				}

				// $p shows that we are sending partial content (range request)
				if ($p)
				{
					$response->setStatus("206 Partial Content");
				}
				else
				{
					$response->SetStatus("200 OK");
				}

				$response->addHeader("Content-Type", $content_type)
					->addHeader("Content-Disposition", "attachment; filename=\"" . $translitName . "\"; filename*=utf-8''" . $utfName)
					->addHeader("Content-Transfer-Encoding", "binary")
					->addHeader("Content-Length", ($size - $cur_pos + 1))
				;

				if (is_resource($src))
				{
					$response->addHeader("Accept-Ranges", "bytes");
					$response->addHeader("Content-Range", "bytes " . $cur_pos . "-" . $size . "/" . $filesize);
				}
			}
			else
			{
				$response->addHeader("Content-Type", $content_type);
				$response->addHeader("Content-Disposition", "inline; filename=\"" . $translitName . "\"; filename*=utf-8''" . $utfName);
			}

			if ($cache_time > 0)
			{
				$response->addHeader("Cache-Control", "private, max-age=" . $cache_time . ", pre-check=" . $cache_time);
				if ($filetime > 0)
				{
					$response->addHeader('Last-Modified', $lastModified);
				}
			}
			else
			{
				$response->addHeader("Cache-Control", "no-cache, must-revalidate, post-check=0, pre-check=0");
			}

			$response->addHeader("Expires", "0");
			$response->addHeader("Pragma", "public");

			$filenameEncoded = Uri::urnEncode($filename);
			// Download from front-end
			if ($fastDownload)
			{
				if ($fromClouds)
				{
					$filenameDisableProto = preg_replace('~^(https?)(\://)~i', '\\1.', $filenameEncoded);
					$cloudUploadPath = COption::GetOptionString('main', 'bx_cloud_upload', '/upload/bx_cloud_upload/');
					$response->addHeader('X-Accel-Redirect', rawurlencode($cloudUploadPath . $filenameDisableProto));
				}
				else
				{
					$response->addHeader('X-Accel-Redirect', $filenameEncoded);
				}
				$response->writeHeaders();
				self::terminate();
			}
			else
			{
				session_write_close();
				$response->writeHeaders();

				if ($specialchars)
				{
					/** @var IO\File $file */
					echo "<", "pre", ">";
					if (is_resource($src))
					{
						while (!feof($src))
						{
							echo htmlspecialcharsbx(fread($src, 32768));
						}
						$file->close();
					}
					else
					{
						/** @var Web\HttpClient $src */
						echo htmlspecialcharsbx($src->get($filenameEncoded));
					}
					echo "<", "/pre", ">";
				}
				else
				{
					if (is_resource($src))
					{
						/** @var IO\File $file */
						$file->seek($cur_pos);
						while (!feof($src) && ($cur_pos <= $size))
						{
							$bufsize = 131072; //128K
							if ($cur_pos + $bufsize > $size)
							{
								$bufsize = $size - $cur_pos + 1;
							}
							$cur_pos += $bufsize;
							echo fread($src, $bufsize);
						}
						$file->close();
					}
					else
					{
						$fp = fopen("php://output", "wb");
						/** @var Web\HttpClient $src */
						$src->setOutputStream($fp);
						$src->get($filenameEncoded);
					}
				}
				flush();
				self::terminate();
			}
		}
		return true;
	}

	private static function terminate(): void
	{
		/** @see \Bitrix\Main\HttpResponse::flush */
		if (function_exists("fastcgi_finish_request"))
		{
			//php-fpm
			fastcgi_finish_request();
		}

		Main\Application::getInstance()->terminate();
	}

	/**
	 * @param $obj
	 * @param $Params
	 *    type - text|image
	 *    size - big|medium|small|real, for custom resizing can be used 'coefficient', real - only for images
	 *    position - of the watermark on picture can be in one of two available notifications:
	 *         tl|tc|tr|ml|mc|mr|bl|bc|br or topleft|topcenter|topright|centerleft|center|centerright|bottomleft|bottomcenter|bottomright
	 * @return array|bool
	 * @deprecated Use \Bitrix\Main\File\Image.
	 */
	public static function Watermark($obj, $Params)
	{
		if ($Params['type'] == 'text')
		{
			$result = static::WatermarkText($obj, $Params);
		}
		else
		{
			$result = static::WatermarkImage($obj, $Params);
		};

		return $result;
	}

	/**
	 * @param $obj
	 * @param array $Params
	 * @return bool
	 * @deprecated Use \Bitrix\Main\File\Image::drawWatermark()
	 */
	public static function WatermarkText($obj, $Params = [])
	{
		//prevents destroing outside the function
		static $engine;

		$engine = new File\Image\Gd();
		$engine->setResource($obj);

		$watermark = Image\Watermark::createFromArray($Params);

		return $engine->drawTextWatermark($watermark);
	}

	/**
	 * Creates watermark from image.
	 * @param $obj
	 * @param array $Params
	 * file - abs path to file
	 * alpha_level - opacity
	 * position - of the watermark
	 * @return bool
	 * @deprecated Use \Bitrix\Main\File\Image::drawWatermark()
	 */
	public static function WatermarkImage($obj, $Params = [])
	{
		//prevents destroing outside the function
		static $engine;

		$engine = new File\Image\Gd();
		$engine->setResource($obj);

		$watermark = Image\Watermark::createFromArray($Params);

		return $engine->drawImageWatermark($watermark);
	}

	/**
	 * Reads an image from a file, rotates it clockwise, and saves it to the same file.
	 * @param string $sourceFile
	 * @param float $angle
	 * @return bool
	 */
	public static function ImageRotate($sourceFile, $angle)
	{
		$image = new File\Image($sourceFile);
		if (!$image->load())
		{
			return false;
		}

		$quality = COption::GetOptionString('main', 'image_resize_quality');

		$result = ($image->rotate($angle) && $image->save($quality));

		$image->clear();

		return $result;
	}

	/**
	 * @param string $path
	 * @return false|resource
	 * @deprecated Use \Bitrix\Main\File\Image
	 */
	public static function CreateImage($path)
	{
		$image = new File\Image\Gd($path);

		if ($image->load())
		{
			return $image->getResource();
		}

		return false;
	}

	/**
	 * @param $src
	 * @return array
	 * @deprecated Use \Bitrix\Main\File\Image::getExifData()
	 */
	public static function ExtractImageExif($src)
	{
		return (new File\Image($src))->getExifData();
	}

	/**
	 * @param $contentType
	 * @return string
	 * @deprecated Use Web\MimeType::normalize()
	 */
	public static function NormalizeContentType($contentType)
	{
		return Web\MimeType::normalize($contentType);
	}

	public static function GetContentType($path, $bPhysicalName = false)
	{
		if ($bPhysicalName)
		{
			$pathX = $path;
		}
		else
		{
			$io = CBXVirtualIo::GetInstance();
			$pathX = $io->GetPhysicalName($path);
		}

		$type = "";
		if (function_exists("mime_content_type"))
		{
			$type = mime_content_type($pathX);
		}

		if ($type == "" && function_exists("image_type_to_mime_type"))
		{
			$info = (new File\Image($pathX))->getInfo();
			if ($info)
			{
				$type = $info->getMime();
			}
		}

		if ($type == "")
		{
			$type = Web\MimeType::getByFileExtension(substr($pathX, bxstrrpos($pathX, ".") + 1));
		}

		return $type;
	}

	/**
	 * @param string $path
	 * @param bool $bPhysicalName
	 * @param bool $flashEnabled
	 * @return array|false
	 * @deprecated Use \Bitrix\Main\File\Image::getInfo()
	 */
	public static function GetImageSize($path, $bPhysicalName = false, $flashEnabled = false)
	{
		if (!$bPhysicalName)
		{
			$io = CBXVirtualIo::GetInstance();
			$path = $io->GetPhysicalName($path);
		}

		$image = new File\Image($path);

		if (($info = $image->getInfo($flashEnabled)) !== null)
		{
			return [
				0 => $info->getWidth(),
				1 => $info->getHeight(),
				2 => $info->getFormat(),
				3 => $info->getAttributes(),
				"mime" => $info->getMime(),
			];
		}
		return false;
	}

	public static function DeleteHashAgent()
	{
		global $DB;

		$res = $DB->Query("select distinct h1.FILE_ID
			FROM b_file_hash h1, b_file_hash h2
			WHERE h1.FILE_ID > h2.FILE_ID
				AND h1.FILE_SIZE = h2.FILE_SIZE
				AND h1.FILE_HASH = h2.FILE_HASH
			limit 10000
		");

		$delete = [];
		while ($ar = $res->Fetch())
		{
			$delete[] = $ar['FILE_ID'];
		}

		if (!empty($delete))
		{
			$DB->Query("DELETE FROM b_file_hash WHERE FILE_ID IN (" . implode(',', $delete) . ")");

			return __METHOD__ . '();';
		}

		return '';
	}
}

global $arCloudImageSizeCache;
$arCloudImageSizeCache = [];
