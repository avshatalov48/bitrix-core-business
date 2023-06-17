<?php
namespace Bitrix\Main\UI\Uploader;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Result;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\Web;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

class File
{
	/** @var Package */
	protected $package;
	/** @var array */
	protected $data = array();
	/** @var ErrorCollection */
	protected $errorCollection;
	/** @var HttpClient */
	protected static $http = null;

	/**
	 * File constructor.
	 * @param Package $package Package for file.
	 * @param array $file File array.
	 */
	public function __construct($package, array $file)
	{
		$hash = self::initHash(array("id" => $file["id"], "name" => $file["name"]));
		$this->data = array(
			"hash" => $hash,
			"id" => $file["id"],
			"uploadStatus" => null,
			"executeStatus" => null,
			"name" => $file["name"],
			"type" => $file["type"],
			"size" => $file["size"],
			"files" => array(
				"default" => array()
			)
		) + $file;

		$this->package = $package;

		if (FileInputUtility::instance()->checkFile($this->package->getCid(), $hash))
		{
			$this->data = self::getFromCache($hash, $this->package->getPath());
			$eventName = "onFileIsContinued";
		}
		else
		{
			$eventName = "onFileIsStarted";
		}
		FileInputUtility::instance()->registerFile($this->package->getCid(), $this->getHash());
		$this->errorCollection = new ErrorCollection();

		foreach(GetModuleEvents(Uploader::EVENT_NAME, $eventName, true) as $event)
		{
			$error = "";
			if (!ExecuteModuleEventEx($event, array($this->getHash(), &$this->data, &$error)))
			{
				$this->addError(new Error($error, "BXU350.1"));
				break;
			}
		}
	}
	/**
	 * @param array $file File array("id" => ... ).
	 * @return string
	 */
	public static function initHash($file = array())
	{
		if (empty($file["id"]))
			return md5($file["name"]);
		if (preg_match("/^file([0-9]+)$/", $file["id"]))
			return $file["id"];
		return md5($file["id"]);
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->data["id"];
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->data["hash"];
	}
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data["name"];
	}
	/**
	 * @return integer
	 */
	public function getSize()
	{
		return $this->data["size"];
	}
	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->data["type"];
	}
	/**
	 * Returns file data array.
	 * @param string $code File code like "default", "real_picture".
	 * @return array|null
	 */
	public function getFile($code)
	{
		return $this->data["files"][$code];
	}
	/**
	 * Sets file data in file with code $code and saves changes into cache.
	 * @param string $code File code like "default", "real_picture".
	 * @param array $data Array("name" => "", "code" => ..., "type" => ..., "uploadStatus" => "inprogress",...).
	 * @return void
	 */
	public function setFile($code, $data)
	{
		$this->data["files"][$code] = $data;
		$this->saveLog();
	}

	/**
	 * Saves file on drive.
	 * @param array $file Array("name" => "", "code" => ..., "type" => ..., "uploadStatus" => "inprogress",...).
	 * @param Storable $storage .
	 * @param array $copies Array("small" => array("width" => 100, "height" => 100)).
	 * @return Result
	 */
	public function saveFile(&$file, Storable $storage, array $copies)
	{
		$result = new Result();
		$code = $file["code"];

		if ($code !== "default" && !array_key_exists($code, $copies))
		{
			$result->addError(new Error("The copy name is not in the list."));
		}
		else if ($this->isUploaded())
		{
			return $result;
		}
		else if (isset($file["chunkId"]))
		{
			$info = $this->getFile($code);
			if (empty($info))
				$info = array(
					"name" => $this->getName(),
					"code" => $code,
					"type" => $file["type"],
					"uploadStatus" => "inprogress",
					"count" => $file["count"],
					"chunks" => array());
			$file["chunks"] = $info["chunks"];
			$r = $storage->copy($this->package->getPath().$this->getHash(), $file);
			if (!$r->isSuccess())
			{
				$result->addError($r->getErrorCollection()->current());
			}
			else
			{
				$info["chunks"][$file["chunkId"]] = array(
					"size" => $file["size"],
					"number" => $file["number"],
					"start" => $file["start"],
					"error" => $file["error"]
				);
				$file["uploadStatus"] = "uploaded";
				$data = $r->getData();
				if (count($info["chunks"]) == $info["count"])
				{
					$data["name"] = $this->getName();
					$data["code"] = $info["code"];
					$data["uploadStatus"] = "uploaded";
					$info = $data + array_intersect_key($info, ["width" => "", "height" => ""]);
				}
				else
				{
					$info += array_intersect_key($data, ["width" => "", "height" => ""]);
				}
				$this->setFile($code, $info);
				$storage->flushDescriptor();
			}
		}
		else
		{
			$r = $storage->copy($this->package->getPath().$this->getHash(), $file);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				$data["name"] = $this->getName();
				$data["code"] = $code;
				$data["uploadStatus"] = "uploaded";
				$this->setFile($code, $data);
			}
			else
			{
				$result->addError($r->getErrorCollection()->current());
			}
		}
		if ($result->isSuccess())
		{
			$info = $this->getFile($code);
			if ($info["uploadStatus"] == "uploaded")
			{
				$info["url"] = $this->getUrl("view", $code);
				$info["~url"] = $this->getUrl("view", $code, \COption::GetOptionString("main.fileinput", "entryPointUrl", "/bitrix/tools/upload.php"));
				$info["sizeFormatted"] = \CFile::FormatSize($info["size"]);
				foreach ($this->data["files"] as $k => $f)
				{
					if ($f["uploadStatus"] == "uploaded")
						unset($copies[$k]);
				}
				if (empty($copies))
					$this->setUploadStatus("uploaded");
			}
			$this->setFile($code, $info);
		}
		return $result;
	}

	/**
	 * Saves changes into cache.
	 * @return void
	 */
	public function saveLog()
	{
		static $lastSaved = null;
		if ($lastSaved != $this->data)
		{
			$lastSaved = self::arrayWalkRecursive($this->data);
			self::setIntoCache($this->data["hash"], $this->package->getPath(), $lastSaved);
		}
	}

	/**
	 * Just function to prepare data array for saving on drive.
	 * @param array $array Data array.
	 * @return array
	 */
	protected static function arrayWalkRecursive(array $array)
	{
		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$array[$k] = self::arrayWalkRecursive($v);
			}
			else if (is_object($v))
			{
				unset($array[$k]);
			}
		}
		return $array;
	}

	/**
	 * Adds error into errorConnection
	 * @param Error $error
	 * @return void
	 */
	public function addError(Error $error)
	{
		$this->errorCollection->add(array($error));
	}

	/**
	 * Checks if errorCollection has errors.
	 * @return bool
	 */
	public function hasError()
	{
		return !$this->errorCollection->isEmpty();
	}

	/**
	 * Returns error collection.
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errorCollection;
	}

	/**
	 * Returns error message.
	 * @return string
	 */
	public function getErrorMessage()
	{
		$m = [];
		for ($this->errorCollection->rewind(); $this->errorCollection->valid(); $this->errorCollection->next())
		{
			/** @var Error $error */
			$error = $this->errorCollection->current();
			$m[] = ($error->getMessage()?:$error->getCode());
		}
		return implode("", $m);
	}
	/**
	 * Return data array from cache.
	 * @param string $hash
	 * @param string $path
	 * @return array|false
	 */
	protected static function getFromCache($hash, $path)
	{
		return unserialize(\CBXVirtualIo::GetInstance()->GetFile($path.$hash."/.log")->GetContents(), ['allowed_classes' => false]);
	}

	/**
	 * @param Package $package
	 * @param array $file
	 * @return void
	 */
	public static function deleteCache(Package $package, array $file)
	{
		$hash = self::initHash($file);
		if (FileInputUtility::instance()->checkFile($package->getCid(), $hash))
		{
			$file = \CBXVirtualIo::GetInstance()->GetFile($package->getPath().$hash."/.log");
			if ($file->IsExists())
				$file->unlink();
			FileInputUtility::instance()->unRegisterFile($package->getCid(), $hash);
		}
	}

	/**
	 * Saves serialized data on disk.
	 * @param string $hash
	 * @param string $path
	 * @param array $data
	 * @return void
	 */
	protected static function setIntoCache($hash, $path, $data)
	{
		$io = \CBXVirtualIo::GetInstance();
		$directory = $io->GetDirectory($path.$hash);
		if ($directory->Create())
			$io->GetFile($path.$hash."/.log")->PutContents(serialize($data));
	}
	/**
	 * this function just merge 2 arrays with a lot of deep keys
	 * array_merge replaces keys in second level and deeper
	 * array_merge_recursive multiplies similar keys
	 * @param $res
	 * @param $res2
	 * @return array
	 */
	static function merge($res, $res2)
	{
		$res = is_array($res) ? $res : array();
		$res2 = is_array($res2) ? $res2 : array();
		foreach ($res2 as $key => $val)
		{
			if (array_key_exists($key, $res) && is_array($val))
				$res[$key] = self::merge($res[$key], $val);
			else
				$res[$key] = $val;
		}
		return $res;
	}

	/**
	 * Sets upload status.
	 * @param string $status
	 * @return void
	 */
	public function setUploadStatus($status)
	{
		$this->data["uploadStatus"] = $status;
		$this->saveLog();
	}

	/**
	 * Checks if file is uploaded.
	 * @return bool
	 */
	public function isUploaded()
	{
		return ($this->data["uploadStatus"] === "uploaded");
	}

	/**
	 * Sets executed status.
	 * @param string $status
	 * @return void
	 */
	public function setExecuteStatus($status)
	{
		$this->data["executeStatus"] = $status;
		$this->saveLog();
	}

	/**
	 * Check if file is executed.
	 * @return bool
	 */
	public function isExecuted()
	{
		return ($this->data["executeStatus"] === "executed");
	}

	/**
	 * Returns file whole data.
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}

	/**
	 * Restore data from array and saves into cache.
	 * @param array $data
	 * @return void
	 */
	public function fromArray(array $data)
	{
		$data["id"] = $this->data["id"];
		$data["hash"] = $this->data["hash"];
		$this->data = $data;
		$this->saveLog();
	}

	/**
	 * @param string $cid Control exemplar ID.
	 * @param string $hash File ID.
	 * @param string $path Path to temporary directory.
	 * @return bool
	 */
	public static function deleteFile($cid, $hash, $path)
	{
		if (FileInputUtility::instance()->unRegisterFile($cid, $hash))
		{
			$io = \CBXVirtualIo::GetInstance();
			$directory = $io->GetDirectory($path.$hash);
			$res = $directory->GetChildren();
			foreach($res as $file)
				$file->unlink();
			$directory->rmdir();

			return true;
		}
		return false;
	}
	/**
	 * @param string $cid Control exemplar ID.
	 * @param string $hash File ID.
	 * @param string $path Path to temporary directory.
	 * @return void
	 */
	public static function viewFile($cid, $hash, $path)
	{
		$file = false;
		$copy = "";
		if (mb_strpos($hash, "_") > 0)
		{
			$copy = explode("_", $hash);
			$hash = $copy[0]; $copy = $copy[1];
		}
		$copy = ($copy ?:"default");
		if (FileInputUtility::instance()->checkFile($cid, $hash))
		{
			$file = self::getFromCache($hash, $path);
			$file = $file["files"][$copy];
		}

		if (is_array($file))
		{
			$docRoot = Application::getInstance()->getContext()->getServer()->getDocumentRoot();
			if (mb_strpos(\CTempFile::GetAbsoluteRoot(), $docRoot) === 0)
				\CFile::ViewByUser($file, array("content_type" => $file["type"]));
			else
				self::view($file, array("content_type" => $file["type"]));
		}
	}
	/**
	 * @param string $act
	 * @param string $copy
	 * @return string
	 */
	private function getUrl($act = "view", $copy = "default", $url = null)
	{
		$url = is_null($url) ? Context::getCurrent()->getRequest()->getRequestUri() : $url;

		$uri = (new Uri($url))
			->addParams([
				Uploader::INFO_NAME => [
					"CID" => $this->package->getCid(),
					"mode" => $act,
					"hash" => $this->getHash(),
					"copy" => $copy
				],
			])
			->toAbsolute()
		;

		return $uri->getUri();
	}

	public static function getUrlFromRelativePath($tmpName)
	{
		$io = \CBXVirtualIo::GetInstance();
		if (($tempRoot = \CTempFile::GetAbsoluteRoot()) && ($filePath = $tempRoot.$tmpName) && $io->FileExists($filePath))
		{
			$f = $io->GetFile($filePath);
			$directory = $io->GetDirectory($f->GetPath());
			$hash = $directory->GetName();
			if (($cache = self::getFromCache($hash, $directory->GetPath()."/")) && is_array($cache) &&
				array_key_exists("files", $cache) && array_key_exists($f->getName(), $cache["files"]))
			{
				return $cache["files"][$f->getName()]["~url"];
			}
		}
		return false;
	}

	/**
	 * @param $error
	 * @return string
	 */
	public static function getUploadErrorMessage($error)
	{
		switch ($error)
		{
			case UPLOAD_ERR_INI_SIZE:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_INI_SIZE");
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_FORM_SIZE");
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_PARTIAL");
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_NO_FILE");
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_NO_TMP_DIR");
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_CANT_WRITE");
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = Loc::getMessage("BXU_UPLOAD_ERR_EXTENSION");
				break;
			default:
				$message = 'Unknown uploading error ['.$error.']';
				break;
		}
		return $message;
	}

	/**
	 * @return HttpClient
	 */
	public static function http()
	{
		if (is_null(static::$http))
		{
			static::$http = new HttpClient;
			static::$http->setPrivateIp(false);
		}
		return static::$http;
	}

	/**
	 * @param $file
	 * @param File $f
	 * @param $params
	 * @return Result
	 */
	public static function checkFile(&$file, File $f, $params)
	{
		$result = new Result();
		if ($file["error"] > 0)
			$result->addError(new Error(File::getUploadErrorMessage($file["error"]), "BXU347.2.9".$file["error"]));
		else if (array_key_exists("tmp_url", $file))
		{
			$url = new Uri($file["tmp_url"]);
			if ($url->getHost() == '' && ($tmp = \CFile::MakeFileArray($url->getPath())) && is_array($tmp))
			{
				$file = array_merge($tmp, $file);
			}
			else if ($url->getHost() <> '' &&
				self::http()->query("HEAD", $file["tmp_url"]) &&
				self::http()->getStatus() == "200")
			{
				$file = array_merge($file, array(
					"size" => self::http()->getHeaders()->get("content-length"),
					"type" => self::http()->getHeaders()->get("content-type")
				));
			}
			else
			{
				$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2"));
			}
		}
		else if (isset($file['bucketId']) && !CloudStorage::checkBucket($file['bucketId']))
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.8"));
		}
		else if (!isset($file['bucketId']) && (!file_exists($file['tmp_name']) || (
				(mb_substr($file["tmp_name"], 0, mb_strlen($params["path"])) !== $params["path"]) &&
				!is_uploaded_file($file['tmp_name'])
			))
		)
		{
			$result->addError(new Error(Loc::getMessage("BXU_FileIsNotUploaded"), "BXU347.2.7"));
		}

		if ($result->isSuccess())
		{
			$params["uploadMaxFilesize"] = $params["uploadMaxFilesize"] ?? 0;
			$params["allowUploadExt"] = $params["allowUploadExt"] ?? false;
			$params["allowUpload"] = $params["allowUpload"] ?? null; // 'I' - image, 'F' - files with ext in $params["allowUploadExt"]

			if ($params["uploadMaxFilesize"] > 0 && $f->getSize() > $params["uploadMaxFilesize"])
			{
				$error = GetMessage("FILE_BAD_SIZE")." (".\CFile::FormatSize($f->getSize()).").";
			}
			else
			{
				$name = $f->getName();
				$ff = array_merge($file, array("name" => $name));
				if ($params["allowUpload"] === "I")
				{
					$error = \CFile::CheckFile($ff, $params["uploadMaxFilesize"], "image/", \CFile::GetImageExtensions());
				}
				elseif ($params["allowUpload"] === "F" && $params["allowUploadExt"])
				{
					$error = \CFile::CheckFile($ff, $params["uploadMaxFilesize"], false, $params["allowUploadExt"]);
				}
				else
				{
					$error = \CFile::CheckFile($ff, $params["uploadMaxFilesize"]);
				}
			}

			if ($error !== "")
				$result->addError(new Error($error, "BXU347.3"));
		}
		if (preg_match("/^(.+?)\\.ch(\\d+)\\.(\\d+)\\.chs(\\d+)$/", $file["code"], $matches))
		{
			$file["code"] = $matches[1];
			$file["number"] = $matches[2];
			$file["start"] = $matches[3];
			$file["count"] = $matches[4];
			$file["chunkId"] = self::getChunkKey($file["count"], $file["number"]);
		}
		$file["~size"] = $f->getSize();
		$file["~name"] = $f->getName();
		$file["~type"] = $f->getType();

		return $result;
	}
	/**
	 * Generates hash from info about file
	 * @param $chunksCount
	 * @param $chunkNumber
	 * @return string
	 */
	protected static function getChunkKey($chunksCount, $chunkNumber)
	{
		$chunksCount = max(ceil(log10($chunksCount)), 4);
		return "p".str_pad($chunkNumber, $chunksCount, "0", STR_PAD_LEFT);
	}

	/**
	 * @param array $source Source file.
	 * @param array $dest Destination File.
	 * @param array $canvasParams Array("width" => 100, "height" => 100).
	 * @param array $watermarkParams Array("position" => "top", "type" => "text", "text" => "Bla-bla", "font" => "", "color" => "red").
	 * @return array
	 */
	public static function createCanvas($source, $dest, $canvasParams = array(), $watermarkParams = array())
	{
		$watermark = (array_key_exists("watermark", $source) ? array() : $watermarkParams);
		if (\CFile::ResizeImageFile(
			$source["tmp_name"],
			$dest["tmp_name"],
			$canvasParams,
			BX_RESIZE_IMAGE_PROPORTIONAL,
			$watermark,
			$canvasParams["quality"],
			array()
		))
		{
			$dest = array_merge($source, $dest);
			if (array_key_exists("watermark", $source) || !empty($watermarkParams))
				$dest["watermark"] = true;
		}
		else
			$dest["error"] = 348;
		$dest["size"] = filesize($dest["tmp_name"]);
		$dest["type"] = $dest["type"] ?: \CFile::GetContentType($dest["tmp_name"]);
		$dest["sizeFormatted"] = \CFile::FormatSize($dest["size"]);

		return $dest;
	}

	/**
	 * @param array $fileData
	 * @param array $options
	 * @return bool|mixed
	 */
	public static function view(array $fileData, $options = array())
	{
		if (!array_key_exists("tmp_name", $fileData) || empty($fileData["tmp_name"]))
			return false;

		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$fastDownload = (\COption::GetOptionString('main', 'bx_fast_download', 'N') == 'Y');

		$attachment_name = "";
		$content_type = (array_key_exists("type", $fileData) && !empty($fileData["type"]) ? $fileData["type"] : "");
		$cache_time = 10800;
		$fromClouds = false;
		$filetime = 0;

		if(is_array($options))
		{
			if(isset($options["content_type"]))
				$content_type = $options["content_type"];
			if(isset($options["specialchars"]))
				$specialchars = $options["specialchars"];
			if(isset($options["force_download"]))
				$force_download = $options["force_download"];
			if(isset($options["cache_time"]))
				$cache_time = intval($options["cache_time"]);
			if(isset($options["attachment_name"]))
				$attachment_name = $options["attachment_name"];
		}

		if($cache_time < 0)
			$cache_time = 0;

		$name = str_replace(array("\n", "\r"), '', $fileData["name"]);

		if ($attachment_name)
			$attachment_name = str_replace(array("\n", "\r"), '', $attachment_name);
		else
			$attachment_name = $name;

		$content_type = Web\MimeType::normalize($content_type);

		$src = null;
		$file = null;
		if (mb_strpos($fileData["tmp_name"], \CTempFile::GetAbsoluteRoot()) === 0)
		{
			$file = new \Bitrix\Main\IO\File($fileData["tmp_name"]);
			try
			{
				$src = $file->open(\Bitrix\Main\IO\FileStreamOpenMode::READ);
			}
			catch(\Bitrix\Main\IO\IoException $e)
			{
				return false;
			}
			$filetime = $file->getModificationTime();
		}
		else
		{
			$fromClouds = true;
		}

		$APPLICATION->RestartBuffer();

		$cur_pos = 0;
		$filesize = $fileData["size"];
		$size = $filesize-1;
		$server = Application::getInstance()->getContext()->getServer();
		$p = $server->get("HTTP_RANGE") && mb_strpos($server->get("HTTP_RANGE"), "=");
		if(intval($p)>0)
		{
			$bytes = mb_substr($server->get("HTTP_RANGE"), $p + 1);
			$p = mb_strpos($bytes, "-");
			if($p !== false)
			{
				$cur_pos = floatval(mb_substr($bytes, 0, $p));
				$size = floatval(mb_substr($bytes, $p + 1));
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

		if ($server->getRequestMethod() == "HEAD")
		{
			\CHTTP::SetStatus("200 OK");
			header("Accept-Ranges: bytes");
			header("Content-Type: ".$content_type);
			header("Content-Length: ".($size-$cur_pos+1));

			if($filetime > 0)
				header("Last-Modified: ".date("r", $filetime));
		}
		else
		{
			$lastModified = '';
			if($cache_time > 0)
			{
				//Handle ETag
				$ETag = md5($fileData["tmp_name"].$filesize.$filetime);
				if ($server->get("HTTP_IF_NONE_MATCH") === $ETag)
				{
					\CHTTP::SetStatus("304 Not Modified");
					header("Cache-Control: private, max-age=".$cache_time.", pre-check=".$cache_time);
					die();
				}
				header("ETag: ".$ETag);

				//Handle Last Modified
				if($filetime > 0)
				{
					$lastModified = gmdate('D, d M Y H:i:s', $filetime).' GMT';
					if ($server->get("HTTP_IF_NONE_MATCH") === $lastModified)
					{
						\CHTTP::SetStatus("304 Not Modified");
						header("Cache-Control: private, max-age=".$cache_time.", pre-check=".$cache_time);
						die();
					}
				}
			}

			$utfName = Uri::urnEncode($attachment_name, "UTF-8");
			$translitName = \CUtil::translit($attachment_name, LANGUAGE_ID, array(
				"max_len" => 1024,
				"safe_chars" => ".",
				"replace_space" => '-',
				"change_case" => false,
			));

			//Disable zlib for old versions of php <= 5.3.0
			//it has broken Content-Length handling
			if(ini_get('zlib.output_compression'))
				ini_set('zlib.output_compression', 'Off');

			if($cur_pos > 0)
				\CHTTP::SetStatus("206 Partial Content");
			else
				\CHTTP::SetStatus("200 OK");

			header("Content-Type: ".$content_type);
			header("Content-Disposition: attachment; filename=\"".$translitName."\"; filename*=utf-8''".$utfName);
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".($size-$cur_pos+1));
			if(is_resource($src))
			{
				header("Accept-Ranges: bytes");
				header("Content-Range: bytes ".$cur_pos."-".$size."/".$filesize);
			}

			if($cache_time > 0)
			{
				header("Cache-Control: private, max-age=".$cache_time.", pre-check=".$cache_time);
				if($filetime > 0)
					header('Last-Modified: '.$lastModified);
			}
			else
			{
				header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
			}

			header("Expires: 0");
			header("Pragma: public");

			// Download from front-end
			if($fastDownload && ($fromClouds || mb_strpos($fileData["tmp_name"], Application::getInstance()->getContext()->getServer()->getDocumentRoot()) === 0))
			{
				if($fromClouds)
				{
					$filename = preg_replace('~^(http[s]?)(\://)~i', '\\1.' , $fileData["tmp_name"]);
					$cloudUploadPath = \COption::GetOptionString('main', 'bx_cloud_upload', '/upload/bx_cloud_upload/');
					header('X-Accel-Redirect: '.$cloudUploadPath.$filename);
				}
				else
				{
					header('X-Accel-Redirect: '.\Bitrix\Main\Text\Encoding::convertEncoding($fileData["tmp_name"], SITE_CHARSET, "UTF-8"));
				}
			}
			else if ($src)
			{
				session_write_close();
				$file->seek($cur_pos);
				while(!feof($src) && ($cur_pos <= $size))
				{
					$bufsize = 131072; //128K
					if($cur_pos + $bufsize > $size)
						$bufsize = $size - $cur_pos + 1;
					$cur_pos += $bufsize;
					echo fread($src, $bufsize);
				}
				$file->close();
			}
			else
			{
				$src = new \Bitrix\Main\Web\HttpClient();
				$fp = fopen("php://output", "wb");
				$src->setOutputStream($fp);
				$src->get($fileData["tmp_name"]);
			}
		}
		\CMain::FinalActions();
		die();
	}}