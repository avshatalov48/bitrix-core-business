<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

ClearVars();

if(!$USER->CanDoOperation('edit_ratings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($_REQUEST["ID"]);
$message = null;
$bVarsFromForm = false;

if($_SERVER['REQUEST_METHOD']=="POST" && ($_POST['save']<>"" || $_POST['apply']<>"") && check_bitrix_sessid())
{
	$arFields = array(
		"ACTIVE"				=> isset($_POST['ACTIVE'])? $_POST['ACTIVE'] : 'N',
		"NAME"					=> $_POST['NAME'],
		"ENTITY_ID"				=> $_POST['ENTITY_ID'],
		"CALCULATION_METHOD"	=> $_POST['CALCULATION_METHOD'],
		"CONFIGS"				=> $_POST['CONFIGS'],
		"POSITION"				=> isset($_POST['POSITION'])? 'Y' : 'N',
		"AUTHORITY"				=> isset($_POST['AUTHORITY'])? 'Y' : 'N',
		"NEW_CALC"				=> isset($_POST['NEW_CALC'])? 'Y' : 'N',
	);
	if($ID>0)
		$res = CRatings::Update($ID, $arFields);
	else
	{
		$ID = CRatings::Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if($_POST["apply"] <> "")
		{
			$_SESSION["SESS_ADMIN"]["RATING_EDIT_MESSAGE"]=array("MESSAGE"=>GetMessage("RATING_EDIT_SUCCESS"), "TYPE"=>"OK");
			LocalRedirect("rating_edit.php?ID=".$ID."&lang=".LANG);
		}
		else
			LocalRedirect(($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"rating_list.php?lang=".LANG));
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("RATING_EDIT_ERROR"), $e);
		$bVarsFromForm = true;
	}
}

$bTypeChange = isset($_POST["action"]) && $_POST["action"] == 'type_changed' ? true : false;
$str_NAME = isset($_REQUEST["NAME"]) ? htmlspecialcharsbx($_REQUEST["NAME"]) : GetMessage("RATING_DEF_NAME");
$str_ENTITY_ID = isset($_REQUEST["ENTITY_ID"]) ? htmlspecialcharsbx($_REQUEST["ENTITY_ID"]) : 'USER';
$str_CALCULATION_METHOD = isset($_REQUEST["CALCULATION_METHOD"]) ? IntVal($_REQUEST["CALCULATION_METHOD"]) : '1';
$str_ACTIVE = isset($_REQUEST["ACTIVE"]) && $_REQUEST["ACTIVE"] == 'Y' ? 'Y' : 'N';
$str_POSITION = isset($_REQUEST["POSITION"]) && $_REQUEST["POSITION"] == 'Y' ? 'Y' : 'N';
$str_AUTHORITY = isset($_REQUEST["AUTHORITY"]) && $_REQUEST["AUTHORITY"] == 'Y' ? 'Y' : 'N';
$str_CONFIGS = null;

if ($ID == 0 && empty($_POST))
{
	$str_ACTIVE = 'Y';
	$str_POSITION = 'Y';
}
if($ID>0 && !$bTypeChange)
{
	$raging = CRatings::GetByID($ID);
	if(!($raging_arr = $raging->ExtractFields("str_")))
		$ID=0;
	$str_CONFIGS = unserialize(htmlspecialcharsback($str_CONFIGS));
}

$sDocTitle = ($ID>0? GetMessage("MAIN_RATING_EDIT_RECORD", array("#ID#"=>$ID)) : GetMessage("MAIN_RATING_NEW_RECORD"));
$APPLICATION->SetTitle($sDocTitle);
$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/ratings.css");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("RATING_LIST"),
		"TITLE"=>GetMessage("RATING_LIST_TITLE"),
		"LINK"=>"rating_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{

	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("RATING_EDIT_ADD"),
		"TITLE"=>GetMessage("RATING_EDIT_ADD_TITLE"),
		"LINK"=>"rating_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("RATING_EDIT_DEL"),
		"TITLE"=>GetMessage("RATING_EDIT_DEL_TITLE"),
		"LINK"=>"javascript:if(confirm('".GetMessage("RATING_EDIT_DEL_CONF")."')) window.location='rating_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if(is_array($_SESSION["SESS_ADMIN"]["RATING_EDIT_MESSAGE"]))
{
	CAdminMessage::ShowMessage($_SESSION["SESS_ADMIN"]["RATING_EDIT_MESSAGE"]);
	$_SESSION["SESS_ADMIN"]["RATING_EDIT_MESSAGE"]=false;
}

if($message)
	echo $message->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("RATING_EDIT_TAB_MAIN"), "TITLE"=>GetMessage("RATING_EDIT_TAB_MAIN_TITLE")),
);

