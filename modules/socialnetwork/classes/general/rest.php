<?php

use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Item\Helper;
use Bitrix\Socialnetwork\UserToGroupTable;

if (!Loader::includeModule('rest'))
{
	return;
}

class CSocNetLogRestService extends IRestService
{
	public const PERM_DENY = 'D';
	public const PERM_READ = 'R';
	public const PERM_WRITE = 'W';

	private static $arAllowedOperations = array('', '!', '<', '<=', '>', '>=', '><', '!><', '?', '=', '!=', '%', '!%', '');

	public static function OnRestServiceBuildDescription()
	{
		return array(
			"log" => array(
				"log.blogpost.get" => array("CSocNetLogRestService", "getBlogPost"),
				'log.blogpost.user.get' =>  array('callback' => array(__CLASS__, 'getUserBlogPost'), 'options' => array('private' => true)),
				"log.blogpost.add" => array("CSocNetLogRestService", "addBlogPost"),
				"log.blogpost.update" => array("CSocNetLogRestService", "updateBlogPost"),
				"log.blogpost.share" => array("CSocNetLogRestService", "shareBlogPost"),
				"log.blogpost.delete" => array("CSocNetLogRestService", "deleteBlogPost"),
				"log.blogpost.getusers.important" => array("CSocNetLogRestService", "getBlogPostUsersImprtnt"),
				"log.blogcomment.add" => array("CSocNetLogRestService", "addBlogComment"),
				'log.blogcomment.user.get' =>  array('callback' => array(__CLASS__, 'getUserBlogComment'), 'options' => array('private' => true)),
				"log.blogcomment.delete" => array("CSocNetLogRestService", "deleteBlogComment"),
				'log.comment.user.get' =>  array('callback' => array(__CLASS__, 'getUserLogComment'), 'options' => array('private' => true)),
				"log.comment.delete" => array("CSocNetLogRestService", "deleteLogComment"),
				CRestUtil::EVENTS => array(
					'onLivefeedPostAdd' => self::createEventInfo('socialnetwork', 'OnAfterSocNetLogAdd', array('CSocNetLogBlogPostRestProxy', 'processEvent')),
					'onLivefeedPostUpdate' => self::createEventInfo('socialnetwork', 'OnAfterSocNetLogUpdate', array('CSocNetLogBlogPostRestProxy', 'processEvent')),
					'onLivefeedPostDelete' => self::createEventInfo('socialnetwork', 'OnSocNetLogDelete', array('CSocNetLogBlogPostRestProxy', 'processEvent')),
				),
			),
			"sonet_group" => array(
				"sonet_group.get" => array("CSocNetLogRestService", "getGroup"),
				"sonet_group.create" => array("CSocNetLogRestService", "createGroup"),
				"sonet_group.update" => array("CSocNetLogRestService", "updateGroup"),
				"sonet_group.delete" => array("CSocNetLogRestService", "deleteGroup"),
				"sonet_group.setowner" => array("CSocNetLogRestService", "setGroupOwner"),
				"sonet_group.user.get" => array("CSocNetLogRestService", "getGroupUsers"),
				"sonet_group.user.invite" => array("CSocNetLogRestService", "inviteGroupUsers"),
				"sonet_group.user.request" => array("CSocNetLogRestService", "requestGroupUser"),
				"sonet_group.user.add" => array("CSocNetLogRestService", "addGroupUsers"),
				"sonet_group.user.update" => array("CSocNetLogRestService", "updateGroupUsers"),
				"sonet_group.user.delete" => array("CSocNetLogRestService", "deleteGroupUsers"),
				"sonet_group.user.groups" => array("CSocNetLogRestService", "getUserGroups"),
				"sonet_group.feature.access" => array("CSocNetLogRestService", "getGroupFeatureAccess"),
				"sonet_group_subject.get" => array("CSocNetLogRestService", "getGroupSubject"),
				"sonet_group_subject.add" => array("CSocNetLogRestService", "addGroupSubject"),
				"sonet_group_subject.update" => array("CSocNetLogRestService", "updateGroupSubject"),
				"sonet_group_subject.delete" => array("CSocNetLogRestService", "deleteGroupSubject"),
				CRestUtil::EVENTS => array(
					'onSonetGroupAdd' => self::createEventInfo('socialnetwork', 'OnSocNetGroupAdd', array('CSocNetGroupRestProxy', 'processEvent')),
					'onSonetGroupUpdate' => self::createEventInfo('socialnetwork', 'OnSocNetGroupUpdate', array('CSocNetGroupRestProxy', 'processEvent')),
					'onSonetGroupDelete' => self::createEventInfo('socialnetwork', 'OnSocNetGroupDelete', array('CSocNetGroupRestProxy', 'processEvent')),
					'onSonetGroupSubjectAdd' => self::createEventInfo('socialnetwork', 'OnSocNetGroupSubjectAdd', array('CSocNetGroupSubjectRestProxy', 'processEvent')),
					'onSonetGroupSubjectUpdate' => self::createEventInfo('socialnetwork', 'OnSocNetGroupSubjectUpdate', array('CSocNetGroupSubjectRestProxy', 'processEvent')),
					'onSonetGroupSubjectDelete' => self::createEventInfo('socialnetwork', 'OnSocNetGroupSubjectDelete', array('CSocNetGroupSubjectRestProxy', 'processEvent'))
				),
				CRestUtil::PLACEMENTS => array(
					'SONET_GROUP_DETAIL_TAB' => array()
				),
			)
		);
	}

	public static function createEventInfo($moduleName, $eventName, array $callback)
	{
		return array($moduleName, $eventName, $callback, array('category' => \Bitrix\Rest\Sqs::CATEGORY_DEFAULT));
	}

	private static function getBlogPostEventId()
	{
		static $blogPostEventIdList = null;
		if ($blogPostEventIdList === null)
		{
			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
			$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
		}

		$arEventId = $blogPostEventIdList;
		$arEventIdFullset = array();
		foreach ($arEventId as $eventId)
		{
			$arEventIdFullset = array_merge($arEventIdFullset, CSocNetLogTools::FindFullSetByEventID($eventId));
		}

		return array_unique($arEventIdFullset);
	}

	private static function getBlogCommentEventId()
	{
		static $blogCommentEventIdList = null;
		if ($blogCommentEventIdList === null)
		{
			$blogCommentLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogComment;
			$blogCommentEventIdList = $blogCommentLivefeedProvider->getEventId();
		}

		return $blogCommentEventIdList;
	}

	private static function getLogCommentEventId()
	{
		static $logCommentEventIdList = null;
		if ($logCommentEventIdList === null)
		{
			$logCommentLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\LogComment;
			$logCommentEventIdList = $logCommentLivefeedProvider->getEventId();
		}

		return $logCommentEventIdList;
	}

	public static function getBlogPost($fields, $n, $server)
	{
		global $USER, $USER_FIELD_MANAGER;

		$result = array();
		if (!CModule::IncludeModule("blog"))
		{
			return $result;
		}

		$tzOffset = CTimeZone::getOffset();
		$arOrder = [ 'LOG_UPDATE' => 'DESC' ];

		$res = CUser::getById($USER->getId());
		if ($userFields = $res->Fetch())
		{
			$currentUserIntranet = (
				!empty($userFields["UF_DEPARTMENT"])
				&& is_array($userFields["UF_DEPARTMENT"])
				&& (int)$userFields["UF_DEPARTMENT"][0] > 0
			);

			$extranetSiteId = false;
			if (ModuleManager::isModuleInstalled('extranet'))
			{
				$extranetSiteId = Option::get("extranet", "extranet_site");
			}

			if (
				empty($extranetSiteId)
				|| $currentUserIntranet
			)
			{
				$userSiteFields = CSocNetLogComponent::getSiteByDepartmentId($userFields["UF_DEPARTMENT"]);
				if (!empty($userSiteFields))
				{
					$siteId = $userSiteFields['LID'];
				}
			}
			elseif (
				!empty($extranetSiteId)
				&& !$currentUserIntranet
			)
			{
				$siteId = $extranetSiteId;
			}
			else
			{
				$siteId = CSite::getDefSite();
			}
		}

		$filter = [
			"EVENT_ID" => self::getBlogPostEventId(),
			"SITE_ID" => [ $siteId, false ],
			"<=LOG_DATE" => "NOW"
		];

		if (
			isset($fields['POST_ID'])
			&& (int)$fields['POST_ID'] > 0
		)
		{
			$filter['SOURCE_ID'] = $fields['POST_ID'];
		}
		elseif (
			isset($fields['LOG_RIGHTS'])
			&& is_array($fields['LOG_RIGHTS'])
		)
		{
			$filter["LOG_RIGHTS"] = $fields['LOG_RIGHTS'];
		}

		$arListParams = array(
			"CHECK_RIGHTS" => "Y",
			"USE_FOLLOW" => "N",
			"USE_SUBSCRIBE" => "N"
		);

		$dbLog = CSocNetLog::GetList(
			$arOrder,
			$filter,
			false,
			self::getNavData($n),
			array("ID", "SOURCE_ID"),
			$arListParams
		);

		$arPostId = $arPostIdToGet = array();

		while ($arLog = $dbLog->Fetch())
		{
			$arPostId[] = $arLog["SOURCE_ID"];
		}

		$cacheTtl = 2592000;

		foreach ($arPostId as $key => $postId)
		{
			$cacheId = 'blog_post_socnet_rest_'.$postId.'_ru'.($tzOffset <> 0 ? '_'.$tzOffset : '');
			$cacheDir = ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'post',
				'POST_ID' => $postId
			));
			$obCache = new CPHPCache;
			if ($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
			{
				$result[$key] = $obCache->GetVars();
			}
			else
			{
				$arPostIdToGet[$key] = $postId;
			}
			$obCache->EndDataCache();
		}

