<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Main\Localization\Loc;
?>
<div>
	<form name="form-add-mails-to-blacklist">
		<div class="ui-control-container ui-control-textarea mail-blacklist-popup-textarea-wrapper">
			<textarea class="ui-control mail-blacklist-popup-textarea"
				rows="30"
				name="emails"
				style="min-height: 250px;"
				data-role="blacklist-mails-textarea"></textarea>
		</div>
		<? if ($isForAllUsers): ?>
			<div class="" data-role="is-for-all-users-block">
				<input type="checkbox" class="" name="isForAllUsers" id="isForAllUsers" value="Y">
				<label class="" for="isForAllUsers" title=""><?= Loc::getMessage('MAIL_BLACKLIST_LIST_POPUP_CHECKBOX_TITLE'); ?></label>
			</div>
		<? endif; ?>
	</form>
</div>