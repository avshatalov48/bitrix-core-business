<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2009 Bitrix			 #
# https://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
use Bitrix\Main\Error;

IncludeModuleLangFile(__FILE__);

class CAllVote
{
	public static function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CAllVote<br>File: ".__FILE__;
	}

	public static function GetFilterOperation($key)
	{
		return CGroup::GetFilterOperation($key);
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		$aMsg = array();
		$ID = intval($ID);
		$arVote = array();
		if ($ID > 0):
			$db_res = CVote::GetByID($ID);
			if ($db_res && $res = $db_res->Fetch()):
				$arVote = $res;
			endif;
		endif;

		unset($arFields["ID"]);
		if (is_set($arFields, "CHANNEL_ID") || $ACTION == "ADD")
		{
			$arFields["CHANNEL_ID"] = intval($arFields["CHANNEL_ID"]);
			if ($arFields["CHANNEL_ID"] <= 0):
				$aMsg[] = array(
					"id" => "CHANNEL_ID",
					"text" => GetMessage("VOTE_EMPTY_CHANNEL_ID"));
			else:
				$rChannel = CVoteChannel::GetList('', '', array('ID' => intval($arFields['CHANNEL_ID'])));
				if (! ($rChannel && $arChannel = $rChannel->Fetch()))
				{
					$aMsg[] = array(
						"id" => "CHANNEL_ID",
						"text" => GetMessage("VOTE_WRONG_CHANNEL_ID"));
				}
			endif;
		}

		if (is_set($arFields, "C_SORT")) $arFields["C_SORT"] = intval($arFields["C_SORT"]);
		if (is_set($arFields, "ACTIVE") || $ACTION == "ADD") $arFields["ACTIVE"] = ($arFields["ACTIVE"] == "N" ? "N" : "Y");

		unset($arFields["TIMESTAMP_X"]);
		$date_start = false;
		if (is_set($arFields, "DATE_START") || $ACTION == "ADD")
		{
			$arFields["DATE_START"] = trim($arFields["DATE_START"]);
			$date_start = MakeTimeStamp($arFields["DATE_START"]);
			if (!$date_start):
				$aMsg[] = array(
					"id" => "DATE_START",
					"text" => GetMessage("VOTE_WRONG_DATE_START"));
			endif;
		}

		if (is_set($arFields, "DATE_END") || $ACTION == "ADD")
		{
			$arFields["DATE_END"] = trim($arFields["DATE_END"]);
			if ($arFields["DATE_END"] == ''):
				if ($date_start != false):
					$date_end = $date_start + 2592000;
					$arFields["DATE_END"] = GetTime($date_end, "FULL");
				else:
					$date_end = 1924984799; // '31.12.2030 23:59:59'
					$arFields["DATE_END"] = GetTime($date_end, "FULL");
				endif;
			else:
				$date_end = MakeTimeStamp($arFields["DATE_END"]);
			endif;
			if (!$date_end):
				$aMsg[] = array(
					"id" => "DATE_END",
					"text" => GetMessage("VOTE_WRONG_DATE_END"));
			elseif ($date_start >= $date_end && !empty($arFields["DATE_START"])):
				$aMsg[] = array(
					"id" => "DATE_END",
					"text" => GetMessage("VOTE_WRONG_DATE_TILL"));
			endif;
		}
		if (empty($aMsg) && (is_set($arFields, "DATE_START") || is_set($arFields, "DATE_END") || is_set($arFields, "CHANNEL_ID") || is_set($arFields, "ACTIVE")))
		{
			$vid = 0;
			if ($ACTION == "ADD" && $arFields["ACTIVE"] == "Y")
			{
				$vid = CVote::WrongDateInterval(0, $arFields["DATE_START"], $arFields["DATE_END"], $arFields["CHANNEL_ID"]);
			}
			elseif ($ACTION != "ADD" && !(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y"))
			{
				$res = array(
					"DATE_START" => (is_set($arFields, "DATE_START") ? $arFields["DATE_START"] : false),
					"DATE_END" => (is_set($arFields, "DATE_END") ? $arFields["DATE_END"] : false),
					"CHANNEL_ID" => (is_set($arFields, "CHANNEL_ID") ? $arFields["CHANNEL_ID"] : false));
				$vid = CVote::WrongDateInterval($ID, $res["DATE_START"], $res["DATE_END"], $res["CHANNEL_ID"]);
			}
			if (intval($vid) > 0):
				$aMsg[] = array(
					"id" => "DATE_START",
					"text" => str_replace("#ID#", $vid, GetMessage("VOTE_WRONG_INTERVAL")));
			endif;
		}
		if (is_set($arFields, "IMAGE_ID") && $arFields["IMAGE_ID"]["name"] == '' && $arFields["IMAGE_ID"]["del"] == '')
		{
			unset($arFields["IMAGE_ID"]);
		}
		elseif (is_set($arFields, "IMAGE_ID"))
		{
			if ($str = CFile::CheckImageFile($arFields["IMAGE_ID"])):
				$aMsg[] = array(
					"id" => "IMAGE_ID",
					"text" => $str);
			else:
				$arFields["IMAGE_ID"]["MODULE_ID"] = "vote";
				if (!empty($arVote)):
					$arFields["IMAGE_ID"]["old_file"] = $arVote["IMAGE_ID"];
				endif;
			endif;
		}

		if (is_set($arFields, "COUNTER")) $arFields["COUNTER"] = intval($arFields["COUNTER"]);
		if (is_set($arFields, "TITLE")) $arFields["TITLE"] = trim($arFields["TITLE"]);
		if (is_set($arFields, "DESCRIPTION")) $arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]);
		if (is_set($arFields, "DESCRIPTION_TYPE") || $ACTION == "ADD") $arFields["DESCRIPTION_TYPE"] = ($arFields["DESCRIPTION_TYPE"] == "html" ? "html" : "text");

		if (is_set($arFields, "EVENT1")) $arFields["EVENT1"] = trim($arFields["EVENT1"]);
		if (is_set($arFields, "EVENT2")) $arFields["EVENT2"] = trim($arFields["EVENT2"]);
		if (is_set($arFields, "EVENT3")) $arFields["EVENT3"] = trim($arFields["EVENT3"]);
		if (is_set($arFields, "UNIQUE_TYPE")) $arFields["UNIQUE_TYPE"] = intval($arFields["UNIQUE_TYPE"]);
		if (is_set($arFields, "OPTIONS"))
		{

			$arFields["OPTIONS"] = intval($arFields["OPTIONS"]);
		}

		if (is_set($arFields, "DELAY") && array_key_exists("DELAY_TYPE", $arFields))
		{
			$arFields["DELAY"] = intval($arFields["DELAY"]);
			$type = in_array($arFields["DELAY_TYPE"], array("S", "M", "H", "D")) ? $arFields["DELAY_TYPE"] : "D";
			$typeMultiplier = array(
				"S" => 1,
				"M" => 60,
				"H" => 3600,
				"D" => 86400
			);
			$arFields["KEEP_IP_SEC"] = $arFields["DELAY"] * $typeMultiplier[$type];
		}
		else if (array_key_exists("KEEP_IP_SEC", $arFields) || $ACTION == "ADD")
		{
			$arFields["KEEP_IP_SEC"] = intval($arFields["KEEP_IP_SEC"]);
		}
		unset ($arFields["DELAY"]);
		unset ($arFields["DELAY_TYPE"]);

		if (CVote::IsOldVersion() != "Y")
		{
			unset($arFields["TEMPLATE"]);
			unset($arFields["RESULT_TEMPLATE"]);
		}

		if (is_set($arFields, "TEMPLATE")) $arFields["TEMPLATE"] = trim($arFields["TEMPLATE"]);
		if (is_set($arFields, "RESULT_TEMPLATE")) $arFields["RESULT_TEMPLATE"] = trim($arFields["RESULT_TEMPLATE"]);
		if (is_set($arFields, "NOTIFY")) $arFields["NOTIFY"] = (in_array($arFields["NOTIFY"], array("Y", "N", "I")) ? $arFields["NOTIFY"] : "N");
		if (is_set($arFields, "REQUIRED")) $arFields["REQUIRED"] = ($arFields["REQUIRED"] == "Y" ? "Y" : "N");
		if (is_set($arFields, "AUTHOR_ID")) $arFields["AUTHOR_ID"] = intval($arFields["AUTHOR_ID"]);

		if(!empty($aMsg))
		{
			global $APPLICATION;
			$e = new CAdminException(array_reverse($aMsg));
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	/**
	 * @deprecated 18.5.1
	 * @param array $arFields
	 * @param bool $strUploadDir
	 * @return array|bool|int
	 * @throws Exception
	 */
	public static function Add($arFields, $strUploadDir = false)
	{
		$result = \Bitrix\Vote\VoteTable::add($arFields);
		if (!$result->isSuccess())
		{
			$aMsg = [];
			$errCollection = $result->getErrorCollection();
			for ($errCollection->rewind(); $errCollection->valid(); $errCollection->next())
			{
				/** @var Error $error */
				$error = $errCollection->current();
				$aMsg[] = ["id" => $error->getCode(), "text" => $error->getMessage()];
			}
			if (!empty($aMsg))
			{
				global $APPLICATION;
				$APPLICATION->ThrowException((new CAdminException(array_reverse($aMsg))));
			}
			return false;
		}
		return $result->getId();
	}

	/**
	 * @deprecated 18.5.1
	 * @param $ID
	 * @param $arFields
	 * @param bool $strUploadDir
	 * @return bool|int
	 */
	public static function Update($ID, $arFields, $strUploadDir = false)
	{
		$result = \Bitrix\Vote\VoteTable::update($ID, $arFields);
		if (!$result->isSuccess())
		{
			$aMsg = [];
			$errCollection = $result->getErrorCollection();
			for ($errCollection->rewind(); $errCollection->valid(); $errCollection->next())
			{
				/** @var Error $error */
				$error = $errCollection->current();
				$aMsg[] = ["id" => $error->getCode(), "text" => $error->getMessage()];
			}
			if (!empty($aMsg))
			{
				global $APPLICATION;
				$APPLICATION->ThrowException((new CAdminException(array_reverse($aMsg))));
			}
			return false;
		}
		return $result->getId();
	}

	public static function Delete($ID)
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: Delete<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;

		/***************** Event onBeforeVoteDelete *************************/
		foreach (GetModuleEvents("vote", "onBeforeVoteDelete", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array(&$ID)) === false)
				return false;
		/***************** /Event ******************************************/

		@set_time_limit(1000);
		$DB->StartTransaction();

		// delete questions
		CVoteQuestion::Delete(false, $ID);
		\Bitrix\Vote\AttachTable::deleteByFilter(array("OBJECT_ID" => $ID));
		// delete vote images
		$strSql = "SELECT IMAGE_ID FROM b_vote WHERE ID = ".$ID." AND IMAGE_ID > 0";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($zr = $z->Fetch()) CFile::Delete($zr["IMAGE_ID"]);

		// delete vote events
		$DB->Query("DELETE FROM b_vote_event WHERE VOTE_ID='$ID'", false, $err_mess.__LINE__);
		// delete vote
		$res = $DB->Query("DELETE FROM b_vote WHERE ID='$ID'", false, $err_mess.__LINE__);
		$DB->Commit();
		/***************** Event onAfterVoteDelete *************************/
		foreach (GetModuleEvents("vote", "onAfterVoteDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));
		/***************** /Event ******************************************/
		return $res;
	}

	public static function Reset($ID)
	{
		\Bitrix\Vote\Event::resetStatistic($ID);
		unset($GLOBALS["VOTE_CACHE_VOTING"][$ID]);
		if (array_key_exists("VOTE", $_SESSION) && array_key_exists("VOTES", $_SESSION["VOTE"]))
			unset($_SESSION["VOTE"]["VOTES"][$ID]);
		return true;
	}

	public static function Copy($ID)
	{
		global $DB;
		$err_mess = (CVote::err_mess())."<br>Function: Copy<br>Line: ";
		$ID = intval($ID);
		if ($ID <= 0):
			return false;
		endif;
		$rCurrentVote = CVote::GetByID($ID);
		if (!$arCurrentVote = $rCurrentVote->Fetch())
			return false;
		unset($arCurrentVote["ID"]);
		$arCurrentVote['ACTIVE'] = "N";

		$newImageId = false;
		if (intval($arCurrentVote['IMAGE_ID'] > 0))
		{
			$imageId = $arCurrentVote['IMAGE_ID'];
			$newImageId = CFile::CopyFile($imageId);
			$arCurrentVote["IMAGE_ID"] = NULL;
		}
		$newID = CVote::Add($arCurrentVote);
		if ($newID === false)
			return false;
		$DB->Update("b_vote", array("COUNTER"=>"0"), "WHERE ID=".$newID, $err_mess.__LINE__);
		if ($newImageId)
		{
			$DB->Update("b_vote", array("IMAGE_ID"=>$newImageId), "WHERE ID=".$newID, $err_mess.__LINE__);
		}

		$state = true;
		$rQuestions = CVoteQuestion::GetList($ID);
		while ($arQuestion = $rQuestions->Fetch())
		{
			$state = $state && ( CVoteQuestion::Copy($arQuestion['ID'], $newID) !== false);
		}

		if ($state == true)
			return $newID;
		else return $state;
	}

	public static function IsOldVersion()
	{
		$res = "N";
		$arr = GetTemplateList("RV");
		if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
		else
		{
			$arr = GetTemplateList("SV");
			if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
			else
			{
				$arr = GetTemplateList("RQ");
				if (is_array($arr) && count($arr["reference"])>0) $res = "Y";
			}
		}
		return $res;
	}

	public static function GetByID($ID)
	{
		$ID = intval($ID);
		return CVote::GetList("s_id", "desc", array("ID" => $ID));
	}

	public static function GetByIDEx($ID)
	{
		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		if (!isset($GLOBALS["VOTE_CACHE"]["VOTE"][$ID]))
		{
			global $CACHE_MANAGER;
			if (!!VOTE_CACHE_TIME && $CACHE_MANAGER->Read(VOTE_CACHE_TIME, $ID, "b_vote"))
			{
				$GLOBALS["VOTE_CACHE"]["VOTE"][$ID] = $CACHE_MANAGER->Get($ID);
			}
			else
			{
				$db_res = CVote::GetListEx(array("ID" => "ASC"),  array("ID" => $ID));
				if ($db_res && ($res = $db_res->Fetch()))
				{
					$GLOBALS["VOTE_CACHE"]["VOTE"][$ID] = $res;
					if (!!VOTE_CACHE_TIME)
						$CACHE_MANAGER->Set($ID, $res);
				}
			}
		}
		$db_res = new CDBResult();
		$db_res->InitFromArray(array($GLOBALS["VOTE_CACHE"]["VOTE"][$ID]));
		return $db_res;
	}

	public static function UserAlreadyVote($voteId, $VOTE_USER_ID, $UNIQUE_TYPE, $delay, $USER_ID = false)
	{
		global $DB, $USER;
		$err_mess = (CAllVote::err_mess())."<br>Function: UserAlreadyVote<br>Line: ";
		$voteId = intval($voteId);
		$UNIQUE_TYPE = intval($UNIQUE_TYPE);
		$VOTE_USER_ID = intval($VOTE_USER_ID);
		$USER_ID = intval($USER_ID);

		if ($voteId <= 0 || $UNIQUE_TYPE <= 0)
			return false;

		//One session
		if (($UNIQUE_TYPE & 1) && IsModuleInstalled('statistic') && array_key_exists($voteId, $_SESSION["VOTE"]["VOTES"]))
			return 1;


		$return = array();
		$arSqlSearch = array();
		$arSqlSelect = array("VE.ID");

		//Same cookie
		if ($UNIQUE_TYPE & 2 && ($VOTE_USER_ID > 0))
		{
			$arSqlSelect[] = "VE.VOTE_USER_ID";
			$arSqlSearch[] = "VE.VOTE_USER_ID='".$VOTE_USER_ID."'";
		}

		// Same IP
		if ($UNIQUE_TYPE & 4)
		{
			$tmp = \CVote::CheckVotingIP($voteId, $_SERVER["REMOTE_ADDR"], $delay, array("RETURN_SEARCH_ARRAY" => "Y"));
			$arSqlSelect[] = $tmp["select"];
			$arSqlSearch[] = $tmp["search"];
		}

		// Same ID
		if ($UNIQUE_TYPE & 8)
		{
			if ($USER_ID <= 0 || $USER_ID == $USER->GetID() && isset($_SESSION["VOTE"]["VOTES"][$voteId]))
			{
				$return[] = 8;
			}
			else
			{
				$arSqlSelect[] = "VU.AUTH_USER_ID";
				$arSqlSearch[] = "VU.AUTH_USER_ID=".$USER_ID;
			}
			// Register date
			if (($UNIQUE_TYPE & 16) &&
				($arUser = \CUser::GetByID($USER_ID)->fetch()) &&
				is_array($arUser) &&
				($userRegister = MakeTimeStamp($arUser['DATE_REGISTER'])) &&
				($vote = \CVote::GetByID($voteId)->Fetch()) &&
				is_array($vote) &&
				($voteStart = MakeTimeStamp($vote['DATE_START'])) &&
				($userRegister > $voteStart)
			)
			{
				$return[] = 16;
			}
		}

		if (!empty($arSqlSearch))
		{
			$db_res = $DB->Query("SELECT ".implode(",", $arSqlSelect)."
				FROM b_vote_event VE
				LEFT JOIN b_vote_user VU ON (VE.VOTE_USER_ID = VU.ID)
				WHERE VE.VOTE_ID=".$voteId." AND ((".implode(") OR (", $arSqlSearch)."))", false, $err_mess.__LINE__);
			while ($res = $db_res->Fetch())
			{
				if ($USER_ID > 0 && $USER_ID == $USER->GetID())
					$_SESSION["VOTE"]["VOTES"][$voteId] = $res["ID"];
				// $UNIQUE_TYPE & 2
				if (isset($res["VOTE_USER_ID"]) && $res["VOTE_USER_ID"] == $VOTE_USER_ID)
				{
					$return[] = 2;
				}
				//$UNIQUE_TYPE & 4
				if (isset($res["IP"]) && $res["IP"] == $_SERVER["REMOTE_ADDR"]
					&& ($delay <= 0 || !isset($res["KEEP_IP_SEC"]) || $delay > $res["KEEP_IP_SEC"]))
				{
					$return[] = 4;
				}
				// $UNIQUE_TYPE & 8
				if (isset($res["AUTH_USER_ID"]) && $res["AUTH_USER_ID"] == $USER_ID)
				{
					$return[] = 8;
				}
			}
		}
		$return = empty($return) ? 0 : min($return);
		return ($return > 0 ? $return : false);
	}

	public static function UserGroupPermission($CHANNEL_ID)
	{
		global $USER;
		return CVoteChannel::GetGroupPermission($CHANNEL_ID, $USER->GetUserGroupArray());
	}

	public static function SetVoteUserID()
	{
		return \Bitrix\Vote\User::getCurrent()->setVotedUserId();
	}

	public static function UpdateVoteUserID($VOTE_USER_ID)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: UpdateVoteUserID<br>Line: ";

		$VOTE_USER_ID = intval($VOTE_USER_ID);
		$arFields = array(
			"DATE_LAST"		=> $DB->GetNowFunction(),
			"COUNTER"		=> "COUNTER+1"
			);
		return $DB->Update("b_vote_user", $arFields, "WHERE ID='".$VOTE_USER_ID."'", $err_mess.__LINE__);
	}

	public static function keepVoting()
	{
		global $USER;
		/** @var $r \Bitrix\Main\HttpRequest */
		$r = \Bitrix\Main\Context::getCurrent()->getRequest();
		$request = array_merge($r->getQueryList()->toArray(), $r->getPostList()->toArray());

		$PUBLIC_VOTE_ID = intval($request["PUBLIC_VOTE_ID"]);
		$errorCollection = new \Bitrix\Main\ErrorCollection();

		try
		{
			if (empty($request["vote"]) || $PUBLIC_VOTE_ID <= 0 || !check_bitrix_sessid())
				throw new \Bitrix\Main\ArgumentException(GetMessage("VOTE_NOT_FOUND"), "bad_params");

			$vote = new \Bitrix\Vote\Vote($PUBLIC_VOTE_ID);

			if (\CVote::UserGroupPermission($vote["CHANNEL_ID"]) < 2)
				throw new \Bitrix\Main\AccessDeniedException();

			$channel = $vote->getChannel();
			if ($channel["USE_CAPTCHA"] == "Y" && !$USER->IsAuthorized())
			{
				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
				$cpt = new CCaptcha();
				if ((!empty($request["captcha_code"]) && !$cpt->CheckCodeCrypt($request["captcha_word"], $request["captcha_code"])) ||
					empty($request["captcha_code"]) && !$cpt->CheckCode($request["captcha_word"], 0))
				{
					$GLOBALS["BAD_CAPTCHA"] = "Y";
					throw new \Bitrix\Main\ArgumentException(GetMessage("VOTE_BAD_CAPTCHA"), "captcha");
				}
			}
			if (!$vote->voteFor($request))
				$errorCollection->add($vote->getErrors());
			else
				$GLOBALS["VOTING_ID"] = $vote->getId();
		}
		catch (\Exception $e)
		{
			$errorCollection->add(array(new \Bitrix\Main\Error($e->getMessage(), $e->getCode())));
		}

		if ($errorCollection->isEmpty())
		{
			$GLOBALS["VOTING_OK"] = "Y";
			return true;
		}
		global $APPLICATION, $VOTING_OK;
		$VOTING_OK = "N";
		$m = [];
		for ($errorCollection->rewind(); $errorCollection->valid(); $errorCollection->next())
			$m[] = $errorCollection->current()->getMessage();

		$APPLICATION->ThrowException(implode("", $m), "CVote::KeepVoting");

		return false;
	}

	public static function GetNextSort($CHANNEL_ID)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: GetNextSort<br>Line: ";
		$CHANNEL_ID = intval($CHANNEL_ID);
		$strSql = "SELECT max(C_SORT) MAX_SORT FROM b_vote WHERE CHANNEL_ID='$CHANNEL_ID'";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	public static function WrongDateInterval($CURRENT_VOTE_ID, $DATE_START, $DATE_END, $CHANNEL_ID)
	{
		global $DB;
		$err_mess = (CAllVote::err_mess())."<br>Function: WrongDateInterval<br>Line: ";
		$CURRENT_VOTE_ID = intval($CURRENT_VOTE_ID);
		$CURRENT_VOTE_ID = ($CURRENT_VOTE_ID > 0 ? $CURRENT_VOTE_ID : false);
		$CHANNEL_ID = intval($CHANNEL_ID);
		$CHANNEL_ID = ($CHANNEL_ID > 0 ? $CHANNEL_ID : false);
		$DATE_START = ($DATE_START == false ? false : (trim($DATE_START) == '' ? false : trim($DATE_START)));
		$DATE_END = ($DATE_END == false ? false : (trim($DATE_END) == '' ? false : trim($DATE_END)));
		
		if($CURRENT_VOTE_ID == false && $CHANNEL_ID == false)
		{
			return 0;
		}
		elseif($CHANNEL_ID > 0)
		{
			$db_res = CVoteChannel::GetByID($CHANNEL_ID);
			if($db_res && $res = $db_res->Fetch())
				if($res["VOTE_SINGLE"] != "Y")
					return 0;
		}
		
		$st = ($DATE_START == false ? "VV.DATE_START" : $DB->CharToDateFunction($DATE_START, "FULL"));
		$en = ($DATE_END == false ? "VV.DATE_END" : $DB->CharToDateFunction($DATE_END, "FULL"));
		if($CURRENT_VOTE_ID <= 0)
		{
			if($DATE_START == false)
				$st = $DB->CurrentTimeFunction();
			if($DATE_END == false)
				$en = $DB->CharToDateFunction(ConvertTimeStamp(1924984799, "FULL"), "FULL"); // '31.12.2030 23:59:59'
		}

		$strSql = "
			SELECT V.ID
			FROM b_vote V 
			".($CURRENT_VOTE_ID > 0 ? 
			"LEFT JOIN b_vote VV ON (VV.ID = ".$CURRENT_VOTE_ID.") " : "")."
			INNER JOIN b_vote_channel VC ON (V.CHANNEL_ID = VC.ID AND VC.VOTE_SINGLE = 'Y')
			WHERE
				V.CHANNEL_ID=".($CHANNEL_ID == false ? "VV.CHANNEL_ID" : $CHANNEL_ID)." AND 
				V.ACTIVE='Y' AND 
				".($CURRENT_VOTE_ID > 0 ? 
				"V.ID<>'".$CURRENT_VOTE_ID."' AND " : "")."
				(
					(".$st." between V.DATE_START and V.DATE_END) OR
					(".$en." between V.DATE_START and V.DATE_END) OR
					(V.DATE_START between ".$st." and ".$en.") OR
					(V.DATE_END between ".$st." and ".$en.")
				)";
		$db_res = $DB->Query($strSql, false, $err_mess.__LINE__);
		if($db_res && $res = $db_res->Fetch())
			return intval($res["ID"]);

		return 0;
	}
}

class _CVoteDBResult extends CDBResult
{
	public function __construct($res, $params = array())
	{
		parent::__construct($res);
	}
	function Fetch()
	{
		if($res = parent::Fetch())
		{
			if ($res["LAMP"] == "yellow" && !empty($res["CHANNEL_ID"]))
			{
				$res["LAMP"] = ($res["ID"] == CVote::GetActiveVoteId($res["CHANNEL_ID"]) ? "green" : "red");
			}
		}
		return $res;
	}
}
?>