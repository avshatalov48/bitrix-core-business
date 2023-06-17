<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\ActionFilter\Service\Token;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Security\Random;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\ComponentHelper;

Loc::loadMessages(__FILE__);

class LogEntry extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	protected const STATUS_SUCCESS = 'success';
	protected const STATUS_DENIED = 'denied';
	protected const STATUS_ERROR = 'error';

	/** @var ErrorCollection errorCollection */
	protected $errorCollection;

	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	public function configureActions(): array
	{
		return [];
	}

	public function onPrepareComponentParams($params = [])
	{
		if (
			!isset($params['IND'])
			|| (string)$params['IND'] === ''
		)
		{
			$params['IND'] = \Bitrix\Main\Security\Random::getString(8);
		}

		if (empty($params['LOG_PROPERTY']))
		{
			$params['LOG_PROPERTY'] = [ 'UF_SONET_LOG_FILE' ];
			if (
				ModuleManager::isModuleInstalled('webdav')
				|| ModuleManager::isModuleInstalled('disk'))
			{
				$params['LOG_PROPERTY'][] = 'UF_SONET_LOG_DOC';
			}
		}

		if (empty($params['COMMENT_PROPERTY']))
		{
			$params['COMMENT_PROPERTY'] = [ 'UF_SONET_COM_FILE' ];
			if (
				ModuleManager::isModuleInstalled('webdav')
				|| ModuleManager::isModuleInstalled('disk')
			)
			{
				$params['COMMENT_PROPERTY'][] = 'UF_SONET_COM_DOC';
			}

			$params['COMMENT_PROPERTY'][] = 'UF_SONET_COM_URL_PRV';
		}

		if (empty($params['PATH_TO_LOG_TAG']))
		{
			$folderUsers = Option::get('socialnetwork', 'user_page', false, SITE_ID);
			$params['PATH_TO_LOG_TAG'] = $folderUsers . 'log/?TAG=#tag#';
			if (SITE_TEMPLATE_ID === 'bitrix24')
			{
				$params['PATH_TO_LOG_TAG'] .= '&apply_filter=Y';
			}
		}

		\CSocNetLogComponent::processDateTimeFormatParams($params);

		$params['COMMENT_ID'] = (int) ($params['COMMENT_ID'] ?? 0);

		return $params;
	}

	public static function addComment(array $params = []): array
	{
		global $USER, $USER_FIELD_MANAGER;

		$result = [];

		$logId = (int)($params['logId'] ?? 0);
		$currentUserId = (int)($params['currentUserId'] ?? $USER->getId());
		$crm = (isset($params['crm']) && $params['crm'] === 'Y' ? 'Y' : 'N');
		$languageId = (string)($params['languageId'] ?? LANGUAGE_ID);
		$siteId = (string)($params['siteId'] ?? SITE_ID);
		$commentParams = (isset($params['commentParams']) && is_array($params['commentParams']) ? $params['commentParams'] : []);
		$message = (string)($params['message'] ?? '');
		$forumId = (int)($params['forumId'] ?? 0);
		$commentUid = (string)($params['commentUid'] ?? '');
		$dateTimeFormat = (string)($params['dateTimeFormat'] ?? \CSite::getTimeFormat());
		$nameTemplate = (string)($params['nameTemplate'] ?? \CSite::getNameFormat(null, $siteId));
		$showLogin = (string)($params['showLogin'] ?? 'N');
		$avatarSize = (int)($params['avatarSize'] ?? 100);
		$pull = (string)($params['pull'] ?? 'N');
		$decode = (bool)($params['decode'] ?? false);

		$pathToSmile = (string)($params['pathToSmile'] ?? '');
		$pathToLogEntry = (string)($params['pathToLogEntry'] ?? '');
		$pathToUser = (string)($params['pathToUser'] ?? '');
		$pathToUserBlogPost = (string)($params['pathToUserBlogPost'] ?? '');
		$pathToGroupBlogPost = (string)($params['pathToGroupBlogPost'] ?? '');
		$pathToUserMicroBlogPost = (string)($params['pathToUserMicroBlogPost'] ?? '');
		$pathToGroupMicroBlogPost = (string)($params['pathToGroupMicroBlogPost'] ?? '');
		$blogAllowPostCode = (string)($params['blogAllowPostCode'] ?? 'N');

		if ($logId <= 0)
		{
			return $result;
		}

		if (!isset($params['currentUserExternalAuthId']))
		{
			$currentUserExternalAuthId = '';

			if ($USER->isAuthorized())
			{
				$res = \CUser::getById($currentUserId);
				if ($userFields = $res->fetch())
				{
					$currentUserExternalAuthId = $userFields['EXTERNAL_AUTH_ID'];
				}
			}
		}
		else
		{
			$currentUserExternalAuthId = '';
		}

		if ($logFields = \CSocNetLog::getById($logId))
		{
			$listParams = [
				'CHECK_RIGHTS' => 'Y',
				'USE_SUBSCRIBE' => 'N'
			];

			if (
				$currentUserExternalAuthId !== 'email'
				&& mb_strpos($logFields['ENTITY_TYPE'], 'CRM') === 0
				&& (
					!in_array($logFields['EVENT_ID'],  [ 'crm_lead_message', 'crm_deal_message', 'crm_company_message', 'crm_contact_message', 'crm_activity_add' ])
					|| $crm === 'Y'
				)
				&& ModuleManager::isModuleInstalled('crm')
			)
			{
				$listParams = [
					'IS_CRM' => 'Y',
					'CHECK_CRM_RIGHTS' => 'Y'
				];
			}
		}
		else
		{
			$logId = 0;
		}

		if (
			$logId <= 0
			|| !($res = \CSocNetLog::GetList(array(), array("ID" => $logId), false, false, array(), $listParams))
			|| !($logFields = $res->fetch())
		)
		{
			$result['strMessage'] = Loc::getMessage('SONET_LOG_COMMENT_NO_PERMISSIONS', false, $languageId);
			return $result;
		}

		$commentEvent = \CSocNetLogTools::FindLogCommentEventByLogEventID($logFields['EVENT_ID']);
		if (!$commentEvent)
		{
			return $result;
		}

		$canAddComments = ComponentHelper::canAddComment($logFields, $commentEvent);

		if (!$canAddComments)
		{
			$result['strMessage'] = Loc::getMessage('SONET_LOG_COMMENT_NO_PERMISSIONS', false, $languageId);
			return $result;
		}

		$editCommentSourceId = (
			isset($commentParams[1]) && (int)$commentParams[1] > 0
				? (int)$commentParams[1]
				: 0
		);

		// add source object and get source_id, $source_url
		$options = [
			'PATH_TO_SMILE' => $pathToSmile,
			'PATH_TO_LOG_ENTRY' => $pathToLogEntry,
			'PATH_TO_USER_BLOG_POST' => $pathToUserBlogPost,
			'PATH_TO_GROUP_BLOG_POST' => $pathToGroupBlogPost,
			'PATH_TO_USER_MICROBLOG_POST' => $pathToUserMicroBlogPost,
			'PATH_TO_GROUP_MICROBLOG_POST' => $pathToGroupMicroBlogPost,
			'BLOG_ALLOW_POST_CODE' => $blogAllowPostCode,
		];

		$commentText = preg_replace("/\xe2\x81\xa0/is", ' ', $message);  // INVISIBLE_CURSOR from editor
		if ($decode)
		{
			\CUtil::decodeURIComponent($commentText);
		}
		$commentText = trim($commentText);

		if ($commentText === '')
		{
			$result['strMessage'] = Loc::getMessage('SONET_LOG_COMMENT_EMPTY', false, $languageId);
			return $result;
		}

		$searchParams = [];

		if ($commentEvent['EVENT_ID'] === 'forum')
		{
			$searchParams['FORUM_ID'] = $forumId;
			$searchParams['PATH_TO_GROUP_FORUM_MESSAGE'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
					? static::replaceGroupPath($logFields['URL'], $siteId)
					: ''
			);
			$searchParams['PATH_TO_USER_FORUM_MESSAGE'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_USER
					? $logFields['URL']
					: ''
			);
		}
		elseif ($commentEvent['EVENT_ID'] === 'files_comment')
		{
			$filesForumId = 0;

			if ((string)$logFields['PARAMS'] !== '')
			{
				$logParams = explode('&', htmlspecialcharsback($logFields["PARAMS"]));
				foreach ($logParams as $prm)
				{
					[ $k, $v ] = explode('=', $prm);
					if ($k === 'forum_id')
					{
						$filesForumId = (int)$v;
						break;
					}
				}
			}

			$searchParams['FILES_FORUM_ID'] = $filesForumId;
			$searchParams['PATH_TO_GROUP_FILES_ELEMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
					? static::replaceGroupPath($logFields['URL'], $siteId)
					: ''
			);
			$searchParams['PATH_TO_USER_FILES_ELEMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_USER
					? $logFields['URL']
					: ''
			);
		}
		elseif ($commentEvent['EVENT_ID'] === 'photo_comment')
		{
			$photoForumId = 0;

			if ((string)$logFields['PARAMS'] !== '')
			{
				$logParams = unserialize(htmlspecialcharsback($logFields['PARAMS']), [ 'allowed_classes' => false ]);
				if (
					isset($logParams['FORUM_ID'])
					&& (int)$logParams['FORUM_ID'] > 0
				)
				{
					$photoForumId = $logParams["FORUM_ID"];
				}
			}
			$searchParams['PHOTO_FORUM_ID'] = $photoForumId;
			$searchParams['PATH_TO_GROUP_PHOTO_ELEMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
					? static::replaceGroupPath($logFields['URL'], $siteId)
					: ''
			);
			$searchParams['PATH_TO_USER_PHOTO_ELEMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_USER
					? $logFields['URL']
					: ''
			);
		}
		elseif ($commentEvent['EVENT_ID'] === 'wiki_comment')
		{
			$searchParams['PATH_TO_GROUP_WIKI_POST_COMMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
					? Option::get('socialnetwork', 'workgroups_page', '', $siteId) . 'group/#group_id#/wiki/#wiki_name#/?MID=#message_id##message#message_id#'
					: ''
			);
		}
		elseif ($commentEvent["EVENT_ID"] === 'tasks_comment')
		{
			if (Loader::includeModule('tasks'))
			{
				$tasksForumId = 0;

				try
				{
					$tasksForumId = \CTasksTools::getForumIdForIntranet();
				}
				catch (\Exception $e)
				{
				}

				if ($tasksForumId > 0)
				{
					$searchParams['TASK_FORUM_ID'] = $tasksForumId;
					$searchParams['PATH_TO_GROUP_TASK_ELEMENT'] = (
						$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
							? Option::get('socialnetwork', 'workgroups_page', '', $siteId) . 'group/#group_id#/tasks/task/view/#task_id#/'
							: ''
					);
					$searchParams['PATH_TO_USER_TASK_ELEMENT'] = (
						$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_USER
						? Option::get('socialnetwork', 'user_page', '', $siteId) . 'user/#user_id#/tasks/task/view/#task_id#/'
						: ""
					);
				}
			}
		}
		elseif ($commentEvent['EVENT_ID'] === 'calendar_comment')
		{
			$searchParams['PATH_TO_GROUP_CALENDAR_ELEMENT'] = (
				$logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP
				? Option::get('socialnetwork', 'workgroups_page', '', $siteId) . 'group/#group_id#/calendar/?EVENT_ID=#element_id#'
				: ''
			);
		}
		elseif ($commentEvent['EVENT_ID'] === 'lists_new_element_comment')
		{
			$searchParams['PATH_TO_WORKFLOW'] = '/services/processes/#list_id#/bp_log/#workflow_id#/';
		}

		global $bxSocNetSearch;

		if (
			!empty($searchParams)
			&& !is_object($bxSocNetSearch)
		)
		{
			$bxSocNetSearch = new \CSocNetSearch(
				($logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_USER ? $logFields['ENTITY_ID'] : false),
				($logFields['ENTITY_TYPE'] === SONET_SUBSCRIBE_ENTITY_GROUP ? $logFields['ENTITY_ID'] : false),
				$searchParams
			);
			AddEventHandler('search', 'BeforeIndex', [ $bxSocNetSearch, 'BeforeIndex' ]);
		}

		if ($editCommentSourceId > 0)
		{
			$fields = [
				'EVENT_ID' => $commentEvent['EVENT_ID'],
				'MESSAGE' => $commentText,
				'TEXT_MESSAGE' => $commentText,
				'BLOG_ALLOW_POST_CODE' => $options['BLOG_ALLOW_POST_CODE']
			];
		}
		else
		{
			$fields = [
				'ENTITY_TYPE' => $logFields['ENTITY_TYPE'],
				'ENTITY_ID' => $logFields['ENTITY_ID'],
				'EVENT_ID' => $commentEvent['EVENT_ID'],
				'=LOG_DATE' => \CDatabase::currentTimeFunction(),
				'MESSAGE' => $commentText,
				'TEXT_MESSAGE' => $commentText,
				'MODULE_ID' => false,
				'LOG_ID' => $logFields['ID'],
				'USER_ID' => $currentUserId,
				'PATH_TO_USER_BLOG_POST' => $options['PATH_TO_USER_BLOG_POST'],
				'PATH_TO_GROUP_BLOG_POST' => $options['PATH_TO_GROUP_BLOG_POST'],
				'PATH_TO_USER_MICROBLOG_POST' => $options['PATH_TO_USER_MICROBLOG_POST'],
				'PATH_TO_GROUP_MICROBLOG_POST' => $options['PATH_TO_GROUP_MICROBLOG_POST'],
				'BLOG_ALLOW_POST_CODE' => $options['BLOG_ALLOW_POST_CODE']
			];
		}

		$USER_FIELD_MANAGER->EditFormAddFields('SONET_COMMENT', $fields);

		if (
			array_key_exists('UF_SONET_COM_FILE', $fields)
			&& !empty($fields['UF_SONET_COM_FILE'])
		)
		{
			if (is_array($fields['UF_SONET_COM_FILE']))
			{
				foreach ($fields["UF_SONET_COM_FILE"] as $key => $fileID)
				{
					if (
						!$commentUid
						|| !array_key_exists('MFI_UPLOADED_FILES_' . $commentUid, $_SESSION)
						|| !in_array($fileID, $_SESSION['MFI_UPLOADED_FILES_' . $commentUid], true)
					)
					{
						unset($fields['UF_SONET_COM_FILE'][$key]);
					}
				}
			}
			elseif (
				!$commentUid
				|| !array_key_exists('MFI_UPLOADED_FILES_' . $commentUid, $_SESSION)
				|| !in_array($fields['UF_SONET_COM_FILE'], $_SESSION['MFI_UPLOADED_FILES_' . $commentUid], true)
			)
			{
				unset($fields['UF_SONET_COM_FILE']);
			}
		}

		$inlineTagList = \Bitrix\Socialnetwork\Util::detectTags($fields, [ 'MESSAGE' ]);

		if (!empty($inlineTagList))
		{
			$fields['TAG'] = $inlineTagList;
		}

		$updatedCommentId = 0;

		if ($editCommentSourceId > 0)
		{
			$updatedCommentLogId = 0;
			$updatedCommentUserId = 0;

			if (
				isset($commentEvent['ADD_CALLBACK'])
				&& is_callable($commentEvent['ADD_CALLBACK'])
			)
			{
				$res = \CSocNetLogComments::getList(
					[],
					[
						'EVENT_ID' => $commentEvent['EVENT_ID'],
						'SOURCE_ID' => $editCommentSourceId
					],
					false,
					false,
					[ 'ID', 'USER_ID', 'LOG_ID', 'SOURCE_ID' ]
				);
				if ($commentFields = $res->fetch())
				{
					$updatedCommentId = $commentFields['ID'];
					$updatedCommentLogId = $commentFields['LOG_ID'];
					$updatedCommentUserId = $commentFields['USER_ID'];
				}
			}

			if ((int)$updatedCommentId <= 0)
			{
				$res = \CSocNetLogComments::getList(
					[],
					[
						'ID' => $editCommentSourceId,
					],
					false,
					false,
					[ 'ID', 'USER_ID', 'LOG_ID', 'SOURCE_ID' ]
				);
				if ($commentFields = $res->fetch())
				{
					$updatedCommentId = $commentFields['ID'];
					$updatedCommentLogId = $commentFields['LOG_ID'];
					$updatedCommentUserId = $commentFields['USER_ID'];
				}
			}

			$canUpdate = false;

			if ((int)$updatedCommentId > 0)
			{
				$canUpdate = \CSocNetLogComponent::canUserChangeComment(array(
					'ACTION' => 'EDIT',
					'LOG_ID' => $updatedCommentLogId,
					'LOG_EVENT_ID' => $logFields['EVENT_ID'],
					'LOG_SOURCE_ID' => $logFields['SOURCE_ID'],
					'COMMENT_ID' => $updatedCommentId,
					'COMMENT_USER_ID' => $updatedCommentUserId
				));
			}

			if ($canUpdate)
			{
				$commentId = \CSocNetLogComments::update($updatedCommentId, $fields, true);
			}
			else
			{
				$result['strMessage'] = Loc::getMessage("SONET_LOG_COMMENT_NO_PERMISSIONS_UPDATE", false, $languageId);
				$result["commentText"] = $commentText;

				return $result;
			}
		}
		else
		{
			$commentId = \CSocNetLogComments::add($fields, true, false);
		}

		if ((int)$commentId <= 0)
		{
			return $result;
		}

		$bSkipCounterIncrement = false;

		if ($editCommentSourceId <= 0)
		{
			$res = getModuleEvents('socialnetwork', 'OnAfterSocNetLogEntryCommentAdd');
			while ($event = $res->fetch())
			{
				ExecuteModuleEventEx($event, [
					$logFields,
					[
						'SITE_ID' => $siteId,
						'COMMENT_ID' => $commentId,
					]
				]);
			}

			$res = getModuleEvents('socialnetwork', 'OnBeforeSocNetLogCommentCounterIncrement');
			while ($event = $res->fetch())
			{
				if (ExecuteModuleEventEx($event, [ $logFields ]) === false)
				{
					$bSkipCounterIncrement = true;
					break;
				}
			}
		}
		else
		{
			$bSkipCounterIncrement = true;
		}

		if (!$bSkipCounterIncrement)
		{
			\CSocNetLog::counterIncrement(
				$commentId,
				false,
				false,
				"LC",
				\CSocNetLogRights::checkForUserAll($logFields['ID'])
			);
		}

		$result['commentID'] = $commentId;

		if ($commentFields = \CSocNetLogComments::getById($result['commentID']))
		{
			$res = ComponentHelper::addLiveComment(
				$commentFields,
				$logFields,
				$commentEvent,
				[
					'ACTION' => ((int)$updatedCommentId <= 0 ? 'ADD' : "UPDATE"),
					'SOURCE_ID' => $editCommentSourceId,
					'TIME_FORMAT' => $dateTimeFormat,
					"PATH_TO_USER" => $pathToUser,
					'PATH_TO_LOG_ENTRY' => $pathToLogEntry,
					'NAME_TEMPLATE' => $nameTemplate,
					'SHOW_LOGIN' => $showLogin,
					'AVATAR_SIZE' => $avatarSize,
					'PATH_TO_SMILE' => $pathToSmile,
					'LANGUAGE_ID' => $languageId,
					'SITE_ID' => $siteId,
					'PULL' => $pull,
				]
			);

			$result = array_merge($result, $res);
		}

		return $result;
	}

	public static function deleteComment(array $params = [])
	{
		global $APPLICATION;

		$result = false;

		$logId = (int)($params['logId'] ?? 0);
		$commentId = (int)($params['commentId'] ?? 0);

		if (
			$logId <= 0
			|| $commentId <= 0
		)
		{
			throw new ArgumentException('Wrong method parameters');
		}

		$commentFields = \CSocNetLogComponent::getCommentByRequest($commentId, $logId, 'delete');
		if (!$commentFields)
		{
			throw new AccessDeniedException('Cannot get comment');
		}

		if (!\CSocNetLogComments::delete($commentFields['ID'], true))
		{
			if ($e = $APPLICATION->getException())
			{
				throw new SystemException($e->getString());
			}
		}
		else
		{
			$result = (int)(
				$commentFields['SOURCE_ID'] > 0
					? $commentFields['SOURCE_ID']
					: $commentFields['ID']
			);
		}

		return $result;
	}

	public static function replaceGroupPath($url = '', $siteId = SITE_ID): string
	{
		static $workgroupsPage = null;
		if ($workgroupsPage === null)
		{
			$workgroupsPage = Option::get('socialnetwork', 'workgroups_page', false, $siteId);
		}

		return str_replace(
			'#GROUPS_PATH#',
			$workgroupsPage,
			$url
		);
	}

	public static function getUserFieldsFMetaData()
	{
		global $USER_FIELD_MANAGER;
		static $arUFMeta;
		if (!$arUFMeta)
		{
			$arUFMeta = $USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT", 0, LANGUAGE_ID);
		}
		return $arUFMeta;
	}

	public static function getCommentsFullList(array $eventData, array &$params, array $options = [], array &$arResult = [])
	{
		global $CACHE_MANAGER;

		$nTopCount = (isset($options['nTopCount']) && (int)$options['nTopCount'] > 0 ? (int)$options['nTopCount'] : 20);
		$timeZoneOffzet = (isset($options['timeZoneOffzet']) && (int)$options['timeZoneOffzet'] > 0 ? (int)$options['timeZoneOffzet'] : 0);
		$commentEvent = (isset($options['commentEvent']) && is_array($options['commentEvent']) ? $options['commentEvent'] : []);
		$commentProvider = ($options['commentProvider'] ?? false);

		$cacheTime = 31536000;
		$cache = false;

		$useCache = ($params['COMMENT_ID'] <= 0);

		if ($useCache)
		{
			$cache = new \CPHPCache;
		}

		$cacheIdPartsList = [];
		$keysList = [
			'AVATAR_SIZE_COMMENT',
			'NAME_TEMPLATE',
			'NAME_TEMPLATE_WO_NOBR',
			'SHOW_LOGIN',
			'DATE_TIME_FORMAT',
			'PATH_TO_USER',
			'PATH_TO_GROUP',
			'PATH_TO_CONPANY_DEPARTMENT',
			'FILTER',
		];

		foreach ($keysList as $paramKey)
		{
			$cacheIdPartsList[$paramKey] = (
				array_key_exists($paramKey, $params)
					? $params[$paramKey]
					: false
			);
		}

		$navParams = \CDBResult::getNavParams($params['COMMENTS_IN_EVENT'] ?? null);
		$navPage = (int)($navParams['PAGEN'] ?? 1);

		$cacheId = implode('_', [
			'log_comments',
			$params['LOG_ID'],
			md5(serialize($cacheIdPartsList)),
			SITE_TEMPLATE_ID,
			SITE_ID,
			LANGUAGE_ID,
			FORMAT_DATETIME,
			$timeZoneOffzet,
			$nTopCount,
			$navPage,
		]);

		$cachePath = '/sonet/log/' . (int)((int)$params['LOG_ID'] / 1000) . '/' . $params['LOG_ID'] . '/comments/';

		$result = [];

		if (
			$useCache
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
		)
		{
			$cacheVariables = $cache->getVars();
			$result = $cacheVariables['COMMENTS_FULL_LIST'];

			$navResultData = $cacheVariables['NAV_RESULT_DATA'];
			$navResult = new \CDBResult;
			$navResult->bShowAll = $navResultData['bShowAll'];
			$navResult->bDescPageNumbering = $navResultData['bDescPageNumbering'];
			$navResult->NavNum = $navResultData['NavNum'];
			$navResult->NavRecordCount = $navResultData['NavRecordCount'];
			$navResult->NavPageNomer = $navResultData['NavPageNomer'];
			$navResult->NavPageSize = $navResultData['NavPageSize'];

			if (!empty($cacheVariables['Assets']))
			{
				if (!empty($cacheVariables['Assets']['CSS']))
				{
					foreach ($cacheVariables['Assets']['CSS'] as $cssFile)
					{
						Asset::getInstance()->addCss($cssFile);
					}
				}

				if (!empty($cacheVariables['Assets']['JS']))
				{
					foreach ($cacheVariables["Assets"]['JS'] as $jsFile)
					{
						Asset::getInstance()->addJs($jsFile);
					}
				}
			}
		}
		else
		{
			if ($useCache)
			{
				$cache->startDataCache($cacheTime, $cacheId, $cachePath);
			}

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->startTagCache($cachePath);
			}

			$filter = [
				'LOG_ID' => $params['LOG_ID']
			];

			$logCommentId = 0;

			if ($params['COMMENT_ID'] > 0)
			{
				$logCommentId = $params['COMMENT_ID'];
			}
			elseif (
				!empty($params['FILTER'])
				&& !empty($params['FILTER']['<ID'])
				&& (int)$params['FILTER']['<ID'] > 0
			)
			{
				$logCommentId = (int)$params['FILTER']['<ID'];
			}

			if (
				!empty($commentEvent)
				&& !empty($commentEvent['RATING_TYPE_ID'])
				&& $logCommentId > 0
			)
			{
				$res = \CSocNetLogComments::getList(
					[],
					[
						'RATING_TYPE_ID' => $commentEvent['RATING_TYPE_ID'],
						'RATING_ENTITY_ID' => $logCommentId,
					],
					false,
					false,
					[ 'ID' ]
				);

				if ($logCommentFields = $res->fetch())
				{
					$logCommentId = (int)$logCommentFields['ID'];
				}
			}

			if ($logCommentId > 0)
			{
				if ($params['COMMENT_ID'] > 0)
				{
					$filter['>=ID'] = $logCommentId;
				}
				elseif (
					!empty($params['FILTER'])
					&& !empty($params['FILTER']['<ID'])
					&& (int)$params['FILTER']['<ID'] > 0
				)
				{
					$filter['<ID'] = $logCommentId;
				}
			}

			$select = [
				'ID', 'LOG_ID', 'SOURCE_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'USER_ID', 'EVENT_ID', 'LOG_DATE', 'MESSAGE', 'LOG_DATE_TS', 'TEXT_MESSAGE', 'URL', 'MODULE_ID',
				'GROUP_NAME', 'GROUP_OWNER_ID', 'GROUP_VISIBLE', 'GROUP_OPENED', 'GROUP_IMAGE_ID',
				'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO', 'USER_PERSONAL_GENDER',
				'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'CREATED_BY_SECOND_NAME', 'CREATED_BY_LOGIN', 'CREATED_BY_PERSONAL_PHOTO', 'CREATED_BY_PERSONAL_GENDER', 'CREATED_BY_EXTERNAL_AUTH_ID',
				'SHARE_DEST',
				'LOG_SITE_ID', 'LOG_SOURCE_ID',
				'RATING_TYPE_ID', 'RATING_ENTITY_ID',
				'UF_*'
			];

			$listParams = [
				'USE_SUBSCRIBE' => 'N',
				'CHECK_RIGHTS' => 'N'
			];

			$usetFieldsMetaData = self::getUserFieldsFMetaData();

			if ($params['COMMENT_ID'] > 0)
			{
				$navParams = false;
			}
			elseif ($navPage > 1)
			{
				$navParams = [
					'iNumPage' => $navPage,
					'nPageSize' => $params['COMMENTS_IN_EVENT'],
				];
			}
			else
			{
				$navParams = [
					'nTopCount' => $nTopCount,
				];
			}

			$assets = [
				'CSS' => [],
				'JS' => []
			];

			$res = \CSocNetLogComments::getList(
				[ 'LOG_DATE' => 'DESC', 'ID' => 'DESC' ], // revert then
				$filter,
				false,
				$navParams,
				$select,
				$listParams
			);

			$navResultData = null;
			if ($res->NavNum !== null)
			{
				$navResultData = [
					'bShowAll' => $res->bShowAll,
					'bDescPageNumbering' => $res->bDescPageNumbering,
					'NavNum' => $res->NavNum,
					'NavRecordCount' => $res->NavRecordCount,
					'NavPageNomer' => $res->NavPageNomer,
					'NavPageSize' => $res->NavPageSize,
				];
			}

			if (
				!empty($eventData['EVENT_FORMATTED'])
				&& !empty($eventData['EVENT_FORMATTED']['DESTINATION'])
				&& is_array($eventData['EVENT_FORMATTED']['DESTINATION'])
			)
			{
				foreach ($eventData['EVENT_FORMATTED']['DESTINATION'] as $destination)
				{
					if (!empty($destination['CRM_USER_ID']))
					{
						$params['ENTRY_HAS_CRM_USER'] = true;
						break;
					}
				}
			}

			$commentsList = $commentSourceIdList = [];
			while ($commentFields = $res->getNext())
			{
				if (!empty($commentFields['SHARE_DEST']))
				{
					$commentFields['SHARE_DEST'] = htmlspecialcharsback($commentFields['SHARE_DEST']);
				}

				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->registerTag('USER_NAME_'.(int)$commentFields['USER_ID']);
				}

				$commentFields['UF'] = $usetFieldsMetaData;
				foreach ($usetFieldsMetaData as $fieldName => $userFieldData)
				{
					if (array_key_exists($fieldName, $commentFields))
					{
						$commentFields['UF'][$fieldName]['VALUE'] = $commentFields[$fieldName];
						$commentFields["UF"][$fieldName]['ENTITY_VALUE_ID'] = $commentFields['ID'];
					}
				}
				$commentsList[] = $commentFields;
				if ((int)$commentFields['SOURCE_ID'] > 0)
				{
					$commentSourceIdList[] = (int)$commentFields['SOURCE_ID'];
				}
			}

			if (
				!empty($commentSourceIdList)
				&& !empty($commentProvider)
			)
			{
				$sourceAdditonalData = $commentProvider->getAdditionalData([
					'id' => $commentSourceIdList
				]);

				if (!empty($sourceAdditonalData))
				{
					foreach ($commentsList as $key => $comment)
					{
						if (
							!empty($comment['SOURCE_ID'])
							&& isset($sourceAdditonalData[$comment['SOURCE_ID']])
						)
						{
							$commentsList[$key]['ADDITIONAL_DATA'] = $sourceAdditonalData[$comment['SOURCE_ID']];
						}
					}
				}
			}

			foreach ($commentsList as $commentFields)
			{
				$result[] = self::getLogCommentRecord($commentFields, $params, $assets);
			}

			if (
				$params['COMMENT_ID'] <= 0
				&& $navResultData === null
			)
			{
				$navResult = \CSocNetLogComments::getList(
					[ ],
					$filter,
					false,
					[
						'nPageSize' => $params['COMMENTS_IN_EVENT'],
						'bShowAll' => false,
					],
					[ 'ID' ],
					$listParams
				);

				$navResultData = [
					'bShowAll' => $navResult->bShowAll,
					'bDescPageNumbering' => $navResult->bDescPageNumbering,
					'NavNum' => $navResult->NavNum,
					'NavRecordCount' => $navResult->NavRecordCount,
					'NavPageNomer' => $navResult->NavPageNomer,
					'NavPageSize' => $navResult->NavPageSize,
				];
			}

			if ($useCache)
			{
				$cacheData = [
					'COMMENTS_FULL_LIST' => $result,
					'NAV_RESULT_DATA' => $navResultData,
					'Assets' => $assets
				];
				$cache->endDataCache($cacheData);
				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->endTagCache();
				}
			}
		}

		$arResult['NAV_RESULT'] = $navResult ?? null;

		return $result;
	}

	public static function getLogCommentRecord(array $comment, array $params, array &$assets): array
	{
		global $APPLICATION, $arExtranetUserID;

		$extranetUserIdList = $arExtranetUserID;

		static $userCache = array();

		// for the same post log_update - time only, if not - date and time
		$timestamp = makeTimeStamp(array_key_exists('LOG_DATE_FORMAT', $comment)
			? $comment['LOG_DATE_FORMAT']
			: $comment['LOG_DATE']
		);

		$timeFormated = formatDateFromDB($comment['LOG_DATE'],
			(
				mb_stripos($params['DATE_TIME_FORMAT'], 'a')
				|| (
					$params['DATE_TIME_FORMAT'] === 'FULL'
					&& isAmPmMode()
				) !== false
					? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'G:MI TT' : 'G:MI T')
					: 'HH:MI'
			)
		);

		$dateTimeFormated = formatDate(
			(!empty($params['DATE_TIME_FORMAT'])
				? ($params['DATE_TIME_FORMAT'] === 'FULL'
					? \CDatabase::dateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME))
					: $params['DATE_TIME_FORMAT']
				)
				: \CDatabase::dateFormatToPHP(FORMAT_DATETIME)
			),
			$timestamp
		);

		if (
			strcasecmp(LANGUAGE_ID, 'EN') !== 0
			&& strcasecmp(LANGUAGE_ID, 'DE') !== 0
		)
		{
			$dateTimeFormated = toLower($dateTimeFormated);
		}

		// strip current year
		if (
			!empty($params['DATE_TIME_FORMAT'])
			&& (
				$params['DATE_TIME_FORMAT'] === 'j F Y G:i'
				|| $params['DATE_TIME_FORMAT'] === 'j F Y g:i a'
			)
		)
		{
			$dateTimeFormated = ltrim($dateTimeFormated, '0');
			$currentYear = date('Y');
			$dateTimeFormated = str_replace(array('-'.$currentYear, '/'.$currentYear, ' '.$currentYear, '.'.$currentYear), '', $dateTimeFormated);
		}

		$path2Entity = (
			$comment['ENTITY_TYPE'] === SONET_ENTITY_GROUP
				? \CComponentEngine::MakePathFromTemplate($params['PATH_TO_GROUP'], [ 'group_id' => $comment['ENTITY_ID'] ])
				: \CComponentEngine::MakePathFromTemplate($params['PATH_TO_USER'], [ 'user_id' => $comment['ENTITY_ID'] ])
		);

		if ((int)$comment['USER_ID'] > 0)
		{
			$suffix = (
				is_array($extranetUserIdList)
				&& in_array($comment['USER_ID'], $extranetUserIdList)
					? Loc::getMessage('SONET_LOG_EXTRANET_SUFFIX')
					: ""
			);

			$userFields = [
				'NAME' => $comment['~CREATED_BY_NAME'],
				'LAST_NAME' => $comment['~CREATED_BY_LAST_NAME'],
				'SECOND_NAME' => $comment['~CREATED_BY_SECOND_NAME'],
				'LOGIN' => $comment['~CREATED_BY_LOGIN']
			];
			$useLogin = ($params["SHOW_LOGIN"] !== "N");
			$createdByFields = [
				'FORMATTED' => \CUser::formatName($params['NAME_TEMPLATE'], $userFields, $useLogin).$suffix,
				'URL' => \CComponentEngine::makePathFromTemplate($params['PATH_TO_USER'], [
					'user_id' => $comment['USER_ID'],
					'id' => $comment['USER_ID']
				])
			];

			$createdByFields['TOOLTIP_FIELDS'] = [
				'ID' => $comment['USER_ID'],
				'NAME' => $comment['~CREATED_BY_NAME'],
				'LAST_NAME' => $comment['~CREATED_BY_LAST_NAME'],
				'SECOND_NAME' => $comment['~CREATED_BY_SECOND_NAME'],
				'LOGIN' => $comment['~CREATED_BY_LOGIN'],
				'PERSONAL_GENDER' => $comment['~CREATED_BY_PERSONAL_GENDER'],
				'USE_THUMBNAIL_LIST' => 'N',
				'PATH_TO_SONET_MESSAGES_CHAT' => $params['PATH_TO_MESSAGES_CHAT'] ?? null,
				'PATH_TO_SONET_USER_PROFILE' => $params['PATH_TO_USER'] ?? null,
				'PATH_TO_VIDEO_CALL' => $params['PATH_TO_VIDEO_CALL'] ?? null,
				'DATE_TIME_FORMAT' => $params['DATE_TIME_FORMAT'],
				'SHOW_YEAR' => $params['SHOW_YEAR'],
				'CACHE_TYPE' => $params['CACHE_TYPE'] ?? null,
				'CACHE_TIME' => $params['CACHE_TIME'] ?? null,
				'NAME_TEMPLATE' => $params['NAME_TEMPLATE'].$suffix,
				'SHOW_LOGIN' => $params['SHOW_LOGIN'],
				'PATH_TO_CONPANY_DEPARTMENT' => $params['PATH_TO_CONPANY_DEPARTMENT'] ?? null,
				'INLINE' => 'Y',
				'EXTERNAL_AUTH_ID' => $comment['~CREATED_BY_EXTERNAL_AUTH_ID']
			];
			if (
				isset($params['ENTRY_HAS_CRM_USER'])
				&& $params['ENTRY_HAS_CRM_USER']
				&& ModuleManager::isModuleInstalled('crm')
			)
			{
				if (isset($userCache[$comment['USER_ID']]))
				{
					$userFields = $userCache[$comment['USER_ID']];
				}
				else
				{
					$res = UserTable::getList([
						'filter' => [
							'ID' => (int)$comment['USER_ID']
						],
						'select' => [ 'ID', 'UF_USER_CRM_ENTITY' ]
					]);
					if ($userFields = $res->fetch())
					{
						$userCache[$userFields['ID']] = $userFields;
					}
				}

				if (!empty($userFields))
				{
					$createdByFields['TOOLTIP_FIELDS'] = array_merge($createdByFields['TOOLTIP_FIELDS'], $userFields);
				}
			}
		}
		else
		{
			$createdByFields = [
				'FORMATTED' => Loc::getMessage("SONET_C73_CREATED_BY_ANONYMOUS")
			];
		}

		$userFields = [
			'NAME' => $comment['~USER_NAME'],
			'LAST_NAME' => $comment['~USER_LAST_NAME'],
			'SECOND_NAME' => $comment['~USER_SECOND_NAME'],
			'LOGIN' => $comment['~USER_LOGIN']
		];

		$temporaryParams = $params;
		$temporaryParams['AVATAR_SIZE'] = ($params['AVATAR_SIZE_COMMON'] ?? $params['AVATAR_SIZE']);

		$commentEventFields = [
			'EVENT' => $comment,
			'LOG_DATE' => $comment['LOG_DATE'],
			'LOG_DATE_TS' => makeTimeStamp($comment['LOG_DATE']),
			'LOG_DATE_DAY' => convertTimeStamp(makeTimeStamp($comment['LOG_DATE']), 'SHORT'),
			'LOG_TIME_FORMAT' => $timeFormated,
			'LOG_DATETIME_FORMAT' => $dateTimeFormated,
			'TITLE_TEMPLATE' => '',
			'TITLE' => '',
			'TITLE_FORMAT' => '', // need to use url here
			'ENTITY_NAME' => (
				$comment["ENTITY_TYPE"] === SONET_ENTITY_GROUP
					? $comment["GROUP_NAME"]
					: \CUser::formatName($params['NAME_TEMPLATE'], $userFields, $useLogin)
			),
			'ENTITY_PATH' => $path2Entity,
			'CREATED_BY' => $createdByFields,
			'AVATAR_SRC' => \CSocNetLogTools::formatEvent_CreateAvatar($comment, $temporaryParams)
		];

		$commentEventData = \CSocNetLogTools::findLogCommentEventByID($comment['EVENT_ID']);
		$formattedFields = [];

		if (
			is_array($commentEventData)
			&& array_key_exists('CLASS_FORMAT', $commentEventData)
			&& array_key_exists('METHOD_FORMAT', $commentEventData)
		)
		{
			$logFields = (
				($params['USER_COMMENTS'] ?? '') === "Y"
					? []
					: [
						'TITLE' => $comment['~LOG_TITLE'] ?? '',
						'URL' => $comment['~LOG_URL'] ?? '',
						'PARAMS' => $comment['~LOG_PARAMS'] ?? null
					]
			);

			$formattedFields = call_user_func([ $commentEventData['CLASS_FORMAT'], $commentEventData['METHOD_FORMAT'] ], $comment, $params, false, $logFields);

			if (
				($params['USE_COMMENTS'] ?? null) !== 'Y'
				&& array_key_exists('CREATED_BY', $formattedFields)
				&& isset($formattedFields['CREATED_BY']['TOOLTIP_FIELDS']))
			{
				$commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'] = $formattedFields['CREATED_BY']['TOOLTIP_FIELDS'];
			}
		}

		$commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
			[
				'POST_TEXT' => $comment['MESSAGE'],
				'SHARE_DEST' => $comment['SHARE_DEST'],
				'SOURCE_ID' => (int)$comment['SOURCE_ID'],
				'EVENT_ID' => $comment['EVENT_ID'],
				'RATING_TYPE_ID' => $comment['RATING_TYPE_ID'],
			],
			[
				'eventId' => $comment['EVENT_ID']
			]
		);

		if ($commentAuxProvider)
		{
			$commentAuxProvider->setOptions([
				'suffix' => (!empty($params['COMMENT_ENTITY_SUFFIX']) ? $params['COMMENT_ENTITY_SUFFIX'] : ''),
				'logId' => $comment['LOG_ID'],
				'cache' => true,
				'parseBBCode' => true,
				'uf' => $comment['UF'],
			]);

			$formattedFields["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] = nl2br($commentAuxProvider->getText());
		}
		else
		{
			$message = (string)(
				is_array($formattedFields)
				&& array_key_exists('EVENT_FORMATTED', $formattedFields)
				&& array_key_exists('MESSAGE', $formattedFields['EVENT_FORMATTED'])
					? $formattedFields['EVENT_FORMATTED']['MESSAGE']
					: $commentEventFields['EVENT']['MESSAGE']
			);

			if ($message !== '')
			{
				$formattedFields['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] = \CSocNetTextParser::closetags(htmlspecialcharsback($message));
			}
		}

		$formattedFields['EVENT_FORMATTED']['DATETIME'] = (
			$commentEventFields['LOG_DATE_DAY'] == convertTimeStamp()
				? $timeFormated
				: $dateTimeFormated
		);
		$commentEventFields['EVENT_FORMATTED'] = $formattedFields['EVENT_FORMATTED'];
		$commentEventFields['EVENT_FORMATTED']['URLPREVIEW'] = false;

		if (
			isset($comment['UF']['UF_SONET_COM_URL_PRV'])
			&& !empty($comment['UF']['UF_SONET_COM_URL_PRV']['VALUE'])
		)
		{
			$css = $APPLICATION->sPath2css;
			$js = $APPLICATION->arHeadScripts;

			$urlPreviewText = ComponentHelper::getUrlPreviewContent($comment['UF']['UF_SONET_COM_URL_PRV'], array(
				'MOBILE' => 'N',
				'NAME_TEMPLATE' => $params['NAME_TEMPLATE'],
				'PATH_TO_USER' => $params['~PATH_TO_USER']
			));

			if (!empty($urlPreviewText))
			{
				$commentEventFields['EVENT_FORMATTED']['URLPREVIEW'] = true;
				$commentEventFields['EVENT_FORMATTED']['FULL_MESSAGE_CUT'] .= $urlPreviewText;
			}

			$assets['CSS'] = array_merge($assets['CSS'], array_diff($APPLICATION->sPath2css, $css));
			$assets['JS'] = array_merge($assets['JS'], array_diff($APPLICATION->arHeadScripts, $js));

			$commentEventFields['UF_HIDDEN']['UF_SONET_COM_URL_PRV'] = $comment['UF']['UF_SONET_COM_URL_PRV'];
			unset($comment['UF']['UF_SONET_COM_URL_PRV']);
		}

		$commentEventFields['UF'] = $comment['UF'];

		if (
			isset($commentEventFields['EVENT_FORMATTED'])
			&& is_array($commentEventFields['EVENT_FORMATTED'])
		)
		{
			$fields2Cache = [
				'DATETIME',
				'MESSAGE',
				'FULL_MESSAGE_CUT',
				'ERROR_MSG',
				'URLPREVIEW',
			];
			foreach ($commentEventFields['EVENT_FORMATTED'] as $field => $value)
			{
				if (!in_array($field, $fields2Cache, true))
				{
					unset($commentEventFields['EVENT_FORMATTED'][$field]);
				}
			}
		}

		if (
			isset($commentEventFields['EVENT'])
			&& is_array($commentEventFields['EVENT'])
		)
		{
			if (!empty($commentEventFields["EVENT"]["URL"]))
			{
				$commentEventFields['EVENT']['URL'] = str_replace(
					'#GROUPS_PATH#',
					Option::get('socialnetwork', 'workgroups_page', '/workgroups/', SITE_ID),
					$commentEventFields['EVENT']['URL']
				);
			}

			$fields2Cache = [
				'ID',
				'SOURCE_ID',
				'EVENT_ID',
				'USER_ID',
				'LOG_DATE',
				'RATING_TYPE_ID',
				'RATING_ENTITY_ID',
				'URL',
				'SHARE_DEST'
			];

			if (
				(
					isset($params['MAIL'])
					&& $params['MAIL'] === 'Y'
				)
				|| (
					isset($params['COMMENT_ID'])
					&& (int)$params['COMMENT_ID'] > 0
				)
			)
			{
				$fields2Cache[] = 'MESSAGE';
			}

			foreach ($commentEventFields['EVENT'] as $field => $value)
			{
				if (!in_array($field, $fields2Cache, true))
				{
					unset($commentEventFields['EVENT'][$field]);
				}
			}
		}

		if (
			isset($commentEventFields['CREATED_BY'])
			&& is_array($commentEventFields['CREATED_BY'])
		)
		{
			$fields2Cache = [
				'TOOLTIP_FIELDS',
				'FORMATTED',
				'URL'
			];
			foreach ($commentEventFields['CREATED_BY'] as $field => $value)
			{
				if (!in_array($field, $fields2Cache, true))
				{
					unset($commentEventFields['CREATED_BY'][$field]);
				}
			}

			if (
				isset($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'])
				&& is_array($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'])
			)
			{
				$fields2Cache = [
					'ID',
					'PATH_TO_SONET_USER_PROFILE',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'PERSONAL_GENDER',
					'LOGIN',
					'EMAIL',
					'EXTERNAL_AUTH_ID',
					'UF_USER_CRM_ENTITY',
					'UF_DEPARTMENT'
				];
				foreach ($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'] as $field => $value)
				{
					if (!in_array($field, $fields2Cache, true))
					{
						unset($commentEventFields['CREATED_BY']['TOOLTIP_FIELDS'][$field]);
					}
				}
			}
		}

		foreach ($commentEventFields['EVENT'] as $key => $value)
		{
			if (mb_strpos($key, '~') === 0)
			{
				unset($commentEventFields['EVENT'][$key]);
			}
		}

		return $commentEventFields;
	}

	public static function formatStubEvent($arFields, $arParams): array
	{
		$arResult = [
			'HAS_COMMENTS' => 'N',
			'EVENT' => $arFields,
			'EVENT_FORMATTED' => [
				'TITLE' => '',
				'TITLE_24' => '',
				'URL' => '',
				'MESSAGE' => '',
				'SHORT_MESSAGE' => '',
				'IS_IMPORTANT' => false,
				'STUB' => true,
			],
		];
		$arResult['ENTITY']['FORMATTED']['NAME'] = '';
		$arResult['ENTITY']['FORMATTED']['URL'] = '';
		$arResult['AVATAR_SRC'] = \CSocNetLog::FormatEvent_CreateAvatar($arFields, $arParams, 'CREATED_BY');

		$arFieldsTooltip = [
			'ID' => $arFields['USER_ID'],
			'NAME' => $arFields['~CREATED_BY_NAME'],
			'LAST_NAME' => $arFields['~CREATED_BY_LAST_NAME'],
			'SECOND_NAME' => $arFields['~CREATED_BY_SECOND_NAME'],
			'LOGIN' => $arFields['~CREATED_BY_LOGIN'],
		];
		$arResult['CREATED_BY']['TOOLTIP_FIELDS'] = \CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);

		return $arResult;
	}


}
