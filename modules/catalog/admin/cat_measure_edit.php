<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;

if(!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_store');

IncludeModuleLangFile(__FILE__);

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$strError = $ex->GetString();
	ShowError($strError);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$ID = (isset($_REQUEST["ID"]) ? (int)$_REQUEST["ID"] : 0);
$classifierMode = false;
$mainSectionId = $subSectionId = 0;
$arMeasureClassifier = array();
$arMeasureCode = array();
$errorMessage = $okMessage = "";

$sTableID = 'b_catalog_measure';

if($_REQUEST["OKEI"] == "Y")
{
	$classifierMode = true;
	$arMeasureClassifier = CCatalogMeasureClassifier::getMeasureClassifier();
	if(!is_array($arMeasureClassifier))
		LocalRedirect("/bitrix/admin/cat_measure_list.php?lang=".LANGUAGE_ID."&".GetFilterParams("filter_", false));
	if(isset($_REQUEST["main_section"]) && intval($_REQUEST["main_section"] < count($arMeasureClassifier)))
		$mainSectionId = intval($_REQUEST["main_section"]);
	if(isset($_REQUEST["sub_section"]) && intval($_REQUEST["sub_section"] <= 6))
		$subSectionId = intval($_REQUEST["sub_section"]);

	$oSort = new CAdminSorting($sTableID, "ID", "asc");
	$lAdmin = new CAdminList($sTableID, $oSort);

	if($lAdmin->EditAction() && !$bReadOnly)
	{
		if(!isset($_POST['ID']) || !is_array($_POST['ID']))
		{
			$errorMessage .= GetMessage("CAT_MEASURE_NOTHING_SELECT")."\n";
		}
		else
		{
			foreach($_POST['ID'] as $code)
			{
				$DB->StartTransaction();
				$code = intval($code);
				$arFields['CODE'] = $code;
				unset($arMeasureClassifier[$mainSectionId][$subSectionId][$code]["MEASURE_TITLE"]);
				unset($arMeasureClassifier[$mainSectionId][$subSectionId][$code]["SYMBOL_RUS"]);
				if(!CCatalogMeasure::add($arMeasureClassifier[$mainSectionId][$subSectionId][$code]))
				{
					if($ex = $APPLICATION->GetException())
						$lAdmin->AddUpdateError($ex->GetString(), $code);
					else
						$lAdmin->AddUpdateError(GetMessage("ERROR_UPDATING_REC")." (".$code.")", $code);

					$DB->Rollback();
				}

				$DB->Commit();
				$okMessage = GetMessage("CAT_MEASURE_SUCCESS_ADD")."\n";
			}
		}
	}

	$dbMeasure = CCatalogMeasure::getList(array(), array(), false, false, array("CODE"));
	while($arMeasure = $dbMeasure->Fetch())
	{
		$arMeasureCode[] = $arMeasure["CODE"];
	}

	$lAdmin->AddHeaders(array(
		array(
			"id" => "CODE",
			"content" => GetMessage("CAT_MEASURE_CODE"),
			"default" => true
		),
		array(
			"id" => "MEASURE_TITLE",
			"content" => GetMessage("CAT_MEASURE_MEASURE_TITLE"),
			"default" => true
		),
		array(
			"id" => "SYMBOL_RUS",
			"content" => GetMessage("CAT_MEASURE_SYMBOL_RUS"),
			"default" => true
		),
		array(
			"id" => "SYMBOL_INTL",
			"content" => GetMessage("CAT_MEASURE_SYMBOL_INTL"),
			"default" => true
		),
		array(
			"id" => "SYMBOL_LETTER_INTL",
			"content" => GetMessage("CAT_MEASURE_SYMBOL_LETTER_INTL"),
			"default" => false
		),
	));
	foreach($arMeasureClassifier[$mainSectionId][$subSectionId] as $code => $value)
	{
		if($code !== 'TITLE' && !in_array($code, $arMeasureCode) && $value['MEASURE_TITLE'] != '')
		{
			$arRes['CODE'] = intval($code);

			$arRows[$arRes['CODE']] = $row =& $lAdmin->AddRow($arRes['CODE']);
			$row->AddField("CODE", $value['CODE']);
			$row->AddField("MEASURE_TITLE", $value['MEASURE_TITLE']);
			$row->AddField("SYMBOL_RUS", $value['SYMBOL_RUS']);
			$row->AddField("SYMBOL_INTL", $value['SYMBOL_INTL']);
			$row->AddField("SYMBOL_LETTER_INTL", $value['SYMBOL_LETTER_INTL']);
		}
	}

	if(!$bReadOnly)
	{
		$lAdmin->AddGroupActionTable(
			array(
				array(
				'type' => "button", 'title' => GetMessage("CAT_MEASURE_ADD"), 'value' => 'add_measure', "name" => GetMessage("CAT_MEASURE_ADD"),
				),
			)
		);
	}
	if($errorMessage)
	{
		CAdminMessage::showMessage($errorMessage);
	}
	elseif($okMessage)
	{
		CAdminMessage::ShowNote($okMessage);
	}

	$lAdmin->CheckListMode();

}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
ClearVars();

$bVarsFromForm = false;

$userId = intval($USER->GetID());

?>
<script type="text/javascript">
	function makeMeasureTable()
	{
		var mainSectionId = BX('CLASSIFIER_MAIN_SECTION').value;
		var subSectionId = BX('CLASSIFIER_SUB_SECTION').value;

		window['<?=$sTableID?>'].GetAdminList("/bitrix/admin/cat_measure_edit.php?OKEI=Y&main_section=" + mainSectionId + "&sub_section=" + subSectionId + '&lang=<? echo LANGUAGE_ID;?>');
	}
</script>
<?
if($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_REQUEST["Update"]) > 0 && !$bReadOnly && check_bitrix_sessid())
{
	$IS_DEFAULT = ($_REQUEST["IS_DEFAULT"] == 'Y') ? 'Y' : 'N';

	if(intval($_REQUEST["CODE"]) <= 0)
		$errorMessage .= GetMessage("CAT_MEASURE_CODE_EMPTY")."\n";
	if(trim($_REQUEST["MEASURE_TITLE"]) == '')
		$errorMessage .= GetMessage("CAT_MEASURE_TITLE_EMPTY")."\n";

	$arFields = Array(
		"CODE" => $_REQUEST["CODE"],
		"MEASURE_TITLE" => $_REQUEST["MEASURE_TITLE"],
		"SYMBOL_RUS" => $_REQUEST["SYMBOL_RUS"],
		"SYMBOL_INTL" => $_REQUEST["SYMBOL_INTL"],
		"SYMBOL_LETTER_INTL" => $_REQUEST["SYMBOL_LETTER_INTL"],
		"IS_DEFAULT" => $IS_DEFAULT,
	);
	$DB->StartTransaction();
	if(strlen($errorMessage) == 0 && $ID > 0 && $res = CCatalogMeasure::update($ID, $arFields))
	{
		$ID = $res;
		$DB->Commit();

		if(strlen($_REQUEST["apply"])<=0)
			LocalRedirect("/bitrix/admin/cat_measure_list.php?lang=".LANG."&".GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/cat_measure_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false));
	}
	elseif(strlen($errorMessage) == 0 && $ID == 0 && $res = CCatalogMeasure::add($arFields))
	{
		$ID = $res;
		$DB->Commit();
		if(strlen($_REQUEST["apply"]) <= 0)
			LocalRedirect("/bitrix/admin/cat_measure_list.php?lang=".LANG."&".GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/cat_measure_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false));
	}
	else
	{
		if($ex = $APPLICATION->GetException())
		{
			$errorMessage .= $ex->GetString()."<br>";
		}
		$bVarsFromForm = true;
		$DB->Rollback();
	}
}

