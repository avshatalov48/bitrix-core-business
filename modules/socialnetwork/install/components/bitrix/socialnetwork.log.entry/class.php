<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Component\LogEntry;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config;
use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Socialnetwork\LogTable;

\Bitrix\Main\Loader::includeModule('socialnetwork');

final class SocialnetworkLogEntry extends LogEntry
{
	protected function listKeysSignedParameters(): array
	{
		return [
			'PUBLIC_MODE',
			'FROM_LOG',
			'IS_CRM',
			'LAZYLOAD',
			'LOG_ID',
			'AVATAR_SIZE',
			'AVATAR_SIZE_COMMON',
			'AVATAR_SIZE_COMMENT',
			'IMAGE_SIZE',
			'DATE_TIME_FORMAT',
			'DATE_TIME_FORMAT_WITHOUT_YEAR',
			'TIME_FORMAT',
			'SHOW_YEAR',
			'CACHE_TYPE',
			'CACHE_TIME',
			'NAME_TEMPLATE',
			'SHOW_LOGIN',
			'RATING_TYPE',
			'USE_FOLLOW',
			'FOLLOW',
			'SHOW_RATING',
			'IND',
			'EVENT',
			'BLOG_ALLOW_POST_CODE',
			'DESTINATION_LIMIT_SHOW',
			'PATH_TO_USER',
			'PATH_TO_GROUP',
			'PATH_TO_CONPANY_DEPARTMENT',
			'PATH_TO_SMILE',
			'PATH_TO_LOG_ENTRY',
			'PATH_TO_USER_BLOG_POST',
			'PATH_TO_GROUP_BLOG_POST',
			'PATH_TO_USER_MICROBLOG_POST',
			'PATH_TO_GROUP_MICROBLOG_POST',
			'mfi',
			'FORUM_ID',
			'IMAGE_MAX_WIDTH',
			'IMAGE_MAX_HEIGHT',
		];
	}

	public function navigateCommentAction(): void
	{
		$this->arParams['COMPONENT_AJAX'] = 'Y';
		$this->arParams['URL'] = $_SERVER['HTTP_REFERER'];
		$this->arParams['COMMENTS_IN_EVENT'] = 20;

		if (!empty($this->request->getPost('FILTER')))
		{
			$this->arParams['FILTER'] = $this->request->getPost('FILTER');
		}

		$this->executeComponent();
	}

	public function processCommentAction(): void
	{
		$this->arParams['COMPONENT_AJAX'] = 'Y';
		$this->executeComponent();
	}

	public function executeComponent()
	{
		try
		{
			$this->checkActions();

			$this->__includeComponent();
		}
		catch (Exception $e)
		{
			$this->handleException($e);
		}
	}

