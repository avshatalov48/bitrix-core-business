<?php
namespace Bitrix\Main\UI\Uploader;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

interface Storable
{
	/**
	 * @param array $file
	 * @return \Bitrix\Main\Result
	 */
	public function copy($path, array $file);
}

class Storage implements Storable
{
	protected $path = "";

	protected static $lastId = null;
	protected static $descriptor = null;

	function __construct()
	{
	}

	private static function flush()
	{
		if (!is_null(self::$descriptor))
		{
			@fflush(self::$descriptor);
			@flock(self::$descriptor, LOCK_UN);
			@fclose(self::$descriptor);
			self::$descriptor = null;
		}
	}
	function __destruct()
	{
		self::flush();
	}

	/**
	 * @param array $path
	 * @param array $file
	 * @return Result
	 */
	public function copy($path, array $file)
	{
		$result = new Result();
		$directory = \CBXVirtualIo::GetInstance()->getDirectory($path);

		$newFile = $directory->GetPathWithName()."/".$file["code"];
		$result->setData(array(
			"size" => $file["size"],
			"tmp_name" => $newFile,
			"type" => $file["type"]
		));
		if (substr($newFile, -strlen($file['tmp_name'])) == $file['tmp_name'])
		{

		}
		else if (!$directory->create())
		{
			$result->addError(new Error(Loc::getMessage("BXU_TemporaryDirectoryIsNotCreated"), "BXU347.1"));
		}
		elseif (array_key_exists('tmp_url', $file))
		{
			if (!((!file_exists($newFile) || @unlink($newFile)) && File::http()->download($file['tmp_url'], $newFile) !== false))
				$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.1"));
		}
		else if (!file_exists($file['tmp_name']))
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.1"));
		}
		else if (!file_exists($file['tmp_name']))
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.2"));
		}
		elseif (array_key_exists('start', $file))
		{
			$result = $this->copyChunk($newFile, $file);
		}
		else if (!((!file_exists($newFile) || @unlink($newFile)) && move_uploaded_file($file['tmp_name'], $newFile)))
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.4"));
		}
		else
		{
			$result->setData(array(
				"size" => filesize($newFile),
				"tmp_name" => $newFile,
				"type" => ($file["type"] ?: \CFile::GetContentType($newFile))
			));
		}
		return $result;
	}
	/**
	 * @param string $path
	 * @param array $chunk
	 * @return Result
	 */
	public function copyChunk($path, array $chunk)
	{
		$result = new Result();
		if (is_null(self::$descriptor) || self::$lastId != $path)
		{
			self::flush();
			self::$descriptor = $fdst = fopen($path, 'cb');
			@chmod($path, BX_FILE_PERMISSIONS);
		}
		else
			$fdst = self::$descriptor;

		if (!$fdst)
		{
			$result->addError(new Error(Loc::getMessage("BXU_TemporaryFileIsNotCreated"), "BXU349.1"));
		}
		else if (!flock($fdst, LOCK_EX))
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsLocked"), "BXU349.100"));
		}
		else
		{
			$buff = 4096;
			if (($fsrc = fopen($chunk['tmp_name'], 'r')) && $fsrc)
			{
				fseek($fdst, $chunk["start"], SEEK_SET);
				while(!feof($fsrc) && ($data = fread($fsrc, $buff)))
				{
					if ($data !== '' && $data !== false)
					{
						fwrite($fdst, $data);
					}
					else
					{
						$result->addError(new Error(Loc::getMessage("BXU_FilePartCanNotBeRead"), "BXU349.2"));
						break;
					}
				}
				fclose($fsrc);
				unlink($chunk['tmp_name']);
			}
			else
			{
				$result->addError(new Error(Loc::getMessage("BXU_FilePartCanNotBeOpened"), "BXU349.3"));
			}
		}
		if (!$result->isSuccess())
		{
			self::flush();
		}

		$result->setData(array(
			"size" => $chunk["~size"],
			"tmp_name" => $path,
			"type" => $chunk["type"]
		));

		return $result;
	}

	public function flushDescriptor()
	{
		self::flush();
	}
}

class CloudStorage extends Storage implements Storable
{
	protected $moduleId = "main";
	function __construct($params)
	{
		if(!Loader::includeModule("clouds"))
			throw new \Bitrix\Main\NotImplementedException();
		if (is_array($params))
		{
			$params = array_change_key_case($params, CASE_LOWER);
			$this->moduleId = ($params["moduleId"] ?: $this->moduleId);
		}
	}

