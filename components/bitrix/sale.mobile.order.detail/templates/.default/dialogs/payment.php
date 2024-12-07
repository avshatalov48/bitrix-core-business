<?
/*
 * Order paying dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<form id="payment_form">
	<div class="order_acceptpay_component">
		<div class="order_acceptpay_title">
			<?=($arResult['ORDER']['PAYED'] == 'N' ? GetMessage('SMOD_PAY') : GetMessage('SMOD_PAY_CANCEL'))?>
		</div>
		<?

		$APPLICATION->IncludeComponent(
			'bitrix:mobileapp.interface.radiobuttons',
			'.default',
			$arResult["DAILOG"]["STATUSES"],
			false
		);

		?>
		<?if($arResult['ORDER']['PAYED'] == 'N'):?>
			<div class="order_acceptpay_infoblock">
				<div class="order_acceptpay_infoblock_title"><?=GetMessage('SMOD_BUDGET')?></div>
				<ul>
					<li>
						<div class="order_acceptpay_li_container">
							<span class="order_acceptpay_infoblock_money"><?=$arResult["ORDER"]["CURRENT_BUDGET_STRING"]?></span>
						</div>
					</li>
				</ul>
			</div>
		<?endif;?>
	</div>
	<?

	if(floatval($arResult["ORDER"]["CURRENT_BUDGET"]) >= floatval($arResult["ORDER"]["PRICE"])
		|| $arResult['ORDER']['PAYED'] == 'Y')
	{
		$APPLICATION->IncludeComponent(
			'bitrix:mobileapp.interface.checkboxes',
			'.default',
			$arResult["DAILOG"]["PAY_CB"],
			false
		);
	}
	elseif($arResult['ORDER']['PAYED'] != 'Y')
	{
		?>
		<div class="order_acceptpay_infoblock">
			<div class="order_acceptpay_infoblock_title"><?=$arResult["DAILOG"]["PAY_CB"]["TITLE"]?></div>
			<ul>
				<li>
					<div class="order_acceptpay_li_container">
						<label><?=GetMessage('SMOD_INSUFF_BUDGET')?></label>
					</div>
				</li>
			</ul>
		</div>
		<?
	}
	?>
</form>
<script>

app.setPageTitle({title: "<?=GetMessage('SMOD_PAYMENT')?>"});

app.addButtons({
	cancelButton:
	{
		type: "back_text",
		style: "custom",
		position: 'left',
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
			payOrderSave();
		}
	}
});

payOrderSave = function()
{
	var form = BX("payment_form"),
		isPayCancel = false,
		isPayFromAccount = false,
		isPayBackToAccont = false,
		statusId = false;


	for(var i=0, l=form.elements.length; i<l; i++)
	{
		switch(form.elements[i]["id"])
		{
			case "pay_cancel": isPayCancel = form.elements[i].checked; break;
			case "pay_from_account": isPayFromAccount = form.elements[i].checked; break;
			case "pay_from_account_back": isPayBackToAccont = form.elements[i].checked; break;
		}
	}

	//if order payed and "cancel payment" not selected
	if('<?=$arResult['ORDER']['PAYED']?>' == 'Y' && !isPayCancel)
	{
		app.closeModalDialog();
		return;
	}

	//get selected status
	for(var s=0, sl=form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"].length; s<sl; s++)
	{
		var el = form.elements["<?=$arResult["DAILOG"]["STATUSES"]["RADIO_NAME"]?>"][s];

		if(el.checked)
		{
			statusId = el.id;
			break;
		}
	}

	var id = <?=$arResult['ORDER']['ID']?>;
	postData = {
		action: 'order_pay',
		id: id,
		payed: isPayCancel ? 'N' : 'Y',
		status_id: statusId,
		sessid: BX.bitrix_sessid()
	};

	if('<?=$arResult['ORDER']['PAYED']?>' == 'Y')
		postData["pay_from_account_back"] = isPayBackToAccont ? 'Y' : 'N';
	else
		postData["pay_from_account"] = isPayFromAccount ? 'Y' : 'N';

	BX.ajax({
		timeout:   30,
		method:   "POST",
		dataType: "json",
		url:       "<?=$arResult['AJAX_URL']?>",
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