<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$blogModulePermissions = $APPLICATION->GetGroupRight("blog");
if ($blogModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/include.php");
IncludeModuleLangFile(__FILE__);

$errorMessage = "";
$bVarsFromForm = false;
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("BLBE_TAB_BLOG"), "ICON" => "blog", "TITLE" => GetMessage("BLBE_TAB_BLOG_DESCR")),
		array("DIV" => "edit2", "TAB" => GetMessage("BLBE_TAB_BLOG_PERMS"), "ICON" => "blog", "TITLE" => GetMessage("BLBE_TAB_BLOG_PERMS_DESCR")),
	);
$aTabs[] = $USER_FIELD_MANAGER->EditFormTab("BLOG_BLOG");

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = IntVal($ID);
if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $blogModulePermissions>="W" && check_bitrix_sessid())
{
	$arFields = array(
		"NAME" => $NAME,
		"DESCRIPTION" => $DESCRIPTION,
		"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
		"URL" => $_POST["URL"],
		//"REAL_URL" => $REAL_PATH,
		"OWNER_ID" => $OWNER_ID,
		"GROUP_ID" => $GROUP_ID,
		"ACTIVE" => (($ACTIVE == "Y") ? "Y" : "N"),
		"ENABLE_COMMENTS" => (($ENABLE_COMMENTS == "Y") ? "Y" : "N"),
		"ENABLE_IMG_VERIF" => (($ENABLE_IMG_VERIF == "Y") ? "Y" : "N"),
		"EMAIL_NOTIFY" => (($EMAIL_NOTIFY == "Y") ? "Y" : "N"),
		"ENABLE_RSS" => (($ENABLE_RSS == "Y") ? "Y" : "N"),
		"SEARCH_INDEX" => (($SEARCH_INDEX == "Y") ? "Y" : "N"),
		"USE_SOCNET" => (($USE_SOCNET == "Y") ? "Y" : "N"),
		"PERMS_POST" => $PERMS_P,
		"PERMS_COMMENT" => $PERMS_C,
		"EDITOR_USE_FONT" => (($EDITOR_USE_FONT == "Y") ? "Y" : "N"),
		"EDITOR_USE_LINK" => (($EDITOR_USE_LINK == "Y") ? "Y" : "N"),
		"EDITOR_USE_IMAGE" => (($EDITOR_USE_IMAGE == "Y") ? "Y" : "N"),
		"EDITOR_USE_VIDEO" => (($EDITOR_USE_VIDEO == "Y") ? "Y" : "N"),
		"EDITOR_USE_FORMAT" => (($EDITOR_USE_FORMAT == "Y") ? "Y" : "N"),
	);

	if(!IsModuleInstalled("socialnetwork"))
		unset($arFields["USE_SOCNET"]);

	if(IntVal($OWNER_ID) > 0)
		$arFields["OWNER_ID"] = IntVal($OWNER_ID);
	else
		$arFields["OWNER_ID"] = false;	
	
	$USER_FIELD_MANAGER->EditFormAddFields("BLOG_BLOG", $arFields);
	if ($ID > 0)
	{
		$dbBlog = CBlog::GetList(
				array(),
				array("ID" => $ID),
				false,
				false,
				array("ID", "GROUP_SITE_ID", "GROUP_ID")
			);
		$arBlogOld = $dbBlog->Fetch();
		$result = CBlog::Update($ID, $arFields);
	}
	else
	{
		$arFields["=DATE_CREATE"] = $DB->CurrentTimeFunction();

		$ID = CBlog::Add($arFields);
		$ID = IntVal($ID);
		$result = ($ID > 0);
		$dbBlog = CBlog::GetList(
				array(),
				array("ID" => $ID),
				false,
				false,
				array("ID", "GROUP_SITE_ID", "GROUP_ID")
			);
		$arBlogOld = $dbBlog->Fetch();
	}

	if(CModule::IncludeModule("socialnetwork"))
	{
		if($arFields["USE_SOCNET"] == "Y")
		{
			$bRights = false;
			$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arFields["OWNER_ID"], "blog", "view_post");
			if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
				$bRights = true;
			if($bRights)
				CBlog::AddSocnetRead($ID);
			else
			{
				if(CBlog::GetSocnetReadByBlog($ID))
					CBlog::DeleteSocnetRead($ID);
			}
		}
		else
		{
			if(CBlog::GetSocnetReadByBlog($ID))
				CBlog::DeleteSocnetRead($ID);
		}
	}


	if (!$result)
	{
		$bVarsFromForm = true;
		if ($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString()."<br />";
		else
			$errorMessage .= GetMessage("BLBE_SAVE_ERROR")."<br />";
	}

	if (strlen($errorMessage) <= 0)
	{
		if (!empty($arBlogOld))
		{
			if($arBlogOld["GROUP_ID"] != $arFields["GROUP_ID"])
			{
				$arBlogGroupCur = CBlogGroup::GetByID($arFields["GROUP_ID"]);
				if($arBlogOld["GROUP_SITE_ID"] != $arBlogGroupCur["SITE_ID"])
				{
					BXClearCache(True, "/".$arBlogGroupCur["SITE_ID"]."/blog/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_blogs/");
					BXClearCache(True, "/".SITE_ID."/blog/new_blogs/");
				}
			}
			BXClearCache(True, "/".$arBlogOld["GROUP_SITE_ID"]."/blog/");
		}


		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/blog_blog.php?lang=".LANG.GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/blog_blog_edit.php?lang=".LANG."&ID=".$ID."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("BLBE_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("BLBE_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

ClearVars("str_");

$dbBlog = CBlog::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array("ID", "NAME", "DESCRIPTION", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "URL", "REAL_URL", "GROUP_ID", "ENABLE_COMMENTS", "ENABLE_IMG_VERIF", "ENABLE_RSS", "LAST_POST_ID", "LAST_POST_DATE", "EMAIL_NOTIFY", "SEARCH_INDEX", "USE_SOCNET", "EDITOR_USE_FONT", "EDITOR_USE_LINK", "EDITOR_USE_IMAGE", "EDITOR_USE_FORMAT", "EDITOR_USE_VIDEO")
	);
if (!$dbBlog->ExtractFields("str_"))
	$ID = 0;
	
if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_blog", "", "str_");
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("BLBE_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/blog_blog.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && $blogModulePermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("BLBE_NEW_BLOG"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/blog_blog_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("BLBE_DELETE_BLOG"), 
			"ICON" => "btn_delete",
			"LINK" => "javascript:if(confirm('".GetMessage("BLBE_DELETE_BLOG_CONFIRM")."')) window.location='/bitrix/admin/blog_blog.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
			"WARNING" => "Y"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="form1" ENCTYPE="multipart/form-data">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>

	<?if ($ID > 0):?>
		<tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr>
	<?endif;?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("BLBE_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="50" value="<?= ($str_NAME) ?>">
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("BLBE_DESCRIPTION")?>:</td>
		<td>
			<textarea name="DESCRIPTION" rows="5" cols="40"><?= ($str_DESCRIPTION) ?></textarea>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td valign="top"><?echo GetMessage("BLBE_URL")?>:<br><small><?echo GetMessage("BLBE_URL_HINT")?></small></td>
		<td valign="top">
			<input type="text" name="URL" size="50" value="<?= ($str_URL) ?>">
		</td>
	</tr>
	<tr>
		<td><label for="ACTIVE"><?echo GetMessage("BLBE_ACTIVE")?>:</label></td>
		<td>
			<input type="checkbox" name="ACTIVE" id="ACTIVE" value="Y"<?if ($str_ACTIVE == "Y") echo " checked";?>>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("BLBE_OWNER_ID")?>:</td>
		<td>
			<?echo FindUserID("OWNER_ID", IntVal($str_OWNER_ID));?>
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("BLBE_GROUP")?>:</td>
		<td>
			<select name="GROUP_ID" style="width:220px">
				<?
				$dbBlogGroup = CBlogGroup::GetList(
					array("NAME" => "ASC"),
					array()
				);
				while ($arBlogGroup = $dbBlogGroup->Fetch())
				{
					?><option value="<?= $arBlogGroup["ID"] ?>"<?if (IntVal($str_GROUP_ID) == IntVal($arBlogGroup["ID"])) echo " selected";?>>[<?= htmlspecialcharsbx($arBlogGroup["SITE_ID"]) ?>] <?= htmlspecialcharsbx($arBlogGroup["NAME"]) ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="ENABLE_COMMENTS"><?echo GetMessage("BLBE_ENABLE_COMMENTS")?>:</label></td>
		<td>
			<input type="checkbox" name="ENABLE_COMMENTS" id="ENABLE_COMMENTS" value="Y"<?if ($str_ENABLE_COMMENTS == "Y") echo " checked";?>>
		</td>
	</tr>
	<tr>
		<td><label for="ENABLE_IMG_VERIF"><?echo GetMessage("BLBE_ENABLE_IMG_VERIF")?>:</label></td>
		<td>
			<input type="checkbox" name="ENABLE_IMG_VERIF" id="ENABLE_IMG_VERIF" value="Y"<?if ($str_ENABLE_IMG_VERIF == "Y") echo " checked";?>>
		</td>
	</tr>
	<tr>
		<td><label for="ENABLE_RSS"><?echo GetMessage("BLBE_ENABLE_RSS")?>:</label></td>
		<td>
			<input type="checkbox" name="ENABLE_RSS" id="ENABLE_RSS" value="Y"<?if ($str_ENABLE_RSS == "Y") echo " checked";?>>
		</td>
	</tr>
	<tr>
		<td><label for="EMAIL_NOTIFY"><?echo GetMessage("BLBE_EMAIL_NOTIFY")?>:</label></td>
		<td>
			<input type="checkbox" name="EMAIL_NOTIFY" id="EMAIL_NOTIFY" value="Y"<?if ($str_EMAIL_NOTIFY == "Y") echo " checked";?>>
		</td>
	</tr>
	<tr>
		<td><span class="required"><sup>1</sup></span><label for="SEARCH_INDEX"><?echo GetMessage("BLBE_SEARCH_INDEX")?>:</label></td>
		<td>
			<input type="checkbox" name="SEARCH_INDEX" id="SEARCH_INDEX" value="Y"<?if ($str_SEARCH_INDEX == "Y") echo " checked";?>>
		</td>
	</tr>
	<?if(IsModuleInstalled("socialnetwork")):?>
	<tr>
		<td><label for="USE_SOCNET"><?echo GetMessage("BLBE_USE_SOCNET")?>:</label></td>
		<td>
			<input type="checkbox" name="USE_SOCNET" id="USE_SOCNET" value="Y"<?if ($str_USE_SOCNET == "Y") echo " checked";?>>
		</td>
	</tr>
	
<!--editor options-->
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("BLBE_EDITOR_SETTINGS")?>:</td>
	</tr>
		<tr>
			<td><label for="EDITOR_USE_FONT"><?echo GetMessage("BLBE_EDITOR_USE_FONT")?>:</label></td>
			<td>
				<input type="checkbox" name="EDITOR_USE_FONT" id="EDITOR_USE_FONT" value="Y"<?if ($str_EDITOR_USE_FONT == "Y") echo " checked";?>>
			</td>
		</tr>
		<tr>
			<td><label for="EDITOR_USE_LINK"><?echo GetMessage("BLBE_EDITOR_USE_LINK")?>:</label></td>
			<td>
				<input type="checkbox" name="EDITOR_USE_LINK" id="EDITOR_USE_LINK" value="Y"<?if ($str_EDITOR_USE_LINK == "Y") echo " checked";?>>
			</td>
		</tr>
		<tr>
			<td>
				<?= ShowJSHint(GetMessage("BLBE_EDITOR_USE_FORMAT_HINT")) ?>
				<label for="EDITOR_USE_FORMAT"><?echo GetMessage("BLBE_EDITOR_USE_FORMAT")?>:</label></td>
			<td>
				<input type="checkbox" name="EDITOR_USE_FORMAT" id="EDITOR_USE_FORMAT" value="Y"<?if ($str_EDITOR_USE_FORMAT == "Y") echo " checked";?>>
			</td>
		</tr>
		<tr>
			<td><label for="EDITOR_USE_IMAGE"><?echo GetMessage("BLBE_EDITOR_USE_IMAGE_AND_FILES")?>:</label></td>
			<td>
				<input type="checkbox" name="EDITOR_USE_IMAGE" id="EDITOR_USE_IMAGE" value="Y"<?if ($str_EDITOR_USE_IMAGE == "Y") echo " checked";?>>
			</td>
		</tr>
		<tr>
			<td>
				<?= ShowJSHint(GetMessage("BLBE_EDITOR_USE_VIDEO_HINT")) ?>
				<label for="EDITOR_USE_VIDEO"><?echo GetMessage("BLBE_EDITOR_USE_VIDEO")?>:</label></td>
			<td>
				<input type="checkbox" name="EDITOR_USE_VIDEO" id="EDITOR_USE_VIDEO" value="Y"<?if ($str_EDITOR_USE_VIDEO == "Y") echo " checked";?>>
			</td>
		</tr>
	<?endif;?>
<?
$tabControl->BeginNextTab();
?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("BLBE_P_POST")?>:</td>
	</tr>

	<?
	if ($ID > 0)
	{
		$arGroupPerms = array();
		$dbGroupPerms = CBlogUserGroupPerms::GetList(array(), array("BLOG_ID" => $ID, "PERMS_TYPE" => BLOG_PERMS_POST, "POST_ID" => 0));
		while ($arGroupPerm = $dbGroupPerms->Fetch())
		{
			$arGroupPerms[IntVal($arGroupPerm["USER_GROUP_ID"])] = $arGroupPerm["PERMS"];
		}
	}
	?>
	<tr>
		<td width="40%"><?echo GetMessage("BLBE_P_ALL")?>:</td>
		<td width="60%">
			<select name="PERMS_P[1]">
			<?
			foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
			{
				if (in_array($key, $GLOBALS["AR_BLOG_POST_PERMS"]))
				{
					?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_P) && array_key_exists(1, $PERMS_P) && $PERMS_P[1] == $key || !$bVarsFromForm && is_array($arGroupPerms) && array_key_exists(1, $arGroupPerms) && $arGroupPerms[1] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
				}
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLBE_P_AUTH")?>:</td>
		<td>
			<select name="PERMS_P[2]">
			<?
			foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
			{
				if (in_array($key, $GLOBALS["AR_BLOG_POST_PERMS"]))
				{
					?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_P) && array_key_exists(2, $PERMS_P)  && $PERMS_P[2] == $key || !$bVarsFromForm && is_array($arGroupPerms) && array_key_exists(2, $arGroupPerms) && $arGroupPerms[2] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
				}
			}
			?>
			</select>
		</td>
	</tr>
	<?
	if(IntVal($ID) > 0)
	{
		$dbGroups = CBlogUserGroup::GetList(array("NAME" => "ASC"), array("BLOG_ID" => $ID));
		while ($arGroup = $dbGroups->Fetch())
		{
			?>
			<tr>
				<td><?= htmlspecialcharsbx($arGroup["NAME"]) ?>:</td>
				<td>
					<select name="PERMS_P[<?= IntVal($arGroup["ID"]) ?>]">
					<?
					foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
					{
						if (in_array($key, $GLOBALS["AR_BLOG_POST_PERMS"]))
						{
							?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_P) && is_array($PERMS_P) && array_key_exists($arGroup["ID"], $PERMS_P)  && $PERMS_P[$arGroup["ID"]] == $key || !$bVarsFromForm  && is_array($arGroupPerms) && array_key_exists($arGroup["ID"], $arGroupPerms) && $arGroupPerms[$arGroup["ID"]] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
						}
					}
					?>
					</select>
				</td>
			</tr>
			<?
		}
	}
	?>

	<tr class="heading">
		<td colspan="2"><?echo GetMessage("BLBE_P_COMMENT")?>:</td>
	</tr>

	<?
	if ($ID > 0)
	{
		$arGroupPerms = array();
		$dbGroupPerms = CBlogUserGroupPerms::GetList(array(), array("BLOG_ID" => $ID, "PERMS_TYPE" => BLOG_PERMS_COMMENT, "POST_ID" => 0));
		while ($arGroupPerm = $dbGroupPerms->Fetch())
		{
			$arGroupPerms[IntVal($arGroupPerm["USER_GROUP_ID"])] = $arGroupPerm["PERMS"];
		}
	}
	?>
	<tr>
		<td><?echo GetMessage("BLBE_P_ALL");?>:</td>
		<td>
			<select name="PERMS_C[1]">
			<?
			foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
			{
				if (in_array($key, $GLOBALS["AR_BLOG_COMMENT_PERMS"]))
				{
					?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_C) && array_key_exists(1, $PERMS_C) && $PERMS_C[1] == $key || !$bVarsFromForm && is_array($arGroupPerms) && array_key_exists(1, $arGroupPerms) && $arGroupPerms[1] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
				}
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("BLBE_P_AUTH")?>:</td>
		<td>
			<select name="PERMS_C[2]">
			<?
			foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
			{
				if (in_array($key, $GLOBALS["AR_BLOG_COMMENT_PERMS"]))
				{
					?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_C) && array_key_exists(2, $PERMS_C)  && $PERMS_C[2] == $key || !$bVarsFromForm && is_array($arGroupPerms) && array_key_exists(2, $arGroupPerms) && $arGroupPerms[2] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
				}
			}
			?>
			</select>
		</td>
	</tr>
	<?
	if(IntVal($ID) > 0)
	{
		$dbGroups = CBlogUserGroup::GetList(array("NAME" => "ASC"), array("BLOG_ID" => $ID));
		while ($arGroup = $dbGroups->Fetch())
		{
			?>
			<tr>
				<td><?= htmlspecialcharsbx($arGroup["NAME"]) ?>:</td>
				<td>
					<select name="PERMS_C[<?= IntVal($arGroup["ID"]) ?>]">
					<?
					foreach($GLOBALS["AR_BLOG_PERMS"] as $key => $val)
					{
						if (in_array($key, $GLOBALS["AR_BLOG_COMMENT_PERMS"]))
						{
							?><option value="<?echo $key ?>"<?if ($bVarsFromForm && is_array($PERMS_C) && array_key_exists($arGroup["ID"], $PERMS_C)  && $PERMS_C[$arGroup["ID"]] == $key || !$bVarsFromForm && is_array($arGroupPerms) && array_key_exists($arGroup["ID"], $arGroupPerms) && $arGroupPerms[$arGroup["ID"]] == $key) echo " selected"?>><?echo htmlspecialcharsex($val) ?></option><?
						}
					}
					?>
					</select>
				</td>
			</tr>
			<?
		}
	}
	?>
<?
$tabControl->BeginNextTab();
$USER_FIELD_MANAGER->EditFormShowTab("BLOG_BLOG", $bVarsFromForm, $ID);
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
		array(
				"disabled" => ($blogModulePermissions < "W"),
				"back_url" => "/bitrix/admin/blog_blog.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
?>

<?
$tabControl->End();
?>

</form>

<?echo BeginNote();?>
<span class="required"><sup>1</sup></span> - <?echo GetMessage("BLBE_SEARCH_INDEX_HINT")?>
<?echo EndNote(); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>