$tabControl = new CAdminForm("rating", $aTabs);
$tabControl->BeginEpilogContent();
?>
<?=bitrix_sessid_post()?>
	<input type="hidden" name="ID" value=<?=$ID?>>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="action" value="" id="action">
<?if($_REQUEST["addurl"]<>""):?>
	<input type="hidden" name="addurl" value="<?echo htmlspecialcharsbx($_REQUEST["addurl"])?>">
<?endif;?>
<?
$tabControl->EndEpilogContent();
$tabControl->Begin();

$tabControl->BeginNextFormTab();

$tabControl->AddEditField("NAME", GetMessage('RATING_EDIT_FRM_NAME'), true, array("size"=>54, "maxlength"=>255), $str_NAME);

$tabControl->BeginCustomField("ENTITY_ID", GetMessage('RATING_EDIT_FRM_TYPE_ID'), true);
$arObjects = CRatings::GetRatingObjects();
?>
	<tr style="<?=(count($arObjects)>1? '': 'display:none')?>" class="adm-detail-required-field">
		<td><?=GetMessage("RATING_EDIT_FRM_TYPE_ID")?></td>
		<td><?=SelectBoxFromArray("ENTITY_ID", array('reference_id' => $arObjects, 'reference' => $arObjects), $str_ENTITY_ID, "", "onChange=\"jsTypeChanged('rating_form')\"");?></td>
	</tr>
<?
$tabControl->EndCustomField("ENTITY_ID");

$tabControl->BeginCustomField("CALCULATION_METHOD", GetMessage('RATING_EDIT_FRM_CALC_METHOD'), true);
$arCalcMethod = array(
	"reference" => Array(GetMessage('RATING_EDIT_CALC_METHOD_SUM'), GetMessage('RATING_EDIT_CALC_METHOD_AVG')),
	"reference_id" => Array("SUM", "AVG"),
);
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("RATING_EDIT_FRM_CALC_METHOD")?></td>
		<td width="60%"><?=SelectBoxFromArray("CALCULATION_METHOD", $arCalcMethod, $str_CALCULATION_METHOD);?></td>
	</tr>
<?
$tabControl->EndCustomField("CALCULATION_METHOD");


$tabControl->BeginCustomField("ACTIVE", GetMessage('RATING_EDIT_FRM_ACTIVE'), false);
?>
	<tr>
		<td><?=GetMessage("RATING_EDIT_FRM_ACTIVE")?></td>
		<td><?=InputType("checkbox", "ACTIVE", "Y", $str_ACTIVE)?></td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVE");

$tabControl->AddSection("CAT_RATING_COMPONENT", GetMessage("RATING_EDIT_CAT_RATING_COMPONENT"));

$arRatingConfigs = CRatings::GetRatingConfigs($str_ENTITY_ID);
$tabControl->BeginCustomField("CAT_WHAT_CNT_FORM", '', true);
?>
	<tr>
		<td width="100%" colspan="2">
<?
$aTabs2 = Array();
foreach ($arRatingConfigs as $arConfigModule => $arConfigModuleValue)
	$aTabs2[] = Array("DIV"=>"panel_".$arConfigModule, "TAB" => $arConfigModuleValue['MODULE_NAME'], "TITLE" => $arConfigModuleValue['MODULE_NAME']);