	protected function checkActions(): void
	{
		if (($this->arParams['COMPONENT_AJAX'] ?? '') !== 'Y')
		{
			return;
		}

		$action = $this->request->getPost('ACTION');

		switch ($action)
		{
			case 'GET':
				$this->arParams['COMMENT_ID'] = $this->request->getPost('ID');
				break;
			case 'ADD':
			case 'EDIT':
				if ($action === 'EDIT')
				{
					$this->arParams['LAZYLOAD'] = 'N';
				}

				$cuid = trim((string)$this->request->getPost('cuid'));
				$cuid = preg_replace('/[^a-z0-9]/i', '', $cuid);

				$res = LogEntry::addComment([
					'logId' => $this->arParams['LOG_ID'],
					'crm' => $this->arParams['IS_CRM'],
					'languageId' => LANGUAGE_ID,
					'commentParams' => $this->request->getPost('id'),
					'pathToSmile' => $this->arParams['PATH_TO_SMILE'],
					'pathToLogEntry' => $this->arParams['PATH_TO_LOG_ENTRY'],
					'pathToUser' => $this->arParams['PATH_TO_USER'],
					'pathToUserBlogPost' => $this->arParams['PATH_TO_USER_BLOG_POST'],
					'pathToGroupBlogPost' => $this->arParams['PATH_TO_USER_GROUP_POST'] ?? '',
					'pathToUserMicroBlogPost' => $this->arParams['PATH_TO_USER_MICROBLOG_POST'],
					'pathToGroupMicroBlogPost' => $this->arParams['PATH_TO_GROUP_MICROBLOG_POST'],
					'dateTimeFormat' => $this->arParams['DATE_TIME_FORMAT'],
					'blogAllowPostCode' => $this->arParams['BLOG_ALLOW_POST_CODE'] ?? null,
					'message' => $this->request->getPost('comment'),
					'forumId' => $this->arParams['FORUM_ID'],
					'siteId' => SITE_ID,
					'commentUid' => $cuid,
					'nameTemplate' => $this->arParams['NAME_TEMPLATE'],
					'showLogin' => $this->arParams['SHOW_LOGIN'],
					'avatarSize' => $this->arParams['AVATAR_SIZE'],
					'pull' => 'N',
				]);

				if (!empty($res['commentID']))
				{
					$this->arParams['COMMENT_ID'] = (int)$res['commentID'];
					$this->arResult['RESULT'] = (int)$res['commentID'];
					$this->arResult['PUSH&PULL_ACTION'] = 'REPLY';
				}
				else
				{
					throw new SystemException($action === 'EDIT' ? Loc::getMessage('SONET_LOG_ENTRY_COMMENT_EDIT_ERROR') : Loc::getMessage('SONET_LOG_ENTRY_COMMENT_ADD_ERROR'));
				}

				break;
			case 'DELETE':
				$logId = (int)$this->arParams['LOG_ID'];
				$commentId = (int)$this->request->getPost('ID');

				if (
					$logId <= 0
					&& $commentId <= 0
				)
				{
					throw new ArgumentException(Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETE_ERROR'));
				}

				$res = LogTable::getList([
					'filter' => [
						'=ID' => $logId,
					],
					'select' => [ 'ID', 'ENTITY_ID', 'EVENT_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID', 'SOURCE_ID' ],
				]);
				if (!($logFields = $res->fetch()))
				{
					throw new ObjectNotFoundException(Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETE_ERROR'));
				}

				$contentId = Provider::getContentId($logFields);
				if (empty($contentId['ENTITY_TYPE']))
				{
					throw new ObjectNotFoundException(Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETE_ERROR'));
				}

				if (
					!($postProvider = Provider::init([
						'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
						'ENTITY_ID' => $contentId['ENTITY_ID'],
						'LOG_ID' => $logFields['ID']
					]))
					|| !($commentProvider = $postProvider->getCommentProvider())
				)
				{
					throw new ObjectNotFoundException(Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETE_ERROR'));
				}

				$commentProvider->setEntityId($commentId);
				$commentProvider->initSourceFields();

				if (!($commentFields = $commentProvider->getSourceFields()))
				{
					throw new ObjectNotFoundException(Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETE_ERROR'));
				}

				try
				{
					$deleteResult = LogEntry::deleteComment([
						'logId' => $logId,
						'commentId' => $commentFields['ID'],
					]);
				}
				catch (\Exception $e)
				{
					throw new SystemException($e->getMessage());
				}

				$this->arParams['COMMENT_ID'] = (int)$deleteResult;
				$this->arResult['RESULT'] = (int)$deleteResult;
				$this->arResult['OK_MESSAGE'] = Loc::getMessage('SONET_LOG_ENTRY_COMMENT_DELETED');
				$this->arResult['PUSH&PULL_ACTION'] = 'DELETE';
				break;
			default:
		}
	}

	protected function handleException(Exception $e): void
	{
		if (($this->arParams['COMPONENT_AJAX'] ?? '') === 'Y')
		{
			$this->sendJsonResponse([
				'status' => self::STATUS_ERROR,
				'data' => null,
				'errors' => [
					[
						'code' => $e->getCode(),
						'message' => $e->getMessage(),
					]
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

			ShowError($e->getMessage());
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
}
