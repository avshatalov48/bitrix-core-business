<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('iblock');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

if(!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$back_url = (string)$request->get('back_url');
if ($back_url === '')
{
	$back_url = '/bitrix/admin/iblock_type_admin.php?lang=' . LANGUAGE_ID;
}
$ID = (string)($request->get('ID') ?? '');

$arIBTLang = [];
$l = CLanguage::GetList();
while($ar = $l->GetNext())
{
	$arIBTLang[] = $ar;
}

$strWarning = "";

$aTabs = array();
$aTabs[] = array(
	"DIV" => "edit1",
	"TAB" => GetMessage("IBTYPE_E_TAB1"),
	"ICON" => "iblock_type",
	"TITLE" => GetMessage("IBTYPE_E_TAB1_T"),
);
$aTabs[] = array(
	"DIV" => "edit2",
	"TAB" => GetMessage("IBTYPE_E_TAB2"),
	"ICON" => "iblock_type",
	"TITLE" => GetMessage("IBTYPE_E_TAB2_T"),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$bVarsFromForm = false;
$arFields = [];
$langFields = [];
if (
	$request->isPost()
	&& $request->getPost('Update') === 'Y'
	&& check_bitrix_sessid()
)
{
	if ($ID === '')
	{
		$arFields["ID"] = ($request->getPost('NEW_ID') ?? null);
	}
	$arFields["EDIT_FILE_BEFORE"] = ($request->getPost('EDIT_FILE_BEFORE') ?? '');
	$arFields["EDIT_FILE_AFTER"] = ($request->getPost('EDIT_FILE_AFTER') ?? '');
	$arFields["IN_RSS"] = ($request->getPost('IN_RSS') === 'Y' ? 'Y' : 'N');
	$arFields["SECTIONS"] = ($request->getPost('SECTIONS') === 'N' ? 'N' : 'Y');
	$arFields["SORT"] = (int)($request->getPost('SORT') ?? 500);

	$rawLangFields = $request->getPost('LANG_FIELDS');
	if (!empty($rawLangFields) && is_array($rawLangFields))
	{
		foreach($arIBTLang as $ar)
		{
			$langId = $ar['LID'];
			if (!isset($rawLangFields[$langId]) || !is_array($rawLangFields[$langId]))
			{
				continue;
			}
			$row = $rawLangFields[$langId];
			$langFields[$langId] = [
				'NAME' => (string)($row['NAME'] ?? ''),
				'SECTION_NAME' => (string)($row['SECTION_NAME'] ?? ''),
				'ELEMENT_NAME' => (string)($row['ELEMENT_NAME'] ?? ''),
			];
		}
	}
	if (!empty($langFields))
	{
		$arFields['LANG'] = $langFields;
	}

	$obBlocktype = new CIBlockType;
	$DB->StartTransaction();
	if ($ID <> '')
	{
		$res = $obBlocktype->Update($ID, $arFields);
	}
	else
	{
		$ID = $obBlocktype->Add($arFields);
		$res = ($ID <> '');
	}

	if(!$res)
	{
		$strWarning.= GetMessage("IBTYPE_E_SAVE_ERROR").": ".$obBlocktype->LAST_ERROR;
		$DB->Rollback();
		$bVarsFromForm = true;
	}
	else
	{
		$DB->Commit();
		if ($request->getPost('apply') === null)
		{
			LocalRedirect("/".ltrim($back_url, "/"));
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()
				. "?lang=" . LANGUAGE_ID
				. "&ID=" . urlencode($ID)
				. "&" . $tabControl->ActiveTabParam()
			);
		}
	}
}

if($ID <> '')
	$APPLICATION->SetTitle(GetMessage("IBTYPE_E_TITLE", array('#ITYPE#' => $ID)));
else
	$APPLICATION->SetTitle(GetMessage("IBTYPE_E_TITLE_2"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

ClearVars("str_");
$str_SECTIONS = "Y";
$str_SORT = "500";
$str_IN_RSS = 'N';
$str_EDIT_FILE_BEFORE = '';
$str_EDIT_FILE_AFTER = '';

$result = CIBlockType::GetByID($ID);
if(!$result->ExtractFields("str_"))
	$ID='';

$NEW_ID = '';
if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_iblock_type", "", "str_");
	$str_SECTIONS = $arFields['SECTIONS'];
	$str_SORT = $arFields['SORT'];
	$str_IN_RSS = $arFields['IN_RSS'];
	$str_EDIT_FILE_BEFORE = $arFields['EDIT_FILE_BEFORE'];
	$str_EDIT_FILE_AFTER = $arFields['EDIT_FILE_AFTER'];
	$NEW_ID = $arFields['ID'];
}

$aMenu = array(
	array(
		"TEXT" => GetMessage("IBTYPE_E_LIST"),
		"TITLE" => GetMessage("IBTYPE_E_LIST_TITLE"),
		"LINK" => "iblock_type_admin.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list"
	)
);

if($ID <> '')
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT" => GetMessage("IBTYPE_E_CREATE"),
		"TITLE" => GetMessage("IBTYPE_E_CREATE_TITLE"),
		"LINK" => "iblock_type_edit.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_new"
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("IBTYPE_E_DEL"),
		"TITLE" => GetMessage("IBTYPE_E_DEL_TITLE"),
		"LINK" => "javascript:if(confirm('".GetMessageJS("IBTYPE_E_DEL_CONF")."')) window.location='/bitrix/admin/iblock_type_admin.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete"
		);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

CAdminMessage::ShowOldStyleError($strWarning);?>
<form method="POST" id="form" name="form" action="iblock_type_edit.php?lang=<?= LANGUAGE_ID; ?>">
<?=bitrix_sessid_post()?>
<?= GetFilterHiddens("find_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?= htmlspecialcharsbx($ID); ?>">
<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($back_url); ?>">
<?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();

	if($ID <> ''):?>
	<tr>
		<td><?= GetMessage("IBTYPE_E_ID")?></td>
		<td><?= htmlspecialcharsbx($ID); ?></td>
	</tr>
	<?php
	else:
	?>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("IBTYPE_E_ID")?></td>
		<td><input type="text" name="NEW_ID" size="50" maxlength="50" value="<?=htmlspecialcharsbx($NEW_ID)?>"></td>
	</tr>
	<?php
	endif;
	?>
	<script>
		function __Chk()
		{
			var c = document.getElementById("SECTIONS");
			var frm = document.getElementById("form");
			var inputs = frm.getElementsByTagName("INPUT");
			for(var i=0; i<inputs.length; i++)
				if(inputs[i].name && inputs[i].name.indexOf("[SECTION_NAME]")>0)
					inputs[i].disabled = !c.checked;

			document.getElementById("SECTION_NAME_TITLE").disabled = !c.checked;
		}
	</script>
	<tr>
		<td width="40%"><label for="SECTIONS"><?= GetMessage("IBTYPE_E_SECTIONS")?></label></td>
		<td width="60%">
			<input type="hidden" id="SECTIONS_hidden" name="SECTIONS" value="N">
			<input type="checkbox" id="SECTIONS" name="SECTIONS" value="Y"<?= ($str_SECTIONS === "Y" ? " checked" : ''); ?> onclick="__Chk()">
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?= GetMessage("IBTYPE_E_LANGS")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table border="0" cellspacing="6" class="internal">
				<tr class="heading">
					<td><?= GetMessage("IBTYPE_E_LANG");?></td>
					<td><?= GetMessage("IBTYPE_E_NAME");?></td>
					<td><span id="SECTION_NAME_TITLE"><?= GetMessage("IBTYPE_E_SECTIONS_LABEL");?></span></td>
					<td><?= GetMessage("IBTYPE_E_ELEMENTS");?></td>
				</tr>
				<?php
				$defaultTypeLang = [
					'NAME' => '',
					'SECTION_NAME' => '',
					'ELEMENT_NAME' => '',
				];
				foreach ($arIBTLang as $ar)
				{
					if ($bVarsFromForm)
					{
						$ibtypelang = $langFields[$ar["LID"]] ?? $defaultTypeLang;
					}
					else
					{
						$ibtypelang = CIBlockType::GetByIDLang($ID, $ar["LID"], false);
						if (is_array($ibtypelang))
						{
							$ibtypelang['NAME'] = (string)($ibtypelang['~NAME'] ?? '');
							$ibtypelang['SECTION_NAME'] = (string)($ibtypelang['~SECTION_NAME'] ?? '');
							$ibtypelang['ELEMENT_NAME'] = (string)($ibtypelang['~ELEMENT_NAME'] ?? '');
						}
						else
						{
							$ibtypelang = $defaultTypeLang;
						}
					}
				?>
				<tr>
					<td><?= $ar["NAME"]?>:</td>
					<td><input type="text" name="LANG_FIELDS[<?= $ar["LID"]?>][NAME]" size="20" maxlength="255" value="<?= htmlspecialcharsbx($ibtypelang["NAME"])?>"></td>
					<td><input type="text" name="LANG_FIELDS[<?= $ar["LID"]?>][SECTION_NAME]" size="20" maxlength="255" value="<?= htmlspecialcharsbx($ibtypelang["SECTION_NAME"])?>"></td>
					<td><input type="text" name="LANG_FIELDS[<?= $ar["LID"]?>][ELEMENT_NAME]" size="20" maxlength="255" value="<?= htmlspecialcharsbx($ibtypelang["ELEMENT_NAME"])?>"></td>
				</tr>
				<?php
				}
				?>
			</table>
		</td>
	</tr>

<?php
	$tabControl->BeginNextTab();?>
	<tr>
		<td width="40%"><label for="IN_RSS"><?= GetMessage("IBTYPE_E_USE_RSS")?>:</label></td>
		<td width="60%">
			<input type="hidden" id="IN_RSS_hidden" name="IN_RSS" value="N">
			<input type="checkbox" id="IN_RSS" name="IN_RSS" value="Y"<?= ($str_IN_RSS === "Y" ? " checked" : '');?>>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IBTYPE_E_SORT")?>:</td>
		<td><input type="text" name="SORT" size="10"  maxlength="15" value="<?= htmlspecialcharsbx($str_SORT); ?>"></td>
	</tr>
	<tr>
		<td>
		<?php
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick",
				"arResultDest" => array("FORM_NAME" => "form", "FORM_ELEMENT_NAME" => "EDIT_FILE_BEFORE"),
				"arPath" => array("SITE" => SITE_ID, "PATH" => GetDirPath($str_EDIT_FILE_BEFORE)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?= GetMessage("IBTYPE_E_FILE_BEFORE")?></td>
		<td><input type="text" name="EDIT_FILE_BEFORE" size="50"  maxlength="255" value="<?= htmlspecialcharsbx($str_EDIT_FILE_BEFORE); ?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick()"></td>
	</tr>
	<tr>
		<td>
		<?php
		CAdminFileDialog::ShowScript
		(
			Array(
				"event" => "BtnClick2",
				"arResultDest" => array("FORM_NAME" => "form", "FORM_ELEMENT_NAME" => "EDIT_FILE_AFTER"),
				"arPath" => array("SITE" => SITE_ID, "PATH" => GetDirPath($str_EDIT_FILE_AFTER)),
				"select" => 'F',// F - file only, D - folder only
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => 'php',
				"allowAllFiles" => true,
				"SaveConfig" => true,
			)
		);
		?>
		<?= GetMessage("IBTYPE_E_FILE_AFTER")?></td>
		<td><input type="text" name="EDIT_FILE_AFTER" size="50"  maxlength="255" value="<?= htmlspecialcharsbx($str_EDIT_FILE_AFTER); ?>">&nbsp;<input type="button" name="browse" value="..." onClick="BtnClick2()"></td>
	</tr>
<?php
	$tabControl->Buttons(array("disabled"=>false, "back_url"=>$back_url));
	$tabControl->End();
?>
</form>
<script>__Chk();</script>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