$tabControl2 = new CAdminViewTabControl("tabControl2", $aTabs2);
$tabControl2->Begin();
foreach ($arRatingConfigs as $arConfigModule => $arConfigModuleValue)
{
	$tabControl2->BeginNextTab();
	foreach ($arConfigModuleValue as $arConfigType => $arConfigTypeValue)
	{
		if (is_array($arConfigTypeValue))
		{
			foreach ($arConfigTypeValue as $configId => $arConfig)
			{
				$bBlockException = false;
				if (isset($arConfig['EXCEPTION_METHOD']))
				{
					$exceptionText = call_user_func(array($arConfig['CLASS'], $arConfig['EXCEPTION_METHOD']));
					if (!($exceptionText == null || $exceptionText === false))
					{
						$bBlockException = true;
						?>
						<div class="rating-component-exception"><?=$exceptionText?></div><br>
						<?
					}
				}
				$FIELD_COUNT = count($arConfig['FIELDS']);
				// define a default value
				$bGroupFieldStatus = isset($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']]['ACTIVE'])?
					($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']]['ACTIVE']) : ($ID>0 ? false : true);

				// if exist editing data and block configuration is active
				if (isset($str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']]['ACTIVE']) &&
					$str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']]['ACTIVE'] == 'Y')
					$bGroupFieldStatus = true;

				?>
				<table cellpadding="2" cellspacing="0" width="100%" class="rating-table">
				<tr><td colspan="2" class="rating-table-td rating-component-gap"></td></tr>
				<tr class="rating-table-header heading-left">
					<td colspan="2" class="rating-table-header-td">
						<input type="checkbox" name="CONFIGS[<?=$arConfigModule?>][<?=$arConfigType?>][<?=$arConfig['ID']?>][ACTIVE]" value="Y" <?=($bGroupFieldStatus && !$bBlockException ? "checked" : "")?> <?=($bBlockException ? "disabled" : "")?> id="<?=$configId?>_block" onclick="jsChangeDisplayRatingBlock('<?=$configId?>')">
						<label for="<?=$configId?>_block" onclick="jsChangeDisplayRatingBlock('<?=$configId?>')"><?=$arConfig['NAME']?></label>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2" class="rating-table-component-td rating-component-td">
						<div id="<?=$configId?>_div" style="display:<?=($bGroupFieldStatus && !$bBlockException ? "block" : "none")?>; padding: 4px">
							<table cellpadding="0" cellspacing="0" border="0" width="100%" class="rating-table-component-table edit-table">
							<tr valign="top" style="">
								<td class="rating-table-component-table-td rating-table-component-table-td-1">
									<table cellpadding="3" cellspacing="0" class="rating-table-component-table-td-table" align="right">
									<?
									for ($i=0; $i<$FIELD_COUNT; $i++)
									{
										if (isset($arConfig['FIELDS'][$i]['TYPE']) && $arConfig['FIELDS'][$i]['TYPE'] == 'SELECT_ARRAY_WITH_INPUT')
										{
											// define a default value
											$strFieldValue = isset($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]) ?
												($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]) : $arConfig['FIELDS'][$i]['DEFAULT'];
											// if exist editing data and block configuration is active
											if (isset($str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]))
												$strFieldValue = $str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']];

											// define a default value
											$strFieldValueInput = isset($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID_INPUT']]) ?
												($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID_INPUT']]) : $arConfig['FIELDS'][$i]['DEFAULT_INPUT'];
											// if exist editing data and block configuration is active
											if (isset($str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID_INPUT']]))
												$strFieldValue = $str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID_INPUT']];

											$arSelect = array();
											foreach ($arConfig['FIELDS'][$i]['PARAMS'] as $key => $value)
											{
												$arSelect['reference'][] = $value;
												$arSelect['reference_id'][] = $key;
											}

											?>
											<tr valign="top">
												<td class="rating-table-component-table-td-table-td rating-table-component-table-td-table-td-1 field-name" style="vertical-align:middle"><label><? echo isset($arConfig['FIELDS'][$i]['NAME'])? $arConfig['FIELDS'][$i]['NAME']: GetMessage('RATING_FIELDS_DEF_NAME')?></label></td>
												<td class="rating-table-component-table-td-table-td rating-table-component-table-td-table-td-2" width="25%" >
													<?=SelectBoxFromArray("CONFIGS[$arConfigModule][$arConfigType>][".$arConfig['ID']."][".$arConfig['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "");?>
													<input type="text" name="CONFIGS[<?=$arConfigModule?>][<?=$arConfigType?>][<?=$arConfig['ID']?>][<?=$arConfig['FIELDS'][$i]['ID_INPUT']?>]" value="<?=htmlspecialcharsbx($strFieldValueInput)?>" style="width:45px;">
												</td>
											</tr>
											<?
										}
										else
										{
											// define a default value
											$strFieldValue = isset($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]) ?
												($_POST['CONFIGS'][$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]) : $arConfig['FIELDS'][$i]['DEFAULT'];
											// if exist editing data and block configuration is active
											if (isset($str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']]))
												$strFieldValue = $str_CONFIGS[$arConfigModule][$arConfigType][$arConfig['ID']][$arConfig['FIELDS'][$i]['ID']];
											?>
											<tr valign="top" style="">
												<td class="rating-table-component-table-td-table-td rating-table-component-table-td-table-td-1 field-name" style="vertical-align:middle"><label><? echo isset($arConfig['FIELDS'][$i]['NAME'])? $arConfig['FIELDS'][$i]['NAME']: GetMessage('RATING_FIELDS_DEF_NAME')?></label></td>
												<td class="rating-table-component-table-td-table-td rating-table-component-table-td-table-td-2" width="20%"><input type="text" name="CONFIGS[<?=$arConfigModule?>][<?=$arConfigType?>][<?=$arConfig['ID']?>][<?=$arConfig['FIELDS'][$i]['ID']?>]" value="<?=htmlspecialcharsbx($strFieldValue)?>"></td>
											</tr>
											<?
										}
									}
									?>
									</table>
								</td>
								<td width="50%" class="rating-table-component-table-td rating-table-component-table-td-2 rating-component-descr" style="padding-left:10px;" rowspan="<?=$FIELD_COUNT?>">
								<? if(isset($arConfig['DESC'])): ?>
									<p style="margin-top:5px"><?=$arConfig['DESC']?></p>
								<? else: ?>
									<p><?=GetMessage('RATING_FIELDS_DEF_DESC')?></p>
								<? endif; ?>
								<? if(isset($arConfig['FORMULA'])): ?>
									<p class="formula"><?=$arConfig['FORMULA']?></p>
								<? else: ?>
									<p class="formula"><?=GetMessage('RATING_FIELDS_DEF_FORMULA')?></p>
								<? endif; ?>
								<? if(isset($arConfig['FORMULA_DESC'])): ?>
									<p><?=$arConfig['FORMULA_DESC']?></p>
								<? else: ?>
									<p><?=GetMessage('RATING_FIELDS_DEF_FORMULA_DESC')?></p>
								<? endif; ?>
								</td>
							</tr>
						</table>
						</div>
					</td>
				</tr>
				<tr><td colspan="2" class="rating-table-td rating-component-gap"></td></tr>
				</table>
			<?
			}
		}
	}
}
$tabControl2->End();
?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("CAT_WHAT_CNT_FORM");
$tabControl->AddSection("CAT_WHAT_NEW_CALC", GetMessage("RATING_EDIT_CAT_WHAT_NEW_CALC"));
$tabControl->AddCheckBoxField("POSITION", GetMessage('RATING_EDIT_FRM_POSITION'), false, "Y", ($str_POSITION == 'Y' ? true : false), array());

if ($str_ENTITY_ID == 'USER')
	$tabControl->AddCheckBoxField("AUTHORITY", GetMessage('RATING_EDIT_FRM_AUTHORITY'), false, "Y", ($str_AUTHORITY == 'Y' ? true : false), array());
if($ID>0)
	$tabControl->AddCheckBoxField("NEW_CALC", GetMessage('RATING_EDIT_FRM_NEW_CALC'), false, "Y", false, array());

$tabControl->Buttons(array(
	"disabled"=>false,
	"back_url"=>($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"rating_list.php?lang=".LANG),
));
$tabControl->Show();
$tabControl->ShowWarnings($tabControl->GetName(), $message);
?>
<script language="javascript">
function jsTypeChanged(form_id)
{
	var _form = document.forms[form_id];
	var _flag = document.getElementById('action');

	if(_form)
	{
		_flag.value = 'type_changed';
		_form.submit();
	}
}
function jsChangeDisplayRatingBlock(block_id)
{
	var _div = document.getElementById(block_id+'_div');
	var _input = document.getElementById(block_id+'_block');

	_div.style.display = (_input.checked? "block" : "none");
}
</script>

<?
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
