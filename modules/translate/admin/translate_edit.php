<?php
//region HEAD
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");

use Bitrix\Main,
	Bitrix\Main\Localization,
	Bitrix\Main\Localization\Loc,
	Bitrix\Translate;

Loc::loadLanguageFile(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

/** @global \CMain $APPLICATION */
$permissionRight = $APPLICATION->GetGroupRight('translate');
if ($permissionRight == \Bitrix\Translate\Permission::DENY)
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

define("HELP_FILE","translate_list.php");

//endregion

//-----------------------------------------------------------------------------------
//region handle GET,POST

$htmlSpecialChars = function ($string)
{
	$encoding = Localization\Translation::getCurrentEncoding();

	return htmlspecialchars($string, ENT_COMPAT, $encoding, true);
};

$enabledLanguages = Translate\Translation::getEnabledLanguages();
$currentEncoding = Main\Localization\Translation::getCurrentEncoding();
$limitEncoding = !(Localization\Translation::useTranslationRepository() || $currentEncoding == 'utf-8');

$isEncodingCompatible = function ($langId) use ($limitEncoding, $currentEncoding)
{
	global $arTLanguages;
	$compatible = true;
	if ($limitEncoding)
	{
		$compatible = (
			$langId == Loc::getCurrentLang() ||
			$arTLanguages[$langId]['CHARSET'] == $currentEncoding ||
			$langId == 'en'
		);
	}

	return $compatible;
};

$request = Main\Context::getCurrent()->getRequest();

$strError = "";
$arDIFF = array();

$hasUntranslated = false;

$path = Rel2Abs("/", $request->get('file'));
if (!\Bitrix\Translate\Permission::isAllowPath($path) || strpos($path, "/lang/") === false || GetFileExtension($path) <> "php")
{
	$strError = Loc::getMessage("trans_edit_err")."<br>";
}

$chain = "";
$arPath = array();

$arKEYS = [];
$arMESS = [];
$differences = [];

$showOnlyUntranslated = 'N';
if ($request->get('show_error') === 'Y')
{
	$showOnlyUntranslated = "Y";
}

if($strError == "")
{

	// form a way to get back
	$path_back = dirname($path);
	$arSlash = explode("/",$path_back);
	if (is_array($arSlash))
	{
		$arSlash_tmp = $arSlash;
		$lang_key = array_search("lang", $arSlash) + 1;
		unset($arSlash_tmp[$lang_key]);
		if ($lang_key==count($arSlash)-1)
		{
			unset($arSlash[$lang_key]);
			$path_back = implode("/",$arSlash);
		}
		$i = 0;
		foreach($arSlash_tmp as $dir)
		{
			$i++;
			if ($i==1)
			{
				$chain .= "<a href=\"translate_list.php?lang=".LANGUAGE_ID."&path=/\" title=\"".Loc::getMessage("TRANS_CHAIN_FOLDER_ROOT")."\">..</a> / ";
			}
			else
			{
				$arPath[] = htmlspecialcharsbx($dir);
				if ($i>2)
				{
					$chain .= " / ";
				}
				$chain .= "<a href=\"translate_list.php?lang=".LANGUAGE_ID."&path="."/".implode("/",$arPath)."/\" title=\"".Loc::getMessage("TRANS_CHAIN_FOLDER")."\">".htmlspecialcharsbx($dir)."</a>";
			}
		}
	}


	$arTLanguages = array();
	$iterator = Main\Localization\LanguageTable::getList([
		'select' => ['LID' => 'LID', 'NAME' => 'NAME', 'CHARSET' => 'CULTURE.CHARSET', 'SORT'],
		'filter' => ['ID' => $enabledLanguages],
		'order' => ['SORT' => 'ASC']
	]);
	while ($row = $iterator->fetch())
	{
		$arTLanguages[$row['LID']] = $row;
	}
	unset($row, $iterator);


	$arLangFiles = array();
	$arFiles = array();
	foreach ($enabledLanguages as $langId)
	{
		if ($limitEncoding && !$isEncodingCompatible($langId))
		{
			continue;
		}

		$arSlash = explode("/",$path);
		if (is_array($arSlash))
		{
			$pos = array_search('lang', $arSlash) + 1;
			$arSlash[$pos] = $langId;
			$arLangFiles[$langId] = implode('/', $arSlash);
			$arFiles[] = $arLangFiles[$langId];
		}
	}
	unset($arSlash, $pos, $langId);


	if(!empty($arFiles))
	{
		$arFilesLng = [];
		foreach ($enabledLanguages as $langId)
		{
			if ($limitEncoding && !$isEncodingCompatible($langId))
			{
				continue;
			}

			$arFilesLng[$langId] = [];
		}
		// form the array for each file by language
		foreach ($arFiles as $langFileName)
		{
			$langId = Translate\Path::extractLangId($langFileName);

			if (!in_array($langId, $enabledLanguages))
			{
				continue;
			}
			if ($limitEncoding && !$isEncodingCompatible($langId))
			{
				continue;
			}

			$fullPath = Translate\Path::tidy(Main\Application::getDocumentRoot(). $langFileName);

			if (Main\Localization\Translation::useTranslationRepository())
			{
				if (in_array($langId, Translate\Translation::getTranslationRepositoryLanguages()))
				{
					$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);
				}
				else
				{
					$fullPath = realpath($fullPath);
				}
			}
			else
			{
				$fullPath = realpath($fullPath);
			}

			$fullPath = Translate\Path::tidy($fullPath);

			$MESS_tmp = $MESS;
			$MESS = [];
			if (file_exists($fullPath))
			{
				include($fullPath);

				$arFilesLng[$langId] = array_keys($MESS);

				$sourceEncoding = Main\Localization\Translation::getSourceEncoding($langId);

				if ($sourceEncoding != $currentEncoding)
				{
					foreach ($MESS as $phraseId => $phrase)
					{
						$MESS[$phraseId] = Main\Text\Encoding::convertEncoding($phrase, $sourceEncoding, $currentEncoding, $errorMessage);
					}
					$arMESS[$langId] = $MESS;
				}
				else
				{
					$arMESS[$langId] = $MESS;
				}
			}

			$MESS = $MESS_tmp;
		}
		unset($langFileName, $fullPath, $langId, $MESS_tmp);

		if (!empty($arFilesLng))
		{
			// calculate the sum and difference for file
			foreach ($arFilesLng as $phraseCodes)
			{
				foreach ($phraseCodes as $phraseId)
				{
					$arKEYS[$phraseId] = $phraseId;
				}
			}
			unset($phraseCodes, $phraseId);

			$arKEYS = array_values($arKEYS);
			$total = count($arKEYS);

			foreach ($arFilesLng as $langId => $phraseCodes)
			{
				$differences[$langId] = array_diff($arKEYS, $phraseCodes);
				if (count($differences[$langId]) > 0)
				{
					$hasUntranslated = true;
				}
				$arDIFF[$langId] = array('TOTAL' => $total, 'DIFF' => count($differences[$langId]));
			}
		}
		unset($arFilesLng, $langId, $phraseCodes);
	}

	//region POST save|apply
	// gather in the array is that it is necessary to write to file
	if (
		$request->isPost() &&
		check_bitrix_sessid() &&
		($permissionRight == Translate\Permission::WRITE) &&
		($request->getPost('save') !== null || $request->getPost('apply') !== null)
	)
	{
		$KEYS = $request->getPost('KEYS');
		$LANGS = $request->getPost('LANGS');
		if (is_array($KEYS))
		{
			$arTEXT = array();
			foreach ($KEYS as $phraseId)
			{
				$delPhrase = ($request->getPost('DEL_'.$phraseId) === 'Y');
				if (is_array($LANGS))
				{
					foreach ($LANGS as $langId)
					{
						if ($limitEncoding && !$isEncodingCompatible($langId))
						{
							continue;
						}

						$inpKey = str_replace('.', '_', $phraseId).'_'.$langId;
						$langFileName = $arLangFiles[$langId];

						if (!isset($arTEXT[$langFileName]))
						{
							$arTEXT[$langFileName] = [];
						}

						$inpValue = null;
						if ($request->getPost($inpKey) !== null)
						{
							$inpValue = $request->getPost($inpKey);
						}

						$prevValueExisted = ($request->getPost($inpKey.'_PREV') === 'Y');

						if ($delPhrase !== true && !empty($inpValue) && strlen($inpValue) > 0)
						{
							$targetEncoding = Main\Localization\Translation::getSourceEncoding($langId);
							if ($targetEncoding != $currentEncoding)
							{
								$inpValue = Main\Text\Encoding::convertEncoding($inpValue, $currentEncoding, $targetEncoding, $errorMessage);
							}

							$arTEXT[$langFileName][$phraseId] = $inpValue;
						}
						elseif ($prevValueExisted)
						{
							$arTEXT[$langFileName][$phraseId] = '';
						}
					}
				}
			}


			// collect all the variables and write to files
			$errorCollection = [];
			foreach ($arTEXT as $langFileName => $phrases)
			{
				saveTranslationFile($langFileName, $phrases, $errorCollection);
			}
			if (!empty($errorCollection))
			{
				$strError .= implode("<br>\n", $errorCollection);
			}
			else
			{
				if (strlen($save) > 0)
				{
					LocalRedirect("translate_list.php?lang=".LANGUAGE_ID."&path=".$path_back);
				}
				else
				{
					LocalRedirect("translate_edit.php?lang=".LANGUAGE_ID."&file=".urlencode($path)."&show_error=".$showOnlyUntranslated);
				}
			}
		}
	}
	//endregion
}


