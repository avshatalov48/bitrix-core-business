<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;

Main\UI\Extension::load(['bizproc.condition']);
Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/propertyvariablecondition/script.js'));
CAdminCalendar::ShowScript();

/** @var array $arCurrentValues */
/** @var $documentType */
/** @var $arVariables */
/** @var $arProperties */

$conditions = !empty($arCurrentValues) ? $arCurrentValues : ['variable_condition_count' => '1'];
?>

<script>
	BX.ready(() => {
		BX.Loc.setMessage(<?= Main\Web\Json::encode(Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

		const propertyVariableCondition = new BX.Bizproc.Activity.PropertyVariableCondition({
			table: document.getElementById('id_bwfiba_type_propertyvariablecondition'),
			conditions: <?= CUtil::PhpToJSObject($conditions) ?>,
			variables: <?= CUtil::PhpToJSObject($arVariables) ?>,
			properties: <?= CUtil::PhpToJSObject($arProperties) ?>,
			documentType: <?= CUtil::PhpToJSObject($documentType) ?>,
		});

		propertyVariableCondition.init();
	});
</script>