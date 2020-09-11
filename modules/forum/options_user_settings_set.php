<?
$forumWarningTmp = "";
if (CModule::IncludeModule("forum") && check_bitrix_sessid()):
	$arforumFields = Array(
		"SHOW_NAME" => ($forum_SHOW_NAME=="Y") ? "Y" : "N",
		"HIDE_FROM_ONLINE" => ($forum_HIDE_FROM_ONLINE=="Y") ? "Y" : "N",
		"SUBSC_GROUP_MESSAGE" => ($forum_SUBSC_GROUP_MESSAGE=="Y") ? "Y" : "N",
		"SUBSC_GET_MY_MESSAGE" => ($forum_SUBSC_GET_MY_MESSAGE=="Y") ? "Y" : "N",
		"DESCRIPTION" => $forum_DESCRIPTION,
		"INTERESTS" => $forum_INTERESTS,
		"SIGNATURE" => $forum_SIGNATURE,
		"AVATAR" => $_FILES["forum_AVATAR"]
	);
	
	$arforumFields["AVATAR"]["del"] = $forum_AVATAR_del;

	if ($USER->IsAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("forum") >= "W")
	{
		$arforumFields["ALLOW_POST"] = (($forum_ALLOW_POST=="Y") ? "Y" : "N");
	}

	$ar_res = CForumUser::GetByUSER_ID($ID);
	if ($ar_res)
	{
		$arforumFields["AVATAR"]["old_file"] = $ar_res["AVATAR"];
		$FORUM_USER_ID = intval($ar_res["ID"]);

		$FORUM_USER_ID1 = CForumUser::Update($FORUM_USER_ID, $arforumFields);
		$forum_res = (intval($FORUM_USER_ID1)>0);
	}
	else
	{
		$arforumFields["USER_ID"] = $ID;

		$FORUM_USER_ID = CForumUser::Add($arforumFields);
		$forum_res = (intval($FORUM_USER_ID)>0);
	}
endif;
?>