<?
global $DB, $MESS, $APPLICATION, $voteCache;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/vote_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/classes/".strtolower($DB->type)."/channel.php");
IncludeModuleLangFile(__FILE__);

if (!defined("VOTE_CACHE_TIME"))
	define("VOTE_CACHE_TIME", 3600);

define("VOTE_DEFAULT_DIAGRAM_TYPE", "histogram");

$GLOBALS["VOTE_CACHE"] = array(
	"CHANNEL" => array(),
	"VOTE" => array(),
	"QUESTION" => array());
$GLOBALS["VOTE_CACHE_VOTING"] = array();
$GLOBALS["aVotePermissions"] = array(
	"reference_id" => array(0, 1, 2, /*3, */4),
	"reference" => array(GetMessage("VOTE_DENIED"), GetMessage("VOTE_READ"), GetMessage("VOTE_WRITE"), /*GetMessage("VOTE_EDIT_MY_OWN"), */GetMessage("VOTE_EDIT")));
$_SESSION["VOTE"] = (is_array($_SESSION["VOTE"]) ? $_SESSION["VOTE"] : array());
$_SESSION["VOTE"]["VOTES"] = (is_array($_SESSION["VOTE"]["VOTES"]) ? $_SESSION["VOTE"]["VOTES"] : array());

CModule::AddAutoloadClasses("vote", array(
	"CVoteAnswer" => "classes/".strtolower($DB->type)."/answer.php",
	"CVoteEvent" => "classes/".strtolower($DB->type)."/event.php",
	"CVoteQuestion" => "classes/".strtolower($DB->type)."/question.php", 
	"CVoteUser" => "classes/".strtolower($DB->type)."/user.php", 
	"CVote" => "classes/".strtolower($DB->type)."/vote.php",
	"CVoteCacheManager" => "classes/general/functions.php",
	"CVoteNotifySchema" => "classes/general/im.php",
	"bitrix\\vote\\answertable" => "lib/answer.php",
	"bitrix\\vote\\answer" => "lib/answer.php",
	"bitrix\\vote\\attachtable" => "lib/attach.php",
	"bitrix\\vote\\attach" => "lib/attach.php",
	"bitrix\\vote\\attachment\\attach" => "lib/attachment/attach.php",
	"bitrix\\vote\\attachment\\blogpostconnector" => "lib/attachment/blogpostconnector.php",
	"bitrix\\vote\\attachment\\connector" => "lib/attachment/connector.php",
	"bitrix\\vote\\attachment\\controller" => "lib/attachment/controller.php",
	"bitrix\\vote\\attachment\\defaultconnector" => "lib/attachment/defaultconnector.php",
	"bitrix\\vote\\attachment\\forummessageconnector" => "lib/attachment/forummessageconnector.php",
	"bitrix\\vote\\attachment\\storable" => "lib/attachment/storage.php",
	"bitrix\\vote\\base\\baseobject" => "lib/base/baseobject.php",
	"bitrix\\vote\\base\\controller" => "lib/base/controller.php",
	"bitrix\\vote\\base\\diag" => "lib/base/diag.php",
	"bitrix\\vote\\channeltable" => "lib/channel.php",
	"bitrix\\vote\\channelgrouptable" => "lib/channel.php",
	"bitrix\\vote\\channelsitetable" => "lib/channel.php",
	"bitrix\\vote\\channel" => "lib/channel.php",
	"bitrix\\vote\\dbresult" => "lib/dbresult.php",
	"bitrix\\vote\\voteeventtable" => "lib/event.php",
	"bitrix\\vote\\eventtable" => "lib/event.php",
	"bitrix\\vote\\eventquestiontable" => "lib/event.php",
	"bitrix\\vote\\eventanswertable" => "lib/event.php",
	"bitrix\\vote\\event" => "lib/event.php",
	"bitrix\\vote\\questiontable" => "lib/question.php",
	"bitrix\\vote\\question" => "lib/question.php",
	"bitrix\\vote\\uf\\manager" => "lib/uf/manager.php",
	"bitrix\\vote\\uf\\voteusertype" => "lib/uf/voteusertype.php",
	"bitrix\\vote\\usertable" => "lib/user.php",
	"bitrix\\vote\\voteeventquestiontable" => "lib/user.php",
	"bitrix\\vote\\voteeventanswertable" => "lib/user.php",
	"bitrix\\vote\\voteeventanswer" => "lib/user.php",
	"bitrix\\vote\\user" => "lib/user.php",
	"bitrix\\vote\\votetable" => "lib/vote.php",
	"bitrix\\vote\\vote" => "lib/vote.php"
));

