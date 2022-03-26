<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $USER, $APPLICATION;

$userId = $USER->getId();
$siteId = SITE_ID;
\CUserCounter::set($userId, 'mail_unseen', 0, $siteId);

?>

<div class="mail-error-text">
	<?=Loc::getMessage('MAIL_MODULE_NOT_INSTALLED')?>
</div>

<script type="text/javascript">
	(function ()
	{
		//delete the loader (the envelope is bouncing)
		var elements = top.document.getElementsByClassName('mail-loader-modifier');

		for (var element of elements)
		{
			element.classList.remove('mail-loader-modifier');
		}

	})();
</script>