<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Forum\Internals\Error\Error;
use Bitrix\Forum\Internals\Error\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Forum;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config;

Loc::loadMessages(__FILE__);

final class ForumCommentsComponent extends CBitrixComponent implements Main\Engine\Contract\Controllerable
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
	/** @var Forum\Comments\Feed */
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

		Main\Loader::includeModule("forum");

		$this->componentId = $this->isAjaxRequest() ? randString(7) : $this->randString();
		$this->errorCollection = new ErrorCollection();

		$this->prepareMobileData = IsModuleInstalled("mobile");
		$this->scope = self::STATUS_SCOPE_WEB;
		if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 && defined("BX_MOBILE") && BX_MOBILE === true)
			$this->scope = self::STATUS_SCOPE_MOBILE;
		$this->changeTemplate();

		self::$index++;

		$this->changeTemplate();
	}

	protected function changeTemplate()
	{
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
				'data' => null,
				'errors' => $errors,
			));
		}
		else
		{
			$this->sendResponse(implode("", $errorsText));
		}
	}

	protected function handleException(\Exception $e)
	{
		if ($this->isAjaxRequest())
		{
			$this->sendJsonResponse(array(
				'status' => static::STATUS_ERROR,
				'data' => null,
				'errors' => [
					[
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
					]
				],
			));
		}
		else
		{
			$exceptionHandling = Config\Configuration::getValue("exception_handling");
			if($exceptionHandling["debug"])
			{
				throw $e;
			}
			else
			{
				ShowError($e->getMessage());
			}
		}
	}

	protected function end($terminate = true)
	{
		if ($terminate)
		{
			/** @noinspection PhpUndefinedClassInspection */
			CMain::finalActions();
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

	public function onPrepareComponentParams($arParams)
	{
		return $arParams;
	}

	public function executeComponent()
	{
		try
		{
			if (!Main\Loader::includeModule("forum"))
			{
				throw new Main\NotSupportedException(Loc::getMessage("F_NO_MODULE"));
			}

			$this->checkRequiredParams();
			$this->feed = new Forum\Comments\Feed(
				$this->arParams["FORUM_ID"],
				array(
					"type" => $this->arParams["ENTITY_TYPE"],
					"id" => $this->arParams["ENTITY_ID"],
					"xml_id" => $this->arParams["ENTITY_XML_ID"]
				),
				(isset($this->arParams["RECIPIENT_ID"]) ? intval($this->arParams["RECIPIENT_ID"]) : null)
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

				if (
					(
						$this->arParams["CHECK_ACTIONS"] != "N"
						&& !$this->checkPreview()
						&& $this->checkActions() === false
					)
					||
					$this->checkActionComponentAction() === false
				)
				{
					foreach (GetModuleEvents('forum', 'OnCommentError', true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array(&$this));
				}

				$this->arResult['UNREAD_MID'] = $this->feed->getUserUnreadMessageId();
				if (!isset($this->arParams['SKIP_USER_READ']) || $this->arParams['SKIP_USER_READ'] !== 'Y')
				{
					$this->feed->setUserAsRead();
				}
				if ($this->arParams['SET_LAST_VISIT'] == "Y")
				{
					$this->feed->setUserLocation();
				}

				ob_start();

				$this->__includeComponent();

				$output = ob_get_clean();

				foreach (GetModuleEvents('forum', 'OnCommentsDisplayTemplate', true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(&$output, $this->arParams, $this->arResult));

				$this->unbindObjects();

				echo $output;
			}
			else
			{
				$this->showError();
			}
		}
		catch(\Exception $e)
		{
			$this->handleException($e);
		}
	}

	protected function checkRequiredParams()
	{
		if (intval($this->arParams["FORUM_ID"]) <= 0)
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_FID_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));
		elseif (empty($this->arParams["ENTITY_TYPE"]))
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_ENT_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));
		elseif (mb_strlen(trim($this->arParams["ENTITY_TYPE"])) !== 2 )
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_ENT_INVALID'), self::ERROR_REQUIRED_PARAMETER)));
		// TODO allow to skip XML_ID
		elseif (empty($this->arParams["ENTITY_XML_ID"]) || (intval($this->arParams['ENTITY_ID']) <= 0 && $this->arParams['ENTITY_ID'] !== 0))
			$this->errorCollection->add(array(new Error(Loc::getMessage('F_ERR_EID_EMPTY'), self::ERROR_REQUIRED_PARAMETER)));

		$this->arParams["NAME_TEMPLATE"] = empty($this->arParams["NAME_TEMPLATE"]) ? \CSite::GetNameFormat() : $this->arParams["NAME_TEMPLATE"];
		$this->arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "", $this->arParams["NAME_TEMPLATE"]);
		$this->arParams["URL"] = $this->arParams["URL"] <> '' ? $this->arParams["URL"] : $this->getApplication()->GetCurPageParam("", ["IFRAME", "IFRAME_TYPE"]);
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
			if ($captchaPass == '')
			{
				$captchaPass = randString(10);
				COption::SetOptionString("main", "captcha_password", $captchaPass);
			}
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

	private function subscribeAuthor($tid)
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
		$this->arResult["FORUM_TOPIC_ID"] = ($topic = $this->feed->getTopic()) ? $topic["ID"] : 0;
		$forum = $this->feed->getForum();
		$this->arParams["ALLOW_UPLOAD"] = $this->arParams["ALLOW_UPLOAD"] ?? $forum["ALLOW_UPLOAD"];
		$this->arParams["ALLOW_UPLOAD_EXT"] = $this->arParams["ALLOW_UPLOAD_EXT"] ?? $forum["ALLOW_UPLOAD_EXT"];
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

	private function unbindObjects()
	{
		foreach (GetModuleEvents("forum", "OnCommentsFinished", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(&$this));
		}
		unset($this->arResult["objFiles"]);
		unset($this->arResult["objUFs"]);
		unset($this->arResult["objRating"]);
	}

	private function checkCaptcha()
	{
		if (is_object($this->captcha))
		{
			$code = $this->request->getPost("captcha_code");
			$word = $this->request->getPost("captcha_word");

			if ($code <> '' && !$this->captcha->CheckCodeCrypt($word, $code, COption::GetOptionString("main", "captcha_password", "")) ||
				$code == '' && !$this->captcha->CheckCode($word, 0))
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
				ExecuteModuleEventEx($arEvent, [$this]);
			if (is_array($this->arResult['ERROR']))
			{
				foreach ($this->arResult['ERROR'] as $res)
					$this->arResult['ERROR_MESSAGE'] .= (empty($res["title"]) ? $res["code"] : $res["title"]);
			}
			return true;
		}
		return false;
	}

	private function checkActionComponentAction()
	{
		if ((mb_strtolower($this->arParams["ACTION"])) === "send" && $this->arParams["MID"] > 0)
		{
			$this->arResult["ACTION"] = "reply";
			$this->arResult["RESULT"] = (int) $this->arParams["MID"];
			$this->arResult["MODE"] = "PULL_MESSAGE";
			$this->arParams["COMPONENT_AJAX"] = "Y";
			$this->arResult["PUSH&PULL"] = [
				"ID" => (int) $this->arParams["MID"],
				"ACTION" => "reply"
			];
		}
		return null;
	}

	private function checkActions()
	{
		if ($this->request["ENTITY_XML_ID"] !== $this->feed->getEntity()->getXmlId())
		{
			return null;
		}
		$post = array_merge($this->request->getQueryList()->toArray(), $this->request->getPostList()->toArray());

		if ($this->arParams["COMPONENT_AJAX"] === "Y")
		{
			$action = $this->request->getPost("ACTION");
			if ($action === "DELETE")
			{
				$action = "del";
			}
			$mid = $this->request->getPost("ID");
		}
		else
		{
			$mid = $this->request->get("MID");
			if ($post["comment_review"] == "Y")
			{
				$action = mb_strtolower($post["REVIEW_ACTION"]) == "edit" ? "edit" : "add";
			}
			else
			{
				$action = ($this->request->get("REVIEW_ACTION") ?: $this->request->get("ACTION"));
			}
		}

		$action = (is_string($action)? mb_strtolower($action) : $action);

		if (!in_array($action, array("add", "del", "hide", "show", "edit")))
		{
			return null;
		}

		$this->arResult["ACTION"] = $action;
		$this->arResult["RESULT"] = $mid;

		$actionErrors = new ErrorCollection();
		$arPost = array();
		if (!check_bitrix_sessid())
		{
			$actionErrors->addOne(new Error(Loc::getMessage("F_ERR_SESSION_TIME_IS_UP"), self::ERROR_ACTION));
		}
		else if (!$this->checkCaptcha())
		{
			$actionErrors->addOne(new Error(Loc::getMessage("POSTM_CAPTCHA"), self::ERROR_ACTION));
		}
		else
		{
			if ($post["AJAX_POST"] == "Y" && $this->arParams["COMPONENT_AJAX"] !== "Y")
				CUtil::decodeURIComponent($post);

			if ($action == "add" || $action == "edit")
			{
				$arPost = array(
					"POST_MESSAGE" => $post["REVIEW_TEXT"],
					"AUTHOR_NAME" => ($this->getUser()->isAuthorized() ? $this->getUserName() : (empty($post["REVIEW_AUTHOR"]) ? $GLOBALS["FORUM_STATUS_NAME"]["guest"] : $post["REVIEW_AUTHOR"])),
					"AUTHOR_EMAIL" => $post["REVIEW_EMAIL"],
					"USE_SMILES" => $post["REVIEW_USE_SMILES"]
				);

				foreach (GetModuleEvents('forum', 'OnCommentSave', true) as $arEvent) // add custom data from $_REQUEST to arElement, validate here
				{
					if (ExecuteModuleEventEx($arEvent, array(
						$this->feed->getEntity()->getType(),
							$this->feed->getEntity()->getId(),
							$mid,
							&$arPost)) === false
					)
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
				$message = ($action == "add" ? $this->feed->add($arPost) : $this->feed->edit($mid, $arPost));
				if ($message)
				{
					if ($action == "add")
					{
						$this->feed->setUserAsRead();
						if ($this->arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" && $this->getUser()->IsAuthorized())
						{
							$this->subscribeAuthor($message["TOPIC_ID"]);
						}
					}
					if ($this->request["TOPIC_SUBSCRIBE"] == "Y")
					{
						ForumSubscribeNewMessagesEx($this->arParams["FORUM_ID"], $message["TOPIC_ID"], "N", $strErrorMessage, $strOKMessage);
						BXClearCache(true, "/bitrix/forum/user/".$this->getUser()->getId()."/subscribe/");
					}
				}
			}
			elseif ($action == "show" || $action == "hide")
			{
				$message = $this->feed->moderate($mid, $action == "show");
			}
			else
			{
				$message = $this->feed->delete($mid);
			}

			if ($this->feed->hasErrors())
			{
				$actionErrors->add($this->feed->getErrors());
			}
			else if ($this->request["NOREDIRECT"] != "Y" && !$this->isAjaxRequest())
			{
				$strURL = $this->request["back_page"] ?: $this->getApplication()->GetCurPageParam("", array("MID", "ID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result", "sessid", "bxajaxid"));
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
	public function configureActions()
	{
		return [];
	}
	protected function listKeysSignedParameters()
	{
		return [
			"FORUM_ID",
			"ENTITY_TYPE",
			"ENTITY_ID",
			"ENTITY_XML_ID",
			"RECIPIENT_ID",
			"PERMISSION",
			"USER_FIELDS",
			"USER_FIELDS_SETTINGS",

			"URL_TEMPLATES_READ",
			"URL_TEMPLATES_PROFILE_VIEW",
			"MESSAGES_PER_PAGE",
			"PAGE_NAVIGATION_TEMPLATE",
			"PREORDER",
			"PUBLIC_MODE",

			"DATE_TIME_FORMAT",
			"NAME_TEMPLATE",

			"IMAGE_SIZE",
			"IMAGE_HTML_SIZE",
			"EDITOR_CODE_DEFAULT",
			"ALLOW_MENTION",

			"SUBSCRIBE_AUTHOR_ELEMENT",
			"SHOW_RATING",
			"RATING_TYPE",
			"SET_LAST_VISIT",
//			"SHOW_MINIMIZED",
//			"USE_CAPTCHA",
		];
	}

	public function navigateCommentAction()
	{
		$this->arParams["COMPONENT_AJAX"] = "Y";
		$this->arParams["URL"] = $_SERVER["HTTP_REFERER"];
		if ($this->request->getPost("scope") &&
			$this->scope !== $this->request->getPost("scope")
		)
		{
			$this->scope = $this->request->getPost("scope");
			$this->changeTemplate();
		}

		$this->executeComponent();
	}

	public function getCommentAction()
	{
		$this->arParams["COMPONENT_AJAX"] = "Y";
		$this->arParams["URL"] = $_SERVER["HTTP_REFERER"];
		if ($this->request->getPost("scope") &&
			$this->scope !== $this->request->getPost("scope")
		)
		{
			$this->scope = $this->request->getPost("scope");
			$this->changeTemplate();
		}
		$this->executeComponent();
	}

	public function processCommentAction()
	{
		$this->arParams["COMPONENT_AJAX"] = "Y";
		$this->arParams["URL"] = $_SERVER["HTTP_REFERER"];
		$this->executeComponent();
	}

	public function readCommentAction()
	{
		$this->arParams["COMPONENT_AJAX"] = "Y";

		$this->checkRequiredParams();
		$this->feed = new Forum\Comments\Feed(
			$this->arParams["FORUM_ID"],
			array(
				"type" => $this->arParams["ENTITY_TYPE"],
				"id" => $this->arParams["ENTITY_ID"],
				"xml_id" => $this->arParams["ENTITY_XML_ID"]
			),
			(isset($this->arParams["RECIPIENT_ID"]) ? intval($this->arParams["RECIPIENT_ID"]) : null)
		);

		$this->forum = $this->feed->getForum();
		if (array_key_exists("PERMISSION", $this->arParams))
		{
			$this->feed->setPermission($this->arParams["PERMISSION"]);
		}

		if ($this->feed->canRead())
		{
			$this->feed->setUserAsRead();
		}
	}
}
