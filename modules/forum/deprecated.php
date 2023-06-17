<?php
IncludeModuleLangFile(__FILE__);

$GLOBALS["aSortTypes"] = array(
	"reference" => array(GetMessage("FDATE_LAST_MESSAGE"), GetMessage("FMESSAGE_TOPIC"), GetMessage("FNUM_ANSWERS"), GetMessage("FNUM_VIEWS"), GetMessage("FSTART_DATE"), GetMessage("FAUTHOR_TOPIC")),
	"reference_id" => array("P", "T", "N", "V", "D", "A"));

$GLOBALS["aSortDirection"] = array(
	"reference" => array(GetMessage("FASC"), GetMessage("FDESC")),
	"reference_id" => array("ASC", "DESC"));

// A < E < I < M < Q < U < Y
// A - NO ACCESS		E - READ			I - ANSWER
// M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS
$GLOBALS["aForumPermissions"] = array(
	"reference" => array(GetMessage("FNO_ACCESS"), GetMessage("FREAD_ACCESS"), GetMessage("FANSWER_ACCESS"), GetMessage("FNEW_MESSAGE_ACCESS"), GetMessage("FMODERATE_ACCESS"), GetMessage("FEDIT_ACCESS"), GetMessage("FFULL_ACCESS")),
	"reference_id" => array("A", "E", "I", "M", "Q", "U", "Y"));
$GLOBALS["FORUMS_PER_PAGE"] = intVal(COption::GetOptionString("forum", "FORUMS_PER_PAGE", "10"));
$GLOBALS["FORUM_TOPICS_PER_PAGE"] = intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
$GLOBALS["FORUM_MESSAGES_PER_PAGE"] = intVal(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"));


/**
 * @deprecated
 */
function ForumSetAllMessagesReaded($FID = false)
{
	global $USER;

	if ($FID!==false)
	{
		$FID = IntVal($FID);
		CForumNew::SetLabelsBeRead($FID, $USER->GetUserGroupArray());
		return true;
	}

	$arFilter = array();
	if (!CForumUser::IsAdmin())
	{
		$arFilter["LID"] = LANG;
		$arFilter["PERMS"] = array($USER->GetGroups(), 'A');
		$arFilter["ACTIVE"] = "Y";
	}
	$db_Forum = CForumNew::GetList(array(), $arFilter);
	while ($ar_Forum = $db_Forum->Fetch())
	{
		CForumNew::SetLabelsBeRead($ar_Forum["ID"], $USER->GetUserGroupArray());
	}

	return false;
}
/**
 * @deprecated
 */
function ForumSetReader($FID) // DEPRECATED
{
	global $USER;
	$FID = intVal($FID);
	$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID] = CForumNew::GetNowTime("timestamp");
	return false;
}
/**
 * @deprecated
 */
function ForumSetAllMessagesRead($FID = false)
{
	ForumSetReadForum($FID);
}
/**
 * @deprecated
 */
function ForumDeleteSubscribe($ID, &$strErr, &$strOk)
{
	global $USER;
	$ID = IntVal($ID);
	if (CForumSubscribe::CanUserDeleteSubscribe($ID, $USER->GetUserGroupArray(), $USER->GetID()))
	{
		CForumSubscribe::Delete($ID);
		return true;
	}
	else
	{
		$strErr = GetMessage("FSUBSC_NO_SPERMS").". \n";
	}
	return false;
}
/**
 * @deprecated
 */
