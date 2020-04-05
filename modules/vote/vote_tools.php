<?
global $VOTE_CACHE_VOTING;
$VOTE_CACHE_VOTING = Array();

function GetAnswerTypeList()
{
	$arr = array(
		"reference_id" => array(0,1,2,3,4,5),
		"reference" => array("radio", "checkbox", "dropdown", "multiselect", "text", "textarea")
		);
	return $arr;
}

function GetVoteDiagramArray()
{
	$object =& CVoteDiagramType::getInstance();
	return $object->arType;
}

function GetVoteDiagramList()
{
	$object =& CVoteDiagramType::getInstance();

	return Array(
		"reference_id" => array_keys($object->arType),
		"reference" => array_values($object->arType)
		);
}

// vote data
function GetVoteDataByID($VOTE_ID, &$arChannel, &$arVote, &$arQuestions, &$arAnswers, &$arDropDown, &$arMultiSelect, &$arGroupAnswers, $arAddParams = "N")
{
	$VOTE_ID = intval($VOTE_ID);
	$arChannel = array();
	$arVote = array();
	$arQuestions = array();
	$arAnswers = array();
	$arDropDown = array();
	$arMultiSelect = array();
	$arAddParams = (is_array($arAddParams) ? $arAddParams : array("bGetMemoStat" => $arAddParams));

	$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID] = (is_array($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]) ? $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID] : array());

	if (empty($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]))
	{
		$db_res = CVote::GetByIDEx($VOTE_ID);
		if (!($db_res && $arVote = $db_res->GetNext()))
		{
			return false;
		}

		foreach ($arVote as $key => $res)
		{
			if (strpos($key, "CHANNEL_") === 0)
			{
				$arChannel[substr($key, 8)] = $res;
			}
			elseif (strpos($key, "~CHANNEL_") === 0)
			{
				$arChannel["~".substr($key, 9)] = $res;
			}
		}
		$by = "s_c_sort"; $order = "asc";
		$db_res = CVoteQuestion::GetList($VOTE_ID, $by, $order, array("ACTIVE" => "Y"), $is_filtered);
		while ($res = $db_res->GetNext())
		{
			$arQuestions[$res["ID"]] = $res + array("ANSWERS" => array());
		}
		if (!empty($arQuestions))
		{
			$db_res = CVoteAnswer::GetListEx(
				array("C_SORT" => "ASC"),
				array("VOTE_ID" => $VOTE_ID, "ACTIVE" => "Y", "@QUESTION_ID" => array_keys($arQuestions)));
			while ($res = $db_res->GetNext())
			{
				$arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ID"]] = $res;

				$arAnswers[$res["QUESTION_ID"]][] = $res;

				switch ($res["FIELD_TYPE"]) // dropdown and multiselect and text inputs
				{
					case 2:
						$arDropDown[$res["QUESTION_ID"]] = (is_array($arDropDown[$res["QUESTION_ID"]]) ? $arDropDown[$res["QUESTION_ID"]] :
							array("reference" => array(), "reference_id" => array(), "~reference" => array()));
						$arDropDown[$res["QUESTION_ID"]]["reference"][] = $res["MESSAGE"];
						$arDropDown[$res["QUESTION_ID"]]["~reference"][] = $res["~MESSAGE"];
						$arDropDown[$res["QUESTION_ID"]]["reference_id"][] = $res["ID"];
					break;
					case 3:
						$arMultiSelect[$res["QUESTION_ID"]] = (is_array($arMultiSelect[$res["QUESTION_ID"]]) ? $arMultiSelect[$res["QUESTION_ID"]] :
							array("reference" => array(), "reference_id" => array(), "~reference" => array()));
						$arMultiSelect[$res["QUESTION_ID"]]["reference"][] = $res["MESSAGE"];
						$arMultiSelect[$res["QUESTION_ID"]]["~reference"][] = $res["~MESSAGE"];
						$arMultiSelect[$res["QUESTION_ID"]]["reference_id"][] = $res["ID"];
					break;
				}
			}
			$event_id = intval($arAddParams["bRestoreVotedData"] == "Y" && !!$_SESSION["VOTE"]["VOTES"][$VOTE_ID] ?
				$_SESSION["VOTE"]["VOTES"][$VOTE_ID] : 0);
			$db_res = CVoteEvent::GetUserAnswerStat($VOTE_ID,
				array("bGetMemoStat" => "N", "bGetEventResults" => $event_id));
			if ($db_res && ($res = $db_res->Fetch()))
			{
				do
				{
					if (isset($arQuestions[$res["QUESTION_ID"]]) && is_array($arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ANSWER_ID"]]) && is_array($res))
					{
						$arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ANSWER_ID"]] += $res;
						if ($event_id > 0 && !empty($res["RESTORED_ANSWER_ID"]))
						{
							switch ($arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ANSWER_ID"]]["FIELD_TYPE"]):
								case 0: // radio
								case 2: // dropdown list
									$fieldName = ($arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ANSWER_ID"]]["FIELD_TYPE"] == 0 ?
										"vote_radio_" : "vote_dropdown_").$res["QUESTION_ID"];
									$_REQUEST[$fieldName] = $res["RESTORED_ANSWER_ID"];
									break;
								case 1: // checkbox
								case 3: // multiselect list
									$fieldName = ($arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ANSWER_ID"]]["FIELD_TYPE"] == 1 ?
										"vote_checkbox_" : "vote_multiselect_").$res["QUESTION_ID"];
									$_REQUEST[$fieldName] = (is_array($_REQUEST[$fieldName]) ? $_REQUEST[$fieldName] : array());
									$_REQUEST[$fieldName][] = $res["ANSWER_ID"];
									break;
								case 4: // field
								case 5: // text
									// do not restored
									break;
							endswitch;
						}
					}
				} while ($res = $db_res->Fetch());
			}
		}

		reset($arChannel);
		reset($arVote);
		reset($arQuestions);
		reset($arDropDown);
		reset($arMultiSelect);
		reset($arAnswers);

		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID] = array(
			"V" => $arVote,
			"C" => $arChannel,
			"QA" => array(
				"Q" => $arQuestions,
				"A" => $arAnswers,
				"M" => $arMultiSelect,
				"D" => $arDropDown,
				"G" => array(),
				"GA" => "N"
			)
		);
	}

	if ($arAddParams["bGetMemoStat"] == "Y" && $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["GA"] == "N")
	{
		$db_res = CVoteEvent::GetUserAnswerStat($VOTE_ID, array("bGetMemoStat" => "Y"));
		while ($res = $db_res->GetNext(true, false))
		{
			$arGroupAnswers[$res['ANSWER_ID']][] = $res;
		}
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["G"] = $arGroupAnswers;
		$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["GA"] = "Y";
	}

	$arVote = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["V"];
	$arChannel = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["C"];
	$arQuestions =	$GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["Q"];
	$arAnswers = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["A"];
	$arMultiSelect = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["M"];
	$arDropDown = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["D"];
	$arGroupAnswers = $GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]["QA"]["G"];
	return $arVote["ID"];
}

