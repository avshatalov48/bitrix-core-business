<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->IncludeComponentLang("action.php");

$photo_list_action = $_REQUEST["photo_list_action"];
if (isset($photo_list_action) && $photo_list_action != "")
{
	$APPLICATION->ShowAjaxHead();
	$UCID = preg_replace("/[^a-z0-9\_]+/is" , "", $_REQUEST["UCID"]);
	?><script>
	if (!window.BX && top.BX){BX = top.BX;}
	window.bxph_action_url_<?= $UCID?> = '<?= CUtil::JSEscape(CHTTP::urlDeleteParams(htmlspecialcharsback(POST_FORM_ACTION_URI), array("view_mode", "sessid", "uploader_redirect", "photo_list_action", "pio", "ELEMENT_ID", "UCID"), true));?>';
	<?
	if (!check_bitrix_sessid()){?>window.bxph_error = '<?= GetMessage("IBLOCK_WRONG_SESSION")?>';<?die('</'.'script>');}?>
	</script>
	<?
	if ($photo_list_action == 'load_comments' && $arParams["USE_COMMENTS"] == "Y" && $arParams["PERMISSION"] >= "R")
	{
		$this->InitComponentTemplate("", false, "");
		$arCommentsParams = Array(
			"POPUP_MODE" => "Y",
			"ACTION_URL" => $arParams["ACTION_URL"].(strpos($arParams["ACTION_URL"], "?") === false ? "?" : "&")."photo_list_action=load_comments",
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"SECTION_ID" => $arParams["SECTION_ID"],
			"ELEMENT_ID" => intVal($_REQUEST["photo_element_id"]),
			"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
			"DETAIL_URL" => $arParams["~DETAIL_URL"],
			"SECTION_URL" => $arParams["~SECTION_URL"],
			"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"IS_SOCNET" => $arParams["IS_SOCNET"],
			"SHOW_RATING" => $arParams["USE_RATING"] == "Y" && $arParams["DISPLAY_AS_RATING"] == "rating_main"? "Y": "N",
			"RATING_TYPE" => $arParams["RATING_MAIN_TYPE"],
			"CACHE_TYPE" => "N",
			"CACHE_TIME" => 0,
			"PATH_TO_USER" => $arParams["PATH_TO_USER"],
			"FETCH_USER_ALIAS" => preg_match("/#user_alias#/i".BX_UTF_PCRE_MODIFIER, $arParams["PATH_TO_USER"])
		);

		$arCommentsParams["COMMENTS_TYPE"] = (strToLower($arParams["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");

		if ($arCommentsParams["COMMENTS_TYPE"] == "blog")
		{
			$arCommentsParams["COMMENTS_TYPE"] = "blog";
			$arCommentsParams["BLOG_URL"] = $arParams["BLOG_URL"];
			$arCommentsParams["PATH_TO_BLOG"] = $arParams["PATH_TO_BLOG"];
		}
		else
		{
			$arCommentsParams["FORUM_ID"] = $arParams["FORUM_ID"];
			$arCommentsParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"];
			$arCommentsParams["URL_TEMPLATES_READ"] = $arParams["URL_TEMPLATES_READ"];
			$arCommentsParams["URL_TEMPLATES_PROFILE_VIEW"] = $arParams["URL_TEMPLATES_PROFILE_VIEW"];
			//$arCommentsParams["POST_FIRST_MESSAGE"] = $arParams["POST_FIRST_MESSAGE"];
			//$arCommentsParams["PREORDER"] = ($arParams["PREORDER"] != "N" ? "Y" : "N");
			$arCommentsParams["POST_FIRST_MESSAGE"] = $arParams["POST_FIRST_MESSAGE"] == "N" ? "N" : "Y";
			$arCommentsParams["PREORDER"] = "N";
			$arCommentsParams["SHOW_LINK_TO_FORUM"] = "N";
		}

		if ($arCommentsParams["IS_SOCNET"] == "Y" || !empty($arParams["USER_ALIAS"]))
			$arCommentsParams["USER_ALIAS"] = $arParams["USER_ALIAS"];

		$arCommentsParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"];

		$APPLICATION->IncludeComponent(
			"bitrix:photogallery.detail.comment",
			"",
			$arCommentsParams,
			$this,
			array("HIDE_ICONS" => "Y")
		);
	}
	elseif($photo_list_action == 'save_sort_order' && $arParams["PERMISSION"] >= "U")
	{
		CUtil::JSPostUnEscape();
		if (is_array($_REQUEST['pio']))
		{
			CModule::IncludeModule("iblock");
			$bs = new CIBlockElement;
			foreach ($_REQUEST['pio'] as $id => $sort)
			{
				if (intVal($id) > 0 && intVal($sort) >= 0)
					$bs->Update(intVal($id), array("SORT" => intVal($sort)),false,false);
			}

			PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
		}
	}
	elseif($photo_list_action == 'save_description' && $arParams["PERMISSION"] >= "U")
	{
		CUtil::JSPostUnEscape();
		CModule::IncludeModule("iblock");
		$arFields = Array("MODIFIED_BY" => $USER->GetID());

		$arFields["PREVIEW_TEXT"] = $_REQUEST["description"];
		$arFields["DETAIL_TEXT"] = $_REQUEST["description"];
		$arFields["DETAIL_TEXT_TYPE"] = "text";
		$arFields["PREVIEW_TEXT_TYPE"] = "text";
		$arFields["IBLOCK_SECTION_ID"] = $arParams["SECTION_ID"];

		$bs = new CIBlockElement;
		$ID = $bs->Update($arParams["ELEMENT_ID"], $arFields);

		if ($ID <= 0)
		{
			?>
			<script>
				window.bxph_error = '<?= GetMessage("SAVE_DESC_ERROR").": ".$bs->LAST_ERROR?>';
			</script>
			<?
		}
		else
		{
			PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
		}
	}
	elseif($photo_list_action == 'activate' && $arParams["PERMISSION"] >= "X")
	{
		CUtil::JSPostUnEscape();
		CModule::IncludeModule("iblock");
		$bs = new CIBlockElement;
		$ID = $bs->Update($arParams["ELEMENT_ID"], Array("MODIFIED_BY" => $USER->GetID(), "ACTIVE" => "Y"));
		if ($ID <= 0)
		{
			?><script>window.bxph_error = '<?= $bs->LAST_ERROR?>';</script><?
		}
		else
		{
			PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
		}
	}
	elseif($photo_list_action == 'rotate' && $_REQUEST['angle'] > 0 && $arParams["PERMISSION"] >= "U")
	{
		CUtil::JSPostUnEscape();
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"SECTION_ID" => $arParams["SECTION_ID"],
			"ID" => $arParams["ELEMENT_ID"],
			"CHECK_PERMISSIONS" => "Y"
		);

		// TODO: add pictures sights to select $arParams["PICTURES_SIGHT"]
		$arSelect = array(
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"PROPERTY_REAL_PICTURE"
		);

		$angle = intVal($_REQUEST['angle']);
		$db_res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

		if ($arRes = $db_res->Fetch())
		{
			// Preview
			if ($arRes["PREVIEW_PICTURE"] > 0)
			{
				$arImg = CFile::MakeFileArray($arRes["PREVIEW_PICTURE"]);
				CFile::ImageRotate($arImg['tmp_name'], $angle);
				$arFields["PREVIEW_PICTURE"] = CFile::MakeFileArray($arImg['tmp_name']);
			}

			// Detail
			if ($arRes["DETAIL_PICTURE"] > 0)
			{
				$arImg = CFile::MakeFileArray($arRes["DETAIL_PICTURE"]);
				CFile::ImageRotate($arImg['tmp_name'], $angle);
				$arFields["DETAIL_PICTURE"] = CFile::MakeFileArray($arImg['tmp_name']);
			}

			// Real
			if ($arRes["PROPERTY_REAL_PICTURE_VALUE"] > 0)
			{
				$arImg = CFile::MakeFileArray($arRes["PROPERTY_REAL_PICTURE_VALUE"]);
				CFile::ImageRotate($arImg['tmp_name'], $angle);
				CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"],
					array(
						"REAL_PICTURE" => CFile::MakeFileArray($arImg['tmp_name'])
					),
					"REAL_PICTURE"
				);
			}

			$bs = new CIBlockElement;
			if ($res = $bs->Update($arParams["ELEMENT_ID"], $arFields))
			{
				$db_res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

				if ($arRes1 = $db_res->Fetch())
				{
					$w = 0;
					$h = 0;
					$src = '';
					$thumb_src = '';
					if ($arRes1["PREVIEW_PICTURE"] > 0)
					{
						$file = CFile::GetFileArray($arRes1["PREVIEW_PICTURE"]);
						$thumb_src = $file['SRC'];
					}

					if ($arRes1["PROPERTY_REAL_PICTURE_VALUE"] > 0)
						$file = CFile::GetFileArray($arRes1["PROPERTY_REAL_PICTURE_VALUE"]);
					elseif ($arRes1["DETAIL_PICTURE"] > 0)
						$file = CFile::GetFileArray($arRes1["DETAIL_PICTURE"]);

					$src = $file['SRC'];
					$w = $file['WIDTH'];
					$h = $file['HEIGHT'];

					?><script>
					window.bxphres = {
						Item: {
							id: <?= $arParams['ELEMENT_ID']?>,
							src: '<?= CUtil::JSEscape($file['SRC'])?>',
							w: parseInt('<?= CUtil::JSEscape($file['WIDTH'])?>'),
							h: parseInt('<?= CUtil::JSEscape($file['HEIGHT'])?>')
						}
					};
					</script><?

					PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
				}
			}
			else
			{
				?><script>
				window.bxph_error = '<?= GetMessage("ROTATE_ERROR").": ".$bs->LAST_ERROR?>';
				</script><?
			}
		}
	}
	elseif($photo_list_action == 'delete' && $arParams["PERMISSION"] >= "U")
	{
		CUtil::JSPostUnEscape();
		CModule::IncludeModule("iblock");
		@set_time_limit(0);
		$APPLICATION->ResetException();
		$res = CIBlockElement::Delete($arParams["ELEMENT_ID"]);

		if ($res)
		{
			$arEventFields = array(
				"ID" => $arParams["ELEMENT_ID"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"SECTION_ID" => $arParams["SECTION_ID"]
			);
			foreach(GetModuleEvents("photogallery", "OnAfterPhotoDrop", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arEventFields, $arParams));
			PClearComponentCacheEx($arParams["IBLOCK_ID"], array(0, $arParams["SECTION_ID"]));
		}
		else
		{
			?>
			<script>
				window.bxph_error = '<?= GetMessage("DEL_ITEM_ERROR").($ex = $APPLICATION->GetException() ? ': '.$ex->GetString() : '')?>';
			</script>
			<?
		}
	}
	elseif($photo_list_action == 'edit' && $arParams["PERMISSION"] >= "U")
	{
		CUtil::JSPostUnEscape();
		CModule::IncludeModule("iblock");
		if (intVal($_REQUEST["SECTION_ID"]) > 0)
			$arParams["SECTION_ID"] = intVal($_REQUEST["SECTION_ID"]);
		if (!$arParams["USER_ALIAS"] && isset($_REQUEST["USER_ALIAS"]))
			$arParams["USER_ALIAS"] = $_REQUEST["USER_ALIAS"];

		// Don't delete <!--BX_PHOTO_EDIT_RES-->, <!--BX_PHOTO_EDIT_RES_END--> comments - they are used in js to catch html content
		?><!--BX_PHOTO_EDIT_RES--><?
		$APPLICATION->IncludeComponent(
			"bitrix:photogallery.detail.edit",
			"",
			Array(
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"USER_ALIAS" => $arParams["USER_ALIAS"],
				"PERMISSION" => $arParams["PERMISSION"],
				"SECTION_ID" => $arParams["SECTION_ID"],
				"SECTION_CODE" => $arParams["SECTION_CODE"],
				"ELEMENT_ID" => $arParams["ELEMENT_ID"],
				"BEHAVIOUR" => $arParams["BEHAVIOUR"],
				"ACTION" => "EDIT",
				"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
				"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
				"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
				"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
				"SHOW_TAGS"	=>	$arParams["SHOW_TAGS"],
				"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
				"SET_STATUS_404" => $arParams["SET_STATUS_404"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
				"SET_TITLE" => "N",
				"ADD_CHAIN_ITEM" => "N",
				"SHOW_PUBLIC" => "N",
				"SHOW_APPROVE" => "N",
				"SHOW_TITLE" => "N",
				"SEARCH_URL" => $arParams["SEARCH_URL"],
				"~RESTART_BUFFER" => false
			),
			$component
		);
		?><!--BX_PHOTO_EDIT_RES_END--><?
	}
	die();
}
?>