function ForumInitParams()
{
	//	unset($_SESSION["FORUM"]);
	$UserLogin = "GUEST";
	$LastVisit = time() + CTimeZone::GetOffset();
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		if (!is_array($_SESSION["FORUM"]["USER"]) || $_SESSION["FORUM"]["USER"]["USER_ID"] != $GLOBALS["USER"]->GetID()):
			$_SESSION["FORUM"]["USER"] = CForumUser::GetByUSER_ID($GLOBALS["USER"]->GetID());
			if ($_SESSION["FORUM"]["USER"]):
				$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = MakeTimeStamp($_SESSION["FORUM"]["USER"]["LAST_VISIT"]);
			else:
				$_SESSION["FORUM"]["USER"] = array();
				$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = CForumNew::GetNowTime("timestamp");
			endif;
		elseif (empty($_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"])):
			$_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"] = CForumNew::GetNowTime("timestamp");
		endif;

		$arUser = $_SESSION["FORUM"]["USER"];
		$UserLogin = $GLOBALS["USER"]->GetLogin();
		$LastVisit = $_SESSION["FORUM"]["USER"]["LAST_VISIT_TIMESTAMP"];

		// if info for this user is not exist that info gets from DB
		if (!is_array($_SESSION["FORUM"][$UserLogin]) || intVal($_SESSION["FORUM"][$UserLogin][0]) <= 0)
		{
			$_SESSION["FORUM"][$UserLogin] = array();
			$db_res = CForumUser::GetListUserForumLastVisit(array(), array("USER_ID" => $GLOBALS["USER"]->GetID()));
			if ($db_res && $res = $db_res->Fetch()):
				do
				{
					$_SESSION["FORUM"][$UserLogin][intVal($res["FORUM_ID"])] = MakeTimeStamp($res["LAST_VISIT"]);
				}while ($res = $db_res->Fetch());
			endif;

			if (intVal($_SESSION["FORUM"][$UserLogin][0]) <= 0):
				$_SESSION["FORUM"][$UserLogin] = array();
				CForumUser::SetUserForumLastVisit($GLOBALS["USER"]->GetID(), 0, false);
				$db_res = CForumUser::GetListUserForumLastVisit(array(), array("USER_ID" => $GLOBALS["USER"]->GetID(), "FORUM_ID" => 0));
				if ($db_res && $res = $db_res->Fetch()):
					$_SESSION["FORUM"][$UserLogin][0] = MakeTimeStamp($res["LAST_VISIT"]);
				else:
					$_SESSION["FORUM"][$UserLogin][0] = $LastVisit;
				endif;
			endif;
		}

		// synhronize guest session with authorized user session
		if (isset($_SESSION["FORUM"]) && isset($_SESSION["FORUM"]["GUEST_TID"]) && !empty($_SESSION["FORUM"]["GUEST_TID"]))
		{
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				CForumTopic::SetReadLabelsNew($key, false, $val, array("UPDATE_TOPIC_VIEWS" => "N"));
			endforeach;
		}
		//		if (is_array($_SESSION["FORUM"]["GUEST"]) && (!empty($_SESSION["FORUM"]["GUEST"])))
		//		{
		//			foreach ($_SESSION["FORUM"]["GUEST"] as $key => $val)
		//			{
		//				if (intVal($val) > intVal($_SESSION["FORUM"][$UserLogin][intVal($key)]))
		//					$_SESSION["FORUM"][$UserLogin][intVal($key)] = intVal($val);
		//			}
		//		}
		unset($_SESSION["FORUM"]["GUEST_TID"]);
		unset($_SESSION["FORUM"]["GUEST"]);
	}
	else // If user is not authorized that get info from cookies only
	{
		if (!isset($_SESSION["FORUM"]["GUEST"]) || !is_array($_SESSION["FORUM"]["GUEST"]))
		{
			$forum_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_GUEST";
			if (isset($_COOKIE[$forum_cookie]) && $_COOKIE[$forum_cookie] <> '')
			{
				$arForum = explode("/", $_COOKIE[$forum_cookie]);
				if (is_array($arForum) && count($arForum) > 0)
				{
					foreach ($arForum as $forumInfo)
					{
						list($f, $lv) = explode("-", $forumInfo);
						$_SESSION["FORUM"]["GUEST"][intVal($f)] = intVal($lv);
					}
				}
			}
		}

		if (!isset($_SESSION["FORUM"]["GUEST"]) || !is_array($_SESSION["FORUM"]["GUEST"]) || (intVal($_SESSION["FORUM"]["GUEST"][0]) < 0))
		{
			$_SESSION["FORUM"]["GUEST"] = array();
			$_SESSION["FORUM"]["GUEST"][0] = CForumNew::GetNowTime();
		}
		// All geting info put in cookies
		if (COption::GetOptionString("forum", "USE_COOKIE", "N") == "Y"):
			$arCookie = array();
			foreach ($_SESSION["FORUM"]["GUEST"] as $key => $val):
				$arCookie[] = $key."-".$val;
			endforeach;
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_GUEST", implode("/", $arCookie), false, "/", false, false, "Y", false);
		endif;

		//		It need to save info about visited topics for GUEST in cookies
		if (!isset($_SESSION["FORUM"]["GUEST_TID"]) || !is_array($_SESSION["FORUM"]["GUEST_TID"]))
		{
			$_SESSION["FORUM"]["GUEST_TID"] = array();
			$topic_cookie = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_GUEST_TID";
			if (isset($_COOKIE[$topic_cookie]) && $_COOKIE[$topic_cookie] <> ''):
				$arTopic = explode("/", $_COOKIE[$topic_cookie]);
				if (is_array($arTopic) && count($arTopic) > 0):
					foreach ($arTopic as $topicInfo):
						list($f, $lv) = explode("-", $topicInfo);
						$_SESSION["FORUM"]["GUEST_TID"][intVal($f)] = intVal($lv);
					endforeach;
				endif;
			endif;
		}
	}
	// cleaning session date.
	if (is_array($_SESSION["FORUM"]))
	{
		foreach ($_SESSION["FORUM"] as $key => $val):
			if (substr($key, 0, strLen("LAST_VISIT_FORUM_")) == "LAST_VISIT_FORUM_"):
				unset($_SESSION["FORUM"][$key]);
			endif;
		endforeach;
	}
	// and put info in public variable
	if (is_array($_SESSION["FORUM"][$UserLogin])):
		foreach ($_SESSION["FORUM"][$UserLogin] as $key => $val):
			$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$key] = $val;
		endforeach;
	else:
		$_SESSION["FORUM"]["LAST_VISIT_FORUM_0"] = CForumNew::GetNowTime();
	endif;

	return $_SESSION;
}
/**
 * @deprecated
 */
