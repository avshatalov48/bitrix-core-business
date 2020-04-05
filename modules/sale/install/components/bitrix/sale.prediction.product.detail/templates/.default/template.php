<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);

if (isset($arResult['REQUEST_ITEMS']))
{
	CJSCore::Init(array('ajax'));

	$injectId = 'sale_gift_product_'.rand();

	// component parameters
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedParameters = $signer->sign(
		base64_encode(serialize($arResult['_ORIGINAL_PARAMS'])),
		'bx.sale.prediction.product.detail'
	);
	$signedTemplate = $signer->sign($arResult['RCM_TEMPLATE'], 'bx.sale.prediction.product.detail');

	$frame = $this->createFrame()->begin("");
	?>

	<span id="<?=$injectId?>" class="sale_prediction_product_detail_container"></span>

	<script type="text/javascript">
		BX.ready(function(){

			var giftAjaxData = {
				'parameters':'<?=CUtil::JSEscape($signedParameters)?>',
				'template': '<?=CUtil::JSEscape($signedTemplate)?>',
				'site_id': '<?=CUtil::JSEscape($component->getSiteId())?>'
			};

			bx_sale_prediction_product_detail_load(
				'<?=CUtil::JSEscape($injectId)?>',
				giftAjaxData
			);

			BX.addCustomEvent('onHasNewPrediction', function(html){
				new BX.PopupWindow("simple-prediction", BX('<?= $arParams['BUTTON_ID'] ?>'), {
					offsetLeft: BX("<?= $arParams['BUTTON_ID'] ?>").offsetWidth + 10,
					offsetTop: -42,
					bindOptions: {
						forceTop: true
					},
					content:
					'<div class="catalog-element-popup-inner">' +
					html +
					'</div>',
//					darkMode: true,
					closeIcon: true,
					angle: {position: "left"}
				}).show();
			});
		});
	</script>

	<?
	$frame->end();
	return;
}
else
{
	?>
	<?
	if(!empty($arResult['PREDICTION_TEXT']))
	{ ?>
		<script type="text/javascript">
			BX.ready(function () {
				BX.onCustomEvent('onHasNewPrediction', ['<?= \CUtil::JSEscape($arResult['PREDICTION_TEXT']) ?>']);
			});
		</script>
		<?
	}
}