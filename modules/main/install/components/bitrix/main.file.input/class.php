<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions | 'N'one
// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Error;
use \Bitrix\Main\UI\Uploader\Uploader;
use \Bitrix\Main\UI\FileInputUtility;
include_once(__DIR__."/file.php");
class MFIComponent extends \CBitrixComponent
{
	/** @var ErrorCollection */
	protected $errorCollection;
	/** @var string  */
	protected $componentId = '';
	/** @var MFIController */
	protected $controller;
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
		$this->componentId = $this->isAjaxRequest()? randString(7) : $this->randString();
		$this->controller = new MFIController;
	}

	/**
	 * @return bool|string
	 */
	public function executeComponent()
	{
		try
		{
			$this->prepareParams();
			$this->controller->
				setModuleId($this->arParams["MODULE_ID"])->
				setForceMd5($this->arParams["FORCE_MD5"])->
				generateCid($this->arParams["CONTROL_ID"]);

			if ($this->arParams['ALLOW_UPLOAD'])
				$this->controller->initUploader(array(
					"allowUpload" => $this->arParams["ALLOW_UPLOAD"],
					"allowUploadExt" => $this->arParams["ALLOW_UPLOAD_EXT"],
					"uploadMaxFilesize" => $this->arParams['MAX_FILE_SIZE']
				));
			$this->controller->checkRequest($this->arParams["CONTROL_ID"]);

			$this->arParams["URL_TO_UPLOAD"] = $this->controller->getUrlUpload();
			$this->arParams["CONTROL_UID"] = $this->arResult["CONTROL_UID"] = $this->controller->getCid();
			$this->arParams["CONTROL_SIGN"] = $this->controller->getSignature(
				array(
					"moduleId" => $this->arParams["MODULE_ID"],
					"forceMd5" => $this->arParams["FORCE_MD5"],
					"allowUpload" => $this->arParams["ALLOW_UPLOAD"],
					"allowUploadExt" => $this->arParams["ALLOW_UPLOAD_EXT"],
					"uploadMaxFilesize" => $this->arParams['MAX_FILE_SIZE']
				)
			);

			$value = $this->arParams['INPUT_VALUE'];
			if (is_array($value) && implode(",", $value) <> '')
			{
				$dbRes = CFile::GetList(array(), array("@ID" => implode(",", $value)));
				while ($file = $dbRes->GetNext())
				{
					$this->controller->registerFile($file['ID']);
					$file['URL'] = $this->controller->getUrlDownload($file['ID']);
					$file['URL_DELETE'] = $this->controller->getUrlDelete($file['ID']);
					$file['FILE_SIZE_FORMATTED'] = CFile::FormatSize($file['FILE_SIZE']);
					$file["SRC"] = CFile::GetFileSRC($file);
					$this->arResult['FILES'][$file['ID']] = $file;
				}
			}

			$this->includeComponentTemplate();
			return $this->arParams['CONTROL_ID'];
		}
		catch(\Exception $e)
		{
			$this->errorCollection->add(array(new Error($e->getMessage())));
			if($this->isAjaxRequest())
			{
				$this->controller->sendErrorResponse($this->errorCollection);
			}
			else
			{
				$exceptionHandling = \Bitrix\Main\Config\Configuration::getValue("exception_handling");
				if ($exceptionHandling["debug"])
				{
					throw $e;
				}
			}
		}
		return false;
	}
	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean
	 */
	protected function isAjaxRequest()
	{
		return $this->request->isAjaxRequest();
	}
	protected function prepareParams()
	{
		$arParams = &$this->arParams;
		$arResult = &$this->arResult;
		$arParams['MAX_FILE_SIZE'] = intval($arParams['MAX_FILE_SIZE']);
		$arParams['MODULE_ID'] = $arParams['MODULE_ID'] && IsModuleInstalled($arParams['MODULE_ID']) ? $arParams['MODULE_ID'] : "main";
		$arParams['FORCE_MD5'] = ($arParams['FORCE_MD5'] === true);
		$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_\\-]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : '';
// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions | 'N'one
// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')
		if ($arParams['ALLOW_UPLOAD'] == 'N' || $arParams['ALLOW_UPLOAD'] === false)
		{
			$arParams['ALLOW_UPLOAD'] = 'N';
		}
		elseif (
			$arParams['ALLOW_UPLOAD'] != 'I' &&
			(
				$arParams['ALLOW_UPLOAD'] != 'F' || $arParams['ALLOW_UPLOAD_EXT'] == ''
			)
		)
		{
			$arParams['ALLOW_UPLOAD'] = 'A';
		}

		if (mb_substr($arParams['INPUT_NAME'], -2) == '[]')
			$arParams['INPUT_NAME'] = mb_substr($arParams['INPUT_NAME'], 0, -2);
		if (mb_substr($arParams['INPUT_NAME_UNSAVED'], -2) == '[]')
			$arParams['INPUT_NAME_UNSAVED'] = mb_substr($arParams['INPUT_NAME_UNSAVED'], 0, -2);
		if (!is_array($arParams['INPUT_VALUE']) && intval($arParams['INPUT_VALUE']) > 0)
			$arParams['INPUT_VALUE'] = array($arParams['INPUT_VALUE']);

		$arResult['CONTROL_ID'] = $arParams['CONTROL_ID'] = ($arParams['CONTROL_ID'] != '' ? $arParams['CONTROL_ID'] : 'mfi'.$arParams['INPUT_NAME']);

		$arParams['INPUT_NAME'] = trim($arParams['INPUT_NAME']);
		if (!preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME']))
			throw new \Bitrix\Main\ArgumentException(GetMessage('MFI_ERR_NO_INPUT_NAME'));

		$arParams['MULTIPLE'] = $arParams['MULTIPLE'] == 'N' ? 'N' : 'Y';

		$arResult['FILES'] = array();
	}
}