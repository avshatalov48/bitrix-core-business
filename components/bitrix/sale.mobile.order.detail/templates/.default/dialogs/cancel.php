<?
/*
 * Order cancel dialog
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<form id="cancel_form">
	<div class="order_canceled_component">
		<div class="order_canceled_title">
			<?=$arResult['ORDER']['CANCELED'] == 'N' ? GetMessage('SMOD_CANCEL') : GetMessage('SMOD_CANCEL_CANCEL');?>
		</div>
		<?if($arResult['ORDER']['CANCELED'] == 'N'):?>
			<div class="order_canceled_infoblock">
				<div class="order_canceled_infoblock_title"><?=GetMessage('SMOD_CANCEL_REASON');?></div>
				<div class="order_canceled_infoblock_textarea_container">
					<textarea class="order_canceled_infoblock_textarea" name="" id="cancel_comment"></textarea>
				</div>
				<span class="order_canceled_infoblock_desc"><?=GetMessage('SMOD_USER_ACCESSIBLE');?></span>
			</div>
		<?endif;?>
	</div>
</form>

<script type="text/javascript">

app.setPageTitle({title: "<?=$arResult['ORDER']['CANCELED'] == 'N' ? GetMessage('SMOD_CANCEL') : GetMessage('SMOD_CANCEL_CANCEL');?>"});

var orderCanceled = '<?=$arResult['ORDER']['CANCELED']?>';

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
			onCancelButtonClick();
		}
	}
});

onCancelButtonClick = function ()
{
	var cancelComment = BX("cancel_comment");
	var cancelCommentText = "";
	var cancel = orderCanceled == 'N' ?  true : false;

	if(cancelComment)
		cancelCommentText = cancelComment.value;

	cancelOrder(cancel, cancelCommentText);
}

cancelOrder = function(cancel, comment)
{
	var id = <?=$arResult['ORDER']['ID']?>;
	postData = {
		action: 'order_cancel',
		id: id,
		cancel: cancel ? 'Y' : 'N',
		comment: comment,
		sessid: BX.bitrix_sessid()
	};

	//app.showPopupLoader({"text":"canceling"});

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
				app.onCustomEvent("onAfterOrderCancel", {"id" : id, "cancel": postData["cancel"]});
			}
			else
			{
				//alert("cancelOrder !result"); //develop
			}
		},
		onfailure: function(){
			//alert("cancelOrder failure"); //develop

		}
	});
};
</script>
