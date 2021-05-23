<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('fileman_admin_folders'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$io = CBXVirtualIo::GetInstance();

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');
$strWarning = "";
$strNotice = "";
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = $io->CombinePath("/", $path);
$arPath = Array($site, $path);

if (!$USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;

$module_id = "fileman";
$bMenuTypeExists = false;
$arMenuTypes = Array();
$armt = GetMenuTypes($site);
foreach($armt as $key => $title)
{
	if(!$USER->CanDoFileOperation('fm_edit_existent_file',Array($site, $path."/.".$key.".menu.php")))
		continue;
	$arMenuTypes[] = array($key, $title);
	if($key == $menutype)
		$bMenuTypeExists = true;
}

//check folder access
if (!$USER->CanDoFileOperation('fm_create_new_folder',$arPath))
	$strWarning = '<img src="/bitrix/images/fileman/deny.gif" width="28" height="28" border="0" align="left" alt="">'.GetMessage("ACCESS_DENIED");
else if(!$io->DirectoryExists($abs_path))
	$strWarning = GetMessage("FILEMAN_FOLDER_NOT_FOUND");
else
{
	if($REQUEST_METHOD=="POST" && $save <> '' && check_bitrix_sessid())
	{
		if($foldername == '')
		{
			$strWarning = GetMessage("FILEMAN_NEWFOLDER_ENTER_NAME");
		}
		elseif (($mess = CFileMan::CheckFileName($foldername)) !== true)
		{
			$strWarning = $mess;
		}
		else
		{
			$pathto = $io->CombinePath("/", $path, $foldername);
			if($io->FileExists($DOC_ROOT.$pathto) || $io->DirectoryExists($DOC_ROOT.$pathto))
			{
				$strWarning = GetMessage("FILEMAN_NEWFOLDER_EXISTS");
			}
			else
			{
				$strWarning = CFileMan::CreateDir(Array($site, $pathto));
				if($strWarning == '')
				{
					if($USER->CanDoFileOperation('fm_add_to_menu',$arPath) &&
					$USER->CanDoOperation('fileman_add_element_to_menu') &&
					$mkmenu=="Y" && $bMenuTypeExists)
					{
						$arParsedPathTmp = CFileMan::ParsePath(Array($site, $pathto), true, false, "", $logical == "Y");
						$menu_path = $arParsedPathTmp["PREV"]."/.".$menutype.".menu.php";
						if($USER->CanDoFileOperation('fm_view_file',Array($site, $menu_path)))
						{
							$res = CFileMan::GetMenuArray($DOC_ROOT.$menu_path);
							$aMenuLinksTmp = $res["aMenuLinks"];
							$sMenuTemplateTmp = $res["sMenuTemplate"];
							$aMenuLinksTmp[] = Array($menuname, $arParsedPathTmp["PREV"]."/".$arParsedPathTmp["LAST"]."/", Array(), Array(), "");
							CFileMan::SaveMenu(Array($site, $menu_path), $aMenuLinksTmp, $sMenuTemplateTmp);

							if(COption::GetOptionString($module_id, "log_menu", "Y")=="Y")
							{
								$mt = COption::GetOptionString("fileman", "menutypes", $default_value, $site);
								$mt = unserialize(str_replace("\\", "", $mt), ['allowed_classes' => false]);
								$res_log['menu_name'] = $mt[$menutype];
								$res_log['path'] = mb_substr($path, 1);
								CEventLog::Log(
									"content",
									"MENU_EDIT",
									"fileman",
									"",
									serialize($res_log)
								);
							}
						}
					}

					if($sectionname <> '')
					{
						if(COption::GetOptionString($module_id, "log_page", "Y")=="Y")
						{
							$res_log['path'] = mb_substr($pathto, 1);
							CEventLog::Log(
								"content",
								"SECTION_ADD",
								"fileman",
								"",
								serialize($res_log)
							);
						}
						$APPLICATION->SaveFileContent($DOC_ROOT.$pathto."/.section.php", "<?\n\$sSectionName=\"".CFileMan::EscapePHPString($sectionname)."\";\n?>");
					}
					if ($e = $APPLICATION->GetException())
						$strNotice = $e->msg;
					else
					{
						if($USER->CanDoFileOperation('fm_create_new_file',$arPath) &&
						$USER->CanDoOperation('fileman_admin_files') &&
						$mkindex=="Y")
						{
							if($toedit=="Y")
								LocalRedirect("/bitrix/admin/fileman_html_edit.php?".$addUrl."&site=".$site."&template=".Urlencode($template)."&path=".UrlEncode($pathto)."&filename=index.php&new=Y".($back_url == '' ?"":"&back_url=".UrlEncode($back_url)).($gotonewpage == ''?"":"&gotonewpage=".UrlEncode($gotonewpage)).($backnewurl == ''?"":"&backnewurl=".UrlEncode($backnewurl)));
							else
								$APPLICATION->SaveFileContent($DOC_ROOT.$pathto."/index.php", CFileman::GetTemplateContent($template));
						}
					}
					if ($e = $APPLICATION->GetException())
						$strNotice = $e->msg;
					elseif ($apply == '' && $strNotice == '')
					{
						if($back_url <> '')
							LocalRedirect("/".ltrim($back_url, "/"));
						else
						{
							$arPathtoParsed = CFileMan::ParsePath(Array($site, $pathto), false, false, "", $logical == "Y");
							LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($arPathtoParsed["PREV"]));
						}
					}
				}
			}
		}
	}
	else
	{
		$mkindex="Y";
		$toedit="Y";
	}
}


foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => (($chainLevel["LINK"] <> '') ? $chainLevel["LINK"] : ""),
		)
	);
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_NEW_FOLDER_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strWarning == '')
	$filename = $arParsedPath["LAST"];

