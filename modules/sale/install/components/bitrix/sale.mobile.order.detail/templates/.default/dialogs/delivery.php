<?
/*
 * Order delivery dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<form id="delivery_form">
	<div class="order_acceptpay_component">
		<div class="order_acceptpay_title"><?=GetMessage('SMOD_ALLOW_DELIVERY');?></div>
			<?

			$APPLICATION->IncludeComponent(
				'bitrix:mobileapp.interface.radiobuttons',
				'.default',
				$arResult["DAILOG"]["STATUSES"],
				false
			);

			?>
		<div class="order_acceptpay_infoblock">
			<div class="order_acceptpay_li_container tac">
				<div class="order_acceptpay_infoblock_title_tac"><?=GetMessage('SMOD_DELIVERY');?></div>
				<div class="order_acceptpay_button_shipping">
					<a id="order-delivery-allowed" href="javascript:void(0);"<?=$arResult['ORDER']['ALLOW_DELIVERY'] =="Y" ? ' class="current"' : ''?>><?=GetMessage('SMOD_ALLOW');?></a>
					<a id="order-delivery-not-allowed" href="javascript:void(0);"<?=$arResult['ORDER']['ALLOW_DELIVERY'] !="Y" ? ' class="current"' : ''?>><?=GetMessage('SMOD_DISALLOW');?></a>
					<div class="clb"></div>
				</div>
			</div>
		</div>
	</div>
</form>
<script>

	app.setPageTitle({title: "<?=GetMessage('SMOD_DELIVERY');?>"});

	app.addButtons({
		cancelButton:
		{
			type: "back_text",
			style: "custom",
			position: "left",
			name: "<?=GetMessage('SMOD_BACK');?>",
			callback: function()
			{
				app.closeModalDialog();
			}
		},
		saveButton:
		{
			type: "right_text",
			style: "custom",
			name: "<?=GetMessage('SMOD_SAVE');?>",

			callback: function()
			{
				onSaveButtonClick();
			}
		}
	});

	var initParams = {
		deliver: <?=$arResult['ORDER']['ALLOW_DELIVERY'] == 'Y' ? 1 : 0?>,
		statusId: '<?=$arResult['ORDER']['STATUS_ID']?>'
	};

	new FastButton(BX("order-delivery-allowed"), function(){ toggleDelivery(); }, false);
	new FastButton(BX("order-delivery-not-allowed"), function(){ toggleDelivery(); }, false);

	toggleDelivery = function()
	{
		BX.toggleClass(BX("order-delivery-allowed"),'current');
		BX.toggleClass(BX("order-delivery-not-allowed"),'current');
	}

	onSaveButtonClick = function()
	{
		var allowParams={};

		if(BX.hasClass(BX("order-delivery-allowed"), 'current'))
			allowParams["deliver"] = true;
		else
			allowParams["deliver"] = false;

		//get selected status
		var form = BX("delivery_form");

		for(var s=0, sl=form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"].length; s<sl; s++)
		{
			var el = form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"][s];

			if(el.checked)
			{
				allowParams["statusId"] = el.id;
				break;
			}
		}

		allowOrderDelivery(allowParams);
	}

	allowOrderDelivery = function(params)
	{
		var id = <?=$arResult['ORDER']['ID']?>;
		postData = {
			action: 'delivery_allow',
			id: id,
			sessid: BX.bitrix_sessid()
		};

		if(params.deliver != initParams.deliver)
			postData["deliver"] = params.deliver ? 'Y' : 'N';

		if(params.statusId != initParams.statusId)
			postData["status_id"] = params.statusId;

		if(!postData["deliver"] && !postData["status_id"])
		{
			app.closeModalDialog();
			return;
		}

		BX.ajax({
			timeout:   30,
			method:   'POST',
			dataType: 'json',
			url:       '<?=$arResult['AJAX_URL']?>',
			data:      postData,
			onsuccess: function(result) {
				app.closeModalDialog();
				if(result)
				{
					app.onCustomEvent("onAfterOrderChange", {"id" : id});
				}
			}
		});
	};
</script>