$voteCache = new CVoteCacheManager();

function VoteVoteEditFromArray($CHANNEL_ID, $VOTE_ID = false, $arFields = array(), $params = array())
{
	$CHANNEL_ID = intval($CHANNEL_ID);
	if ($CHANNEL_ID <= 0 || empty($arFields)):
		return false;
	elseif (CVote::UserGroupPermission($CHANNEL_ID) <= 0):
		return false;
	endif;
	$aMsg = array();
	$params = (is_array($params) ? $params : array());
	$params["UNIQUE_TYPE"] = (is_set($params, "UNIQUE_TYPE") ? intval($params["UNIQUE_TYPE"]) : 20);

	$arVote = array();
	$arQuestions = array();

	$arFieldsQuestions = array();
	$arFieldsVote = array(
		"CHANNEL_ID" => $CHANNEL_ID,
		"AUTHOR_ID" => $GLOBALS["USER"]->GetID(),
		"UNIQUE_TYPE" => $params["UNIQUE_TYPE"], 
		"DELAY" => $params["DELAY"] ?: 10,
		"DELAY_TYPE" => $params['DELAY_TYPE'] ?: "D");
	if (!empty($arFields["DATE_START"]))
		$arFieldsVote["DATE_START"] = $arFields["DATE_START"];
	if (!empty($arFields["DATE_END"]))
		$arFieldsVote["DATE_END"] = $arFields["DATE_END"];
	if (!empty($arFields["TITLE"]))
		$arFieldsVote["TITLE"] = $arFields["TITLE"];
	if (isset($arFields["ACTIVE"]))
		$arFieldsVote["ACTIVE"] = $arFields["ACTIVE"];
	if (isset($arFields["NOTIFY"]))
		$arFieldsVote["NOTIFY"] = $arFields["NOTIFY"];
	if (isset($arFields["URL"]))
		$arFieldsVote["URL"] = $arFields["URL"];
/************** Fatal errors ***************************************/
	if (!CVote::CheckFields("UPDATE", $arFieldsVote)):
		$e = $GLOBALS['APPLICATION']->GetException();
		$aMsg[] = array(
			"id" => "VOTE_ID", 
			"text" => $e->GetString());
	elseif (intval($VOTE_ID) > 0):
		$db_res = CVote::GetByID($VOTE_ID);
		if (!($db_res && $res = $db_res->Fetch())):
			$aMsg[] = array(
				"id" => "VOTE_ID", 
				"text" => GetMessage("VOTE_VOTE_NOT_FOUND", array("#ID#", $VOTE_ID)));
		elseif ($res["CHANNEL_ID"] != $CHANNEL_ID):
			$aMsg[] = array(
				"id" => "CHANNEL_ID", 
				"text" => GetMessage("VOTE_CHANNEL_ID_ERR"));
		else:
			$arVote = $res;
			$db_res = CVoteQuestion::GetList($arVote["ID"], $by = "s_id", $order = "asc", array(), $is_filtered);
			if ($db_res && $res = $db_res->Fetch()):
				do { $arQuestions[$res["ID"]] = $res + array("ANSWERS" => array()); } while ($res = $db_res->Fetch());
			endif;
			$db_res = CVoteAnswer::GetListEx(array("ID" => "ASC"), array("VOTE_ID" => $arVote["ID"]));
			if ($db_res && $res = $db_res->Fetch()):
				do { $arQuestions[$res["QUESTION_ID"]]["ANSWERS"][$res["ID"]] = $res; } while ($res = $db_res->Fetch());
			endif;
		endif;
	endif;
	if (!empty($aMsg)):
		$e = new CAdminException(array_reverse($aMsg));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	endif;
/************** Fatal errors/***************************************/
	if (!empty($arFieldsVote["TITLE"]) && !empty($arVote["TITLE"]))
	{
		$q = reset($arQuestions);
		if ($arVote["TITLE"] == substr($q["QUESTION"], 0, strlen($arVote["TITLE"])))
			unset($arFieldsVote["TITLE"]);
	}
/************** Check Data *****************************************/
	// Questions
	$arFields["QUESTIONS"] = (is_array($arFields["QUESTIONS"]) ? $arFields["QUESTIONS"] : array());
	$iQuestions = 0;
	foreach ($arFields["QUESTIONS"] as $key => $arQuestion)
	{
		if ($arQuestion["DEL"] != "Y")
		{
			$arQuestion["ID"] = intval($arQuestion["ID"]);
			$arQuestion = array(
				"ID" => $arQuestion["ID"] > 0 && is_set($arQuestions, $arQuestion["ID"]) ? $arQuestion["ID"] : false,
				"QUESTION" => trim($arQuestion["QUESTION"]),
				"QUESTION_TYPE" => trim($arQuestion["QUESTION_TYPE"]),
				"ANSWERS" => (is_array($arQuestion["ANSWERS"]) ? $arQuestion["ANSWERS"] : array()));

			$arAnswers = ($arQuestion["ID"] > 0 ? $arQuestions[$arQuestion["ID"]]["ANSWERS"] : array());
			foreach ($arQuestion["ANSWERS"] as $keya => $arAnswer)
			{
				$arAnswer["ID"] = intval($arAnswer["ID"]);
				$arAnswer["MESSAGE"] = trim($arAnswer["MESSAGE"]);
				if (!empty($arAnswer["MESSAGE"]) && $arAnswer["DEL"] != "Y")
				{
					$arQuestion["ANSWERS"][$keya] = array(
						"MESSAGE" => $arAnswer["MESSAGE"],
						"MESSAGE_TYPE" => $arAnswer["MESSAGE_TYPE"],
						"FIELD_TYPE" => $arAnswer["FIELD_TYPE"]);
					if ($arAnswer["ID"] > 0 && is_set($arAnswers, $arAnswer["ID"]))
					{
						$arQuestion["ANSWERS"][$keya]["ID"] = $arAnswer["ID"];
						unset($arAnswers[$arAnswer["ID"]]);
					}
				}
			}
		}

		if ($arQuestion["DEL"] == "Y" || empty($arQuestion["QUESTION"]) || empty($arQuestion["ANSWERS"]))
		{
			if ($arQuestion["DEL"] != "Y" && !(empty($arQuestion["QUESTION"]) && empty($arQuestion["ANSWERS"])))
			{
				$aMsg[] = array(
					"id" => "QUESTION_".$key,
					"text" => (empty($arQuestion["QUESTION"]) ?
						GetMessage("VOTE_QUESTION_EMPTY", array("#NUMBER#" => $key)) :
						GetMessage("VOTE_ANSWERS_EMPTY", array("#QUESTION#" => $arQuestion["QUESTION"]))));
			}
			continue;
		}
		if ($arQuestion["ID"] > 0)
		{
			unset($arQuestions[$arQuestion["ID"]]);
			foreach($arAnswers as $arAnswer)
			{
				$arQuestion["ANSWERS"][] = ($arAnswer + array("DEL" => "Y"));
			}
		}
		$iQuestions++;
		$arFieldsQuestions[$key] = $arQuestion;
	}
	foreach ($arQuestions as $arQuestion)
	{
		$arFieldsQuestions[] = ($arQuestion + array("DEL" => "Y"));
	}

	if (!empty($aMsg)):
		$e = new CAdminException(array_reverse($aMsg));
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	elseif (empty($arFieldsQuestions) && $VOTE_ID <= 0):
			return true;
	elseif ($params["bOnlyCheck"] == "Y"):
		return true;
	endif;
/************** Check Data/*****************************************/
/************** Main actions with return ***************************/
	if (empty($arFieldsVote["TITLE"]))
	{
		$q = reset($arFieldsQuestions);
		$arFieldsVote["TITLE"] = null;
		do {
			if ($q["DEL"] != "Y")
			{
				$arFieldsVote["TITLE"] = $q["QUESTION"];
				break;
			}
		} while ($q = next($arFieldsQuestions));
		reset($arFieldsQuestions);
	}
	if (empty($arVote))
	{
		$arVote["ID"] = intval(CVote::Add($arFieldsVote));
	}
	else
	{
		CVote::Update($VOTE_ID, $arFieldsVote);
	}

	if ($iQuestions > 0 && $arVote["ID"] > 0)
	{
		$iQuestions = 0;
		foreach ($arFieldsQuestions as $arQuestion)
		{
			if ($arQuestion["DEL"] == "Y"):
				CVoteQuestion::Delete($arQuestion["ID"]);
				continue;
			elseif ($arQuestion["ID"] > 0):
				$arQuestion["C_SORT"] = ($iQuestions + 1) * 10;
				CVoteQuestion::Update($arQuestion["ID"], $arQuestion);
			else:
				$arQuestion["C_SORT"] = ($iQuestions + 1) * 10;
				$arQuestion["VOTE_ID"] = $arVote["ID"];
				$arQuestion["ID"] = intval(CVoteQuestion::Add($arQuestion));
				if ($arQuestion["ID"] <= 0):
					continue;
				endif;
			endif;
			$iQuestions++;
			$iAnswers = 0;
			foreach ($arQuestion["ANSWERS"] as $arAnswer)
			{
				if ($arAnswer["DEL"] == "Y"):
					CVoteAnswer::Delete($arAnswer["ID"]);
					continue;
				endif;

				if ($arAnswer["ID"] > 0):
					$arAnswer["C_SORT"] = ($iAnswers + 1)* 10;
					CVoteAnswer::Update($arAnswer["ID"], $arAnswer);
				else:
					$arAnswer["QUESTION_ID"] = $arQuestion["ID"];
					$arAnswer["C_SORT"] = ($iAnswers + 1)* 10;
					$arAnswer["ID"] = intval(CVoteAnswer::Add($arAnswer));
					if ($arAnswer["ID"] <= 0):
						continue;
					endif;
				endif;

				$iAnswers++;
			}
			if ($iAnswers <= 0)
			{
				CVoteQuestion::Delete($arQuestion["ID"]);
				$iQuestions--;
			}
		}
	}

	if (intval($arVote["ID"]) <= 0)
	{
		return false;
	}
	elseif ($iQuestions <= 0)
	{
		CVote::Delete($arVote["ID"]);
		return 0;
	}
	return $arVote["ID"];
/************** Actions/********************************************/
/*	$arFields = array(
		"ID" => 345, 
		"TITLE" => "test", 
		"...", 
		"QUESTIONS" => array(
			array(
				"ID" => 348, 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 340, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 350,
						"DEL" => "Y",  
						"MESSAGE" => "test")
					)
				), 
			array(
				"ID" => 351, 
				"DEL" => "Y", 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					array(
						"ID" => 478,
						"DEL" => "Y",  
						"MESSAGE" => "test")
					)
				), 
			array(
				"ID" => 0, 
				"QUESTION" => "test", 
				"ANSWERS" => array(
					array(
						"ID" => 0, 
						"MESSAGE" => "test"), 
					)
				), 
			)
		);
*/
	
	
}
?>