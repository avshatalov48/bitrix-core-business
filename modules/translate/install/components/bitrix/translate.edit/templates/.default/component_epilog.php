<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

\CUtil::InitJSCore(array('translate_process', 'loader'));

\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.icons', 'ui.buttons.icons', 'ui.alerts', 'ui.notification']);