		if (!empty($arPostIdToGet))
		{
			foreach ($arPostIdToGet as $key => $postId)
			{
				$cacheId = 'blog_post_socnet_rest_'.$postId.'_ru'.($tzOffset <> 0 ? '_'.$tzOffset : '');
				$cacheDir = ComponentHelper::getBlogPostCacheDir(array(
					'TYPE' => 'post_general',
					'POST_ID' => $postId
				));
				$obCache = new CPHPCache;
				$obCache->InitCache($cacheTtl, $cacheId, $cacheDir);

				$obCache->StartDataCache();

				$dbPost = CBlogPost::GetList(
					array(),
					array("ID" => $postId),
					false,
					false,
					array(
						"ID",
						"BLOG_ID",
						"PUBLISH_STATUS",
						"TITLE",
						"AUTHOR_ID",
						"ENABLE_COMMENTS",
						"NUM_COMMENTS",
						"VIEWS",
						"CODE",
						"MICRO",
						"DETAIL_TEXT",
						"DATE_PUBLISH",
						"CATEGORY_ID",
						"HAS_SOCNET_ALL",
						"HAS_TAGS",
						"HAS_IMAGES",
						"HAS_PROPS",
						"HAS_COMMENT_IMAGES"
					)
				);

				if ($arPost = $dbPost->Fetch())
				{
					if (!empty($arPost['DETAIL_TEXT']))
					{
						$arPost['DETAIL_TEXT'] = \Bitrix\Main\Text\Emoji::decode($arPost['DETAIL_TEXT']);
					}

					if ($arPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
					{
						unset($arPost);
					}
					else
					{
						if (!empty($arPost['DATE_PUBLISH']))
						{
							$arPost['DATE_PUBLISH'] = CRestUtil::convertDateTime($arPost['DATE_PUBLISH']);
						}

						if ($arPost["HAS_PROPS"] !== 'N')
						{
							$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $arPost["ID"], LANGUAGE_ID);
							$arPost = array_merge($arPost, $arPostFields);
						}

						if (
							!empty($arPost['UF_BLOG_POST_FILE'])
							&& !empty($arPost['UF_BLOG_POST_FILE']['VALUE'])
						)
						{
							$arPost['FILES'] = $arPost['UF_BLOG_POST_FILE']['VALUE'];
						}

						$result[$key] = $arPost;
					}
				}

				$obCache->EndDataCache($arPost);
			}
		}

		ksort($result);

