<?
/*
 * Order delivery dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

require($_SERVER['DOCUMENT_ROOT'] . $templateFolder.'/dialogs/functions.php');

?>
<div class="wrap">
	<div class="order_acceptpay_component">
		<div class="order_acceptpay_title"><?=GetMessage('SMOD_ALLOW_DELIVERY');?></div>
			<? printStatusesListHtml($arResult["STATUSES"], $arResult["ORDER"]['STATUS_ID'], true); ?>
		<div class="order_acceptpay_infoblock">
			<div class="order_acceptpay_li_container tac">
				<div class="order_acceptpay_infoblock_title_tac"><?=GetMessage('SMOD_DELIVERY');?></div>
				<div class="order_acceptpay_button_shipping">
					<a id="order-delivery-allowed" onclick="toggleDelivery();" href="javascript:void(0);"<?=$arResult['ORDER']['ALLOW_DELIVERY'] =="Y" ? ' class="current"' : ''?>><?=GetMessage('SMOD_ALLOW');?></a>
					<a id="order-delivery-not-allowed" onclick="toggleDelivery();" href="javascript:void(0);"<?=$arResult['ORDER']['ALLOW_DELIVERY'] !="Y" ? ' class="current"' : ''?>><?=GetMessage('SMOD_DISALLOW');?></a>
					<div class="clb"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">

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

	allowParams["statusId"] = MAorderStatusControl.getSelectedStatus();

	allowOrderDelivery(allowParams);
}

allowOrderDelivery = function(params)
{
	var id = <?=$arResult['ORDER']['ID']?>;
	postData = {
		action: 'delivery_allow',
		id: id
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

	//app.showPopupLoader({"text":"allowing delivery"});

	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'json',
		url:       '<?=$arResult['AJAX_URL']?>',
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
				//alert("allowOrderDelivery !result"); //develop
			}
		},
		onfailure: function(){
			//alert("allowOrderDelivery onfailure"); //develop
		}
	});
};
</script>