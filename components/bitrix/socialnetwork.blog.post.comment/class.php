<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Security\Sign\Signer;
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
		global $USER;

		static $formId = null;

		if (empty($arParams['FORM_ID']))
		{
			if ($formId === null)
			{
				$formId = 'blogCommentForm' . \Bitrix\Main\Security\Random::getString(4);
			}
			$arParams['FORM_ID'] = $formId;
		}

		return $arParams;
	}

	public function executeComponent()
	{
		try
		{
			$this->arResult['deleteCommentId'] = (int)$_GET['delete_comment_id'];
			$this->arResult['hideCommentId'] = (int)$_GET['hide_comment_id'];
			$this->arResult['showCommentId'] = (int)$_GET['show_comment_id'];

			$this->checkActions();
			$this->fillParams();

			return $this->__includeComponent();
		}
		catch (Exception $e)
		{
			$this->handleException($e);
		}
	}

	/*
	 * ToDo: move action processing from the component there
	*/
	protected function checkActions(): void
	{
		if ($this->arParams['COMPONENT_AJAX'] === 'Y')
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

			if ((int)$_GET["delete_comment_id"])
			{
				$action = 'DELETE';
				$this->arResult['deleteCommentId'] = (int)$this->request->get('delete_comment_id');
			}
			elseif ((int)$_GET["hide_comment_id"])
			{
				$action = 'HIDE';
				$this->arResult['hideCommentId'] = (int)$this->request->get('hide_comment_id');
			}
			elseif ((int)$_GET["show_comment_id"])
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
		$postId = 0;

		if (
			(int)$_REQUEST["comment_post_id"] > 0
			|| $this->arParams['COMPONENT_AJAX'] === 'Y'
		)
		{
			if ((int)$_REQUEST['comment_post_id'] > 0)
			{
				$postId = (int)$_REQUEST['comment_post_id'];
			}
			elseif ((int)$this->arParams['ID'] > 0)
			{
				$postId = (int)$this->arParams['ID'];
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

		$permBySG = false;

		if (
			\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, !$mobile)
			|| \CMain::getGroupRight('blog') >= 'W'
		)
		{
			$this->arResult['Perm'] = Permissions::FULL;
		}
		elseif ($isIntranetInstalled)
		{
			if ($postAuthorId === $currentUserId)
			{
				$this->arResult['Perm'] = Permissions::FULL;
			}
			else
			{
				$this->arResult['Perm'] = (
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

		$this->arResult['PermBySG'] = $permBySG;
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

			$res = \Bitrix\Blog\CommentTable::getList([
				'filter' => [
					'POST_ID' => $postId,
				],
				'select' => [ 'ID', 'AUTHOR_ID', 'PUBLISH_STATUS' ]
			]);

			while ($commentFields = $res->fetch())
			{
				if (
					$permissions >= Permissions::MODERATE
					|| (
						$currentUserId > 0
						&& (int)$commentFields['AUTHOR_ID'] === $currentUserId
					)
					|| $commentFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH
				)
				{
					$result++;
				}
			}

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
