<?php
#############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2007 Bitrix			#
# http://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
#############################################
class CVoteCacheManager
{
	public static $types = array(
		"C" => "vote_form_channel_",
		"V" => "vote_form_vote_",
		"Q" => "vote_form_question_",
		"A" => "vote_form_answer_"
	);

	public static $cacheKey = "/#SITE_ID#/voting.cache/";

	public function cachePath($site_id)
	{
		$site_id = (!empty($site_id) ? $site_id : "bx");
		return str_replace("#SITE_ID#", $site_id, self::$cacheKey);
	}

	function __construct()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();

		AddEventHandler("vote", "onAfterVoteChannelAdd", Array(&$this, "OnAfterVoteChannelChange"));
		AddEventHandler("vote", "onAfterVoteChannelUpdate", Array(&$this, "OnAfterVoteChannelChange"));
		AddEventHandler("vote", "onAfterChannelDelete", Array(&$this, "OnAfterVoteChannelChange"));

		AddEventHandler("vote", "onAfterVoteAdd", array(&$this, "OnAfterVoteChange"));
		AddEventHandler("vote", "onAfterVoteUpdate", array(&$this, "OnAfterVoteChange"));
		AddEventHandler("vote", "onAfterVoteDelete", array(&$this, "OnAfterVoteChange"));

		$eventManager->addEventHandler("vote", "\\Bitrix\\Vote\\Vote::OnAfterAdd", array($this, "OnVoteChange"));
		$eventManager->addEventHandler("vote", "\\Bitrix\\Vote\\Vote::OnAfterUpdate", array($this, "OnVoteChange"));
		$eventManager->addEventHandler("vote", "\\Bitrix\\Vote\\Vote::OnAfterDelete", array($this, "OnVoteChange"));

		AddEventHandler("vote", "onVoteReset", array(&$this, "OnAfterVoteChange"));
		AddEventHandler("vote", "onAfterVoting", array(&$this, "OnAfterVoteChange"));

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			AddEventHandler("vote", "onAfterVoteQuestionAdd", array(&$this, "OnAfterVoteQuestionAdd"));
			AddEventHandler("vote", "onBeforeVoteQuestionUpdate", array(&$this, "OnBeforeVoteQuestionUpdate"));
			AddEventHandler("vote", "onAfterVoteQuestionUpdate", array(&$this, "OnAfterVoteQuestionUpdate"));
			AddEventHandler("vote", "onAfterVoteQuestionDelete", array(&$this, "OnAfterVoteQuestionDelete"));

