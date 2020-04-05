<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("forum"))
{
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_inc.php", Array("FORUM_ID" => 'array()'));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/forum/index.php", Array("FORUM_ID" => 'array()'));
	return;
}

$arLanguages = Array();
$rsLanguage = CLanguage::GetList($by, $order, array());
while($arLanguage = $rsLanguage->Fetch())
	$arLanguages[] = $arLanguage["LID"];

// Forum group
$arGroupID = Array(
	"COMMUNITY" => 0,
	"HIDDEN" => 0,
);

$dbExistsGroup = CForumGroup::GetListEx(array(), array("LID" => LANGUAGE_ID));
while ($arExistsGroup = $dbExistsGroup->Fetch())
{
	foreach ($arGroupID as $xmlID => $ID)
	{
		if ($arExistsGroup["NAME"] == GetMessage($xmlID."_GROUP_NAME") )
			$arGroupID[$xmlID] = $arExistsGroup["ID"];
	}
}

$sort = 1;
foreach ($arGroupID as $xmlID => $groupID)
{
	if ($groupID > 0)
		continue;

	$arNewGroup = Array("SORT" => $sort++, "LANG" => Array());
	foreach($arLanguages as $languageID)
	{
		$arMessages = WizardServices::IncludeServiceLang("index.php", $languageID, $bReturnArray=true);
		$arNewGroup["LANG"][] = Array(
			"LID" => $languageID, 
			"NAME" => (array_key_exists($xmlID."_GROUP_NAME",$arMessages) ? $arMessages[$xmlID."_GROUP_NAME"] : GetMessage($xmlID."_GROUP_NAME")), 
			"DESCRIPTION" => (array_key_exists($xmlID."_GROUP_DESCRIPTION",$arMessages) ? $arMessages[$xmlID."_GROUP_DESCRIPTION"] : GetMessage($xmlID."_GROUP_DESCRIPTION"))
		);
	}
	$arGroupID[$xmlID] = CForumGroup::Add($arNewGroup);
}
$arUsers = array(); 

$db_user = CForumUser::GetList(array("USER_ID"=>"ASC"), array("ACTIVE" => "Y", "SHOW_ABC" => "Y"), array("nTopCount" => 5)); 
if ($db_user && $res = $db_user->Fetch())
{
	do 
	{
		$arUsers[$res["USER_ID"]] = $res; 
	} while ($res = $db_user->Fetch());
}
if (empty($arUsers[1]))
	$arUsers[1] = array("USER_ID" => 1, "SHOW_ABC" => "admin"); 
$arAdmin = $arUsers[1];
unset($arUsers[1]); 
$arUser = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST1"))); 
$arUser2 = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST2"))); 
$arUser3 = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST3"))); 
$arUser4 = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST4"))); 
$arUser5 = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST5"))); 
$arUser6 = (!empty($arUsers) ? array_shift($arUsers) : array("USER_ID" => 0, "SHOW_ABC" => GetMessage("GUEST6"))); 
 