function NewMessageForum($FID, $LAST_POST_DATE = false)
{
	if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
		ForumInitParams();

	$FID = intVal($FID);
	$LAST_VISIT = max($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], $_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID]);
	$LAST_POST_DATE = MakeTimeStamp($LAST_POST_DATE);

	if (intVal($LAST_POST_DATE) > 0 && $LAST_POST_DATE < $LAST_VISIT):
		"";
	elseif ($GLOBALS["USER"]->IsAuthorized()):
		$arFilter = array("FORUM_ID" => $FID, "RENEW" => $GLOBALS["USER"]->GetID());
		if (ForumCurrUserPermissions($FID) < "Q"):
			$arFilter["APPROVED"] = "Y";
		endif;
		$db_res = CForumTopic::GetListEx(array("ID" => "DESC"), $arFilter, false, 1);
		if ($db_res && $res = $db_res->Fetch()):
			return true;
		endif;
	else:
		$arFilter = array("FORUM_ID" => $FID);
		if (is_array($_SESSION["FORUM"]["GUEST_TID"]) && !empty($_SESSION["FORUM"]["GUEST_TID"])):
			$arFilter["RENEW_TOPIC"][0] = ConvertTimeStamp($LAST_VISIT, "FULL");
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				$arFilter["RENEW_TOPIC"][intVal($key)] = ConvertTimeStamp($val, "FULL");
			endforeach;
		else:
			$arFilter[">LAST_POST_DATE"] = ConvertTimeStamp($LAST_VISIT, "FULL");
		endif;
		if (ForumCurrUserPermissions($FID) < "Q"):
			$arFilter["APPROVED"] = "Y";
		endif;
		$db_res = CForumTopic::GetList(array(), $arFilter, false, 1);
		if ($db_res && $res = $db_res->Fetch()):
			return true;
		endif;
	endif;
	ForumInitParams();
	return false;
}
/**
 * @deprecated
 */
