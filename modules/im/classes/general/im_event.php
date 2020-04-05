<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Livefeed;

class CIMEvent
{
	public static function OnFileDelete($params)
	{
		if (!in_array($params['MODULE_ID'], Array('im', 'imopenlines')))
		{
			return true;
		}

		$result = IM\Model\ChatTable::getList(Array(
			'select' => Array('ID', 'AUTHOR_ID'),
			'filter' => Array('=AVATAR' => $params['ID'])
		));
		while ($row = $result->fetch())
		{
			IM\Model\ChatTable::update($row['ID'], Array('AVATAR' => ''));

			$obCache = new CPHPCache();
			$arRel = CIMChat::GetRelationById($row['ID']);
			foreach ($arRel as $rel)
			{
				$obCache->CleanDir('/bx/imc/recent'.CIMMessenger::GetCachePath($rel['USER_ID']));
			}
		}
	}

	public static function OnBeforeUserSendPassword($params)
	{
		$bots = IM\Bot::getListCache();
		if (empty($bots))
			return true;

		if (isset($params['LOGIN']) && !empty($params['LOGIN']))
		{
			if (substr($params['LOGIN'], 0, strlen(IM\Bot::LOGIN_START)) == IM\Bot::LOGIN_START)
			{
				$orm = \Bitrix\Main\UserTable::getList(Array(
					'filter' => Array(
						'=LOGIN' => $params['LOGIN'],
						'=EXTERNAL_AUTH_ID' => IM\Bot::EXTERNAL_AUTH_ID
					)
				));
				if ($orm->fetch())
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_EVENT_ERROR_CHANGE_PASSWORD_FOR_BOT"), "ERROR_CHANGE_PASSWORD_FOR_BOT");
					return false;
				}
			}
		}

		if (isset($params['EMAIL']) && !empty($params['EMAIL']))
		{
			$orm = \Bitrix\Main\UserTable::getList(Array(
				'filter' => Array(
					'=EMAIL' => $params['EMAIL'],
					'=EXTERNAL_AUTH_ID' => IM\Bot::EXTERNAL_AUTH_ID
				)
			));
			if ($orm->fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_EVENT_ERROR_CHANGE_PASSWORD_FOR_BOT"), "ERROR_CHANGE_PASSWORD_FOR_BOT");
				return false;
			}
		}

