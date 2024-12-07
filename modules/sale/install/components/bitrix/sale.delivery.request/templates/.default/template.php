<?
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->addExternalJs($templateFolder.'/script.js');
?>

<?if(!empty($arResult['DELIVERIES'])):?>
	<?foreach($arResult['DELIVERIES'] as $deliveryId => $delivery):?>
		<div id="delivery-request-for-<?=$deliveryId?>">
			<div class="admin-delivery-request-box">
				<div class="admin-delivery-request-inner">
					<div class="admin-delivery-request-block-left">
						<div class="admin-delivery-request-icon">
							<img class="admin-delivery-request-icon-item" src="<?=$delivery['LOGO_SRC']?>">
						</div>
						<div class="admin-delivery-request-name">
							<div class="admin-delivery-request-name-item"><?=$delivery['NAME']?></div>
							<div class="admin-delivery-request-cork">
								<a href="<?=$delivery['EDIT_LINK']?>" class="admin-delivery-request-link"><?=$delivery['DELIVERY_ID']?></a>
							</div>
						</div>
					</div>
					<div class="admin-delivery-request-block-right">
						<div class="admin-delivery-request-value">
							<div class="admin-delivery-request-value-name"><?=Loc::getMessage('SALE_CSDRT_SHIPMENTS_AMOUNT')?>:</div>
							<div class="admin-delivery-request-value-number"><?=count($delivery['SHIPMENT_IDS'])?> <?=Loc::getMessage('SALE_CSDRPJ_PIECE')?>.</div>
						</div>
						<div class="admin-delivery-request-value">
							<div class="admin-delivery-request-value-name"><?=Loc::getMessage('SALE_CSDRT_WEIGHT')?>:</div>
							<div class="admin-delivery-request-value-number"><?=$delivery['WEIGHT']?> <?=Loc::getMessage('SALE_CSDRPJ_KILO')?></div>
						</div>
					</div>
				</div>
				<input class="adm-btn-green" type="button" name="" value="<?=Loc::getMessage('SALE_CSDRT_CREATE_REQUEST')?>" onclick="BX.Sale.Delivery.Request.Component.processRequest({action: 'createDeliveryRequest', deliveryId: <?=$delivery['DELIVERY_ID']?>, shipmentIds: <?=CUtil::PhpToJSObject($delivery['SHIPMENT_IDS'])?>, weight: <?=$delivery['WEIGHT']?>}); return false;">
				<input class="adm-btn-green" type="button" name="" value="<?=Loc::getMessage('SALE_CSDRT_ADD_SHIPMENT_TO_REQUEST')?>" onclick="BX.Sale.Delivery.Request.Component.processRequest({action: 'addShipmentsToRequest', deliveryId: <?=$delivery['DELIVERY_ID']?>, shipmentIds: <?=CUtil::PhpToJSObject($delivery['SHIPMENT_IDS'])?>, weight: <?=$delivery['WEIGHT']?>}); return false;">
			</div>
		</div>
	<?endforeach;?>
<?else:?>
	<div class="admin-delivery-request-box">
		<div class="admin-delivery-request-confirm red">
			<?=Loc::getMessage('SALE_CSDRT_COMPATIBLE_DELIVERIES_NOT_FOUND')?>
		</div>
	</div>
<?endif;?>

<script>
	BX.ready(function ()
	{
		BX.message({
			"SALE_CSDRTJ_ERROR": "<?=Loc::getMessage("SALE_CSDRTJ_ERROR")?>",
			"SALE_CSDRTJ_RESPONSE_ERROR": "<?=Loc::getMessage("SALE_CSDRTJ_RESPONSE_ERROR")?>",
			"SALE_CSDRTJ_RESPONSE_PROCESSING_ERROR": "<?=Loc::getMessage("SALE_CSDRTJ_RESPONSE_PROCESSING_ERROR")?>",
			"SALE_CSDRTJ_DIALOG_CLOSE": "<?=Loc::getMessage("SALE_CSDRTJ_DIALOG_CLOSE")?>",
			"SALE_CSDRTJ_DIALOG_NEXT": "<?=Loc::getMessage("SALE_CSDRTJ_DIALOG_NEXT")?>"
		});

		BX.Sale.Delivery.Request.Component.ajaxUrl = '<?=$arResult['AJAX_URL']?>';

		<?if(!empty($arParams['ACTION']) && count($arResult['DELIVERIES']) == 1):?>
			<?$delivery = current($arResult['DELIVERIES']);?>
			<?if($arParams['ACTION'] == 'CREATE_DELIVERY_REQUEST'):?>
				<?="BX.Sale.Delivery.Request.Component.processRequest({action: 'createDeliveryRequest', deliveryId: ".$delivery['DELIVERY_ID'].", shipmentIds: ".CUtil::PhpToJSObject($delivery['SHIPMENT_IDS']).", weight: ".$delivery['WEIGHT']."});"?>
			<?elseif($arParams['ACTION'] == 'ADD_SHIPMENTS_TO_REQUEST'):?>
				<?="BX.Sale.Delivery.Request.Component.processRequest({action: 'addShipmentsToRequest', deliveryId: ".$delivery['DELIVERY_ID'].", shipmentIds: ".CUtil::PhpToJSObject($delivery['SHIPMENT_IDS']).", weight: ".$delivery['WEIGHT']."});"?>
			<?endif;?>
		<?endif;?>
	});
</script>