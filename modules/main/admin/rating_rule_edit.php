<?
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");

ClearVars();

if(!$USER->CanDoOperation('edit_ratings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
$message = null;

if($_SERVER['REQUEST_METHOD']=="POST" && ($_POST['save']<>"" || $_POST['apply']<>"") && check_bitrix_sessid())
{
	$arFields = array(
		"ACTIVE"					=> isset($_POST['ACTIVE'])? $_POST['ACTIVE'] : 'N',
		"ACTIVATE"				=> isset($_POST['ACTIVATE'])? $_POST['ACTIVATE'] : 'N',
		"DEACTIVATE"			=> isset($_POST['DEACTIVATE'])? $_POST['DEACTIVATE'] : 'N',
		"NAME"					=> $_POST['NAME'],
		"ENTITY_TYPE_ID"		=> $_POST['ENTITY_TYPE_ID'],
		"CONDITION_NAME"		=> $_POST['CONDITION_NAME'],
		"CONDITION_CONFIG"	=> $_POST['CONDITION_CONFIG'],
	);
	if (isset($_POST['ACTION_NAME']) && isset($_POST['ACTION_NAME']))
	{
		$arFields["ACTION_NAME"] = $_POST['ACTION_NAME'];
		$arFields["ACTION_CONFIG"] = $_POST['ACTION_CONFIG'];
	}

	if($ID>0)
		$res = CRatingRule::Update($ID, $arFields);
	else
	{
		$ID = CRatingRule::Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if($apply <> "")
		{
			\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["RATING_RULE_EDIT_MESSAGE"]=array("MESSAGE"=>GetMessage("RATING_RULE_EDIT_SUCCESS"), "TYPE"=>"OK");
			LocalRedirect("rating_rule_edit.php?ID=".$ID."&lang=".LANG);
		}
		else
			LocalRedirect(($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"rating_rule_list.php?lang=".LANG));
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("RATING_RULE_EDIT_ERROR"), $e);
	}
}

// default value
$str_NAME 			= isset($_REQUEST["NAME"]) ? htmlspecialcharsbx($_REQUEST["NAME"]) : GetMessage("RATING_RULE_DEF_NAME");
$str_ENTITY_TYPE_ID = isset($_REQUEST["ENTITY_TYPE_ID"]) ? htmlspecialcharsbx($_REQUEST["ENTITY_TYPE_ID"]) : 'USER';
$str_ACTIVE 		= isset($_REQUEST["ACTIVE"]) && $_REQUEST["ACTIVE"] == 'Y' ? 'Y' : 'N';
$str_CONDITION_NAME = isset($_REQUEST["CONDITION_NAME"]) ? htmlspecialcharsbx($_REQUEST["CONDITION_NAME"]) : 'RATING';
$str_ACTION_NAME	= isset($_REQUEST["ACTION_NAME"]) ? htmlspecialcharsbx($_REQUEST["ACTION_NAME"]) : 'ADD_TO_GROUP';
$bTypeChange 		= isset($_POST["action"]) && $_POST["action"] == 'type_changed' ? true : false;

//when creating a new rule, default check on
if ($ID == 0 && empty($_POST))
	$str_ACTIVE = 'Y';

if($ID>0 && !$bTypeChange)
{
	$ratingRule = CRatingRule::GetByID($ID);
	if(!($arRatingRule = $ratingRule->ExtractFields("str_")))
		$ID=0;
	$str_CONDITION_CONFIG = unserialize(htmlspecialcharsback($str_CONDITION_CONFIG), ['allowed_classes' => false]);
	$str_ACTION_CONFIG = unserialize(htmlspecialcharsback($str_ACTION_CONFIG), ['allowed_classes' => false]);
}

$sDocTitle = ($ID>0? GetMessage("MAIN_RATING_RULE_EDIT_RECORD", array("#ID#"=>$ID)) : GetMessage("MAIN_RATING_RULE_NEW_RECORD"));
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("RATING_RULE_LIST"),
		"TITLE"=>GetMessage("RATING_RULE_LIST_TITLE"),
		"LINK"=>"rating_rule_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("RATING_RULE_EDIT_ADD"),
		"TITLE"=>GetMessage("RATING_RULE_EDIT_ADD_TITLE"),
		"LINK"=>"rating_rule_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("RATING_RULE_EDIT_DEL"),
		"TITLE"=>GetMessage("RATING_RULE_EDIT_DEL_TITLE"),
		"LINK"=>"javascript:if(confirm('".GetMessage("RATING_RULE_EDIT_DEL_CONF")."')) window.location='rating_rule_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if(is_array(\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["RATING_RULE_EDIT_MESSAGE"]))
{
	CAdminMessage::ShowMessage(\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["RATING_RULE_EDIT_MESSAGE"]);
	\Bitrix\Main\Application::getInstance()->getSession()["SESS_ADMIN"]["RATING_RULE_EDIT_MESSAGE"]=false;
}

if($message)
	echo $message->Show();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("RATING_RULE_EDIT_TAB_MAIN"), "TITLE"=>GetMessage("RATING_RULE_EDIT_TAB_MAIN_TITLE")),
);