$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_BACK"),
		"LINK" => "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path),
		"ICON" => "btn_list"
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?CAdminMessage::ShowMessage($strNotice);?>
<?CAdminMessage::ShowMessage($strWarning);?>

<?
if ($USER->CanDoFileOperation('fm_create_new_folder',$arPath))
{
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fnew_folder">
	<input type="hidden" name="logical" value="<?=htmlspecialcharsex($logical)?>">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="site" value="<?= htmlspecialcharsex($site) ?>">
	<input type="hidden" name="path" value="<?= htmlspecialcharsex($path) ?>">
	<input type="hidden" name="save" value="Y">
	<input type="hidden" name="back_url" value="<?= htmlspecialcharsex($back_url)?>">
	<input type="hidden" name="lang" value="<?=LANG ?>">
	<input type="hidden" name="ID" value="<?= htmlspecialcharsex($ID)?>">
	<input type="hidden"  id="bxfm_linked" name="bxfm_linked" value="Y" />
	<?if($gotonewpage=="Y"):?><input type="hidden" name="gotonewpage" value="Y"><?endif?>
	<?if($backnewurl=="Y"):?><input type="hidden" name="backnewurl" value="Y"><?endif?>
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_TAB1"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_TAB1_ALT")),
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();

	$site_template = false;
	$rsSiteTemplates = CSite::GetTemplateList($site);
	while($arSiteTemplate = $rsSiteTemplates->Fetch())
	{
		if($arSiteTemplate["CONDITION"] == '')
		{
			$site_template = $arSiteTemplate["TEMPLATE"];
			break;
		}
	}
	$arTemplates = CFileman::GetFileTemplates(LANGUAGE_ID, array($site_template));
	?>
	<tr>
		<td><label for="bxfm_sectionname" style="font-weight: bold;"><?=GetMessage("FILEMAN_NEWFOLDER_SEACTION_NAME")?></label></td>
		<td><input type="text" id="bxfm_sectionname" name="sectionname" value="<?=htmlspecialcharsex($sectionname)?>" size="30" maxlength="255"></td>
	</tr>
	<tr>
		<td width="40%"><label for="bxfm_foldername" style="font-weight: bold;"><?=GetMessage("FILEMAN_NEWFOLDER_NAME")?></label></td>
		<td width="60%"><input id="bxfm_foldername" type="text" name="foldername" value="<?=htmlspecialcharsex($foldername)?>" size="30" maxlength="255"></td>
	</tr>

	<?if($USER->CanDoFileOperation('fm_add_to_menu',$arPath) && $USER->CanDoOperation('fileman_add_element_to_menu') ):?>

	<tr>
		<td><?=GetMessage("FILEMAN_NEWFOLDER_ADDMENU")?></td>
		<td><input type="checkbox" name="mkmenu" value="Y"<?if($mkmenu=="Y")echo " checked"?> onclick="document.fnew_folder.menuname.disabled=!this.checked;document.fnew_folder.menutype.disabled=!this.checked;if(this.checked && document.fnew_folder.sectionname.value.length!='' && document.fnew_folder.menuname.value=='') document.fnew_folder.menuname.value=document.fnew_folder.sectionname.value;fx1.disabled=!this.checked;fx2.disabled=!this.checked;"></td>
	</tr>
	<tr id="fx1"<?if($mkmenu!="Y")echo " disabled"?>>
		<td><?=GetMessage("FILEMAN_NEWFOLDER_MENU")?></td>
		<td>
			<select name="menutype" <?if($mkmenu!="Y")echo " disabled"?>>
				<?for($i = 0, $l = count($arMenuTypes); $i < $l; $i++):?>
				<option value="<?echo htmlspecialcharsex($arMenuTypes[$i][0])?>" <?if($menutype==$arMenuTypes[$i][0])echo " selected"?>><?echo htmlspecialcharsex("[".$arMenuTypes[$i][0]."] ".$arMenuTypes[$i][1])?></option>
				<?endfor;?>
			</select>
		</td>
	</tr>
	<tr id="fx2"<?if($mkmenu!="Y")echo " disabled"?>>
		<td><?=GetMessage("FILEMAN_NEWFOLDER_MENUITEM")?></td>
		<td><input type="text" name="menuname" value="<?echo htmlspecialcharsex($menuname)?>"<?if($mkmenu!="Y")echo " disabled"?>></td>
	</tr>
	<?endif;?>

	<?if($USER->CanDoFileOperation('fm_create_new_file',$arPath) && $USER->CanDoOperation('fileman_admin_files')):?>

	<tr>
		<td><?=GetMessage("FILEMAN_NEWFOLDER_MAKE_INDEX")?></td>
		<td><input type="checkbox" name="mkindex" value="Y"<?if($mkindex=="Y")echo " checked"?> onclick="document.fnew_folder.toedit.disabled=!this.checked;document.fnew_folder.template.disabled=!this.checked;ff1.disabled=!this.checked;ff2.disabled=!this.checked;"></td>
	</tr>
	<tr id="ff1">
		<td><?=GetMessage("FILEMAN_NEWFOLDER_INDEX_TEMPLATE")?></td>
		<td>
		<select name="template" <?if($mkindex!="Y")echo " disabled"?>>
			<?for($i = 0, $l = count($arTemplates); $i < $l; $i++):?>
			<option value="<?echo htmlspecialcharsex($arTemplates[$i]["file"])?>"<?if($template==$arTemplates[$i]["file"])echo " selected"?>><?echo htmlspecialcharsex($arTemplates[$i]["name"])?></option>
			<?endfor;?>
		</select>
		</td>
	</tr>
	<tr id="ff2">
		<td><?=GetMessage("FILEMAN_NEWFOLDER_INDEX_EDIT")?></td>
		<td><input type="checkbox" name="toedit" value="Y"<?if($toedit=="Y")echo " checked"?><?if($mkindex!="Y")echo " disabled"?>></td>
	</tr>
	<?endif;?>
	<?
	$tabControl->EndTab();
	$tabControl->Buttons(
		array(
			"disabled" => false,
			"back_url" => ($back_url <> '' ? $back_url : "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path))
		)
	);
	$tabControl->End();

	if (COption::GetOptionString("fileman", "use_translit", true))
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
		CFilemanTransliterate::Init(array(
			'fromInputId' => 'bxfm_sectionname',
			'toInputId' => 'bxfm_foldername',
			'linkedId' => 'bxfm_linked',
			'linked' => $_REQUEST['bxfm_linked'] != "N",
			'linkedTitle' => GetMessage('FILEMAN_TRANS_LINKED'),
			'unlinkedTitle' => GetMessage('FILEMAN_TRANS_UNLINKED')
		));
	}
	?>
	</form>
	<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
