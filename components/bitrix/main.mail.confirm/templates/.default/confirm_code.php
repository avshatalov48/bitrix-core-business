<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<span style="font-size:16px;line-height:20px;">
<?=getMessage('MAIN_MAIL_CONFIRM_MESSAGE_HINT') ?><br>

<span style="font-size:24px;line-height:70px;"><b><?=htmlspecialcharsbx($arParams['CONFIRM_CODE']) ?></b></span><br>
<span style="color:#808080;">
<?=getMessage('MAIN_MAIL_CONFIRM_MESSAGE_FAQ_Q1') ?><br><br>
<span style="font-size:14px;"><?=getMessage('MAIN_MAIL_CONFIRM_MESSAGE_FAQ_A1') ?></span>
</span>
