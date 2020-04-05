<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arBarcode = array();
$barcodeIdTempl = "bar_code_##STORE_ID##_##BARCODE_ID##";
$arBarcode[] = array(
	"TYPE" => "TEXT",
	"TITLE" => GetMessage("SMOB_BARCODE_ENTER"),
	"NAME" => "BAR_CODES[]",
	"ID" => $barcodeIdTempl,
	"CUSTOM_ATTRS" => array(
		'onchange' =>'var barcode=barCodes.getInput(\''.$barcodeIdTempl.'\'); barCodes.check(\''.$barcodeIdTempl.'\', barcode);',
		'onkeydown' =>'barCodes.setInputStyle(\''.$barcodeIdTempl.'\', \'default\');'
	),
	"VALUE" => "##VALUE##"
);

$arBarcode[] = array(
	"TYPE" => "BUTTON",
	"VALUE" => GetMessage("SMOB_BARCODE_SCAN"),
	"CUSTOM_ATTRS" => array(
		"onclick" => 'barCodes.scan(\''.$barcodeIdTempl.'\');'
	)
);

$arData = array(
	array(
		"TITLE" => GetMessage("SMOB_STORE_NAME_TMPL"),
		"TYPE" => "BLOCK",
		"DATA" => $arBarcode
	)
);

ob_start();
$APPLICATION->IncludeComponent(
	"bitrix:mobileapp.edit",
	".default",
	array(
		"DATA" => $arData,
		"SKIP_FORM" => 'Y'
		),
	false
);

$barcodeBodyTempl = ob_get_contents();
ob_end_clean();

$arData = array(
	array(
		"TITLE" => GetMessage("SMOB_PRODUCT_NAME"),
		"TYPE" => "BLOCK",
		"DATA" => array(
			array(
				"TYPE" => "TEXT_RO",
				"VALUE" => htmlspecialcharsbx($arParams["PRODUCT_DATA"]["NAME"])
			)
		)
	)
);

?>
<form id="smob_barcodes_form" name="smob_barcodes_form" enctype="multipart/form-data" action="<?=$arResult["FORM_ACTION"]?>" method="POST">
<?
$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.edit',
	'.default',
	array(
		"TITLE" => GetMessage("SMODE_TITLE"),
		"DATA" => $arData,
		"SKIP_FORM" => 'Y'
		),
	false
);

?>
	<div id="smob_data_div"></div>
</form>

<script type="text/javascript">

	BX.message({
		"SMOB_CHECK_ERROR": "<?=GetMessage("SMOB_CHECK_ERROR")?>",
		"SMOB_SCAN_ERROR": "<?=GetMessage("SMOB_SACAN_ERROR")?>"

	});
	var barCodeParams = {
		ajaxUrl: '<?=$arResult['AJAX_URL']?>',
		productId: '<?=$arParams['PRODUCT_DATA']['PRODUCT_ID']?>',
		basketItemId: '<?=$arParams['PRODUCT_DATA']['ID']?>',
		orderId: '<?=$arParams['ORDER_ID']?>',
		storeIds: <?=CUtil::PhpToJsObject($arResult['STORE_IDS'])?>,
		itemTmpl:'<?=CUtil::JSEscape($barcodeBodyTempl)?>'
	};

	var barCodes = new __MASaleOrderBarcode(barCodeParams);

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: 'left',
			name: "<?=GetMessage('SMOB_BUTTON_BACK');?>",
			callback: function()
			{
				barCodes.close();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('SMOB_BUTTON_SAVE');?>",

			callback: function()
			{
				BX.addCustomEvent('onBitrixSaleMOBCheckingComplete', function() {
					app.onCustomEvent("onBitrixSaleMOBResult", {
						"productId" : "<?=$arParams["PRODUCT_DATA"]["ID"]?>",
						"productData": barCodes.prepareResult(),
					});

					barCodes.close();
				});

				if(document.activeElement && document.activeElement.onchange)
					document.activeElement.onchange();
				else
					BX.onCustomEvent("onBitrixSaleMOBCheckingComplete");

			}
		}
	});

	BX.addCustomEvent('onBitrixSaleMOBGetInfo', function (params){
		barCodes.setBarcodes(params);
	});

	BX.addCustomEvent('onOpenPageBefore', function (params){
		if(!barCodes.isScanning)
		{
			app.onCustomEvent("onBitrixSaleMOBLoaded", {
				"productId" : "<?=$arParams["PRODUCT_DATA"]["ID"]?>"
			});
		}
	});

	app.onCustomEvent("onBitrixSaleMOBLoaded", {
		"productId" : "<?=$arParams["PRODUCT_DATA"]["ID"]?>"
	});
</script>