// return vote id for channel sid with check permissions and ACTIVE vote
function GetCurrentVote($GROUP_SID, $site_id=SITE_ID, $access=1)
{
	$z = CVoteChannel::GetList($by, $order, array("SID"=>$GROUP_SID, "SID_EXACT_MATCH"=>"Y", "SITE"=>$site_id, "ACTIVE"=>"Y"), $is_filtered);
	if ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);
		if (intval($perm)>=$access)
		{
			$v = CVote::GetList($by, $order, array("CHANNEL_ID"=>$zr["ID"], "LAMP"=>"green"), $is_filtered);
			if ($vr = $v->Fetch()) return $vr["ID"];
		}
	}
	return 0;
}

// return PREIOUS vote id for channel sid with check permissions and ACTIVE vote
function GetPrevVote($GROUP_SID, $level=1, $site_id=SITE_ID, $access=1)
{
	$VOTE_ID = 0;
	$z = CVoteChannel::GetList($by, $order, array("SID"=>$GROUP_SID, "SID_EXACT_MATCH"=>"Y", "SITE"=>$site_id, "ACTIVE"=>"Y"), $is_filtered);
	if ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);
		if (intval($perm)>=$access)
		{
			$v = CVote::GetList(($by = "s_date_start"), ($order = "desc"), array("CHANNEL_ID"=>$zr["ID"], "LAMP"=>"red"), $is_filtered);
			$i = 0;
			while ($vr=$v->Fetch())
			{
				$i++;
				if ($level==$i) 
				{
					$VOTE_ID = $vr["ID"];
					break;
				}
			}
		}
	}
	return intval($VOTE_ID);
}

