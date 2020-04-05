<?
/*
 * Order paying dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/functions.php');

?>
<div class="wrap">
	<div class="order_acceptpay_component">
		<div class="order_acceptpay_title"><?=($arResult['ORDER']['PAYED'] == 'N' ? GetMessage('SMOD_PAY') : GetMessage('SMOD_PAY_CANCEL'))?></div>
		<?printStatusesListHtml($arResult["STATUSES"], $arResult["ORDER"]['STATUS_ID'], true);?>
		<?if($arResult['ORDER']['PAYED'] == 'N'):?>
			<div class="order_acceptpay_infoblock">
				<div class="order_acceptpay_infoblock_title"><?=GetMessage('SMOD_BUDGET')?></div>
				<ul>
					<li>
						<div class="order_acceptpay_li_container">
							<span class="order_acceptpay_infoblock_money"><?=$arResult["ORDER"]["CURRENT_BUDGET_STRING"]?>.</span>
						</div>
					</li>
				</ul>
			</div>
			<div class="order_acceptpay_infoblock">
				<div class="order_acceptpay_infoblock_title"><?=GetMessage('SMOD_ACCOUNT')?></div>
				<ul>
					<li>
						<div id="pay_from_account" class="order_acceptpay_li_container" onclick="BX.toggleClass(this,'checked');">
							<table>
								<tr>
									<td><span class="inputcheckbox"><input type="checkbox" id="r1"></span></td>
									<td><label for="r1"><span><?=GetMessage('SMOD_PAY_CONFIRM')?></span></label></td>
								</tr>
							</table>
						</div>
					</li>
				</ul>
			</div>
		<?else: //alredy peyed?>
			<div class="order_acceptpay_infoblock">
				<div class="order_acceptpay_infoblock_title"><?=GetMessage('SMOD_PAYMENT')?></div>
				<ul>
					<li>
						<div id="pay_from_account_back" class="order_acceptpay_li_container" onclick="BX.toggleClass(this,'checked');">
							<table>
								<tr>
									<td><span class="inputcheckbox"><input type="checkbox" id="r2"></span></td>
									<td><label for="r2"><span><?=GetMessage('SMOD_PAY_BACK')?></span></label></td>
								</tr>
							</table>
						</div>
					</li>
					<li>
						<div id="pay_cancel" class="order_acceptpay_li_container" onclick="BX.toggleClass(this,'checked');">
							<table>
								<tr>
									<td><span class="inputcheckbox"><input type="checkbox" id="r3"></span></td>
									<td><label for="r3"><?=GetMessage('SMOD_PAY_CANCEL')?></label></td>
								</tr>
							</table>
						</div>
					</li>
				</ul>
			</div>
		<?endif;?>
	</div>
</div>

<script type="text/javascript">

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
			onSaveButtonClick();
		}
	}
});

isPayFromAccount = function()
{
	return BX.hasClass(BX('sale_popup_pay'),'checked');
}

isPayFromAccountBack = function()
{
	return BX.hasClass(BX('pay_from_account_back'),'checked');
}

isPayCancel = function()
{
	return BX.hasClass(BX('pay_cancel'),'checked');
}


onSaveButtonClick = function()
{
	if('<?=$arResult['ORDER']['PAYED']?>' == 'Y' && !isPayCancel())
	{
		app.closeModalDialog();
		return;
	}

	payOrderSave();
}

payOrderSave = function()
{
	var id = <?=$arResult['ORDER']['ID']?>;
	postData = {
		action: 'order_pay',
		id: id,
		payed: isPayCancel() ? 'N' : 'Y',
		pay_from_account: isPayFromAccount() ? 'Y' : 'N',
		pay_from_account_back: isPayFromAccountBack() ? 'Y' : 'N',
		status_id: MAorderStatusControl.getSelectedStatus()
	};

	//app.showPopupLoader({"text":"saving pay"});

	BX.ajax({
		timeout:   30,
		method:   "POST",
		dataType: "json",
		url:       "<?=$arResult['AJAX_URL']?>",
		data:      postData,
		onsuccess: function(result) {
			//app.hidePopupLoader();
			app.closeModalDialog();
			if(result)
			{
				app.onCustomEvent("onAfterOrderChange", {"id" : id});
			}
			else
			{
				//alert("payOrderSave !result"); //develop
			}
		},
		onfailure: function(){
			//alert("payOrderSave failure"); //develop
		}
	});
};
</script>