<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

?>

<a href="<?=\CComponentEngine::makePathFromTemplate(
	$arParams['PATH_TO_MAIL_CONFIG'],
	array('act' => '')
) ?>">add</a>
