<?
namespace Bitrix\Main\UI\Uploader;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use \Bitrix\Main\UI\FileInputUtility;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Main\Context;

class Package
{
	/* @var string $index Package ID. */
	protected $index;
	/* @var Log $cidLog */
	protected $cidLog = null;
	/* @var Log $log */
	protected $log = null;
	/** @var string $path Path to temp directory. */
	protected $path;
	/** @var string $CID Control exemplar ID. */
	protected $CID;
	/* @var array */
	protected $copies = array(
		"default" => array(
			"width" => null,
			"height" => null
		)
	);
	/* @var Storable */
	protected $storage;
	/** @var array File */
	public $files = array();
	/** @var string */
	public $controlId = "fileUploader";
	/*
	 * @var string $script Url to uploading page for forming url to view
	 * @var array $processTime Time limits
	*/
	protected $processTime = array( // Time limits
		"max" => 30,
		"start" => 0,
		"current" => 0);

	/**
	 * Package constructor.
	 * @param $path
	 * @param $CID
	 * @param $index
	 * @throws ArgumentNullException
	 * @throws NotImplementedException
	 */
	public function __construct($path, $CID, $index)
	{
		if (!is_string($path))
			throw new ArgumentNullException("path");
		$this->path = $path;
		if (!is_string($CID))
			throw new ArgumentNullException("CID");
		else if (strpos($CID, "/") !== false)
			throw new ArgumentException("CID contains forbidden symbol /");
		$this->CID = $CID;
		$this->cidLog = new Log($this->path.$this->getCid().".log");

		if (!is_string($index))
			throw new ArgumentNullException("packageIndex");
		$this->index = $index;

		$this->request = Context::getCurrent()->getRequest();
		if (!$this->request->isPost())
			throw new NotImplementedException("File uploader support only POST method.");

		$post = Context::getCurrent()->getRequest()->getPostList()->toArray();
		$post = $post[Uploader::INFO_NAME];
		$this->log = new Log($this->path.$this->getIndex().".package");
		if (!isset($this->log["CID"]))
		{
			$this->log["CID"] = $this->CID;
			$this->log["pIndex"] = $this->getIndex();
			$this->log["filesCount"] = $post["filesCount"];
			$this->log["files"] = array();
		}

		$this->processTime["max"] = intval(ini_get("max_execution_time")) * 0.75;
		$this->processTime["start"] = time();

		set_time_limit(0);

		return $this;
	}

	/**
	 * Returns package Index.
	 * @return string
	 */
	public function getIndex()
	{
		return $this->index;
	}
	/**
	 * Returns package Log of Control Exemplar.
	 * @return Log
	 */
	public function getCidLog($key = null)
	{
		if (is_null($key))
			return $this->cidLog->getLog();
		$log = $this->cidLog->getLog();
		return $log[$key];
	}
	/**
	 * Returns package Log.
	 * @return array|mixed
	 */
	public function getLog($key = null)
	{
		if (is_null($key))
			return $this->log->getLog();
		$log = $this->log->getLog();
		return $log[$key];
	}

	/**
	 * Returns file array.
	 * @return array
	 */
	public function getFile($id)
	{
		return $this->files[$id];
	}

