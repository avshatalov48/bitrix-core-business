<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bxProductConfig = array();
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

if(isset($bxProductConfig["admin"]["index"]))
	$sProduct = $bxProductConfig["admin"]["index"];
else
	$sProduct = GetMessage("GD_INFO_product").' &quot;'.GetMessage("GD_INFO_product_name_".COption::GetOptionString("main", "vendor", "1c_bitrix")).'#VERSION#&quot;';
$sVer = ($GLOBALS['USER']->CanDoOperation('view_other_settings')? " ".SM_VERSION : "");
$sProduct = str_replace("#VERSION#", $sVer, $sProduct);

?><div class="bx-gadgets-info">
	<div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t" style="font-weight: bold; line-height: 28px;"><?=$sProduct;?></div>
	<div style="margin: 0 1px 0 1px; border-bottom: 1px solid #D7E0E8;"></div>
	<div class="bx-gadgets-content-padding-rl">
		<table class="bx-gadgets-info-site-table">
		<tr>
			<td align="left" valign="top" style="padding-bottom: 20px; line-height: 28px;"><span><?
				if ($GLOBALS['USER']->CanDoOperation('view_other_settings'))
				{
					$last_updated = COption::GetOptionString("main", "update_system_update", "-");
					?><div><?=str_replace("#VALUE#", $last_updated, GetMessage("GD_INFO_LASTUPDATE"));?></div><?
				}

				if(IsModuleInstalled("perfmon") && $GLOBALS["APPLICATION"]->GetGroupRight("perfmon") != "D")
				{
					$mark_value = (double)COption::GetOptionString("perfmon", "mark_php_page_rate", "");
					if($mark_value < 5)
						$mark_value = GetMessage("GD_INFO_PERFMON_NO_RESULT");
					?><div><?=str_replace("#VALUE#", $mark_value, GetMessage("GD_INFO_PERFMON"));?></div><?
				}

				if ($GLOBALS["USER"]->CanDoOperation('view_all_users'))
				{
					?><div><?=str_replace("#VALUE#", CUser::GetCount(), GetMessage("GD_INFO_USERS"));?></div><?
				}
			?></span></td>
			<td align="right" valign="bottom"><span style="display: inline-block; vertical-align: bottom; align: right;"><img src="/bitrix/gadgets/bitrix/admin_info/images/<?=(in_array(LANGUAGE_ID, array("ru", "en", "de"))?LANGUAGE_ID:"en")?>/logo.gif"></span></td>
		</tr>
		</table>
	</div>	
</div>