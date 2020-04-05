<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Forum\Internals\Error\Error;
use Bitrix\Forum\Internals\Error\ErrorCollection;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Uploader\ErrorCatcher;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config;

Loc::loadMessages(__FILE__);

final class ForumCommentsComponent extends CBitrixComponent
{
	const ERROR_REQUIRED_PARAMETER = 'FORUM_BASE_COMPONENT_22001';
	const ERROR_ACTION = 'FORUM_BASE_COMPONENT_22002';

	const STATUS_SUCCESS = 'success';
	const STATUS_DENIED  = 'denied';
	const STATUS_ERROR   = 'error';

	/** @var  string */
	protected $actionPrefix = 'action';
	/** @var  string */
	protected $action;
	/** @var  ErrorCollection */
	protected $errorCollection;
	protected $componentId = '';
	/** @var \Bitrix\Forum\Comments\Feed */
	protected $feed;
	/** @var  CCaptcha */
	public $captcha;
	/** @var  array */
	private $forum;

	/** @var array */
	private static $users = array();
	/** @var integer */
	private static $index = 0;

	const STATUS_SCOPE_MOBILE = 'mobile';
	const STATUS_SCOPE_WEB = 'web';

	private $scope;
	public $prepareMobileData;

	public function __construct($component = null)
	{
		parent::__construct($component);
		\Bitrix\Main\Loader::includeModule("forum");
		$this->componentId = $this->isAjaxRequest()? randString(7) : $this->randString();
		$this->errorCollection = new ErrorCollection();

		$this->prepareMobileData = IsModuleInstalled("mobile");
		$this->scope = self::STATUS_SCOPE_WEB;
		if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 &&
			defined("BX_MOBILE") && BX_MOBILE === true)
			$this->scope = self::STATUS_SCOPE_MOBILE;

		self::$index++;

		$templateName = $this->getTemplateName();

