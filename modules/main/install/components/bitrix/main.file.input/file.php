<?
if (!defined("B_PROLOG_INCLUDED"))
	require_once(__DIR__."/ajax.php");

use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Error;
use \Bitrix\Main\UI\Uploader\Uploader;
use \Bitrix\Main\UI\FileInputUtility;
use \Bitrix\Main\UI\Uploader\File;

class MFIController
{
	const STATUS_SUCCESS = "success";
	const STATUS_ERRORED = "error";
	/** @var string  */
	protected $cid = "";
	/** @var string  */
	protected $moduleId = "main";
	/** @var boolean  */
	protected $forceMd5 = false;
	/** @var  \Bitrix\Main\HttpRequest */
	protected $request;
	/** @var Uploader  */
	protected $uploader = null;

	/**
	 * MFIController constructor.
	 * @param Uploader $uploader
	 */
	public function __construct()
	{
		$this->request = \Bitrix\Main\Context::getCurrent()->getRequest();
	}
	/**
	 * @param string $moduleId
	 * @return $this
	 */
	public function setModuleId($moduleId)
	{
		if (\Bitrix\Main\ModuleManager::isModuleInstalled($moduleId))
			$this->moduleId = $moduleId;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}
	/**
	 * @param boolean|string $md5
	 * @return $this
	 */
	public function setForceMd5($md5)
	{
		$this->forceMd5 = ($md5===true || $md5==="true");
		return $this;
	}
	/**
	 * @return boolean
	 */
	public function getForceMd5()
	{
		return $this->forceMd5;
	}
	/**
	 * @param string $cid Id of Controls.
	 * @return null|string
	 */
	public function getControlByCid($cid)
	{
		return FileInputUtility::instance()->getControlByCid($cid);
	}
	/**
	 * @param string $cid Id of Controls.
	 * @return $this
	 */
	public function setCid($cid)
	{
		$this->cid = $cid;
		return $this;
	}

	/**
	 * @param string $controlId Control ID.
	 * @return $this
	 */
	public function generateCid($controlId)
	{
		$this->cid = FileInputUtility::instance()->registerControl("", $controlId);
		return $this;
	}
	/**
	 * @return string
	 */
	public function getCid()
	{
		return $this->cid;
	}
	/**
	 * @param string $cid Id of control instance.
	 * @return string
	 */
	public function isCidRegistered($cid)
	{
		return FileInputUtility::instance()->isCidRegistered($cid);
	}
	/**
	 * @param array $params
	 * @return MFIController
	 */
	public function initUploader($params)
	{
		return $this->setUploader(new Uploader(array(
			"allowUpload" => $params["allowUpload"],
			"allowUploadExt" => $params["allowUploadExt"],
			"uploadMaxFilesize" => $params['uploadMaxFilesize']
		)));
	}

	/**
	 * @param array $params
	 * @return MFIController
	 */
	public function setUploader($uploader)
	{
		if ($this->uploader != $uploader)
		{
			$this->uploader = $uploader;
			$this->uploader->setHandler("onFileIsUploaded", array($this, "handleFile"));
		}
		return $this;
	}

	public function getUploader()
	{
		return $this->uploader;
	}


