<?
/*
 * Dialog. Chooseed fields will be shown in orders list.
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arFieldsParams = array(
	"ITEMS" => $arResult["FIELDS"],
	"CHECKED" => $arResult['VISIBLE_FIELDS'],
	"TITLE" => GetMessage('SMOL_D_FIELDS_TITLE'),
	"JS_EVENT_TAKE_CHECKBOXES_VALUES" => "onTakeCheckBoxesValues",
	"JS_RESULT_HANDLER" => "saveVisibleFields"
);


$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.interface.checkboxes',
	'.default',
	$arFieldsParams,
	false
);

?>
<script type="text/javascript">

	app.setPageTitle({title: "<?=GetMessage('SMOL_D_FIELDS_TITLE')?>"});

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: "left",
			name: "<?=GetMessage('SMOL_BACK');?>",
			callback: function()
			{
				app.closeModalDialog();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('SMOL_SAVE');?>",

			callback: function()
			{
				BX.onCustomEvent("<?=$arFieldsParams["JS_EVENT_TAKE_CHECKBOXES_VALUES"]?>");
			}
		}
	});

	saveVisibleFields = function(params)
	{
		BX.userOptions.del('sale', 'maListVisibleFields');

		if(params.arChecked.length != 0)
		{
			for(var i in params.arChecked)
				BX.userOptions.save('sale', 'maListVisibleFields', i, params.arChecked[i]);
		}

		app.onCustomEvent("onAfterOrdersListVisibleFieldsChange");
		app.closeModalDialog();
	};

</script>