function NewMessageTopic($FID, $TID, $LAST_POST_DATE, $LAST_VISIT)
{
	if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
		ForumInitParams();
	$TID = intVal($TID);
	$LAST_POST_DATE = intVal(MakeTimeStamp($LAST_POST_DATE));
	$LAST_VISIT = intVal($GLOBALS["USER"]->IsAuthorized() ? MakeTimeStamp($LAST_VISIT) : $_SESSION["FORUM"]["GUEST_TID"][$TID]);
	$LAST_VISIT = max($LAST_VISIT, $_SESSION["FORUM"]["LAST_VISIT_FORUM_0"], intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID]));
	return ($LAST_POST_DATE > $LAST_VISIT);
}
/**
 * @deprecated
 */
function ForumSetReadForum($FID = false)
{
	$UserLogin = "GUEST";
	$timestamp = CForumNew::GetNowTime("timestamp");
	$FID = intVal($FID);

	if ($GLOBALS["USER"]->IsAuthorized()):
		$UserLogin = $GLOBALS["USER"]->GetLogin();
		CForumUser::SetUserForumLastVisit($GLOBALS["USER"]->GetID(), $FID, $timestamp);
	endif;

	if ($FID <= 0)
	{
		if (is_array($_SESSION["FORUM"])):
			foreach ($_SESSION["FORUM"] as $key => $val):
				if (substr($key, 0, strLen("LAST_VISIT_FORUM_")) == "LAST_VISIT_FORUM_"):
					unset($_SESSION["FORUM"][$key]);
				endif;
			endforeach;
		endif;
		unset($_SESSION["FORUM"][$UserLogin]);
	}
	$_SESSION["FORUM"][$UserLogin][$FID] = $timestamp;
	$_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID] = $timestamp;
	return ForumInitParams();
}
/**
 * @deprecated
 */
function ForumSetReadTopic($FID, $TID)
{
	CForumTopic::SetReadLabelsNew($TID);

	if (!$GLOBALS['USER']->IsAuthorized())
	{
		if (!isset($_SESSION["FORUM"]["GUEST_TID"]))
			ForumInitParams();
		$_SESSION["FORUM"]["GUEST_TID"][intVal($TID)] = CForumNew::GetNowTime();
		if (COption::GetOptionString("forum", "USE_COOKIE", "N") == "Y")
		{
			$arCookie = array();
			foreach ($_SESSION["FORUM"]["GUEST_TID"] as $key => $val):
				$arCookie[] = intVal($key)."-".intVal($val);
			endforeach;
			$GLOBALS["APPLICATION"]->set_cookie("FORUM_GUEST_TID", implode("/", $arCookie), false, "/", false, false, "Y", false);
		}
	}
}
/**
 * @deprecated
 */
function ForumSetLastVisit($forumId = false, $TID = false, $arAddParams = array())
{
	global $USER, $FID;
	// For custom components
	$FID = $forumId = intval($forumId === false ? $FID : $forumId);

	if ($USER->isAuthorized())
	{
		$GLOBALS["SHOW_FORUM_ICON"] = true; // out-of-date param
		$forumUser = \Bitrix\Forum\User::getById($USER->getID());
		$forumUser->setLastVisit();

		if (!is_array($_SESSION["FORUM"]["USER"]) || $_SESSION["FORUM"]["USER"]["USER_ID"] != $USER->getID())
		{
			$_SESSION["FORUM"]["USER"] = $forumUser->getData();
			$_SESSION["FORUM"]["SHOW_NAME"] = $_SESSION["FORUM"]["USER"]["SHOW_NAME"];
		}
	}

	ForumInitParams();

	if (IsModuleInstalled('statistic') && !empty($_SESSION["SESS_SEARCHER_ID"]))
	{
		CForumStat::RegisterUSER(array("SITE_ID" => SITE_ID, "FORUM_ID" => $forumId, "TOPIC_ID" => $TID));
	}

	return true;
}
/**
 * @deprecated
 */