$APPLICATION->SetTitle(Loc::getMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strError <> "")
{
	\CAdminMessage::ShowMessage($strError);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
//endregion


//-------------------------------------------------------------------------------------
//region Menu

$aMenu = array();
$aMenu[] = array(
	"TEXT"	=> Loc::getMessage("TRANS_LIST"),
	"LINK"	=> "/bitrix/admin/translate_list.php?lang=".LANGUAGE_ID."&path=/".implode("/",$arPath)."/",
	"TITLE"	=> Loc::getMessage("TRANS_LIST_TITLE"),
	"ICON"	=> "btn_list"
	);

if ($showOnlyUntranslated == "N")
{
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("TRANS_SHOW_ONLY_ERROR"),
		"LINK"	=> "/bitrix/admin/translate_edit.php?file=".htmlspecialcharsbx($path)."&lang=".LANGUAGE_ID."&show_error=Y",
		"TITLE"	=> Loc::getMessage("TRANS_SHOW_ONLY_ERROR_TITLE"),
		"ICON"	=> ""
		);
}
elseif ($showOnlyUntranslated == "Y")
{
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("TRANS_SHOW_ALL"),
		"LINK"	=> "/bitrix/admin/translate_edit.php?file=".htmlspecialcharsbx($path)."&lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("TRANS_SHOW_ALL_TITLE"),
		"ICON"	=> ""
		);
}

