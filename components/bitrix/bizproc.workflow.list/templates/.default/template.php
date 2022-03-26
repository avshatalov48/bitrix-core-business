<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;
if (empty($arResult["GRID_TEMPLATES"]))
{
	if($arParams["CREATE_DEFAULT_TEMPLATE"] == "Y"):
	?>
		<div class="wd-help-list selected"><?=str_replace(
				"#HREF#",
				'"'.$APPLICATION->GetCurPageParam("action=create_default&".bitrix_sessid_get(), array("action", "sessid")).'"',
				GetMessage("WD_EMPTY"))?>
		</div>
	<?
	else:
	?>
		<div class="wd-help-list selected"><?=GetMessage("WD_EMPTY_DEFAULT") ?>
		</div>
	<?
	endif;
}
else
{
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID" => $arResult["GRID_ID"],
		"HEADERS" => array(
			array("id" => "NAME", "name" =>GetMessage("BPATT_NAME"), "default" => true, "sort" => "NAME"),
			array("id" => "MODIFIED", "name" => GetMessage("BPATT_MODIFIED"), "default" => true, "sort" => "MODIFIED"),
			array("id" => "USER", "name" => GetMessage("BPATT_USER"), "default" => true),
			array("id" => "AUTO_EXECUTE", "name" => GetMessage("BPATT_AUTO_EXECUTE"), "default" => true, "sort" => "AUTO_EXECUTE")
		), 
		"SORT" => $arResult['SORT'],
		"ROWS" => $arResult["GRID_TEMPLATES"],
		"FOOTER" => array(array("title" => GetMessage("BPATT_ALL"), "value" => count($arResult["GRID_TEMPLATES"]))),
		"EDITABLE" => false,
		"ACTIONS" => array(
			"delete" => true
		),
		"ACTION_ALL_ROWS" => false,
		"NAV_OBJECT" => $arResult["NAV_RESULT"],
		"AJAX_MODE" => "Y",
	),
	($this->__component->__parent ? $this->__component->__parent : $component)
);?><?
}
/****************************************************************************/
if (IsModuleInstalled("bizprocdesigner")):
?>
<br />
<div class="wd-help-list selected">
	<?=GetMessage("BPATT_HELP1_TEXT_1")?><br />
	<?=GetMessage("BPATT_HELP2_TEXT_1")?>
</div>
<?
endif;
?>