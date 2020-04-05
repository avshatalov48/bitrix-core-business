<?
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;

if(!CModule::IncludeModule('rest'))
	return;

class CSocNetLogRestService extends IRestService
{
	private static $arAllowedOperations = array('', '!', '<', '<=', '>', '>=', '><', '!><', '?', '=', '!=', '%', '!%', '');

	public static function OnRestServiceBuildDescription()
	{
		return array(
			"log" => array(
				"log.blogpost.get" => array("CSocNetLogRestService", "getBlogPost"),
				"log.blogpost.add" => array("CSocNetLogRestService", "addBlogPost"),
				"log.blogpost.update" => array("CSocNetLogRestService", "updateBlogPost"),
				"log.blogpost.share" => array("CSocNetLogRestService", "shareBlogPost"),
				"log.blogpost.getusers.important" => array("CSocNetLogRestService", "getBlogPostUsersImprtnt"),
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
				"sonet_group.user.groups" => array("CSocNetLogRestService", "getUserGroups"),
				"sonet_group.feature.access" => array("CSocNetLogRestService", "getGroupFeatureAccess"),
			),
		);
	}

	public static function createEventInfo($moduleName, $eventName, array $callback)
	{
		return array($moduleName, $eventName, $callback, array('category' => \Bitrix\Rest\Sqs::CATEGORY_DEFAULT));
	}