?>
<form name="form3" method="post" action="/bitrix/admin/translate_csv_download.php?lang=<?=LANGUAGE_ID?>" style="display: none">
	<?= bitrix_sessid_post() ?>
	<input name="download_translate_lang" value="" type="hidden">
	<input name="path" value="<?= htmlspecialcharsbx($path_back) ?>" type="hidden">
	<input name="file" value="<?=htmlspecialcharsbx($path)?>" type="hidden">
	<?if (!$limitEncoding):?>
		<input name="convert_encoding" value="Y" type="checkbox" checked="checked">
	<?endif?>
	<?if ($limitEncoding):?>
		<?foreach ($enabledLanguages as $langId):?>
			<?if ($isEncodingCompatible($langId)):?>
				<input name="languages[]" value="<?=$langId?>" type="hidden">
			<?endif?>
		<?endforeach?>
	<?endif?>
</form>
<script>
	function translate_csv_download(flag){
		document.forms['form3'].elements['download_translate_lang'].value = (flag == 'Y' ? 'Y' : 'N');
		BX.submit(document.forms['form3']);
	}
</script>
<?

$arSubMenu = array();
$arSubMenu[] = array(
	"TEXT"	=> Loc::getMessage("TRANS_GET_FULL_TRANSLATE"),
	"ONCLICK"	=> "translate_csv_download('Y')",
	"TITLE"	=> Loc::getMessage("TRANS_GET_FULL_TRANSLATE_TITLE"),
);
if ($hasUntranslated)
{
	$arSubMenu[] = array(
		"TEXT"	=> Loc::getMessage("TRANS_GET_UNTRANSLATE"),
		"ONCLICK"	=> "translate_csv_download('N')",
		"TITLE"	=> Loc::getMessage("TRANS_GET_UNTRANSLATE_TITLE"),
	);
}
$aMenu[] = array(
	"TEXT" => Loc::getMessage("TRANS_GET_TRANSLATE"),
	"MENU" => $arSubMenu,
);


$arSubMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("TR_FILE_SHOW"),
		"LINK"	=> "/bitrix/admin/translate_show_php.php?file=".htmlspecialcharsbx($path)."&lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("TR_FILE_SHOW_TITLE"),
	),
	array(
		"TEXT"	=> Loc::getMessage("TR_FILE_EDIT"),
		"LINK"	=> "/bitrix/admin/translate_edit_php.php?file=".htmlspecialcharsbx($path)."&lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("TR_FILE_EDIT_TITLE"),
	),
);
$aMenu[] = array(
	"TEXT" => Loc::getMessage("TR_FILE_PHP"),
	"MENU" => $arSubMenu,
);

$context = new \CAdminContextMenu($aMenu);
$context->Show();

//endregion

//-------------------------------------------------------------------------------------
//region Grid
?>
<p><?=$chain?></p>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?show_error=<?=htmlspecialcharsbx($showOnlyUntranslated)?>&file=<?=htmlspecialcharsbx($path)?>&lang=<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
<?

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("TRANS_TITLE"), "ICON" => "translate_edit", "TITLE" => Loc::getMessage("TRANS_TITLE_TITLE")),
);
$tabControl = new \CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();

$tabControl->BeginNextTab();

?>
<tr valign="top">
<td width="100%" colspan="2">
	<table border="0" cellspacing="3" cellpadding="3" width="100%">
		<tr>
			<td colspan="2" style="padding: 0; height: 15px;"></td>
		</tr>
		<tr>
			<td valign="top" align="right" width="35%" nowrap><?echo Loc::getMessage("TRANS_FILENAME")?></td>
			<td valign="top" align="left" width="65%" nowrap><b><?=htmlspecialcharsbx(basename($path))?></b></td>
		</tr>
		<tr>
			<td valign="top" align="right" nowrap><?echo Loc::getMessage("TRANS_TOTAL")?></td>
			<td valign="top" align="left" nowrap><?=$total?></td>
		</tr>
		<tr>
			<td valign="top" align="right" nowrap><?echo Loc::getMessage("TRANS_NOT_TRANS")?></td>
			<td valign="top" align="left" nowrap><table border="0" cellspacing="0" cellpadding="0" width="0%" class="internal">
			<?
			$str1 = $str2 = "";
			if (is_array($arDIFF))
			{
				foreach ($arDIFF as $ln => $arD)
				{
					$str1 .= '<td width="'.round(100/count($enabledLanguages)).'%" align="center">'.$ln.'</td>';
					$str2 .= '<td align="right">';
					$cl = (intval($arD["DIFF"])>0) ? 'class="required"' : '';
					$str2 .= '&nbsp;<span '.$cl.'>'.$arD["DIFF"].'</span>&nbsp;</td>';
				}
			}
		?>
		<tr class="heading"><?=$str1?></tr>
		<tr><?=$str2?></tr>
	</table>
