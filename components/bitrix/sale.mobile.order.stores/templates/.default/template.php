<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arStoresIds = array();

$arData = array(
		array(
			"TITLE" => GetMessage("SMOS_PRODUCT_NAME"),
			"TYPE" => "BLOCK",
			"DATA" => array(
				array(
					"TYPE" => "TEXT_RO",
					"VALUE" => htmlspecialcharsbx($arParams["PRODUCT_DATA"]["NAME"])
				)
			)
		),
		array(
			"TITLE" => GetMessage("SMOS_MUST_DEDUCT"),
			"TYPE" => "BLOCK",
			"DATA" => array(
				array(
					"TYPE" => "TEXT_RO",
					"VALUE" => $arParams["PRODUCT_DATA"]["QUANTITY"]
				)
			)
		)
);

if (isset($arParams["PRODUCT_DATA"]["STORES"]) && is_array($arParams["PRODUCT_DATA"]["STORES"]))
{
	foreach ($arParams["PRODUCT_DATA"]["STORES"] as $arStore)
	{
		$arData[] =
			array(
				"TITLE" => htmlspecialcharsbx($arStore["STORE_NAME"])." (".$arStore["STORE_ID"].")",
				"TYPE" => "BLOCK",
				"DATA" => array(
					array(
						"TYPE" => "TEXT_RO",
						"VALUE" => GetMessage("SMOS_PRODUCT_AMOUNT").": ".$arStore["AMOUNT"]
					),
					array(
						"TYPE" => "TEXT",
						"TITLE" => GetMessage("SMOS_PRODUCT_QUANTITY"),
						"ID" => "STORE_".$arStore["STORE_ID"]."_QUANTITY"
					)
			)
		);

		$arStoresIds[] = $arStore["STORE_ID"];
	}
}

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"TITLE" => GetMessage("SMODE_TITLE"),
		"FORM_ID" => 'sale_mos_form_id',
		"DATA" => $arData,
		),
	false
);
?>
<script>

	var listParams  = {
		aStoresIds: "<?=CUtil::PhpToJsObject($arStoresIds)?>",
		neededAmount: parseFloat("<?=$arParams["PRODUCT_DATA"]["QUANTITY"]?>")
	};

	var bsmos = new __BitrixSaleMOS(listParams);

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: 'left',
			name: "<?=GetMessage('SMOS_BACK');?>",
			callback: function()
			{
				bsmos.close();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('SMOS_SAVE');?>",

			callback: function()
			{
				var qByStoresId = bsmos.getQuantities();

				if(!bsmos.checkQuantities(qByStoresId))
				{
					app.alert({
						text: "<?=GetMessage("SMOS_CHECK_ERROR")?>"
					});

					return;
				}

				app.onCustomEvent("onBitrixSaleMOSResult", {
					"productId" : "<?=$arParams["PRODUCT_DATA"]["ID"]?>",
					"qByStoresId" : qByStoresId
				});

				bsmos.close();
			}
		}
	});

	BX.addCustomEvent('onBitrixSaleMOSGetInfo', function (params){
		bsmos.setQuantities(params);
	});

	app.onCustomEvent("onBitrixSaleMOSLoaded", {
		"productId" : "<?=$arParams["PRODUCT_DATA"]["ID"]?>"
	});

</script>