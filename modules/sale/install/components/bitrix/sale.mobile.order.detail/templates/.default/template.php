<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

switch ($arResult["ACTION"])
{
	case "get_status_dialog":
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/status.php');
		return;
	break;

	case "get_cancel_dialog":
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/cancel.php');
		return;
	break;

	case "get_delivery_dialog":
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/delivery.php');
		return;
	break;

	case "get_payment_dialog":
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/payment.php');
		return;
	break;

	case "get_deduct_dialog":
		require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/deduction.php');
		return;
	break;

}

?>
	<div class="order_component" id="order_detail_<?=$arResult['ORDER']['ID']?>">
		<div class="order_title">
			<?=GetMessage("SMOB_ORDER_N").$arResult["ORDER"]['ACCOUNT_NUMBER']?>
		<div class="order-title-date">
				<?=GetMessage("SMOB_FROM").' '.CSaleMobileOrderUtils::getDateTime($arResult["ORDER"]['DATE_INSERT'])?>
			</div>
		</div>

		<?
		if($arResult["SHOW_UPPER_BUTTONS"] === true)
		{
			$arTSParams = array(
				"ITEMS" => array(
					"detail" =>GetMessage("SMOB_ORDER"),
					"history" =>GetMessage("SMOB_HISTORY"),
					"transact" =>GetMessage("SMOB_TRANSACT")
					),
				"SELECTED" => "detail",
				"JS_CALLBACK_FUNC" => "onTopSwitcherClick",
			);

			$APPLICATION->IncludeComponent(
				'bitrix:mobileapp.interface.topswitchers',
				'.default',
				$arTSParams,
				false
			);
			?>

			<script>
				function onTopSwitcherClick(params)
				{
					switch(params.selectedId)
					{
						case "detail": orderDetail.updateOrder({id: '<?=$arResult["ORDER"]['ID']?>'}); break;
						case "history": orderDetail.getHistory('<?=$arResult["ORDER"]['ID']?>'); break;
						case "transact": orderDetail.getTransact('<?=$arResult["ORDER"]['ID']?>'); break;
					}
				}
			</script>

			<?
		}
		?>
	<div id="detail_info_body_<?=$arResult['ORDER']['ID']?>">
		<?=CSaleMobileOrderUtils::makeDetailClassFromOrder($arResult["ORDER"]);?>
	</div>
</div>
<script>

	app.setPageTitle({title: "<?=(GetMessage('SMOD_ORDER_N').$arResult['ORDER']['ACCOUNT_NUMBER'])?>"});

	var orderDetail = new __MASaleOrderDetail({id: "<?=$arResult['ORDER']['ID']?>",
					dialogUrl: "<?=$arResult['CURRENT_PAGE']?>",
					ajaxUrl: "<?=$arResult['AJAX_URL']?>",
					showUpperButtons: <?=($arResult['SHOW_UPPER_BUTTONS'] ? "true" : "false")?>
				});

	orderDetail.messages = {
		cancel: "<?=GetMessage('SMOD_CANCEL')?>",
		cancelCancel: "<?=GetMessage('SMOD_CANCEL_CANCEL')?>"
	};

	<?if(!empty($arResult['MENU_ITEMS'])):?>
		orderDetail.detailMenuItems = {items: []};

		<?if(in_array("STATUS_CHANGE", $arResult['MENU_ITEMS'])):?>
			orderDetail.detailMenuItems.items.push({
				name: "<?=GetMessage('SMOD_CHANGE_STATUS');?>",
				action: function() {orderDetail.dialogShow("status"); },
				icon: 'edit'
			});
		<?endif;?>

		<?if(in_array("DELIVERY", $arResult['MENU_ITEMS'])):?>
			orderDetail.detailMenuItems.items.push({
				name: "<?=GetMessage('SMOD_ALLOW_DELIVERY');?>",
				action: function() {orderDetail.dialogShow("delivery"); },
				icon: 'edit'
			});
		<?endif;?>

		<?if(in_array("PAYMENT", $arResult['MENU_ITEMS'])):?>
			orderDetail.detailMenuItems.items.push({
				name: "<?=GetMessage('SMOD_PAY_FOR_ORDER');?>",
				action: function() {orderDetail.dialogShow("payment"); },
				icon: 'edit'
			});
		<?endif;?>

		<?if(in_array("DEDUCTION", $arResult['MENU_ITEMS'])):?>
			orderDetail.detailMenuItems.items.push({
				name: "<?=($arResult["ORDER"]["DEDUCTED"] == 'N' ? GetMessage('SMOD_DEDUCT') : GetMessage('SMOD_DEDUCT_UNDO'))?>",
				action: function() {
					app.loadPageBlank({
						url: "<?=$arResult['CURRENT_PAGE']?>?action=get_deduct_dialog&id=<?=$arResult['ORDER']['ID']?>"
					});
				},
				icon: 'edit'
			});
		<?endif;?>

		<?if(in_array("ORDER_CANCEL", $arResult['MENU_ITEMS'])):?>
			orderDetail.detailMenuItems.items.push({
				name: "<?=($arResult['ORDER']['CANCELED'] == 'N' ? GetMessage('SMOD_CANCEL') : GetMessage('SMOD_CANCEL_CANCEL'))?>",
				action: function() { orderDetail.dialogShow("cancel"); },
				icon: 'cancel'
			});

			BX.addCustomEvent('onAfterOrderCancel', function (params){
														if(params.id == <?=$arResult["ORDER"]["ID"]?>)
															orderDetail.onItemCancelChange(params);
													});

		<?endif;?>

		orderDetail.menuShow();

	<?endif;?>

	BX.addCustomEvent('onAfterOrderChange', function (params){
												if(params.id == <?=$arResult["ORDER"]["ID"]?>)
													orderDetail.updateOrder(params);
											});

</script>