	/**
	 * @param $file
	 * @return \CCloudStorageBucket|null
	 */
	private function findBucket($file)
	{
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$bucket = \CCloudStorage::findBucketForFile(array('FILE_SIZE' => $file['size'], 'MODULE_ID' => $this->moduleId), $file["name"]);
		if(!$bucket || !$bucket->init())
		{
			return null;
		}
		return $bucket;
	}
	protected function moveIntoCloud(\CCloudStorageBucket $bucket, $path, $file)
	{
		$result = new Result();
		$absPath = \CTempFile::getAbsoluteRoot();
		$relativePath = $path;
		if (substr($path, 0, strlen($absPath)) == $absPath && strpos($path, "/bxu/") > 0)
			$relativePath = substr($path, strpos($path, "/bxu/"));
		$subdir = explode("/", trim($relativePath, "/"));
		$filename = array_pop($subdir);
		if (!isset($_SESSION["upload_tmp"]))
		{
			$_SESSION["upload_tmp"] = array();
		}

		if (!isset($_SESSION["upload_tmp"][$path]))
		{
			$relativePath = $_SESSION["upload_tmp"][$path] =\CCloudTempFile::GetDirectoryName($bucket, 12).$filename;
		}
		else
		{
			$relativePath = $_SESSION["upload_tmp"][$path];
		}

		$upload = new \CCloudStorageUpload($relativePath);
		$finished = false;
		if(!$upload->isStarted() && !$upload->start($bucket->ID, $file["size"], $file["type"]))
		{
			$result->addError(new Error("File transfer into Cloud is failed.", "BXU346.2"));
		}
		else if (!($fileContent = \Bitrix\Main\IO\File::getFileContents($file["tmp_name"])))
		{
			$result->addError(new Error("It is impossible to get file content.", "BXU346.3"));
		}
		else
		{
			$fails = 0;
			$success = false;
			while ($upload->hasRetries())
			{
				if (method_exists($upload, "part") && $upload->part($fileContent, $file["number"]) ||
					!method_exists($upload, "part") && $upload->next($fileContent))
				{
					$success = true;
					break;
				}
				$fails++;
			}
			if (!$success)
			{
				$result->addError(new Error("Could not upload file for {$fails} times.", "BXU346.4"));
			}
			else if (isset($file["count"]) && $upload->GetPartCount() < $file["count"])
			{
			}
			else if (!$upload->finish())
			{
				$result->addError(new Error("Could not resume file transfer.", "BXU346.5"));
			}
			else
			{
				$finished = true;
			}
		}

		$result->setData(array(
			"tmp_name" => $bucket->getFileSRC($relativePath),
			"size" => $file["size"],
			"type" => $file["type"],
			"finished" => $finished
		));
		return $result;
	}

	public function copy($path, array $file)
	{
		$result = parent::copy($path, $file);
		if ($result->isSuccess() && !array_key_exists('start', $file))
		{
			$res = $result->getData();
			$file["tmp_name"] = $res["tmp_name"];
			$file["size"] = $res["size"];
			$file["type"] = $res["type"];
			$img = \CFile::GetImageSize($file["tmp_name"]);
			$file["width"] = $img[0];
			$file["height"] = $img[1];
			if ($bucket = $this->findBucket($file))
			{
				unset($file["count"]);
				if (($r = $this->moveIntoCloud($bucket, $file["tmp_name"], $file)) && $r->isSuccess())
				{
					$res = $r->getData();
					$result->setData(array(
						"size" => $file["size"],
						"file_size" => $file["size"],
						"tmp_name" => $res["tmp_name"],
						"type" => $file["type"],
						"width" => $file["width"],
						"height" => $file["height"],
						"bucketId" => $bucket->ID
					));
				}
				if ($r->getErrors())
				{
					$result->addErrors($r->getErrors());
				}
				@unlink($path);
			}
		}
		return $result;
	}
	/**
	 * @param string $path
	 * @param array $file
	 * @return Result
	 */
	public function copyChunk($path, array $file)
	{
		$res = "";
		if (substr($file["~type"], 0, 6) == "image/")
		{
			$result = parent::copyChunk($path, $file);

			if ($result->isSuccess())
			{
				$file["chunks"][$file["chunkId"]] = "does not matter"; // if picture is already uploaded
				if (count($file["chunks"]) == $file["count"])
				{
					parent::__destruct();
					$file["type"] = $file["~type"];
					$file["tmp_name"] = $path;
					$file["size"] = filesize($path);
					$img = \CFile::GetImageSize($file["tmp_name"]);
					$file["width"] = $img[0];
					$file["height"] = $img[1];

					if ($bucket = $this->findBucket($file))
					{
						unset($file["count"]);
						if (($r = $this->moveIntoCloud($bucket, $path, $file)) && $r->isSuccess())
						{
							$res = $r->getData();
							$result->setData(array(
								"size" => $file["size"],
								"file_size" => $file["size"],
								"tmp_name" => $res["tmp_name"],
								"type" => $file["type"],
								"width" => $file["width"],
								"height" => $file["height"],
								"bucketId" => $bucket->ID
							));
						}
						else
						{
							$result->addError($r->getErrorCollection()->current());
						}
						@unlink($path);
					}
				}
			}
			return $result;
		}

		if ($bucket = $this->findBucket(array(
			"name" => $file["~name"],
			"size" => $file["~size"],
		)))
		{
			if (($result = $this->moveIntoCloud($bucket, $path, array_merge($file, array("size" => $file["~size"])))) &&
				$result->isSuccess() && ($res = $result->getData()) && $res["finished"] === true)
			{
				$res = $result->getData();
				$result->setData(array(
					"size" => $file["~size"],
					"file_size" => $file["~size"],
					"tmp_name" => $res["tmp_name"],
					"type" => $file["type"],
					"bucketId" => $bucket->ID
				));
			}
		}
		else
		{
			$result = parent::copyChunk($path, $file);
		}
		return $result;
	}

	/**
	 * Checks storage.
	 * @param int $id
	 * @return bool
	 */
	public static function checkBucket($id)
	{
		$res = false;
		if(Loader::includeModule("clouds"))
		{
			$r = \CCloudStorageBucket::GetAllBuckets();
			$res = (is_array($r) && array_key_exists($id, $r));
		}
		return $res;
	}
}