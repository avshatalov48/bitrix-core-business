<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) 
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<input type='hidden' name='paypal' value='Y'>
<input type='hidden' name='token' value="<?=htmlspecialcharsbx($params['TOKEN']);?>">
<input type='hidden' name='PayerID' value="<?=htmlspecialcharsbx($params['PAYER_ID']);?>">