	public static function getBlogPost($arFields, $n, $server)
	{
		global $USER, $USER_FIELD_MANAGER;
		static $blogPostEventIdList = null;

		$result = array();
		if (!CModule::IncludeModule("blog"))
		{
			return $result;
		}

		$tzOffset = CTimeZone::getOffset();
		$arOrder = array("LOG_UPDATE" => "DESC");

		if ($blogPostEventIdList === null)
		{
			$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
			$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();
		}

		$arEventId = $blogPostEventIdList;
		$arEventIdFullset = array();
		foreach($arEventId as $eventId)
		{
			$arEventIdFullset = array_merge($arEventIdFullset, CSocNetLogTools::FindFullSetByEventID($eventId));
		}

		$res = \CUser::getById($USER->getId());
		if ($userFields = $res->Fetch())
		{
			$currentUserIntranet = (
				!empty($userFields["UF_DEPARTMENT"])
				&& is_array($userFields["UF_DEPARTMENT"])
				&& intval($userFields["UF_DEPARTMENT"][0]) > 0
			);

			$extranetSiteId = false;
			if (\Bitrix\Main\ModuleManager::isModuleInstalled('extranet'))
			{
				$extranetSiteId = Option::get("extranet", "extranet_site");
			}

			if (
				empty($extranetSiteId)
				|| $currentUserIntranet
			)
			{
				$userSiteFields = \CSocNetLogComponent::getSiteByDepartmentId($userFields["UF_DEPARTMENT"]);
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
				$siteId = \CSite::getDefSite();
			}
		}

		$arFilter = array(
			"EVENT_ID" => array_unique($arEventIdFullset),
			"SITE_ID" => array($siteId, false),
			"<=LOG_DATE" => "NOW"
		);

		if (
			isset($arFields['POST_ID'])
			&& intval($arFields['POST_ID']) > 0
		)
		{
			$arFilter['SOURCE_ID'] = $arFields['POST_ID'];
		}
		else // list
		{
			$arAccessCodes = $USER->GetAccessCodes();
			foreach ($arAccessCodes as $i => $code)
			{
				if (!preg_match("/^(U|D|DR|SG)/", $code)) //Users, Departments and Sonet groups
				{
					unset($arAccessCodes[$i]);
				}
			}
			$arAccessCodes[] = 'UA';
			$arFilter["LOG_RIGHTS"] = array_unique($arAccessCodes);
		}

		$arListParams = array(
			"CHECK_RIGHTS" => "Y",
			"USE_FOLLOW" => "N",
			"USE_SUBSCRIBE" => "N"
		);

		$dbLog = CSocNetLog::GetList(
			$arOrder,
			$arFilter,
			false,
			self::getNavData($n),
			array("ID", "SOURCE_ID"),
			$arListParams
		);

		$arPostId = $arPostIdToGet = array();

		while($arLog = $dbLog->Fetch())
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

						if($arPost["HAS_PROPS"] != "N")
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

	public static function addBlogPost($arFields)
	{
		global $USER;

		$siteId = (
			is_set($arFields, "SITE_ID")
			&& !empty($arFields["SITE_ID"])
				? $arFields["SITE_ID"]
				: SITE_ID
		);

		$authorId = (
			isset($arFields["USER_ID"])
			&& intval($arFields["USER_ID"]) > 0
			&& $USER->isAdmin()
				? $arFields["USER_ID"]
				: $USER->getId()
		);

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('No blog module installed');
		}

		$blog = \Bitrix\Blog\Item\Blog::getByUser(array(
			"GROUP_ID" => Option::get("socialnetwork", "userbloggroup_id", false, $siteId),
			"SITE_ID" => $siteId,
			"USER_ID" => $authorId,
			"CREATE" => "Y",
		));

		if (!$blog)
		{
			throw new Exception('No blog found');
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$postFields = array(
			"BLOG_ID" => $blog["ID"],
			"AUTHOR_ID" => $authorId,
			"=DATE_CREATE" => $helper->getCurrentDateTimeFunction(),
			"=DATE_PUBLISH" => $helper->getCurrentDateTimeFunction(),
			"MICRO" => "N",
			"TITLE" => (strlen($arFields["POST_TITLE"]) > 0 ? $arFields["POST_TITLE"] : ''),
			"DETAIL_TEXT" => $arFields["POST_MESSAGE"],
			"DETAIL_TEXT_TYPE" => "text",
			"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"HAS_IMAGES" => "N",
			"HAS_TAGS" => "N",
			"HAS_SOCNET_ALL" => "N"
		);

		if (
			!empty($arFields["DEST"])
			&& is_array($arFields["DEST"])
		)
		{
			$resultFields = array(
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS']
			);

			$postFields["SOCNET_RIGHTS"] = ComponentHelper::checkBlogPostDestinationList(array(
				'DEST' => $arFields["DEST"],
				'SITE_ID' => $siteId,
				'AUTHOR_ID' => $authorId,
			), $resultFields);

			$postFields["PUBLISH_STATUS"] = $resultFields['PUBLISH_STATUS'];
			if ($resultFields['ERROR_MESSAGE'])
			{
				throw new Exception($resultFields['ERROR_MESSAGE']);
			}
		}
		elseif (!empty($arFields["SPERM"]))
		{
			$resultFields = array(
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS'],
			);

			$postFields["SOCNET_RIGHTS"] = ComponentHelper::convertBlogPostPermToDestinationList(array(
				'PERM' => $arFields["SPERM"],
				'IS_REST' => true,
				'AUTHOR_ID' => $authorId,
				'SITE_ID' => $siteId
			), $resultFields);

			$postFields["PUBLISH_STATUS"] = $resultFields['PUBLISH_STATUS'];
			if (!empty($resultFields['ERROR_MESSAGE']))
			{
				throw new Exception($resultFields['ERROR_MESSAGE']);
			}
		}
		elseif (
			!Loader::includeModule("extranet")
			|| \CExtranet::isIntranetUser()
		)
		{
			$postFields["SOCNET_RIGHTS"] = array('UA');
		}

		if (empty($postFields["SOCNET_RIGHTS"]))
		{
			throw new Exception('No destination specified');
		}

		if (strlen($postFields["TITLE"]) <= 0)
		{
			$postFields["MICRO"] = "Y";
			$postFields["TITLE"] = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", \blogTextParser::killAllTags($postFields["DETAIL_TEXT"]));
			$postFields["TITLE"] = trim($postFields["TITLE"], " \t\n\r\0\x0B\xA0");
		}

		$result = \CBlogPost::add($postFields);

		if (!$result)
		{
			throw new Exception('Blog haven\'t been added');
		}

		if (
			isset($arFields["FILES"])
			&& Option::get('disk', 'successfully_converted', false)
			&& Loader::includeModule('disk')
			&& ($storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($authorId))
			&& ($folder = $storage->getFolderForUploadedFiles())
		)
		{
			// upload to storage
			$filesList = array();

			foreach($arFields["FILES"] as $tmp)
			{
				$fileFields = \CRestUtil::saveFile($tmp);

				if(is_array($fileFields))
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
				\CBlogPost::update(
					$result,
					array(
						"HAS_PROPS" => "Y",
						"UF_BLOG_POST_FILE" => $filesList
					)
				);
			}
		}

		$pathToPost = Option::get("socialnetwork", "userblogpost_page", false, $siteId);

		$postFields['ID'] = $result;

		$paramsNotify = array(
			"bSoNet" => true,
			"allowVideo" => Option::get("blog", "allow_video", "Y"),
			"PATH_TO_POST" => $pathToPost,
			"user_id" => $authorId,
			"NAME_TEMPLATE" => \CSite::getNameFormat(null, $siteId)
		);

		$logId = \CBlogPost::notify($postFields, $blog, $paramsNotify);

		$postUrl = \CComponentEngine::makePathFromTemplate(htmlspecialcharsBack($pathToPost), array(
			"post_id" => $result,
			"user_id" => $blog["OWNER_ID"]
		));

		if($postFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH)
		{
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'posts_last',
				'SITE_ID' => $siteId
			)));

			preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/ies".BX_UTF_PCRE_MODIFIER, $postFields["DETAIL_TEXT"], $matches);
			$mentionList = (!empty($matches) ? $matches[1] : array());

			ComponentHelper::notifyBlogPostCreated(array(
				'post' => array(
					'ID' => $result,
					'TITLE' => $postFields["TITLE"],
					'AUTHOR_ID' => $authorId
				),
				'siteId' => $siteId,
				'postUrl' => $postUrl,
				'socnetRights' => $postFields["SOCNET_RIGHTS"],
				'socnetRightsOld' => array(),
				'mentionListOld' => array(),
				'mentionList' => $mentionList
			));
		}
		elseif (
			$postFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY
			&& !empty($postFields["SOCNET_RIGHTS"])
		)
		{
			CBlogPost::NotifyImReady(array(
				"TYPE" => "POST",
				"POST_ID" => $result,
				"TITLE" => $postFields["TITLE"],
				"POST_URL" => $postUrl,
				"FROM_USER_ID" => $authorId,
				"TO_SOCNET_RIGHTS" => $postFields["SOCNET_RIGHTS"]
			));
		}

		foreach($postFields["SOCNET_RIGHTS"] as $destination)
		{
			if (preg_match('/^SG(\d+)/i', $destination, $matches))
			{
				\CSocNetGroup::setLastActivity($matches[1]);
			}
		}

		return $result;
	}

	public static function updateBlogPost($arFields)
	{
		global $USER, $USER_FIELD_MANAGER;

		$postId = intval($arFields['POST_ID']);

		if($postId <= 0)
		{
			throw new Exception('Wrong post ID');
		}

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$currentUserId = (
			isset($arFields["USER_ID"])
			&& intval($arFields["USER_ID"]) > 0
			&& $USER->isAdmin()
				? $arFields["USER_ID"]
				: $USER->getId()
		);

		$siteId = (
			is_set($arFields, "SITE_ID")
			&& !empty($arFields["SITE_ID"])
				? $arFields["SITE_ID"]
				: SITE_ID
		);

		$currentUserPerm = self::getBlogPostPerm(array(
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId
		));

		if ($currentUserPerm <= \Bitrix\Blog\Item\Permissions::WRITE)
		{
			throw new Exception('No write perms');
		}

		$postFields = \Bitrix\Blog\Item\Post::getById($postId)->getFields();
		if (empty($postFields))
		{
			throw new Exception('No post found');
		}

		$blog = \Bitrix\Blog\Item\Blog::getByUser(array(
			"GROUP_ID" => Option::get("socialnetwork", "userbloggroup_id", false, $siteId),
			"SITE_ID" => $siteId,
			"USER_ID" => $postFields['AUTHOR_ID']
		));

		if (!$blog)
		{
			throw new Exception('No blog found');
		}

		$updateFields = array(
			'PUBLISH_STATUS' => $postFields['PUBLISH_STATUS']
		);

		if (isset($arFields["POST_TITLE"]))
		{
			$updateFields['TITLE'] = $arFields["POST_TITLE"];
			$updateFields["MICRO"] = "N";
			if (
				strlen($updateFields["TITLE"]) <= 0
				&& isset($arFields["POST_MESSAGE"])
			)
			{
				$updateFields["MICRO"] = "Y";
				$updateFields["TITLE"] = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", \blogTextParser::killAllTags($arFields["POST_MESSAGE"]));
				$updateFields["TITLE"] = trim($updateFields["TITLE"], " \t\n\r\0\x0B\xA0");
			}
		}
		if (strlen($arFields["POST_MESSAGE"]) > 0)
		{
			$updateFields['DETAIL_TEXT'] = $arFields["POST_MESSAGE"];
		}

		if (!empty($arFields["DEST"]))
		{
			$resultFields = array(
				'ERROR_MESSAGE' => false,
				'PUBLISH_STATUS' => $updateFields['PUBLISH_STATUS']
			);

			$updateFields["SOCNET_RIGHTS"] = ComponentHelper::checkBlogPostDestinationList(array(
				'DEST' => $arFields["DEST"],
				'SITE_ID' => $siteId,
				'AUTHOR_ID' => $postFields['AUTHOR_ID'],
			), $resultFields);

			$updateFields["PUBLISH_STATUS"] = $resultFields['PUBLISH_STATUS'];
			if ($resultFields['ERROR_MESSAGE'])
			{
				throw new Exception($resultFields['ERROR_MESSAGE']);
			}
		}

		if($result = \CBlogPost::update($postId, $updateFields))
		{
			if (
				!empty($arFields["FILES"])
				&& Option::get('disk', 'successfully_converted', false)
				&& Loader::includeModule('disk')
				&& ($storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($postFields['AUTHOR_ID']))
				&& ($folder = $storage->getFolderForUploadedFiles())
			)
			{
				$filesList = array();

				$postUF = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $postId, LANGUAGE_ID);
				if (
					!empty($postUF['UF_BLOG_POST_FILE'])
					&& !empty($postUF['UF_BLOG_POST_FILE']['VALUE'])
				)
				{
					$filesList = array_merge($filesList, $postUF['UF_BLOG_POST_FILE']['VALUE']);
				}

				$needToDelete = false;

				foreach($arFields["FILES"] as $key => $tmp)
				{
					if (
						$tmp == 'del'
						&& in_array($key, $filesList)
					)
					{
						foreach($filesList as $i => $v)
						{
							if ($v == $key)
							{
								unset($filesList[$i]);
								$needToDelete = true;
							}
						}
					}
					else
					{
						$fileFields = \CRestUtil::saveFile($tmp);

						if(is_array($fileFields))
						{
							$file = $folder->uploadFile(
								$fileFields,
								array(
									'NAME' => $fileFields["name"],
									'CREATED_BY' => $postFields['AUTHOR_ID']
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
				}

				if (
					!empty($filesList)
					|| $needToDelete
				)
				{
					\CBlogPost::update($postId, array("HAS_PROPS" => "Y", "UF_BLOG_POST_FILE" => $filesList));
				}
			}

			BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'post',
				'POST_ID' => $postId
			)));
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'post_general',
				'POST_ID' => $postId
			)));
			BXClearCache(true, ComponentHelper::getBlogPostCacheDir(array(
				'TYPE' => 'posts_popular',
				'SITE_ID' => $siteId
			)));

			$updateFields["AUTHOR_ID"] = $postFields["AUTHOR_ID"];
			if (
				$updateFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT
				&& $postFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
			)
			{
				\CBlogPost::deleteLog($postId);
			}
			elseif (
				$updateFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
				&& $postFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH
			)
			{
				\CBlogPost::updateLog($postId, $updateFields, $blog, array(
					"allowVideo" => Option::get("blog", "allow_video", "Y"),
					"PATH_TO_SMILE" => false
				));
			}
		}

		return $result;
	}

	public static function shareBlogPost($arFields)
	{
		global $USER;

		$postId = intval($arFields['POST_ID']);

		if($postId <= 0)
		{
			throw new Exception('Wrong post ID');
		}

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$siteId = (
			is_set($arFields, "SITE_ID")
			&& !empty($arFields["SITE_ID"])
				? $arFields["SITE_ID"]
				: SITE_ID
		);

		$blogId = false;

		if (
			!is_set($arFields, "BLOG_ID")
			|| intval($arFields["BLOG_ID"]) <= 0
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
				$blogId = intval($postFields['BLOG_ID']);
			}
		}
		else
		{
			$blogId = intval($arFields["BLOG_ID"]);
		}

		$blogPostPermsNewList = $arFields['DEST'];

		if(empty($blogPostPermsNewList))
		{
			throw new Exception('Wrong destinations');
		}

		if(!is_array($blogPostPermsNewList))
		{
			$blogPostPermsNewList = array($blogPostPermsNewList);
		}

		$currentUserId = (
			isset($arFields["USER_ID"])
			&& intval($arFields["USER_ID"]) > 0
			&& $USER->isAdmin()
				? $arFields["USER_ID"]
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

		foreach($blogPostPermsOldList as $type => $val)
		{
			foreach($val as $id => $values)
			{
				if($type != "U")
				{
					$permsFull[] = $type.$id;
				}
				else
				{
					$permsFull[] = (
						in_array("US".$id, $values)
							? "UA"
							: $type.$id
						);
				}
			}
		}

		foreach($permsNew as $key => $code)
		{
			if(!in_array($code, $permsFull))
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

	public static function getBlogPostUsersImprtnt($arFields)
	{
		global $CACHE_MANAGER, $USER;

		if (!is_array($arFields))
		{
			throw new Exception('Incorrect input data');
		}

		$arParams["postId"] = intval($arFields['POST_ID']);

		if($arParams["postId"] <= 0)
		{
			throw new Exception('Wrong post ID');
		}

		$arParams["nTopCount"] = 500;
		$arParams["paramName"] = 'BLOG_POST_IMPRTNT';
		$arParams["paramValue"] = 'Y';

		$arResult = array();

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
			$arResult = $cache->GetVars();
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
						$db_res = CBlogUserOptions::GetList(
							array(
							),
							array(
								'POST_ID' => $arParams["postId"],
								'NAME' => $arParams["paramName"],
								'VALUE' => $arParams["paramValue"],
								'USER_ACTIVE' => 'Y'
							),
							array(
								"nTopCount" => $arParams["nTopCount"],
								"SELECT" => array("USER_ID")
							)
						);
						if ($db_res)
						{
							while ($res = $db_res->Fetch())
							{
								$arResult[] = $res["USER_ID"];
							}
						}
					}
				}

				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->EndTagCache();
				}

				$cache->EndDataCache($arResult);
			}
		}

		return $arResult;
	}

	private static function getBlogPostPerm($arFields)
	{
		global $USER;

		if (!Loader::includeModule('blog'))
		{
			throw new Exception('Blog module not installed');
		}

		$postId = $arFields['POST_ID'];

		$currentUserId = (
			isset($arFields["USER_ID"])
			&& intval($arFields["USER_ID"]) > 0
			&& $USER->isAdmin()
				? $arFields["USER_ID"]
				: $USER->getId()
		);

		$arPost = self::getBlogPostFields($postId);

		if($arPost["AUTHOR_ID"] == $currentUserId)
		{
			$result = Bitrix\Blog\Item\Permissions::FULL;
		}
		else
		{
			if (CSocNetUser::isUserModuleAdmin($currentUserId, SITE_ID))
			{
				$result = Bitrix\Blog\Item\Permissions::FULL;
			}
			else
			{
				$postItem = \Bitrix\Blog\Item\Post::getById($postId);
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
			}
		}

		return $result;
	}

	private static function getBlogPostFields($postId)
	{
		$tzOffset = \CTimeZone::getOffset();

		$cacheTtl = 2592000;
		$cacheId = 'blog_post_socnet_general_'.$postId.'_'.LANGUAGE_ID.($tzOffset <> 0 ? "_".$tzOffset : "")."_".Bitrix\Main\Context::getCurrent()->getCulture()->getDateTimeFormat();
		$cacheDir = ComponentHelper::getBlogPostCacheDir(array(
			'TYPE' => 'post_general',
			'POST_ID' => $postId
		));

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$arPost = $obCache->getVars();
			$postItem = new \Bitrix\Blog\Item\Post;
			$postItem->setFields($arPost);
		}
		else
		{
			$obCache->StartDataCache();
			$postItem = \Bitrix\Blog\Item\Post::getById($postId);
			$arPost = $postItem->getFields();
			$obCache->EndDataCache($arPost);
		}

		return $arPost;
	}

	public static function createGroup($arFields)
	{
		global $USER;

		if (!is_array($arFields))
		{
			throw new Exception('Incorrect input data');
		}

		foreach($arFields as $key => $value)
		{
			if (
				substr($key, 0, 1) == "~"
				|| substr($key, 0, 1) == "="
			)
			{
				unset($arFields[$key]);
			}
		}

		if (isset($arFields["IMAGE_ID"]))
		{
			unset($arFields["IMAGE_ID"]);
		}

		if (
			!is_set($arFields, "SITE_ID")
			|| strlen($arFields["SITE_ID"]) <= 0
		)
		{
			$arFields["SITE_ID"] = array(SITE_ID);
		}

		if (
			!is_set($arFields, "SUBJECT_ID")
			|| intval($arFields["SUBJECT_ID"]) <= 0
		)
		{
			$rsSubject = CSocNetGroupSubject::GetList(
				array("SORT" => "ASC"),
				array("SITE_ID" => $arFields["SITE_ID"]),
				false,
				false,
				array("ID")
			);
			if ($arSubject = $rsSubject->Fetch())
			{
				$arFields["SUBJECT_ID"] = $arSubject["ID"];
			}
		}

		$groupID = CSocNetGroup::CreateGroup($USER->GetID(), $arFields, false);

		if($groupID <= 0)
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

		foreach($arFields as $key => $value)
		{
			if (
				substr($key, 0, 1) == "~"
				|| substr($key, 0, 1) == "="
			)
			{
				unset($arFields[$key]);
			}
		}

		if (isset($arFields["IMAGE_ID"]))
		{
			unset($arFields["IMAGE_ID"]);
		}

		$groupID = $arFields['GROUP_ID'];
		unset($arFields['GROUP_ID']);

		if(intval($groupID) <= 0)
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
		if(is_array($arGroup))
		{
			if (
				$arGroup["OWNER_ID"] == $USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				$res = CSocNetGroup::Update($arGroup["ID"], $arFields, false);
				if(intval($res) <= 0)
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
		else
		{
			throw new Exception('Socialnetwork group not found');
		}
	}

	public static function deleteGroup($arFields)
	{
		global $USER;

		$groupID = $arFields['GROUP_ID'];

		if(intval($groupID) <= 0)
			throw new Exception('Wrong group ID');

		$arFilter = array(
			"ID" => $groupID
		);

		if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
		{
			$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$dbRes = CSocNetGroup::GetList(array(), $arFilter);
		$arGroup = $dbRes->Fetch();
		if(is_array($arGroup))
		{
			if (
				$arGroup["OWNER_ID"] == $USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				if (!CSocNetGroup::Delete($arGroup["ID"]))
					throw new Exception('Cannot delete group');
			}
			else
				throw new Exception('User has no permissions to delete group');
		}
		else
			throw new Exception('Socialnetwork group not found');

		return true;
	}

	public static function setGroupOwner($arFields)
	{
		global $USER;

		$groupId = $arFields['GROUP_ID'];
		$newOwnerId = $arFields['USER_ID'];

		if(intval($groupId) <= 0)
			throw new Exception('Wrong group ID');

		if(intval($newOwnerId) <= 0)
			throw new Exception('Wrong new owner ID');

		$arFilter = array(
			"ID" => $groupId
		);

		if (!CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$dbRes = CSocNetGroup::getList(array(), $arFilter);
		$arGroup = $dbRes->fetch();
		if(is_array($arGroup))
		{
			if (
				$arGroup["OWNER_ID"] == $USER->GetID()
				|| CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
			)
			{
				if (!CSocNetUserToGroup::setOwner($newOwnerId, $arGroup["ID"], $arGroup))
					throw new Exception('Cannot change group owner');
			}
			else
				throw new Exception('User has no permissions to change group owner');
		}
		else
			throw new Exception('Socialnetwork group not found');

		return true;

	}

	public static function getGroup($arFields, $n, $server)
	{
		global $USER;

		$arOrder = $arFields['ORDER'];
		if(!is_array($arOrder))
		{
			$arOrder = array("ID" => "DESC");
		}

		if ($arFields['IS_ADMIN'] == 'Y')
		{
			if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
			{
				unset($arFields['IS_ADMIN']);
			}
		}

		$arFilter = self::checkGroupFilter($arFields['FILTER']);
		if ($arFields['IS_ADMIN'] != 'Y')
		{
			$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
		}

		$result = array();
		$dbRes = CSocNetGroup::GetList($arOrder, $arFilter, false, self::getNavData($n));
		while($arRes = $dbRes->Fetch())
		{
			$arRes['DATE_CREATE'] = CRestUtil::ConvertDateTime($arRes['DATE_CREATE']);
			$arRes['DATE_UPDATE'] = CRestUtil::ConvertDateTime($arRes['DATE_UPDATE']);
			$arRes['DATE_ACTIVITY'] = CRestUtil::ConvertDateTime($arRes['DATE_ACTIVITY']);

			if($arRes['IMAGE_ID'] > 0)
			{
				$arRes['IMAGE'] = self::getFile($arRes['IMAGE_ID']);
			}

			if (
				CModule::IncludeModule("extranet")
				&& ($extranet_site_id = CExtranet::GetExtranetSiteID())
			)
			{
				$arRes["IS_EXTRANET"] = "N";
				$rsGroupSite = CSocNetGroup::GetSite($arRes["ID"]);
				while ($arGroupSite = $rsGroupSite->Fetch())
				{
					if ($arGroupSite["LID"] == $extranet_site_id)
					{
						$arRes["IS_EXTRANET"] = "Y";
						break;
					}
				}
			}

			unset($arRes['INITIATE_PERMS']);
			unset($arRes['SPAM_PERMS']);
			unset($arRes['IMAGE_ID']);

			$result[] = $arRes;
		}

		return self::setNavData($result, $dbRes);
	}

	public static function getGroupUsers($arFields, $n, $server)
	{
		global $USER;

		$GROUP_ID = intval($arFields['ID']);

		if($GROUP_ID > 0)
		{
			$arFilter = array(
				"ID" => $GROUP_ID
			);

			if (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
			{
				$arFilter['CHECK_PERMISSIONS'] = $USER->GetID();
			}

			$dbRes = CSocNetGroup::GetList(array(), $arFilter);
			$arGroup = $dbRes->Fetch();
			if(is_array($arGroup))
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
			else
			{
				throw new Exception('Socialnetwork group not found');
			}
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

		if(intval($groupID) <= 0)
			throw new Exception('Wrong group ID');

		if (
			(is_array($arUserID) && count($arUserID) <= 0)
			|| (!is_array($arUserID) && intval($arUserID) <= 0)
		)
			throw new Exception('Wrong user IDs');

		if (!is_array($arUserID))
			$arUserID = array($arUserID);

		$arSuccessID = array();

		$dbRes = CSocNetGroup::GetList(array(), array(
			"ID" => $groupID,
			"CHECK_PERMISSIONS" => $USER->GetID(),
		));
		$arGroup = $dbRes->Fetch();
		if(is_array($arGroup))
		{
			foreach($arUserID as $user_id)
			{
				$isCurrentUserTmp = ($USER->GetID() == $user_id);
				$canInviteGroup = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $user_id, "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false));
				$user2groupRelation = CSocNetUserToGroup::GetUserRole($user_id, $arGroup["ID"]);

				if (
					!$isCurrentUserTmp && $canInviteGroup && !$user2groupRelation
					&& CSocNetUserToGroup::SendRequestToJoinGroup($USER->GetID(), $user_id, $arGroup["ID"], $message, true)
				)
					$arSuccessID[] = $user_id;
			}
		}
		else
			throw new Exception('Socialnetwork group not found');

		return $arSuccessID;
	}

	public static function requestGroupUser($arFields)
	{
		global $USER;

		$groupID = $arFields['GROUP_ID'];
		$message = $arFields['MESSAGE'];

		if(intval($groupID) <= 0)
			throw new Exception('Wrong group ID');

		$dbRes = CSocNetGroup::GetList(array(), array(
			"ID" => $groupID,
			"CHECK_PERMISSIONS" => $USER->GetID()
		));
		$arGroup = $dbRes->Fetch();
		if(is_array($arGroup))
		{
			$url = (CMain::IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"].CComponentEngine::MakePathFromTemplate("/workgroups/group/#group_id#/requests/", array("group_id" => $arGroup["ID"]));

			if (!CSocNetUserToGroup::SendRequestToBeMember($USER->GetID(), $arGroup["ID"], $message, $url, false))
			{
				throw new Exception('Cannot request to join group');
			}

			return true;
		}
		else
		{
			throw new Exception('Socialnetwork group not found');
		}
	}


	public static function getUserGroups($arFields, $n, $server)
	{
		global $USER;

		$dbRes = CSocNetUserToGroup::GetList(
			array('ID' => 'ASC'),
			array(
				'USER_ID' => $USER->GetID(),
				'<=ROLE' => SONET_ROLES_USER
			), false, false, array('GROUP_ID', 'GROUP_NAME', 'ROLE')
		);

		$res = array();
		while ($arRes = $dbRes->Fetch())
		{
			$res[] = $arRes;
		}

		return $res;
	}

	public static function getGroupFeatureAccess($arFields)
	{
		$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

		$groupID = intval($arFields["GROUP_ID"]);
		$feature = trim($arFields["FEATURE"]);
		$operation = trim($arFields["OPERATION"]);

		if ($groupID <= 0)
		{
			throw new Exception("Wrong socialnetwork group ID");
		}

		if (
			strlen($feature) <= 0
			|| !array_key_exists($feature, $arSocNetFeaturesSettings)
			|| !array_key_exists("allowed", $arSocNetFeaturesSettings[$feature])
			|| !in_array(SONET_ENTITY_GROUP, $arSocNetFeaturesSettings[$feature]["allowed"])
		)
		{
			throw new Exception("Wrong feature");
		}

		if (
			strlen($operation) <= 0
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

		if(!is_array($arFilter))
		{
			$arFilter = array();
		}
		else
		{
			foreach ($arFilter as $key => $value)
			{
				if(preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
				{
					$operation = $matches[1];
					$field = $matches[2];

					if(!in_array($operation, self::$arAllowedOperations))
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
		if(is_array($arFile))
		{
			return $arFile['SRC'];
		}
		else
		{
			return '';
		}
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

		switch (strtolower($eventName))
		{
			case 'onlivefeedpostadd':
			case 'onlivefeedpostupdate':
			case 'onlivefeedpostdelete':
				if (in_array(strtolower($eventName), array('onlivefeedpostadd')))
				{
					$fields = isset($arParams[0]) && is_array($arParams[0]) ? $arParams[0] : array();
					$id = isset($fields['ID']) ? (int)$fields['ID'] : 0;
				}
				elseif (in_array(strtolower($eventName), array('onlivefeedpostupdate', 'onlivefeedpostdelete')))
				{
					$id = isset($arParams[0]) ? (int)$arParams[0] : 0;
					$fields = isset($arParams[1]) && is_array($arParams[1]) ? $arParams[1] : array();
				}

				if($id <= 0)
				{
					throw new RestException("Could not find livefeed entity ID in fields of event \"{$eventName}\"");
				}

				if (
					in_array(strtolower($eventName), array('onlivefeedpostupdate'))
					&& in_array($id, $processedIdList[$appKey])
				)
				{
					throw new RestException("ID {$id} has already been processed");
				}

				if (in_array(strtolower($eventName), array('onlivefeedpostadd', 'onlivefeedpostupdate')))
				{
					$processedIdList[$appKey][] = $id;
				}

				if (
					strtolower($eventName) == 'onlivefeedpostupdate'
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
						$sourceId = isset($logFields['SOURCE_ID']) ? (int)$logFields['SOURCE_ID'] : 0;
						$logEventId = isset($logFields['EVENT_ID']) ? $logFields['EVENT_ID'] : false;
					}
				}
				else
				{
					$sourceId = isset($fields['SOURCE_ID']) ? (int)$fields['SOURCE_ID'] : 0;
					$logEventId = isset($fields['EVENT_ID']) ? $fields['EVENT_ID'] : false;
				}

				if (in_array($logEventId, \Bitrix\Blog\Integration\Socialnetwork\Log::getEventIdList()))
				{
					if($sourceId <= 0)
					{
						throw new RestException("Could not find livefeed source ID in fields of event \"{$eventName}\"");
					}

					return array('FIELDS' => array('POST_ID' => $sourceId));
				}
				else
				{
					throw new RestException("The event \"{$logEventId }\" is not processed by the log.blogpost REST events");
				}
				break;
			default:
				throw new RestException("Incorrect handler ID");
		}

		return false;
	}
}
?>