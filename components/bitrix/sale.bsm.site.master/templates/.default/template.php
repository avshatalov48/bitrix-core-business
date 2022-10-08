<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @noinspection PhpUndefinedClassInspection */
/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleBsmSiteMaster $component
 * @var string $templateFolder
 */

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

Loc::loadMessages(__FILE__);
$messages = Loc::loadLanguageFile(__FILE__);

echo $arResult["CONTENT"];

?>
	<script>
		BX.message(<?=CUtil::PhpToJSObject($messages)?>);
		BX.Sale.BsmSiteMasterComponent.init({
			wizardSteps: <?=CUtil::PhpToJSObject($arResult["WIZARD_STEPS"])?>,
			formId: '<?=$component->getWizard()->GetFormName()?>',
			documentRoot: '<?=htmlspecialcharsbx(CUtil::addslashes($_SERVER["DOCUMENT_ROOT"]))?>',
			siteNameId: "BSM_SITE",
			docRootId: "DOC_ROOT",
			docRootLinkId: "DOC_ROOT_LINK",
			createSiteId: "create_site",
			keyInputBlockId: "check_key",
			keyId: "id_key",
			keyButtonId: "id_key_btn",
			confirmationCheckboxId: "confirmation_done",
			currentStepId: '<?=htmlspecialcharsbx(CUtil::addslashes($component->getWizard()->GetCurrentStepID()))?>',
			nextButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetNextButtonID())?>',
			prevButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetPrevButtonID())?>',
			cancelButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetCancelButtonID())?>',
			finishButtonId: '<?=htmlspecialcharsbx($component->getWizard()->GetFinishButtonID())?>'
		});
	</script>
<?