$arForums = Array(
	/*Array(
		"XML_ID" => "COMMUNITY_GENERAL",
		"NAME" => GetMessage("GENERAL_FORUM_NAME"),
		"DESCRIPTION" => GetMessage("GENERAL_FORUM_DESCRIPTION"),
		"SORT" => 1,
		"ACTIVE" => "Y",
		"ALLOW_HTML" => "N",
		"ALLOW_ANCHOR" => "Y",
		"ALLOW_BIU" => "Y",
		"ALLOW_IMG" => "Y",
		"ALLOW_LIST" => "Y",
		"ALLOW_QUOTE" => "Y",
		"ALLOW_CODE" => "Y",
		"ALLOW_FONT" => "Y",
		"ALLOW_SMILES" => "Y",
		"ALLOW_UPLOAD" => "Y",
		"ALLOW_NL2BR" => "N",
		"MODERATION" => "N",
		"ALLOW_MOVE_TOPIC" => "Y",
		"ORDER_BY" => "P",
		"ORDER_DIRECTION" => "DESC",
		"LID" => LANGUAGE_ID,
		"PATH2FORUM_MESSAGE" => "",
		"ALLOW_UPLOAD_EXT" => "",
		"FORUM_GROUP_ID" => $arGroupID["COMMUNITY"],
		"ASK_GUEST_EMAIL" => "N",
		"USE_CAPTCHA" => "N",
		"SITES" => Array(
			WIZARD_SITE_ID => WIZARD_SITE_DIR."forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#",
		),
		"EVENT1" => "forum", 
		"EVENT2" => "message",
		"EVENT3" => "",
		"GROUP_ID" => Array(
			"2" => "M",
			"1" => "Y",
		),
		"TOPICS" => Array(
			Array(
				"TITLE"			=> GetMessage("GENERAL_FORUM_TOPIC1_TITLE"),
				"DESCRIPTION"	=> "",
				"ICON_ID"		=> 0,
				"TAGS"			=> "",
				"USER_START_ID" => $arUser["USER_ID"],
				"USER_START_NAME" => $arUser["SHOW_ABC"],
				"LAST_POSTER_NAME" => $arUser["SHOW_ABC"],
				"APPROVED" => "Y",
				"MESSAGES" => Array(
					Array(
						"POST_MESSAGE"	=> GetMessage("GENERAL_FORUM_TOPIC1_MESSAGE1"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
					Array(
						"POST_MESSAGE"	=> GetMessage("GENERAL_FORUM_TOPIC1_MESSAGE2"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arAdmin["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arAdmin["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
				),
			),
			Array(
				"TITLE"			=> GetMessage("GENERAL_FORUM_TOPIC2_TITLE"),
				"DESCRIPTION"	=> "",
				"ICON_ID"		=> 0,
				"TAGS"			=> "",
				"USER_START_ID" => $arUser2["USER_ID"],
				"USER_START_NAME" => $arUser2["SHOW_ABC"],
				"LAST_POSTER_NAME" => $arUser2["SHOW_ABC"],
				"APPROVED" => "Y",
				"MESSAGES" => Array(
					Array(
						"POST_MESSAGE"	=> GetMessage("GENERAL_FORUM_TOPIC2_MESSAGE1"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser2["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser2["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
					Array(
						"POST_MESSAGE"	=> GetMessage("GENERAL_FORUM_TOPIC2_MESSAGE2"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser3["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser3["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
					Array(
						"POST_MESSAGE"	=> GetMessage("GENERAL_FORUM_TOPIC2_MESSAGE3"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser4["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser4["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
				),
			),			
		),
	),

	Array(
		"XML_ID" => "COMMUNITY_SITE",
		"NAME" => GetMessage("SITE_FORUM_NAME"),
		"DESCRIPTION" => GetMessage("SITE_FORUM_DESCRIPTION"),
		"SORT" => 2,
		"ACTIVE" => "Y",
		"ALLOW_HTML" => "N",
		"ALLOW_ANCHOR" => "Y",
		"ALLOW_BIU" => "Y",
		"ALLOW_IMG" => "Y",
		"ALLOW_LIST" => "Y",
		"ALLOW_QUOTE" => "Y",
		"ALLOW_CODE" => "Y",
		"ALLOW_FONT" => "Y",
		"ALLOW_SMILES" => "Y",
		"ALLOW_UPLOAD" => "Y",
		"ALLOW_NL2BR" => "N",
		"MODERATION" => "N",
		"ALLOW_MOVE_TOPIC" => "Y",
		"ORDER_BY" => "P",
		"ORDER_DIRECTION" => "DESC",
		"LID" => LANGUAGE_ID,
		"PATH2FORUM_MESSAGE" => "",
		"ALLOW_UPLOAD_EXT" => "",
		"FORUM_GROUP_ID" => $arGroupID["COMMUNITY"],
		"ASK_GUEST_EMAIL" => "N",
		"USE_CAPTCHA" => "N",
		"SITES" => Array(
			WIZARD_SITE_ID => WIZARD_SITE_DIR."forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#"
		),
		"EVENT1" => "forum", 
		"EVENT2" => "message",
		"EVENT3" => "",
		"GROUP_ID" => Array(
			"2" => "M",
			"1" => "Y",
		),
		"TOPICS" => Array(
			Array(
				"TITLE"			=> GetMessage("SITE_FORUM_TOPIC1_TITLE"),
				"DESCRIPTION"	=> "",
				"ICON_ID"		=> 0,
				"TAGS"			=> "",
				"USER_START_ID" => $arUser5["USER_ID"],
				"USER_START_NAME" => $arUser5["SHOW_ABC"],
				"LAST_POSTER_NAME" => $arUser5["SHOW_ABC"],
				"APPROVED" => "Y",
				"MESSAGES" => Array(
					Array(
						"POST_MESSAGE"	=> GetMessage("SITE_FORUM_TOPIC1_MESSAGE1"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser5["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser5["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
					Array(
						"POST_MESSAGE"	=> GetMessage("SITE_FORUM_TOPIC1_MESSAGE2"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arAdmin["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arAdmin["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
				),
			),
			Array(
				"TITLE"			=> GetMessage("SITE_FORUM_TOPIC2_TITLE"),
				"DESCRIPTION"	=> "",
				"ICON_ID"		=> 0,
				"TAGS"			=> "",
				"USER_START_ID" => $arUser6["USER_ID"],
				"USER_START_NAME" => $arUser6["SHOW_ABC"],
				"LAST_POSTER_NAME" => $arUser6["SHOW_ABC"],
				"APPROVED" => "Y",
				"MESSAGES" => Array(
					Array(
						"POST_MESSAGE"	=> GetMessage("SITE_FORUM_TOPIC2_MESSAGE1"),
						"USE_SMILES"	=> "Y",
						"APPROVED"		=> "Y",
						"AUTHOR_NAME"	=> $arUser6["SHOW_ABC"],
						"AUTHOR_EMAIL"	=> "",
						"AUTHOR_ID"		=> $arUser6["USER_ID"],
						"AUTHOR_IP"		=> "<no address>",
					),
				),
			),			
		),
	),   */

	Array(
		"XML_ID" => "COMMUNITY_PHOTO_COMMENTS",
		"NAME" => GetMessage("PHOTOGALLERY_COMMENTS_FORUM_NAME"),
		"DESCRIPTION" => "",
		"SORT" => 3,
		"ACTIVE" => "Y",
		"ALLOW_HTML" => "N",
		"ALLOW_ANCHOR" => "Y",
		"ALLOW_BIU" => "Y",
		"ALLOW_IMG" => "Y",
		"ALLOW_LIST" => "Y",
		"ALLOW_QUOTE" => "Y",
		"ALLOW_CODE" => "Y",
		"ALLOW_FONT" => "Y",
		"ALLOW_SMILES" => "Y",
		"ALLOW_UPLOAD" => "Y",
		"ALLOW_NL2BR" => "N",
		"MODERATION" => "N",
		"ALLOW_MOVE_TOPIC" => "Y",
		"ORDER_BY" => "P",
		"ORDER_DIRECTION" => "DESC",
		"LID" => LANGUAGE_ID,
		"PATH2FORUM_MESSAGE" => "",
		"ALLOW_UPLOAD_EXT" => "",
		"FORUM_GROUP_ID" => $arGroupID["HIDDEN"],
		"ASK_GUEST_EMAIL" => "N",
		"USE_CAPTCHA" => "N",
		"INDEXATION" => "Y",
		"SITES" => Array(
			WIZARD_SITE_ID => WIZARD_SITE_DIR."forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#"
		),
		"EVENT1" => "forum", 
		"EVENT2" => "message",
		"EVENT3" => "",
		"GROUP_ID" => Array(
			"2" => "M",
			"1" => "Y",
		),
	),	

	Array(
		"XML_ID" => "COMMUNITY_USERS_AND_GROUPS",
		"NAME" => GetMessage("USERS_AND_GROUPS_FORUM_NAME"),
		"DESCRIPTION" => GetMessage("USERS_AND_GROUPS_FORUM_DESCRIPTION"),
		"SORT" => 4,
		"ACTIVE" => "Y",
		"ALLOW_HTML" => "N",
		"ALLOW_ANCHOR" => "Y",
		"ALLOW_BIU" => "Y",
		"ALLOW_IMG" => "Y",
		"ALLOW_LIST" => "Y",
		"ALLOW_QUOTE" => "Y",
		"ALLOW_CODE" => "Y",
		"ALLOW_FONT" => "Y",
		"ALLOW_SMILES" => "Y",
		"ALLOW_UPLOAD" => "Y",
		"ALLOW_NL2BR" => "N",
		"MODERATION" => "N",
		"ALLOW_MOVE_TOPIC" => "Y",
		"ORDER_BY" => "P",
		"ORDER_DIRECTION" => "DESC",
		"LID" => LANGUAGE_ID,
		"PATH2FORUM_MESSAGE" => "",
		"ALLOW_UPLOAD_EXT" => "",
		"FORUM_GROUP_ID" => $arGroupID["HIDDEN"],
		"ASK_GUEST_EMAIL" => "N",
		"USE_CAPTCHA" => "N",
		"INDEXATION" => "Y",
		"SITES" => Array(
			WIZARD_SITE_ID => WIZARD_SITE_DIR."forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#"
		),
		"EVENT1" => "forum", 
		"EVENT2" => "message",
		"EVENT3" => "",
		"GROUP_ID" => Array(
			"1" => "Y",
		),
	),
);
foreach ($arForums as $arForum)
{
	$dbForum = CForumNew::GetList(Array(), Array("SITE_ID" => WIZARD_SITE_ID, "XML_ID" => $arForum["XML_ID"]));
	if ($resForum = $dbForum->Fetch())
	{
		if (WIZARD_INSTALL_DEMO_DATA)
		{
			CForumNew::Delete($resForum["ID"]); 
		}
		else
		{
			$res = CForumNew::GetSites($resForum["ID"]); 
			if (!array_key_exists(WIZARD_SITE_ID, $res))
			{
				$res[WIZARD_SITE_ID] = WIZARD_SITE_DIR."forum/messages/forum#FORUM_ID#/topic#TOPIC_ID#/message#MESSAGE_ID#/#message#MESSAGE_ID#";
				CForumNew::Update($resForum["ID"], array("SITES" => $res)); 
			}
			continue; 
		}
	}

	$forumID = CForumNew::Add($arForum);
	if ($forumID < 1 || !isset($arForum["TOPICS"]) || !is_array($arForum["TOPICS"]) )
		continue;

	foreach ($arForum["TOPICS"] as $arTopic)
	{
		$arTopic["FORUM_ID"] = $forumID;
		$topicID = CForumTopic::Add($arTopic);
		if ($topicID < 1 || !isset($arTopic["MESSAGES"]) || !is_array($arTopic["MESSAGES"]) )
			continue;

		foreach ($arTopic["MESSAGES"] as $arMessage)
		{
			$arMessage["FORUM_ID"] = $forumID;
			$arMessage["TOPIC_ID"] = $topicID;
			$messageID = CForumMessage::Add($arMessage, false);
			if ($messageID < 1)
			{
				CForumTopic::Delete($topicID);
				continue 2;
			}
			CForumTopic::SetStat($topicID);
		}
	}
}

