<?$RIGHTS = $GLOBALS["APPLICATION"]->GetGroupRight("photogallery");
if ($RIGHTS > "D"):
	IncludeModuleLangFile(__FILE__);
	$arSights = array();
	$counter = 0;
	$bVarsFromForm = false;

	$arSights = @unserialize(COption::GetOptionString("photogallery", "pictures"), ['allowed_classes' => false]);
	if (!is_array($arSights))
		$arSights = array();

	$arLangs = array();
	$db_res = CLanguage::GetList();
	while($res = $db_res->Fetch())
		$arLangs[$res["LID"]] = $res;
	//*****************************************************************************************************************
	if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && ($RIGHTS>="W") && check_bitrix_sessid())
	{
		$arSights = array();
		if (is_array($_REQUEST["CODE"]))
		{
			foreach ($_REQUEST["CODE"] as $key => $val)
			{
				$val = mb_strtolower(trim($val));
				if (preg_match("/[0-9]/", mb_substr($val, 0, 1), $matches))
					continue;

				if (!empty($val) && intval($_REQUEST["SIZE"][$key]) > 0 && ($_REQUEST["DROP"][$key] != "Y"))
				{
					$_REQUEST["SIGHTS"][$key] = (empty($_REQUEST["SIGHTS"][$key]) ? $val : $_REQUEST["SIGHTS"][$key]);
					$arSights[] = array(
						"size" => intval($_REQUEST["SIZE"][$key]),
						"quality" => (intval($_REQUEST["QUALITY"][$key]) <= 0 ? 95 : intval($_REQUEST["QUALITY"][$key])),
						"title" => $_REQUEST["SIGHTS"][$key],
						"code" =>  $val);
				}
			}
		}
		COption::SetOptionString("photogallery", "pictures", serialize($arSights));

		if($apply!="")
			LocalRedirect("/bitrix/admin/settings.php?&lang=".LANG."&back_url=".urlencode($back_url)."&mid=photogallery");
		elseif($back_url)
			LocalRedirect($back_url);
		else
			LocalRedirect("/bitrix/admin/settings.php?lang=".LANG);
	}
	//*****************************************************************************************************************
?><form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=photogallery&lang=<?=LANGUAGE_ID?>" id="FORMACTION">
<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($back_url)?>" />
<?=bitrix_sessid_post()?><?

$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")));
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();

?>
<tr valign="top"z><td width="50%"><?=GetMessage("P_FONT")?>:</td><td width="50%"><?
$arFiles = array();
$path = str_replace(array("\\", "//"), "/", __DIR__."/fonts/");
CheckDirPath($path);
$handle = opendir($path);
$file_exist = false;
if ($handle)
{
	while($file = readdir($handle))
	{
		if ($file == "." || $file == ".." || !is_file($path.$file))
			continue;
		$file_exist = true;
		?><?=$file?><br/><?
	}
}
if (!$file_exist)
{
	?><?=GetMessage("P_FONT_IS_NOT_EXISTS")?><br /><?
}
?>
<a href="/bitrix/admin/fileman_file_upload.php?path=/bitrix/modules/photogallery/fonts/"><?=GetMessage("P_UPLOAD")?></a>
</td></tr>

<?/*
<tr class="heading"><td colspan="2"><?=GetMessage("P_PICTURES")?><sup>1</sup>:</td></tr>
<tr><td align="center" colspan="2">
	<table cellpadding="0" cellspacing="5">
		<tr class="heading">
			<td><?=GetMessage("P_TITLE")?></td>
			<td><?=GetMessage("P_CODE")?><sup>2</sup></td>
			<td><?=GetMessage("P_SIZE")?></td>
			<td><?=GetMessage("P_QUALITY")?></td>
			<td><?=GetMessage("P_DROP")?></td>
		</tr><?

foreach ($arSights as $key => $val):
	$counter++;
?><tr>
	<td style="text-align: center !important;"><input type="text" name="SIGHTS[<?=$counter?>]" value="<?=htmlspecialcharsbx($val["title"])?>" /></td>
	<td style="text-align: center !important;"><input type="text" name="CODE[<?=$counter?>]" value="<?=htmlspecialcharsbx($val["code"])?>" size="10" /></td>
	<td style="text-align: center !important;"><input type="text" name="SIZE[<?=$counter?>]" value="<?=htmlspecialcharsbx($val["size"])?>" size="10" /></td>
	<td style="text-align: center !important;"><input type="text" name="QUALITY[<?=$counter?>]" value="<?=htmlspecialcharsbx($val["quality"])?>" size="10" /></td>
	<td style="text-align: center !important;"><input type="checkbox" name="DROP[<?=$counter?>]" value="Y" /></td>
</tr><?
endforeach;
for ($ii = 0; $ii < 5; $ii++):
	$counter++;
?><tr>
	<td style="text-align: center !important;"><input type="text" name="SIGHTS[<?=$counter?>]" value="" /></td>
	<td style="text-align: center !important;"><input type="text" name="CODE[<?=$counter?>]" value="" size="10" /></td>
	<td style="text-align: center !important;"><input type="text" name="SIZE[<?=$counter?>]" value="" size="10" /></td>
	<td style="text-align: center !important;"><input type="text" name="QUALITY[<?=$counter?>]" value="95" size="10" /></td>
	<td style="text-align: center !important;"></td>
</tr><?
endfor;
?>
</table></td></tr><?*/
$tabControl->Buttons(
	array(
		"disabled"=>$RIGHTS<"W",
		"back_url"=>(empty($back_url) ? "settings.php?lang=".LANG : $back_url)));
$tabControl->End();
?></form>
<?=BeginNote();?>
<?=str_replace(
	array(
		"#FILEMAN_ADMIN#",
		"#FILEMAN_FILE_UPLOAD#"),
	array(
		"/bitrix/admin/fileman_admin.php?site=&path=/bitrix/modules/photogallery/fonts/",
		"/bitrix/admin/fileman_file_upload.php?path=/bitrix/modules/photogallery/fonts/"),
	Getmessage("P_FONTS_NOTE"))?>

<?=EndNote();?>
<?/*
<?=BeginNote();?>
<sup>1</sup> <?=GetMessage("P_PICTURES_HELP")?><br />
<sup>2</sup> <span class="required"><?=GetMessage("P_PICTURES_CODE_HELP")?></span><br />
<?=EndNote();?>
*/?>
<?endif;?>