			AddEventHandler("vote", "onAfterVoteAnswerAdd", array(&$this, "OnAfterVoteAnswerAdd"));
			AddEventHandler("vote", "onBeforeVoteAnswerUpdate", array(&$this, "OnBeforeVoteAnswerUpdate"));
			AddEventHandler("vote", "onAfterVoteAnswerUpdate", array(&$this, "OnAfterVoteAnswerUpdate"));
			AddEventHandler("vote", "onAfterVoteAnswerDelete", array(&$this, "OnAfterVoteAnswerDelete"));
		}
	}

	public static function SetTag($path, $tag, $ID = 0)
	{
		global $CACHE_MANAGER;
		if (! defined("BX_COMP_MANAGED_CACHE"))
			return false;
		$CACHE_MANAGER->StartTagCache($path);
		$tags = is_array($tag) ? $tag : array($tag => $ID);
		foreach ($tags as $tag => $ID)
		{
			if (array_key_exists($tag, self::$types))
			{
				$ID = is_array($ID) ? $ID : array($ID);
				foreach ($ID as $i)
					$CACHE_MANAGER->RegisterTag(self::$types[$tag].$i);
			}
			else
			{
				$CACHE_MANAGER->RegisterTag($tag);
			}
		}
		$CACHE_MANAGER->EndTagCache();
		return true;
	}

	public static function ClearTag($type, $ID=0)
	{
		if (! defined("BX_COMP_MANAGED_CACHE"))
			return false;
		global $CACHE_MANAGER;
		if (array_key_exists($type, self::$types))
			$CACHE_MANAGER->ClearByTag(self::$types[$type].$ID);
		else
			$CACHE_MANAGER->ClearByTag($type);
		return true;
	}

	function OnAfterVoteChannelChange($ID, $arFields = array())
	{
		self::ClearTag("C", $ID);
		// drop permissions
		if (VOTE_CACHE_TIME !== false):
			global $CACHE_MANAGER;
			$CACHE_MANAGER->CleanDir("b_vote_channel");
			if (empty($arFields) || array_key_exists("GROUP_ID", $arFields))
				$CACHE_MANAGER->CleanDir("b_vote_perm");
			if (empty($arFields) || !empty($arFields["SITE"]))
				$CACHE_MANAGER->CleanDir("b_vote_channel_2_site");
			$CACHE_MANAGER->CleanDir("b_vote");
		endif;
	}
	function OnVoteChange(\Bitrix\Main\Entity\Event $event)
	{
		$data = $event->getParameter("primary");
		$this->OnAfterVoteChange($data["ID"]);
	}
	function OnAfterVoteChange($ID)
	{
		self::ClearTag("V", $ID);
		if (VOTE_CACHE_TIME !== false):
			global $CACHE_MANAGER;
			$CACHE_MANAGER->CleanDir("b_vote");
			unset($GLOBALS["VOTE_CACHE"]["VOTE"][$ID]);
		endif;
	}

	function OnAfterVoteQuestionAdd($ID, $arFields)
	{
		self::ClearTag("V", $arFields['VOTE_ID']);
	}

	function OnBeforeVoteQuestionUpdate(&$ID, &$arFields)
	{
		if (array_key_exists("VOTE_ID", $arFields))
		{
			$db_res = CVoteQuestion::GetByID($ID);
			if ($db_res && ($res = $db_res->Fetch()))
				self::ClearTag("V", $res["VOTE_ID"]);
		}
	}

	function OnAfterVoteQuestionUpdate($ID, $arFields)
	{
		if (array_key_exists("VOTE_ID", $arFields))
		{
			self::ClearTag("V", $arFields["VOTE_ID"]);
		}
		else
		{
			$db_res = CVoteQuestion::GetByID($ID);
			if ($db_res && ($res = $db_res->Fetch()))
				self::ClearTag("V", $res["VOTE_ID"]);
		}
		self::ClearTag("Q", $ID);
	}

	function OnAfterVoteQuestionDelete($ID, $VOTE_ID)
	{
		self::ClearTag("V", $VOTE_ID);
	}

	function OnAfterVoteAnswerAdd($ID, $arFields)
	{
		self::ClearTag("Q", $arFields["QUESTION_ID"]);
	}

	function OnBeforeVoteAnswerUpdate($ID, $arFields)
	{
		if (array_key_exists("QUESTION_ID", $arFields))
		{
			global $DB;
			$res = $DB->Query("SELECT QUESTION_ID FROM b_vote_answer WHERE ID=".$ID, false, "File:".__FILE__." Line: ".__LINE__);
			if ($row = $res->Fetch())
				self::ClearTag("Q", $row["QUESTION_ID"]);
		}
	}

	function OnAfterVoteAnswerUpdate($ID, $arFields)
	{
		if (array_key_exists("QUESTION_ID", $arFields))
		{
			self::ClearTag("Q", $arFields["QUESTION_ID"]);
		}
		else
		{
			global $DB;
			$db_res = $DB->Query("SELECT QUESTION_ID FROM b_vote_answer WHERE ID=".$ID, false, "File:".__FILE__." Line: ".__LINE__);
			if ($res = $db_res->Fetch())
				self::ClearTag("Q", $res["QUESTION_ID"]);
		}
	}

	function OnAfterVoteAnswerDelete($ID, $QUESTION_ID, $VOTE_ID)
	{
		if ($QUESTION_ID != false)
			self::ClearTag("Q", $QUESTION_ID);
		if ($VOTE_ID != false)
			self::ClearTag("V", $VOTE_ID);
	}

	function onAfterVoting($VOTE_ID, $EVENT_ID)
	{
		unset($GLOBALS["VOTE_CACHE_VOTING"][$VOTE_ID]);
		unset($GLOBALS["VOTE_CACHE"]["VOTE"][$VOTE_ID]);
		CVoteCacheManager::ClearTag("V", $VOTE_ID);
	}
}