		return true;
	}

	public static function OnAddRatingVote($id, $arParams)
	{
		if (!Loader::includeModule("socialnetwork"))
		{
			return true;
		}

		$ratingNotifyTag = "RATING|".($arParams['VALUE'] >= 0 ? "" : "DL|").$arParams['ENTITY_TYPE_ID']."|".$arParams['ENTITY_ID'];
		if (!empty($arParams['REACTION']))
		{
			$ratingNotifyTag .= "|".$arParams['REACTION'];
		}
		$ratingMentionNotifyTag = "RATING_MENTION|".($arParams['VALUE'] >= 0?"":"DL|").$arParams['ENTITY_TYPE_ID']."|".$arParams['ENTITY_ID'];

		$contentId = Livefeed\Provider::getContentId(array(
			"RATING_TYPE_ID" => $arParams['ENTITY_TYPE_ID'],
			"RATING_ENTITY_ID" => $arParams['ENTITY_ID']
		));

		if (!empty($contentId['ENTITY_TYPE']))
		{
			$liveFeedEntity = Livefeed\Provider::init(array(
				'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
				'ENTITY_ID' => $contentId['ENTITY_ID']
			));

			if (!$liveFeedEntity)
			{
				return false;
			}

			if ($arParams['OWNER_ID'] != $arParams['USER_ID']) // AUX
			{
				$originalText = $liveFeedEntity->getSourceOriginalText();
				$auxData = $liveFeedEntity->getSourceAuxData();

				if (
					strlen($originalText) > 0
					&& !empty($auxData)
				)
				{
					$handlerManager = new Bitrix\Socialnetwork\CommentAux\HandlerManager();
					/** @var bool|object $handler */
					if($handler = $handlerManager->getHandlerByPostText($originalText))
					{
						$handler->setOptions(array(
							'im' => true
						));
						$handler->sendRatingNotification($auxData, $arParams);
						return true;
					}
				}
			}

			$title = $liveFeedEntity->getSourceTitle();
			$description = $liveFeedEntity->getSourceDescription();
			$url = $liveFeedEntity->getLiveFeedUrl();

			$arMentionedUserID = array();
			if (!empty($description))
			{
				preg_match_all("/\[user\s*=\s*([^\]]*)\](.+?)\[\/user\]/is".BX_UTF_PCRE_MODIFIER, $description, $mention);
				if (!empty($mention))
				{
					$arMentionedUserID = array_merge($arMentionedUserID, $mention[1]);
				}
			}
			$arMentionedUserID = array_unique($arMentionedUserID);

			$title = CTextParser::clearAllTags($title);
			$description = CTextParser::clearAllTags($description);

			if (
				$arParams['OWNER_ID'] != $arParams['USER_ID']
				|| !empty($arMentionedUserID)
			)
			{
				$arParams["ENTITY_LINK"] = $url;
				$arParams["ENTITY_PARAM"] = '';

				if (
					in_array('photo_photo', $liveFeedEntity->getEventId())
					|| in_array('photo', $liveFeedEntity->getEventId())
				)
				{
					$arParams["ENTITY_PARAM"] = 'photos'; // it was also 'library' value
				}
				elseif (in_array('wiki', $liveFeedEntity->getEventId()))
				{
					$arParams["ENTITY_PARAM"] = 'wiki';
				}

				$arParams["ENTITY_TITLE"] = trim(strip_tags(str_replace(array("\r\n","\n","\r"), ' ', $title)));
				$arParams["ENTITY_MESSAGE"] = trim(strip_tags(str_replace(array("\r\n","\n","\r"), ' ', $description)));
				$arParams["ENTITY_BODY"] = preg_replace('/(.+?)(\r|\n)+.*/is'.BX_UTF_PCRE_MODIFIER,'\\1', $description);

				if (
					(
						strlen($arParams["ENTITY_TITLE"]) > 0
						|| strlen($arParams["ENTITY_MESSAGE"]) > 0
					)
					&& strlen($arParams["ENTITY_LINK"]) > 0
				)
				{
					$originalLink = $arParams["ENTITY_LINK"];

					$bExtranetInstalled = CModule::IncludeModule("extranet");
					if ($bExtranetInstalled)
					{
						$arSites = array();
						$extranet_site_id = CExtranet::GetExtranetSiteID();
						$intranet_site_id = CSite::GetDefSite();
						$dbSite = CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));
						while($arSite = $dbSite->Fetch())
						{
							$arSites[$arSite["ID"]] = array(
								"DIR" => (strlen(trim($arSite["DIR"])) > 0 ? $arSite["DIR"] : "/"),
								"SERVER_NAME" => (strlen(trim($arSite["SERVER_NAME"])) > 0 ? $arSite["SERVER_NAME"] : COption::GetOptionString("main", "server_name", $_SERVER["HTTP_HOST"]))
							);
						}
					}
					$bSentToOwner = false;
					if ($arParams['OWNER_ID'] != $arParams['USER_ID'])
					{
						$followValue = CSocNetLogFollow::GetExactValueByRating(
							intval($arParams['OWNER_ID']),
							trim($arParams["ENTITY_TYPE_ID"]),
							intval($arParams["ENTITY_ID"])
						);

						if ($followValue != "N")
						{
							$arParams['ENTITY_LINK'] = self::GetMessageRatingEntityURL(
								$originalLink,
								intval($arParams['OWNER_ID']),
								$arSites,
								$intranet_site_id,
								$extranet_site_id
							);

							$arMessageFields = array(
								"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
								"TO_USER_ID" => intval($arParams['OWNER_ID']),
								"FROM_USER_ID" => intval($arParams['USER_ID']),
								"NOTIFY_TYPE" => IM_NOTIFY_FROM,
								"NOTIFY_MODULE" => "main",
								"NOTIFY_EVENT" => "rating_vote",
								"NOTIFY_TAG" => $ratingNotifyTag,
								"NOTIFY_SUB_TAG" => $ratingNotifyTag.'|'.intval($arParams['OWNER_ID']),
								"NOTIFY_MESSAGE" => self::GetMessageRatingVote($arParams),
								"NOTIFY_MESSAGE_OUT" => self::GetMessageRatingVote($arParams, true)
							);
							$bSentToOwner = CIMNotify::Add($arMessageFields);
						}
					}

					if (is_array($arMentionedUserID))
					{
						if (in_array($arParams['ENTITY_TYPE_ID'], array("BLOG_COMMENT", "FORUM_POST")))
						{
							$rsLogComment = CSocNetLogComments::GetList(
								array(),
								array(
									"RATING_TYPE_ID" => $arParams['ENTITY_TYPE_ID'],
									"RATING_ENTITY_ID" =>  $arParams['ENTITY_ID']
								),
								false,
								false,
								array("LOG_ID")
							);
							if ($arLogComment = $rsLogComment->Fetch())
							{
								$logId = $arLogComment["LOG_ID"];
							}
						}
						elseif (in_array($arParams['ENTITY_TYPE_ID'], array("BLOG_POST")))
						{
							$rsLog = CSocNetLog::GetList(
								array(),
								array(
									"RATING_TYPE_ID" => $arParams['ENTITY_TYPE_ID'],
									"RATING_ENTITY_ID" =>  $arParams['ENTITY_ID']
								),
								false,
								false,
								array("ID")
							);
							if ($arLog = $rsLog->Fetch())
							{
								$logId = $arLog["ID"];
							}
						}

						if (intval($logId) > 0)
						{
							$arParams["MENTION"] = true; // for self::GetMessageRatingVote()

							foreach ($arMentionedUserID as $mentioned_user_id)
							{
								if (
									$mentioned_user_id != $arParams['USER_ID']
									&& ($mentioned_user_id != $arParams['OWNER_ID'] || !$bSentToOwner)
									&& CSocNetLogRights::CheckForUserOnly($logId, $mentioned_user_id)
								)
								{
									$followValue = CSocNetLogFollow::GetExactValueByRating(
										intval($mentioned_user_id),
										trim($arParams["ENTITY_TYPE_ID"]),
										intval($arParams["ENTITY_ID"])
									);

									if ($followValue != "N")
									{
										$arParams['ENTITY_LINK'] = self::GetMessageRatingEntityURL(
											$originalLink,
											intval($mentioned_user_id),
											$arSites,
											$intranet_site_id,
											$extranet_site_id
										);

										$arMessageFields = array(
											"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
											"TO_USER_ID" => intval($mentioned_user_id),
											"FROM_USER_ID" => intval($arParams['USER_ID']),
											"NOTIFY_TYPE" => IM_NOTIFY_FROM,
											"NOTIFY_MODULE" => "main",
											"NOTIFY_EVENT" => "rating_vote_mentioned",
											"NOTIFY_TAG" => $ratingMentionNotifyTag,
											"NOTIFY_SUB_TAG" => $ratingMentionNotifyTag.'|'.intval($mentioned_user_id),
											"NOTIFY_MESSAGE" => self::GetMessageRatingVote($arParams),
											"NOTIFY_MESSAGE_OUT" => self::GetMessageRatingVote($arParams, true)
										);

										CIMNotify::Add($arMessageFields);
									}
								}
							}
						}
					}
				}
			}
		}

		return true;
	}

	public static function OnCancelRatingVote($id, $arParams)
	{
		CIMNotify::DeleteByTag("RATING|".$arParams['ENTITY_TYPE_ID']."|".$arParams['ENTITY_ID'], $arParams['USER_ID']);
	}

	public static function GetMessageRatingVote($arParams, $bForMail = false)
	{
		static $intranetInstalled = null;

		if ($intranetInstalled === null)
		{
			$intranetInstalled = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet');
		}

		$like = (
			$arParams['VALUE'] >= 0
				? ($intranetInstalled ? '_REACT' : '_LIKE')
				: '_DISLIKE'
		);

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "OnGetMessageRatingVote") as $event)
		{
			ExecuteModuleEventEx($event, array(&$arParams, &$bForMail));
		}

		if(isset($arParams['MESSAGE'])) // message was generated manually inside OnGetMessageRatingVote
		{
			return $arParams['MESSAGE'];
		}

		$genderSuffix = '';
		if (
			$like == '_REACT'
			&& !empty($arParams['USER_ID'])
			&& intval($arParams['USER_ID']) > 0
		)
		{
			$res = \Bitrix\Main\UserTable::getList(array(
				'filter' => array(
					'ID' => intval($arParams['USER_ID'])
				),
				'select' => array('PERSONAL_GENDER')
			));
			if ($userFields = $res->fetch())
			{
				switch ($userFields['PERSONAL_GENDER'])
				{
					case "M":
					case "F":
						$genderSuffix = '_'.$userFields['PERSONAL_GENDER'];
						break;
					default:
						$genderSuffix = '';
				}
			}
			$like .= $genderSuffix;
		}

		if (!isset($CCTP))
		{
			$CCTP = new CTextParser();
		}

		if (
			$arParams['ENTITY_TYPE_ID'] == 'FORUM_POST'
			|| $arParams['ENTITY_TYPE_ID'] == 'BLOG_COMMENT'
		)
		{
			$stripped = $CCTP->strip_words($arParams["ENTITY_MESSAGE"], 199);
			$arParams["ENTITY_MESSAGE"] = $stripped.(strlen($stripped) != strlen($arParams["ENTITY_MESSAGE"]) ? '...' : '');
		}
		else
		{
			$stripped = $CCTP->strip_words($arParams["ENTITY_TITLE"], 199);
			$arParams["ENTITY_TITLE"] = $stripped.(strlen($stripped) != strlen($arParams["ENTITY_TITLE"]) ? '...' : '');
		}

		if ($bForMail)
		{
			if ($arParams['ENTITY_TYPE_ID'] == 'BLOG_POST')
			{
				$message = str_replace('#LINK#', $arParams["ENTITY_TITLE"], GetMessage('IM_EVENT_RATING_BLOG_POST'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'BLOG_COMMENT')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_MESSAGE"], '', ''), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'FORUM_TOPIC')
			{
				$message = str_replace('#LINK#', $arParams["ENTITY_TITLE"], GetMessage('IM_EVENT_RATING_FORUM_TOPIC'.$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'FORUM_POST')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_MESSAGE"], '', ''), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_SECTION'
				&& $arParams['ENTITY_PARAM'] == 'photos'
			)
			{
				if (isset($arParams["ENTITY_BODY"]))
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), array($arParams["ENTITY_BODY"], '', ''), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM'.$like));
				}
				elseif (is_numeric($arParams["ENTITY_TITLE"]))
				{
					$message = str_replace(Array('#A_START#', '#A_END#'), Array('', ''), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM1'.$like));
				}
				else
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '', ''), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM'.$like));
				}
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'library'
			)
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '', ''), GetMessage('IM_EVENT_RATING_FILE'.$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'wiki'
			)
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '', ''), GetMessage('IM_EVENT_RATING_WIKI'.$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'photos'
			)
			{
				if (isset($arParams["ENTITY_BODY"]))
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_BODY"], '', ''), GetMessage('IM_EVENT_RATING_PHOTO'.$like));
				}
				elseif (is_numeric($arParams["ENTITY_TITLE"]))
				{
					$message = str_replace(Array('#A_START#', '#A_END#'), Array('', ''), GetMessage('IM_EVENT_RATING_PHOTO1'.$like));
				}
				else
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '', ''), GetMessage('IM_EVENT_RATING_PHOTO'.$like));
				}
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'LOG_COMMENT')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '', ''), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'LISTS_NEW_ELEMENT')
			{
				$message = str_replace(
					array(
						'#TITLE#',
						'#A_START#',
						'#A_END#'
					),
					array(
						$arParams["ENTITY_TITLE"],
						'',
						''
					),
					GetMessage('IM_EVENT_RATING_LISTS_NEW_ELEMENT_LIKE'.$like)
				);
			}
			else
			{
				$message = str_replace('#LINK#', $arParams["ENTITY_TITLE"], GetMessage('IM_EVENT_RATING_ELSE'.$like));
			}

			if (strlen($arParams['ENTITY_LINK']) > 0)
			{
				$message .= ' ('.$arParams['ENTITY_LINK'].')';
			}
		}
		else
		{
			if ($arParams['ENTITY_TYPE_ID'] == 'BLOG_POST')
			{
				$message = str_replace('#LINK#', '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">'.$arParams["ENTITY_TITLE"].'</a>', GetMessage('IM_EVENT_RATING_BLOG_POST'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'BLOG_COMMENT')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_MESSAGE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'FORUM_TOPIC')
			{
				$message = str_replace('#LINK#', '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">'.$arParams["ENTITY_TITLE"].'</a>', GetMessage('IM_EVENT_RATING_FORUM_TOPIC'.$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'FORUM_POST')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_MESSAGE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'library'
			)
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_FILE'.$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'wiki'
			)
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_WIKI'.$like));
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_SECTION'
				&& $arParams['ENTITY_PARAM'] == 'photos'
			)
			{
				if (isset($arParams["ENTITY_BODY"]))
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_BODY"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM'.$like));
				}
				elseif (is_numeric($arParams["ENTITY_TITLE"]))
				{
					$message = str_replace(Array('#A_START#', '#A_END#'), Array('<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM1'.$like));
				}
				else
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO_ALBUM'.$like));
				}
			}
			elseif (
				$arParams['ENTITY_TYPE_ID'] == 'IBLOCK_ELEMENT'
				&& $arParams['ENTITY_PARAM'] == 'photos'
			)
			{
				if (isset($arParams["ENTITY_BODY"]))
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_BODY"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO'.$like));
				}
				elseif (is_numeric($arParams["ENTITY_TITLE"]))
				{
					$message = str_replace(Array('#A_START#', '#A_END#'), Array('<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO1'.$like));
				}
				else
				{
					$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_PHOTO'.$like));
				}
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'LOG_COMMENT')
			{
				$message = str_replace(Array('#TITLE#', '#A_START#', '#A_END#'), Array($arParams["ENTITY_TITLE"], '<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">', '</a>'), GetMessage('IM_EVENT_RATING_COMMENT'.($arParams['MENTION'] ? '_MENTION' : '').$like));
			}
			elseif ($arParams['ENTITY_TYPE_ID'] == 'LISTS_NEW_ELEMENT')
			{
				$message = str_replace(
					array(
						'#TITLE#',
						'#A_START#',
						'#A_END#'
					),
					array(
						$arParams["ENTITY_TITLE"],
						'<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">',
						'</a>'
					),
					GetMessage('IM_EVENT_RATING_LISTS_NEW_ELEMENT'.$like)
				);
			}
			else
			{
				$message = str_replace('#LINK#', strlen($arParams['ENTITY_LINK'])>0?'<a href="'.$arParams['ENTITY_LINK'].'" class="bx-notifier-item-action">'.$arParams["ENTITY_TITLE"].'</a>': '<i>'.$arParams["ENTITY_TITLE"].'</i>', GetMessage('IM_EVENT_RATING_ELSE'.$like));
			}

			if ($intranetInstalled)
			{
				$message .= "\n".str_replace("#REACTION#", \CRatingsComponentsMain::getRatingLikeMessage(!empty($arParams['REACTION']) ? $arParams['REACTION'] : ''), Bitrix\Main\Localization\Loc::getMessage("IM_EVENT_RATING_REACTION"));
			}
		}

		return $message;
	}

	public static function GetMessageRatingEntityURL($url, $user_id = false, $arSites = false, $intranet_site_id = false, $extranet_site_id = false)
	{
		static $arSiteData = false;

		if (
			!$arSiteData
			&& IsModuleInstalled('intranet')
			&& CModule::IncludeModule('socialnetwork')
		)
		{
			$arSiteData = CSocNetLogTools::GetSiteData();
		}

		if (
			$arSiteData
			&& count($arSiteData) > 1
		)
		{
			foreach($arSiteData as $siteId => $arUrl)
			{
				$url = str_replace($arUrl["USER_PATH"], "#USER_PATH#", $url);
			}

			$arTmp = CSocNetLogTools::ProcessPath(
				array(
					"URL" => $url
				),
				$user_id
			);

			$url = $arTmp["URLS"]["URL"];
			$url = (
				strpos($url, "http://") === 0
				|| strpos($url, "https://") === 0
					? ""
					: (
						isset($arTmp["SERVER_NAME"])
						&& !empty($arTmp["SERVER_NAME"])
							? $arTmp["SERVER_NAME"]
							: ""
					)
			).$arTmp["URLS"]["URL"];
		}
		else
		{
			if (
				is_array($arSites)
				&& intval($user_id) > 0
				&& strlen($extranet_site_id) > 0
				&& strlen($intranet_site_id) > 0
			)
			{
				$bExtranetUser = false;
				if ($arSites[$extranet_site_id])
				{
					$bExtranetUser = true;
					$rsUser = CUser::GetByID(intval($user_id));
					if ($arUser = $rsUser->Fetch())
					{
						if (intval($arUser["UF_DEPARTMENT"][0]) > 0)
						{
							$bExtranetUser = false;
						}
					}
				}

				if ($bExtranetUser)
				{
					$link = $url;
					if (substr($link, 0, strlen($arSites[$extranet_site_id]['DIR'])) == $arSites[$extranet_site_id]['DIR'])
					{
						$link = substr($link, strlen($arSites[$extranet_site_id]['DIR']));
					}

					$SiteServerName = $arSites[$extranet_site_id]['SERVER_NAME'].$arSites[$extranet_site_id]['DIR'].ltrim($link, "/");
				}
				else
				{
					$link = $url;
					if (substr($link, 0, strlen($arSites[$intranet_site_id]['DIR'])) == $arSites[$intranet_site_id]['DIR'])
					{
						$link = substr($link, strlen($arSites[$intranet_site_id]['DIR']));
					}

					$SiteServerName = $arSites[$intranet_site_id]['SERVER_NAME'].$arSites[$intranet_site_id]['DIR'].ltrim($link, "/");
				}

				$url = (CMain::IsHTTPS() ? "https" : "http")."://".$SiteServerName;
			}
			else
			{
				$SiteServerName = (defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", $_SERVER['SERVER_NAME']));
				if (strlen($SiteServerName) > 0)
				{
					$url = (CMain::IsHTTPS() ? "https" : "http")."://".$SiteServerName.$url;
				}
			}
		}

		return $url;
	}

	private static function GetMessageRatingLogCommentURL($arComment, $user_id = false, $arSites = false, $intranet_site_id = false, $extranet_site_id = false)
	{
		$url = false;

		if (
			!is_array($arComment)
			|| !isset($arComment["ENTITY_TYPE"]) || strlen($arComment["ENTITY_TYPE"]) <= 0
			|| !isset($arComment["ID"]) || intval($arComment["ID"]) <= 0
			|| !isset($arComment["LOG_ID"]) || intval($arComment["LOG_ID"]) <= 0
		)
		{
			return false;
		}

		if (
			is_array($arSites)
			&& intval($user_id) > 0
			&& strlen($extranet_site_id) > 0
			&& strlen($intranet_site_id) > 0
		)
		{
			$bExtranetUser = false;
			if ($arSites[$extranet_site_id])
			{
				$bExtranetUser = true;
				$rsUser = CUser::GetByID($user_id);
				if ($arUser = $rsUser->Fetch())
				{
					if (intval($arUser["UF_DEPARTMENT"][0]) > 0)
					{
						$bExtranetUser = false;
					}
				}
			}

			$user_site_id = ($bExtranetUser ? $extranet_site_id : $intranet_site_id);

			$url = (in_array($arComment["ENTITY_TYPE"], array("CRMLEAD", "CRMCONTACT", "CRMCOMPANY", "CRMDEAL", "CRMACTIVITY")) ? $arSites[$user_site_id]["DIR"]."crm/stream?log_id=#log_id#" : COption::GetOptionString("socialnetwork", "log_entry_page", $arSites[$user_site_id]["DIR"]."company/personal/log/#log_id#/", $user_site_id));
			$url = str_replace("#log_id#", $arComment["LOG_ID"], $url);
			$url .= (strpos($url, "?") !== false ? "&" : "?")."commentId=".$arComment["ID"]."#com".$arComment["ID"];
			$url = (CMain::IsHTTPS() ? "https" : "http")."://".$arSites[$user_site_id]['SERVER_NAME'].$url;
		}
		else
		{
			$url = (in_array($arComment["ENTITY_TYPE"], array("CRMLEAD", "CRMCONTACT", "CRMCOMPANY", "CRMDEAL", "CRMACTIVITY")) ? SITE_DIR."crm/stream?log_id=#log_id#" : COption::GetOptionString("socialnetwork", "log_entry_page", SITE_DIR."company/personal/log/#log_id#/", SITE_ID));
			$url = str_replace("#log_id#", $arComment["LOG_ID"], $url);
			$url .= (strpos($url, "?") !== false ? "&" : "?")."commentId=".$arComment["ID"]."#com".$arComment["ID"];

			$SiteServerName = (defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", $_SERVER['SERVER_NAME']));
			if (strlen($SiteServerName) > 0)
			{
				$url = (CMain::IsHTTPS() ? "https" : "http")."://".$SiteServerName.$url;
			}
		}

		return $url;
	}

	public static function OnAfterUserAdd($arParams)
	{
		if($arParams["ID"] <= 0)
			return false;

		if ($arParams['ACTIVE'] == 'N')
			return false;

		if (IsModuleInstalled('intranet') && !CIMContactList::IsExtranet($arParams))
		{
			if (
				!\Bitrix\Im\User::getInstance($arParams["ID"])->isBot()
				&& !\Bitrix\Im\User::getInstance($arParams["ID"])->isConnector()
				&& !\Bitrix\Im\User::getInstance($arParams["ID"])->isNetwork()
			)
			{
				\CIMContactList::SetRecentForNewUser($arParams["ID"]);
			}

			$commonChatId = CIMChat::GetGeneralChatId();
			if ($commonChatId <= 0)
				return true;

			if (\Bitrix\Im\User::getInstance($arParams["ID"])->isBot())
				return true;

			if (!CIMChat::CanJoinGeneralChatId($arParams["ID"]))
				return true;

			$CIMChat = new CIMChat(0);
			$CIMChat->AddUser($commonChatId, Array($arParams["ID"]));
		}

		return true;
	}

	public static function OnAfterUserUpdate($arParams)
	{
		$commonChatId = CIMChat::GetGeneralChatId();
		if ($commonChatId > 0 && (isset($arParams['ACTIVE']) || isset($arParams['UF_DEPARTMENT'])))
		{
			if ($arParams['ACTIVE'] == 'N')
			{
				CIMMessage::SetReadMessageAll($arParams['ID']);

				if ($commonChatId && CIMChat::GetRelationById($commonChatId, $arParams["ID"]))
				{
					$CIMChat = new CIMChat($arParams["ID"]);
					$CIMChat->DeleteUser($commonChatId, $arParams["ID"]);
				}
			}
			else
			{
				$commonChatId = CIMChat::GetGeneralChatId();
				if ($commonChatId)
				{
					if (\Bitrix\Im\User::getInstance($arParams["ID"])->isBot())
						return true;

					if (!\Bitrix\Im\User::getInstance($arParams["ID"])->isActive())
						return true;

					$userInChat = CIMChat::GetRelationById($commonChatId, $arParams["ID"]);
					$userCanJoin = CIMChat::CanJoinGeneralChatId($arParams["ID"]);

					if ($userInChat && !$userCanJoin)
					{
						$CIMChat = new CIMChat($arParams["ID"]);
						$CIMChat->DeleteUser($commonChatId, $arParams["ID"]);
					}
					else if (!$userInChat && $userCanJoin)
					{
						$CIMChat = new CIMChat(0);
						$CIMChat->AddUser($commonChatId, Array($arParams["ID"]));
					}
				}
			}
		}
	}

	public static function OnUserDelete($ID)
	{
		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		global $DB;

		$arChat = Array();
		$strSQL = "
			SELECT R.CHAT_ID
			FROM b_im_chat C, b_im_relation R
			WHERE R.USER_ID = ".$ID." and R.MESSAGE_TYPE IN ('".IM_MESSAGE_PRIVATE."', '".IM_MESSAGE_SYSTEM."') and R.CHAT_ID = C.ID
		";
		$dbRes = $DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
			$arChat[$arRes['CHAT_ID']] = $arRes['CHAT_ID'];

		if (count($arChat) > 0)
		{
			$strSQL = "DELETE FROM b_im_chat WHERE ID IN (".implode(',', $arChat).")";
			$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		\Bitrix\Im\Bot::unRegister(Array('BOT_ID' => $ID));

		$strSQL = "DELETE FROM b_im_message WHERE AUTHOR_ID = ".$ID;
		$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSQL = "DELETE FROM b_im_relation WHERE USER_ID =".$ID;
		$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSQL = "DELETE FROM b_im_recent WHERE USER_ID = ".$ID;
		$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSQL = "DELETE FROM b_im_status WHERE USER_ID = ".$ID;
		$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);

		$obCache = new CPHPCache();
		if (IsModuleInstalled('bitrix24'))
		{
			$relationList = IM\Model\RecentTable::getList(array(
				"select" => array("USER_ID"),
				"filter" => array(
					"=ITEM_TYPE" => IM_MESSAGE_PRIVATE,
					"=ITEM_ID" => $ID,
				),
			));
			while ($relation = $relationList->fetch())
			{
				$obCache->CleanDir('/bx/imc/recent'.CIMMessenger::GetCachePath($relation['USER_ID']));
			}
		}
		else
		{
			$obCache->CleanDir('/bx/imc/recent');
		}

		$strSQL = "DELETE FROM b_im_recent WHERE ITEM_TYPE = '".IM_MESSAGE_PRIVATE."' and ITEM_ID = ".$ID;
		$DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	public static function OnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "im",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}

class DesktopApplication extends Bitrix\Main\Authentication\Application
{
	protected $validUrls = array(
		"/",
	);

	public static function OnApplicationsBuildList()
	{
		return array(
			"ID" => "desktop",
			"NAME" => GetMessage('DESKTOP_APPLICATION_NAME'),
			"DESCRIPTION" => GetMessage("DESKTOP_APPLICATION_DESC"),
			"SORT" => 80,
			"CLASS" => "DesktopApplication",
		);
	}
}
?>