<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arData = array();

$arData[] = array(
	"TYPE" => "BLOCK",
	"TITLE" => GetMessage("SMOP_ALL_SUBSCRIBES"),
	"DATA" => array(
		array(
			"VALUE" => $arResult["SUBSCRIBED_ALL"],
			"TYPE" => "2_RADIO_BUTTONS",
			"NAME" => "SUBSCRIBE_ALL",
			"ID" => "subs_2_all",
			"BUTT_Y" => array(
				"TITLE" => GetMessage("SMOP_ALL_Y"),
				"ONCLICK" => "salePush.toggleSubsBlock();"
			),
			"BUTT_N" => array(
				"TITLE" => GetMessage("SMOP_ALL_N"),
				"ONCLICK" => "salePush.toggleSubsBlock();"
			),
		)
	)
);

$arSubscriptions = array();

foreach ($arResult["EVENTS"] as $eventId => $arEvent)
{
	$arSubscriptions[] = array(
	"TITLE" => $arEvent["TITLE"],
	"CHECKED" => $arEvent["SUBSCRIBED"],
	"VALUE" => $eventId,
	"NAME" => "SUBS[]",
	"TYPE" => "CHECKBOX"
	);
}

$arData[] = array(
	"TYPE" => "BLOCK",
	"TITLE" => GetMessage("SMOP_SUBSCRIBES"),
	"ID" => "subs_items_block_id",
	"CUSTOM_ATTRS" => array(
		"style" => $arResult["SUBSCRIBED_ALL"] == "Y" ? "display: none;" : ""
	),
	"DATA" => $arSubscriptions
);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"HEAD" => GetMessage("SMOP_HEAD"),
		"DATA" => $arData
		),
	false
);

?>

<script type="text/javascript">

	app.setPageTitle({title: "<?=GetMessage('SMOP_TITLE')?>"});

	BX.message({
		"SMOP_JS_SAVING": "<?=GetMessage("SMOP_JS_SAVING")?>",
		"SMOP_JS_SAVE_ERROR": "<?=GetMessage("SMOP_JS_SAVE_ERROR")?>",
	});

	var jsParams = {
		ajaxUrl: "<?=$arResult['AJAX_URL']?>"
	};

	var salePush = new __bitrixSalePush(jsParams);

	app.addButtons({
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('SMOP_SAVE');?>",
			callback: function()
			{
				salePush.save();
			}
		}
	});
</script>