$tabControl = new CAdminForm("rating_rule", $aTabs, true, true);
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


$tabControl->AddEditField("NAME", GetMessage('RATING_RULE_EDIT_FRM_NAME'), true, array("size"=>54, "maxlength"=>255), $str_NAME);

$tabControl->BeginCustomField("ACTIVE", GetMessage('RATING_RULE_EDIT_FRM_ACTIVE'), false);
?>
	<tr>
		<td><?=GetMessage("RATING_RULE_EDIT_FRM_ACTIVE")?></td>
		<td><?=InputType("checkbox", "ACTIVE", "Y", $str_ACTIVE)?></td>
	</tr>
<?
$tabControl->EndCustomField("ACTIVE");

$tabControl->BeginCustomField("ENTITY_TYPE_ID", GetMessage('RATING_RULE_EDIT_FRM_TYPE_ID'), true);
$arObjects = CRatingRule::GetRatingRuleObjects();
?>
	<tr style="<?=(count($arObjects)>1? '': 'display:none')?>" class="adm-detail-required-field">
		<td><?=GetMessage("RATING_RULE_EDIT_FRM_TYPE_ID")?></td>
		<td><?=SelectBoxFromArray("ENTITY_TYPE_ID", array('reference_id' => $arObjects, 'reference' => $arObjects), $str_ENTITY_TYPE_ID, "", "onChange=\"jsTypeChanged('rating_rule_form')\"");?></td>
	</tr>
<?
$tabControl->EndCustomField("ENTITY_TYPE_ID");

$tabControl->AddSection("CAT_HOW_ACTIVATE", GetMessage("RATING_RULE_EDIT_CAT_HOW_ACTIVATE"));