// return votes list id for channel sid with check permissions and ACTIVE vote
function GetVoteList($GROUP_SID = "", $params = array(), $site_id = SITE_ID)
{
	$strSqlOrder = (is_string($params) ? $params : "ORDER BY C.C_SORT, C.ID, V.C_SORT, V.DATE_START desc");
	$params = (is_array($params) ? $params : array());
	if (array_key_exists("order", $params))
		$strSqlOrder = $params["order"];
	$arFilter["SITE"] = (array_key_exists("SITE_ID", $params)  ? $params["SITE_ID"] : (
		array_key_exists("siteId", $params)  ? $params["siteId"] : $site_id
	));

	if (is_array($GROUP_SID) && !empty($GROUP_SID))
	{
		$arr = array();
		foreach ($GROUP_SID as $v)
		{
			if (!empty($v))
				$arr[] = $v;
		}
		if (!empty($arr))
			$arFilter["CHANNEL"] = $arr;
	}
	elseif (!empty($GROUP_SID))
	{
		$arFilter["CHANNEL"] = $GROUP_SID;
	}
	$z = CVote::GetPublicList($arFilter, $strSqlOrder, $params);
	return $z;
}

// return true if user already vote on this vote
function IsUserVoted($voteId)
{
	return \Bitrix\Vote\User::getCurrent()->isVotedFor($voteId);
}

// return random unvoted vote id for user whith check permissions
function GetAnyAccessibleVote($site_id=SITE_ID, $channel_id=null)
{
	$arParams = array("ACTIVE"=>"Y","SITE"=>$site_id);

	if ($channel_id !== null)
	{
		$arParams['SID'] = $channel_id;
		$arParams['SID_EXACT_MATCH'] = 'Y';
	}

	$z = CVoteChannel::GetList($by="s_c_sort", $order="asc", $arParams, $is_filtered);
	$arResult = array();

	while ($zr = $z->Fetch())
	{
		$perm = CVoteChannel::GetGroupPermission($zr["ID"]);

		if (intval($perm)>=2)
		{
			$v = CVote::GetList($by, $order, array("CHANNEL_ID"=>$zr["ID"], "LAMP"=>"green"), $is_filtered);
			while ($vr = $v->Fetch()) 
			{
				if (!(IsUserVoted($vr['ID']))) $arResult[] = $vr['ID'];
			}
		}
	}

	if (sizeof($arResult) > 0)
		return array_rand(array_flip($arResult));

	return false;
}


/********************************************************************
				Functions for old templates
/*******************************************************************/
function GetTemplateList($type="SV", $path="xxx")
{
	$arReferenceId = array();
	$arReference = array();
	if ($path=="xxx")
	{
		if ($type=="SV")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH");
		elseif ($type=="RV")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_VOTE");
		elseif ($type=="RQ")
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_QUESTION");
	}
	if (is_dir($_SERVER["DOCUMENT_ROOT"].$path))
	{
		$handle=@opendir($_SERVER["DOCUMENT_ROOT"].$path);
		if($handle)
		{
			while (false!==($fname = readdir($handle)))
			{
				if (is_file($_SERVER["DOCUMENT_ROOT"].$path.$fname) && $fname!="." && $fname!="..")
				{
					$arReferenceId[] = $fname;
					$arReference[] = $fname;
				}
			}
			closedir($handle);
		}
	}
	$arr = array("reference" => $arReference,"reference_id" => $arReferenceId);
	return $arr;
}

function arrAnswersSort(&$arr, $order="desc")
{
	$count = count($arr);
	for ($key1=0; $key1<$count; $key1++)
	{
		for ($key2=0; $key2<$count; $key2++)
		{
			$sort1 = intval($arr[$key1]["COUNTER"]);
			$sort2 = intval($arr[$key2]["COUNTER"]);
			if ($order=="asc")
			{
				if ($sort1<$sort2)
				{
					$arr_tmp = $arr[$key1];
					$arr[$key1] = $arr[$key2];
					$arr[$key2] = $arr_tmp;
				}
			}
			else
			{
				if ($sort1>$sort2)
				{
					$arr_tmp = $arr[$key1];
					$arr[$key1] = $arr[$key2];
					$arr[$key2] = $arr_tmp;
				}
			}
		}
	}
}