</td>
</tr>
<tr>
	<td colspan="2" valign="top" align="left" width="100%" nowrap>
	<?

	foreach ($enabledLanguages as $langId)
	{
		?><input type="hidden" name="LANGS[]" value="<?= htmlspecialcharsbx($langId) ?>"><?
	}
	$boolShowDeleteAll = false;
	$boolShowDeleteFromCur = false;
	$arDelFromCur = array();

	if (!empty($arDIFF) && is_array($arDIFF))
	{
		if (array_key_exists(LANGUAGE_ID, $arDIFF))
		{
			$boolShowDeleteFromCur = (0 < intval($arDIFF[LANGUAGE_ID]['DIFF']));
		}
	}
	$intShowCount = 0;
	$key_del = 0;
	if (!empty($arKEYS))
	{

		?><table border="0" cellspacing="0" cellpadding="0" width="100%"><?

		foreach ($arKEYS as $phraseId)
		{
			$key_del++;
			$red = false;
			foreach ($differences as $langId => $arDLang)
			{
				if (in_array($phraseId, $arDLang) && in_array($langId, $enabledLanguages))
				{
					$red = true;
					break;
				}
			}

			?><input type="hidden" name="KEYS[]" value="<?= htmlspecialcharsbx($phraseId) ?>"><?

			if (($showOnlyUntranslated == 'Y' && $red) || $showOnlyUntranslated == 'N')
			{
				$boolShowDeleteAll = true;
				$intShowCount ++;
				?>
				<tr><td colspan="3" style="padding: 10px 0;"><hr></td></tr>
				<tr>
					<td>ID:</td>
					<td>
					<?
					if ($red)
					{
						?><span class="required"><b><?=htmlspecialcharsbx($phraseId); ?></b></span><?
					}
					else
					{
						?><b><?=htmlspecialcharsbx($phraseId); ?></b><?
					}
					?><a name="<?= htmlspecialcharsbx($phraseId); ?>"></a></td><?

					$s = ($permissionRight < Translate\Permission::WRITE ? "disabled" : '');
					?>
					<td align="right">&nbsp;<label for="DEL_<?= $key_del ?>"><?= Loc::getMessage("TRANS_DELETE")?></label>
						<input type="checkbox" name="<?= 'DEL_'.$phraseId ?>" value="Y" <?= $s ?> id="<?= 'DEL_'.$key_del ?>" onclick="SelectOneDelete(this);">
					</td>
				</tr>
				<tr>
					<td colspan="3" style="padding: 10px 0; height: 1px;"></td>
				</tr>
				<?
				$rows = '2';
				foreach ($enabledLanguages as $langId)
				{
					if ($limitEncoding && !$isEncodingCompatible($langId))
					{
						continue;
					}

					if (strpos($arMESS[$langId][$phraseId], "\n") !== false)
					{
						$rows = '10';
						break;
					}
				}

				foreach ($enabledLanguages as $langId)
				{
					if ($limitEncoding && !$isEncodingCompatible($langId))
					{
						continue;
					}

					$valMsg = '';
					$inpKey = str_replace('.', '_', $phraseId). '_'.$langId;

					if ($request->getPost($inpKey) !== null && $request->getPost($inpKey) != $arMESS[$langId][$phraseId])
					{
						$valMsg = $htmlSpecialChars($request->getPost($inpKey));
					}
					else
					{
						$valMsg = $htmlSpecialChars($arMESS[$langId][$phraseId]);
					}
					if ($boolShowDeleteFromCur && LANGUAGE_ID == $langId && '' == $valMsg)
					{
						$arDelFromCur[] = 'DEL_'.$key_del;
					}

					?>
					<tr>
						<td valign="top">[<?= $langId ?>]&nbsp;<?= $arTLanguages[$langId]["NAME"] ?>:&nbsp;</td>
						<td colspan="2">
							<input type="hidden" name="<?= $inpKey ?>_PREV" value="<?= (!empty($arMESS[$langId][$phraseId]) ? 'Y' : '') ?>">
							<textarea cols="60" rows="<?= $rows ?>" name="<?= $inpKey ?>" style="width:90%"><?= $valMsg ?></textarea>
						</td>
					</tr>
					<?
				}
			}
			else
			{
				foreach ($enabledLanguages as $langId)
				{
					if ($limitEncoding && !$isEncodingCompatible($langId))
					{
						continue;
					}

					$inpKey = str_replace('.', '_', $phraseId). '_'.$langId;

					?>
					<input type="hidden" name="<?= $inpKey ?>_PREV" value="<?= (!empty($arMESS[$langId][$phraseId]) ? 'Y' : '') ?>">
					<input type="hidden" name="<?= $inpKey ?>" value="<?= $htmlSpecialChars($arMESS[$langId][$phraseId]) ?>">
					<?
				}
			}
		}
		?></table><?
	}
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 0; height: 15px;"></td>
	</tr>
	<script type="text/javascript">
		function SelectAllDelete()
		{
			var intShowCount = parseInt(BX('show_count').value);
			if (0 < intShowCount)
			{
				var intAllCount = parseInt(BX('all_count').value);
				if (0 < intAllCount)
				{
					var val = BX('all').checked;
					var obCountChecked = BX('count_checked');
					for (var i = 1;i <= intAllCount; i++)
					{
						var ck = BX("DEL_"+i);
						if (ck && !ck.disabled)
							ck.checked = val;
					}
					if (!!obCountChecked)
					{
						obCountChecked.value = (val ? intShowCount : 0);
					}
					if (!val)
					{
						var obCur = BX('del_current');
						if (!!obCur && obCur.checked)
						{
							obCur.checked = false;
						}
					}
				}
			}
		}
		function SelectOneDelete(obj)
		{
			var intShowCount = parseInt(BX('show_count').value);
			if (0 < intShowCount)
			{
				var boolCheck = obj.checked;
				var intCurrent = parseInt(BX('count_checked').value);
				intCurrent += (boolCheck ? 1 : -1);
				BX('all').checked = (intCurrent >= intShowCount);
				BX('count_checked').value = intCurrent;
			}
		}
		var arDelCur = <? echo CUtil::PhpToJSObject($arDelFromCur); ?>;
		function SelectDeleteCurrent(obj)
		{
			if (!!obj)
			{
				var val = obj.checked;
				if (0 < arDelCur.length)
				{
					for (var i = 0 ; i < arDelCur.length; i++)
					{
						var obCheck = BX(arDelCur[i]);
						if (!!obCheck)
						{
							var boolTemp = obCheck.checked;
							obCheck.checked = val;
							if (boolTemp !== val)
								SelectOneDelete(obCheck);
						}
					}
				}
			}
		}
	</script>
	<input type="hidden" name="all_count" id="all_count" value="<?= $key_del; ?>">
	<input type="hidden" name="show_count" id="show_count" value="<?= $intShowCount; ?>">
	<input type="hidden" name="count_checked" id="count_checked" value="0">
	<?
	if ($permissionRight >= Translate\Permission::WRITE && $boolShowDeleteFromCur)
	{
		?>
		<tr>
			<td valign="top" align="right" nowrap colspan="2">
				<b><label for="del_current"><?= Loc::getMessage('TRANS_DELETE_CURRENT'); ?></label></b>
				<input type="checkbox" name="del_current" id="del_current" onclick="SelectDeleteCurrent(this);">
			</td>
		</tr>
		<?
	}

	if ($permissionRight >= Translate\Permission::WRITE && $boolShowDeleteAll)
	{
		?>
		<tr>
			<td valign="top" align="right" nowrap colspan="2">
				<b><label for="all"><?=Loc::getMessage("TRANS_DELETE_ALL")?></label></b>
				<input type="checkbox" name="all" id="all" value="" onclick="SelectAllDelete('<?=$key_del?>');"<?if ($permissionRight<Translate\Permission::WRITE) echo " disabled";?>>
			</td>
		</tr>
		<?
	}
	?>
	</table>
</td></tr>
<?

$tabControl->Buttons(array("disabled" => ($permissionRight < Translate\Permission::WRITE), "back_url"=>"translate_list.php?lang=".LANGUAGE_ID."&path=".urlencode($path_back)));
$tabControl->End();

?></form>
<?


//endregion
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");