function ForumGetFirstUnreadMessage($FID, $TID)
{
	global $USER, $DB;
	$TID = intVal($TID);
	if ($TID > 0 )
	{
		if (intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]) <= 0)
			ForumInitParams();
		$LastVisit = max(intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_0"]), intVal($_SESSION["FORUM"]["LAST_VISIT_FORUM_".$FID])); // client TZ

		if ($USER->IsAuthorized())
		{
			$db_res = CForumMessage::GetListEx(array("ID" => "ASC"),
				array("TOPIC_ID" => $TID, "USER_ID" => $USER->GetId(), ">NEW_MESSAGE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), $LastVisit)), 0, 1);
		}
		else
		{
			$LastVisit = max($LastVisit, intVal($_SESSION["FORUM"]["GUEST_TID"][$TID]));
			$db_res = CForumMessage::GetList(array("ID" => "ASC"),
				array("TOPIC_ID" => $TID, ">POST_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), $LastVisit)), 0, 1);
		}
		if ($db_res && $res = $db_res->Fetch())
			return $res["ID"];
	}
	return false;
}
function ForumAddDeferredScript($script)
{
	$url = CUtil::GetAdditionalFileURL($script);
	return "<script>BX.ready(function(){BX.loadScript(\"".$url."\");});</script>\n";
}
/*
GetMessage("FORUM_NO_MODULE");
*/

function CustomizeLHEForForum()
{
	?>
	<script>
		LHEButtons['Translit'].handler = function(pBut)
		{
			var but = pBut;
			var translit = function(textbody)
			{
				if (typeof but.pLEditor.bTranslited == 'undefined')
					but.pLEditor.bTranslited = false;

				var arStack = new Array();
				var i = 0;

				function bPushTag(str, p1, offset, s)
				{
					arStack.push(p1);
					return "\001";
				}

				function bPopTag(str, p1, offset, s)
				{
					return arStack.shift();
				}


				var r = new RegExp("(\\[[^\\]]*\\])", 'gi');
				textbody = textbody.replace(r, bPushTag);

				if ( but.pLEditor.bTranslited == false)
				{
					for (i=0; i<capitEngLettersReg.length; i++) textbody = textbody.replace(capitEngLettersReg[i], capitRusLetters[i]);
					for (i=0; i<smallEngLettersReg.length; i++) textbody = textbody.replace(smallEngLettersReg[i], smallRusLetters[i]);
					but.pLEditor.bTranslited = true;
				}
				else
				{
					for (i=0; i<capitRusLetters.length; i++) textbody = textbody.replace(capitRusLettersReg[i], capitEngLetters[i]);
					for (i=0; i<smallRusLetters.length; i++) textbody = textbody.replace(smallRusLettersReg[i], smallEngLetters[i]);
					but.pLEditor.bTranslited = false;
				}

				textbody = textbody.replace(new RegExp("\001", "g"), bPopTag);

				return textbody;
			}

			pBut.pLEditor.SaveContent();
			var content = translit(pBut.pLEditor.GetContent());

			BX.defer(function()
			{
				if (window.oLHE.sEditorMode == 'code')
					window.oLHE.SetContent(content);
				else
					window.oLHE.SetEditorContent(content);
			})();
		}
		LHEButtons['SmileList']['SetSmile'] = function(k, pList)
		{
			//pList.pLEditor.RestoreSelectionRange();
			var oSmile = pList.oSmiles[k];

			if (pList.pLEditor.sEditorMode == 'code') // In BB or in HTML
				pList.pLEditor.WrapWith(' ', ' ', oSmile.code);
			else // WYSIWYG
				pList.pLEditor.InsertHTML('<img id="' + pList.pLEditor.SetBxTag(false, {tag: "smile", params: oSmile}) + '" src="' + oSmile.path + '" title="' + oSmile.name + '"/>');

			if (pList.bOpened)
				pList.Close();
		};
		LHEButtons['SmileList']['parser']['obj']['UnParse'] = function(bxTag, pNode, pLEditor)
		{
			if (!bxTag.params || !bxTag.params.code)
				return '';
			return ' ' + bxTag.params.code + ' ';
		};
		LHEButtons['ForumVideo'] = {
			id : 'ForumInputVideo',
			src : '/bitrix/components/bitrix/forum/templates/.default/images/bbcode/font_video.gif',
			name : '<?=GetMessage("FR_VIDEO")?>',
			handler: function(pBut)
			{
				pBut.pLEditor.OpenDialog({id : 'ForumVideo', obj: false});
			},
			OnBeforeCreate: function(pLEditor, pBut)
			{
				// Disable in non BBCode mode in html
				pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
				return pBut;
			},
			parser: {
				name: 'forumvideo',
				obj: {
					Parse: function(sName, sContent, pLEditor)
					{
						sContent = sContent.replace(/\[VIDEO\s*?width=(\d+)\s*?height=(\d+)\s*\]((?:\s|\S)*?)\[\/VIDEO\]/ig, function(str, w, h, src)
						{
							var
								w = parseInt(w) || 400,
								h = parseInt(h) || 300,
								src = BX.util.trim(src);

							return '<img id="' + pLEditor.SetBxTag(false, {tag: "forumvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />';
						});
						return sContent;
					},
					UnParse: function(bxTag, pNode, pLEditor)
					{
						if (bxTag.tag == 'forumvideo')
						{
							return "[VIDEO WIDTH=" + pNode.arAttributes["width"] + " HEIGHT=" + pNode.arAttributes["height"] + "]" + bxTag.params.value + "[/VIDEO]";
						}
						return "";
					}
				}
			}
		}
		if (!LHEButtons['InputVideo'])
			LHEButtons['InputVideo'] = LHEButtons['ForumVideo'];

		window.LHEDailogs['ForumVideo'] = function(pObj)
		{
			var str = '<table width="100%"><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_forum_video_path"><b><?= GetMessage('FR_VIDEO_P')?>:</b></label></td>' +
				'<td class="lhe-dialog-param">' +
				'<input id="' + pObj.pLEditor.id + 'lhed_forum_video_path" value="" size="30"/>' +
				'</td>' +
				'</tr><tr>' +
				'<td></td>' +
				'<td style="padding: 0!important; font-size: 11px!important;"><?= GetMessageJS('FR_VIDEO_PATH_EXAMPLE')?></td>' +
				'</tr><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_forum_video_width">' + BX.message.ImageSizing + ':</label></td>' +
				'<td class="lhe-dialog-param">' +
				'<input id="' + pObj.pLEditor.id + 'lhed_forum_video_width" value="" size="4"/>' +
				' x ' +
				'<input id="' + pObj.pLEditor.id + 'lhed_forum_video_height" value="" size="4" />' +
				'</td>' +
				'</tr></table>';

			return {
				title: "<?= GetMessageJS('FR_VIDEO')?>",
				innerHTML : str,
				width: 480,
				OnLoad: function()
				{
					pObj.pPath = BX(pObj.pLEditor.id + "lhed_forum_video_path");
					pObj.pWidth = BX(pObj.pLEditor.id + "lhed_forum_video_width");
					pObj.pHeight = BX(pObj.pLEditor.id + "lhed_forum_video_height");

					pObj.pLEditor.focus(pObj.pPath);
				},
				OnSave: function()
				{
					pLEditor = window.oLHE;

					var
						src = BX.util.trim(pObj.pPath.value),
						w = parseInt(pObj.pWidth.value) || 400,
						h = parseInt(pObj.pHeight.value) || 300;

					if (src == "")
						return;

					if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
					{
						pLEditor.WrapWith("", "", "[VIDEO WIDTH=" + w + " HEIGHT=" + h + "]" + src + "[/VIDEO]");
					}
					else if(pLEditor.sEditorMode == 'html') // WYSIWYG
					{
						pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "forumvideo", params: {value : src}}) +
							'" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h +
							' title="' + BX.message.Video + ": " + src + '" />');
					}
				}
			};
		};
	</script>
	<?
}