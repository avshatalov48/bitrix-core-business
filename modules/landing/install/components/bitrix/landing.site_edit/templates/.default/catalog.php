<?php
namespace Bitrix\Landing\Components\LandingEdit;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \CMain $APPLICATION */
/** @var \LandingSiteEditComponent $component */

use Bitrix\Landing\Hook\Page;
use Bitrix\Landing\Manager;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__DIR__ . '/template.php');
Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
	'landing_master',
]);

if ($arResult['ERRORS'])
{
	?><div class="landing-message-label error"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?></div><?
}
if ($arResult['FATAL'])
{
	return;
}

// vars
$domains = $arResult['DOMAINS'];
$row = $arResult['SITE'];
$hooks = $arResult['HOOKS'];
$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();
$landingKeys = array_keys($arResult['LANDINGS']);
$isAjax = $component->isAjax();

// title
Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE_EDIT_CATALOG')
);

// assets
Extension::load([
	'ui.buttons',
	'ui.layout-form',
	'ui.forms',
]);
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass.' ' : '') . 'landing-slider-frame-popup'
);
// view-functions
include 'template_class.php';
$template = new Template($arResult);

// some url
$uriSave = new \Bitrix\Main\Web\Uri(htmlspecialcharsback(POST_FORM_ACTION_URI));
$uriSave->addParams(array(
	'action' => 'save'
));

// domain
if (Manager::isB24())
{
	$domainName = $domains[$row['DOMAIN_ID']['CURRENT']]['DOMAIN'] ?? $row['DOMAIN_ID']['CURRENT'];
}
else
{
	$domainName = $row['DOMAIN_ID']['CURRENT'];
}
?>