	/**
	 * @return string
	 */
	public function getCid()
	{
		return $this->CID;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param array $params
	 * @return $this
	 * @throws \Exception
	 */
	public function setStorage(array $params)
	{
		$params = array_change_key_case($params, CASE_LOWER);
		try
		{
			if (array_key_exists("cloud", $params) && $params["cloud"] === true &&
				(\CUtil::Unformat(ini_get("upload_max_filesize")) / 1024 / 1024) >= 5)
				$this->storage = new CloudStorage($params);
		}
		catch(\Exception $e)
		{
		}
		if (!($this->storage instanceof Storable))
		{
			$this->storage = new Storage($params);
		}
		return $this;
	}
	/**
	 * @return Storable
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * @param array $params
	 * @return $this
	 */
	public function setCopies(array $params)
	{
		foreach ($params as $code => $p)
		{
			$this->copies[$code] = $p;
		}
		return $this;
	}
	/**
	 * @return array
	 */
	public function getCopies()
	{
		return $this->copies;
	}
	/**
	 * Decodes and converts keys(!) and values
	 * @param $data
	 * @return array
	 */
	protected static function unescape($data)
	{
		global $APPLICATION;

		if(is_array($data))
		{
			$res = array();
			foreach($data as $k => $v)
			{
				$k = $APPLICATION->ConvertCharset(\CHTTP::urnDecode($k), "UTF-8", LANG_CHARSET);
				$res[$k] = self::unescape($v);
			}
		}
		else
		{
			$res = $APPLICATION->ConvertCharset(\CHTTP::urnDecode($data), "UTF-8", LANG_CHARSET);
		}

		return $res;
	}
	/**
	 * Main function for uploading data.
	 *
	 * @throws NotImplementedException
	 * @return array
	 */
	public function checkPost($fileLimits)
	{
		$unescapedPost = self::unescape(Context::getCurrent()->getRequest()->getPostList()->toArray());
		$postFiles = $unescapedPost[Uploader::FILE_NAME];
		$post = $unescapedPost[Uploader::INFO_NAME];
		if (!(is_array($post) &&
			$this->log["filesCount"] > 0 &&
			$post["filesCount"] == $this->log["filesCount"] &&
			is_array($postFiles) &&
			!empty($postFiles))
		)
			return array();

		$files = Context::getCurrent()->getRequest()->getFileList()->toArray();
		$files = self::unescape($files[Uploader::FILE_NAME]);

		if ($post["type"] != "brief") // If it is IE8
		{
			$error = "";
			if ($this->log["executeStatus"] != "executed")
			{
				$eventName = ($this->cidLog["executeStatus"] === "executed" ? "onUploadIsContinued" : "onUploadIsStarted");
				$this->cidLog["executeStatus"] = "executed";

				foreach(GetModuleEvents(Uploader::EVENT_NAME, $eventName, true) as $event)
				{
					if (ExecuteModuleEventEx($event, array(&$this->log, &$this->cidLog, &$unescapedPost, &$files, &$error)) === false)
						throw new NotImplementedException($error);
				}
				$eventName = "onPackageIsStarted";
			}
			else
			{
				$eventName = "onPackageIsContinued";
			}

			$this->log["executeStatus"] = "executed";
			foreach(GetModuleEvents(Uploader::EVENT_NAME, $eventName, true) as $event)
			{
				if (ExecuteModuleEventEx($event, array(&$this->log, &$this->cidLog, &$unescapedPost, &$files, &$error)) === false)
					throw new NotImplementedException($error);
			}
		}

		$filesRaw = array();
		// $_POST
		foreach($postFiles as $fileID => $file)
		{
			if (is_array($file))
			{
				if (isset($file["restored"]))
				{
					$f = array_merge($file, array("id" => $fileID));
					if ($f["restored"] === "Y")
					{
						File::deleteCache($this, $f);
					}
					else
					{
						$filesRaw[] = $f;
					}
				}
				if (array_key_exists("files", $file) && is_array($file["files"]))
				{
					foreach ($file["files"] as $serviceName => $f)
					{
						if (is_array($f) && array_key_exists("tmp_url", $f))
						{
							/**
							 * $file = array(
							 *  "id" => "file4545454",
							 *  "name" => "Foxes.jpg",
							 *  "~name" => "default",
							 *  "type" => "image/jpg"
							 * );
							 */
							$filesRaw[] = array_merge($f, array(
								"id" => $fileID,
								"code" => $serviceName
							));
						}
					}
				}
			}
		}
		// $_FILES
		if (is_array($files))
		{
			foreach($files["name"] as $fileID => $fileNames)
			{
				if (is_array($fileNames))
				{
					foreach ($fileNames as $fileName => $val)
					{
						$filesRaw[] = array(
							"id" => $fileID,
							"code" => $fileName,
							"tmp_name" => $files["tmp_name"][$fileID][$fileName],
							"type" => $files["type"][$fileID][$fileName],
							"size" => $files["size"][$fileID][$fileName],
							"error" => $files["error"][$fileID][$fileName]
						);
					}
				}
				else
				{
					$filesRaw[] = array(
						"id" => $fileID,
						"code" => $fileNames,
						"tmp_name" => $files["tmp_name"][$fileID],
						"type" => $files["type"][$fileID],
						"size" => $files["size"][$fileID],
						"error" => $files["error"][$fileID]
					);
				}
			}
		}

		$file = null;
		$filesFromLog = is_array($this->log["files"]) ? $this->log["files"] : array();
		$filesOnThisPack = array();
		if ($fileRaw = reset($filesRaw))
		{
			$this->log["uploadStatus"] = "inprogress";
			do
			{
				if (!array_key_exists($fileRaw["id"], $postFiles))
					continue;
				if (!$this->checkTime())
					break;
				if (!array_key_exists($fileRaw["id"], $filesOnThisPack))
				{
					$file = new File($this, array(
						"id" => $fileRaw["id"],
						"name" => $postFiles[$fileRaw["id"]]["name"],
						"type" => $postFiles[$fileRaw["id"]]["type"],
						"size" => $postFiles[$fileRaw["id"]]["size"]
					));
					if (isset($fileRaw["restored"]))
					{
						if ($file->isExecuted())
							$file->setExecuteStatus("none");
						$fileRaw = $file->getFile("default");
						if (empty($fileRaw) || !is_array($fileRaw))
							$file->addError(new Error(\Bitrix\Main\Localization\Loc::getMessage("BXU_FileIsNotRestored"), "BXU350.0"));
					}
					$filesOnThisPack[$fileRaw["id"]] = $file;
				}
				/* @var File $file */
				$file = $filesOnThisPack[$fileRaw["id"]];
				if ($file->hasError())
					continue;
				$result = File::checkFile($fileRaw, $file, $fileLimits + array("path" => $this->getPath()));
				if ($result->isSuccess() && ($result = $file->saveFile($fileRaw, $this->getStorage(), $this->getCopies())) && $result->isSuccess() &&
					$post["type"] != "brief" &&
					$file->isUploaded() &&
					!$file->isExecuted()
				)
				{
					$file->setExecuteStatus("executed");
					$fileArray = $file->toArray();
					foreach(GetModuleEvents(Uploader::EVENT_NAME, "onFileIsUploaded", true) as $event)
					{
						$error = "";
						if (!ExecuteModuleEventEx($event, array($file->getHash(), &$fileArray,
							&$this->log,
							&$this->cidLog,
							&$error)))
						{
							$result->addError(new Error($error, "BXU350.1"));
							break;
						}
					}
					$file->fromArray($fileArray);
				}
				if (!$result->isSuccess())
					$file->addError($result->getErrorCollection()->current());
			} while ($fileRaw = next($filesRaw));
		}

		$response = array();
		/* @var File $file */
		foreach ($filesOnThisPack as $file)
		{
			$response[$file->getId()] = $file->toArray();
			$filesFromLog[$file->getId()] = $response[$file->getId()]["status"] = $file->isUploaded() ? "uploaded" : "inprogress";
			if ($file->hasError())
			{
				$response[$file->getId()]["status"] = "error";
				$response[$file->getId()]["error"] = $file->getErrorMessage();
				$filesFromLog[$file->getId()] = "error";
			}
		}
		$this->files = $filesOnThisPack;
		$this->log["files"] = $filesFromLog;
		$declaredFiles = (int) $this->log["filesCount"];

		$cnt = 0;
		foreach ($filesFromLog as $status)
			$cnt += ($status == "uploaded" || $status == "error" ? 1 : 0);

		if ($declaredFiles > 0 && $declaredFiles == $cnt)
		{
			if ($post["type"] != "brief") // If it is IE8
			{
				$this->log["uploadStatus"] = "uploaded";
				$error = "";
				foreach(GetModuleEvents(Uploader::EVENT_NAME, "onPackageIsFinished", true) as $event)
				{
					if (ExecuteModuleEventEx($event, array(&$this->log, &$this->cidLog, &$unescapedPost, &$response, &$error)) === false)
						throw new NotImplementedException($error);
				}
			}
		}
		return $response;
	}

	/**
	 * @return bool
	 */
	public function checkTime()
	{
		if ($this->processTime["max"] > 0)
		{
			$res = (getmicrotime() - START_EXEC_TIME);
			return $res < $this->processTime["max"];
		}
		return true;
	}

	/**
	 * this function just merge 2 arrays with a lot of deep keys
	 * array_merge replaces keys in second level and deeper
	 * array_merge_recursive multiplies similar keys
	 * @param $res
	 * @param $res2
	 * @return array
	 */
	public static function merge($res, $res2)
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
}

?>