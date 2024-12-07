<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\AI;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\Json;
use Bitrix\Blog\Item\Permissions;

final class SocialnetworkBlogPostComment extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
	protected const STATUS_SCOPE_MOBILE = 'mobile';
	protected const STATUS_SCOPE_WEB = 'web';

	protected const STATUS_SUCCESS = 'success';
	protected const STATUS_DENIED = 'denied';
	protected const STATUS_ERROR = 'error';

	private $scope;
	public $prepareMobileData;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->prepareMobileData = ModuleManager::isModuleInstalled('mobile');
		$this->scope = self::STATUS_SCOPE_WEB;

		if (
			is_callable([ '\Bitrix\MobileApp\Mobile', 'getApiVersion' ])
			&& \Bitrix\MobileApp\Mobile::getApiVersion() >= 1
			&& defined('BX_MOBILE')
			&& BX_MOBILE === true
		)
		{
			$this->scope = self::STATUS_SCOPE_MOBILE;
		}

		$this->changeTemplate();
	}

	protected function changeTemplate()
	{
		$templateName = $this->getTemplateName();

		if (
			empty($templateName)
			|| $templateName === '.default'
			|| $templateName === 'bitrix24'
		)
		{
			$this->setTemplateName($this->isWeb() ? '.default' : 'mobile_app');
		}
	}

	public function configureActions(): array
	{
		return [];
	}

	public function isWeb(): bool
	{
		return ($this->scope === self::STATUS_SCOPE_WEB);
	}

	public function prepareData(&$result): void
	{
		$result['FORM_ID'] = $this->arParams['FORM_ID'];

		$this->prepareUrls($result);
	}

	protected function prepareUrls(&$result): void
	{
		if ($this->prepareMobileData)
		{
			$url = SITE_DIR . 'mobile/log/index.php';
			$url = (new \Bitrix\Main\Web\Uri($url))->addParams([
				'ACTION' => 'GET',
				'detail_log_id' => (int)$this->arParams['LOG_ID'],
				'comment_post_id' => (int)$this->arParams['ID'],
			]);

			$result['urlMobileToPost'] = $url . '#LAST_LOG_TS#';
			$result['urlMobileToComment'] = $url . '&' . $this->arParams['COMMENT_ID_VAR'] . '=#comment_id#';
			$result['urlMobileToDelete'] = $url . '&delete_comment_id=#comment_id#';
			$result['urlMobileToHide'] = $url . '&hide_comment_id=#comment_id#';
			$result['urlMobileToShow'] = $url . '&show_comment_id=#comment_id#';
		}
	}

	protected function listKeysSignedParameters(): array
	{
		return [
			'ID',
			'LOG_ID',
			'bPublicPage',
			'bFromList',
			'mfi',
			'ENTITY_XML_ID',
			'CAN_USER_COMMENT',
			'COMMENT_PROPERTY',
			'MOBILE',
			'LAZYLOAD',
			'FOLLOW',
			'PATH_TO_BLOG',
			'PATH_TO_POST',
			'PATH_TO_POST_CURRENT',
			'PATH_TO_USER',
			'~PATH_TO_USER',
			'PATH_TO_SMILE',
			'PATH_TO_CONPANY_DEPARTMENT',
			'ALLOW_POST_CODE',
			'SOCNET_GROUP_ID',
			'COMMENT_ID_VAR',
			'IMAGE_MAX_WIDTH',
			'IMAGE_MAX_HEIGHT',
			'IMAGE_SIZE',
			'ATTACHED_IMAGE_MAX_WIDTH_SMALL',
			'ATTACHED_IMAGE_MAX_HEIGHT_SMALL',
			'ATTACHED_IMAGE_MAX_WIDTH_FULL',
			'ATTACHED_IMAGE_MAX_HEIGHT_FULL',
			'AVATAR_SIZE',
			'AVATAR_SIZE_COMMON',
			'AVATAR_SIZE_COMMENT',
			'USER_ID',
			'CREATED_BY_ID',
			'NAME_TEMPLATE',
			'SHOW_LOGIN',
			'bPublicPage',
			'BLOG_URL',
			'GROUP_ID',
			'CACHE_TYPE',
			'CACHE_TIME',
			'BLOG_VAR',
			'PAGE_VAR',
			'USER_VAR',
			'POST_VAR',
			'NAV_PAGE_VAR',
			'NAV_PAGE_VAR',
			'COMMENT_ID_VAR',
			'LAST_LOG_TS',
			'MARK_NEW_COMMENTS',
			'COMMENTS_COUNT',
			'USE_ASC_PAGING',
			'USE_DESC_PAGING',
			'NAV_TYPE_NEW',
			'DATE_TIME_FORMAT',
			'DATE_TIME_FORMAT_WITHOUT_YEAR',
			'DATE_TIME_FORMAT_S',
			'TIME_FORMAT',
			'DATE_FORMAT',
			'SHOW_RATING',
			'SEF',
			'ALLOW_VIDEO',
			'ALLOW_IMAGE_UPLOAD',
			'PAGE_SIZE',
			'PAGE_SIZE_MIN',
			'NO_URL_IN_COMMENTS',
			'NO_URL_IN_COMMENTS_AUTHORITY',
			'NO_URL_IN_COMMENTS_AUTHORITY_CHECK',
		];
	}

	public function navigateCommentAction(): void
	{
		$this->arParams['COMPONENT_AJAX'] = 'Y';
		$this->arParams['URL'] = $_SERVER['HTTP_REFERER'];

		if (
			$this->request->getPost('scope')
			&& $this->scope !== $this->request->getPost('scope')
		)
		{
			$this->scope = $this->request->getPost('scope');
			$this->changeTemplate();
		}

		$this->executeComponent();
	}

	public function processCommentAction(): void
	{
		$this->arParams['COMPONENT_AJAX'] = 'Y';
		$this->executeComponent();
	}

	public function onPrepareComponentParams($arParams)
	{
		global $APPLICATION;

		if (!Loader::includeModule('socialnetwork'))
		{
			return [];
		}

		static $formId = null;

		if (empty($arParams['FORM_ID']))
		{
			if ($formId === null)
			{
				$formId = 'blogCommentForm' . Random::getString(4);
			}
			$arParams['FORM_ID'] = $formId;
		}

		$arParams['SOCNET_GROUP_ID'] = (int) ($arParams['SOCNET_GROUP_ID'] ?? 0);

		$arParams['ID'] = (
			preg_match('/^[1-9][0-9]*$/', trim($arParams['ID']))
				? (int)$arParams['ID']
				: preg_replace('/[^a-zA-Z0-9_-]/i', '', $arParams['ID'])
		);

		$arParams['BLOG_URL'] = preg_replace('/[^a-zA-Z0-9_-]/i', '', ($arParams['BLOG_URL'] ?? ''));
		$arParams["GROUP_ID"] = $arParams["GROUP_ID"] ?? [];
		if (!is_array($arParams['GROUP_ID']))
		{
			$arParams['GROUP_ID'] = [ $arParams['GROUP_ID'] ];
		}

		foreach ($arParams['GROUP_ID'] as $key => $value)
		{
			if ((int)$value <= 0)
			{
				unset($arParams['GROUP_ID'][$key]);
			}
		}

		$arParams['CACHE_TIME'] = (
			$arParams['CACHE_TYPE'] === 'Y'
			|| (
				$arParams['CACHE_TYPE'] === 'A'
				&& Config\Option::get('main', 'component_cache_on', 'Y') === 'Y'
			)
				? (int)$arParams['CACHE_TIME']
				: 0
		);

		$arParams["BLOG_VAR"] = $arParams["BLOG_VAR"] ?? '';
		$arParams["PAGE_VAR"] = $arParams["PAGE_VAR"] ?? '';
		$arParams["USER_VAR"] = $arParams["USER_VAR"] ?? '';
		$arParams["POST_VAR"] = $arParams["POST_VAR"] ?? '';
		$arParams["NAV_PAGE_VAR"] = $arParams["NAV_PAGE_VAR"] ?? '';
		$arParams["COMMENT_ID_VAR"] = $arParams["COMMENT_ID_VAR"] ?? '';

		if ($arParams["BLOG_VAR"] == '')
		{
			$arParams["BLOG_VAR"] = "blog";
		}
		if ($arParams["PAGE_VAR"] == '')
		{
			$arParams["PAGE_VAR"] = "page";
		}
		if ($arParams["USER_VAR"] == '')
		{
			$arParams["USER_VAR"] = "id";
		}
		if ($arParams["POST_VAR"] == '')
		{
			$arParams["POST_VAR"] = "id";
		}
		if ($arParams["NAV_PAGE_VAR"] == '')
		{
			$arParams["NAV_PAGE_VAR"] = "pagen";
		}
		if ($arParams["COMMENT_ID_VAR"] == '')
		{
			$arParams["COMMENT_ID_VAR"] = "commentId";
		}

		$tzOffset = \CTimeZone::GetOffset();

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if ((int)$request->get('LAST_LOG_TS') > 0)
		{
			$timeZoneOffset = (
				$request->get('AJAX_CALL') === 'Y'
				|| $request->get('empty_get_comments') === 'Y'
					? $tzOffset
					: 0
			);

			$arParams["LAST_LOG_TS"] = (int)$request->get('LAST_LOG_TS') + $timeZoneOffset; // next mobile livefeed page or get_empty_comments
			if ($arParams['MOBILE'] !== 'Y')
			{
				$arParams['MARK_NEW_COMMENTS'] = 'Y';
			}
		}

		if ((int)$arParams['COMMENTS_COUNT'] <= 0)
		{
			$arParams['COMMENTS_COUNT'] = 25;
		}

		if (($arParams['USE_ASC_PAGING'] ?? null) !== 'Y')
		{
			$arParams['USE_DESC_PAGING'] = 'Y';
		}

		$applicationPage = $APPLICATION->GetCurPage();

		$arParams['PATH_TO_BLOG'] = trim($arParams['PATH_TO_BLOG'] ?? '');
		if ($arParams['PATH_TO_BLOG'] === '')
		{
			$arParams['PATH_TO_BLOG'] = htmlspecialcharsbx($applicationPage . "?" . $arParams["PAGE_VAR"] . "=blog&" . $arParams["BLOG_VAR"] . "=#blog#");
		}

		$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"] ?? '');
		if ($arParams["PATH_TO_USER"] === '')
		{
			$arParams["PATH_TO_USER"] = htmlspecialcharsbx($applicationPage . "?" . $arParams["PAGE_VAR"] . "=user&" . $arParams["USER_VAR"] . "=#user_id#");
		}

		$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"] ?? '');
		if ($arParams["PATH_TO_POST"] === '')
		{
			$arParams["PATH_TO_POST"] = htmlspecialcharsbx($applicationPage . "?" . $arParams["PAGE_VAR"] . "=post&" . $arParams["BLOG_VAR"] . "=#blog#" . "&" . $arParams["POST_VAR"] . "=#post_id#");
		}

		$arParams["PATH_TO_POST_CURRENT"] = $arParams["PATH_TO_POST"];

		if ($arParams["bPublicPage"] ?? null)
		{
			$arParams["PATH_TO_POST"] = \Bitrix\Socialnetwork\Helper\Path::get('userblogpost_page');
		}

		$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"] ?? '');
		if ($arParams["PATH_TO_SMILE"] === '')
		{
			$arParams["PATH_TO_SMILE"] = false;
		}

		if (!isset($arParams["PATH_TO_CONPANY_DEPARTMENT"]) || $arParams["PATH_TO_CONPANY_DEPARTMENT"] == "")
		{
			$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
		}
		if (!isset($arParams["PATH_TO_MESSAGES_CHAT"]) || $arParams["PATH_TO_MESSAGES_CHAT"] == "")
		{
			$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
		}
		if (!isset($arParams["PATH_TO_VIDEO_CALL"]) || $arParams["PATH_TO_VIDEO_CALL"] == "")
		{
			$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#user_id#/";
		}

		if (trim($arParams["NAME_TEMPLATE"] ?? '') == '')
		{
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		}

		$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] !== "N" ? "Y" : "N";
		$arParams["IMAGE_MAX_WIDTH"] = (int)$arParams["IMAGE_MAX_WIDTH"];
		$arParams["IMAGE_MAX_HEIGHT"] = (int)$arParams["IMAGE_MAX_HEIGHT"];
		$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

		$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = $arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] ?? 0;
		$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = $arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] ?? 0;
		$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = $arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] ?? 0;
		$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = $arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] ?? 0;

		$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] = (
			(int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"] > 0
				? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_SMALL"]
				: 70
		);
		$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] = (
			(int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"] > 0
				? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_SMALL"]
				: 70
		);
		$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] = (
			(int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"] > 0
				? (int)$arParams["ATTACHED_IMAGE_MAX_WIDTH_FULL"]
				: 1000
		);
		$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] = (
			(int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"] > 0
				? (int)$arParams["ATTACHED_IMAGE_MAX_HEIGHT_FULL"]
				: 1000
		);

		$arParams["NAV_TYPE_NEW"] = (
			isset($arParams['NAV_TYPE_NEW'])
			&& $arParams['NAV_TYPE_NEW'] === 'Y'
				? 'Y'
				: 'N'
		);

		$arParams["DATE_TIME_FORMAT_S"] = $arParams["DATE_TIME_FORMAT"];

		\CSocNetLogComponent::processDateTimeFormatParams($arParams);
		\CRatingsComponentsMain::getShowRating($arParams);

		$arParams["SEF"] = (
			isset($arParams["SEF"])
			&& $arParams["SEF"] === "N"
				? "N"
				: "Y"
		);
		$arParams["CAN_USER_COMMENT"] = (
			!isset($arParams["CAN_USER_COMMENT"])
			|| $arParams["CAN_USER_COMMENT"] === 'Y'
				? 'Y'
				: 'N'
		);

		$arParams["ALLOW_VIDEO"] = (
			$arParams["ALLOW_VIDEO"] === "N"
				? "N"
				: "Y"
		);

		$arParams["PAGE_SIZE"] = (int) ($arParams["PAGE_SIZE"] ?? 0);
		if ($arParams["PAGE_SIZE"] <= 0)
		{
			$arParams["PAGE_SIZE"] = 20;
		}

		$arParams["PAGE_SIZE_MIN"] = 3;

		$arParams["COMMENT_PROPERTY"] = [ 'UF_BLOG_COMMENT_DOC' ];
		if (
			Loader::includeModule('webdav')
			|| Loader::includeModule('disk')
		)
		{
			$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FILE";
			$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMMENT_FH";
		}

		$arParams["COMMENT_PROPERTY"][] = "UF_BLOG_COMM_URL_PRV";

		return $arParams;
	}

	public function executeComponent()
	{
		try
		{
			$this->arResult['deleteCommentId'] = (int) ($_GET['delete_comment_id'] ?? 0);
			$this->arResult['hideCommentId'] = (int) ($_GET['hide_comment_id'] ?? 0);
			$this->arResult['showCommentId'] = (int) ($_GET['show_comment_id'] ?? 0);

			$this->checkActions();
			$this->fillParams();
			$this->prepareCopilotParams();

			return $this->__includeComponent();
		}
		catch (Exception $e)
		{
			$this->handleException($e);
		}
	}

	protected function prepareCopilotParams(): void
	{
		$this->arResult['IS_QUOTE_COPILOT_ENABLED'] = $this->isCopilotEnabled() && $this->isCopilotEnabledBySettings();
	}

	protected function isCopilotEnabled(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = AI\Engine::getByCategory(AI\Engine::CATEGORIES['text'], AI\Context::getFake());

		return !is_null($engine);
	}

	protected function isCopilotEnabledBySettings(): bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}

		return \Bitrix\Socialnetwork\Integration\AI\Settings::isTextAvailable();
	}

	/*
	 * ToDo: move action processing from the component there
	*/
	protected function checkActions(): void
	{
		if (
			isset($this->arParams['COMPONENT_AJAX'])
			&& $this->arParams['COMPONENT_AJAX'] === 'Y'
		)
		{
			$action = $this->request->getPost('ACTION');

			switch ($action)
			{
				case 'EDIT':
					$this->arParams['LAZYLOAD'] = 'N';
					break;
				case 'DELETE':
					$this->arResult['deleteCommentId'] = (int)$this->request->getPost('ID');
					break;
				case 'HIDE':
					$this->arResult['hideCommentId'] = (int)$this->request->getPost('ID');
					break;
				case 'SHOW':
					$this->arResult['showCommentId'] = (int)$this->request->getPost('ID');
					break;
				default:
			}
		}
		else
		{
			$action = '';

			if ($this->arResult['deleteCommentId'])
			{
				$action = 'DELETE';
				$this->arResult['deleteCommentId'] = (int)$this->request->get('delete_comment_id');
			}
			elseif ($this->arResult['hideCommentId'])
			{
				$action = 'HIDE';
				$this->arResult['hideCommentId'] = (int)$this->request->get('hide_comment_id');
			}
			elseif ($this->arResult['showCommentId'])
			{
				$action = 'SHOW';
				$this->arResult['showCommentId'] = (int)$this->request->get('show_comment_id');
			}
			elseif (
				$_SERVER['REQUEST_METHOD'] === "POST"
				&& isset($_POST['post'])
				&& (string)$_POST['post'] !== ''
			)
			{
				$action = ($_POST['act'] === 'edit' ? 'EDIT' : 'ADD');
			}

			if (!in_array($action, [ 'ADD', 'EDIT', 'DELETE', 'HIDE', 'SHOW' ]))
			{
				return;
			}

			if (!check_bitrix_sessid())
			{
				throw new Exception(Loc::getMessage('B_B_PC_MES_ERROR_SESSION', 'SESSION_EXPIRED'));
			}
		}
	}

	protected function fillParams(): void
	{
		$postId = ($_REQUEST["comment_post_id"] ?? 0);

		if (
			$postId
			|| (
				isset($this->arParams['COMPONENT_AJAX'])
				&& $this->arParams['COMPONENT_AJAX'] === 'Y'
			)
		)
		{
			if (!$postId && (int) $this->arParams['ID'] > 0)
			{
				$postId = (int) $this->arParams['ID'];
			}

			if (
				$postId > 0
				&& Loader::includeModule('blog')
			)
			{
				$this->arParams['ID'] = $postId;

				$arPost = \CBlogPost::getById($postId);
				$arPost = \CBlogTools::htmlspecialcharsExArray($arPost);
				$arBlog = \CBlog::getById($arPost['BLOG_ID']);
				$arBlog = \CBlogTools::htmlspecialcharsExArray($arBlog);

				$this->arParams['POST_DATA'] = $arPost;
				$this->arParams['BLOG_DATA'] = $arBlog;
			}
		}
	}

	protected function handleException(Exception $e): void
	{
		$logger = new \Bitrix\Socialnetwork\Log\Log();
		$logger->collect("Error. Reason: {$e->getMessage()}");

		if ($this->isAjaxRequest())
		{
			$this->sendJsonResponse([
				'status' => self::STATUS_ERROR,
				'data' => null,
				'errors' => [
					[
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
					],
				],
			]);
		}
		else
		{
			$exceptionHandling = Config\Configuration::getValue('exception_handling');
			if ($exceptionHandling['debug'])
			{
				throw $e;
			}
			else
			{
				ShowError($e->getMessage());
			}
		}
	}

	protected function sendJsonResponse($response): void
	{
		$this->getApplication()->restartBuffer();

		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode($response);

		$this->end();
	}

	public function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	protected function end($terminate = true): void
	{
		if ($terminate)
		{
			CMain::finalActions();
		}
	}

	protected function isAjaxRequest(): bool
	{
		return (
			(
				$this->request['AJAX_MODE'] === 'Y'
				|| isset($_SERVER["HTTP_BX_AJAX"])
				|| (
					isset($_SERVER['HTTP_X_REQUESTED_WITH'])
					&& $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
				)
			)
			&& $this->request['c'] === $this->getName()
		);
	}

	protected function getCommentsPerm(array $params = [], array &$arResult = []): void
	{
		// todo &$arResult ppc
		global $USER;

		$this->arResult['Perm'] = Permissions::DENY;

		$currentUserId = (int)($params['currentUserId'] ?? $USER->getId());
		$postId = (int)($params['postId'] ?? 0);
		$postAuthorId = (int)($params['postAuthorId'] ?? 0);
		$mobile = (bool)$params['mobile'];
		$postHasAllDestination = (bool)$params['postHasAllDestination'];

		if (
			$postId <= 0
			|| $postAuthorId <= 0
		)
		{
			return;
		}

		static $isIntranetInstalled = null;
		if ($isIntranetInstalled === null)
		{
			$isIntranetInstalled = ModuleManager::isModuleInstalled('intranet');
		}

		if (
			\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, !$mobile)
			|| \CMain::getGroupRight('blog') >= 'W'
		)
		{
			$this->arResult['Perm'] = $arResult['Perm'] = Permissions::FULL;
		}
		elseif ($isIntranetInstalled)
		{
			if ($postAuthorId === $currentUserId)
			{
				$this->arResult['Perm'] = $arResult['Perm'] = Permissions::FULL;
			}
			else
			{
				$this->arResult['Perm'] = $arResult['Perm'] = (
					$postHasAllDestination
						? Permissions::WRITE
						: \CBlogComment::getSocNetUserPermsNew($postId, $postAuthorId, $currentUserId, $permBySG)
				);
			}
		}
		else
		{
			$this->arResult['Perm'] = \CBlogComment::getSocNetUserPermsNew($postId, $postAuthorId, $currentUserId, $permBySG);
		}

		$this->arResult['PermBySG'] = $arResult['PermBySG'] = false;
	}

	public function getAllCommentsCount(array $params = []): int
	{
		global $USER;

		$result = 0;

		$cacheTime = (int)($params['cacheTime'] ?? 0);
		$postId = (int)$this->arParams['ID'];
		$currentUserId = (int)($params['currentUserId'] ?? $USER->getId());
		$permissions = ($params['permissions'] ?? Permissions::DENY);
		if (
			$postId <= 0
			|| $permissions <= Permissions::DENY
			|| !Loader::includeModule('blog')
		)
		{
			return $result;
		}

		$cache = new \CPHPCache;

		$cacheIdList = [
			'postId' => $postId,
			'currentUserId' => $currentUserId,
			'permissions' => $permissions,
		];

		$cacheId = 'blog_comment_all_' . md5(serialize($cacheIdList));

		$cachePath = \Bitrix\Socialnetwork\ComponentHelper::getBlogPostCacheDir(array(
			'TYPE' => 'post_comments',
			'POST_ID' => $postId,
		));

		if (
			$cacheTime > 0
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
		)
		{
			$vars = $cache->getVars();
			$result = ($vars['result'] ?? 0);
		}
		else
		{
			if ($cacheTime > 0)
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
			}

			if ($permissions >= Permissions::MODERATE)
			{
				$res = \Bitrix\Blog\CommentTable::getList([
					'select' => ['CNT'],
					'filter' => [
						'POST_ID' => $postId,
					],
					'runtime' => [
						new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
					],
				]);
			}
			else
			{
				$res = \Bitrix\Blog\CommentTable::getList([
					'select' => ['CNT'],
					'filter' => [
						'LOGIC' => 'OR',
						[
							'POST_ID' => $postId,
							'AUTHOR_ID' => $currentUserId,
						],
						[
							'POST_ID' => $postId,
							'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
						],
					],
					'runtime' => [
						new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
					],
				]);
			}

			$row = $res->fetch();
			$result = $row['CNT'] ?? 0;

			if ($cacheTime > 0)
			{
				$cache->endDataCache([
					'result' => $result,
				]);
			}
		}

		return $result;
	}
}
