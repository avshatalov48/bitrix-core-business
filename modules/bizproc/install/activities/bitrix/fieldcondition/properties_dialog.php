<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['bizproc.condition']);
\Bitrix\Main\Page\Asset::getInstance()->addJs(getLocalPath('activities/bitrix/fieldcondition/script.js'));
CAdminCalendar::ShowScript();

/** @var array $arCurrentValues */
/** @var array $arDocumentFields */
/** @var CBPDocumentService $documentService */
/** @var $documentType */

$conditions = !empty($arCurrentValues) ? $arCurrentValues : ['field_condition_count' => '1'];
?>

<script>
	BX.ready(() => {
		BX.Loc.setMessage(<?= \Bitrix\Main\Web\Json::encode(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)) ?>);

		const fieldCondition = new BX.Bizproc.Activity.FieldCondition({
			table: document.getElementById('id_bwfiba_type_fieldcondition'),
			conditions: <?= CUtil::PhpToJSObject($conditions) ?>,
			documentFields: <?= CUtil::PhpToJSObject($arDocumentFields) ?>,
			documentType: <?= CUtil::PhpToJSObject($documentType) ?>,
			useOperatorModified: '<?= $documentService->isFeatureEnabled($documentType, CBPDocumentService::FEATURE_MARK_MODIFIED_FIELDS) ? 'Y' : 'N'?>'
		});

		fieldCondition.init();
	})
</script>