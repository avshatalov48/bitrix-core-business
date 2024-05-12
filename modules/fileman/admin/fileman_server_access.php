<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_admin_folders'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");
IncludeModuleLangFile(__FILE__);

$site = CFileMan::__CheckSite($site);
$documentRoot = CSite::GetSiteDocRoot($site);

$io = CBXVirtualIo::GetInstance();

$path = $io->CombinePath("/", $path);

$addUrl = 'lang='.LANGUAGE_ID.($logical == "Y"?'&logical=Y':'');

$arPath = Array($site, $path);
$strNotice = "";
$strWarning = "";

$abs_path = $documentRoot.$path;

$arFiles = Array();

$bSearch = isset($_REQUEST['search']) && $_REQUEST['search'] == 'Y';
$searchSess = preg_replace("/[^a-z0-9]/i", "", $_REQUEST['ssess']);

if (CFileMan::IsWindows())
	$strWarning .= GetMessage("FILEMAN_SA_WINDOWS_WARN")."\n";

if (count($files) > 0)
{
	for($i = 0; $i < count($files); $i++)
	{
		if(!$USER->CanDoFileOperation('fm_edit_permission', Array($site, $path."/".$files[$i])))
			$strWarning .= GetMessage("FILEMAN_ACCESS_TO_DENIED")." \"".$files[$i]."\".\n";
		elseif($files[$i] != '.')
			$arFiles[] = $files[$i];
	}
}
else
{
	if ($bSearch)
	{
		$searchRes = CFilemanSearch::GetSearchResult($searchSess);
		for($i = 0, $l = count($searchRes); $i < $l; $i++)
			$arFiles[] = $searchRes[$i]['path'];
	}
	else
	{
		$arD = array();
		$arF = array();

		CFileMan::GetDirList(Array($site, $path), $arD, $arF, array("MIN_PERMISSION" => "X"), array(), "DF");
		foreach ($arD as $dir)
			if($USER->CanDoFileOperation('fm_edit_permission',Array($site, $dir['ABS_PATH'])))
				$arFiles[] = $dir["NAME"];

		foreach ($arF as $file)
			if($USER->CanDoFileOperation('fm_edit_permission',Array($site, $file['ABS_PATH'])))
				$arFiles[] = $file["NAME"];
	}
}

$filesCount = count($arFiles);
$arFilesEx = array();
$bFolderInList = false;
$currentValue = false;
$bCurrentValueDiff = false;

for($i = 0; $i < $filesCount; $i++)
{
	$arFile = array("NAME" => $arFiles[$i]);

	$arFile["PATH"] = $bSearch ? $arFiles[$i] : $path."/".$arFiles[$i];
	$arFile["ABS_PATH"] = $documentRoot.$arFile["PATH"];

	if (!$bFolderInList && $io->DirectoryExists($arFile["ABS_PATH"]))
		$bFolderInList = true;

	$arFile["PERM"] = CFileMan::GetUnixFilePermissions($arFile["ABS_PATH"]);

	if ($currentValue === false)
		$currentValue = $arFile["PERM"][0];

	if (!$bCurrentValueDiff && $currentValue != $arFile["PERM"][0])
		$bCurrentValueDiff = true;

	$arFilesEx[] = $arFile;
}

if ($REQUEST_METHOD == "POST" && $USER->CanDoOperation('fileman_admin_folders') && $_GET["fu_action"] == 'change_perms' && check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();

	if (CFileMan::IsWindows())
		$result_value = $_POST['readonly'] == "Y" ? '0' : '666';
	else
		$result_value = intval($_POST['res_value']);

	$result_value = (int) "0".$result_value;
	$oChmod = new CFilemanChmod;

	$oChmod->Init(array(
		'value' => $result_value,
		'lastPath' => isset($_POST['last_path']) ? $_POST['last_path'] : false
	));

	$bStoped = true;
	for($i = 0; $i < $filesCount; $i++)
	{
		$arFile = $arFilesEx[$i];
		if ($io->DirectoryExists($arFile['ABS_PATH']) && $_POST['recurcive'] == "Y")
		{
			$oDir = new CFilemanUtilDir($arFile['ABS_PATH'], array(
				'obj' => $oChmod,
				'site' => $Params['site'],
				'callBack' => "Chmod",
				'checkBreak' => "CheckBreak",
				'checkSubdirs' => true
			));

			$bSuccess = $oDir->Start();
			$bBreak = $oDir->bBreak;
			$nextPath = $oDir->nextPath;
			$bStoped = $oDir->bStoped;
		}
		else
		{
			$bBreak = $oChmod->CheckBreak();
			$bStoped = $i == $filesCount - 1; // Last iterration

			if ($bBreak && !$bStoped)
				$nextPath = $arFilesEx[$i]['ABS_PATH'];
		}

		if ($bStoped)
			$bBreak = false;

		if ($bBreak)
			break;

		$oChmod->Chmod($arFile['ABS_PATH']);
	}

	clearstatcache();
	?>
	<script>
	<?if ($bBreak):  // Execution breaks on timeout?>
		window.spBtimeout = true;
		window.spLastPath = '<?= CUtil::JSEscape(CFilemanUtils::TrimPath($nextPath))?>';
	<? else: ?>
		window.spBtimeout = false;
	<? endif; ?>

	window.spBstoped = <?= $bStoped ? 'true' : 'false'?>;
	window.spResult = <?= CUtil::PhpToJSObject($oChmod->Result)?>;
	</script>
	<?
	die();
}