$tabControl->BeginCustomField("CONDITION_NAME", GetMessage('RATING_RULE_EDIT_FRM_CONDITION_NAME'), true);
$arRatingRuleConfigs = CRatingRule::GetRatingRuleConfigs($str_ENTITY_TYPE_ID);
$arConditionName = array();
foreach ($arRatingRuleConfigs["CONDITION_CONFIG"] as $configId => $arConfig)
{
	$arConditionName['reference'][] = $arConfig["NAME"];
	$arConditionName['reference_id'][] = $arConfig["ID"];
}
$arCurrentCondition = $arRatingRuleConfigs["CONDITION_CONFIG"][$str_CONDITION_NAME];
$conditionCount = count($arCurrentCondition['FIELDS']);
?>
	<tr>
		<td colspan="2">
			<table cellpadding="2" cellspacing="0" border="0" width="100%" class="edit-table">
			<tr valign="top" style="" class="adm-detail-required-field">
				<td class="field-name" style="vertical-align:middle;" width="40%">
					<?=GetMessage("RATING_RULE_EDIT_FRM_CONDITION_NAME")?>:
				</td>
				<td>
					<?=SelectBoxFromArray("CONDITION_NAME", $arConditionName, $str_CONDITION_NAME, "", "onChange=\"jsTypeChanged('rating_rule_form')\"");?>
				</td>
				<td style="font-size:1em; padding-left: 15px" width="200" rowspan="<?=$conditionCount+1?>">
				<? if(isset($arCurrentCondition['DESC'])): ?>
					<p style="margin-top:0"><?=$arCurrentCondition['DESC']?></p>
				<? endif; ?>
				</td>
			</tr>
			<?
				for ($i=0; $i<$conditionCount; $i++)
				{
					// define a default value
					$strFieldValue = isset($_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID']])?
											$_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID']] : $arCurrentCondition['FIELDS'][$i]['DEFAULT'];
					// if exist editing data
					if (isset($str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID']]))
						$strFieldValue = $str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID']];

					if (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'SELECT_CLASS')
					{
						$arSelect = array();
						$arFieldParams = array();
						foreach($arCurrentCondition['FIELDS'][$i]['PARAMS'] as $key => $value)
							$arFieldParams[$key] = &$arCurrentCondition['FIELDS'][$i]['PARAMS'][$key];

						$res = call_user_func_array(array($arCurrentCondition['FIELDS'][$i]['CLASS'], $arCurrentCondition['FIELDS'][$i]['METHOD']), $arFieldParams);
						while ($row = $res->Fetch())
						{

							$arSelect['reference'][] = '['.$row[$arCurrentCondition['FIELDS'][$i]["FIELD_ID"]].'] '.$row[$arCurrentCondition['FIELDS'][$i]["FIELD_VALUE"]];
							$arSelect['reference_id'][] = $row[$arCurrentCondition['FIELDS'][$i]["FIELD_ID"]];
						}
						?>
						<tr valign="top" style="">
							<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentCondition['FIELDS'][$i]['NAME']?>:</label></td>
							<td width="25%"><?=SelectBoxFromArray("CONDITION_CONFIG[".$arCurrentCondition['ID']."][".$arCurrentCondition['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue);?></td>
						</tr>
						<?
					}
					elseif (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'SELECT_ARRAY')
					{
						$arSelect = array();
						foreach ($arCurrentCondition['FIELDS'][$i]['PARAMS'] as $key => $value)
						{
							$arSelect['reference'][] = $value;
							$arSelect['reference_id'][] = $key;
						}

						?>
						<tr valign="top" style="">
							<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentCondition['FIELDS'][$i]['NAME']?>:</label></td>
							<td width="25%"><?=SelectBoxFromArray("CONDITION_CONFIG[".$arCurrentCondition['ID']."][".$arCurrentCondition['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "");?></td>
						</tr>
						<?
					}
					elseif (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'SELECT_ARRAY_WITH_INPUT')
					{
						// define a default value
						$strFieldValueInput = isset($_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_INPUT']])?
												$_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_INPUT']] : $arCurrentCondition['FIELDS'][$i]['DEFAULT_INPUT'];
						// if exist editing data
						if (isset($str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_INPUT']]))
							$strFieldValueInput = $str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_INPUT']];

						$arSelect = array();
						foreach ($arCurrentCondition['FIELDS'][$i]['PARAMS'] as $key => $value)
						{
							$arSelect['reference'][] = $value;
							$arSelect['reference_id'][] = $key;
						}

						?>
						<tr valign="top" style="">
							<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentCondition['FIELDS'][$i]['NAME']?>:</label></td>
							<td width="25%">
								<?=SelectBoxFromArray("CONDITION_CONFIG[".$arCurrentCondition['ID']."][".$arCurrentCondition['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "");?>
								<input type="text" name="CONDITION_CONFIG[<?=$arCurrentCondition['ID']?>][<?=$arCurrentCondition['FIELDS'][$i]['ID_INPUT']?>]" value="<?=$strFieldValueInput?>" style="width:45px;">
							</td>
						</tr>
						<?
					}
					elseif (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'INPUT_INTERVAL')
					{
						// define a default value
						$strFieldValue2 = isset($_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_2']])?
												$_POST['CONDITION_CONFIG'][$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_2']] : $arCurrentCondition['FIELDS'][$i]['DEFAULT_2'];
						// if exist editing data
						if (isset($str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_2']]))
							$strFieldValue2 = $str_CONDITION_CONFIG[$arCurrentCondition['ID']][$arCurrentCondition['FIELDS'][$i]['ID_2']];

						?>
						<tr valign="top">
							<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentCondition['FIELDS'][$i]['NAME']?>:</label></td>
							<td width="25%" style="vertical-align:middle">
								<?=GetMessage('PP_USER_CONDITION_RATING_INTERVAL_FROM')?> <input type="text" name="CONDITION_CONFIG[<?=$arCurrentCondition['ID']?>][<?=$arCurrentCondition['FIELDS'][$i]['ID']?>]" value="<?=$strFieldValue?>" style="width:45px;">
								<?=GetMessage('PP_USER_CONDITION_RATING_INTERVAL_TO')?> <input type="text" name="CONDITION_CONFIG[<?=$arCurrentCondition['ID']?>][<?=$arCurrentCondition['FIELDS'][$i]['ID_2']?>]" value="<?=$strFieldValue2?>" style="width:45px;">
							</td>
						</tr>
						<?
					}
					elseif (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'SEPARATOR')
					{
						?>
						<tr valign="top" class="heading">
							<td colspan="3"><?=$arCurrentCondition['FIELDS'][$i]['NAME']?></td>
						</tr>
						<?
					}
					elseif (isset($arCurrentCondition['FIELDS'][$i]['TYPE']) && $arCurrentCondition['FIELDS'][$i]['TYPE'] == 'TEXT')
					{
						?>
						<tr valign="top">
							<td colspan="3" style="text-align:center"><?=$arCurrentCondition['FIELDS'][$i]['NAME']?></td>
						</tr>
						<?
					}
					else
					{
						?>
						<tr valign="top">
							<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentCondition['FIELDS'][$i]['NAME']?>:</label></td>
							<td width="25%" style="vertical-align:middle"><input type="text" name="CONDITION_CONFIG[<?=$arCurrentCondition['ID']?>][<?=$arCurrentCondition['FIELDS'][$i]['ID']?>]" size="<?=intval($arCurrentCondition['FIELDS'][$i]['SIZE'])?>" value="<?=$strFieldValue?>"> <?=isset($arCurrentCondition['FIELDS'][$i]['NAME_DESC'])? $arCurrentCondition['FIELDS'][$i]['NAME_DESC']: ''?></td>
						</tr>
						<?
					}
				}
			?>
			</table>
		</td>
	</tr>
<?
$tabControl->EndCustomField("CONDITION_NAME");

if (!isset($arCurrentCondition['HIDE_ACTION']) || !$arCurrentCondition['HIDE_ACTION'])
{
	$tabControl->AddSection("CAT_WHAT_DO", GetMessage("RATING_RULE_EDIT_CAT_WHAT_DO"));

	$tabControl->BeginCustomField("ACTION_NAME", GetMessage('RATING_RULE_EDIT_FRM_ACTION_NAME'), true);
	$arActionName = array();
	foreach ($arRatingRuleConfigs["ACTION_CONFIG"] as $configId => $arConfig)
	{
		$arActionName['reference'][] = $arConfig["NAME"];
		$arActionName['reference_id'][] = $arConfig["ID"];
	}
	$arCurrentAction = $arRatingRuleConfigs["ACTION_CONFIG"][$str_ACTION_NAME];
	$actionCount = count($arCurrentAction['FIELDS']);
	?>
		<tr>
			<td colspan="2">
				<table cellpadding="2" cellspacing="0" border="0" width="100%" class="edit-table">
				<tr valign="top" style="" class="adm-detail-required-field">
					<td class="field-name" style="vertical-align:middle; text-align:right" width="40%">
						<?=GetMessage("RATING_RULE_EDIT_FRM_ACTION_NAME")?>:
					</td>
					<td>
						<?=SelectBoxFromArray("ACTION_NAME", $arActionName, $str_ACTION_NAME, "", "style=\"width: 300px\" onChange=\"jsTypeChanged('rating_rule_form')\"");?>
					</td>
					<td style="font-size:1em; padding-left: 15px" rowspan="<?=$actionCount+1?>">
					<? if(isset($arCurrentAction['DESC'])): ?>
						<p style="margin-top:0"><?=$arCurrentAction['DESC']?></p>
					<? else: ?>
						<p style="margin-top:0"><?=GetMessage("RATING_RULE_EDIT_FRM_ACTION_DESC")?></p>
					<? endif; ?>
					</td>
				</tr>
				<?
					for ($i=0; $i<$actionCount; $i++)
					{
						// define a default value
						$strFieldValue = isset($_POST['ACTION_CONFIG'][$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID']]) ?
											$_POST['ACTION_CONFIG'][$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID']] : $arCurrentAction['FIELDS'][$i]['DEFAULT'];
						// if exist editing data
						if (isset($str_ACTION_CONFIG[$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID']]))
							$strFieldValue = $str_ACTION_CONFIG[$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID']];

						if (isset($arCurrentAction['FIELDS'][$i]['TYPE']) && $arCurrentAction['FIELDS'][$i]['TYPE'] == 'SELECT_CLASS')
						{
							$arSelect = array();
							$arFieldParams = array();
							foreach($arCurrentAction['FIELDS'][$i]['PARAMS'] as $key => $value)
								$arFieldParams[$key] = &$arCurrentAction['FIELDS'][$i]['PARAMS'][$key];
							$res = call_user_func_array(array($arCurrentAction['FIELDS'][$i]['CLASS'], $arCurrentAction['FIELDS'][$i]['METHOD']), $arFieldParams);
							while ($row = $res->Fetch())
							{
								$arSelect['reference'][] = $row[$arCurrentAction['FIELDS'][$i]["FIELD_VALUE"]];
								$arSelect['reference_id'][] = $row[$arCurrentAction['FIELDS'][$i]["FIELD_ID"]];
							}
							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%"><?=SelectBoxFromArray("ACTION_CONFIG[".$arCurrentAction['ID']."][".$arCurrentAction['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "", 'style="width: 300px"');?></td>
							</tr>
							<?
						}
						else if (isset($arCurrentAction['FIELDS'][$i]['TYPE']) && $arCurrentAction['FIELDS'][$i]['TYPE'] == 'SELECT_CLASS_ARRAY')
						{
							$arSelect = array();
							$arFieldParams = array();
							foreach($arCurrentAction['FIELDS'][$i]['PARAMS'] as $key => $value)
								$arFieldParams[$key] = &$arCurrentAction['FIELDS'][$i]['PARAMS'][$key];
							$array = call_user_func_array(array($arCurrentAction['FIELDS'][$i]['CLASS'], $arCurrentAction['FIELDS'][$i]['METHOD']), $arFieldParams);
							foreach ($array as $key => $value)
							{
								$arSelect['reference'][] = $value;
								$arSelect['reference_id'][] = $key;
							}
							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%"><?=SelectBoxFromArray("ACTION_CONFIG[".$arCurrentAction['ID']."][".$arCurrentAction['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "", 'style="width: 300px"');?></td>
							</tr>
							<?
						}
						else if (isset($arCurrentAction['FIELDS'][$i]['TYPE']) && $arCurrentAction['FIELDS'][$i]['TYPE'] == 'SELECT_ARRAY')
						{
							$arSelect = array();
							foreach ($arCurrentAction['FIELDS'][$i]['PARAMS'] as $key => $value)
							{
								$arSelect['reference'][] = $value;
								$arSelect['reference_id'][] = $key;
							}
							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%"><?=SelectBoxFromArray("ACTION_CONFIG[".$arCurrentAction['ID']."][".$arCurrentAction['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "");?></td>
							</tr>
							<?
						}
						elseif (isset($arCurrentAction['FIELDS'][$i]['TYPE']) && $arCurrentAction['FIELDS'][$i]['TYPE'] == 'SELECT_ARRAY_WITH_INPUT')
						{
							// define a default value
							$strFieldValueInput = isset($_POST['ACTION_CONFIG'][$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID_INPUT']])?
													$_POST['ACTION_CONFIG'][$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID_INPUT']] : $arCurrentAction['FIELDS'][$i]['DEFAULT_INPUT'];
							// if exist editing data
							if (isset($str_ACTION_CONFIG[$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID_INPUT']]))
								$strFieldValueInput = $str_ACTION_CONFIG[$arCurrentAction['ID']][$arCurrentAction['FIELDS'][$i]['ID_INPUT']];

							$arSelect = array();
							foreach ($arCurrentCondition['FIELDS'][$i]['PARAMS'] as $key => $value)
							{
								$arSelect['reference'][] = $value;
								$arSelect['reference_id'][] = $key;
							}

							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%">
									<?=SelectBoxFromArray("CONDITION_CONFIG[".$arCurrentAction['ID']."][".$arCurrentAction['FIELDS'][$i]['ID']."]", $arSelect, $strFieldValue, "");?>
									<input type="text" name="CONDITION_CONFIG[<?=$arCurrentAction['ID']?>][<?=$arCurrentAction['FIELDS'][$i]['ID_INPUT']?>]" value="<?=$strFieldValueInput?>" style="width:45px;">
								</td>
							</tr>
							<?
						}
						elseif (isset($arCurrentAction['FIELDS'][$i]['TYPE']) && $arCurrentAction['FIELDS'][$i]['TYPE'] == 'TEXTAREA')
						{
							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%"><textarea name="ACTION_CONFIG[<?=$arCurrentAction['ID']?>][<?=$arCurrentAction['FIELDS'][$i]['ID']?>]" wrap="OFF" rows="10" cols="34"></textarea><?=$strFieldValue?></textarea></td>
							</tr>
							<?
						}
						else
						{
							?>
							<tr valign="top" style="">
								<td class="field-name" style="vertical-align:middle"><label><?=$arCurrentAction['FIELDS'][$i]['NAME']?>:</label></td>
								<td width="25%"><input type="text" name="ACTION_CONFIG[<?=$arCurrentAction['ID']?>][<?=$arCurrentAction['FIELDS'][$i]['ID']?>]" value="<?=$strFieldValue?>"></td>
							</tr>
							<?
						}
					}

				// define a default value
				$strFieldValue = isset($_REQUEST["ACTIVATE"]) && $_REQUEST["ACTIVATE"] == 'Y' ? 'Y' : (isset($str_ACTIVATE) ? $str_ACTIVATE : 'N');
				if ($ID == 0 && empty($_POST))
					$strFieldValue = $arCurrentAction['ACTIVATE_DEFAULT'];
				?>

				<?
				// define a default value
				$strFieldValue = isset($_REQUEST["DEACTIVATE"]) && $_REQUEST["DEACTIVATE"] == 'Y' ? 'Y' : (isset($str_DEACTIVATE) ? $str_DEACTIVATE : 'N');
				if ($ID == 0 && empty($_POST))
					$strFieldValue = $arCurrentAction['DEACTIVATE_DEFAULT'];
				?>

				</table>
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("ACTION_NAME");
}
$tabControl->Buttons(array(
	"disabled"=>false,
	"back_url"=>($_REQUEST["addurl"]<>""? $_REQUEST["addurl"]:"rating_rule_list.php?lang=".LANG),
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
</script>
<style type="text/css">.field-name { text-align:right; padding-top: 10px; vertical-align: top!important }</style>
<?
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
