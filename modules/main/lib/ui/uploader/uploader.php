<?
namespace Bitrix\Main\UI\Uploader;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\NotImplementedException;
use \Bitrix\Main\UI\FileInputUtility;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Context;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main;
Loc::loadMessages(__FILE__);
Loader::registerAutoLoadClasses(
	"main",
	array(
		"bitrix\\main\\ui\\uploader\\storage" => "lib/ui/uploader/storage.php",
		"bitrix\\main\\ui\\uploader\\cloudstorage" => "lib/ui/uploader/storage.php"
	));

class Log implements \ArrayAccess
{
	/*
	 * @var \CBXVirtualFileFileSystem $file
	 */
	protected $file = null;
	var $data = array();

	/**
	 * Log constructor.
	 * @param string $path Path to log file.
	 * @return void
	 */
	function __construct($path)
	{
		try
		{
			$this->file = \CBXVirtualIo::GetInstance()->GetFile($path);

			if ($this->file->IsExists())
			{
				$data = unserialize($this->file->GetContents(), ["allowed_classes" => false]);
				foreach($data as $key => $val)
				{
					if (array_key_exists($key , $this->data) && is_array($this->data[$key]) && is_array($val))
						$this->data[$key] = array_merge($this->data[$key], $val);
					else
						$this->data[$key] = $val;
				}
			}
		}
		catch (\Throwable $e)
		{
			throw new Main\SystemException("Temporary file has wrong structure.", "BXU351.01");
		}
	}

	/**
	 * Saves log.
	 * @param string $key Key of log array.
	 * @param mixed $value value of log array.
	 * @return $this
	 */
	public function setLog($key, $value)
	{
		if (array_key_exists($key, $this->data) && is_array($this->data) && is_array($value))
			$this->data[$key] = array_merge($this->data[$key], $value);
		else
			$this->data[$key] = $value;
		$this->save();

		return $this;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getValue($key)
	{
		return $this->data[$key];
	}

	/**
	 *
	 */
	public function save()
	{
		try
		{
			$this->file->PutContents(serialize($this->data));
		}
		catch (\Throwable $e)
		{
			throw new Main\SystemException("Temporary file was not saved.", "BXU351.02");
		}
	}

	/**
	 * @return array
	 */
	public function getLog()
	{
		return $this->data;
	}

	/**
	 *
	 */
	public function unlink()
	{
		try
		{
			if ($this->file instanceof \CBXVirtualFileFileSystem && $this->file->IsExists())
				$this->file->unlink();
		}
		catch (\Throwable $e)
		{
			throw new Main\SystemException("Temporary file was not deleted.", "BXU351.03");
		}
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->data);
	}

	/**
	 * @param mixed $offset
	 * @return mixed|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (array_key_exists($offset, $this->data))
			return $this->data[$offset];
		return null;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value): void
	{
		$this->setLog($offset, $value);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset): void
	{
		if (array_key_exists($offset, $this->data))
		{
			unset($this->data[$offset]);
			$this->save();
		}
	}
}

class Uploader
{
	public $files = array();
	public $controlId = "fileUploader";
	public $params = array(
		"allowUpload" => "A",
		"allowUploadExt" => "",
		"copies" => array(
			"default" => array(
				"width" => null,
				"height" => null
			),
		/*	"copyName" => array(
				"width" => 100,
				"height" => 100
			)*/
		),
		"storage" => array(
			"moduleId" => "main",
			"cloud" => false
		)
	);
