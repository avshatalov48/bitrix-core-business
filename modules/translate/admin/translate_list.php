<?
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");
$TRANS_RIGHT = $APPLICATION->GetGroupRight("translate");
if ($TRANS_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('translate');
IncludeModuleLangFile(__FILE__);

@set_time_limit(0);
$sTableID = "tbl_translate_list";
$lAdmin = new CAdminList($sTableID);

function GetPhraseCounters($arCommon, $path, $key)
{
	global $arCommonCounter, $Counter, $arTLangs;
	$Counter++;
	$arDirFiles = array();

	// is directori
	if (is_dir(prepare_path($_SERVER["DOCUMENT_ROOT"]."/".$path."/")))
	{
		if (is_lang_dir($path))
		{
			if (is_array($arTLangs))
			{
				// files array for directory language
				foreach ($arTLangs as $lng)
				{
					$path = replace_lang_id($path, $lng);
					$path_l = strlen($path);

					foreach($arCommon as $arr)
					{
						if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
						{
							$arDirFiles[] = $arr["PATH"];
						}
					}
				}
			}
		}
		else
		{
			if (is_array($arCommon))
			{
				$path_l = strlen($path);
				// array files for directory
				foreach ($arCommon as $arr)
				{
					if($arr["IS_DIR"]=="N" && (strncmp($arr["PATH"], $path, $path_l) == 0))
					{
						$arDirFiles[] = $arr["PATH"];
					}
				}
			}
		}
	}
	else
	{
		foreach ($arTLangs as $lng)
			$arDirFiles[] = replace_lang_id($path, $lng);
	}

	$arFilesLng = array();
	// array for every files
	foreach ($arDirFiles as $file)
	{
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$file) && preg_match("#/lang/(.*?)/#", $file, $arMatch))
		{
			$file_lang = $arMatch[1];
			if(isset($arTLangs[$file_lang]))
			{
				if(substr($file, -3) != "php")
					continue;
				/** @global array $MESS */
				$MESS_tmp = $MESS;
				$MESS = array();
				include($_SERVER["DOCUMENT_ROOT"].$file);
				$file_name = remove_lang_id($file, $arTLangs);
				$arFilesLng[$file_name][$file_lang] = array_keys($MESS);
				$MESS = $MESS_tmp;
			}
		}
	}

	$arFilesLngCounter = array();
	//rashogdenia for files
	foreach($arFilesLng as $file => $arLns)
	{
		$total_arr = array();

		//summa
		foreach($arLns as $ln => $arLn)
		{
			$total_arr = array_merge($total_arr, $arLn);
		}
		$total_arr = array_unique($total_arr);
		$total = sizeof($total_arr);

		foreach($arTLangs as $lang)
		{
			$arr = array();
			$arLn = is_array($arLns[$lang]) ? $arLns[$lang] : array();
			$diff_arr = array_diff($total_arr, $arLn);
			$arr["TOTAL"] = $total;
			$diff = sizeof($diff_arr);
			$arr["DIFF"] = $diff;
			$arFilesLngCounter[$file][$lang] = $arr;
		}
	}

	foreach($arFilesLngCounter as $file => $arCount)
	{
		foreach($arCount as $ln => $arLn)
		{
			$file_path = str_replace("/lang/", "/lang/".$ln."/", $file);
			$arCommonCounter[$key][$ln][$file_path]["TOTAL"] += $arLn["TOTAL"];
			$arCommonCounter[$key][$ln][$file_path]["DIFF"] += $arLn["DIFF"];
		}
	}
}

$request = Main\Context::getCurrent()->getRequest();

