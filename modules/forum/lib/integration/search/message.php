<?php


namespace Bitrix\Forum\Integration\Search;


use Bitrix\Forum\Permission;

class Message
{
	public static function index(\Bitrix\Forum\Forum $forum, \Bitrix\Forum\Topic $topic, array $message)
	{
		if (!\Bitrix\Main\Loader::includeModule("search"))
		{
			return; 
		}

		if (\Bitrix\Main\Config\Option::get("forum", "FILTER", "Y") == "Y" && $message["POST_MESSAGE_FILTER"] !== null)
		{
			$message["POST_MESSAGE"] = $message["POST_MESSAGE_FILTER"];
		}

		$arParams = [
			"PERMISSION" => [1],
			"SITE" => $forum->getSites(),
			"DEFAULT_URL" => "/"
		];

		foreach ($forum->getPermissions() as $groupId => $permission)
		{
			if (
				$message["APPROVED"] == "Y" && $permission >= Permission::CAN_READ
				||
				$message["APPROVED"] != "Y" && $permission >= Permission::CAN_MODERATE
			)
			{
				$arParams["PERMISSION"][] = $groupId;
			}
		}


		$arSearchInd = array(
			"LID" => array(),
			"LAST_MODIFIED" => $message["POST_DATE"],
			"PARAM1" => $message["FORUM_ID"],
			"PARAM2" => $message["TOPIC_ID"],
			"ENTITY_TYPE_ID"  => ($message["NEW_TOPIC"] == "Y"? "FORUM_TOPIC": "FORUM_POST"),
			"ENTITY_ID" => ($message["NEW_TOPIC"] == "Y"? $message["TOPIC_ID"]: $message["ID"]),
			"USER_ID" => $message["AUTHOR_ID"],
			"PERMISSIONS" => $arParams["PERMISSION"],
			"TITLE" => $topic["TITLE"].( $message["NEW_TOPIC"] == "Y" && !empty($topic["DESCRIPTION"]) ? ", ".$topic["DESCRIPTION"] : ""),
			"TAGS" => ($message["NEW_TOPIC"] == "Y" ? $topic["TAGS"] : ""),
			"BODY" => GetMessage("AVTOR_PREF")." ".$message["AUTHOR_NAME"].". ".(\CSearch::KillTags(\forumTextParser::clearAllTags($message["POST_MESSAGE"]))),
			"URL" => "",
			"INDEX_TITLE" => $message["NEW_TOPIC"] == "Y",
		);

		// get mentions
		$arMentionedUserID = \CForumMessage::GetMentionedUserID($message["POST_MESSAGE"]);
		if (!empty($arMentionedUserID))
		{
			$arSearchInd["PARAMS"] = array(
				"mentioned_user_id" => $arMentionedUserID
			);
		}

		$urlPatterns = array(
			"FORUM_ID" => $message["FORUM_ID"],
			"TOPIC_ID" => $message["TOPIC_ID"],
			"TITLE_SEO" => $topic["TITLE_SEO"],
			"MESSAGE_ID" => $message["ID"],
			"SOCNET_GROUP_ID" => $topic["SOCNET_GROUP_ID"],
			"OWNER_ID" => $topic["OWNER_ID"],
			"PARAM1" => $message["PARAM1"],
			"PARAM2" => $message["PARAM2"]);
		foreach ($arParams["SITE"] as $key => $val)
		{
			$arSearchInd["LID"][$key] = \CForumNew::PreparePath2Message($val, $urlPatterns);
			if (empty($arSearchInd["URL"]) && !empty($arSearchInd["LID"][$key]))
				$arSearchInd["URL"] = $arSearchInd["LID"][$key];
		}

		if (empty($arSearchInd["URL"]) && ($res = \CLang::GetByID(SITE_ID)->fetch()))
		{
			$arParams["DEFAULT_URL"] .= $res["DIR"].
				\Bitrix\Main\Config\Option::get("forum", "REL_FPATH", "").
				"forum/read.php?FID=#FID#&TID=#TID#&MID=#MID##message#MID#";
			$arSearchInd["URL"] = \CForumNew::PreparePath2Message($arParams["DEFAULT_URL"], $urlPatterns);
		}
			/***************** Events onMessageIsIndexed ***********************/
		$index = true;
		foreach(GetModuleEvents("forum", "onMessageIsIndexed", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($message["ID"], $message, &$arSearchInd)) === false)
			{
				$index = false;
				break;
			}
		}
		/***************** /Events *****************************************/
		if ($index == true)
		{
			\CSearch::Index("forum", $message["ID"], $arSearchInd, true);
		}
	}
}