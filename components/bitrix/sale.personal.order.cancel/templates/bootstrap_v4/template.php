<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="row mb-3">
	<div class="col">
		<a href="<?=$arResult["URL_TO_LIST"]?>"><?=GetMessage("SALE_RECORDS_LIST")?></a>
	</div>
</div>
<div class="row">
	<div class="col">
		<div class="bx-order-cancel">
			<?if($arResult["ERROR_MESSAGE"] == ''):?>
				<form method="post" action="<?=POST_FORM_ACTION_URI?>">
					<input type="hidden" name="CANCEL" value="Y">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="ID" value="<?=$arResult["ID"]?>">

					<p class="mb-2">
						<?=GetMessage("SALE_CANCEL_ORDER1") ?>
						<a href="<?=$arResult["URL_TO_DETAIL"]?>"><?=GetMessage("SALE_CANCEL_ORDER2")?> #<?=$arResult["ACCOUNT_NUMBER"]?></a>?
					</p>

					<p class="mb-3">
						<strong class="text-danger"><?= GetMessage("SALE_CANCEL_ORDER3") ?></strong>
					</p>

					<div class="form-group">
						<label for="orderCancel"><?= GetMessage("SALE_CANCEL_ORDER4") ?></label>
						<textarea name="REASON_CANCELED" class="form-control" id="orderCancel" rows="3"></textarea>
					</div>

					<input type="submit" name="action" class="btn btn-danger" value="<?=GetMessage("SALE_CANCEL_ORDER_BTN") ?>">
					<a href="<?=$arResult["URL_TO_LIST"]?>" class="btn btn-link"><?=GetMessage("SALE_RECORDS_LIST")?></a>
				</form>
			<?else:?>
				<?=ShowError($arResult["ERROR_MESSAGE"]);?>
			<?endif;?>
		</div>
	</div>
</div>