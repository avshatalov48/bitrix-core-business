<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<a name="tb"></a>
<a href="<?=$arResult["URL_TO_LIST"]?>"><?=GetMessage("STPSC_2LIST")?></a>
<br /><br />
<?if(strlen($arResult["ERROR_MESSAGE"])<=0):?>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="ID" value="<?=$arResult["ID"]?>">
		<?=$arResult["CONFIRM"]?><br /><br />
		<?= GetMessage("STPSC_REASON") ?>:<br />
		<textarea name="REASON_CANCELED" cols="60" rows="3"></textarea><br /><br />
		<input type="hidden" name="CANCEL_SUBSCRIBE" value="Y">
		<input type="submit" value="<?echo GetMessage("STPSC_ACTION")?>">
	</form>
<?
else:
	echo ShowError($arResult["ERROR_MESSAGE"]);
endif;?>