$fidParameter = "";
$dbForum = CForumNew::GetList(array(), array());
while ($arForum = $dbForum->Fetch())
{
	if ($arForum["FORUM_GROUP_ID"] != $arGroupID["HIDDEN"])
		$fidParameter .= $arForum["ID"].",";
}
$fidParameter = rtrim($fidParameter, ",");
/************** Forum Replace Data *********************************/
$iForumIDPhoto = 0;
$dbRes = CForumNew::GetListEx(array(), array("SITE_ID" => WIZARD_SITE_ID, "XML_ID" => "COMMUNITY_PHOTO_COMMENTS"));
if ($arRes = $dbRes->Fetch())
	$iForumIDPhoto = $arRes["ID"];
$iForumIDForum = 0; 
$dbRes = CForumNew::GetListEx(array(), array("SITE_ID" => WIZARD_SITE_ID, "XML_ID" => "COMMUNITY_USERS_AND_GROUPS"));
if ($arRes = $dbRes->Fetch())
	$iForumIDForum = $arRes["ID"];
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/people/user.php", Array("PHOTO_FORUM_ID" => $iForumIDPhoto, "PHOTO_USE_COMMENTS" => "Y", "FORUM_ID" => $iForumIDForum));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/photo/index.php", Array("FORUM_ID" => $iForumIDPhoto));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/groups/group.php", Array("PHOTO_FORUM_ID" => $iForumIDPhoto, "PHOTO_USE_COMMENTS" => "Y", "FORUM_ID" => $iForumIDForum));

$arForumsID = array();
$dbRes = CForumNew::GetListEx(array(), array("SITE_ID" => WIZARD_SITE_ID, "!FORUM_GROUP_ID" => $arGroupID["HIDDEN"]));
if ($arRes = $dbRes->Fetch())
{
	do
	{
		$arForumsID[] = $arRes["ID"]; 
	} while ($arRes = $dbRes->Fetch()); 
}
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_inc.php", Array("FORUM_ID" => 'array('.implode(", ", $arForumsID).')'));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/forum/index.php", Array("FORUM_ID" => 'array('.implode(", ", $arForumsID).')'));

if (!WIZARD_IS_RERUN)
{
	$APPLICATION->SetGroupRight("forum", WIZARD_PORTAL_ADMINISTRATION_GROUP, "W");
	COption::SetOptionString("forum", "SHOW_VOTES", "N");
	COption::SetOptionString("forum", "file_max_size", 10485760);
}
?>