$backToFolderUrl = "fileman_admin.php?".$addUrl."&site=".$site."&path=".UrlEncode($path).($bSearch ? "&search=Y&ssess=".$searchSess : "");

if ($bSearch)
{
	$adminChain->AddItem(array("TEXT" => GetMessage("FM_SA_SEARCH_RESULT"), "LINK" => $backToFolderUrl));
}
else
{
	$arParsedPath = CFileMan::ParsePath(Array($site, $path), true, false, "", $logical == "Y");
	foreach ($arParsedPath["AR_PATH"] as $chainLevel)
	{
		$adminChain->AddItem(
			array(
				"TEXT" => htmlspecialcharsex($chainLevel["TITLE"]),
				"LINK" => (($chainLevel["LINK"] <> '') ? $chainLevel["LINK"] : ""),
			)
		);
	}
}

$adminChain->AddItem(array("TEXT" => GetMessage("FILEMAN_SERV_ACCESS_TITLE"), "LINK" => ""));
$APPLICATION->SetTitle(GetMessage("FILEMAN_SERV_ACCESS_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
CFilemanUtils::InitScript(array(
	'initServerAccess' => true,
	'site' => $site
));

$aMenu = array(
	array(
		"TEXT" => $bSearch ? GetMessage("FILEMAN_SA_BACK_2_SEARCH_RES") : GetMessage("FILEMAN_SA_BACK_2_FOLDER"),
		"LINK" => $backToFolderUrl
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<?CAdminMessage::ShowMessage($strNotice);?>
<?CAdminMessage::ShowMessage($strWarning);?>

<?if($strWarning == ""):?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("FILEMAN_SA_TAB"), "ICON" => "fileman", "TITLE" => GetMessage("FILEMAN_SA_TAB_ALT"))
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<?$tabControl->BeginNextTab();?>

<tr>
	<td colspan="2">
		<div id="bxsp_note_success" style="display: none; margin: 15px 5px;">
		<?= BeginNote();?>
			<?= GetMessage("FM_SA_SUCCESS_NOTE")?>
		<?= EndNote();?>
		</div>
		<div id="bxsp_note_errors" class="bxsp-error-note" style="display: none; margin: 15px 5px;">
		<?= BeginNote();?>
			<?= GetMessage("FM_SA_ERRORS_NOTE")?>: <br/>
			<div id="bxsp_note_errors_cont" class="bxsp-file"></div>
		<?= EndNote();?>
		</div>

		<span style="padding: 5px 15px;"><?= GetMessage("FILEMAN_SA_CHANGE_TO");?>:</span><br />
		<table class="bxsp-file-list bxsp-file-list-init" id="bxsp_file_list">
			<tr class="bxsp-header">
				<td><?= GetMessage("FM_SA_FILE_PATH")?></td>
				<td></td>
				<td><?= GetMessage("FM_SA_CUR_VAL")?></td>
				<td class="bxsp-status"><?= GetMessage("FM_SA_CHANGE_STATUS")?></td>
			</tr>

		<?for($i = 0, $l = count($arFilesEx); $i < $l; $i++):?>
			<?
			if (CFileMan::IsWindows())
			{
				$html = $arFilesEx[$i]["PERM"][0] == '444' ? GetMessage("FM_SA_WIN_READONLY") : GetMessage("FM_SA_WIN_FULL_ACCESS");
				$title = $html;
			}
			else
			{
				$html = $arFilesEx[$i]["PERM"][0];
				$title = GetMessage("FILEMAN_SA_CUR_VAL").": ".$arFilesEx[$i]["PERM"][1];
			}
			?>
			<tr id="bxsp_file_row_<?= $i?>">
				<td class="bxsp-filename">"<?= htmlspecialcharsbx($APPLICATION->UnJSEscape($arFilesEx[$i]["PATH"]))?>"</td>
				<td class="bxsp-separator"> - </td>
				<td class="bxsp-value" title="<?= $title?>"><?= $html?></td>
				<td class="bxsp-status"><?= GetMessage("FM_SA_IN_PROC")?>...</td>
			</tr>
		<?endfor;?>
		</table>
	</td>
</tr>

<tr>
	<td colspan="2">
		<?$ar = array('owner', 'group', 'public');?>
		<span style="font-weight: bold; padding: 5px 15px;"><?= GetMessage("FM_SA_SET_NEW")?>:</b>
		<div class="bxfm-sperm-cont">
			<? foreach(array('owner', 'group', 'public') as $k):?>
			<div class="bx-s-perm-gr">
				<div class="bx-s-title"><?= GetMessage("FM_SA_".mb_strtoupper($k))?></div>
				<table class="bxsp-tbl"><tr>
					<td class="bxsp-inp-cell"><input  id="bxsp_<?= $k?>_read" type="checkbox" value="Y"/></td>
					<td class="bxsp-label-cell"><label for="bxsp_<?= $k?>_read"><?= GetMessage("FM_SA_READ")?></label></td>

					<td class="bxsp-inp-cell"><input  id="bxsp_<?= $k?>_write" type="checkbox" value="Y"/></td>
					<td class="bxsp-label-cell"><label for="bxsp_<?= $k?>_write"><?= GetMessage("FM_SA_WRITE")?></label></td>

					<td class="bxsp-inp-cell"><input id="bxsp_<?= $k?>_exec" type="checkbox" value="Y"/></td>
					<td class="bxsp-label-cell"><label for="bxsp_<?= $k?>_exec"><?= GetMessage("FM_SA_EXECUTE")?> </label></td>

					<td style="padding: 0 6px 0 80px !important;"><label for="bxsp_<?= $k?>_value"><?= GetMessage("FM_SA_VALUE")?>: </label></td>
					<td><input id="bxsp_<?= $k?>_value" type="text" readonly="readonly" size="2"/></td>
				</tr></table>
			</div>
			<?endforeach;?>

			<table class="bxsp-tbl-2">
				<tr>
					<td colSpan="2">
						<label for="bxsp_res_value" style="font-weight: bold;"><?= GetMessage("FM_SA_RES_VALUE")?>: </label>
						<input id="bxsp_res_value" type="text" value="" size="4" name="result_value" />
					</td>
				</tr>

				<?if ($bCurrentValueDiff):?>
				<tr id="bxsp_cur_val_diff">
					<td colSpan="4"><i style="color: #494949;"><?= GetMessage("FILEMAN_SA_CUR_VALUE_DIFF")?></i></td>
				</tr>
				<?endif;?>

				<?if ($bFolderInList):?>
				<tr>
					<td style="width: 20px;"><input name="recurcive" id="bxsp_recurcive" type="checkbox" value="Y" checked="checked"/></td>
					<td><label for="bxsp_recurcive"><?= GetMessage("FM_SA_SET_RECURCIVE")?></label></td>
				</tr>
				<?endif;?>
			</table>
		</div>
<script>
BX.ready(function()
{
	new BXFMServerPerm(
		{
			currentValue: "<?= $currentValue?>",
			arFiles: <?= CUtil::PhpToJSObject($arFilesEx)?>,
			lang: '<?= LANGUAGE_ID?>',
			site: '<?= CUtil::JSEscape($site)?>',
			sessid_get: '<?= bitrix_sessid_get()?>',
			path: '<?= CUtil::JSEscape($path)?>',
			backUrl: '<?= CUtil::JSEscape($backToFolderUrl)?>',
			bSearch: <?= $bSearch ? 'true' : 'false'?>,
			searchSess: "<?= CUtil::JSEscape($searchSess)?>"
		}
	);
});
</script>
	</td>
</tr>

<?$tabControl->EndTab();?>
<?$tabControl->Buttons(false);?>

<input type="button" id="bx_sp_save" value="<?= GetMessage("admin_lib_edit_save")?>" title="<?= GetMessage("admin_lib_edit_save_title")?>" />
<input type="button" id="bx_sp_apply" value="<?= GetMessage("admin_lib_edit_apply")?>" title="<?= GetMessage("admin_lib_edit_apply_title")?>" />
<input type="button" id="bx_sp_cancel" value="<?= GetMessage("admin_lib_edit_cancel")?>" title="<?= GetMessage("admin_lib_edit_cancel_title")?>" />

<?$tabControl->End();?>

<?endif;?>
<br>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>