<div class="landing-form-wrapper">
	<form
		action="<?= \htmlspecialcharsbx($uriSave->getUri());?>"
		method="post"
		class="ui-form ui-form-section landing-form landing-form-gray-padding landing-form-collapsed"
		id="landing-site-catalog-set-form"
	>
		<?= bitrix_sessid_post();?>
		<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
		<input type="hidden" name="fields[TYPE]" value="<?= $row['TYPE']['CURRENT'];?>" />
		<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'];?>" />
		<input type="hidden" name="fields[TPL_ID]" value="<?= $row['TPL_ID']['CURRENT'];?>" />
		<input type="hidden" name="fields[LANDING_ID_404]" value="<?= $row['LANDING_ID_404']['CURRENT'];?>" />
		<input type="hidden" name="fields[LANDING_ID_INDEX]" value="<?= $row['LANDING_ID_INDEX']['CURRENT'];?>" />
		<input type="hidden" name="fields[DOMAIN_ID]" value="<?= $domainName;?>" />
		<?if (count($arResult['LANDINGS']) === 1):?>
			<input name="fields[LANDING_ID_INDEX]" type="hidden" value="<?= array_pop($landingKeys);?>" />
		<?endif;?>
		<input type="hidden" name="fields[TITLE]" value="<?= $row['TITLE']['CURRENT'];?>" />

		<?if (isset($hooks['SETTINGS'])):
			$pageFields = $hooks['SETTINGS']->getPageFields();
			foreach (Page\Settings::getCodes() as $header => $codes)
			{
				if ($header)
				{
					?>
					<div class="ui-form-row landing-form-title-catalog">
						<?= Loc::getMessage('LANDING_TPL_HOOK_SETT_HEADER_'.mb_strtoupper($header)) ?>
					</div>
					<?php
				}
				foreach ($codes as $code)
				{
					$fieldCode = 'SETTINGS_' . $code;
					if (isset($pageFields[$fieldCode]))
					{
						$field = $pageFields[$fieldCode];
						$label = Loc::getMessage('LANDING_TPL_HOOK_SETT_' . $code);
						?>
						<div class="ui-form-row ui-form-row-middle" id="row_<?= $template->getFieldId($code) ?>">
							<div class="ui-form-label">
								<div class="ui-ctl-label-text">
									<?= $label ?: $field->getLabel() ?>
								</div>
							</div>
							<div class="ui-form-content">
								<?php if ($field->getCode() === 'SETTINGS_SECTION_ID'):?>
									<div id="fieldSectionId"></div>
									<input
										type="hidden"
										id="fieldSectionIdReal"
										name="fields[ADDITIONAL_FIELDS][SETTINGS_SECTION_ID]"
										value="<?= (int)$field->getValue() ?>"
									>
									<script>
										const fieldSection = new BX.Landing.UI.Field.LinkUrl({
											title: "",
											textOnly: true,
											disableCustomURL: true,
											disallowType: true,
											allowedTypes: [
												BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG
											],
											allowedCatalogEntityTypes: [
												BX.Landing.UI.Panel.Catalog.TYPE_CATALOG_SECTION
											],
											typeData: {
												button : {
													'className': 'fa fa-chevron-right',
													'text': '',
													'action': BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG_SECTION,
												},
												hideInput : false,
												contentEditable : false,
											},
											settingMode: true,
											content: "<?= $field->getValue() ? '#catalogSection' . (int)$field->getValue() : '' ?>",
											onValueChange: function()
											{
												BX("fieldSectionIdReal").value = fieldSection.getValue().substr(15);
											}
										});
										BX("fieldSectionId").appendChild(fieldSection.layout);
									</script>
								<?php else:?>
									<?php $template->showField($field, [
										'title' => ($field->getType() === 'checkbox')
									]);?>
								<?php endif;?>
							</div>
						</div>
						<?php
					}
				}
			}
		endif;?>

		<?php if (isset($hooks['SETTINGS'], $pageFields['SETTINGS_AGREEMENT_ID'])):
			$agreementId = $pageFields['SETTINGS_AGREEMENT_ID']->getValue() ?: 0;
			$agreementUseField = $pageFields['SETTINGS_AGREEMENT_USE'];
			if(!$agreementUseField->getValue())
			{
				$agreementUseField->setValue($agreementId ? 'Y' : 'N');
			}
			?>
			<div class="ui-form-row landing-form-title-catalog">
				<?= Loc::getMessage('LANDING_TPL_HOOK_SETT_HEADER_USERCONSENT') ?>
			</div>
			<div class="ui-form-row" id="row_userconsent">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">
						<?= Loc::getMessage('LANDING_TPL_HOOK_SETT_HEADER_USERCONSENT_LABEL') ?>
					</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-form-row">
						<div class="ui-form-label" data-form-row-hidden>
							<?php $template->showField($agreementUseField, [
								'title' => Loc::getMessage('LANDING_TPL_HOOK_SETT_HEADER_USERCONSENT_USE')
							]);?>
						</div>
						<div class="ui-form-row-hidden">
							<div class="ui-form-row">
								<?$APPLICATION->IncludeComponent(
									'bitrix:landing.userconsent.selector',
									'',
									[
										'ID' => $agreementId,
										'INPUT_NAME' => 'fields[ADDITIONAL_FIELDS][SETTINGS_AGREEMENT_ID]'
									]
								);?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif;?>

		<!--BUTTONS-->
		<?php
		// for complex component landing.settings not need buttons. If isAjax will be incorrect - need add other flag for landgin.settings
		if (!$isAjax)
		{
			$buttonSave = [
				'TYPE' => 'save',
				'ID' => 'landing-save-btn',
				'NAME' => 'submit',
				'CAPTION' => Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD')),
				'VALUE' => Loc::getMessage('LANDING_TPL_BUTTON_SAVE'),
			];
			$buttonCancel = [
				'TYPE' => 'cancel',
				'CAPTION' => Loc::getMessage('LANDING_TPL_BUTTON_CANCEL'),
				'LINK' => $arParams['PAGE_URL_SITES'],
			];
			if ($request->get('IFRAME') === 'Y')
			{
				$buttonCancel['ID'] = $template->getFieldId('ACTION_CLOSE');
				$buttonCancel['LINK'] = '#';
			}
			$APPLICATION->IncludeComponent(
				'bitrix:ui.button.panel',
				'',
				['BUTTONS' => [$buttonSave, $buttonCancel]]
			);
		}
		?>

	</form>
</div>

<script>
	BX.ready(function(){
		new BX.UI.LayoutForm({container: BX('landing-site-catalog-set-form')});
		const editComponent = new BX.Landing.EditComponent('<?= $template->getFieldId('ACTION_CLOSE') ?>');
		<?php if ($arParams['SUCCESS_SAVE']):?>
			top.window['landingSettingsSaved'] = true;
			top.BX.onCustomEvent('BX.Landing.Filter:apply');
			editComponent.actionClose();
		<?php else: ?>
			top.window['landingSettingsSaved'] = false;
		<?php endif;?>
		BX.Landing.Env.createInstance({
			site_id: '<?= $row['ID']['CURRENT'] ?>',
			params: {
				type: '<?= $arParams['TYPE'] ?>',
			},
		});
	});
</script>