		if ((empty($templateName) || $templateName == ".default" || $templateName == "bitrix24"))
		{
			if ($this->isWeb())
				$this->setTemplateName(".default");
			else
				$this->setTemplateName("mobile_app");
		}
	}

	public function isWeb()
	{
		return ($this->scope == self::STATUS_SCOPE_WEB);
	}

	protected function sendResponse($response)
	{
		$this->getApplication()->restartBuffer();
		while (ob_end_clean());

		echo $response;

		$this->end();
	}


	protected function sendJsonResponse($response)
	{
		$this->getApplication()->restartBuffer();
		while (ob_end_clean());

		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode($response);

		$this->end();
	}

	protected function showError()
	{
		$errors = array();
		foreach($this->getErrors() as $error)
		{
			/** @var Error $error */
			$errors[] = $error->getMessage();
		}
		unset($error);
		ShowError(implode("", $errors));

		$this->end(false);
	}

	protected function sendError()
	{
		$errors = array(); $errorsText = array();
		foreach($this->getErrors() as $error)
		{
			/** @var Error $error */
			$errors[] = array(
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
			);
			$errorsText[] = $error->getMessage();
		}
		unset($error);

		if ($this->isAjaxRequest())
		{
			$this->sendJsonResponse(array(
				'status' => self::STATUS_ERROR,
				'errors' => $errors,
			));
		}
		else
		{
			$this->sendResponse(implode("", $errorsText));
		}
	}

	protected function handleException(Exception $e)
	{
		if($this->isAjaxRequest())
		{
			$this->sendJsonResponse(array(
				'status' => static::STATUS_ERROR,
				'message' => $e->getMessage(),
			));
		}
		else
		{
			$exceptionHandling = Config\Configuration::getValue("exception_handling");
			if($exceptionHandling["debug"])
			{
				throw $e;
			}
		}

	}

	protected function end($terminate = true)
	{
		if (IsModuleInstalled("disk"))
		{
			\Bitrix\Disk\Internals\Diag::getInstance()->logDebugInfo($this->getName());
		}
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

	public function executeComponent()
	{
		try
		{
/*
			if (IsModuleInstalled("disk"))
			{
				\Bitrix\Disk\Internals\Diag::getInstance()
//					->setExclusiveUserId(45)
					->setEnableTimeTracker(true)
					->setMemoryBehavior(\Bitrix\Disk\Internals\Diag::MEMORY_PRINT_DIFF)
					->setSqlBehavior(\Bitrix\Disk\Internals\Diag::SQL_COUNT)
					->collectDebugInfo($this->componentId, $this->getName());
			}
*/
			$this->checkRequiredParams();

			$this->feed = new \Bitrix\Forum\Comments\Feed(
				$this->arParams["FORUM_ID"],
				array(
					"type" => $this->arParams["ENTITY_TYPE"],
					"id" => $this->arParams["ENTITY_ID"],
					"xml_id" => $this->arParams["ENTITY_XML_ID"]
				),
				(isset($this->arParams["RECIPIENT_ID"]) ? intval($this->arParams["RECIPIENT_ID"]) : 0)
			);

			$this->forum = $this->feed->getForum();
			if (array_key_exists("PERMISSION", $this->arParams))
			{
				$this->feed->setPermission($this->arParams["PERMISSION"]);
			}
			if (array_key_exists("ALLOW_EDIT_OWN_MESSAGE", $this->arParams))
				$this->feed->setEditOwn($this->arParams["ALLOW_EDIT_OWN_MESSAGE"] == "ALL" ||
					$this->arParams["ALLOW_EDIT_OWN_MESSAGE"] === "LAST");
			$this->arParams["ALLOW_EDIT_OWN_MESSAGE"] = $this->feed->canEditOwn() ? "ALL" : "N";

			if (!$this->errorCollection->hasErrors() && $this->feed->canRead())
			{
				$this->bindObjects();
				$this->prepareParams();

				foreach (GetModuleEvents('forum', 'OnCommentsInit', true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(&$this));

				if ($this->arParams["CHECK_ACTIONS"] != "N" && !$this->checkPreview() && $this->checkActions() === false)
				{
					foreach (GetModuleEvents('forum', 'OnCommentError', true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array(&$this));
				}

				ob_start();

				$this->__includeComponent();

				$output = ob_get_clean();

				foreach (GetModuleEvents('forum', 'OnCommentsDisplayTemplate', true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(&$output, $this->arParams, $this->arResult));

				echo $output;
			}
			else
			{
				$this->showError();
			}
			if (IsModuleInstalled("disk"))
			{
				\Bitrix\Disk\Internals\Diag::getInstance()->logDebugInfo($this->componentId, $this->getName());
			}
		}
		catch(Exception $e)
		{
			$this->handleException($e);
		}
	}

	protected function checkRequiredParams()
	{
		if (!CModule::IncludeModule("forum"))
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_NO_MODULE'), self::ERROR_REQUIRED_PARAMETER)));
		elseif (intval($this->arParams["FORUM_ID"]) <= 0)
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_FID_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));
		elseif (empty($this->arParams["ENTITY_TYPE"]))
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_ENT_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));
		elseif (strlen(trim($this->arParams["ENTITY_TYPE"])) !== 2 )
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_ENT_INVALID'), self::ERROR_REQUIRED_PARAMETER)));
		// TODO allow to skip XML_ID
		elseif (empty($this->arParams["ENTITY_XML_ID"]) || (intval($this->arParams['ENTITY_ID']) <= 0 && $this->arParams['ENTITY_ID'] !== 0))
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_EID_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));

		$this->arParams["NAME_TEMPLATE"] = empty($this->arParams["NAME_TEMPLATE"]) ? \CSite::GetNameFormat() : $this->arParams["NAME_TEMPLATE"];
		$this->arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "", $this->arParams["NAME_TEMPLATE"]);
	}

	protected function prepareParams()
	{
		$this->arParams["SHOW_LOGIN"] = ($this->arParams["SHOW_LOGIN"] == "N" ? "N" : "Y");
		if (!array_key_exists("USE_CAPTCHA", $this->arParams))
			$this->arParams["USE_CAPTCHA"] = $this->forum["USE_CAPTCHA"];
		if ($this->arParams["USE_CAPTCHA"] == "Y" && !$this->getUser()->IsAuthorized())
		{

			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$this->captcha = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (strlen($captchaPass) <= 0)
			{
				$captchaPass = randString(10);
				COption::SetOptionString("main", "captcha_password", $captchaPass);
			}
		}
		AddEventHandler("forum", "OnAfterCommentTopicAdd", array(&$this, "readTopic"));
		if ($this->arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" && $this->getUser()->IsAuthorized())
		{
			AddEventHandler("forum", "OnAfterCommentTopicAdd", array(&$this, "subscribeAuthor"));
		}
		if (in_array($this->arParams["ALLOW_UPLOAD"], array("A", "Y", "F", "N", "I")))
		{
			$this->feed->setForumFields(array(
				"ALLOW_UPLOAD" => ($this->arParams["ALLOW_UPLOAD"] == "I" ? "Y" : $this->arParams["ALLOW_UPLOAD"]),
				"ALLOW_UPLOAD_EXT" => $this->arParams["ALLOW_UPLOAD_EXT"]
			));
		}
		$this->arResult["ERROR_MESSAGE"] = "";
		$this->arResult["OK_MESSAGE"] = "";
		if ($this->request["result"] == "reply")
			$this->arResult["OK_MESSAGE"] = Loc::getMessage("COMM_COMMENT_OK");
		else if ($this->request["result"] == "not_approved")
			$this->arResult["OK_MESSAGE"] = Loc::getMessage("COMM_COMMENT_OK_AND_NOT_APPROVED");
		unset($_GET["result"]);
		unset($GLOBALS["HTTP_GET_VARS"]["result"]);
		$this->arParams["AJAX_MODE"] = $this->isAjaxRequest() ? "Y" : "N";
		$this->arParams["index"] = $this->componentId;
		$this->arParams["COMPONENT_ID"] = $this->componentId;
		return $this;
	}

	public function subscribeAuthor($type, $id, $tid)
	{
		if ($this->feed->getEntity()->getType() == $type && $this->feed->getEntity()->getId() == $id)
		{
			CForumSubscribe::Add(array(
				"USER_ID" => $this->getUser()->getId(),
				"FORUM_ID" => $this->arParams["FORUM_ID"],
				"SITE_ID" => SITE_ID,
				"TOPIC_ID" => $tid,
				"NEW_TOPIC_ONLY" => "N")
			);
			BXClearCache(true, "/bitrix/forum/user/".$this->getUser()->getId()."/subscribe/");
		}
	}

	public function readTopic($type, $id, $tid)
	{
		if ($this->feed->getEntity()->getType() == $type && $this->feed->getEntity()->getId() == $id)
		{
			ForumSetReadTopic($this->arParams["FORUM_ID"], $tid);
		}
	}

	/**
	 * Returns whether this is an AJAX (XMLHttpRequest) request.
	 * @return boolean
	 */
	protected function isAjaxRequest()
	{
		return
			$this->request['AJAX_MODE'] == "Y" ||
			isset($_SERVER["HTTP_BX_AJAX"]) ||
			isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	private function bindObjects()
	{
		$path = dirname(__FILE__);
		include_once($path."/files_input.php");
		$this->arResult["objFiles"] = new CCommentFiles($this);

		include_once($path."/ufs.php");
		$this->arResult["objUFs"] = new CCommentUFs($this);

		$this->arResult["objRating"] = false;
		if ($this->arParams["SHOW_RATING"] == "Y")
		{
			include_once($path."/ratings.php");
			$this->arResult["objRating"] = new CCommentRatings($this);
		}
	}

	private function checkCaptcha()
	{
		if (is_object($this->captcha))
		{
			$code = $this->request->getPost("captcha_code");
			$word = $this->request->getPost("captcha_word");

			if (strlen($code) > 0 && !$this->captcha->CheckCodeCrypt($word, $code, COption::GetOptionString("main", "captcha_password", "")) ||
				strlen($code) <= 0 && !$this->captcha->CheckCode($word, 0))
			{
				return false;
			}
		}
		return true;
	}

	private function checkPreview()
	{
		if ($this->request->getPost("preview_comment") == "VIEW" || $this->request->getPost("preview_comment") == "Y")
		{
			$post = array_merge($this->request->getQueryList()->toArray(), $this->request->getPostList()->toArray());

			$this->arResult["MESSAGE_VIEW"] = array(
				"POST_MESSAGE" => $post["REVIEW_TEXT"],
				"USE_SMILES" => $post["REVIEW_USE_SMILES"],
				"AUTHOR_ID" => $this->getUser()->getId(),
				"FILES" => array());
			foreach (GetModuleEvents('forum', 'OnCommentPreview', true) as $arEvent)
				ExecuteModuleEventEx($arEvent);
			if (is_array($this->arResult['ERROR']))
			{
				foreach ($this->arResult['ERROR'] as $res)
					$this->arResult['ERROR_MESSAGE'] .= (empty($res["title"]) ? $res["code"] : $res["title"]);
			}
			return true;
		}
		return false;
	}

	private function checkActions()
	{
		if ($this->request["ENTITY_XML_ID"] !== $this->feed->getEntity()->getXmlId())
		{
			return null;
		}
		$post = array_merge($this->request->getQueryList()->toArray(), $this->request->getPostList()->toArray());
		$action = strtolower($post["comment_review"] == "Y" ? (strtolower($post['REVIEW_ACTION']) == "edit" ? "edit" : "add") : $post['REVIEW_ACTION']);
		if (!in_array($action, array("add", 'del', 'hide', 'show', 'edit')))
		{
			return null;
		}

		$actionErrors = new ErrorCollection();
		$arPost = array();
		if (!check_bitrix_sessid())
		{
			$actionErrors->addOne(new Error(Loc::getMessage("F_ERR_SESSION_TIME_IS_UP"), self::ERROR_ACTION));
		}
		else if (!$this->checkCaptcha($actionErrors))
		{
			$actionErrors->addOne(new Error(Loc::getMessage("POSTM_CAPTCHA"), self::ERROR_ACTION));
		}
		else
		{
			if ($post["AJAX_POST"] == "Y")
				CUtil::decodeURIComponent($post);

			if ($action == "add" || $action == "edit")
			{
				$arPost = array(
					"POST_MESSAGE" => $post["REVIEW_TEXT"],
					"AUTHOR_NAME" => ($this->getUser()->isAuthorized() ? $this->getUserName() : (empty($post["REVIEW_AUTHOR"]) ? $GLOBALS["FORUM_STATUS_NAME"]["guest"] : $post["REVIEW_AUTHOR"])),
					"AUTHOR_EMAIL" => $post["REVIEW_EMAIL"],
					"USE_SMILES" => $post["REVIEW_USE_SMILES"]
				);

				foreach (GetModuleEvents('forum', 'OnCommentAdd', true) as $arEvent) // add custom data from $_REQUEST to arElement, validate here
				{
					if (ExecuteModuleEventEx($arEvent, array($this->feed->getEntity()->getType(), $this->feed->getEntity()->getId(), &$arPost)) === false)
					{
						$actionErrors->addOne(new Error((isset($arPost['ERROR']) ? $arPost['ERROR'] : Loc::getMessage("F_ERR_DURING_ACTIONS").print_r($arEvent, true)), self::ERROR_ACTION));
					}
				}
			}
		}
		if (!$actionErrors->hasErrors())
		{
			if ($action == "add" || $action == "edit")
			{
				$message = ($action == "add" ? $this->feed->add($arPost) : $this->feed->edit($this->request["MID"], $arPost));
				if ($message && $this->request["TOPIC_SUBSCRIBE"] == "Y")
				{
					ForumSubscribeNewMessagesEx($this->arParams["FORUM_ID"], $message["TOPIC_ID"], "N", $strErrorMessage, $strOKMessage);
					BXClearCache(true, "/bitrix/forum/user/".$this->getUser()->getId()."/subscribe/");
				}
			}
			elseif ($action == "show" || $action == "hide")
			{
				$message = $this->feed->moderate($this->request["MID"], $action == "show");
			}
			else
			{
				$message = $this->feed->delete($this->request["MID"]);
			}

			if ($this->feed->hasErrors())
			{
				$actionErrors->add($this->feed->getErrors());
			}
			else if ($this->request["NOREDIRECT"] != "Y" && !$this->isAjaxRequest())
			{
				$strURL = $this->request["back_page"] ?: $this->getApplication()->GetCurPageParam("", array("MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result", "sessid", "bxajaxid"));
				$strURL = ForumAddPageParams($strURL, array("MID" => $message["ID"], "result" => ($message["APPROVED"] == "Y" ? "reply" : "not_approved")));
				LocalRedirect($strURL);
			}
			else
			{
				$this->arResult['RESULT'] = $message["ID"];
				if ($action == "add")
					$this->arResult['OK_MESSAGE'] = ($message["APPROVED"] == "Y" ? GetMessage("COMM_COMMENT_OK") : GetMessage("COMM_COMMENT_OK_AND_NOT_APPROVED"));
				else if ($action == "edit")
					$this->arResult['OK_MESSAGE'] = Loc::getMessage("COMM_COMMENT_UPDATED");
				else if ($action == "show")
					$this->arResult['OK_MESSAGE'] = Loc::getMessage("COMM_COMMENT_SHOWN");
				else if ($action == "hide")
					$this->arResult['OK_MESSAGE'] = Loc::getMessage("COMM_COMMENT_HIDDEN");
				else
					$this->arResult['OK_MESSAGE'] = Loc::getMessage("COMM_COMMENT_DELETED");
			}
		}
		if ($actionErrors->hasErrors())
		{
			/** @var $error Error */
			$this->arResult["RESULT"] = false;
			$this->arResult["OK_MESSAGE"] = '';
			foreach ($actionErrors->toArray() as $error)
			{
				$this->arResult['ERROR_MESSAGE'] .= $error->getMessage();
			}
			return false;
		}
		return true;
	}

	public function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	public function getUser()
	{
		global $USER;
		return $USER;
	}

	private static function getUserFromForum($userId)
	{
		if ($userId > 0 && !array_key_exists($userId, self::$users))
		{
			self::$users[$userId] = CForumUser::GetListEx(array(), array("USER_ID" => $userId))->fetch();
		}
		return self::$users[$userId];
	}
	private function getUserName()
	{
		$user = self::getUserFromForum($this->getUser()->getId());
		$sName = "";
		if (is_array($user) && $user["SHOW_NAME"] == "Y")
		{
			$sName = ($user["SHOW_NAME"] == "Y" ? trim(CUser::FormatName($this->arParams["NAME_TEMPLATE"], $user, true, false)) : "");
			$sName = (empty($sName) ? trim($user["LOGIN"]) : $sName);
		}
		return $sName;
	}
}