<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/admin_perfmon/styles.css');

$bPerfmonModuleInstalled = IsModuleInstalled("perfmon");
if($bPerfmonModuleInstalled):
	$mark_value = str_replace(".", ",", (string)(double)COption::GetOptionString("perfmon", "mark_php_page_rate", ""));
	if($mark_value > 0):
		$text2 = GetMessage("GD_PERFMON_CUR");
	else:
		$text2 = str_replace(array("#STARTLINK#", "#ENDLINK#"), ($GLOBALS["APPLICATION"]->GetGroupRight("perfmon") >= "W" ? array('<a href="/bitrix/admin/perfmon_panel.php?lang='.LANGUAGE_ID.'">', '</a>') : array('', '')), GetMessage("GD_PERFMON_NO_RES"));
	endif;
else:
	$text2 = GetMessage("GD_PERFMON_NO_MODULE_INST");
endif;

?><div class="bx-gadgets-content-layout-perform"><div class="bx-gadgets-title"><?=GetMessage("GD_PERFMON")?></div><?
?><div class="bx-gadget-bottom-cont<?=(
	(
		!$bPerfmonModuleInstalled
		&& $GLOBALS["USER"]->CanDoOperation('edit_other_settings')
	)
	|| (
		$bPerfmonModuleInstalled
		&& (
			$GLOBALS["APPLICATION"]->GetGroupRight("perfmon") >= "W"
			|| $mark_value > 0
		)
	)
		? " bx-gadget-bottom-button-cont"
		: ""
)?><?=($mark_value > 0 ? " bx-gadget-mark-cont" : "")?>"><?
	if (!$bPerfmonModuleInstalled)
	{
		if ($GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
		{
			?><a class="bx-gadget-button bx-gadget-button-clickable" href="/bitrix/admin/module_admin.php?id=perfmon&install=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>">
				<div class="bx-gadget-button-lamp"></div>
				<div class="bx-gadget-button-text"><?=GetMessage("GD_PERFMON_ON")?></div>
			</a><?
		}
	}
	else
	{
		if ($GLOBALS["APPLICATION"]->GetGroupRight("perfmon") >= "W")
		{
			?><a class="bx-gadget-button bx-gadget-button-clickable<?=($mark_value > 0 ? " bx-gadget-button-active" : "")?>" href="/bitrix/admin/perfmon_panel.php?lang=<?=LANGUAGE_ID?>">
				<div class="bx-gadget-button-lamp"></div>
				<div class="bx-gadget-button-text"><?=GetMessage(($mark_value > 0 ? "GD_PERFMON_TESTED" : "GD_PERFMON_TEST"))?></div>
			</a><?
		}
		elseif($mark_value > 0)
		{
			?><div class="bx-gadget-button bx-gadget-button-active">
				<div class="bx-gadget-button-lamp"></div>
				<div class="bx-gadget-button-text"><?=GetMessage("GD_PERFMON_TESTED")?></div>
			</div><?
		}

		if ($mark_value > 0)
		{
			?><div class="bx-gadget-mark"><?=$mark_value?></div><?
		}
	}
	?><div class="bx-gadget-desc<?=($mark_value > 0 ? " bx-gadget-desc-wmark" : "")?>"><?=$text2?></div><?
?></div></div>
<div class="bx-gadget-shield"></div>