		return self::setNavData($result, $dbLog);
	}

	public static function getUserBlogPost($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		global $USER;

		$result = Array(
			'POSTS' => array(),
			'FILES' => array(),
		);

		if (!Loader::includeModule("blog"))
		{
			return $result;
		}

		$userId = (
			isset($arParams["USER_ID"])
			&& (int)$arParams["USER_ID"] > 0
			&& self::isAdmin()
				? $arParams["USER_ID"]
				: $USER->getId()
		);

		$otherUserMode = ($userId != $USER->getId());

		if ($userId <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['FIRST_ID']))
		{
			$options['FIRST_ID'] = (int)$arParams['FIRST_ID'];
		}
		else
		{
			$options['LAST_ID'] = isset($arParams['LAST_ID']) && (int)$arParams['LAST_ID'] > 0 ? (int)$arParams['LAST_ID'] : 0;
		}

		$options['LIMIT'] = isset($arParams['LIMIT'])? ((int)$arParams['LIMIT'] > 1000 ? 1000 : (int)$arParams['LIMIT']) : 100;

		$filter = [
			'=USER_ID' => $userId,
			'@EVENT_ID' => self::getBlogPostEventId()
		];

		if (isset($options['FIRST_ID']))
		{
			$order = [];

			if ((int)$options['FIRST_ID'] > 0)
			{
				$filter['>ID'] = $options['FIRST_ID'];
			}
		}
		else
		{
			$order = [ 'ID' => 'DESC' ];

			if (isset($options['LAST_ID']) && (int)$options['LAST_ID'] > 0)
			{
				$filter['<ID'] = (int)$options['LAST_ID'];
			}
		}

		$logIdList = array();

		$res = Bitrix\Socialnetwork\LogTable::getList(array(
			'filter' => $filter,
			'select' => array(
				'ID', 'SOURCE_ID'
			),
			'order' => $order,
			'limit' => $options['LIMIT']
		));

		$postIdList = array();
		while ($logFields = $res->fetch())
		{
			if ((int)$logFields['SOURCE_ID'] > 0)
			{
				$postIdList[] = $logFields['SOURCE_ID'];
				$logIdList[$logFields['SOURCE_ID']] = $logFields['ID'];
			}
		}

		$postIdList = array_unique($postIdList);
		if (empty($postIdList))
		{
			return $result;
		}

		$res = Bitrix\Blog\PostTable::getList([
			'filter' => [
				'@ID' => $postIdList,
			],
			'select' => [
				'ID', 'DATE_CREATE', 'TITLE', 'DETAIL_TEXT', 'UF_BLOG_POST_FILE'
			],
			'order' => [ 'ID' => 'DESC' ],
		]);

		$attachedIdList = [];
		$postAttachedList = [];

		while ($postFields = $res->fetch())
		{
			$result['POSTS'][$postFields['ID']] = [
				'ID' => (int)$logIdList[$postFields['ID']],
				'POST_ID' => (int)$postFields['ID'],
				'DATE_CREATE' => $postFields['DATE_CREATE'],
				'TITLE' => ($otherUserMode ? '' : (string)$postFields['TITLE']),
				'TEXT' => ($otherUserMode ? '' : (string)$postFields['DETAIL_TEXT']),
				'ATTACH' => [],
			];
			if (!empty($postFields['UF_BLOG_POST_FILE']))
			{
				if (is_array($postFields['UF_BLOG_POST_FILE']))
				{
					$attached = $postFields['UF_BLOG_POST_FILE'];
				}
				elseif ((int)$postFields['UF_BLOG_POST_FILE'] > 0)
				{
					$attached = array((int)$postFields['UF_BLOG_POST_FILE']);
				}
				else
				{
					$attached = array();
				}

				if (!empty($attached))
				{
					$attachedIdList = array_merge($attachedIdList, $attached);
				}

				$postAttachedList[$postFields['ID']] = $attached;
			}
		}

		$attachedObjectList = [];

		if (
			!empty($attachedIdList)
			&& Loader::includeModule('disk')
		)
		{
			$res = Bitrix\Disk\AttachedObject::getList([
				'filter' => [
					'@ID' => array_unique($attachedIdList)
				],
				'select' => [ 'ID', 'OBJECT_ID' ],
			]);
			while ($attachedObjectFields = $res->fetch())
			{
				$diskObjectId = $attachedObjectFields['OBJECT_ID'];

				if ($fileData = self::getFileData($diskObjectId))
				{
					$attachedObjectList[$attachedObjectFields['ID']] = $diskObjectId;
					$result['FILES'][$diskObjectId] = $fileData;
				}
			}
		}

		foreach ($result['POSTS'] as $key => $value)
		{
			if ($value['DATE_CREATE'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$result['POSTS'][$key]['DATE_CREATE'] = date('c', $value['DATE_CREATE']->getTimestamp());
			}

			if (!empty($postAttachedList[$key]))
			{
				foreach ($postAttachedList[$key] as $attachedId)
				{
					if (!empty($attachedObjectList[$attachedId]))
					{
						$result['POSTS'][$key]['ATTACH'][] = $attachedObjectList[$attachedId];
					}
				}
			}

			$result['POSTS'][$key] = array_change_key_case($result['POSTS'][$key], CASE_LOWER);
		}

		$result['POSTS'] = array_values($result['POSTS']);
		$result['FILES'] = self::convertFileData($result['FILES']);

		return $result;
	}

	public static function addBlogPost($arFields)
	{
		global $APPLICATION;

		try
		{
			$postId = Helper::addBlogPost($arFields, \Bitrix\Main\Engine\Controller::SCOPE_REST);
			if ($postId <= 0)
			{
				$e = $APPLICATION->getException();
				throw new Exception($e ? $e->getString() : 'Cannot add blog post');
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return $postId;
	}

	public static function updateBlogPost($arFields)
	{
		global $APPLICATION;

		try
		{
			$postId = Helper::updateBlogPost($arFields, \Bitrix\Main\Engine\Controller::SCOPE_REST);
			if ($postId <= 0)
			{
				$e = $APPLICATION->getException();
				throw new Exception($e ? $e->getString() : 'Cannot update blog post');
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return $postId;
	}

	public static function deleteBlogPost($arFields): bool
	{
		try
		{
			$result = Helper::deleteBlogPost([
				'POST_ID' => (int)$arFields['POST_ID'],
			]);
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	public static function shareBlogPost($fields)
	{
		global $USER;

		$postId = (int)$fields['POST_ID'];

		if ($postId <= 0)
		{
			throw new Exception('Wrong post ID');
		}

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$siteId = (
			is_set($fields, "SITE_ID")
			&& !empty($fields["SITE_ID"])
				? $fields["SITE_ID"]
				: SITE_ID
		);

		$blogId = false;

		if (
			!is_set($fields, "BLOG_ID")
			|| (int)$fields["BLOG_ID"] <= 0
		)
		{
			$res = \Bitrix\Blog\PostTable::getList(array(
				'filter' => array(
					'=ID' => $postId
				),
				'select' => array('BLOG_ID')
			));
			if (
				($postFields = $res->fetch())
				&& !empty($postFields['BLOG_ID'])
			)
			{
				$blogId = (int)$postFields['BLOG_ID'];
			}
		}
		else
		{
			$blogId = (int)$fields["BLOG_ID"];
		}

		$blogPostPermsNewList = $fields['DEST'];

		foreach ($blogPostPermsNewList as $key => $code)
		{
			if (
				!preg_match('/^SG(\d+)$/', $code, $matches)
				&& !preg_match('/^U(\d+)$/', $code, $matches)
				&& !preg_match('/^UE(.+)$/', $code, $matches)
				&& !preg_match('/^DR(\d+)$/', $code, $matches)
				&& $code !== 'UA'
			)
			{
				unset($blogPostPermsNewList[$key]);
			}
		}

		if (empty($blogPostPermsNewList))
		{
			throw new Exception('Wrong destinations');
		}

		if (!is_array($blogPostPermsNewList))
		{
			$blogPostPermsNewList = array($blogPostPermsNewList);
		}

		$currentUserId = (
			isset($fields["USER_ID"])
			&& (int)$fields["USER_ID"] > 0
			&& self::isAdmin()
				? $fields["USER_ID"]
				: $USER->getId()
		);

		$currentUserPerm = self::getBlogPostPerm(array(
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId
		));

		if ($currentUserPerm <= \Bitrix\Blog\Item\Permissions::READ)
		{
			throw new Exception('No read perms');
		}

		$resultFields = array(
			'ERROR_MESSAGE' => false,
			'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH
		);

		if (ModuleManager::isModuleInstalled('mail')
			&& ModuleManager::isModuleInstalled('intranet')
			&& (
				!Loader::includeModule('bitrix24')
				|| CBitrix24::isEmailConfirmed()
			)
		)
		{
			$destinationList = $blogPostPermsNewList;
			ComponentHelper::processBlogPostNewMailUserDestinations($destinationList);
			$blogPostPermsNewList = array_unique($destinationList);
		}

		$permsNew = ComponentHelper::checkBlogPostDestinationList(array(
			'DEST' => $blogPostPermsNewList,
			'SITE_ID' => $siteId,
			'AUTHOR_ID' => $currentUserId,
		), $resultFields);

		if ($resultFields['ERROR_MESSAGE'])
		{
			throw new Exception($resultFields['ERROR_MESSAGE']);
		}
		elseif ($resultFields['PUBLISH_STATUS'] != BLOG_PUBLISH_STATUS_PUBLISH)
		{
			throw new Exception('No permissions to share by this user (ID ='.$currentUserId.')');
		}

		$permsFull = array();
		$blogPostPermsOldList = CBlogPost::getSocNetPerms($postId);

		foreach ($blogPostPermsOldList as $type => $val)
		{
			foreach ($val as $id => $values)
			{
				if ($type !== 'U')
				{
					$permsFull[] = $type.$id;
				}
				else
				{
					$permsFull[] = (
						in_array('US' . $id, $values)
							? 'UA'
							: $type . $id
						);
				}
			}
		}

		foreach ($permsNew as $key => $code)
		{
			if (!in_array($code, $permsFull))
			{
				$permsFull[] = $code;
			}
			else
			{
				unset($permsNew[$key]);
			}
		}

		if (!empty($permsNew))
		{
			ComponentHelper::processBlogPostShare(
				array(
					"POST_ID" => $postId,
					"BLOG_ID" => $blogId,
					"SITE_ID" => $siteId,
					"SONET_RIGHTS" => $permsFull,
					"NEW_RIGHTS" => $permsNew,
					"USER_ID" => $currentUserId
				),
				array(
					'PATH_TO_POST' => Option::get("socialnetwork", "userblogpost_page", false, $siteId)
				)
			);
		}

		return true;
	}

	public static function getBlogPostUsersImprtnt($fields)
	{
		global $CACHE_MANAGER, $USER;

		if (!is_array($fields))
		{
			throw new Exception('Incorrect input data');
		}

		$arParams["postId"] = (int)$fields['POST_ID'];

		if ($arParams['postId'] <= 0)
		{
			throw new Exception('Wrong post ID');
		}

		$arParams["nTopCount"] = 500;
		$arParams["paramName"] = 'BLOG_POST_IMPRTNT';
		$arParams["paramValue"] = 'Y';

		$result = array();

		$cache = new CPHPCache();
		$cache_id = "blog_post_param_".serialize(array(
			$arParams["postId"],
			$arParams["nTopCount"],
			$arParams["paramName"],
			$arParams["paramValue"]
		));
		$cache_path = $CACHE_MANAGER->GetCompCachePath(CComponentEngine::MakeComponentPath("socialnetwork.blog.blog"))."/".$arParams["postId"];
		$cache_time = (defined("BX_COMP_MANAGED_CACHE") ? 3600*24*365 : 600);

		if ($cache->InitCache($cache_time, $cache_id, $cache_path))
		{
			$result = $cache->GetVars();
		}
		else
		{
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);

			if (CModule::IncludeModule("blog"))
			{
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->StartTagCache($cache_path);
					$CACHE_MANAGER->RegisterTag($arParams["paramName"].$arParams["postId"]);
				}

				if ($arBlogPost = CBlogPost::GetByID($arParams["postId"]))
				{
					$postPerms = CBlogPost::GetSocNetPostPerms($arParams["postId"], true, $USER->GetID(), $arBlogPost["AUTHOR_ID"]);
					if ($postPerms >= BLOG_PERMS_READ)
					{
						$res = CBlogUserOptions::GetList(
							[],
							[
								'POST_ID' => $arParams["postId"],
								'NAME' => $arParams["paramName"],
								'VALUE' => $arParams["paramValue"],
								'USER_ACTIVE' => 'Y',
							],
							[
								"nTopCount" => $arParams["nTopCount"],
								'SELECT' => [ 'USER_ID' ],
							]
						);
						if ($res)
						{
							while ($res = $res->Fetch())
							{
								$result[] = $res["USER_ID"];
							}
						}
					}
				}

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->EndTagCache();
				}

				$cache->EndDataCache($result);
			}
		}

		return $result;
	}

	public static function getUserBlogComment($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		global $USER;

		$result = Array(
			'COMMENTS' => array(),
			'FILES' => array(),
		);

		if (!Loader::includeModule("blog"))
		{
			return $result;
		}

		$userId = (
			isset($arParams["USER_ID"])
			&& (int)$arParams['USER_ID'] > 0
			&& self::isAdmin()
				? $arParams["USER_ID"]
				: $USER->getId()
		);

		$otherUserMode = ($userId != $USER->getId());

		if ($userId <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['FIRST_ID']))
		{
			$options['FIRST_ID'] = (int)$arParams['FIRST_ID'];
		}
		else
		{
			$options['LAST_ID'] = (
				isset($arParams['LAST_ID']) && (int)$arParams['LAST_ID'] > 0
					? (int)$arParams['LAST_ID']
					: 0
			);
		}

		$options['LIMIT'] = (
			isset($arParams['LIMIT'])
				? (
					(int)$arParams['LIMIT'] > 1000
						? 1000
						: (int)$arParams['LIMIT'])
				: 100
		);

		$filter = [
			'=USER_ID' => $userId,
			'@EVENT_ID' => self::getBlogCommentEventId(),
		];

		if (isset($options['FIRST_ID']))
		{
			$order = array();

			if ((int)$options['FIRST_ID'] > 0)
			{
				$filter['>ID'] = $options['FIRST_ID'];
			}
		}
		else
		{
			$order = Array('ID' => 'DESC');

			if (isset($options['LAST_ID']) && (int)$options['LAST_ID'] > 0)
			{
				$filter['<ID'] = (int)$options['LAST_ID'];
			}
		}

		$logCommentIdList = array();

		$res = Bitrix\Socialnetwork\LogCommentTable::getList(array(
			'filter' => $filter,
			'select' => array(
				'ID', 'SOURCE_ID'
			),
			'order' => $order,
			'limit' => $options['LIMIT']
		));

		$commentIdList = [];
		while ($logCommentFields = $res->fetch())
		{
			if ((int)$logCommentFields['SOURCE_ID'] > 0)
			{
				$commentIdList[] = $logCommentFields['SOURCE_ID'];
				$logCommentIdList[$logCommentFields['SOURCE_ID']] = $logCommentFields['ID'];
			}
		}

		$commentIdList = array_unique($commentIdList);
		if (empty($commentIdList))
		{
			return $result;
		}

		$res = Bitrix\Blog\CommentTable::getList(array(
			'filter' => array(
				'@ID' => $commentIdList
			),
			'select' => array(
				'ID', 'AUTHOR_ID', 'POST_ID', 'DATE_CREATE', 'POST_TEXT', 'SHARE_DEST', 'UF_BLOG_COMMENT_FILE'
			),
			'order' => array('ID' => 'DESC')
		));

		$attachedIdList = array();
		$commentAttachedList = array();

		$loadedSocialnetwork = Loader::includeModule('socialnetwork');

		while ($commentFields = $res->fetch())
		{
			$result['COMMENTS'][$commentFields['ID']] = array(
				'ID' => (int)$logCommentIdList[$commentFields['ID']],
				'COMMENT_ID' => (int)$commentFields['ID'],
				'POST_ID' => (int)$commentFields['POST_ID'],
				'DATE' => $commentFields['DATE_CREATE'],
				'TEXT' => ($otherUserMode ? '' : (string)$commentFields['POST_TEXT']),
				'ATTACH' => array()
			);

			if (
				$loadedSocialnetwork
				&& ($commentAuxProvider = \Bitrix\Socialnetwork\CommentAux\Base::findProvider(
					$commentFields,
					array(
						"mobile" => false,
						"bPublicPage" => true,
						"cache" => true
					)
				)
			))
			{
				$result['COMMENTS'][$commentFields['ID']]['TEXT'] = $commentAuxProvider->getText();
			}

			if (!empty($commentFields['UF_BLOG_COMMENT_FILE']))
			{
				if (is_array($commentFields['UF_BLOG_COMMENT_FILE']))
				{
					$attached = $commentFields['UF_BLOG_COMMENT_FILE'];
				}
				elseif ((int)$commentFields['UF_BLOG_COMMENT_FILE'] > 0)
				{
					$attached = [ (int)$commentFields['UF_BLOG_COMMENT_FILE'] ];
				}
				else
				{
					$attached = [];
				}

				if (!empty($attached))
				{
					$attachedIdList = array_merge($attachedIdList, $attached);
				}

				$commentAttachedList[$commentFields['ID']] = $attached;
			}
		}

		$attachedObjectList = array();

		if (
			!empty($attachedIdList)
			&& Loader::includeModule('disk')
		)
		{
			$res = Bitrix\Disk\AttachedObject::getList(array(
				'filter' => array(
					'@ID' => array_unique($attachedIdList)
				),
				'select' => array('ID', 'OBJECT_ID')
			));
			while ($attachedObjectFields = $res->fetch())
			{
				$diskObjectId = $attachedObjectFields['OBJECT_ID'];
				if ($fileData = self::getFileData($diskObjectId))
				{
					$attachedObjectList[$attachedObjectFields['ID']] = $diskObjectId;
					$result['FILES'][$diskObjectId] = $fileData;
				}
			}
		}

		foreach ($result['COMMENTS'] as $key => $value)
		{
			if ($value['DATE'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$result['COMMENTS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			if (!empty($commentAttachedList[$key]))
			{
				foreach ($commentAttachedList[$key] as $attachedId)
				{
					if (!empty($attachedObjectList[$attachedId]))
					{
						$result['COMMENTS'][$key]['ATTACH'][] = $attachedObjectList[$attachedId];
					}
				}
			}

			$result['COMMENTS'][$key] = array_change_key_case($result['COMMENTS'][$key], CASE_LOWER);
		}

		$result['COMMENTS'] = array_values($result['COMMENTS']);
		$result['FILES'] = self::convertFileData($result['FILES']);

		return $result;
	}

	public static function addBlogComment($fields)
	{
		global $USER;

		$authorId = (int)(
			isset($fields["USER_ID"])
			&& (int)$fields["USER_ID"] > 0
			&& self::isAdmin()
				? $fields["USER_ID"]
				: $USER->getId()
		);

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('No blog module installed');
		}

		$postId = (int)$fields['POST_ID'];
		if ($postId <= 0)
		{
			throw new Exception('No post found');
		}

		$res = CBlogPost::getList(
			array(),
			array(
				"ID" => $postId
			),
			false,
			false,
			array("ID", "BLOG_ID", "AUTHOR_ID", "BLOG_OWNER_ID", "TITLE")
		);

		$post = $res->fetch();
		if (!$post)
		{
			throw new Exception('No post found');
		}

		$blog = CBlog::getById($post["BLOG_ID"]);
		if (!$blog)
		{
			throw new Exception('No blog found');
		}

		if (
			empty($fields["FILES"])
			&& !\Bitrix\Blog\Item\Comment::checkDuplicate(array(
				'MESSAGE' => $fields["TEXT"],
				'BLOG_ID' => $post['BLOG_ID'],
				'POST_ID' => $post['ID'],
				'AUTHOR_ID' => $authorId,
		))
		)
		{
			throw new Exception('Duplicate comment');
		}

		$userIP = CBlogUser::getUserIP();

		$commentFields = array(
			"POST_ID" => $post['ID'],
			"BLOG_ID" => $post['BLOG_ID'],
			"TITLE" => '',
			"POST_TEXT" => $fields["TEXT"],
			"DATE_CREATE" => convertTimeStamp(time() + CTimeZone::getOffset(), "FULL"),
			"AUTHOR_IP" => $userIP[0],
			"AUTHOR_IP1" => $userIP[1],
			"URL" => $blog["URL"],
			"PARENT_ID" => false,
			"SEARCH_GROUP_ID" => $blog['GROUP_ID'],
			"AUTHOR_ID" => $authorId
		);

		$perm = \Bitrix\Blog\Item\Permissions::DENY;
		if ((int)$post['AUTHOR_ID'] === $authorId)
		{
			$perm = \Bitrix\Blog\Item\Permissions::FULL;
		}
		else
		{
			$postPerm = CBlogPost::getSocNetPostPerms($post["ID"]);
			if ($postPerm > \Bitrix\Blog\Item\Permissions::DENY)
			{
				$perm = CBlogComment::getSocNetUserPerms($post["ID"], $post["AUTHOR_ID"]);
			}
		}

		if ($perm === \Bitrix\Blog\Item\Permissions::DENY)
		{
			throw new Exception('No permissions');
		}

		if ($perm === \Bitrix\Blog\Item\Permissions::PREMODERATE)
		{
			$commentFields["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_READY;
		}

		$result = CBlogComment::add($commentFields);
		if (!$result)
		{
			throw new Exception('Blog comment hasn\'t been added');
		}

		if (
			isset($fields["FILES"])
			&& Option::get('disk', 'successfully_converted', false)
			&& Loader::includeModule('disk')
			&& ($storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($authorId))
			&& ($folder = $storage->getFolderForUploadedFiles())
		)
		{
			// upload to storage
			$filesList = array();

			foreach ($fields["FILES"] as $tmp)
			{
				$fileFields = CRestUtil::saveFile($tmp);

				if (is_array($fileFields))
				{
					$file = $folder->uploadFile(
						$fileFields, // file array
						array(
							'NAME' => $fileFields["name"],
							'CREATED_BY' => $authorId
						),
						array(),
						true
					);

					if ($file)
					{
						$filesList[] = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$file->getId();
					}
				}
			}

			if (!empty($filesList)) // update post
			{
				CBlogComment::update(
					$result,
					array(
						"HAS_PROPS" => "Y",
						"UF_BLOG_COMMENT_FILE" => $filesList
					)
				);
			}
		}

		\Bitrix\Blog\Item\Comment::actionsAfter(array(
			'MESSAGE' => $commentFields["POST_TEXT"],
			'BLOG_ID' => $post["BLOG_ID"],
			'BLOG_OWNER_ID' => $post["BLOG_OWNER_ID"],
			'POST_ID' => $post["ID"],
			'POST_TITLE' => $post["TITLE"],
			'POST_AUTHOR_ID' => $post["AUTHOR_ID"],
			'COMMENT_ID' => $result,
			'AUTHOR_ID' => $authorId,
		));

		return $result;
	}

	public static function deleteBlogComment($fields)
	{
		global $USER;

		$commentId = (int)$fields['COMMENT_ID'];

		if ($commentId <= 0)
		{
			throw new Exception('Wrong comment ID');
		}

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$currentUserId = (
			isset($fields["USER_ID"])
			&& (int)$fields["USER_ID"] > 0
			&& self::isAdmin()
				? $fields["USER_ID"]
				: $USER->getId()
		);

		$currentUserPerm = self::getBlogCommentPerm(array(
			'USER_ID' => $currentUserId,
			'COMMENT_ID' => $commentId
		));

		if ($currentUserPerm < \Bitrix\Blog\Item\Permissions::FULL)
		{
			throw new Exception('No delete perms');
		}

		$commentFields = \Bitrix\Blog\Item\Comment::getById($commentId)->getFields();
		if (empty($commentId))
		{
			throw new Exception('No comment found');
		}

		if ($result = CBlogComment::Delete($commentId))
		{
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'post_comments',
				'POST_ID' => $commentFields["POST_ID"]
			)));
			CBlogComment::DeleteLog($commentId);
		}

		return (bool)$result;
	}

	public static function getUserLogComment($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		global $USER;

		$result = [
			'COMMENTS' => [],
			'FILES' => [],
		];

		$userId = (
			isset($arParams["USER_ID"])
			&& (int)$arParams["USER_ID"] > 0
			&& self::isAdmin()
				? $arParams["USER_ID"]
				: $USER->getId()
		);

		$otherUserMode = ($userId != $USER->getId());

		if ($userId <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['FIRST_ID']))
		{
			$options['FIRST_ID'] = (int)$arParams['FIRST_ID'];
		}
		else
		{
			$options['LAST_ID'] = (
				isset($arParams['LAST_ID'])
				&& (int)$arParams['LAST_ID'] > 0
					? (int)$arParams['LAST_ID']
					: 0
			);
		}

		$options['LIMIT'] = (
			isset($arParams['LIMIT'])
				? (
					(int)$arParams['LIMIT'] > 1000
						? 1000
						: (int)$arParams['LIMIT'])
				: 100
		);

		$filter = array(
			'=USER_ID' => $userId,
			'@EVENT_ID' => self::getLogCommentEventId()
		);

		if (isset($options['FIRST_ID']))
		{
			$order = array();

			if ((int)$options['FIRST_ID'] > 0)
			{
				$filter['>ID'] = $options['FIRST_ID'];
			}
		}
		else
		{
			$order = [ 'ID' => 'DESC' ];

			if (isset($options['LAST_ID']) && (int)$options['LAST_ID'] > 0)
			{
				$filter['<ID'] = (int)$options['LAST_ID'];
			}
		}

		$res = Bitrix\Socialnetwork\LogCommentTable::getList(array(
			'filter' => $filter,
			'select' => array(
				'ID', 'LOG_ID', 'LOG_DATE', 'MESSAGE', 'UF_SONET_COM_DOC'
			),
			'order' => $order,
			'limit' => $options['LIMIT']
		));

		$attachedIdList = array();

		while ($commentFields = $res->fetch())
		{
			$result['COMMENTS'][$commentFields['ID']] = array(
				'ID' => (int)$commentFields['ID'],
				'COMMENT_ID' => (int)$commentFields['ID'],
				'LOG_ID' => (int)$commentFields['LOG_ID'],
				'DATE' => $commentFields['LOG_DATE'],
				'TEXT' => ($otherUserMode ? '' : (string)$commentFields['MESSAGE']),
				'ATTACH' => array()
			);

			if (!empty($commentFields['UF_SONET_COM_DOC']))
			{
				if (is_array($commentFields['UF_SONET_COM_DOC']))
				{
					$attached = $commentFields['UF_SONET_COM_DOC'];
				}
				elseif ((int)$commentFields['UF_SONET_COM_DOC'] > 0)
				{
					$attached = array((int)$commentFields['UF_SONET_COM_DOC']);
				}
				else
				{
					$attached = array();
				}

				if (!empty($attached))
				{
					$attachedIdList = array_merge($attachedIdList, $attached);
				}

				$commentAttachedList[$commentFields['ID']] = $attached;
			}
		}

		$attachedObjectList = array();

		if (
			!empty($attachedIdList)
			&& Loader::includeModule('disk')
		)
		{
			$res = Bitrix\Disk\AttachedObject::getList(array(
				'filter' => array(
					'@ID' => array_unique($attachedIdList)
				),
				'select' => array('ID', 'OBJECT_ID')
			));
			while ($attachedObjectFields = $res->fetch())
			{
				$diskObjectId = $attachedObjectFields['OBJECT_ID'];

				if ($fileData = self::getFileData($diskObjectId))
				{
					$attachedObjectList[$attachedObjectFields['ID']] = $diskObjectId;
					$result['FILES'][$diskObjectId] = $fileData;
				}
			}
		}

		foreach ($result['COMMENTS'] as $key => $value)
		{
			if ($value['DATE'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$result['COMMENTS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			if (!empty($commentAttachedList[$key]))
			{
				foreach ($commentAttachedList[$key] as $attachedId)
				{
					if (!empty($attachedObjectList[$attachedId]))
					{
						$result['COMMENTS'][$key]['ATTACH'][] = $attachedObjectList[$attachedId];
					}
				}
			}

			$result['COMMENTS'][$key] = array_change_key_case($result['COMMENTS'][$key], CASE_LOWER);
		}

		$result['COMMENTS'] = array_values($result['COMMENTS']);
		$result['FILES'] = self::convertFileData($result['FILES']);

		return $result;
	}

	public static function deleteLogComment($arFields)
	{
		global $USER;

		$commentId = (int)$arFields['COMMENT_ID'];

		if ($commentId <= 0)
		{
			throw new Exception('Wrong comment ID');
		}

		$currentUserId = (
			isset($arFields["USER_ID"])
			&& (int)$arFields["USER_ID"] > 0
			&& self::isAdmin()
				? $arFields["USER_ID"]
				: $USER->getId()
		);

		$commentFields = \Bitrix\Socialnetwork\Item\LogComment::getById($commentId)->getFields();
		if (empty($commentFields))
		{
			throw new Exception('No comment found');
		}

		$currentUserPerm = self::getLogCommentPerm(array(
			'USER_ID' => $currentUserId,
			'COMMENT_ID' => $commentId
		));

		if ($currentUserPerm < self::PERM_WRITE)
		{
			throw new Exception('No write perms');
		}

		$result = CSocNetLogComments::Delete($commentId);

		return (bool)$result;
	}

	private static function getBlogPostPerm($fields)
	{
		return Helper::getBlogPostPerm($fields);
	}

	private static function getBlogCommentPerm($fields)
	{
		global $USER;

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$result = Bitrix\Blog\Item\Permissions::DENY;

		$commentId = $fields['COMMENT_ID'];

		$currentUserId = (int)(
			isset($fields["USER_ID"])
			&& (int)$fields['USER_ID'] > 0
			&& self::isAdmin()
				? $fields["USER_ID"]
				: $USER->getId()
		);

		$arComment = self::getBlogCommentFields($commentId);
		if (empty($arComment))
		{
			return $result;
		}

		if ((int)$arComment["AUTHOR_ID"] === $currentUserId)
		{
			$result = Bitrix\Blog\Item\Permissions::FULL;
		}
		else
		{
			if (CSocNetUser::isUserModuleAdmin($currentUserId, SITE_ID))
			{
				$result = Bitrix\Blog\Item\Permissions::FULL;
			}
			elseif ($arComment['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_PUBLISH)
			{
				$postItem = \Bitrix\Blog\Item\Post::getById($arComment['POST_ID']);
				$permsResult = $postItem->getSonetPerms(array(
					"CHECK_FULL_PERMS" => true
				));
				$result = $permsResult['PERM'];
				if (
					$result <= \Bitrix\Blog\Item\Permissions::READ
					&& $permsResult['READ_BY_OSG']
				)
				{
					$result = Bitrix\Blog\Item\Permissions::READ;
				}
				elseif ($result > \Bitrix\Blog\Item\Permissions::READ)
				{
					$result = \Bitrix\Blog\Item\Permissions::READ;
				}
			}
		}

		return $result;
	}

	private static function getLogCommentPerm($arFields)
	{
		global $USER;

		$result = self::PERM_DENY;

		$commentId = $arFields['COMMENT_ID'];

		$currentUserId = (int)(
			isset($arFields["USER_ID"])
			&& (int)$arFields["USER_ID"] > 0
			&& self::isAdmin()
				? $arFields["USER_ID"]
				: $USER->getId()
		);

		if (
			CSocNetUser::isUserModuleAdmin($currentUserId, SITE_ID)
			|| (
				($arComment = self::getLogCommentFields($commentId))
				&& (int)$arComment['USER_ID'] === $currentUserId
			)
		)
		{
			$result = self::PERM_WRITE;
		}

		return $result;
	}

	private static function getBlogPostFields($postId)
	{
		return Helper::getBlogPostFields($postId);
	}

	private static function getBlogCommentFields($commentId)
	{
		$result = array();
		if ($commentItem = \Bitrix\Blog\Item\Comment::getById($commentId))
		{
			$result = $commentItem->getFields();
		}
		return $result;
	}

	private static function getLogCommentFields($commentId)
	{
		$result = array();
		if ($commentItem = \Bitrix\Socialnetwork\Item\LogComment::getById($commentId))
		{
			$result = $commentItem->getFields();
		}
		return $result;
	}

	private static function getFileData($diskObjectId)
	{
		$result = false;

		$diskObjectId = (int)$diskObjectId;
		if ($diskObjectId <= 0)
		{
			return $result;
		}

		if ($fileModel = \Bitrix\Disk\File::getById($diskObjectId))
		{
			/** @var \Bitrix\Disk\File $fileModel */
			$contentType = 'file';
			$imageParams = false;
			if (\Bitrix\Disk\TypeFile::isImage($fileModel->getName()))
			{
				$contentType = 'image';
				$params = $fileModel->getFile();
				$imageParams = Array(
					'width' => (int)$params['WIDTH'],
					'height' => (int)$params['HEIGHT'],
				);
			}
			else if (\Bitrix\Disk\TypeFile::isVideo($fileModel->getName()))
			{
				$contentType = 'video';
				$params = $fileModel->getView()->getPreviewData();
				$imageParams = Array(
					'width' => (int)$params['WIDTH'],
					'height' => (int)$params['HEIGHT'],
				);
			}

			$isImage = \Bitrix\Disk\TypeFile::isImage($fileModel);
			$urlManager = \Bitrix\Disk\Driver::getInstance()->getUrlManager();

			$result = array(
				'id' => (int)$fileModel->getId(),
				'date' => $fileModel->getCreateTime(),
				'type' => $contentType,
				'name' => $fileModel->getName(),
				'size' => (int)$fileModel->getSize(),
				'image' => $imageParams,
				'authorId' => (int)$fileModel->getCreatedBy(),
				'authorName' => CUser::FormatName(CSite::getNameFormat(false), $fileModel->getCreateUser(), true, true),
				'urlPreview' => (
					$fileModel->getPreviewId()
						? $urlManager->getUrlForShowPreview($fileModel, [ 'width' => 640, 'height' => 640])
						: (
							$isImage
								? $urlManager->getUrlForShowFile($fileModel, [ 'width' => 640, 'height' => 640])
								: null
						)
				),
				'urlShow' => ($isImage ? $urlManager->getUrlForShowFile($fileModel) : $urlManager->getUrlForDownloadFile($fileModel)),
				'urlDownload' => $urlManager->getUrlForDownloadFile($fileModel)
			);
		}

		return $result;
	}

	private static function convertFileData($fileData)
	{
		if (!is_array($fileData))
		{
			return array();
		}

		foreach ($fileData as $key => $value)
		{
			if ($value['date'] instanceof \Bitrix\Main\Type\DateTime)
			{
				$fileData[$key]['date'] = date('c', $value['date']->getTimestamp());
			}

			foreach (['urlPreview', 'urlShow', 'urlDownload'] as $field)
			{
				$url = $fileData[$key][$field];
				if (is_string($url) && $url && mb_strpos($url, 'http') !== 0)
				{
					$fileData[$key][$field] = self::getPublicDomain().$url;
				}
			}
		}

		return $fileData;
	}

	private static function getPublicDomain()
	{
		static $result = null;
		if ($result === null)
		{
			$result = (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '') ? SITE_SERVER_NAME : Option::get("main", "server_name", $_SERVER['SERVER_NAME']));
		}

		return $result;
	}

	public static function createGroup($fields)
	{
		global $USER;

		if (!is_array($fields))
		{
			throw new Exception('Incorrect input data');
		}

		foreach ($fields as $key => $value)
		{
			if (in_array(mb_substr($key, 0, 1), [ '~', '=' ]))
			{
				unset($fields[$key]);
			}
		}

		if (isset($fields['IMAGE']))
		{
			$fields['IMAGE_ID'] = CRestUtil::saveFile($fields['IMAGE']);
			if (!$fields['IMAGE_ID'])
			{
				unset($fields['IMAGE_ID']);
			}
			unset($fields['IMAGE']);
		}

		if (
			!is_set($fields, "SITE_ID")
			|| $fields["SITE_ID"] == ''
		)
		{
			$fields["SITE_ID"] = array(SITE_ID);
		}

		if (
			!is_set($fields, "SUBJECT_ID")
			|| (int)$fields["SUBJECT_ID"] <= 0
		)
		{
			$rsSubject = CSocNetGroupSubject::GetList(
				array("SORT" => "ASC"),
				array("SITE_ID" => $fields["SITE_ID"]),
				false,
				false,
				array("ID")
			);
			if ($arSubject = $rsSubject->Fetch())
			{
				$fields["SUBJECT_ID"] = $arSubject["ID"];
			}
		}

		if (!empty($fields['PROJECT_DATE_START']))
		{
			$fields['PROJECT_DATE_START'] = CRestUtil::unConvertDate($fields['PROJECT_DATE_START']);
		}
		if (!empty($fields['PROJECT_DATE_FINISH']))
		{
			$fields['PROJECT_DATE_FINISH'] = CRestUtil::unConvertDate($fields['PROJECT_DATE_FINISH']);
		}

		$ownerId = (
			!empty($fields['OWNER_ID'])
			&& (int)$fields['OWNER_ID'] > 0
			&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
				? (int)$fields['OWNER_ID']
				: $USER->getId()
		);

		$groupID = CSocNetGroup::createGroup($ownerId, $fields, false);

		if ($groupID <= 0)
		{
			throw new Exception('Cannot create group');
		}
		else
		{
			CSocNetFeatures::SetFeature(
				SONET_ENTITY_GROUP,
				$groupID,
				'files',
				true,
				false
			);
		}

		return $groupID;
	}

	public static function updateGroup($arFields)
	{
		global $USER;

		foreach ($arFields as $key => $value)
		{
			if (in_array(mb_substr($key, 0, 1), [ '~', '=' ]))
			{
				unset($arFields[$key]);
			}
		}

		if (isset($arFields['IMAGE']))
		{
			$arFields['IMAGE_ID'] = CRestUtil::saveFile($arFields['IMAGE']);
			if (!$arFields['IMAGE_ID'])
			{
				$arFields['IMAGE_ID'] = array('del' => 'Y');
			}
			unset($arFields['IMAGE']);
		}

		if (!empty($arFields['PROJECT_DATE_START']))
		{
			$arFields['PROJECT_DATE_START'] = CRestUtil::unConvertDate($arFields['PROJECT_DATE_START']);
		}
		if (!empty($arFields['PROJECT_DATE_FINISH']))
		{
			$arFields['PROJECT_DATE_FINISH'] = CRestUtil::unConvertDate($arFields['PROJECT_DATE_FINISH']);
		}

		$groupID = $arFields['GROUP_ID'];
		unset($arFields['GROUP_ID']);

		if ((int)$groupID <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		$arFilter = array(
			"ID" => $groupID
		);

		if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
		{
			$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$dbRes = CSocNetGroup::GetList(array(), $arFilter);
		$arGroup = $dbRes->Fetch();
		if (is_array($arGroup))
		{
			if (
				$arGroup["OWNER_ID"] == $USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				$res = CSocNetGroup::Update($arGroup["ID"], $arFields, false);
				if ((int)$res <= 0)
				{
					throw new Exception('Cannot update group');
				}
			}
			else
			{
				throw new Exception('User has no permissions to update group');
			}

			return $res;
		}

		throw new Exception('Socialnetwork group not found');
	}

	public static function deleteGroup($arFields)
	{
		global $USER;

		$groupId = (int)$arFields['GROUP_ID'];

		if ($groupId <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		$filter = [
			'ID' => $groupId,
		];

		if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
		{
			$filter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$res = CSocNetGroup::GetList([], $filter);
		$groupFiels = $res->Fetch();
		if (is_array($groupFiels))
		{
			if (
				(int)$groupFiels["OWNER_ID"] === (int)$USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				if (!CSocNetGroup::Delete($groupFiels["ID"]))
				{
					throw new Exception('Cannot delete group');
				}
			}
			else
			{
				throw new Exception('User has no permissions to delete group');
			}
		}
		else
		{
			throw new Exception('Socialnetwork group not found');
		}

		return true;
	}

	public static function setGroupOwner($arFields)
	{
		global $USER;

		$groupId = $arFields['GROUP_ID'];
		$newOwnerId = $arFields['USER_ID'];

		if ((int)$groupId <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		if ((int)$newOwnerId <= 0)
		{
			throw new Exception('Wrong new owner ID');
		}

		$filter = [
			'ID' => $groupId,
		];

		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			$filter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$dbRes = CSocNetGroup::getList(array(), $filter);
		$groupFields = $dbRes->fetch();
		if (is_array($groupFields))
		{
			if (
				(int)$groupFields['OWNER_ID'] === (int)$USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				if (!CSocNetUserToGroup::setOwner($newOwnerId, $groupFields["ID"], $groupFields))
				{
					throw new Exception('Cannot change group owner');
				}
			}
			else
			{
				throw new Exception('User has no permissions to change group owner');
			}
		}
		else
			throw new Exception('Socialnetwork group not found');

		return true;

	}

	public static function getGroup($arFields, $n, $server)
	{
		global $USER;

		$arOrder = $arFields['ORDER'];
		if (!is_array($arOrder))
		{
			$arOrder = array("ID" => "DESC");
		}

		if (
			$arFields['IS_ADMIN'] === 'Y'
			&& !CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
		)
		{
			unset($arFields['IS_ADMIN']);
		}

		$filter = self::checkGroupFilter($arFields['FILTER']);

		if (
			isset($arFields['GROUP_ID'])
			&& (int)$arFields['GROUP_ID'] > 0
		)
		{
			$filter['ID'] = $arFields['GROUP_ID'];
		}

		if ($arFields['IS_ADMIN'] !== 'Y')
		{
			$filter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$result = [];
		$res = CSocNetGroup::GetList($arOrder, $filter, false, self::getNavData($n));
		while ($groupFields = $res->Fetch())
		{
			$groupFields['DATE_CREATE'] = CRestUtil::ConvertDateTime($groupFields['DATE_CREATE']);
			$groupFields['DATE_UPDATE'] = CRestUtil::ConvertDateTime($groupFields['DATE_UPDATE']);
			$groupFields['DATE_ACTIVITY'] = CRestUtil::ConvertDateTime($groupFields['DATE_ACTIVITY']);

			if ($groupFields['IMAGE_ID'] > 0)
			{
				$groupFields['IMAGE'] = self::getFile($groupFields['IMAGE_ID']);
			}

			if (
				CModule::IncludeModule("extranet")
				&& ($extranet_site_id = CExtranet::GetExtranetSiteID())
			)
			{
				$groupFields["IS_EXTRANET"] = "N";
				$rsGroupSite = CSocNetGroup::GetSite($groupFields["ID"]);
				while ($arGroupSite = $rsGroupSite->Fetch())
				{
					if ($arGroupSite["LID"] == $extranet_site_id)
					{
						$groupFields["IS_EXTRANET"] = "Y";
						break;
					}
				}
			}

			unset($groupFields['INITIATE_PERMS']);
			unset($groupFields['SPAM_PERMS']);
			unset($groupFields['IMAGE_ID']);

			$result[] = $groupFields;
		}

		return self::setNavData($result, $res);
	}

	public static function getGroupUsers($arFields, $n, $server)
	{
		global $USER;

		$GROUP_ID = (int)$arFields['ID'];

		if ($GROUP_ID > 0)
		{
			$arFilter = [
				"ID" => $GROUP_ID,
			];

			if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
			{
				$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
			}

			$dbRes = CSocNetGroup::GetList(array(), $arFilter);
			$arGroup = $dbRes->Fetch();
			if (is_array($arGroup))
			{
				$dbRes = CSocNetUserToGroup::GetList(
					array('ID' => 'ASC'),
					array(
						'GROUP_ID' => $arGroup['ID'],
						'<=ROLE' => SONET_ROLES_USER,
						'=USER_ACTIVE' => 'Y'
					), false, false, array('USER_ID', 'ROLE')
				);

				$res = array();
				while ($arRes = $dbRes->Fetch())
				{
					$res[] = $arRes;
				}

				return $res;
			}

			throw new Exception('Socialnetwork group not found');
		}
		else
		{
			throw new Exception('Wrong socialnetwork group ID');
		}
	}

	public static function inviteGroupUsers($arFields)
	{
		global $USER;

		$groupID = $arFields['GROUP_ID'];
		$arUserID = $arFields['USER_ID'];
		$message = $arFields['MESSAGE'];

		if ((int)$groupID <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		if (
			(is_array($arUserID) && count($arUserID) <= 0)
			|| (!is_array($arUserID) && (int)$arUserID <= 0)
		)
		{
			throw new Exception('Wrong user IDs');
		}

		if (!is_array($arUserID))
		{
			$arUserID = array($arUserID);
		}

		$arSuccessID = array();

		$dbRes = CSocNetGroup::GetList(array(), array(
			"ID" => $groupID,
			"CHECK_PERMISSIONS" => $USER->GetID(),
		));
		$arGroup = $dbRes->Fetch();
		if (is_array($arGroup))
		{
			foreach ($arUserID as $user_id)
			{
				$isCurrentUserTmp = ($USER->GetID() == $user_id);
				$canInviteGroup = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $user_id, "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false));
				$user2groupRelation = CSocNetUserToGroup::GetUserRole($user_id, $arGroup["ID"]);

				if (
					!$isCurrentUserTmp && $canInviteGroup && !$user2groupRelation
					&& CSocNetUserToGroup::SendRequestToJoinGroup($USER->GetID(), $user_id, $arGroup["ID"], $message, true)
				)
				{
					$arSuccessID[] = $user_id;
				}
			}
		}
		else
		{
			throw new Exception('Socialnetwork group not found');
		}

		return $arSuccessID;
	}

	public static function requestGroupUser($arFields)
	{
		global $USER;

		$groupID = $arFields['GROUP_ID'];
		$message = $arFields['MESSAGE'];

		if ((int)$groupID <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		$dbRes = CSocNetGroup::GetList(array(), array(
			"ID" => $groupID,
			"CHECK_PERMISSIONS" => $USER->GetID()
		));
		$arGroup = $dbRes->Fetch();
		if (is_array($arGroup))
		{
			$url = (CMain::IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"].CComponentEngine::MakePathFromTemplate("/workgroups/group/#group_id#/requests/", array("group_id" => $arGroup["ID"]));

			if (!CSocNetUserToGroup::SendRequestToBeMember($USER->GetID(), $arGroup["ID"], $message, $url, false))
			{
				throw new Exception('Cannot request to join group');
			}

			return true;
		}

		throw new Exception('Socialnetwork group not found');
	}

	public static function addGroupUsers($arFields)
	{
		$groupId = $arFields['GROUP_ID'];
		$userIdList = $arFields['USER_ID'];

		if ((int)$groupId <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			throw new Exception('No permissions to add users');
		}

		if (
			(is_array($userIdList) && count($userIdList) <= 0)
			|| (!is_array($userIdList) && (int)$userIdList <= 0)
		)
		{
			throw new Exception('Wrong user IDs');
		}

		if (!is_array($userIdList))
		{
			$userIdList = [ $userIdList ];
		}

		$res = CSocNetGroup::getList(array(), array(
			"ID" => $groupId
		));
		$groupFields = $res->fetch();
		if (!is_array($groupFields))
		{
			throw new Exception('Socialnetwork group not found');
		}

		if (
			!empty($userIdList)
			&& Loader::includeModule('intranet')
		)
		{
			$extranetSiteId = false;
			if (ModuleManager::isModuleInstalled('extranet'))
			{
				$extranetSiteId = Option::get('extranet', 'extranet_site');
			}

			$res = \Bitrix\Intranet\UserTable::getList([
				'filter' => [
					'@ID' => $userIdList
				],
				'select' => [ 'ID', 'USER_TYPE' ]
			]);
			$userIdList = [];
			while ($userFields = $res->fetch())
			{
				if (!in_array($userFields['USER_TYPE'], ['employee', 'extranet']))
				{
					continue;
				}
				$userIdList[] = $userFields['ID'];

				if (
					$userFields['USER_TYPE'] === 'extranet'
					&& $extranetSiteId
				)
				{
					$groupSiteList = [];
					$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList([
						'filter' => [
							'=GROUP_ID' => $groupId
						],
						'select' => [ 'SITE_ID' ]
					]);
					while ($groupSite = $resSite->fetch())
					{
						$groupSiteList[] = $groupSite['SITE_ID'];
					}
					if (!in_array($extranetSiteId, $groupSiteList))
					{
						$groupSiteList[] = $extranetSiteId;
						CSocNetGroup::update($groupId, [
							'SITE_ID' => $groupSiteList
						]);
					}
				}
			}
		}

		$successUserId = [];

		foreach ($userIdList as $userId)
		{
			$user2groupRelation = CSocNetUserToGroup::getUserRole($userId, $groupId);
			if ($user2groupRelation)
			{
				continue;
			}

			if (CSocNetUserToGroup::add([
				"USER_ID" => $userId,
				"GROUP_ID" => $groupId,
				"ROLE" => UserToGroupTable::ROLE_USER,
				"=DATE_CREATE" => CDatabase::currentTimeFunction(),
				"=DATE_UPDATE" => CDatabase::currentTimeFunction(),
				"MESSAGE" => '',
				"INITIATED_BY_TYPE" => UserToGroupTable::INITIATED_BY_GROUP,
				"INITIATED_BY_USER_ID" => $groupFields['OWNER_ID']
			]))
			{
				$successUserId[] = $userId;
			}
		}

		return $successUserId;
	}

	public static function updateGroupUsers($arFields)
	{
		$groupId = $arFields['GROUP_ID'];
		$userIdList = $arFields['USER_ID'];
		$role = $arFields['ROLE'];

		if ((int)$groupId <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			throw new Exception('No permissions to update users role');
		}

		if (!in_array($role, [ UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER ]))
		{
			throw new Exception('Incorrect role code');
		}

		if (
			(is_array($userIdList) && count($userIdList) <= 0)
			|| (!is_array($userIdList) && (int)$userIdList <= 0)
		)
		{
			throw new Exception('Wrong user IDs');
		}

		if (!is_array($userIdList))
		{
			$userIdList = [ $userIdList ];
		}

		$res = CSocNetGroup::getList(array(), array(
			"ID" => $groupId
		));
		$groupFields = $res->fetch();
		if (!is_array($groupFields))
		{
			throw new Exception('Socialnetwork group not found');
		}

		$successUserId = [];

		$resRelation = UserToGroupTable::getList(array(
			'filter' => array(
				'GROUP_ID' => $groupId,
				'@USER_ID' => $userIdList
			),
			'select' => array('ID', 'USER_ID', 'ROLE')
		));
		while ($relation = $resRelation->fetch())
		{
			if (
				$relation['ROLE'] === $role
				|| $relation['ROLE'] === UserToGroupTable::ROLE_OWNER
			)
			{
				continue;
			}

			if (CSocNetUserToGroup::update($relation['ID'], [
				"ROLE" => $role,
				"=DATE_UPDATE" => CDatabase::currentTimeFunction(),
			]))
			{
				$successUserId[] = $relation['USER_ID'];
			}
		}

		return $successUserId;
	}

	public static function deleteGroupUsers($arFields)
	{
		$groupId = $arFields['GROUP_ID'];
		$userIdList = $arFields['USER_ID'];

		if ((int)$groupId <= 0)
		{
			throw new Exception('Wrong group ID');
		}

		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			throw new Exception('No permissions to update users role');
		}

		if (
			(is_array($userIdList) && count($userIdList) <= 0)
			|| (!is_array($userIdList) && (int)$userIdList <= 0)
		)
		{
			throw new Exception('Wrong user IDs');
		}

		if (!is_array($userIdList))
		{
			$userIdList = [ $userIdList ];
		}

		$res = CSocNetGroup::getList(array(), array(
			"ID" => $groupId
		));
		$groupFields = $res->fetch();
		if (!is_array($groupFields))
		{
			throw new Exception('Socialnetwork group not found');
		}

		$successUserId = [];

		$resRelation = UserToGroupTable::getList(array(
			'filter' => array(
				'GROUP_ID' => $groupId,
				'@USER_ID' => $userIdList
			),
			'select' => array('ID', 'USER_ID', 'ROLE')
		));
		while ($relation = $resRelation->fetch())
		{
			if ($relation['ROLE'] === UserToGroupTable::ROLE_OWNER)
			{
				continue;
			}

			if (CSocNetUserToGroup::delete($relation['ID']))
			{
				$successUserId[] = $relation['USER_ID'];
			}
		}

		return $successUserId;
	}

	public static function getUserGroups($arFields, $n, $server)
	{
		global $USER;

		$res = CSocNetUserToGroup::getList(
			[ 'ID' => 'ASC' ],
			[
				'USER_ID' => $USER->GetID(),
				'<=ROLE' => SONET_ROLES_USER
			],
			false,
			false,
			[ 'GROUP_ID', 'GROUP_NAME', 'ROLE', 'GROUP_IMAGE_ID' ]
		);

		$result = [];
		$files = [];
		while ($groupFields = $res->fetch())
		{
			$groupFields['GROUP_IMAGE'] = '';
			$result[] = $groupFields;

			if ($groupFields['GROUP_IMAGE_ID'] > 0)
			{
				$files[] = (int)$groupFields['GROUP_IMAGE_ID'];
			}
		}

		if (
			!empty($result)
			&& Loader::includeModule('extranet')
			&& ($extranetSiteId = CExtranet::getExtranetSiteId())
		)
		{
			$extranetWorkgroupIdList = [];
			$workgroupIdList = array_map(function($item) { return $item['GROUP_ID']; }, $result);
			$res = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList([
				'filter' => [
					'GROUP_ID' => $workgroupIdList,
					'SITE_ID' => $extranetSiteId
				],
				'select' => [ 'GROUP_ID' ]
			]);
			while ($workgroupSiteFields = $res->fetch())
			{
				$extranetWorkgroupIdList[] = (int)$workgroupSiteFields['GROUP_ID'];
			}

			if (!empty($extranetWorkgroupIdList))
			{
				foreach ($result as $key => $groupFields)
				{
					if (in_array((int)$groupFields['GROUP_ID'], $extranetWorkgroupIdList))
					{
						$result[$key]['IS_EXTRANET'] = 'Y';
					}
				}
			}
		}

		if (!empty($files))
		{
			$files = CRestUtil::getFile($files, [
				'width' => 150,
				'height' => 150,
			]);

			foreach ($result as $key => $groupFields)
			{
				if ($groupFields['GROUP_IMAGE_ID'] > 0)
				{
					$result[$key]['GROUP_IMAGE'] = $files[$groupFields['GROUP_IMAGE_ID']];
				}
			}
		}

		return $result;
	}

	public static function getGroupFeatureAccess($arFields)
	{
		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		$groupID = (int)$arFields["GROUP_ID"];
		$feature = trim($arFields["FEATURE"]);
		$operation = trim($arFields["OPERATION"]);

		if ($groupID <= 0)
		{
			throw new Exception("Wrong socialnetwork group ID");
		}

		if (
			$feature == ''
			|| !array_key_exists($feature, $arSocNetFeaturesSettings)
			|| !array_key_exists("allowed", $arSocNetFeaturesSettings[$feature])
			|| !in_array(SONET_ENTITY_GROUP, $arSocNetFeaturesSettings[$feature]["allowed"])
		)
		{
			throw new Exception("Wrong feature");
		}

		if (
			$operation == ''
			|| !array_key_exists("operations", $arSocNetFeaturesSettings[$feature])
			|| !array_key_exists($operation, $arSocNetFeaturesSettings[$feature]["operations"])
		)
		{
			throw new Exception("Wrong operation");
		}

		return CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $groupID, $feature, $operation);
	}

	private static function checkGroupFilter($arFilter)
	{

		if (!is_array($arFilter))
		{
			$arFilter = array();
		}
		else
		{
			foreach ($arFilter as $key => $value)
			{
				if (preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
				{
					$operation = $matches[1];
					$field = $matches[2];

					if (!in_array($operation, self::$arAllowedOperations))
					{
						unset($arFilter[$key]);
					}
					else
					{
						switch($field)
						{
							case 'DATE_CREATE':
							case 'DATE_ACTIVITY':
							case 'DATE_UPDATE':
								$arFilter[$key] = CRestUtil::unConvertDateTime($value);
								break;
							case 'IS_EXTRANET':
								$extranetSiteId = false;
								if (ModuleManager::isModuleInstalled('extranet'))
								{
									$extranetSiteId = Option::get("extranet", "extranet_site");
								}
								if ($extranetSiteId)
								{
									if ($value === 'Y')
									{
										$arFilter['=SITE_ID'] = $extranetSiteId;
									}
									elseif ($value === 'N')
									{
										$arFilter['!=SITE_ID'] = $extranetSiteId;
									}
								}
								unset($arFilter[$key]);
								break;
							case 'CHECK_PERMISSIONS':
								unset($arFilter[$key]);
								break;
							default:
								break;
						}
					}
				}
			}
		}

		return $arFilter;
	}

	private static function getFile($fileId)
	{
		$arFile = CFile::GetFileArray($fileId);
		if (is_array($arFile))
		{
			return $arFile['SRC'];
		}

		return '';
	}

	private static function isAdmin()
	{
		global $USER;
		return (
			$USER->isAdmin()
			|| (
				Loader::includeModule('bitrix24')
				&& CBitrix24::isPortalAdmin($USER->getId())
			)
		);
	}

	public static function getGroupSubject($arFields, $n, $server)
	{
		$arOrder = $arFields['ORDER'];
		if (!is_array($arOrder))
		{
			$arOrder = array("SORT" => "ASC");
		}

		$arFilter = [
			'SITE_ID' => (
				isset($arFields['SITE_ID'])
				&& !empty($arFields['SITE_ID'])
					? $arFields['SITE_ID']
					: CSite::getDefSite()
			)
		];

		if (
			isset($arFields['SUBJECT_ID'])
			&& (int)$arFields['SUBJECT_ID'] > 0
		)
		{
			$arFilter['ID'] = $arFields['SUBJECT_ID'];
		}

		$subjectIdList = [];

		$resSubject = CSocNetGroupSubject::getList(
			$arOrder,
			$arFilter,
			false,
			self::getNavData($n),
			array("ID", "NAME")
		);
		while ($subjectFields = $resSubject->fetch())
		{
			$subjectIdList[] = $subjectFields['ID'];
			$result[$subjectFields['ID']] = $subjectFields;
		}

		if (!empty($subjectIdList))
		{
			$res = \Bitrix\Socialnetwork\WorkgroupSubjectSiteTable::getList([
				'filter' => [
					'@SUBJECT_ID' => $subjectIdList
				],
				'select' => ['SUBJECT_ID', 'SITE_ID']
			]);

			while ($subjectSiteFields = $res->Fetch())
			{
				if (
					isset($result[$subjectSiteFields['SUBJECT_ID']])
					&& is_array($result[$subjectSiteFields['SUBJECT_ID']])
				)
				{
					if (!isset($result[$subjectSiteFields['SUBJECT_ID']]['SITE_ID']))
					{
						$result[$subjectSiteFields['SUBJECT_ID']]['SITE_ID'] = [];
					}
					$result[$subjectSiteFields['SUBJECT_ID']]['SITE_ID'][] = $subjectSiteFields['SITE_ID'];
				}
			}
		}

		$result = array_values($result);

		return self::setNavData($result, $resSubject);
	}

	public static function updateGroupSubject($fields)
	{
		foreach ($fields as $key => $value)
		{
			if (in_array(mb_substr($key, 0, 1), [ '~', '=' ]))
			{
				unset($fields[$key]);
			}
		}

		$subjectId = $fields['SUBJECT_ID'];
		unset($fields['SUBJECT_ID']);

		if ((int)$subjectId <= 0)
		{
			throw new Exception('Wrong group subject ID');
		}

		$arFilter = [
			'ID' => $subjectId
		];

		$dbRes = CSocNetGroupSubject::getList(array(), $arFilter);

		if ($arGroupSubject = $dbRes->fetch())
		{
			if (CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
			{
				$res = CSocNetGroupSubject::update($arGroupSubject["ID"], $fields);
				if ((int)$res <= 0)
				{
					throw new Exception('Cannot update group subject');
				}
			}
			else
			{
				throw new Exception('User has no permissions to update group subject');
			}

			return $res;
		}

		throw new Exception('Socialnetwork group subject not found');
	}

	public static function deleteGroupSubject($arFields)
	{
		$subjectId = $arFields['SUBJECT_ID'];

		if ((int)$subjectId <= 0)
		{
			throw new Exception('Wrong group subject ID');
		}

		$arFilter = [
			'ID' => $subjectId,
		];

		$dbRes = CSocNetGroupSubject::getList(array(), $arFilter);
		$arGroupSubject = $dbRes->fetch();
		if (is_array($arGroupSubject))
		{
			$resSites = CSocNetGroupSubject::getSite($arGroupSubject['ID']);
			while ($siteFields = $resSites->fetch())
			{
				$count = CSocNetGroupSubject::getList(
					[],
					[
						'SITE_ID' => $siteFields['LID']
					],
					[], // count
					false
				);
				if ($count <= 1)
				{
					throw new Exception('Cannot delete the sole group subject for site ('.$siteFields['LID'].')');
				}
			}

			if (CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
			{
				if (!CSocNetGroupSubject::delete($arGroupSubject["ID"]))
				{
					throw new Exception('Cannot delete group subject');
				}
			}
			else
			{
				throw new Exception('User has no permissions to delete group subject');
			}
		}
		else
		{
			throw new Exception('Socialnetwork group subject not found');
		}

		return true;
	}

	public static function addGroupSubject($fields)
	{
		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			throw new Exception('User has no permissions to add group subject');
		}

		foreach ($fields as $key => $value)
		{
			if (in_array(mb_substr($key, 0, 1), [ '~', '=' ]))
			{
				unset($fields[$key]);
			}
		}

		if (empty($fields['SITE_ID']))
		{
			$fields['SITE_ID'] = [ CSite::getDefSite() ];
		}

		$result = (int)CSocNetGroupSubject::add($fields);

		if (!$result)
		{
			throw new Exception('Socialnetwork group subject hasn\'t been added');
		}

		return [
			'SUBJECT_ID' => $result,
		];
	}
}

class CSocNetLogBlogPostRestProxy
{
	public static function processEvent(array $arParams, array $arHandler)
	{
		static $processedIdList = array();

		if (!Loader::includeModule('blog'))
		{
			return false;
		}

		$eventName = $arHandler['EVENT_NAME'];

		$appKey = $arHandler['APP_ID'].$arHandler['APPLICATION_TOKEN'];
		if (!isset($processedIdList[$appKey]))
		{
			$processedIdList[$appKey] = array();
		}

		switch(mb_strtolower($eventName))
		{
			case 'onlivefeedpostadd':
			case 'onlivefeedpostupdate':
			case 'onlivefeedpostdelete':
				if (mb_strtolower($eventName) === 'onlivefeedpostadd')
				{
					$fields = isset($arParams[0]) && is_array($arParams[0])? $arParams[0] : array();
					$id = isset($fields['ID'])? (int)$fields['ID'] : 0;
				}
				elseif (in_array(mb_strtolower($eventName), array('onlivefeedpostupdate', 'onlivefeedpostdelete')))
				{
					$id = isset($arParams[0])? (int)$arParams[0] : 0;
					$fields = isset($arParams[1]) && is_array($arParams[1])? $arParams[1] : array();
				}

				if ($id <= 0)
				{
					throw new RestException("Could not find livefeed entity ID in fields of event \"{$eventName}\"");
				}

				if (
					mb_strtolower($eventName) === 'onlivefeedpostupdate'
					&& in_array($id, $processedIdList[$appKey])
				)
				{
					throw new RestException("ID {$id} has already been processed");
				}

				if (in_array(mb_strtolower($eventName), array('onlivefeedpostadd', 'onlivefeedpostupdate')))
				{
					$processedIdList[$appKey][] = $id;
				}

				if (
					mb_strtolower($eventName) === 'onlivefeedpostupdate'
					&& (
						!isset($fields['SOURCE_ID'])
						|| !isset($fields['EVENT_ID'])
					)
				)
				{
					$res = \Bitrix\Socialnetwork\LogTable::getList(array(
						'filter' => array(
							'ID' => $id
						),
						'select' => array('EVENT_ID', 'SOURCE_ID')
					));

					if ($logFields = $res->fetch())
					{
						$sourceId = isset($logFields['SOURCE_ID'])? (int)$logFields['SOURCE_ID'] : 0;
						$logEventId = isset($logFields['EVENT_ID'])? $logFields['EVENT_ID'] : false;
					}
				}
				else
				{
					$sourceId = isset($fields['SOURCE_ID'])? (int)$fields['SOURCE_ID'] : 0;
					$logEventId = isset($fields['EVENT_ID'])? $fields['EVENT_ID'] : false;
				}

				if (in_array($logEventId, \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList()))
				{
					if ($sourceId <= 0)
					{
						throw new RestException("Could not find livefeed source ID in fields of event \"{$eventName}\"");
					}

					return array('FIELDS' => array('POST_ID' => $sourceId));
				}

				throw new RestException("The event \"{$logEventId }\" is not processed by the log.blogpost REST events");
			default:
				throw new RestException("Incorrect handler ID");
		}
	}
}

class CSocNetGroupRestProxy
{
	public static function processEvent(array $arParams, array $arHandler)
	{
		static $processedIdList = array();

		$eventName = $arHandler['EVENT_NAME'];

		$appKey = $arHandler['APP_ID'].$arHandler['APPLICATION_TOKEN'];
		if (!isset($processedIdList[$appKey]))
		{
			$processedIdList[$appKey] = array();
		}

		switch(mb_strtolower($eventName))
		{
			case 'onsonetgroupadd':
			case 'onsonetgroupupdate':
			case 'onsonetgroupdelete':

				$id = isset($arParams[0])? (int)$arParams[0] : 0;
				$fields = isset($arParams[1]) && is_array($arParams[1])? $arParams[1] : array();

				if ($id <= 0)
				{
					throw new RestException("Could not find sonet group ID in fields of event \"{$eventName}\"");
				}

				if (
					mb_strtolower($eventName) === 'onsonetgroupupdate'
					&& in_array($id, $processedIdList[$appKey])
				)
				{
					throw new RestException("ID {$id} has already been processed");
				}

				if (in_array(mb_strtolower($eventName), array('onsonetgroupadd', 'onsonetgroupupdate')))
				{
					$processedIdList[$appKey][] = $id;
				}

				return array('FIELDS' => array('ID' => $id));
			default:
				throw new RestException("Incorrect handler ID");
		}
	}
}

class CSocNetGroupSubjectRestProxy
{
	public static function processEvent(array $arParams, array $arHandler)
	{
		static $processedIdList = array();

		$eventName = $arHandler['EVENT_NAME'];

		$appKey = $arHandler['APP_ID'].$arHandler['APPLICATION_TOKEN'];
		if (!isset($processedIdList[$appKey]))
		{
			$processedIdList[$appKey] = array();
		}

		switch(mb_strtolower($eventName))
		{
			case 'onsonetgroupsubjectadd':
			case 'onsonetgroupsubjectupdate':
			case 'onsonetgroupsubjectdelete':

				$id = isset($arParams[0])? (int)$arParams[0] : 0;
				$fields = isset($arParams[1]) && is_array($arParams[1])? $arParams[1] : array();

				if ($id <= 0)
				{
					throw new RestException("Could not find sonet group subject ID in fields of event \"{$eventName}\"");
				}

				if (
					mb_strtolower($eventName) === 'onsonetgroupsubjectupdate'
					&& in_array($id, $processedIdList[$appKey])
				)
				{
					throw new RestException("ID {$id} has already been processed");
				}

				if (in_array(mb_strtolower($eventName), array('onsonetgroupsubjectadd', 'onsonetgroupsubjectupdate')))
				{
					$processedIdList[$appKey][] = $id;
				}

				return array('FIELDS' => array('ID' => $id));
			default:
				throw new RestException("Incorrect handler ID");
		}
	}
}