	/**
	 * @return Application|CAllMain|CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	protected function sendJSResponse($response)
	{
		$this->getApplication()->restartBuffer();
		while(ob_end_clean()); // hack!
		header('Content-Type: text/html; charset='.LANG_CHARSET);
		echo $response;
		$this->end();
	}

	protected function sendJsonResponse($response)
	{
		$this->getApplication()->restartBuffer();
		while(ob_end_clean()); // hack!
		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode($response);
		$this->end();
	}

	public function sendErrorResponse($errorCollection)
	{
		$errors = array();
		$errorsText = array();
		foreach($errorCollection as $error)
		{
			/** @var Error $error */
			$errors[] = array(
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			);
			$errorsText[] = $error->getMessage();
		}
		unset($error);
		$response['status'] = self::STATUS_ERRORED;
		if (($uid = intval($this->request->getPost("uniqueID"))) && $uid > 0) // for custom components
		{
			$this->sendJSResponse("<script>parent.FILE_UPLOADER_CALLBACK_{$uid}('".CUtil::JSEscape(implode("", $errorsText))."', {$uid});</script>");
		}
		else
		{
			$this->sendJsonResponse(array(
				'status' => 'error',
				'errors' => $errors,
			));
		}
	}

	public function sendSuccessResponse(array $response = array())
	{
		if (($uid = intval($this->request->getPost("uniqueID"))) && $uid > 0) // for custom components
		{
			$this->sendJSResponse("<script>parent.FILE_UPLOADER_CALLBACK_{$uid}(".CUtil::PhpToJsObject($response).", {$uid});</script>");
		}
		else
		{
			$response['status'] = self::STATUS_SUCCESS;
			$this->sendJsonResponse($response);
		}
	}

	protected function end($terminate = true)
	{
		if($terminate)
		{
			/** @noinspection PhpUndefinedClassInspection */
			\CMain::finalActions();
			die;
		}
	}

	/**
	 * @throws \Bitrix\Main\AccessDeniedException
	 * @return void
	 */
	public function checkRequest($controlId=null)
	{
		$cid = null;
		$action = null;

		if ($this->request->isPost() && $this->request->getPost("mfi_mode"))
		{
			$action = $this->request->getPost("mfi_mode");
			$cid = trim($this->request->getPost("cid"));
		}
		else if (!($this->request->isPost()) &&  $this->request->getQuery("mfi_mode"))
		{
			$action = $this->request->getQuery("mfi_mode");
			$cid = trim($this->request->getQuery("cid"));
		}
		if (is_null($action))
			return;
		if (!preg_match('/^[a-f01-9]{32}$/', $cid))
			throw new \Bitrix\Main\AccessDeniedException("CID has wrong format.");

		$registeredControlId = $this->getControlByCid($cid);

		if (!is_null($controlId) && $controlId != $registeredControlId)
			return;
		if (is_null($registeredControlId))
			throw new \Bitrix\Main\AccessDeniedException("CID is not registered.");
		if (!check_bitrix_sessid())
			throw new \Bitrix\Main\AccessDeniedException("Bad sessid.");

		$this->setCid($cid);

		if ($action == "upload")
		{
			if ($this->getUploader() === null)
				throw new \Bitrix\Main\AccessDeniedException("Uploading is forbidden.");
			$this->getUploader()->setControlId($registeredControlId);
			$this->executeActionUpload();
		}
		else if ($action == "delete")
		{
			$this->executeActionDelete(intval($this->request->getPost("fileID") ?: $this->request->getQuery("fileID")));
		}
		else if ($action == "down")
		{
			$this->executeActionDownload(intval($this->request->getPost("fileID") ?: $this->request->getQuery("fileID")));
		}
	}

	protected function executeActionUpload()
	{
		$this->getUploader()->checkPost();

		$count = sizeof($_FILES["mfi_files"]["name"]);
		$max_file_size = $this->getUploader()->getParam("uploadMaxFilesize");

		$result = array();
		for ($i = 0; $i < $count; $i++)
		{
			$fileName = \CUtil::ConvertToLangCharset($_FILES["mfi_files"]["name"][$i]);
			$file = array(
				"name" => $fileName,
				"size" => $_FILES["mfi_files"]["size"][$i],
				"tmp_name" => $_FILES["mfi_files"]["tmp_name"][$i],
				"type" => $_FILES["mfi_files"]["type"][$i]
			);

			if ($_FILES["mfi_files"]["error"][$i] != UPLOAD_ERR_OK)
				$res = File::getUploadErrorMessage($_FILES["mfi_files"]["error"][$i]);
			elseif ($this->getUploader()->getParam("allowUpload") == "I")
				$res = \CFile::CheckImageFile($file, $max_file_size, 0, 0);
			elseif ($this->getUploader()->getParam("allowUpload") == "F")
				$res = \CFile::CheckFile($file, $max_file_size, false, $this->getUploader()->getParam("allowUploadExt"));
			else
				$res = \CFile::CheckFile($file, $max_file_size, false, false);

			try
			{
				if (strlen($res) > 0)
					throw new \Bitrix\Main\ArgumentException($res);
				$tmp = $this->saveFile($file);

				$tmp["status"] = self::STATUS_SUCCESS;
			}
			catch(\Exception $e)
			{
				$tmp = array(
					"fileName" => $file["name"],
					"fileID" => 0,
					"status" => self::STATUS_ERRORED,
					"message" => $e->getMessage()
				);
			}
			$result[] = $tmp;
		}
		$this->sendSuccessResponse($result);
	}

	public function handleFile($hash, &$file, $packData, $logData, &$error)
	{
		$key = "default";
		try
		{
			$tmp = $this->saveFile($file["files"][$key]);

			$this->getUploader()->deleteFile($hash);

			$file["name"] = $tmp["fileName"];
			$file["originalName"] = $tmp["originalName"];
			$file["size"] = $tmp["~fileSize"];
			$file["type"] = $tmp["fileContentType"];
			$file["url"] = $tmp["fileURL"];
			$file["file_id"] = $tmp["fileID"];
			return true;
		}
		catch(\Exception $e)
		{
			$error .= $e->getMessage();
			return false;
		}
	}

	protected function saveFile($file)
	{
		$cid = $this->getCid();
		$mid = $this->getModuleId();
		$md5 = $this->getForceMd5();
		$file["MODULE_ID"] = $mid;
		if (($fileID = CFile::SaveFile($file, $mid, $md5)) &&
			$fileID > 0 &&
			($file = CFile::GetFileArray($fileID)) &&
			is_array($file)
		)
		{
			$tmp = array(
				"fileName" => $file["FILE_NAME"],
				"originalName" => $file["ORIGINAL_NAME"],
				"fileID" => $fileID,
				"fileContentType" => $file["CONTENT_TYPE"],
				"~fileSize" => $file['FILE_SIZE'],
				"fileSize" => CFile::FormatSize($file['FILE_SIZE']),
				"fileURL" => $this->getUrlDownload($fileID),
				"fileURLDelete" => $this->getUrlDelete($fileID),
			);

			FileInputUtility::instance()->registerFile($cid, $fileID);

			foreach(GetModuleEvents("main", "main.file.input.upload", true) as $event)
				ExecuteModuleEventEx($event, array(&$tmp));

			return $tmp;
		}
		throw new \Bitrix\Main\NotImplementedException();
	}

	protected function executeActionDelete($fid)
	{
		if ($fid > 0 && FileInputUtility::instance()->unRegisterFile($this->getCid(), $fid))
		{
			CFile::Delete($fid);
		}
	}
	protected function executeActionDownload($fid)
	{
		if ($fid > 0 &&
			FileInputUtility::instance()->checkFile($this->getCid(), $fid) &&
			($file = \CFile::GetFileArray($fid)) &&
			!empty($file))
		{

			$this->getApplication()->RestartBuffer();
			while(ob_end_clean()); // hack!

			if ($this->validate($this->getCid(), $_REQUEST["s"]))
				CFile::ViewByUser($file, array("content_type" => $file["CONTENT_TYPE"]));
			else
				CFile::ViewByUser($file, array("force_download" => true));
		}
	}

	public function registerFile($id)
	{
		FileInputUtility::instance()->registerFile($this->getCid(), $id);
	}

	/**
	 * @param integer $id
	 * @return string
	 */
	public function getUrlDownload($id)
	{
		$query = array(
			"mfi_mode" => "down",
			"fileID" => $id,
			"cid" => $this->getCid(),
			"sessid" => bitrix_sessid(),
			"s" => $this->getSignature($this->getCid())
		);
		return "/bitrix/components/bitrix/main.file.input/ajax.php?" . http_build_query($query);
	}
	/**
	 * @param integer $id
	 * @return string
	 */
	public function getUrlDelete($id)
	{
		$query = array(
			"mfi_mode" => "delete",
			"fileID" => $id,
			"cid" => $this->getCid(),
			"sessid" => bitrix_sessid(),
			"s" => $this->getSignature($this->getCid())
		);
		return "/bitrix/components/bitrix/main.file.input/ajax.php?" . http_build_query($query);
	}
	/**
	 * @return string
	 */
	public function getUrlUpload()
	{
		$query = array(
			"mfi_mode" => "upload",
			"cid" => $this->getCid(),
			"sessid" => bitrix_sessid(),
			"s" => $this->getSignature($this->getCid())
		);
		return "/bitrix/components/bitrix/main.file.input/ajax.php?" . http_build_query($query);
	}

	/**
	 * Returns message signature.
	 * @param string $value Message.
	 * @return string
	 */
	public function getSignature($value)
	{
		$signer = new \Bitrix\Main\Security\Sign\Signer;
		$value = self::prepareValueForSigner($value);
		return $signer->getSignature($value, "main.file.input");
	}
	/**
	 * Simply validation of message signature.
	 * @param string $value Message.
	 * @param string $signature Signature.
	 * @return bool True if OK, otherwise - false.
	 */
	public function validate($value, $signature)
	{
		if (is_string($signature) && strlen($signature) > 0)
		{
			$value = self::prepareValueForSigner($value);
			$signer = new \Bitrix\Main\Security\Sign\Signer;
			return $signer->validate($value, $signature, "main.file.input");
		}
		return false;
	}
	private static function prepareValueForSigner($value)
	{
		if (is_array($value))
		{
			array_walk($value, function(&$item1)
			{
				if (is_bool($item1))
					$item1 = ($item1 === true ? "true" : "false");
				else if (empty($item1))
					$item1 = "";
				else if (is_integer($item1))
					$item1 .= "";
			});
			ksort($value);
			$value = serialize($value);
		}
		return $value;
	}
}

?>