$arCSVMessage = false;
$arSearchParam = false;
if ($request->isPost() && check_bitrix_sessid())
{
	if (array_key_exists('upload_csv', $_POST))
	{
		if (SaveTCSVFile())
		{
			$arCSVMessage = array('TYPE' => 'OK', 'MESSAGE' => GetMessage('TR_CSV_UPLOAD_OK'));
		}
		else
		{
			$ex = $APPLICATION->GetException();
			$arCSVMessage = array('TYPE' => 'ERROR', 'MESSAGE' => $ex->GetString());
		}
	}
	elseif (array_key_exists('tr_search', $_REQUEST))
	{
		if (isset($_REQUEST['replace_oper']) && $_REQUEST['replace_oper'] == 'Y')
		{
			$arSearchParam['is_replace'] = true;
			$arSearchParam['search'] = trim($_REQUEST['search_phrase2']);
			$arSearchParam['replace'] = trim($_REQUEST['replace_phrase2']);
			$arSearchParam['bSubFolders'] = isset($_REQUEST['search_subfolders2']) && $_REQUEST['search_subfolders2'] == 'Y';
			$arSearchParam['bSearchMessage'] = isset($_REQUEST['search_message2']) && $_REQUEST['search_message2'] == 'Y';
			$arSearchParam['bSearchMnemonic'] = isset($_REQUEST['search_mnemonic2']) && $_REQUEST['search_mnemonic2'] == 'Y';
			$arSearchParam['bCaseSens'] = isset($_REQUEST['search_case_sens2']) && $_REQUEST['search_case_sens2'] == 'Y';
		}
		else
		{
			$arSearchParam['is_replace'] = false;
			$arSearchParam['search'] = trim($_REQUEST['search_phrase']);
			$arSearchParam['replace'] = '';
			$arSearchParam['bSubFolders'] = isset($_REQUEST['search_subfolders']) && $_REQUEST['search_subfolders'] == 'Y';
			$arSearchParam['bSearchMessage'] = isset($_REQUEST['search_message']) && $_REQUEST['search_message'] == 'Y';
			$arSearchParam['bSearchMnemonic'] = isset($_REQUEST['search_mnemonic']) && $_REQUEST['search_mnemonic'] == 'Y';
			$arSearchParam['bCaseSens'] = isset($_REQUEST['search_case_sens']) && $_REQUEST['search_case_sens'] == 'Y';
		}
		if (empty($arSearchParam['search']))
			$arSearchParam = false;
	}
}

$SHOW_DIFF_GET = false;
$AUTO_CALCULATE = (string)Main\Config\Option::get('translate', 'AUTO_CALCULATE') == 'Y';
if (!$AUTO_CALCULATE && isset($_GET['SHOW_DIFF']))
{
	$SHOW_DIFF_GET = ($_GET['SHOW_DIFF'] == 'Y');
	$_SESSION['BX_SHOW_LANG_DIFF'] = $SHOW_DIFF_GET;
}

$SHOW_LANG_DIFF = $AUTO_CALCULATE || $SHOW_DIFF_GET || (isset($_SESSION['BX_SHOW_LANG_DIFF']) && $_SESSION['BX_SHOW_LANG_DIFF']);
$GET_SUBFOLRERS = $SHOW_LANG_DIFF || ($arSearchParam && $arSearchParam['bSubFolders']);
if ($arSearchParam)
{
	$AUTO_CALCULATE = false;
	$SHOW_LANG_DIFF = false;
}
$arLangCounters = array();
$arCommonCounter = array();
//$arTLangs = GetTLangList();
$arTLangs = array();
$ln = CLanguage::GetList($o, $b, Array("ACTIVE"=>"Y"));
while($lnr = $ln->Fetch())
	$arTLangs[$lnr["LID"]] = $lnr["LID"];

$path = $_REQUEST["path"];
$go_path = $_REQUEST["go_path"];

