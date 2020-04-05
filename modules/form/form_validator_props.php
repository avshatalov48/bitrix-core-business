<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D")
	die(GetMessage("ACCESS_DENIED"));

if (!CModule::IncludeModule('form'))
	die(GetMessage("FORM_MODULE_NOT_INSTALLED"));


IncludeModuleLangFile(__FILE__);

$validator_name = $_REQUEST["validator"];
$rsValidators = CFormValidator::GetAllList();
$bFound = false;
while ($arValidatorInfo = $rsValidators->Fetch())
{
	if ($arValidatorInfo["NAME"] == $validator_name)
	{
		$bFound = true;
		break;
	}
}
if ($bFound)
{
	if (is_array($arValidatorInfo["SETTINGS"]) || strlen($arValidatorInfo["SETTINGS"]) > 0)
	{

		$arSettings = call_user_func($arValidatorInfo["SETTINGS"]);

		//echo "<pre>"; print_r($arSettings); echo "</pre>";
?>
	<div class="description">
		<p>
			<b><?=htmlspecialcharsbx($arValidatorInfo["DESCRIPTION"])?></b> [<?=htmlspecialcharsbx($arValidatorInfo["NAME"])?>]
		</p>
	</div>
	<form name="val_settings_form">
		<input type="hidden" name="VALIDATOR" value="<?=htmlspecialcharsbx($validator_name)?>" />
		<table cellspacing="0" align="center">
<?
		foreach ($arSettings as $settingName => $arSetting)
		{
?>
			<tr>
				<td id="PARAM_TITLE_<?=htmlspecialcharsbx($settingName);?>"><?=htmlspecialcharsbx($arSetting["TITLE"])?>:</td>
				<td>
<?
			switch ($arSetting["TYPE"])
			{
				case "CHECKBOX":
					$res = "<input type=\"checkbox\" id=\"PARAM_".htmlspecialcharsbx($settingName)."\" name=\"PARAMS[".htmlspecialcharsbx($settingName)."]\" value=\"Y\"".($arSetting["DEFAULT"] == "Y" ? " checked=\"checked\"" : "")." />";
				break;

				case "DROPDOWN":
					$res = "<select id=\"PARAM_".htmlspecialcharsbx($settingName)."\" name=\"PARAMS[".htmlspecialcharsbx($settingName)."]\">";

					foreach ($arSetting["VALUES"] as $value => $title)
					{
						$res .= "<option value=\"".htmlspecialcharsbx($value)."\"".($value == $arSetting["DEFAULT"] ? " selected=\"selected\"" : "").">".htmlspecialcharsbx($title)."</option>";
					}

					$res .= "</select>";

				break;

				case "DATE":
					$res = "<input type=\"text\" name=\"PARAMS[".htmlspecialcharsbx($settingName)."]\" id=\"PARAM_".htmlspecialcharsbx($settingName)."\" value=\"".htmlspecialcharsbx($arSettings["DEFAULT"])."\" />\n".Calendar("PARAMS[".htmlspecialcharsbx($settingName)."]", "form1");
				break;

				default:
					$res = "<input type=\"text\" id=\"PARAM_".htmlspecialcharsbx($settingName)."\" name=\"PARAMS[".htmlspecialcharsbx($settingName)."]\" value=\"".htmlspecialcharsbx($arSetting["DEFAULT"])."\" />";
				break;
			} // endswitch

			echo $res;
?>
				</td>
			</tr>
<?
		} // endforeach
?>
		</table>
	</form>

<?
	}
}
else
{
	?><div align="center"><?=ShowError(GetMessage('FORM_VALIDATOR_NOT_FOUND'));?></div><?
}
