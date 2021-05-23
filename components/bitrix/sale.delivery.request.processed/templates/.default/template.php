<?
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="admin-delivery-request-box admin-delivery-request-accept">
	<div class="admin-delivery-request-inner">
		<div class="admin-delivery-request-block-left">
			<div class="admin-delivery-request-icon">
				<img class="admin-delivery-request-icon-item" src="<?=$arResult['DELIVERY']['LOGO_SRC']?>">
			</div>
			<div class="admin-delivery-request-name">
				<div class="admin-delivery-request-name-item"><?=$arResult['DELIVERY']['NAME']?></div>
				<div class="admin-delivery-request-cork">
					<a href="<?=$arResult['DELIVERY']['EDIT_LINK']?>" class="admin-delivery-request-link"><?=$arResult['DELIVERY']['ID']?></a>
				</div>
			</div>
		</div>
		<div class="admin-delivery-request-block-right">
			<div class="admin-delivery-request-value">
				<div class="admin-delivery-request-value-name"><?=Loc::getMessage('SALE_CSDRPT_SHIPMENTS_AMOUNT')?>:</div>
				<div class="admin-delivery-request-value-number"><?=$arParams['SHIPMENTS_COUNT']?> <?=Loc::getMessage('SALE_CSDRPT_PIECE')?>.</div>
			</div>
			<div class="admin-delivery-request-value">
				<div class="admin-delivery-request-value-name"><?=Loc::getMessage('SALE_CSDRPT_WEIGHT')?>:</div>
				<div class="admin-delivery-request-value-number"><?=$arParams['WEIGHT']?> <?=Loc::getMessage('SALE_CSDRPT_KILO')?></div>
			</div>
		</div>
	</div>

	<?if($arParams['DELIVERY_REQUESTS'] > 0):?>
		<?foreach($arResult['DELIVERY_REQUESTS'] as $id => $params):?>
			<div class="admin-delivery-request-confirm green">
				<span class="admin-delivery-request-confirm-text">
					<?if($arParams['ACTION'] == 'CREATE'):?>
						<?=Loc::getMessage('SALE_CSDRPT_DELIVERY_REQUEST_CREATED', array('#REQUEST_ID#' => '<a href="/bitrix/admin/sale_delivery_request_view.php?ID='.$id.'&lang='.LANGUAGE_ID.'" title="'.Loc::getMessage('SALE_CSDRPT_DELIVERY_REQUEST_TITLE', array('#REQUEST_ID#' => $id)).'">'.$id.'</a>'))?>
					 <?else:?>
						<?=Loc::getMessage('SALE_CSDRPT_DELIVERY_REQUEST_UPD', array('#REQUEST_ID#' => '<a href="/bitrix/admin/sale_delivery_request_view.php?ID='.$id.'&lang='.LANGUAGE_ID.'" title="'.Loc::getMessage('SALE_CSDRPT_DELIVERY_REQUEST_TITLE', array('#REQUEST_ID#' => $id)).'">'.$id.'</a>'))?>
					 <?endif;?>
				</span>

				<span class="admin-delivery-request-confirm-text">. <?=Loc::getMessage('SALE_CSDRPT_SHIPMENTS_SUCCESS_AMOUNT')?>: <?=$params['SHIPMENTS_COUNT']?> .</span>
			</div>
		<?endforeach;?>
	<?endif;?>

	<?if($arParams['SHIPMENTS_ERRORS'] > 0):?>
		<div class="admin-delivery-request-confirm red">
			<span class="admin-delivery-request-confirm-text"><?=Loc::getMessage('SALE_CSDRPT_SHIPMENTS_FAIL_AMOUNT')?>: <a href="/bitrix/admin/sale_order_shipment.php?lang=<?=LANGUAGE_ID?>&filter_delivery_id=<?=$arResult['DELIVERY']['ID']?>&filter_is_delivery_request_failed=Y&set_filter=Y" title="<?=Loc::getMessage('SALE_CSDRPT_SHIPMENTS_FAIL_AMOUNT_TITLE')?>"><?=$arParams['SHIPMENTS_ERRORS']?></a></span>
		</div>
	<?endif;?>

</div>