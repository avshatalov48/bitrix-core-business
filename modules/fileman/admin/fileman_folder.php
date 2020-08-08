<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!($USER->CanDoOperation('fileman_edit_existent_folders') || $USER->CanDoOperation('fileman_admin_folders')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

ClearVars("g_");
$ind=0;

$strWarning = "";
$strNotice = "";

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$io = CBXVirtualIo::GetInstance();

$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);

$path = $io->CombinePath("/", $path);
$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
$abs_path = $DOC_ROOT.$path;
$arPath = Array($site, $path);

// let's check rights on this folder
if(!$USER->CanDoFileOperation('fm_edit_existent_folder',$arPath))
	$strWarning = GetMessage("ACCESS_DENIED");
else if(!$io->DirectoryExists($abs_path))
	$strWarning = GetMessage("FILEMAN_FOLDER_NOT_FOUND");
else
{
	function GetAccessArrTmp($path)
	{
		global $DOC_ROOT;

		$io = CBXVirtualIo::GetInstance();
		if($io->DirectoryExists($DOC_ROOT.$path))
		{
			@include($io->GetPhysicalName($DOC_ROOT.$path."/.access.php"));
			return $PERM;
		}
		return Array();
	}
	// let's get array of access rights for whole folder
	$CUR_PERM = GetAccessArrTmp($arParsedPath["PREV"]);

	if($REQUEST_METHOD=="POST" && $save <> '' && $propeditmore == '' && check_bitrix_sessid())
	{
		$bNeedSectionFile = False;

		$strSectionName = "";
		if($sectionname <> '')
		{
			$strSectionName = "\$sSectionName = \"".CFileMan::EscapePHPString($sectionname)."\";\n";
			$bNeedSectionFile = True;
		}

		$strDirProperties = "\$arDirProperties = array(\n";
		$numpropsvals = intval($numpropsvals);
		$bNeedComma = False;
		for($i = 0; $i<$numpropsvals; $i++)
		{
			if(Trim($_POST["CODE_".$i]) <> '' && Trim($_POST["VALUE_".$i]) <> '')
			{
				if($bNeedComma) $strDirProperties .= ",\n";
				$strDirProperties .= "   \"".CFileMan::EscapePHPString(Trim($_POST["CODE_".$i]))."\" => \"".CFileMan::EscapePHPString(Trim($_POST["VALUE_".$i]))."\"";
				$bNeedComma = True;
				$bNeedSectionFile = True;
			}
		}
		$strDirProperties .= "\n);\n";

		if($bNeedSectionFile)
			$APPLICATION->SaveFileContent($DOC_ROOT.$path."/.section.php", "<"."?\n".$strSectionName.$strDirProperties."?".">");
		else
			CFileman::DeleteFile(Array($site, $path."/.section.php"));

		if($USER->CanDoFileOperation('fm_edit_permission',$arPath))
		{
			$arPermissions=Array();
			$db_groups = CGroup::GetList($order="sort", $by="asc");
			while($arGroup = $db_groups->Fetch())
			{
				$gperm = isset($_POST["g_".$arGroup["ID"]]) ? $_POST["g_".$arGroup["ID"]] : '';
				if (!isset($gperm))
				{
					if($path=="")
						$gperm = $CUR_PERM["/"][$arGroup['ID']];
					else
						$gperm = $CUR_PERM[$arParsedPath["LAST"]][$arGroup['ID']];
				}
				else
				{
					if ($gperm == 'NOT_REF')
						$gperm = '';
					if (intval($gperm) > 0)
					{
						$z = CTask::GetById($gperm);
						$r = $z->Fetch();
						if ($r && $r['LETTER'] && $r['SYS'] == 'Y')
							$gperm = $r['LETTER'];
						else
							$gperm = 'T_'.$gperm;
					}
				}
					$arPermissions[$arGroup["ID"]] = $gperm;
			}
			//$gperm = $g_ALL;
			$gperm = $_POST['g_ALL'];
			if ($gperm == 'NOT_REF')
				$gperm = '';
			if (intval($gperm) > 0)
			{
				$z = CTask::GetById($gperm);
				$r = $z->Fetch();
				if ($r && $r['LETTER'] && $r['SYS'] == 'Y')
					$gperm = $r['LETTER'];
				else
					$gperm = 'T_'.$gperm;
			}
			$arPermissions["*"] = $gperm;
			$APPLICATION->SetFileAccessPermission(Array($site, $path), $arPermissions);
		}

		if ($e = $APPLICATION->GetException())
			$strNotice = $e->msg;
		else
		{
			if($apply == '')
			{
				if($back_url <> '')
					LocalRedirect("/".ltrim($back_url, "/"));
				else
					LocalRedirect("/bitrix/admin/fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
			}
			else
				LocalRedirect("/bitrix/admin/fileman_folder.php?".$addUrl."&site=".$site."&path=".UrlEncode($path));
		}
	}
}

if($propeditmore <> '') $bInitVars = True;

foreach ($arParsedPath["AR_PATH"] as $chainLevel)
{
	$adminChain->AddItem(
		array(
			"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
			"LINK" => (($chainLevel["LINK"] <> '') ? $chainLevel["LINK"] : ""),
		)
	);
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_FOLDER_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("FILEMAN_FOLDER_BACK"),
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
if($strWarning == ''):
	$sectionname = "";
	$arDirProperties = false;
	$sSectionName = "";
	if($io->FileExists($abs_path."/.section.php"))
	{
		@include($io->GetPhysicalName($abs_path."/.section.php"));
		$f_SECTIONNAME = $sSectionName;
	}

	if($bInitVars)
	{
		$f_SECTIONNAME = $_POST["sectionname"];
	}

?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fnew_folder">
<input type="hidden" name="logical" value="<?=htmlspecialcharsbx($logical)?>">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="save" value="Y">
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="site" value="<?echo $site?>">
<input type="hidden" name="back_url" value="<?echo htmlspecialcharsbx($back_url);?>">
<input type="hidden" name="path" value="<?echo htmlspecialcharsbx($path)?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_TAB"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_TAB_ALT")));
if ($USER->CanDoFileOperation('fm_edit_permission',$arPath))
	$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("FILEMAN_ACCESS"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_FOLDER_ACCESS"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td width="40%">
			<?echo GetMessage("FILEMAN_FOLDER_SECTION_NAME")?>
		</td>
		<td width="60%">
			<input type="text" name="sectionname" value="<?echo htmlspecialcharsbx($f_SECTIONNAME)?>" size="50" maxlength="255">
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("FILEMAN_FOLDER_FILEPROPS")?></td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<table border="0" cellspacing="1" cellpadding="3" class="internal">
				<tr class="heading">
					<td align="center"><?echo GetMessage("FILEMAN_FOLDER_PROPSCODE")?></td>
					<td align="center"><?echo GetMessage("FILEMAN_FOLDER_PROPSVAL")?></td>
				</tr>
				<?
				$arPropTypes = CFileMan::GetPropstypes($site);

				if(is_array($arDirProperties))
				{
					foreach ($arDirProperties as $f_CODE => $f_VALUE)
					{
						$ind++;
						$oldind++;
						if($bInitVars)
						{
							$f_CODE = $_POST["CODE_".$oldind];
							$f_VALUE = $_POST["VALUE_".$oldind];
						}

						$bPredefinedProperty = False;
						if(is_set($arPropTypes, $f_CODE))
						{
							$bPredefinedProperty = True;
							$f_CODE_NAME = $arPropTypes[$f_CODE];
							unset($arPropTypes[$f_CODE]);
						}
						?>
						<tr>
							<td>
								<?if($bPredefinedProperty):?>
									<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE);?>">
									<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE_NAME);?>" size="30" readonly style='background-color:#F1F1F1;'>
									<!--<?echo $f_CODE_NAME;?>-->
								<?else:?>
									<input type="text" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE);?>" size="30">
								<?endif;?>
							</td>
							<td>
								<input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_VALUE);?>" size="60">
							</td>
						</tr>
						<?
					}
				}

				$numpropsvals = intval($numpropsvals);
				$numnewpropsvals = $numpropsvals-$oldind;
				if($bInitVars && $numnewpropsvals>0)
				{
					for($i = 0; $i<$numnewpropsvals; $i++)
					{
						$oldind++;
						$f_CODE = $_POST["CODE_".$oldind];
						$f_VALUE = $_POST["VALUE_".$oldind];
						if($f_CODE == '') continue;

						$bPredefinedProperty = False;
						if(is_set($arPropTypes, $f_CODE))
						{
							$bPredefinedProperty = True;
							$f_CODE_NAME = $arPropTypes[$f_CODE];
							unset($arPropTypes[$f_CODE]);
						}

						$ind++;
						?>
						<tr>
							<td>
								<?if($bPredefinedProperty):?>
									<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE);?>">
									<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE_NAME);?>" size="30" readonly style='background-color:#F1F1F1;'>
									<!--<?echo $f_CODE_NAME;?>-->
								<?else:?>
									<input type="text" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE);?>" size="30">
								<?endif;?>
							</td>
							<td><input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_VALUE);?>" size="60"><?
								if($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)) && $f_VALUE == '')
								{
									?><br><small><b><?echo GetMessage("FILEMAN_FOLDER_CURVAL")?></b> <?echo htmlspecialcharsbx($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)));?></small><?
								}
								?></td>
						</tr>
						<?
					}
				}
				if(count($arPropTypes)>0 && is_array($arPropTypes))
				{
					foreach ($arPropTypes as $key => $value)
					{
						$ind++;
						$oldind++;
						$f_CODE = $key;
						$f_CODE_NAME = $value;
						$f_VALUE = "";
						?>
						<tr>
							<td>
								<input type="hidden" name="CODE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE);?>">
								<input type="text" name="CODE_NAME_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_CODE_NAME);?>" size="30" readonly style='background-color:#F1F1F1;'>
							</td>
							<td><input type="text" name="VALUE_<?echo $ind;?>" value="<?echo htmlspecialcharsbx($f_VALUE);?>" size="60"><?
								if($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)))
								{
									?><br><small><b><?echo GetMessage("FILEMAN_FOLDER_CURVAL")?></b> <?echo htmlspecialcharsbx($APPLICATION->GetDirProperty($f_CODE, Array($site, $path)));?></small><?
								}
								?></td>
						</tr>
						<?
					}
				}

				for($i=0; $i<2; $i++)
				{
					$ind++;
					$oldind++;
					?>
					<tr>
						<td>
							<input type="text" name="CODE_<?echo $ind;?>" value="" size="30">
						</td>
						<td>
							<input type="text" name="VALUE_<?echo $ind;?>" value="" size="60">
						</td>
					</tr>
					<?
				}
				?>
				<tr>
					<td colspan="2">
						<input type="hidden" name="numpropsvals" value="<?echo $ind+1; ?>">
						<input type="submit" name="propeditmore" value="<?echo GetMessage("FILEMAN_FOLDER_PROPSMORE")?>">
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?if($USER->CanDoFileOperation('fm_edit_permission',$arPath)):?>
	<?$tabControl->BeginNextTab();?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("FILEMAN_FOLDER_ACCESS")?>:</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<table border="0" cellspacing="1" cellpadding="2" width="100%" class="internal">
				<tr class="heading">
					<td valign="middle" align="center" nowrap>
						<?echo GetMessage("FILEMAN_FOLDER_ACCESS_GROUP")?>
					</td>
					<td valign="top" align="center" nowrap>
						<?echo GetMessage("FILEMAN_FOLDER_ACCESS_LEVEL")?>
					</td>
				</tr>
				<?
				$arPermTypes = Array();

				$res = CTask::GetList(Array('LETTER' => 'asc'), Array('MODULE_ID' => 'main','BINDING' => 'file'));
				while($arRes = $res->Fetch())
				{
					$name = '';
					if ($arRes['SYS'])
						$name = GetMessage(mb_strtoupper($arRes['NAME']));
					if ($name == '')
						$name = $arRes['NAME'];

					$arPermTypes[$arRes['ID']] = Array(
						'title' => $name,
						'letter' => $arRes['LETTER']
					);
				}
				$arPermTypes['NOT_REF'] = Array(
					'title' => GetMessage("FILEMAN_FOLDER_ACCESS_INHERIT"),
					'letter' => 'N'
				);

				//**** Inherit access level *******
				if($path=="/")
					$inh_perm = $CUR_PERM["/"]["*"];
				else
					$inh_perm = $CUR_PERM[$arParsedPath["LAST"]]["*"];

				if (mb_substr($inh_perm, 0, 2) == 'T_')
					$inh_taskId = intval(mb_substr($inh_perm, 2));
				elseif(mb_strlen($inh_perm) == 1)
					$inh_taskId = CTask::GetIdByLetter($inh_perm,'main','file');
				else
					$inh_taskId = 'NOT_REF';

				if ($inh_taskId != 'NOT_REF')
				{
					$z = CTask::GetById($inh_taskId);
					if (!($r = $z->Fetch()))
						$inh_taskId = 'NOT_REF';
				}
				// *****************************
				// If user can manage only subordinate groups
				if (false && $USER->CanDoOperation('edit_subordinate_users') && !$USER->CanDoOperation('edit_all_users'))
				{
					$arSubordGroups = Array();
					$arGroups = explode(',',$USER->GetGroups());
					for ($i = 0,$l = count($arGroups);$i < $l;$i++)
						$arSubordGroups = array_merge($arSubordGroups,CGroup::GetSubordinateGroups($arGroups[$i]));
					$arSubordGroups = array_values(array_unique($arSubordGroups));
					$hide_groups = '';
				}

				//for each groups
				$db_groups = CGroup::GetList($order="sort", $by="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
				while($db_groups->ExtractFields("g_")):
					if($g_ANONYMOUS=="Y")
						$anonym = $g_NAME;
					if($path=="/")
						$perm = $CUR_PERM["/"][$g_ID];
					else
						$perm = $CUR_PERM[$arParsedPath["LAST"]][$g_ID];

					if (mb_substr($perm, 0, 2) == 'T_')
						$taskId = intval(mb_substr($perm, 2));
					elseif(mb_strlen($perm) == 1)
						$taskId = CTask::GetIdByLetter($perm,'main','file');
					else
						$taskId = 'NOT_REF';

					if ($taskId != 'NOT_REF')
					{
						$z = CTask::GetById($taskId);
						if (!($r = $z->Fetch()))
							$taskId = 'NOT_REF';
					}
					//if(isset($arSubordGroups) && !in_array($g_ID,$arSubordGroups))
					//{
					//	$hidden_groups .= '<input type="hidden" name="g_'.$g_ID.'" value="'.$taskId.'">';
					//	continue;
					//}
				?>
				<tr>
					<td>
						[<a href="/bitrix/admin/group_edit.php?ID=<?=$g_ID?>&lang=<?=LANGUAGE_ID?>"><?=$g_ID?></a>]&nbsp;<?echo $g_NAME?>:
					</td>
					<td>
						<?
						// Inherit access level
						if ($inh_taskId == 'NOT_REF')
						{
							//Prev folder access level
							$pAr = $APPLICATION->GetFileAccessPermission(Array($site, $arParsedPath["PREV"]), Array($g_ID), true);
							if (count($pAr) > 0)
								$pr_taskId = $pAr[0];
							else
								$pr_taskId = 'NOT_REF';
						}
						else
							$pr_taskId = $inh_taskId;
						?>
						<select name="g_<?echo $g_ID?>" class="typeselect">
						<?
						foreach ($arPermTypes as $id => $ar):?>
							<option value="<?=$id?>"<?if($id == $taskId)echo" selected";?>>
							<?echo htmlspecialcharsbx($ar['title'])?>
							<?if($id=="NOT_REF")
								echo "[".$arPermTypes[$pr_taskId]['title']."]";?>
							</option>
						<?endforeach;?>
						</select>
					</td>
				</tr>
				<?endwhile;?>
				<tr>
					<td>
						<?echo GetMessage("FILEMAN_FOLDER_ACCESS_FOR_INHERIT")?>:
					</td>
					<td>
						<select name="g_ALL">
						<?
						foreach ($arPermTypes as $id => $ar):
						?>
							<option value="<?=$id?>"<?if($id == $inh_taskId) echo" selected";?>>
							<?echo htmlspecialcharsbx($ar['title'])?>
							</option>
						<?endforeach;?>
						</select>
					</td>
				</tr>
			</table></td>
	</tr>
<?endif; //if($USER->CanDoFileOperation('fm_edit_permission',$arPath)):?>

<?
$tabControl->EndTab();
$tabControl->Buttons(
	array(
		"disabled" => (!$USER->CanDoFileOperation('fm_edit_existent_folder',$arPath)),
		"back_url" => ($back_url <> '' ? $back_url : "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path))
	)
);
$tabControl->End();
?>
</form>

<?endif?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
