<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CListsLiveFeed
{
	public static function setMessageLiveFeed($users, $elementId, $workflowId, $flagCompleteProcess)
	{
		$elementId = intval($elementId);
		$elementObject = CIBlockElement::getList(
			array(),
			array('ID' => $elementId),
			false,
			false,
			array('ID', 'CREATED_BY', 'IBLOCK_NAME', 'NAME', 'IBLOCK_ID', 'LANG_DIR', 'IBLOCK_CODE')
		);
		$element = $elementObject->fetch();

		if (!CLists::getLiveFeed($element["IBLOCK_ID"] ?? null))
		{
			return false;
		}

		$listSystemIblockCode = array(
			'bitrix_holiday',
			'bitrix_invoice',
			'bitrix_trip',
			'bitrix_cash',
			'bitrix_incoming_doc',
			'bitrix_outgoing_doc',
		);

		$params = serialize(array("ELEMENT_NAME" => $element['NAME']));

		$element['NAME'] = htmlspecialcharsbx($element['NAME']);
		if (in_array($element['IBLOCK_CODE'], $listSystemIblockCode))
		{
			$element['NAME'] = preg_replace_callback(
				'#^[^\[\]]+?\[(\d+)\]#i',
				function ($matches)
				{
					$userId = $matches[1];
					$db = CUser::GetByID($userId);
					if ($ar = $db->GetNext())
					{
						$ix = randString(5);
						return '<a class="feed-post-user-name" href="/company/personal/user/'.$userId.'/"
						bx-post-author-id="'.$userId.'" bx-post-author-gender="'.$ar['PERSONAL_GENDER'].'" bx-tooltip-user-id="'.$userId.'">'.CUser::FormatName(CSite::GetNameFormat(false), $ar, true, false).'</a>';
					}
					return $matches[0];
				},
				$element['NAME']
			);
		}

		$path = rtrim($element['LANG_DIR'], '/');
		$urlElement = $path.COption::GetOptionString('lists', 'livefeed_url').'?livefeed=y&list_id='.$element["IBLOCK_ID"].'&element_id='.$elementId;
		$createdBy = $element['CREATED_BY'];
		if(!Loader::includeModule('socialnetwork') || $createdBy <= 0)
			return false;

		$sourceId = CBPStateService::getWorkflowIntegerId($workflowId);
		$logId = 0;
		$userObject = CUser::getByID($createdBy);
		$siteId = array();
		$siteObject = CSite::getList("sort", "desc", array("ACTIVE" => "Y"));
		while ($site = $siteObject->fetch())
			$siteId[] = $site['LID'];

		if ($userObject->fetch())
		{
			global $DB;
			$soFields = Array(
				'ENTITY_TYPE' => SONET_LISTS_NEW_POST_ENTITY,
				'EVENT_ID' => 'lists_new_element',
				'ENTITY_ID' => 1,
				'=LOG_UPDATE' => $DB->currentTimeFunction(),
				'SOURCE_ID' => $sourceId,
				'USER_ID' => $createdBy,
				'MODULE_ID' => 'lists',
				'TITLE_TEMPLATE' => $urlElement,
				'TITLE' => $element['IBLOCK_NAME'],
				'PARAMS' => $params,
				'MESSAGE' => $workflowId,
				'CALLBACK_FUNC' => false,
				'SITE_ID' => $siteId,
				'ENABLE_COMMENTS' => 'Y',
				'RATING_TYPE_ID' => 'LISTS_NEW_ELEMENT',
				'RATING_ENTITY_ID' => $sourceId,
				'URL' => '#SITE_DIR#'.COption::GetOptionString('socialnetwork', 'user_page', false, SITE_ID).'log/',
			);

			$logObject = CSocNetLog::getList(array(), array(
				'ENTITY_TYPE' => $soFields['ENTITY_TYPE'],
				'ENTITY_ID' => $soFields['ENTITY_ID'],
				'EVENT_ID' => $soFields['EVENT_ID'],
				'SOURCE_ID' => $soFields['SOURCE_ID'],
			));

			$iblockPicture = CIBlock::getArrayByID($element['IBLOCK_ID'], 'PICTURE');
			$imageFile = CFile::getFileArray($iblockPicture);
			if($imageFile !== false)
			{
				$imageFile = CFile::ResizeImageGet(
					$imageFile,
					array("width" => 36, "height" => 30),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					false
				);
			}
			if(empty($imageFile['src']))
				$imageFile['src'] = '/bitrix/images/lists/default.png';

			$soFields['TEXT_MESSAGE'] = '
				<span class="bp-title-desc">
					<span class="bp-title-desc-icon">
						<img src="'.$imageFile['src'].'" width="36" height="30" border="0" />
					</span>
					'.$element['NAME'].'
				</span>
			';

			if($log = $logObject->fetch())
			{
				if (intval($log['ID']) > 0)
				{
					if(empty($users))
					{
						CSocNetLog::update($log['ID'], $soFields);
					}
					else
					{
						$activeUsers = CBPTaskService::getWorkflowParticipants($workflowId);

						$rights = self::getRights($activeUsers, $log['ID'], $createdBy, 'post');
						$usersRight = self::getUserIdForRight($rights);

						self::setSocnetFollow($usersRight, $log['ID'], 'Y', true);
						/* Recipients tasks bp */
						CSocNetLog::update($log['ID'], $soFields);

						/* Increment the counter for participants */
						CSocNetLogRights::deleteByLogID($log['ID']);
						$rightsCounter = self::getRights($users, $log['ID'], $createdBy, 'counter');
						CSocNetLogRights::add($log['ID'], $rightsCounter, false, false);
						CSocNetLog::counterIncrement($log['ID'], $soFields['EVENT_ID'], false, 'L', false);

						/* Return previous state rights */
						CSocNetLogRights::deleteByLogID($log['ID']);
						CSocNetLogRights::add($log['ID'], $rights, false, false);

						self::setSocnetFollow($users, $log['ID'], 'Y');
						self::setSocnetFollow($users, $log['ID'], 'N');
					}

					/* Completion of the process for the author */
					if ($flagCompleteProcess)
					{
						$activeUsers = CBPTaskService::getWorkflowParticipants($workflowId);
						$rights = self::getRights($activeUsers, $log['ID'], $createdBy, 'post');
						$usersRight = self::getUserIdForRight($rights);

						/* Increment the counter for author */
						$users[] = $createdBy;
						CSocNetLogRights::deleteByLogID($log['ID']);
						$rightsCounter = self::getRights($users, $log['ID'], $createdBy, 'counter');
						CSocNetLogRights::add($log['ID'], $rightsCounter, false, false);
						CSocNetLog::counterIncrement($log['ID'], $soFields['EVENT_ID'], false, 'L', false);

						/* Return previous state rights */
						CSocNetLogRights::deleteByLogID($log['ID']);
						CSocNetLogRights::add($log['ID'], $rights, false, false);

						self::setSocnetFollow($users, $log['ID'], 'Y');
						self::setSocnetFollow($usersRight, $log['ID'], 'N');
					}
				}
			}
			else
			{
				$activeUsers = CBPTaskService::getWorkflowParticipants($workflowId);

				$soFields['=LOG_DATE'] = $DB->currentTimeFunction();
				$logId = CSocNetLog::add($soFields, false);
				if (intval($logId) > 0)
				{
					$rights = self::getRights($activeUsers, $logId, $createdBy, 'post');
					CSocNetLogRights::add($logId, $rights, false, false);
					$usersRight = self::getUserIdForRight($rights);
					self::setSocnetFollow($usersRight, $logId, 'N');
				}
				CSocNetLog::counterIncrement($logId, $soFields['EVENT_ID'], false, 'L', false);
			}
		}
		return $logId;
	}

	public static function onFillSocNetAllowedSubscribeEntityTypes(&$socnetEntityTypes)
	{
		$socnetEntityTypes[] = SONET_LISTS_NEW_POST_ENTITY;

		global $arSocNetAllowedSubscribeEntityTypesDesc;
		$arSocNetAllowedSubscribeEntityTypesDesc[SONET_LISTS_NEW_POST_ENTITY] = array(
			'USE_CB_FILTER' => 'Y',
			'TITLE_LIST' => '',
			'TITLE_ENTITY' => '',
		);
	}

	public static function onFillSocNetLogEvents(&$socnetLogEvents)
	{
		$socnetLogEvents['lists_new_element'] = array(
			'ENTITIES' => array(
				SONET_LISTS_NEW_POST_ENTITY => array(),
			),
			'FORUM_COMMENT_ENTITY' => 'WF',
			'CLASS_FORMAT' => 'CListsLiveFeed',
			'METHOD_FORMAT' => 'formatListsElement',
			'HAS_CB' => 'Y',
			'FULL_SET' => array('lists_new_element', 'lists_new_element_comment'),
			'COMMENT_EVENT' => array(
				'MODULE_ID' => 'lists_new_element',
				'EVENT_ID' => 'lists_new_element_comment',
				'OPERATION' => 'view',
				'OPERATION_ADD' => 'log_rights',
				'ADD_CALLBACK' => array('CListsLiveFeed', 'addCommentLists'),
				'UPDATE_CALLBACK' => array('CSocNetLogTools', 'UpdateComment_Forum'),
				'DELETE_CALLBACK' => array('CListsLiveFeed', 'deleteCommentLists'),
				'CLASS_FORMAT' => 'CSocNetLogTools',
				'METHOD_FORMAT' => 'FormatComment_Forum',
				"RATING_TYPE_ID" => "FORUM_POST",
			),
		);
	}

	public static function formatListsElement($fields, $params, $mail = false)
	{
		global $CACHE_MANAGER;

		$element = array(
			'EVENT' => $fields,
			'CREATED_BY' => array(),
			'ENTITY' => array(),
			'EVENT_FORMATTED' => array(),
		);

		$userObject = CUser::getByID($fields['ENTITY_ID']);
		$user = $userObject->fetch();
		if ($user)
		{
			if(!$mail)
			{
				global $APPLICATION;
				$rights = array();
				$rightsQuery = CSocNetLogRights::getList(array(), array('LOG_ID' => $fields['ID']));
				while ($right = $rightsQuery->fetch())
				{
					$rights[] = $right['GROUP_CODE'];
				}

				if(defined('BX_COMP_MANAGED_CACHE'))
				{
					$CACHE_MANAGER->registerTag('LISTS_ELEMENT_LIVE_FEED');
				}

				$componentResult = $APPLICATION->includeComponent(
					'bitrix:bizproc.workflow.livefeed',
					'',
					Array(
						'WORKFLOW_ID' => $fields['MESSAGE'],
						'SITE_TEMPLATE_ID' => (isset($params['SITE_TEMPLATE_ID']) ? $params['SITE_TEMPLATE_ID'] : ''),
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);

				$siteDir = rtrim(SITE_DIR, '/');
				$url = CSocNetLogTools::formatEvent_GetURL($fields, true);
				$url = str_replace('#SITE_DIR#', $siteDir, $url);
				$url .= ''.$fields['ID'].'/';

				$componentResultMessage = $componentResult ? $componentResult['MESSAGE'] : null;
				$element = array(
					'EVENT' => $fields,
					'EVENT_FORMATTED' => array(
						'TITLE_24' => '<a href="'.$fields['TITLE_TEMPLATE'].'" class="bx-lists-live-feed-title-link">'.$fields['TITLE'].'</a>',
						'MESSAGE' => $fields['TEXT_MESSAGE'] . $componentResultMessage,
						'IS_IMPORTANT' => false,
						'STYLE' => 'new-employee',
						'AVATAR_STYLE' => 'avatar-info',
						'DESTINATION' => CSocNetLogTools::formatDestinationFromRights($rights, array_merge($params, array('CREATED_BY' => $fields['USER_ID']))),
						'URL' => $url,
					),
					'CREATED_BY' => CSocNetLogTools::formatEvent_GetCreatedBy($fields, $params, $mail),
					'AVATAR_SRC' => CSocNetLog::formatEvent_CreateAvatar($fields, $params),
					'CACHED_JS_PATH' => $componentResult['CACHED_JS_PATH'] ?? null,
					'CACHED_CSS_PATH' => $componentResult['CACHED_CSS_PATH'] ?? null,
				);
				if (isset($params['MOBILE']) && $params['MOBILE'] == 'Y')
				{
					$element['EVENT_FORMATTED']['TITLE_24'] = Loc::getMessage('LISTS_LF_MOBILE_DESTINATION');
					$element['EVENT_FORMATTED']['TITLE_24_2'] = $fields['TITLE'];
					unset($element['CACHED_CSS_PATH']);
				}

				if (CModule::IncludeModule('bizproc'))
				{
					$workflowId = \CBPStateService::getWorkflowByIntegerId($element['EVENT']['SOURCE_ID']);
				}

				if ($workflowId)
				{
					$element['EVENT']['SOURCE_ID'] = $workflowId;
				}
			}
			return $element;
		}
	}

	public static function addCommentLists($fields)
	{
		global $DB, $USER_FIELD_MANAGER;

		if (!CModule::IncludeModule('forum') || !CModule::IncludeModule('bizproc'))
			return false;

		$ufFileId = array();
		$ufDocId = array();
		$fieldsMessage = array();
		$messageId = array();
		$error = array();
		$note = array();
		$workflowId = null;
		$authorId = (int)($fields['USER_ID'] ?? 0);
		$mentions = [];

		$sonetLogQuery = CSocNetLog::GetList(
			array(),
			array('ID' => $fields['LOG_ID']),
			false,
			false,
			array('ID', 'SOURCE_ID', 'SITE_ID', 'MESSAGE', 'USER_ID')
		);
		if($sonetLog = $sonetLogQuery->fetch())
		{
			$workflowId = $sonetLog['MESSAGE'];
			$users = CBPTaskService::getWorkflowParticipants($sonetLog['MESSAGE'], CBPTaskUserStatus::Waiting);

			if(preg_match_all("/(?<=\[USER=)(?P<id>[0-9]+)(?=\])/", $fields['TEXT_MESSAGE'], $matches))
			{
				$mentions = $matches['id'];
				$users = array_unique(array_merge($users, $mentions));
			}

			$users[] = $sonetLog['USER_ID'];
			self::setSocnetFollow($users, $sonetLog['ID'], 'Y', false, true);

			$forumId = CBPHelper::getForumId();
			if($forumId)
			{
				$topicQuery = CForumTopic::GetList(array(), array('FORUM_ID' => $forumId, 'XML_ID' => 'WF_'.$sonetLog['MESSAGE']));
				if ($topicQuery && ($topic = $topicQuery->fetch()))
				{
					$topicId = $topic['ID'];
				}
				else
				{
					$dataTopic = array(
						'AUTHOR_ID' => 0,
						'TITLE' => 'WF_'.$sonetLog['MESSAGE'],
						'TAGS' => '',
						'MESSAGE' => 'WF_'.$sonetLog['MESSAGE'],
						'XML_ID' => 'WF_'.$sonetLog['MESSAGE'],
					);

					$userStart = array(
						"ID" => $dataTopic["AUTHOR_ID"],
						"NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"],
					);

					$DB->StartTransaction();
					$topicFields = Array(
						"TITLE" => $dataTopic["TITLE"],
						"TAGS" => $dataTopic["TAGS"],
						"FORUM_ID" => $forumId,
						"USER_START_ID"	=> $userStart["ID"],
						"USER_START_NAME" => $userStart["NAME"],
						"LAST_POSTER_NAME" => $userStart["NAME"],
						"XML_ID" => $dataTopic["XML_ID"],
						"APPROVED" => "Y",
						"PERMISSION_EXTERNAL" =>'Q',
						"PERMISSION" => 'Y',
					);

					$topicId = CForumTopic::Add($topicFields);

					if (intval($topicId) > 0)
					{
						$dataTopic['MESSAGE'] = strip_tags($dataTopic['MESSAGE']);

						$dataFields = Array(
							"POST_MESSAGE" => $dataTopic['MESSAGE'],
							"AUTHOR_ID" => $userStart["ID"],
							"AUTHOR_NAME" => $userStart["NAME"],
							"FORUM_ID" => $forumId,
							"TOPIC_ID" => $topicId,
							"APPROVED" => "Y",
							"NEW_TOPIC" => "Y",
							"PARAM1" => "WF",
							"PARAM2" => 0,
							"PERMISSION_EXTERNAL" => 'Q',
							"PERMISSION" => 'Y',
						);
						$startMessageId = CForumMessage::Add($dataFields, false, array("SKIP_INDEXING" => "Y", "SKIP_STATISTIC" => "N"));
						if (intval($startMessageId) <= 0)
						{
							CForumTopic::Delete($topicId);
							$topicId = 0;
						}
					}

					if (intval($topicId) <= 0)
					{
						$DB->Rollback();
					}
					else
					{
						$DB->Commit();
					}
				}

				if ($topicId)
				{
					$fieldsMessage = array(
						'POST_MESSAGE' => $fields['TEXT_MESSAGE'],
						'USE_SMILES' => 'Y',
						'PERMISSION_EXTERNAL' => 'Q',
						'PERMISSION' => 'Y',
						'APPROVED' => 'Y',
					);

					$tmp = false;
					$USER_FIELD_MANAGER->editFormAddFields('SONET_COMMENT', $tmp);
					if (is_array($tmp))
					{
						if (array_key_exists('UF_SONET_COM_DOC', $tmp))
						{
							$GLOBALS['UF_FORUM_MESSAGE_DOC'] = $tmp['UF_SONET_COM_DOC'];
						}
						elseif (array_key_exists('UF_SONET_COM_FILE', $tmp))
						{
							$fieldsMessage['FILES'] = array();
							foreach($tmp['UF_SONET_COM_FILE'] as $fileId)
							{
								$fieldsMessage['FILES'][] = array('FILE_ID' => $fileId);
							}
						}

						if (array_key_exists("UF_SONET_COM_URL_PRV", $tmp))
						{
							$GLOBALS["UF_FORUM_MES_URL_PRV"] = $tmp["UF_SONET_COM_URL_PRV"];
						}
					}

					$messageId = ForumAddMessage("REPLY", $forumId, $topicId, 0, $fieldsMessage, $error, $note);

					if ($messageId > 0)
					{
						$addedMessageFilesQuery = CForumFiles::getList(array('ID' => 'ASC'), array('MESSAGE_ID' => $messageId));
						while ($addedMessageFiles = $addedMessageFilesQuery->fetch())
						{
							$ufFileId[] = $addedMessageFiles['FILE_ID'];
						}
						$ufDocId = $USER_FIELD_MANAGER->getUserFieldValue('FORUM_MESSAGE', 'UF_FORUM_MESSAGE_DOC', $messageId, LANGUAGE_ID);
						$ufUrlPreview = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MES_URL_PRV", $messageId, LANGUAGE_ID);
					}
				}
			}
		}

		if (!$messageId)
		{
			$error = Loc::getMessage('LISTS_LF_ADD_COMMENT_SOURCE_ERROR');
		}

		if (!$error)
		{
			\Bitrix\Bizproc\Integration\CommentListener::onListsProcessesCommentAdd($workflowId, $authorId, $mentions);
		}

		return array(
			'SOURCE_ID' => $messageId,
			'MESSAGE' => ($fieldsMessage ? $fieldsMessage['POST_MESSAGE'] : false),
			'RATING_TYPE_ID' => 'FORUM_POST',
			'RATING_ENTITY_ID' => $messageId,
			'ERROR' => $error,
			'NOTES' => $note,
			'UF' => array(
				'FILE' => $ufFileId,
				'DOC' => $ufDocId,
				'URL_PREVIEW' => $ufUrlPreview,
			),
		);
	}

	public static function deleteCommentLists($fields)
	{
		$parentResult = \CSocNetLogTools::DeleteComment_Forum($fields);

		if (empty($parentResult['ERROR']) && CModule::IncludeModule('bizproc'))
		{
			$workflowId = (string)\CBPStateService::getWorkflowByIntegerId($fields['LOG_SOURCE_ID']);
			$authorId = (int)$fields['USER_ID'];
			$created = \Bitrix\Main\Type\DateTime::createFromUserTime($fields['LOG_DATE']);
			\Bitrix\Bizproc\Integration\CommentListener::onListsProcessesCommentDelete($workflowId, $authorId, $created);
		}

		return $parentResult;
	}

	protected static function getRights($users, $logId, $createdBy, $method)
	{
		$rights = array();
		$rights[] = 'SA'; //socnet admin

		if(!empty($users))
		{
			if($method == 'post')
				$users[] = $createdBy;

			foreach($users as $userId)
			{
				$rights[] = 'U'.$userId;
			}
		}

		$rights = array_unique($rights);

		return $rights;
	}

	protected static function getUserIdForRight($rights)
	{
		$users = array();
		foreach($rights as $user)
		{
			if($user != 'SA')
			{
				$users[] = mb_substr($user, 1);
			}
		}
		return $users;
	}

	protected static function setSocnetFollow($users, $logId, $type, $manualMode = false, $addingComment = false)
	{
		if($manualMode)
		{
			foreach($users as $userId)
			{
				$logFollowObject = CSocNetLogFollow::getList(
					array('USER_ID' => $userId, 'REF_ID' => $logId), array('BY_WF', 'TYPE'));
				$logFollow = $logFollowObject->fetch();
				if(!empty($logFollow) && $logFollow['TYPE'] == 'Y' && !$logFollow['BY_WF'])
				{
					CSocNetLogFollow::delete($userId, 'L'.$logId, false);
					CSocNetLogFollow::set($userId, 'L'.$logId, $type,
						ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL", SITE_ID), SITE_ID, true);

					if (
						$type == 'Y'
						&& method_exists('\Bitrix\Socialnetwork\ComponentHelper','userLogSubscribe')
					)
					{
						\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
							'logId' => $logId,
							'userId' => $userId,
							'typeList' => array(
								'COUNTER_COMMENT_PUSH',
							),
						));
					}
				}
			}
		}
		else
		{
			if($type == 'Y')
			{
				foreach($users as $userId)
				{
					$logFollowObject = CSocNetLogFollow::getList(
						array('USER_ID' => $userId, 'REF_ID' => $logId), array('BY_WF'));
					$logFollow = $logFollowObject->fetch();

					if(
						empty($logFollow)
						|| ($logFollow['BY_WF'] == 'Y' || $addingComment)
					)
					{
						CSocNetLogFollow::delete($userId, 'L'.$logId, false);

						if (method_exists('\Bitrix\Socialnetwork\ComponentHelper','userLogSubscribe'))
						{
							\Bitrix\Socialnetwork\ComponentHelper::userLogSubscribe(array(
								'logId' => $logId,
								'userId' => $userId,
								'typeList' => array(
									'FOLLOW',
									'COUNTER_COMMENT_PUSH',
								),
								'followDate' => 'CURRENT',
								'followByWF' => true,
							));
						}
						else
						{
							CSocNetLogFollow::set($userId, 'L'.$logId, 'Y',
								ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL", SITE_ID), SITE_ID, true);
						}
					}
				}
			}
			else
			{
				foreach($users as $userId)
				{
					$logFollowObject = CSocNetLogFollow::getList(
						array('USER_ID' => $userId, 'REF_ID' => $logId), array('BY_WF'));
					$logFollow = $logFollowObject->fetch();

					if(
						empty($logFollow)
						|| $logFollow['BY_WF'] == 'Y'
					)
					{
						CSocNetLogFollow::set($userId, 'L'.$logId, 'N', false, SITE_ID, true);
					}
				}
			}
		}
	}

	protected static function getSiteName()
	{
		return COption::getOptionString('main', 'site_name', '');
	}

	public static function BeforeIndexSocNet($bxSocNetSearch, $fields)
	{
		static $bizprocForumId = false;

		if (!$bizprocForumId)
		{
			$bizprocForumId = intval(COption::GetOptionString('bizproc', 'forum_id'));
		}

		if(
			isset($fields['ENTITY_TYPE_ID'])
			&& $fields['ENTITY_TYPE_ID'] == 'FORUM_POST'
			&& intval($fields['PARAM1']) == $bizprocForumId
			&& !empty($fields['PARAM2'])
			&& !empty($bxSocNetSearch->_params["PATH_TO_WORKFLOW"])
			&& CModule::IncludeModule("forum")
			&& CModule::IncludeModule("bizproc")
		)
		{
			$topic = CForumTopic::GetByID($fields['PARAM2']);

			if (
				!empty($topic)
				&& is_array($topic)
				&& !empty($topic["XML_ID"])
			)
			{
				if (preg_match('/^WF_([0-9a-f\.]+)/', $topic["XML_ID"], $match))
				{
					$workflowId = $match[1];
					$state = CBPStateService::GetStateDocumentId($workflowId);

					if (
						$state[0] == 'lists'
						&& $state[1] == 'BizprocDocument'
						&& CModule::IncludeModule('iblock')
						&& (intval($state[2]) > 0)
					)
					{
						$iblockElementQuery = CIBlockElement::GetList(
							array(),
							array(
								"ID" => intval($state[2]),
							),
							false,
							false,
							array("ID", "IBLOCK_ID")
						);

						if ($iblockElement = $iblockElementQuery->Fetch())
						{
							$listId = $iblockElement["IBLOCK_ID"];

							$fields["URL"] = $bxSocNetSearch->Url(
								str_replace(
									array("#list_id#", "#workflow_id#"),
									array($listId, urlencode($workflowId)),
									$bxSocNetSearch->_params["PATH_TO_WORKFLOW"]
								),
								array(
									"MID" => $fields["ENTITY_ID"],
								),
								"message".$fields["ENTITY_ID"]
							);

							if (
								!empty($fields["LID"])
								&& is_array($fields["LID"])
							)
							{
								foreach ($fields["LID"] as $siteId => $url)
								{
									$fields["LID"][$siteId] = $fields["URL"];
								}
							}
						}
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Called from LiveFeed
	 * @param array $comment
	 */
	public static function OnAfterSonetLogEntryAddComment($comment)
	{
		if ($comment["EVENT_ID"] != "lists_new_element_comment")
		{
			return;
		}

		$logQuery = CSocNetLog::getList(
			array(),
			array(
				"ID" => $comment["LOG_ID"],
				"EVENT_ID" => "lists_new_element",
			),
			false,
			false,
			array("ID", "SOURCE_ID", "URL", "TITLE", "USER_ID", "PARAMS")
		);

		if (($log = $logQuery->fetch()) && (intval($log["SOURCE_ID"]) > 0))
		{
			$params = unserialize($log["PARAMS"], ['allowed_classes' => false]);
			$title = $log["TITLE"]." - ".$params["ELEMENT_NAME"];

			$userIdsToNotify = self::getUserIdsFromRights($log['ID']);

			foreach ($userIdsToNotify as $userId)
			{
				CListsLiveFeed::notifyComment(
					[
						"LOG_ID" => $comment["LOG_ID"],
						"MESSAGE_ID" => $comment["SOURCE_ID"],
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => $comment["USER_ID"],
						"URL" => $log["URL"],
						"TITLE" => $title,
					]
				);
			}
		}
	}

	/**
	 * Called from popup
	 * @param string $entityType
	 * @param int $entityId
	 * $param array $comment
	 */
	public static function OnForumCommentIMNotify($entityType, $entityId, $comment)
	{
		if ($entityType != "WF")
			return;

		$logQuery = CSocNetLog::getList(
			array(),
			array(
				"ID" => $comment["LOG_ID"],
				"EVENT_ID" => "lists_new_element",
			),
			false,
			false,
			array("ID", "SOURCE_ID", "URL", "TITLE", "USER_ID", "PARAMS")
		);

		if (($log = $logQuery->fetch()) && (intval($log["SOURCE_ID"]) > 0))
		{
			$params = unserialize($log["PARAMS"], ['allowed_classes' => false]);
			$title = $log["TITLE"]." - ".$params["ELEMENT_NAME"];

			$userIdsToNotify = self::getUserIdsFromRights($log['ID']);

			foreach ($userIdsToNotify as $userId)
			{
				CListsLiveFeed::notifyComment(
					[
						"LOG_ID" => $log["ID"],
						"MESSAGE_ID" => $comment["MESSAGE_ID"],
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => $comment["USER_ID"],
						"URL" => $log["URL"],
						"TITLE" => $title,
					]
				);
			}
		}
	}

	public static function NotifyComment($comment)
	{
		if (!Loader::includeModule("im"))
			return;
		if($comment["TO_USER_ID"] == $comment["FROM_USER_ID"])
			return;

		$siteDir = rtrim(SITE_DIR, '/');
		$url = str_replace('#SITE_DIR#', $siteDir, $comment["URL"]);
		$url .= ''.$comment['LOG_ID'].'/';

		$messageAddComment = Loc::getMessage("LISTS_LF_COMMENT_MESSAGE_ADD",
			array("#PROCESS#" => '<a href="'.$url.'" class="bx-notifier-item-action">'.$comment["TITLE"].'</a>'));
		$userQuery = CUser::getList(
			"id",
			"asc",
			array("ID_EQUAL_EXACT" => intval($comment["FROM_USER_ID"])),
			array("FIELDS" => array("PERSONAL_GENDER"))
		);
		if ($user = $userQuery->fetch())
		{
			switch ($user["PERSONAL_GENDER"])
			{
				case "F":
				case "M":
				$messageAddComment = Loc::getMessage("LISTS_LF_COMMENT_MESSAGE_ADD" . '_' . $user["PERSONAL_GENDER"],
					array("#PROCESS#" => '<a href="'.$url.'" class="bx-notifier-item-action">'.$comment["TITLE"].'</a>'));
					break;
				default:
					break;
			}
		}

		$messageFields = array(
			"TO_USER_ID" => $comment["TO_USER_ID"],
			"FROM_USER_ID" => $comment["FROM_USER_ID"],
			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
			"NOTIFY_MODULE" => "lists",
			"NOTIFY_TAG" => "SONET|EVENT|".$comment["LOG_ID"],
			"NOTIFY_SUB_TAG" => "FORUM|COMMENT|".$comment["MESSAGE_ID"]."|".$comment["TO_USER_ID"],
			"NOTIFY_EVENT" => "event_lists_comment_add",
			"NOTIFY_MESSAGE" => $messageAddComment,
		);

		CIMNotify::Add($messageFields);
	}

	public static function OnSendMentionGetEntityFields($commentFields)
	{
		if (!in_array($commentFields["EVENT_ID"], array("lists_new_element_comment")))
		{
			return false;
		}

		if (!CModule::IncludeModule("socialnetwork"))
		{
			return true;
		}

		$dbLog = CSocNetLog::GetList(
			array(),
			array(
				"ID" => $commentFields["LOG_ID"],
			),
			false,
			false,
			array("ID", "TITLE", "SOURCE_ID", "PARAMS")
		);

		if ($log = $dbLog->GetNext())
		{
			$genderSuffix = "";
			$dbUser = CUser::GetByID($commentFields["USER_ID"]);
			if($user = $dbUser->Fetch())
			{
				$genderSuffix = $user["PERSONAL_GENDER"];
			}

			$params = unserialize($log["~PARAMS"], ['allowed_classes' => false]);
			$title = $log["TITLE"]." - ".$params["ELEMENT_NAME"];
			$entityName = GetMessage("LISTS_LF_COMMENT_MENTION_TITLE", Array("#PROCESS#" => $title));
			$notifyMessage = GetMessage("LISTS_LF_COMMENT_MENTION" . ($genderSuffix <> '' ? "_" . $genderSuffix : ""), Array("#title#" => "<a href=\"#url#\" class=\"bx-notifier-item-action\">".$entityName."</a>"));
			$notifyMessageOut = GetMessage("LISTS_LF_COMMENT_MENTION" . ($genderSuffix <> '' ? "_" . $genderSuffix : ""), Array("#title#" => $entityName)) . " (" . "#server_name##url#)";

			$strPathToLogEntry = str_replace("#log_id#", $log["ID"], COption::GetOptionString("socialnetwork", "log_entry_page", "/company/personal/log/#log_id#/", SITE_ID));
			$strPathToLogEntryComment = $strPathToLogEntry . (mb_strpos($strPathToLogEntry, "?") !== false ? "&" : "?") . "commentID=" . $commentFields["ID"] . "#com" . $commentFields["ID"];

			$return = array(
				"URL" => $strPathToLogEntryComment,
				"NOTIFY_MODULE" => "lists",
				"NOTIFY_TAG" => "LISTS|COMMENT_MENTION|".$commentFields["ID"],
				"NOTIFY_MESSAGE" => $notifyMessage,
				"NOTIFY_MESSAGE_OUT" => $notifyMessageOut,
			);

			return $return;
		}
		else
		{
			return false;
		}
	}

	public static function OnSocNetGroupDelete($groupId)
	{
		$iblockIdList = array();
		$res = \CIBlock::getList(array(), array("SOCNET_GROUP_ID" => $groupId));
		while($iblock = $res->fetch())
		{
			$iblockIdList[] = $iblock["ID"];
		}

		if (empty($iblockIdList))
		{
			return true;
		}

		foreach($iblockIdList as $iblockId)
		{
			CIBlock::Delete($iblockId);
		}

		return true;
	}

	private static function getUserIdsFromRights(int $logId): array
	{
		$userIdsToNotify = [];
		$rightsResult = \CSocNetLogRights::getList([], ['LOG_ID' => $logId]);
		while ($right = $rightsResult->fetch())
		{
			if (preg_match('/^U(\d+)$/', $right["GROUP_CODE"], $matches))
			{
				$userIdsToNotify[] = $matches[1];
			}
		}

		return $userIdsToNotify;
	}
}