// return current vote form for channel
function ShowCurrentVote($GROUP_SID, $site_id=SITE_ID)
{
	$CURRENT_VOTE_ID = GetCurrentVote($GROUP_SID, $site_id, 2);
	if (intval($CURRENT_VOTE_ID)>0) ShowVote($CURRENT_VOTE_ID);
}
// return previous vote results
function ShowPrevVoteResults($GROUP_SID, $level=1, $site_id=SITE_ID)
{
	$PREV_VOTE_ID = GetPrevVote($GROUP_SID, $level, $site_id);
	if (intval($PREV_VOTE_ID)>0) ShowVoteResults($PREV_VOTE_ID);
}
// return current vote results
function ShowCurrentVoteResults($GROUP_SID, $site_id=SITE_ID)
{
	$CURRENT_VOTE_ID = GetCurrentVote($GROUP_SID,  $site_id);
	if (intval($CURRENT_VOTE_ID)>0) ShowVoteResults($CURRENT_VOTE_ID);
}

// return current vote form with check permissions
function ShowVote($VOTE_ID, $template1="")
{
	global $VOTING_LAMP, $VOTING_OK, $USER_ALREADY_VOTE, $USER_GROUP_PERMISSION, $APPLICATION;

	$VOTING_LAMP = ($VOTING_LAMP == "green") ? $VOTING_LAMP : "red";
	$VOTING_OK = ($VOTING_OK == "Y") ? $VOTING_OK : "N";
	$USER_ALREADY_VOTE = ($USER_ALREADY_VOTE == "Y") ? $USER_ALREADY_VOTE : "N";
	$USER_GROUP_PERMISSION = intval($USER_GROUP_PERMISSION);
	if ($USER_GROUP_PERMISSION > 2) $USER_GROUP_PERMISSION = 0;

	$VOTE_ID = GetVoteDataByID($VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N");
	if (intval($VOTE_ID)>0)
	{
		$perm = CVoteChannel::GetGroupPermission($arChannel["ID"]);
		/***** for old pre-component templates **********/
		$GLOBALS["VOTE_PERMISSION"] = $perm;
		/***** /old *************************************/
		if (intval($perm)>=2)
		{
			$template = (strlen($arVote["TEMPLATE"])<=0) ? "default.php" : $arVote["TEMPLATE"];
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
			IncludeModuleLangFile(__FILE__);
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH");
			if (strlen($template1)>0) $template = $template1;

			if ($APPLICATION->GetShowIncludeAreas())
			{
				$arIcons = Array();
				if (CModule::IncludeModule("fileman"))
				{
					$arIcons[] = Array(
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($path.$template),
								"SRC" => "/bitrix/images/vote/panel/edit_template.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_TEMPLATE")
							);
					$arrUrl = parse_url($_SERVER["REQUEST_URI"]);
					$arIcons[] = Array(
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($arrUrl["path"]),
								"SRC" => "/bitrix/images/vote/panel/edit_file.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_HANDLER")
							);
				}
				$arIcons[] = Array(
							"URL" => "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$VOTE_ID,
							"SRC" => "/bitrix/images/vote/panel/edit_vote.gif",
							"ALT" => GetMessage("VOTE_PUBLIC_ICON_SETTINGS")
						);
				echo $APPLICATION->IncludeStringBefore($arIcons);
			}
			$template = Rel2Abs('/', $template);
			include($_SERVER["DOCUMENT_ROOT"].$path.$template);
			if ($APPLICATION->GetShowIncludeAreas())
			{
				echo $APPLICATION->IncludeStringAfter();
			}
		}
	}
}
// return current vote results with check permissions
function ShowVoteResults($VOTE_ID, $template1="")
{
	global $APPLICATION;
	$VOTE_ID = GetVoteDataByID($VOTE_ID, $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "Y");
	if (intval($VOTE_ID)>0)
	{
		/***** for old pre-component templates **********/
		global $VOTE_PERMISSION;
		$VOTE_PERMISSION = CVote::UserGroupPermission($arChannel["ID"]);
		/***** /old *************************************/

		$perm = CVoteChannel::GetGroupPermission($arChannel["ID"]);
		if (intval($perm)>=1)
		{
			$template = (strlen($arVote["RESULT_TEMPLATE"])<=0) ? "default.php" : $arVote["RESULT_TEMPLATE"];
			require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
			IncludeModuleLangFile(__FILE__);
			$path = COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_VOTE");
			if (strlen($template1)>0) $template = $template1;
			if ($APPLICATION->GetShowIncludeAreas())
			{
				$arIcons = Array();
				if (CModule::IncludeModule("fileman"))
				{
					$arIcons[] =
							Array(
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($path.$template),
								"SRC" => "/bitrix/images/vote/panel/edit_template.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_TEMPLATE")
							);
					$arrUrl = parse_url($_SERVER["REQUEST_URI"]);
					$arIcons[] =
							Array(
								"URL" => "/bitrix/admin/fileman_file_edit.php?lang=".LANGUAGE_ID."&site=".SITE_ID."&full_src=Y&path=". urlencode($arrUrl["path"]),
								"SRC" => "/bitrix/images/vote/panel/edit_file.gif",
								"ALT" => GetMessage("VOTE_PUBLIC_ICON_HANDLER")
							);
				}
				$arIcons[] =
						Array(
							"URL" => "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$VOTE_ID,
							"SRC" => "/bitrix/images/vote/panel/edit_vote.gif",
							"ALT" => GetMessage("VOTE_PUBLIC_ICON_SETTINGS")
						);
				echo $APPLICATION->IncludeStringBefore($arIcons);
			}
			$template = Rel2Abs('/', $template);
			include($_SERVER["DOCUMENT_ROOT"].$path.$template);
			if ($APPLICATION->GetShowIncludeAreas())
			{
				echo $APPLICATION->IncludeStringAfter();
			}
		}
	}
}