if($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CAT_MEASURE_TITLE_EDIT")));
elseif($_REQUEST["OKEI"] == 'Y')
	$APPLICATION->SetTitle(GetMessage("CAT_MEASURE_TITLE_OKEI"));
else
	$APPLICATION->SetTitle(GetMessage("CAT_MEASURE_TITLE_NEW"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($ID > 0)
{
	$arSelect = array(
		"ID",
		"CODE",
		"MEASURE_TITLE",
		"SYMBOL_RUS",
		"SYMBOL_INTL",
		"SYMBOL_LETTER_INTL",
		"IS_DEFAULT",
	);

	$dbResult = CCatalogMeasure::GetList(array(), array('ID' => $ID), false, false, $arSelect);
	if(!$dbResult->ExtractFields("str_"))
		$ID = 0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_measure", "", "str_");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CAT_MEASURE_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_measure_list.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

if($ID > 0 && !$bReadOnly)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CAT_MEASURE_ADD"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/cat_measure_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("CAT_MEASURE_DELETE"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessage("CAT_MEASURE_DELETE_CONFIRM")."')) window.location='/bitrix/admin/cat_measure_list.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

<form enctype="multipart/form-data" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="catalog_measure_edit">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?echo LANG ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("CAT_MEASURE_TITLE"), "ICON" => "catalog", "TITLE" => GetMessage("CAT_MEASURE_TITLE_ONE")),
	);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	?>

	<?
	$tabControl->BeginNextTab();
	?>
<?if($classifierMode):?>
	<tr class="adm-detail-required-field" id="classifier-main-section" >
		<td><?= GetMessage("CAT_MEASURE_CLASSIFIER_MAIN") ?>:</td>
		<td>
			<select id="CLASSIFIER_MAIN_SECTION" name="CLASSIFIER_MAIN_SECTION" onchange="makeMeasureTable()"<?=($bReadOnly) ? " disabled" : ""?>>
			<?foreach($arMeasureClassifier as $key => $val)
			{
				if(is_array($val) && count($val) > 0)
				{
					$selected = ($key == $mainSectionId) ? 'selected' : '';
					echo"<option ".$selected." value=".$key.">".$val["TITLE"]."</option>";
				}
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="adm-detail-required-field" id="classifier-sub-section" >
		<td><?= GetMessage("CAT_MEASURE_CLASSIFIER_SUB") ?>:</td>
		<td>
			<select id="CLASSIFIER_SUB_SECTION" name="CLASSIFIER_SUB_SECTION" onchange="makeMeasureTable()" <?=($bReadOnly) ? " disabled" : ""?>>
				<?foreach($arMeasureClassifier[0] as $key => $val)
				{
					if($key !== 'TITLE' && is_array($val))
					{
						$selected = ($key == $subSectionId) ? 'selected' : '';
						echo"<option ".$selected." value=".$key.">".$val["TITLE"]."</option>";
					}
				}
				?>
			</select>
		</td>
	</tr>
	<?else:?>
	<?if($ID > 0):
		?>
		<tr>
			<td>ID:</td>
			<td><?= $ID ?></td>
		</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?= GetMessage("CAT_MEASURE_DEFAULT") ?>:</td>
		<td width="60%">
			<input type="checkbox" name="IS_DEFAULT" value="Y" <?if(($str_IS_DEFAULT=='Y')) echo"checked";?> size="50" />
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("CAT_MEASURE_CODE") ?>:</td>
		<td>
			<input type="text" style="width:50px" name="CODE" value="<?=$str_CODE?>" />
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= GetMessage("CAT_MEASURE_MEASURE_TITLE") ?>:</td>
		<td>
			<input type="text" style="width:300px" name="MEASURE_TITLE" value="<?=$str_MEASURE_TITLE?>" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("CAT_MEASURE_SYMBOL_RUS") ?>:</td>
		<td>
			<input type="text" style="width:100px" name="SYMBOL_RUS" value="<?=$str_SYMBOL_RUS?>" />
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("CAT_MEASURE_SYMBOL_INTL") ?>:</td>
		<td>
			<input type="text" style="width:100px" name="SYMBOL_INTL" value="<?=$str_SYMBOL_INTL?>" size="45" />
		</td>
	</tr><tr>
	</tr>
	<tr>
		<td><?= GetMessage("CAT_MEASURE_SYMBOL_LETTER_INTL") ?>:</td>
		<td><input type="text" style="width:100px" name="SYMBOL_LETTER_INTL" value="<?=$str_SYMBOL_LETTER_INTL?>" size="15" />
		</td>
	</tr>
<?endif;?>
<?
$tabControl->EndTab();
if(!$classifierMode)
{
	$tabControl->Buttons(
		array(
			"disabled" => $bReadOnly,
			"back_url" => "/bitrix/admin/cat_measure_list.php?lang=".LANG."&".GetFilterParams("filter_", false)
		)
	);
}
$tabControl->End();
?>
</form>
<?
if($classifierMode)
{
	$lAdmin->DisplayList();
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>