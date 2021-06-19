<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CForumAutosave $arParams["AUTOSAVE"]
 * @var CBitrixComponent $this
 * @var CMain $APPLICATION
 * @var CUser $USER
*/
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$post = $this->request->getPostList()->toArray();
if ($post["AJAX_POST"] == "Y")
	CUtil::decodeURIComponent($post);

$this->includeComponentLang("action.php");

// 1.3 Check Permission
if ((empty($request["preview_comment"]) || $request["preview_comment"] == "N"))
{
	$strErrorMessage = "";
	// 1.5 Create Property
	$needProperty = array();
	$PRODUCT_IBLOCK_ID = intval($arResult["ELEMENT"]["IBLOCK_ID"]);
	$PRODUCT_NAME = trim($arResult["ELEMENT"]["~NAME"]);
	$FORUM_TOPIC_ID = intval($arResult["ELEMENT"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
	$FORUM_MESSAGE_CNT = intval($arResult["ELEMENT"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);

	if ($FORUM_TOPIC_ID <= 0 && !($res = CIBlockElement::GetProperty($arResult["ELEMENT"]["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], false, false, array("CODE" => "FORUM_TOPIC_ID"))->fetch()))
	{
		$needProperty[] = "FORUM_TOPIC_ID";
	}
	if ($FORUM_MESSAGE_CNT <= 0 && !($res = CIBlockElement::GetProperty($arResult["ELEMENT"]["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], false, false, array("CODE" => "FORUM_MESSAGE_CNT"))->fetch()))
	{
		$needProperty[] = "FORUM_MESSAGE_CNT";
	}
	if (!empty($needProperty))
	{
		$obProperty = new CIBlockProperty;
		foreach ($needProperty as $nameProperty)
		{
			$sName = trim($nameProperty == "FORUM_TOPIC_ID" ? GetMessage("F_FORUM_TOPIC_ID") : GetMessage("F_FORUM_MESSAGE_CNT"));
			$sName = (empty($sName) ? $nameProperty : $sName);
			if($obProperty->Add(array(
				"IBLOCK_ID" => $PRODUCT_IBLOCK_ID,
				"ACTIVE" => "Y",
				"PROPERTY_TYPE" => "N",
				"MULTIPLE" => "N",
				"NAME" => $sName,
				"CODE" => $nameProperty)))
				${mb_strtoupper($nameProperty)} = 0;
		}
	}
	// 1.5 Set NULL for topic_id if it was deleted
	if ($FORUM_TOPIC_ID > 0):
		$arTopic = CForumTopic::GetByID($FORUM_TOPIC_ID);
		if (!is_array($arTopic) || $arTopic["FORUM_ID"] != $arParams["FORUM_ID"]):
			CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, 0, "FORUM_TOPIC_ID");
			CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, 0, "FORUM_MESSAGE_CNT");
			$FORUM_TOPIC_ID = 0;
		elseif ($arTopic["XML_ID"] !== "IBLOCK_".$arParams["ELEMENT_ID"]):
			CForumTopic::Update($FORUM_TOPIC_ID, array("XML_ID" => "IBLOCK_".$arParams["ELEMENT_ID"]));
		endif;
	elseif (($arTopic = CForumTopic::GetList(array(), array("XML_ID" => "IBLOCK_".$arParams["ELEMENT_ID"]))->fetch()) && $arTopic):
		$FORUM_TOPIC_ID = intval($arTopic["ID"]);
		CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, $arTopic["ID"], "FORUM_TOPIC_ID");
		CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, $arTopic["POSTS"], "FORUM_MESSAGE_CNT");
	endif;

	// 1.6 Create New topic and add messages
	if ($FORUM_TOPIC_ID <= 0)
	{

		$arUserStart["NAME"] = (empty($arUserStart["NAME"]) ? $GLOBALS["FORUM_STATUS_NAME"]["guest"] : $arUserStart["NAME"]);
	// 1.6.a.1 Add Topic
		$DB->StartTransaction();
		$arFields = Array(
			"TITLE"			=> $arResult["ELEMENT"]["~NAME"],
			"TAGS"			=> $arResult["ELEMENT"]["~TAGS"],
			"FORUM_ID"		=> $arParams["FORUM_ID"],
			"USER_START_ID"	=> $arUserStart["ID"],
			"USER_START_NAME" => $arUserStart["NAME"],
			"LAST_POSTER_NAME" => $arUserStart["NAME"],
			"APPROVED" => "Y",
			"XML_ID" => "IBLOCK_".$arParams["ELEMENT_ID"]
		);
		$TID = CForumTopic::Add($arFields);
		if ($TID <= 0)
		{
			$arError[] = array(
				"code" => "topic is not created",
				"title" => GetMessage("F_ERR_ADD_TOPIC"));
		}
		else
		{
	// 1.6.b Add post as new message
			$sImage = ""; $arSection = array();
			$url = (empty($arParams["URL_TEMPLATES_DETAIL"]) ? $arResult["ELEMENT"]["DETAIL_PAGE_URL"] : $arParams["URL_TEMPLATES_DETAIL"]);
			$SECTION_CODE_PATH = "";
			if (mb_strpos($arParams["URL_TEMPLATES_DETAIL"], "#SECTION_CODE#") !== false && intval($arResult["ELEMENT"]["IBLOCK_SECTION_ID"]) > 0):
				$db_res = CIBlockSection::GetList(array(), array("ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"]), false, array("ID", "NAME", "CODE"));
				if ($db_res && $res = $db_res->Fetch()):
					$arSection = $res;
				endif;
			endif;
			if (mb_strpos($arParams["URL_TEMPLATES_DETAIL"], "#SECTION_CODE_PATH#") !== false && intval($arResult["ELEMENT"]["IBLOCK_SECTION_ID"]) > 0):
				$db_res = CIBlockSection::GetNavChain(0, $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], array("ID", "IBLOCK_SECTION_ID", "CODE"));
				while ($a = $db_res->Fetch())
					$SECTION_CODE_PATH .= urlencode($a["CODE"])."/";
				$SECTION_CODE_PATH = rtrim($SECTION_CODE_PATH, "/");
			endif;
			$url = str_replace(
				array("#ELEMENT_ID#", "#ID#", "#ELEMENT_CODE#", "#SECTION_ID#", "#SECTION_CODE#", "#SECTION_CODE_PATH#"),
				array($arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["CODE"],
							$arResult["ELEMENT"]["IBLOCK_SECTION_ID"], $arSection["CODE"], $SECTION_CODE_PATH), $url);
			if (intval($arResult["ELEMENT"]["PREVIEW_PICTURE"]) > 0):
				$arImage = CFile::GetFileArray($arResult["ELEMENT"]["PREVIEW_PICTURE"]);
				if (!empty($arImage)):
					$sImage = ($arResult["FORUM"]["ALLOW_IMG"] == "Y" ? "[IMG]".$arImage["SRC"]."[/IMG]" : '');
				endif;
			endif;
			$sElementPreview = $arResult["ELEMENT"]["~PREVIEW_TEXT"];
			if ($arAllow["HTML"] != "Y")
				$sElementPreview = strip_tags($sElementPreview);
			$arFields = Array(
				"POST_MESSAGE" => str_replace(array("#IMAGE#", "#TITLE#", "#BODY#", "#LINK#"),
					array($sImage, $arResult["ELEMENT"]["~NAME"], $sElementPreview, $url),
					$arParams["POST_FIRST_MESSAGE_TEMPLATE"]),
				"AUTHOR_ID" => $arUserStart["ID"],
				"AUTHOR_NAME" => $arUserStart["NAME"],
				"FORUM_ID" => $arParams["FORUM_ID"],
				"TOPIC_ID" => $TID,
				"APPROVED" => "Y",
				"NEW_TOPIC" => "Y",
				"PARAM1" => "IB",
				"PARAM2" => $arParams["ELEMENT_ID"]);
			$MID = CForumMessage::Add($arFields, false, array("SKIP_INDEXING" => "Y", "SKIP_STATISTIC" => "N"));
			if ($MID <= 0)
			{
				$arError[] = array(
					"code" => "message is not added 1",
					"title" => GetMessage("F_ERR_ADD_MESSAGE"));
				CForumTopic::Delete($TID);
				$TID = 0;
			}
			else
			{
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $PRODUCT_IBLOCK_ID, intval($TID), "FORUM_TOPIC_ID");
				if ($arParams["SUBSCRIBE_AUTHOR_ELEMENT"] == "Y" && intval($arResult["ELEMENT"]["~CREATED_BY"]) > 0)
				{
					if ($arUserStart["USER_PROFILE"] == "N"):
						$arUserStart["FORUM_USER_ID"] = CForumUser::Add(array("USER_ID" => $arResult["ELEMENT"]["~CREATED_BY"]));
					endif;
					if (intval($arUserStart["FORUM_USER_ID"]) > 0):
						CForumSubscribe::Add(array(
							"USER_ID" => $arResult["ELEMENT"]["~CREATED_BY"],
							"FORUM_ID" => $arParams["FORUM_ID"],
							"SITE_ID" => SITE_ID,
							"TOPIC_ID" => $TID,
							"NEW_TOPIC_ONLY" => "N"));
						BXClearCache(true, "/bitrix/forum/user/".$arResult["ELEMENT"]["~CREATED_BY"]."/subscribe/"); // Sorry, Max.
					endif;
				}
			}
		}
	// Second exit point
		if (!empty($arError)):
			$DB->Rollback();
			return false;
		else:
			$DB->Commit();
		endif;
		$FORUM_TOPIC_ID = $TID;
	}
		// 1.6.1 Add post comment
	$arFieldsG = array(
		"POST_MESSAGE" => $post["REVIEW_TEXT"],
		"AUTHOR_NAME" => trim($post["REVIEW_AUTHOR"]),
		"AUTHOR_EMAIL" => $post["REVIEW_EMAIL"],
		"USE_SMILES" => $post["REVIEW_USE_SMILES"],
		"PARAM2" => intval($arParams["ELEMENT_ID"]),
		"TITLE" => $PRODUCT_NAME);

	if (!empty($_FILES["REVIEW_ATTACH_IMG"]))
	{
		$arFieldsG["ATTACH_IMG"] = $_FILES["REVIEW_ATTACH_IMG"];
	}
	else
	{
		$arFiles = array();
		if (!empty($post["FILES"])):
			foreach ($post["FILES"] as $key):
				$arFiles[$key] = array("FILE_ID" => $key);
				if (!in_array($key, $post["FILES_TO_UPLOAD"]))
					$arFiles[$key]["del"] = "Y";
			endforeach;
		endif;
		if (!empty($_FILES)):
			foreach ($_FILES as $key => $val):
				if (mb_substr($key, 0, mb_strlen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"])):
					if ($post["AJAX_POST"] == "Y")
						$val["name"] = $APPLICATION->ConvertCharset($val["name"], "UTF-8", LANG_CHARSET);

					$arFiles[] = $val;
				endif;
			endforeach;
		endif;
		if (!empty($arFiles))
			$arFieldsG["FILES"] = $arFiles;
	}
	$MID = ForumAddMessage(($FORUM_TOPIC_ID > 0 ? "REPLY" : "NEW"), $arParams["FORUM_ID"], $FORUM_TOPIC_ID, 0, $arFieldsG, $strErrorMessage, $arNote, false,
		$post["captcha_word"], 0, $post["captcha_code"]);

	if ($MID <= 0 || !empty($strErrorMessage)):
		$arError[] = array(
			"code" => "message is not added 2",
			"title" => (empty($strErrorMessage) ? GetMessage("F_ERR_ADD_MESSAGE") : $strErrorMessage));
		$arResult['RESULT'] = false;
		$arResult["OK_MESSAGE"] = '';
	else:
		if ($FORUM_TOPIC_ID <= 0):
			$res = CForumMessage::GetByID($MID);
			$FORUM_TOPIC_ID = intval($res["TOPIC_ID"]);
		endif;
		if ($arParams["AUTOSAVE"])
			$arParams["AUTOSAVE"]->Reset();

		$arResult["FORUM_TOPIC_ID"] = intval($FORUM_TOPIC_ID);
		ForumClearComponentCache($componentName);

		// SUBSCRIBE
		if ($post["TOPIC_SUBSCRIBE"] == "Y"):
			ForumSubscribeNewMessagesEx($arParams["FORUM_ID"], $FORUM_TOPIC_ID, "N", $strErrorMessage, $strOKMessage);
			BXClearCache(true, "/bitrix/forum/user/".$USER->GetID()."/subscribe/");
		endif;

		$strURL = (!empty($post["back_page"]) ? $post["back_page"] : $APPLICATION->GetCurPageParam("",
			array("MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result", "AJAX_CALL", "bxajaxid")));
		$bNotModerated = ($arResult["FORUM"]["MODERATION"] != "Y" || CForumNew::CanUserModerateForum($arParams["FORUM_ID"], $USER->GetUserGroupArray()));
		$strURL = ForumAddPageParams($strURL, array("MID" => $MID, "result" => ($bNotModerated ? "reply" : "not_approved")), true, false);
		$strURL .= ($bNotModerated ? "#message".$MID : "#reviewnote");

		if ($arParams["NO_REDIRECT_AFTER_SUBMIT"] != "Y")
			LocalRedirect($strURL);
		else
			$arResult['RESULT'] = $MID;
	endif;
}
elseif ($post["save_product_review"] == "Y") // preview
{
	$arAllow["SMILES"] = ($post["REVIEW_USE_SMILES"] !="Y" ? "N" : $arResult["FORUM"]["ALLOW_SMILES"]);
	$arResult["MESSAGE_VIEW"] = array(
		"POST_MESSAGE_TEXT" => $post["REVIEW_TEXT"],
		"AUTHOR_NAME" => htmlspecialcharsbx($arResult["USER"]["SHOWED_NAME"]),
		"AUTHOR_ID" => intval($USER->GetID()),
		"AUTHOR_URL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $USER->GetID())),
		"POST_DATE" => CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], time()+CTimeZone::GetOffset()),
		"FILES" => array());

	$arFields = array(
			"FORUM_ID" => intval($arParams["FORUM_ID"]),
			"TOPIC_ID" => 0,
			"MESSAGE_ID" => 0,
			"USER_ID" => intval($USER->GetID()));
	$arFiles = array();
	$arFilesExists = array();
	$res = array();

	foreach ($_FILES as $key => $val):
		if ((mb_substr($key, 0, mb_strlen("FILE_NEW")) == "FILE_NEW") && !empty($val["name"])):
			if ($post["AJAX_POST"] == "Y")
				$val["name"] = $APPLICATION->ConvertCharset($val["name"], "UTF-8", LANG_CHARSET);
			$arFiles[] = $val;
		endif;
	endforeach;
	if (is_array($post["FILES"]))
	{
		$post["FILES_TO_UPLOAD"] = is_array($post["FILES_TO_UPLOAD"]) ? $post["FILES_TO_UPLOAD"] : array();
		foreach ($post["FILES"] as $key => $val)
		{
			if (in_array($val, $post["FILES_TO_UPLOAD"]))
				$arFilesExists[$val] = array("FILE_ID" => $val);
			else
			{
				$arFiles[$val] = array("FILE_ID" => $val, "del" => "Y");
				unset($post["FILES"][$key]);
				unset($post["FILES_TO_UPLOAD"][$key]);
			}
		}
	}

	if (!empty($arFiles)):
		$res = CForumFiles::Save($arFiles, $arFields);
		$res1 = $APPLICATION->GetException();
		if ($res1):
			$arError[] = array(
				"code" => "file upload error",
				"title" => $res1->GetString());
		endif;
	endif;

	$res = is_array($res) ? $res : array();
	foreach ($res as $key => $val):
		$arFilesExists[$key] = $val;
	endforeach;
	$arFilesExists = array_keys($arFilesExists);
	sort($arFilesExists);
	$arResult["MESSAGE_VIEW"]["FILES"] = $post["FILES"] = $arFilesExists;
	$arResult["MESSAGE_VIEW"]["POST_MESSAGE_TEXT"] = $parser->convert($post["REVIEW_TEXT"], $arAllow, "html", $arFilesExists);

}
if (isset($request['REVIEW_ACTION']))
{
	$arFields = array();
	if (empty($arError))
	{
		if (isset($request['MID']) && intval($request['MID']) > 0)
			$arFields = array("MID" => intval($request['MID']));
		if (($result = ForumActions($request['REVIEW_ACTION'], $arFields, $strErrorMessage, $strOKMessage)) && $result)
		{
			ForumClearComponentCache($componentName);
		}
	}
	if (isset($request['AJAX_CALL']))
	{
		$APPLICATION->RestartBuffer();
		if (empty($arError))
		{
			$arRes = array('status' => $result, 'message' => ($result ? (!empty($arNote) ? $arNote[0]["text"] : $strOKMessage) : $strErrorMessage));
		}
		else
		{
			$arRes = array('status' => false, 'message' => $arError[0]['title']);
		}
		echo CUtil::PhpToJSObject($arRes);
		die();
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", array("REVIEW_ACTION", "sessid", "MID")));
	}
}
?>