//button going
if(strlen($go_path)>0 && !preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
	$path = add_lang_id($go_path, reset($arTLangs), $arTLangs);

if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
	$path = "";
//no path
if (strlen($path)<=0)
	$path = TRANSLATE_DEFAULT_PATH;

$path = Rel2Abs("/", "/".$path."/");
if (!isAllowPath($path))
	$path = TRANSLATE_DEFAULT_PATH;

$arLangDirFiles = array();
$arFiles = array();
$arDirs = array();
$arLangDirs = array();
$IS_LANG_DIR = false;

$go_path = remove_lang_id($path, $arTLangs);

$IS_LANG_DIR = is_lang_dir($path);
//no lang
if ($IS_LANG_DIR)
{
	foreach ($arTLangs as $hlang)
	{
		$ph = add_lang_id($path, $hlang, $arTLangs);
		if (strlen($ph)>0) GetTDirList($ph, $GET_SUBFOLRERS);
		$ph = "";
	}
}
else
{
	GetTDirList($path, $GET_SUBFOLRERS);
}

$arrChain = array();
$arr = explode("/",$go_path);
if (is_array($arr))
{
	$arrP = array();
	TrimArr($arr);
	foreach($arr as $d)
	{
		$arrP[] = $d;
		$p = prepare_path("/".implode("/",$arrP)."/");
		if (remove_lang_id($path, $arTLangs)==$p) $p="";
		$arrChain[] = array("NAME" => $d, "PATH" => $p);
	}
}

$show_error = COption::GetOptionString("translate", "ONLY_ERRORS");
$show_error = ($show_error=="Y") ? "Y" : "";

GetLangDirs($arDirs, $SHOW_LANG_DIFF);

$arLangDirFiles = array_merge($arLangDirs, $arFiles);

// find
if ($arSearchParam)
{
	$_arLangDirFiles = $arLangDirFiles;
	$arLangDirFiles = array();
	foreach ($_arLangDirFiles as $_k => $_v)
	{
		if ($_v['IS_DIR'] == 'Y')
			continue ;
		if ($_v['LANG'] != LANGUAGE_ID)
			continue ;

		$_coincidence = 0;
		if (!TSEARCH(CSite::GetSiteDocRoot(false).$_v['PATH'], $_coincidence))
			continue ;

		$_v['COINCIDENCE'] = $_coincidence;
		$arLangDirFiles[$_k] = $_v;
	}
}

$lAdmin->BeginPrologContent();
?>
<p><?
if (!$arSearchParam)
{
	$last_path = "";
	for ($i=0; $i<=sizeof($arrChain)-1; $i++) :
		echo " / ";
		if (strlen($arrChain[$i]["PATH"])>0):
			$last_path = $arrChain[$i]["PATH"];
			?><a href="?lang=<?=LANGUAGE_ID; ?>&path=<?=urlencode($last_path)?>" title="<?=GetMessage("TR_FOLDER_TITLE")?>"><?=htmlspecialcharsbx($arrChain[$i]["NAME"])?></a><?
		else:
			?><?=htmlspecialcharsbx($arrChain[$i]["NAME"])?><?
		endif;
	endfor;
}
?></p>
<?
$lAdmin->EndPrologContent();

$header = array();
$header[] = array("id"=>"TRANS_FILE_NAME", "content"=>GetMessage("TRANS_FILE_NAME"),	"default"=>true, "align"=>"left");
if ($AUTO_CALCULATE || $SHOW_LANG_DIFF)
{
	$header[] = array("id"=>"TRANS_TOTAL_MESSAGES", "content"=>GetMessage("TRANS_TOTAL_MESSAGES"), "default"=>true, "align"=>"right");

	foreach($arTLangs as $vlang)
		$header[] = array("id"=>$vlang, "content"=>$vlang, "default"=>true, "align"=>"left");
}
$lAdmin->AddHeaders($header);


if (strlen($path)>0 && !$arSearchParam)
{
	$row =& $lAdmin->AddRow("0", Array());
	$row->AddViewField("TRANS_FILE_NAME", '<a href="?lang='.LANGUAGE_ID.'&path='.urlencode($last_path).'" title="'.GetMessage("TR_UP_TITLE").'">
			<img src="/bitrix/images/translate/up.gif" width="11" height="13" border=0 alt=""></a>'.
			'&nbsp;<a href="?lang='.LANGUAGE_ID.'&path='.urlencode($last_path).'" title="'.GetMessage("TR_UP_TITLE").'">..</a>');
	if ($AUTO_CALCULATE || $SHOW_LANG_DIFF)
	{
		$row->AddViewField("TRANS_TOTAL_MESSAGES", "&nbsp;");
		foreach($arTLangs as $vlang)
			$row->AddViewField($vlang, "&nbsp;");
	}
}

$ORIGINAL_MESS = $MESS;

if (is_array($arLangDirFiles)) :

	if ($IS_LANG_DIR)
	{
		$arPath[] = add_lang_id($path, LANGUAGE_ID, $arTLangs);
	}
	else
		$arPath[] = $path;


	$arShown = array();
	$arrTOTAL_NOT_TRANSLATED = array();
	$TOTAL_MESS = 0;
	$i = 0;

	foreach($arLangDirFiles as $key => $ar) :
		$i++;
		if (in_array($ar["PARENT"],$arPath) || $arSearchParam) :
			if ($arSearchParam && $ar['IS_DIR'] == 'Y')
				continue ;

			$is_dir = $ar["IS_DIR"];
			$fpath = $ar["PATH"];
			$fparent = $ar["PARENT"];
			$ftitle = $arSearchParam ? $ar["PATH"]: $ar["FILE"];

			if ($IS_LANG_DIR)
			{
				if (in_array($ftitle, $arShown))
					continue;
				$arShown[] = $ftitle;
			}

			$fkey = remove_lang_id($fpath, $arTLangs);

			if ($SHOW_LANG_DIFF)
			{
				GetPhraseCounters($arLangDirFiles, $fpath, $fkey);
			}
			if ($is_dir=="Y") :
				$row =& $lAdmin->AddRow($i, Array(), "translate_list.php?lang=".LANGUAGE_ID."&path=".$fpath, GetMessage("TR_FOLDER_TITLE"));
				$row->AddViewField("TRANS_FILE_NAME", '<a href="?lang='.LANGUAGE_ID.'&path='.$fpath.'" title="'.GetMessage("TR_FOLDER_TITLE").'"><img src="/bitrix/images/translate/folder.gif" width="16" height="16" border=0 alt=""></a>'.'&nbsp;<a href="?lang='.LANGUAGE_ID.'&path='.$fpath.'" title="'.GetMessage("TR_FOLDER_TITLE").'">'.$ftitle.'</a>');
			else :
				$row =& $lAdmin->AddRow($i, Array(), "translate_edit.php?lang=".LANGUAGE_ID."&file=".$fpath."&show_error=".$show_error, GetMessage("TR_FILE_TITLE"));
				$arAction = array(array('TEXT'=>GetMessage("TR_MESSAGE_EDIT"), 'ACTION'=> $lAdmin->ActionRedirect('translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$show_error),
					'DEFAULT'=>true, 'ICON'=>''),
					array('TEXT'=>GetMessage("TR_FILE_EDIT"), 'ACTION'=> $lAdmin->ActionRedirect('translate_edit_php.php?lang='.LANGUAGE_ID.'&file='.$fpath),
					'DEFAULT'=>false, 'ICON'=>'edit'),
					array('TEXT'=>GetMessage("TR_FILE_SHOW"), 'ACTION' => $lAdmin->ActionRedirect('translate_show_php.php?lang='.LANGUAGE_ID.'&file='.$fpath),
					'DEFAULT'=>false, 'ICON'=>'view')
					);
				if ($arSearchParam)
				{
					$arAction[] = array('SEPARATOR' => true);
					$arAction[] = array('TEXT'=>GetMessage("TR_PATH_GO"), 'ACTION' => $lAdmin->ActionRedirect('translate_list.php?lang='.LANGUAGE_ID.'&path='.$fparent),
					'DEFAULT'=>false, 'ICON'=>'go');
				}
				$row->AddActions($arAction);
				$row->AddViewField("TRANS_FILE_NAME", '<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$show_error.'" title="'.GetMessage("TR_FILE_TITLE").'"><img src="/bitrix/images/translate/file.gif" width="16" height="16" border=0 alt=""></a>'.
					'&nbsp;<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$show_error.'" title="'.GetMessage("TR_FILE_TITLE").'">'.$ftitle.'</a>'.($arSearchParam ? ' ('.$ar["COINCIDENCE"].')' : ''));
			endif;
			if ($AUTO_CALCULATE || $SHOW_LANG_DIFF)
			{
			$arr = array();
			foreach($arTLangs as $vlang)
			{
				$total_sum = 0;
				if(is_array($arCommonCounter[$fkey][$vlang]))
					foreach ($arCommonCounter[$fkey][$vlang] as $fileName => $fileCounter)
						$total_sum += intval($fileCounter["TOTAL"]);

				$arr[] = $total_sum;
			}

			$total_messages = max($arr);
			$TOTAL_MESS += $total_messages;
			$row->AddViewField("TRANS_TOTAL_MESSAGES", $total_messages);

			foreach($arTLangs as $vlang):
				$arFilesDiff = array();
				$arFilesTotal = array();
				$lang_not_translated = 0;
				$lang_total = 0;
				if(is_array($arCommonCounter[$fkey][$vlang]))
				{
					foreach ($arCommonCounter[$fkey][$vlang] as $file => $fileCounter)
					{
						if (intval($fileCounter["DIFF"]) > 0) $arFilesDiff[$file] = intval($fileCounter["DIFF"]);
						if (intval($fileCounter["TOTAL"]) > 0) $arFilesTotal[$file] = intval($fileCounter["TOTAL"]);
						$lang_not_translated += intval($fileCounter["DIFF"]);
						$lang_total += intval($fileCounter["TOTAL"]);
					}
				}
				$diff_total = $total_messages - $lang_total;
				if (intval($lang_not_translated)>0):
					foreach ($arFilesDiff as $fileName => $counter)
					{
						$arFilesDiff[$fileName] = '<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.urlencode($fileName).'&show_error=Y" title="'.$fileName.'">'.$counter.'</a>';
					}
					$sStr = '<span class="required">'.$lang_not_translated.'</span>: '.implode(', ', $arFilesDiff);
					$arrTOTAL_NOT_TRANSLATED[$vlang] += $lang_not_translated;
					$row->AddViewField($vlang, $sStr);
				elseif (intval($diff_total)>0):
					$sStr = '<span class="required">'.$lang_total.'</span>: '.implode(', ', $arFilesTotal);
					$arrTOTAL_NOT_TRANSLATED[$vlang] += $diff_total;
					$row->AddViewField($vlang, $sStr);
				else:
					$row->AddViewField($vlang, "&nbsp;");
				endif;
			endforeach;
			}
		endif;
	endforeach;
endif;

$i = 0;
$MESS = $ORIGINAL_MESS;
if ($AUTO_CALCULATE || $SHOW_LANG_DIFF)
{
	$row =& $lAdmin->AddRow($i++, Array());
	$row->AddViewField("TRANS_FILE_NAME", "<b>".GetMessage("TRANS_TOTAL").":</b>");
	$row->AddViewField("TRANS_TOTAL_MESSAGES", "<b>".$TOTAL_MESS."</b>");
	foreach($arTLangs as $vlang):
		if (intval($arrTOTAL_NOT_TRANSLATED[$vlang])>0)
		{
			$row->AddViewField($vlang, "<b>".$arrTOTAL_NOT_TRANSLATED[$vlang]."</b>");
		}
	endforeach;
}


//$rsData = CDBResult::InitFromArray();
$lAdmin->BeginEpilogContent();
?>
	<input type="hidden" name="go_path" id="go_path" value="">
<?
$lAdmin->EndEpilogContent();

$aContext = array();
ob_start();
?>
<form action="<?=$APPLICATION->GetCurPage()?>" name="form1">
<table cellspacing="0">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
<tr>
	<td style="padding-left:5px;"><?=GetMessage("TRANS_PATH")?></td>
	<td style="padding-left:5px;"><input class="form-text" type="text" name="path" id="path_to" size="50" value="<?=htmlspecialcharsbx($path)?>"></td>
	<td style="padding-left:3px; padding-right:3px;"><input type="submit" value="<?=GetMessage("TRANS_GO")?>" class="form-button"></td>
</tr>
</table>
</form>
<?
$s = ob_get_contents();
ob_end_clean();
$aContext[] = array("HTML"=>$s);
// Search / replace
$sFormValues = 1;
$url = $APPLICATION->GetPopupLink(
					array(
						'URL' => "/bitrix/admin/translate_search.php?lang=".LANGUAGE_ID."&bxpublic=Y&path=".urlencode($path),
								'PARAMS' => array(
									'width' => 470,
									'height' => 310,
									'resizable' => false
								)
					)
				);
$aContext[] = array(
	"TEXT" => $arSearchParam ? GetMessage("TR_NEW_SEARCH") : GetMessage("TR_SEARCH"),
	"ICON" => "btn_fileman_search",
	"LINK" => 'javascript:'.$url,
	"TITLE" => GetMessage("TR_SEARCH_TITLE")
);

if (!$AUTO_CALCULATE || $IS_LANG_DIR)
{
	$aContext[] = array('NEWBAR' => true);
}
if (!$AUTO_CALCULATE)
{
	if ($SHOW_LANG_DIFF)
	{
		$aContext[] = array(
				"TEXT"	=> GetMessage('TR_NO_SHOW_DIFF_TEXT'),
				"LINK"	=> $APPLICATION->GetCurPageParam('SHOW_DIFF=N&path='.urlencode($path), array('SHOW_DIFF', 'mode', 'path')),
				"TITLE"	=> GetMessage('TR_NO_SHOW_DIFF_TITLE'),
			);
	}
	else
	{
		$aContext[] = array(
				"TEXT"	=> GetMessage('TR_SHOW_DIFF_TEXT'),
				"LINK"	=> $APPLICATION->GetCurPageParam('SHOW_DIFF=Y&path=' . urlencode($path), array('SHOW_DIFF', 'mode', 'path')),
				"TITLE"	=> GetMessage('TR_SHOW_DIFF_TITLE'),
				"ICON" => "btn_green"
			);
	}
}

if ($IS_LANG_DIR)
{
	$aContext[] = array(
			"TEXT"	=> GetMessage('TR_CHECK_FILES_TEXT'),
			"LINK"	=> "translate_check_files.php?lang=".LANGUAGE_ID."&path=" . htmlspecialcharsbx($path),
			"TITLE"	=> GetMessage('TR_CHECK_FILES_TITLE'),
		);
}

$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->CheckListMode();

$aTabs = array(
	array("DIV" => "fileupl1", "TAB" => GetMessage("TR_FILEUPLFORM_TAB1"),
		"TITLE" => GetMessage("TR_UPLOAD_FILE"), 'ONSELECT' => "BX('tr_submit').value='".GetMessage("TR_UPLOAD_SUBMIT_BUTTON")."'"),
	array("DIV" => "filedown2", "TAB" => GetMessage("TR_FILEDOWNFORM_TAB2"),
		"TITLE" => GetMessage("TR_DOWNLOAD_CSV_TEXT"), 'ONSELECT' => "BX('tr_submit').value='".GetMessage("TR_DOWNLOAD_SUBMIT_BUTTON")."'")
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false);

$APPLICATION->SetTitle(GetMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
if ($arCSVMessage)
{
	CAdminMessage::ShowMessage($arCSVMessage);
}

$lAdmin->DisplayList();

if ($TRANS_RIGHT == 'W')
{
	?>
	<br>
	<?$tabControl->Begin();?>
	<form action="<?=$APPLICATION->GetCurPageParam('go_path='.htmlspecialcharsbx($path), array('go_path'))?>" name="form3" method="POST" enctype="multipart/form-data">
	<?$tabControl->BeginNextTab();?>

	<tr>
		<td width="10%" nowrap><?=GetMessage("TR_UPLOAD_CSV_FILE")?></td>
		<td valign="top" width="90%"><input type="file" name="csvfile"></td>
	</tr>
	<tr>
		<td width="10%" valign="top" nowrap><?=GetMessage('TR_FILE_ACTIONS')?></td>
		<td valign="top" width="90%">
		<input type="hidden" name="upload_csv" value="1" >
		<?=bitrix_sessid_post()?>
		<input id="F_ACTION_1" type="radio" name="rewrite_lang_files" value="N" checked><label for="F_ACTION_1"><?=GetMessage('TR_NO_REWRITE_LANG_FILES')?></label><br>
		<input id="F_ACTION_3" type="radio" name="rewrite_lang_files" value="U"><label for="F_ACTION_3"><?=GetMessage('TR_UPDATE_LANG_FILES')?></label><br>
		<input id="F_ACTION_2" type="radio" name="rewrite_lang_files" value="Y"><label for="F_ACTION_2"><?=GetMessage('TR_REWRITE_LANG_FILES')?></label>
		</td>
	</tr>

<?$tabControl->EndTab();?>
</form>
	<?$tabControl->BeginNextTab();?>
	<form action="translate_csv_download.php" name="form4" method="POST">
		<tr>
			<td width="10%" valign="top" nowrap><?=GetMessage('TR_FILE_ACTIONS')?></td>
			<td valign="top" width="90%">
				<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>" >
				<input type="hidden" name="path" value="<?=htmlspecialcharsbx($path);?>" >
				<?=bitrix_sessid_post()?>
				<input id="F_ACTION_2_1" type="radio" name="download_translate_lang" value="A" checked><label for="F_ACTION_2_1"><?=GetMessage('TR_DOWNLOAD_LANG')?></label><br>
				<input id="F_ACTION_2_2" type="radio" name="download_translate_lang" value="N"><label for="F_ACTION_2_2"><?=GetMessage('TR_DOWNLOAD_NO_TRANSLATE')?></label>
			</td>
		</tr>
		<?
		$customScriptsFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/langs.txt";
		if(file_exists($customScriptsFile)):
		?>
		<tr>
			<td width="10%" valign="top" nowrap>Only /bitrix/modules/langs.txt files:</td>
			<td valign="top" width="90%">
				<input type="checkbox" name="use_custom_list" value="Y">
			</td>
		</tr>
		<?endif?>
	</form>
<?
	$tabControl->Buttons();
?>
	<input type="submit" id="tr_submit" value="<?=GetMessage("TR_UPLOAD_SUBMIT_BUTTON")?>" class="adm-btn-save">
	<script type="text/javascript">
	BX.bind(BX('F_ACTION_2'), 'click', function(){
		if (!confirm('<? echo GetMessageJS('CONFRIM_REWRITE_LANG_FILES'); ?>'))
		{
			BX('F_ACTION_2').checked = false;
			BX('F_ACTION_1').checked = true;
		}
	});
	BX.bind(BX('tr_submit'), 'click', function ()
	{
		if (BX('tabControl_active_tab').value == 'fileupl1') {
			document.forms['form3'].submit();
		} else {
		document.forms['form4'].submit();
		}
	});
	</script>
<?
	$tabControl->End();
}
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");