function fill_arc($start, $end, $color)
{
	global $diameter, $centerX, $centerY, $im, $radius;
	$radius = $diameter/2;
	imagearc($im, $centerX, $centerY, $diameter, $diameter, $start, $end+1, $color);
	imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start)) * $radius, $centerY + sin(deg2rad($start)) * $radius, $color);
	imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end)) * $radius, $centerY + sin(deg2rad($end)) * $radius, $color);
	$x = $centerX + $radius * 0.5 * cos(deg2rad($start+($end-$start)/2));
	$y = $centerY + $radius * 0.5 * sin(deg2rad($start+($end-$start)/2));
	imagefill ($im, $x, $y, $color);
}

function DecRGBColor($hex, &$dec1, &$dec2, &$dec3)
{
	if (substr($hex,0,1)!="#") $hex = "#".$hex;
	$dec1 = hexdec(substr($hex,1,2));
	$dec2 = hexdec(substr($hex,3,2));
	$dec3 = hexdec(substr($hex,5,2));
}

function DecColor($hex)
{
	if (substr($hex,0,1)!="#") $hex = "#".$hex;
	$dec = hexdec(substr($hex,1,6));
	return intval($dec);
}

function HexColor($dec)
{
	$hex = sprintf("%06X",$dec); 
	return $hex;
}

function GetNextColor(&$color, &$current_color, $total, $start_color="0000CC", $end_color="FFFFCC")
{
	if (substr($start_color,0,1)=="#") $start_color = substr($start_color,1,6);
	if (substr($end_color,0,1)=="#") $end_color = substr($end_color,1,6);
	if (substr($current_color,0,1)=="#") $current_color = substr($current_color,1,6);
	if (strlen($current_color)<=0) $color = "#".$start_color;
	else
	{
		$step = round((hexdec($end_color)-hexdec($start_color))/$total);
		if (intval($step)<=0) $step = "1500";
		$dec = DecColor($current_color)+intval($step);
		if ($dec<hexdec($start_color)) $dec = $start_color;
		elseif ($dec>hexdec($end_color)) $dec = $end_color;
		elseif ($dec>hexdec("FFFFFF")) $dec = "000000"; 
		else $dec = HexColor($dec);
		$color = "#".$dec;
	}
	$current_color = $color;
}