/*
 * @var string $script Url to uploading page for forming url to view
 * @var string $path Path to temp directory
 * @var string $CID Controller ID
 * @var string $mode
 * @var array $processTime Time limits
 * @var HttpClient $http
*/
	public $script;
	protected $path = "";
	protected $CID = null;
	protected int $version = 0;
	protected $mode = "view";
	protected $param = array();
	protected $requestMethods = array(
		"get" => true,
		"post" => true
	);
	/* @var HttpRequest $request */
	protected $request;
	protected $http;

	const FILE_NAME = "bxu_files";
	const INFO_NAME = "bxu_info";
	const EVENT_NAME = "main_bxu";
	const SESSION_LIST = "MFI_SESSIONS";
	const SESSION_TTL = 86400;

	public function __construct($params = array())
	{
		global $APPLICATION;

		$this->script = $APPLICATION->GetCurPageParam();

		$params = is_array($params) ? $params : array($params);
		$params["copies"] = (isset($params["copies"]) && is_array($params["copies"]) ? $params["copies"] : array()) + $this->params["copies"];
		$params["storage"] = (isset($params["storage"]) && is_array($params["storage"]) ? $params["storage"] : array()) + $this->params["storage"];
		$params["storage"]["moduleId"] = preg_replace("/[^a-z_-]/i", "_", $params["storage"]["moduleId"]);
		$this->params = $params;
		if (array_key_exists("controlId", $params))
			$this->controlId = $params["controlId"];
		if (array_key_exists("events", $params) && is_array($params["events"]))
		{
			foreach($params["events"] as $key => $val)
			{
				$this->setHandler($key, $val);
			}
		}
		unset($this->params["events"]);

		$this->path = \CTempFile::GetDirectoryName(
			12,
			array(
				"bxu",
				$this->params["storage"]["moduleId"],
				md5(serialize(array(
					$this->controlId,
					bitrix_sessid(),
					\CMain::GetServerUniqID()
					))
				)
			)
		);
		$this->request = Context::getCurrent()->getRequest();
	}

	public function setControlId($controlId)
	{
		$this->controlId = $controlId;
	}

	public function setHandler($name, $callback)
	{
		AddEventHandler(self::EVENT_NAME, $name, $callback);
		return $this;
	}
	/**
	 * excludes real paths from array
	 * @param $item - array
	 * @return array
	 */
	protected static function removeTmpPath($item)
	{
		if (is_array($item))
		{
			if (array_key_exists("tmp_name", $item))
			{
				unset($item["tmp_name"]);
			}
			foreach ($item as $key => $val)
			{
				if ($key == "chunksInfo")
				{
					$item[$key]["uploaded"] = count($item[$key]["uploaded"]);
					$item[$key]["written"] = count($item[$key]["written"]);
				}
				else if (is_array($val))
				{
					$item[$key] = self::removeTmpPath($val);
				}
			}
		}
		return $item;
	}

	public static function prepareData($data)
	{
		array_walk_recursive(
			$data,
			function(&$v, $k) {
				if ($k == "error")
				{
					$v = preg_replace("/<(.+?)>/is".BX_UTF_PCRE_MODIFIER, "", $v);
				}
			}
		);
		return self::removeTmpPath($data);
	}

	/**
	 * @return bool
	 * @throws AccessDeniedException
	 * @throws ArgumentOutOfRangeException
	 * @throws NotImplementedException
	 */
	protected function fillRequireData()
	{
		$this->mode = $this->getRequest("mode");
		if (!in_array($this->mode, array("upload", "delete", "view")))
			throw new ArgumentOutOfRangeException("mode");

		if ($this->mode != "view" && !check_bitrix_sessid())
			throw new AccessDeniedException("Bad sessid.");

		$this->version = (int) $this->getRequest("version");

		$directory = \CBXVirtualIo::GetInstance()->GetDirectory($this->path);
		$directoryExists = $directory->IsExists();
		if (!$directory->Create())
			throw new NotImplementedException("Mandatory directory has not been created.");
		if (!$directoryExists)
		{
			$access = \CBXVirtualIo::GetInstance()->GetFile($directory->GetPath()."/.access.php");
			$content = '<?$PERM["'.$directory->GetName().'"]["*"]="X";?>';

			if (!$access->IsExists() || mb_strpos($access->GetContents(), $content) === false)
			{
				if (($fd = $access->Open('ab')))
				{
					fwrite($fd, $content);
				}
				fclose($fd);
			}
		}

		return false;
	}

	protected function showJsonAnswer($result)
	{
		if (!defined("PUBLIC_AJAX_MODE"))
			define("PUBLIC_AJAX_MODE", true);
		if (!defined("NO_KEEP_STATISTIC"))
			define("NO_KEEP_STATISTIC", "Y");
		if (!defined("NO_AGENT_STATISTIC"))
			define("NO_AGENT_STATISTIC", "Y");
		if (!defined("NO_AGENT_CHECK"))
			define("NO_AGENT_CHECK", true);
		if (!defined("DisableEventsCheck"))
			define("DisableEventsCheck", true);

		require_once(\Bitrix\Main\Application::getInstance()->getContext()->getServer()->getDocumentRoot()."/bitrix/modules/main/include/prolog_before.php");
		global $APPLICATION;

		$APPLICATION->RestartBuffer();

		$version = IsIE();
		if ( !(0 < $version && $version < 10) )
			header('Content-Type:application/json; charset=UTF-8');

		echo Json::encode($result);
		\CMain::finalActions();
		die;
	}

	/**
	 * Sets request methods to check.
	 * @param array $methods Request methods array("get", "post").
	 * @return $this
	 */
	protected function setRequestMethodToCheck(array $methods)
	{
		foreach ($this->requestMethods as $method => $value)
			$this->requestMethods[$method] = in_array($method, $methods);
		return $this;
	}
	/**
	 * @param string $key Array key in request.
	 * @return null|mixed
	 */
	protected function getRequest($key)
	{
		if ($this->requestMethods["post"] &&
			is_array($this->request->getPost(self::INFO_NAME)) &&
			array_key_exists($key, $this->request->getPost(self::INFO_NAME)))
		{
			$res = $this->request->getPost(self::INFO_NAME);
			return $res[$key];
		}
		if ($this->requestMethods["get"] &&
			is_array($this->request->getQuery(self::INFO_NAME)) &&
			array_key_exists($key, $this->request->getQuery(self::INFO_NAME)))
		{
			$res = $this->request->getQuery(self::INFO_NAME);
			return $res[$key];
		}

		return null;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getParam($key)
	{
		return $this->params[$key];
	}
	/**
	 *
	 * @param bool $checkPost Flag for check post.
	 * @return boolean
	 */
	public function checkPost($checkPost = true)
	{
		if ($checkPost === false && !is_array($this->request->getQuery(self::INFO_NAME)) ||
			$checkPost !== false && !is_array($this->request->getPost(self::INFO_NAME)))
			return false;

		if ($checkPost === false)
			$this->setRequestMethodToCheck(array("get"));

		try
		{
			$this->fillRequireData();
			$cid = FileInputUtility::instance()->registerControl($this->getRequest("CID"), $this->controlId);

			if ($this->mode == "upload")
			{
				$package = new Package(
					$this->path,
					$cid,
					$this->getRequest("packageIndex")
				);
				$package
					->setStorage($this->params["storage"])
					->setCopies($this->params["copies"]);

				$response = $package->checkPost($this->params);

				if ($this->version <= 1)
				{
					$response2 = array();
					foreach ($response as $k => $r)
					{
						$response2[$k] = array(
							"hash" => $r["hash"],
							"status" => $r["status"],
							"file" => $r
						);
						if (isset($r["error"]))
							$response2[$k]["error"] = $r["error"];
					}
					$result = array(
						"status" => $package->getLog("uploadStatus") == "uploaded" ? "done" : "inprogress",
						"package" => array(
							$package->getIndex() => array_merge(
								$package->getLog(),
								array(
									"executed" => $package->getLog("executeStatus") == "executed",
									"filesCount" => $package->getLog("filesCount")
								)
							)
						),
						"report" => array(
							"uploading" => array(
								$package->getCid() => $package->getCidLog()
							)
						),
						"files" => self::prepareData($response2)
					);
					$this->showJsonAnswer($result);
				}
			}
			else if ($this->mode == "delete")
			{
				$cid = FileInputUtility::instance()->registerControl($this->getRequest("CID"), $this->controlId);
				File::deleteFile($cid, $this->getRequest("hash"), $this->path);
			}
			else
			{
				File::viewFile($cid, $this->getRequest("hash"), $this->path);
			}
			return true;
		}
		catch (Main\IO\IoException $e)
		{
			$this->showJsonAnswer(array(
				"status" => "error",
				"error" => "Something went wrong with the temporary file."
			));
		}
		catch (\Exception $e)
		{
			$this->showJsonAnswer(array(
				"status" => "error",
				"error" => $e->getMessage()
			));
		}
		return false;
	}

	/**
	 * @param $hash
	 * @param $file
	 * @param array $canvases
	 * @param array $watermark
	 * @return mixed
	 */
	public function checkCanvases($hash, &$file, $canvases = array(), $watermark = array())
	{
		if (!empty($watermark))
		{
			$file["files"]["default"] = File::createCanvas(
				$file["files"]["default"],
				$file["files"]["default"],
				array(),
				$watermark
			);
		}
		if (is_array($canvases))
		{
			foreach ($canvases as $canvas => $canvasParams)
			{
				if (!array_key_exists($canvas, $file["files"]))
				{
					$source = $file["files"]["default"]; // TODO pick up more appropriate copy by params
					$file["files"][$canvas] = File::createCanvas($source,
						array(
							"code" => $canvas,
							"tmp_name" => mb_substr($source["tmp_name"], 0, -7).$canvas,
							"url" => mb_substr($source["url"], 0, -7).$canvas
						), $canvasParams, $watermark);
				}
			}
		}
		return $file;
	}
	public function deleteFile($hash) {
		$cid = FileInputUtility::instance()->registerControl($this->getRequest("CID"), $this->controlId);
		File::deleteFile($cid, $hash, $this->path);
	}

	/**
	 * @param string $tmpName
	 * @return false|array
	 */
	public static function getPaths($tmpName)
	{
		$docRoot = null;
		$io = \CBXVirtualIo::GetInstance();
		if (($tempRoot = \CTempFile::GetAbsoluteRoot()) && ($strFilePath = $tempRoot.$tmpName) &&
			$io->FileExists($strFilePath) && ($url = File::getUrlFromRelativePath($tmpName)))
		{
			return array(
				"tmp_url" => $url,
				"tmp_name" => $strFilePath
			);
		}
		else if ((($docRoot = \Bitrix\Main\Application::getInstance()->getContext()->getServer()->getDocumentRoot()) && $strFilePath = $docRoot.$tmpName) && $io->FileExists($strFilePath))
		{
			return array(
				"tmp_url" => $tmpName,
				"tmp_name" => $strFilePath
			);
		}
		else if ($io->FileExists($tmpName) && ($docRoot = \Bitrix\Main\Application::getInstance()->getContext()->getServer()->getDocumentRoot()) &&
			mb_strpos($tmpName, $docRoot) === 0)
		{
			return array(
				"tmp_url" => str_replace("//", "/", "/".mb_substr($tmpName, mb_strlen($docRoot))),
				"tmp_name" => $tmpName
			);
		}
		return false;
	}

}
