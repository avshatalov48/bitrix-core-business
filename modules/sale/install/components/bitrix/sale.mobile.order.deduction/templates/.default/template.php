<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arData = array();

if($arParams['DEDUCTED'] == 'N')
{
	if($arResult["USE_STORES"])
	{
		foreach ($arResult["BASKET"] as $basketItem)
		{
			$arRows = array();
			$storeLink = CHTTP::urlAddParams(
				$arResult["STORE_PAGE"],
					array(
						"product_id" => $basketItem["ID"]
					)
			);

			$barcodeLink = CHTTP::urlAddParams(
				$arResult["BARCODE_PAGE"],
					array(
						"product_id" => $basketItem["ID"]
					)
			);

			$arRows[] = array(
				"TYPE" => "CUSTOM",
				"HTML_DATA" =>
					'<ul>'.
						'<li id="store_link_cont_'.$basketItem["ID"].'">'.
							'<a href="javascript:void(0);">'.GetMessage("SMODE_STORE").'</a>'.
						'</li>'.
					'</ul>'.
					'<script>'.
						'BX.ready(function(){ bsmode.makeFastButton("store_link_cont_'.$basketItem["ID"].'", "'.$storeLink.'");});'.
					'</script>'
			);

			$arRows[] = array(
				"TYPE" => "CUSTOM",
				"HTML_DATA" =>
					'<div id="bc_link_div_'.$basketItem["ID"].'" style="display: none;">'.
						'<ul>'.
							'<li id="barcode_link_cont_'.$basketItem["ID"].'">'.
								'<a href="javascript:void(0);">'.GetMessage("SMODE_BARCODE").'</a>'.
							'</li>'.
						'</ul>'.
						'<script>'.
							'BX.ready(function(){ bsmode.makeFastButton("barcode_link_cont_'.$basketItem["ID"].'", "'.$barcodeLink.'"); });'.
						'</script>'.
					'</div>'
			);

			$arData[] =
				array(
				"TITLE" => GetMessage("SMODE_PRODUCT").": ".$basketItem["NAME"],
				"TYPE" => "BLOCK",
				"DATA" => $arRows
			);
		}
	}
	else
	{
		$arData[] =
			array(
				"TITLE" => GetMessage("SMODE_TITLE"),
				"TYPE" => "BLOCK",
				"DATA" => array(
					array( "TYPE" => "TEXT_RO", "VALUE" => GetMessage("SMODE_DEDUCT_HINT"))
				)
		);
	}
}
else //if $arParams['DEDUCTED'] == 'Y'
{
	$arData[] =
		array(
			"TITLE" => GetMessage("SMODE_DEDUCT_UNDO_REASON"),
			"TYPE" => "BLOCK",
			"DATA" => array(
				array( "TYPE" => "TEXTAREA", "VALUE" => "", "ID"=>"deduct_undo_reason")
			)
	);
}

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"TITLE" => $arParams['DEDUCTED'] == 'N' ? GetMessage("SMODE_TITLE") : GetMessage("SMODE_TITLE_UNDO"),
		"DATA" => $arData
		),
	false
);

?>
<script>

	var jsParams = {
		ajaxUrl: "<?=$arResult['AJAX_URL']?>",
		orderId: "<?=$arParams["ORDER_ID"]?>",
		useStores: "<?=$arResult["USE_STORES"] ? 'Y' : 'N'?>",
		products: <?=CUtil::PhpToJsObject($arResult["BASKET"])?>
	};

	var bsmode = new __BitrixSaleMODE(jsParams);

	BX.message({
		"SMODE_READY": "<?=GetMessage("SMODE_READY")?>",
		"SMODE_ERROR": "<?=GetMessage("SMODE_ERROR")?>"
	});

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: 'left',
			name: "<?=GetMessage('SMODE_BACK');?>",
			callback: function()
			{
				app.closeController({drop: true});
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=$arParams['DEDUCTED'] == 'N' ? GetMessage('SMODE_DEDUCT') : GetMessage('SMODE_SAVE')?>",

			callback: function()
			{
				bsmode.deductOrder({
					deducted: "<?=($arParams["DEDUCTED"] == 'N' ? 'Y' : 'N')?>"
				});
			}
		}
	});

	BX.addCustomEvent('onBitrixSaleMOSResult', function(params) { bsmode.setProductStores(params);});
	BX.addCustomEvent('onBitrixSaleMOBResult', function(params) { bsmode.setProductBarcodes(params); });
	BX.addCustomEvent("onBitrixSaleMOSLoaded", function (params) {
		app.onCustomEvent('onBitrixSaleMOSGetInfo', bsmode.getProductStores(params.productId) );
	});
	BX.addCustomEvent("onBitrixSaleMOBLoaded", function (params) {
		app.onCustomEvent('onBitrixSaleMOBGetInfo', bsmode.getProductInfo(params.productId) );
	});

</script>

