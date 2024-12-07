<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arFieldsParams = array(
	"ITEMS" => $arResult["FIELDS_LIST"],
	"CHECKED" => $arResult["VISIBLE_FIELDS"],
	"NAME" => "FIELDS[]",
	"TITLE" => GetMessage("MOBILE_APP_FILTER_VISIBLE_FIELDS"),
	"JS_EVENT_TAKE_CHECKBOXES_VALUES" => "onGetFilterFields",
	"JS_RESULT_HANDLER" => "saveVisibleFields"
);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.interface.checkboxes',
	'.default',
	$arFieldsParams,
	false
);

?>
<script>

	app.setPageTitle({title: "<?=GetMessage("MOBILE_APP_FILTER_FIELDS_LIST")?>"});

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: "left",
			name: "<?=GetMessage("MOBILE_APP_FILTER_BACK")?>",
			callback: function()
			{
				app.closeModalDialog();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage("MOBILE_APP_FILTER_SAVE")?>",

			callback: function()
			{
				BX.onCustomEvent("<?=$arFieldsParams["JS_EVENT_TAKE_CHECKBOXES_VALUES"]?>");
			}
		}
	});

	saveVisibleFields = function(params)
	{
		var visFields = {};

		for(var i in params.arChecked)
			visFields[params.arChecked[i]] = 'Y';

		app.onCustomEvent("onAfterFilterVisibleFieldsChange", visFields);
		app.closeModalDialog();
	};
</script>