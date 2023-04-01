<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\ErrorCollection;
use \Bitrix\Main\Config;
use \Bitrix\Vote\Base\Diag;

class CVoteUfComponent extends \CBitrixComponent
{
	protected $editMode = false;

	const STATUS_SUCCESS = 'success';
	const STATUS_DENIED  = 'denied';
	const STATUS_ERROR   = 'error';

	/** @var  string */
	protected $actionPrefix = "action";
	/** @var  string */
	protected $action = "default";
	/** @var  ErrorCollection */
	protected $errorCollection;

	protected $componentId = '';

	protected static $events = array();

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->componentId = $this->isAjaxRequest()? randString(7) : $this->randString();
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
	}

	protected function sendResponse($response)
	{
		$this->getApplication()->restartBuffer();
		echo $response;
		$this->end();
	}

	protected function sendJsonResponse($response)
	{
		$this->getApplication()->restartBuffer();

		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode($response);

		$this->end();
	}

	protected function sendJsonErrorResponse()
	{
		$errors = array();
		foreach($this->getErrors() as $error)
		{
			/** @var Error $error */
			$errors[] = array(
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			);
		}
		unset($error);
		$this->sendJsonResponse(array(
			'status' => 'error',
			'errors' => $errors,
		));
	}

	protected function sendJsonSuccessResponse(array $response = array())
	{
		$response['status'] = self::STATUS_SUCCESS;
		$this->sendJsonResponse($response);
	}

	protected function sendJsonAccessDeniedResponse($message = '')
	{
		$this->sendJsonResponse(array(
			'status' => self::STATUS_DENIED,
			'message' => $message,
		));
	}

	protected function end($terminate = true)
	{

		Diag::getInstance()->logDebugInfo($this->getName());

		if($terminate)
		{
			/** @noinspection PhpUndefinedClassInspection */
			\CMain::finalActions();
			die;
		}
	}

	public function hasErrors()
	{
		return $this->errorCollection->hasErrors();
	}

	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	public function getComponentId()
	{
		return $this->componentId;
	}

	public function executeComponent()
	{
		try
		{
			\Bitrix\Main\Loader::includeModule("vote");
			Diag::getInstance()->collectDebugInfo($this->componentId);
			$this->prepareParams();

			if ($this->request->getQuery("exportVoting") > 0)
			{
				/** @var \Bitrix\Vote\Attach $attach */
				foreach ($this->arResult["ATTACHES"] as $attach)
				{
					if ($attach->getAttachId() == $this->request->getQuery("exportVoting"))
						$attach->exportExcel();
				}
			}
			if ($this->editMode)
				$this->processActionEdit();
			else
				$this->processActionView();

			Diag::getInstance()->logDebugInfo($this->componentId, $this->getName());
		}
		catch(Exception $e)
		{
			if($this->isAjaxRequest())
			{
				$this->sendJsonResponse(array(
					'status' => 'error',
					'message' => $e->getMessage(),
				));
			}
			else
			{
				$exceptionHandling = Config\Configuration::getValue("exception_handling");
				if ($exceptionHandling["debug"])
				{
					throw $e;
				}
			}
		}
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean
	 */
	protected function isAjaxRequest()
	{
		return $this->request->isAjaxRequest();
	}

	/**
	 * @return Application|\Bitrix\Main\HttpApplication|\CAllMain|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @return array|bool|\CAllUser|\CUser
	 */
	protected function getUser()
	{
		global $USER;
		return $USER;
	}

	protected function prepareParams()
	{
		$this->editMode = isset($this->arParams["EDIT"]) && $this->arParams["EDIT"] === "Y";
		if (array_key_exists("PARAMS", $this->arParams) && array_key_exists("PARAMS", $this->arParams))
		{
			$this->arResult = $this->arParams["RESULT"];
			$this->arParams = $this->arParams["PARAMS"];
			$this->editMode = $this->editMode || (isset($this->arParams["EDIT"]) && $this->arParams["EDIT"] === "Y");
		}

		if (empty($this->arParams["arUserField"]))
			throw new \Bitrix\Main\ArgumentNullException("arUserField");

		$this->userFieldManager = \Bitrix\Vote\Uf\Manager::getInstance($this->arParams["arUserField"]);

		$cacheTtl = 0;

		$cacheId = "uf_".md5(serialize($this->arParams["arUserField"]));
		$cacheDir = '/vote/uf/';

		if ($this->arParams["CACHE_TYPE"] == "Y")
			$cacheTtl = intval($this->arParams["CACHE_TIME"]);
		else if ($this->arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y")
			$cacheTtl = 3600;

		$cache = new \CPHPCache();

		if($cache->initCache($cacheTtl, $cacheId, $cacheDir))
		{
			$result = $cache->getVars();
		}
		else
		{
			$result = $this->userFieldManager->loadFromEntity();
			if (!empty($result))
			{
				$cache->StartDataCache($cacheTtl, $cacheId, $cacheDir);
				/**
				 * @var $attach \Bitrix\Vote\Attach
				 */
				foreach ($result as $attach)
				{
					if ($attach->getVoteId() > 0)
						\CVoteCacheManager::SetTag($cacheDir, "V", $attach->getVoteId());
				}

				$cache->EndDataCache($result);
			}
		}

		$this->arResult["ATTACHES"] = $result;

		return $this;
	}

	protected function processActionView()
	{
		$values = is_array($this->arResult["VALUE"]) ? $this->arResult["VALUE"] : array($this->arResult["VALUE"]);
		$attaches = array();
		foreach ($values as $value)
		{
			$value = intval($value);
			/* @var \Bitrix\Vote\Attach $attach */
			if ($value > 0 &&
				array_key_exists($value, $this->arResult["ATTACHES"]) &&
				($attach = $this->arResult["ATTACHES"][$value]) &&
				$attach["ACTIVE"] == "Y" &&
				$attach->canRead($this->getUser()->getId()))
			{
				$attaches[$value] = $attach;
			}
		}
		$this->arResult["ATTACHES"] = $attaches;

		$this->includeComponentTemplate("view");
	}

	protected function processActionEdit()
	{
		$values = is_array($this->arResult["VALUE"]) ? $this->arResult["VALUE"] : array($this->arResult["VALUE"]);
		$attaches = array();
		foreach ($values as $value)
		{
			$value = intval($value);
			/* @var \Bitrix\Vote\Attach $attach */
			if ($value > 0 &&
				array_key_exists($value, $this->arResult["ATTACHES"]) &&
				($attach = $this->arResult["ATTACHES"][$value]) &&
				$attach->canEdit($this->getUser()->getId()))
			{
				$attaches[$value] = $attach;
			}
		}
		$this->arResult["ATTACHES"] = $attaches;

		$this->includeComponentTemplate("edit");
	}
}