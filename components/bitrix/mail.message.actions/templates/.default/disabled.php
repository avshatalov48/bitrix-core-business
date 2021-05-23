<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load('ui.buttons');

?>

<div class="ui-btn-split ui-btn-primary ui-btn-disabled">
	<a class="ui-btn-main"><?=\Bitrix\Main\Localization\Loc::getMessage('MAIL_MESSAGE_ACTIONS_TASK_BTN') ?></a>
	<a class="ui-btn-extra"></a>
</div>
