<?php
namespace Bitrix\Landing\Components\LandingEdit;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var CMain $APPLICATION */
/** @var LandingEditComponent $component */

use Bitrix\Landing\Domain;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Restriction;
use Bitrix\Landing\Site;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\ModuleManager;
use CJSCore;
use CMain;
use CUtil;
use LandingEditComponent;
use function htmlspecialcharsback;
use function htmlspecialcharsbx;

Loc::loadMessages(__FILE__);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

if ($arResult['ERRORS'])
{
	$errorMessages = [];
	foreach ($arResult['ERRORS'] as $errorCode => $errorMessage)
	{
		$errorMessages[] = $component->getSettingLinkByError($errorCode) ?: $errorMessage;
	}

	if (!empty($errorMessages))
	{
		?>
		<div class="landing-error-page">
			<div class="landing-error-page-inner">
				<div class="landing-error-page-title"><?= implode('<br>', $errorMessages)?></div>
				<div class="landing-error-page-img">
					<div class="landing-error-page-img-inner"></div>
				</div>
			</div>
		</div>
		<?php
	}
}

if ($arResult['FATAL'])
{
	return;
}

// vars
$isIndex = false;
$isFolderIndex = false;
$domainId = 0;
$domainName = Domain::getHostUrl();
$domainProtocol = '';
$row = $arResult['LANDING'];
$meta = $arResult['META'];
$hooks = $arResult['HOOKS'];
$domains = $arResult['DOMAINS'];
$sites = $arResult['SITES'];
$isIntranet = $arResult['IS_INTRANET'];
$isFormEditor = $arResult['SPECIAL_TYPE'] == Site\Type::PSEUDO_SCOPE_CODE_FORMS;
$isMainpageEditor = $arParams['TYPE'] == Site\Type::SCOPE_CODE_MAINPAGE;
$siteCurrent = $sites[$row['SITE_ID']['CURRENT']] ?? null;
$isSMN = $siteCurrent['TYPE'] === 'SMN';
$isAjax = $component->isAjax();
$availableOnlyForZoneRu = Manager::availableOnlyForZone('ru');
$isArea = $arResult['IS_AREA'];

// check if this page is folder's index
if (
	$row['FOLDER_ID']['CURRENT'] &&
	(
		$row['CODE']['CURRENT'] === ($arResult['LAST_FOLDER']['CODE'] ?? null)
		|| $row['ID']['CURRENT'] === ($arResult['LAST_FOLDER']['INDEX_ID'] ?? null)
	)
)
{
	$isFolderIndex = true;
}

// correct some vars
if (!$row['SITE_ID']['CURRENT'])
{
	$row['SITE_ID']['CURRENT'] = $arParams['SITE_ID'];
}
if ($siteCurrent)
{
	$domainId = $siteCurrent['DOMAIN_ID'];
	$isIndex = $row['ID']['CURRENT'] === $siteCurrent['LANDING_ID_INDEX'];
}
if (isset($domains[$domainId]))
{
	$domainName = $domains[$domainId]['DOMAIN'];
	$domainProtocol = $domains[$domainId]['PROTOCOL'];
}

// title
if ($arParams['LANDING_ID'])
{
	Manager::setPageTitle(
		Loc::getMessage('LANDING_TPL_TITLE_EDIT')
	);
}
else
{
	Manager::setPageTitle(
		Loc::getMessage('LANDING_TPL_TITLE_ADD')
	);
}

// assets

Extension::load(['ui.buttons', 'ui.layout-form', 'ui.icon-set.actions', 'ai-copilot']);
CJSCore::init(['color_picker', 'landing_master']);
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css');
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/style.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/script.js');

$this->getComponent()->initAPIKeys();

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass.' ' : '') . 'landing-slider-frame-popup'
);

// view-functions
include Manager::getDocRoot() . '/bitrix/components/bitrix/landing.site_edit/templates/.default/template_class.php';
$template = new Template($arResult);

// some url
$uriSave = new Uri(htmlspecialcharsback(POST_FORM_ACTION_URI));
$uriSave->addParams(array(
	'action' => 'save'
));

// for special sites - special abilities
if ($isFormEditor)
{
	$formHooks = [
		'METAOG',
		'METAMAIN',
		'YACOUNTER',
		'GACOUNTER',
		'GTM',
		'B24BUTTON',
	];

	foreach($hooks as $code => $hook)
	{
		if (!in_array($code, $formHooks))
		{
			unset($hooks[$code]);
		}
	}

	$arResult['TEMPLATES'] = [];
}
elseif ($isMainpageEditor)
{
	$formHooks = [
		'METAOG',
	];

	foreach($hooks as $code => $hook)
	{
		if (!in_array($code, $formHooks))
		{
			unset($hooks[$code]);
		}
	}
}
?>

<script>
	BX.ready(function()
	{
		<?php if ($arParams['SUCCESS_SAVE']): ?>
			top.window['landingSettingsSaved'] = true;
			top.BX.onCustomEvent('BX.Landing.Filter:apply');
			const editComponent = new BX.Landing.EditComponent('<?= $template->getFieldId('ACTION_CLOSE') ?>');
			editComponent.actionClose();
			top.BX.Landing.UI.Tool.ActionDialog.getInstance().close();
		<?php else: ?>
			top.window['landingSettingsSaved'] = false;
		<?php endif;?>
		BX.Landing.Env.createInstance({
 			site_id: '<?= $row['SITE_ID']['CURRENT'] ?>',
			params: {
				type: '<?= $arParams['TYPE'] ?>',
			},
		});
	});
</script>

<?php
if ($arParams['SUCCESS_SAVE'])
{
	if ($request->get('IFRAME') != 'Y')
	{
		$this->getComponent()->refresh([], ['action']);
	}
	return;
}
?>

<div class="landing-form-wrapper">
	<form
		action="<?=htmlspecialcharsbx($uriSave->getUri())?>"
		method="post"
		class="landing-form landing-form-gray-padding landing-form-collapsed landing-page-set-form"
		id="landing-page-set-form"
	>
		<?= bitrix_sessid_post()?>
		<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
		<input type="hidden" name="fields[SITE_ID]" value="<?= htmlspecialcharsbx($row['SITE_ID']['CURRENT'])?>">

		<div class="landing-form-title-block">
			<div class="ui-form-title-block">
				<div class="landing-editable-field --one-row" id="<?= $template->getFieldId('EDITABLE_TITLE') ?>">
					<label class="landing-editable-field-label landing-editable-field-label-js">
						<?= $row['TITLE']['CURRENT']?>
					</label>
					<input type="text"
						name="fields[TITLE]"
						class="ui-input landing-editable-field-input landing-editable-field-input-js"
						value="<?=$row['TITLE']['CURRENT']?>"
						placeholder="<?=$row['TITLE']['TITLE']?>"
						autocomplete="off"
					/>
					<div class="landing-editable-field-buttons">
						<div class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen">
							<div class="ui-icon-set --pencil-60"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="ui-form ui-form-section">
			<!--Adress-->
			<div class="ui-form-row">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">
						<?= Loc::getMessage('LANDING_TPL_FIELD_CODE') ?>
					</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-form-row">
						<div class="landing-form-site-name-block">
							<span class="landing-form-site-name-label">
								<?php
								echo $domainName;
								if ($isMainpageEditor)
								{
									if ($siteCurrent)
									{
										echo htmlspecialcharsbx(Manager::getPublicationPath());
									}
									else
									{
										echo '/';
									}
								}
								elseif ($isIntranet)
								{
									if ($siteCurrent)
									{
										echo htmlspecialcharsbx(
											Manager::getPublicationPath(trim($siteCurrent['CODE'], '/'))
										);
									}
									else
									{
										echo '/';
									}
								}
								elseif (Manager::isB24())
								{
									if ($siteCurrent && $siteCurrent['TYPE'] == 'SMN')
									{
										echo htmlspecialcharsbx(Manager::getPublicationPath(
											null,
											$siteCurrent['SMN_SITE_ID']
										));
									}
									else
									{
										echo '/';
									}
								}
								else
								{
									echo htmlspecialcharsbx(Manager::getPublicationPath(
										null,
										$request->get('site')
									));
								}
								if ($arResult['FOLDER_PATH'])
								{
									echo htmlspecialcharsbx(ltrim($arResult['FOLDER_PATH'], '/'));
								}
								?>
							</span>
							<input type="<?=($isIndex || $isFolderIndex) ? 'hidden' : 'text'?>"
								name="fields[CODE]"
								value="<?=$row['CODE']['CURRENT']?>"
								maxlength="100"
								class="ui-input"/>
							<?=($isIndex || $isFolderIndex) ? '' : '<span class="landing-form-site-name-label">/</span>'?>
							<?php if ($isIndex && !$isMainpageEditor): ?>
								<?php
								$link1 = '';
								if ($arParams['PAGE_URL_SITE_EDIT'])
								{
									$link1 = $isAjax
										? '<a data-page="SITE_EDIT" style="cursor: pointer;">'
										: '<a href="' . $arParams['PAGE_URL_SITE_EDIT'] . '">'
									;
								}
								$link2 = $link1 ? '</a>' : '';
								?>
								<div class="landing-form-field-description">
									<?= $component->getMessageType('LANDING_TPL_CODE_SETTINGS', [
										'#LINK1#' => $link1,
										'#LINK2#' => $link2,
									]) ?>
								</div>
								<?php if (!$isAjax && $arParams['PAGE_URL_SITE_EDIT']): ?>
									<script>
										BX.ready(function()
										{
											if (typeof BX.SidePanel !== 'undefined')
											{
												BX.SidePanel.Instance.bindAnchors({
													rules: [{
														condition: ['<?= str_replace(['?', '&'], ['\\\?', '\\\&'], CUtil::jsEscape($arParams['PAGE_URL_SITE_EDIT']))?>'],
														options: { allowChangeHistory: false }
													}]
												});
											}
										});
									</script>
								<?php endif; ?>
							<?php elseif ($isFolderIndex): ?>
								<div class="landing-form-field-description">
									<?=$component->getMessageType('LANDING_TPL_CODE_FOLDER_SETTINGS', [
										'#LINK1#' => $arParams['PAGE_URL_FOLDER_EDIT'] ? '<a href="'
											. str_replace('#folder_edit#',
												$row['FOLDER_ID']['CURRENT'],
												$arParams['PAGE_URL_FOLDER_EDIT'])
											. '">' : '',
										'#LINK2#' => $arParams['PAGE_URL_FOLDER_EDIT'] ? '</a>' : '',
									])?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!--Image-->
			<?php if (isset($hooks['METAOG'])): ?>
			<?php $pageFields = $hooks['METAOG']->getPageFields(); ?>
			<div class="ui-form-row landing-form-row-metaog">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">
						<?= $component->getMessageType('LANDING_FIELD_TITLE_METAOG_NAME') ?>
					</div>
				</div>
				<div class="ui-form-content landing-form-content-metaog">
					<div class="ui-form-row">
						<div class="landing-form-social-view">
							<?php
							if (isset($pageFields['METAOG_IMAGE'])):
								$imgPath = '';
								if (!empty($meta['og:image']))
								{
									$imgPath = array_shift($meta['og:image']);
									if (isset($imgPath['src']))
									{
										$imgPath = $imgPath['src'];
									}
								}
								$template->showPictureJS(
									$pageFields['METAOG_IMAGE'],
									Manager::isB24()
										? 'https://' . $domainName . '/preview.jpg'
										: $imgPath,
									[
										'imgEdit' => true,
										'width' => 520,
										'height' => 520,
										'uploadParams' =>
											$row['ID']['CURRENT']
												? [
												'action' => 'Landing::uploadFile',
												'lid' => $row['ID']['CURRENT'],
											]
												: [//
											],
									]
								);
								?>
							<?php endif; ?>
							<div class="landing-form-social-text-block">
								<?php if (isset($pageFields['METAOG_TITLE'])):
									if (!$pageFields['METAOG_TITLE']->getValue())
									{
										$pageFields['METAOG_TITLE']->setValue($meta['og:title']);
									}
									?>
									<script>
										BX.ready(function ()
										{
											new BX.Landing.EditTitleForm({
												node: BX('<?=$template->getFieldId('EDITABLE_PAGE_TITLE') ?>'),
												isEventTargetNode: true,
												display: true,
												isAiAvailable: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_AVAILABLE']) ?>,
												isAiActive: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_ACTIVE']) ?>,
												aiUnactiveInfoCode: <?= \CUtil::PhpToJSObject($arResult['AI_UNACTIVE_INFO_CODE']) ?>,
												siteId: '<?= $row['SITE_ID']['CURRENT'] ?>',
											});
										});
									</script>
									<div class="landing-form-social-text-title">
										<span
											class="landing-editable-field"
											id="<?= $template->getFieldId('EDITABLE_PAGE_TITLE') ?>"
										>
											<label class="landing-editable-field-label landing-editable-field-label-js">
												<?=htmlspecialcharsbx($pageFields['METAOG_TITLE']->getValue())?>
											</label>
											<?php
											$pageFields['METAOG_TITLE']->viewForm([
												'class' => 'ui-input landing-editable-field-input landing-editable-field-input-js',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
												'id' => $template->getFieldId('METAOG_TITLE'),
												'rows' => 1,
												'autocomplete' => 'off',
											]);
											?>
											<div class="landing-editable-field-buttons">
												<div class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen">
													<div class="ui-icon-set --pencil-60"></div>
												</div>
												<?php if ($arResult['AI_TEXT_AVAILABLE']): ?>
													<div class="landing-editable-field-button --copilot"></div>
												<?php endif; ?>
											</div>
										</span>
									</div>
								<?php endif; ?>
								<?php if (isset($pageFields['METAOG_DESCRIPTION'])):
									if (!$pageFields['METAOG_DESCRIPTION']->getValue())
									{
										$pageFields['METAOG_DESCRIPTION']->setValue($meta['og:description']);
									}
									?>
									<script>
										BX.ready(function ()
										{
											new BX.Landing.EditTitleForm({
												node: BX('<?=$template->getFieldId('EDITABLE_PAGE_TEXT') ?>'),
												isEventTargetNode: true,
												isAiAvailable: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_AVAILABLE']) ?>,
												isAiActive: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_ACTIVE']) ?>,
												aiUnactiveInfoCode: <?= \CUtil::PhpToJSObject($arResult['AI_UNACTIVE_INFO_CODE']) ?>,
												siteId: '<?= $row['SITE_ID']['CURRENT'] ?>',
												display: true,
											});
										});
									</script>
									<div class="landing-form-social-text">
										<span
											class="landing-editable-field landing-editable-field-textar-wrap"
											id="<?= $template->getFieldId('EDITABLE_PAGE_TEXT') ?>"
										>
											<label class="landing-editable-field-label landing-editable-field-label-js">
												<?=htmlspecialcharsbx($pageFields['METAOG_DESCRIPTION']->getValue())?>
											</label>
											<?php
											$pageFields['METAOG_DESCRIPTION']->viewForm([
												'class' => 'ui-textarea landing-editable-field-textarea landing-editable-field-input-js',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
												'autocomplete' => 'off',
												'rows' => '1',
											]);
											?>
											<div class="landing-editable-field-buttons">
												<div class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen">
													<div class="ui-icon-set --pencil-60"></div>
												</div>
												<?php if ($arResult['AI_TEXT_AVAILABLE']): ?>
													<div class="landing-editable-field-button --copilot"></div>
												<?php endif; ?>
											</div>
										</span>
									</div>
								<?php endif; ?>
								<?php if (!$isIntranet): ?>
									<div class="landing-form-social-site-name"><?=$domainName?></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif;?>
		</div>

		<!--Additional labels-->
		<?php if (!$isArea): ?>
			<div class="landing-form-additional-fields landing-form-collapse-block landing-form-additional-fields-js">
				<span class="landing-form-collapse-label"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL')?></span>
				<span class="landing-additional-alt-promo-wrap">
					<?php if (isset($hooks['B24BUTTON'])): ?>
						<span class="landing-additional-alt-promo-text"
							  data-landing-additional-option="b24widget"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_WIDGET')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['METAMAIN'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="meta"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_TAGS')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['VIEW'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="view"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_VIEW')?></span>
					<?php endif; ?>
					<?php if ($arResult['TEMPLATES']): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="layout"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_LAYOUT')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="metrika"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_METRIKA')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="pixel"><?=Loc::getMessage('LANDING_TPL_HOOK_PIXEL')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['METAROBOTS'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="index"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_INDEX')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['HEADBLOCK'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="html"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_HTML')?></span>
					<?php endif; ?>
					<?php if (isset($hooks['CSSBLOCK'])): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="css"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_CSS')?></span>
					<?php endif; ?>
					<?php if (!$isIntranet && !$isFormEditor && !$isMainpageEditor && !$isSMN): ?>
						<span class="landing-additional-alt-promo-text"
							data-landing-additional-option="sitemap"><?=Loc::getMessage('LANDING_TPL_ADDITIONAL_SITEMAP')?></span>
					<?php endif; ?>
				</span>
			</div>

			<div class="ui-form ui-form-section landing-form-additional">
				<!--Widget-->
				<?php if (isset($hooks['B24BUTTON'])): ?>
					<?php $pageFields = $hooks['B24BUTTON']->getPageFields(); ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="b24widget">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['B24BUTTON']->getPageTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<div class="ui-form-label" data-form-row-hidden>
									<?php $template->showField($pageFields['B24BUTTON_USE'], ['title' => true]); ?>
								</div>
								<div class="ui-form-row-hidden">
									<div class="ui-form-row landing-form-widget">
										<?php
										$template->showField($pageFields['B24BUTTON_CODE']);
										?>
										<?php if (ModuleManager::isModuleInstalled('crm')):?>
											<a href="/crm/button/" class="landing-form-input-right" target="_blank">
												<?= Loc::getMessage('LANDING_TPL_ACTION_SETTINGS') ?>
											</a>
										<?php elseif (ModuleManager::isModuleInstalled('b24connector')):?>
											<a href="/bitrix/admin/b24connector_b24connector.php?lang=<?= LANGUAGE_ID ?>" class="landing-form-input-right" target="_blank">
												<?= Loc::getMessage('LANDING_TPL_ACTION_SETTINGS') ?>
											</a>
										<?php else:?>
											<a href="/bitrix/admin/module_admin.php?lang=<?= LANGUAGE_ID ?>" class="landing-form-input-right" target="_blank">
												<?= Loc::getMessage('LANDING_TPL_ACTION_INSTALL_B24') ?>
											</a>
										<?php endif;?>
									</div>
									<div class="ui-form-label">
										<div class="ui-ctl-label-text">
											<?= $pageFields['B24BUTTON_COLOR']->getLabel() ?>
										</div>
									</div>
									<div class="ui-form-row">
										<?php $template->showField($pageFields['B24BUTTON_COLOR'], ['additional' => 'readonly']); ?>
										<script>
											BX.ready(function() {
												new BX.Landing.B24ButtonColor(
													BX('<?= $template->getFieldId('B24BUTTON_COLOR') ?>'),
													BX('<?= $template->getFieldId('B24BUTTON_COLOR_VALUE') ?>')
												);
											});
										</script>
										<div class="landing-ui-form-row-hidden">
											<?php $template->showField($pageFields['B24BUTTON_HELP'], ['additional' => 'hidden']); ?>
										</div>
									</div>

									<?php $template->showField($pageFields['B24BUTTON_COLOR_VALUE'], ['title' => true]); ?>
									<script>
										BX.ready(function() {
											new BX.Landing.ColorPicker(BX('<?= $template->getFieldId('B24BUTTON_COLOR_VALUE') ?>'));
										});
									</script>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!--MetaMain-->
				<?php if (isset($hooks['METAMAIN'])): ?>
					<?php $pageFields = $hooks['METAMAIN']->getPageFields(); ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="meta">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['METAMAIN']->getTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<div class="ui-form-label" data-form-row-hidden>
									<?php $template->showField($pageFields['METAMAIN_USE'], ['title' => true]); ?>
								</div>
								<div class="ui-form-row-hidden">
									<div class="landing-form-field-description">
										<?=$hooks['METAMAIN']->getDescription()?>
									</div>
									<?php if (
										isset($pageFields['METAMAIN_TITLE']) && isset($pageFields['METAMAIN_DESCRIPTION'])
									):
										if (!$pageFields['METAMAIN_TITLE']->getValue())
										{
											$pageFields['METAMAIN_TITLE']->setValue($meta['title']);
										}
										if (!$pageFields['METAMAIN_DESCRIPTION']->getValue())
										{
											$pageFields['METAMAIN_DESCRIPTION']->setValue($meta['description']);
										}
										?>
										<div class="landing-form-meta">
											<div
												class="landing-form-meta-title"
												id="<?= $template->getFieldId('METAMAIN_TITLE_NODE') ?>"
											>
												<?=htmlspecialcharsbx($pageFields['METAMAIN_TITLE']->getValue())?>
											</div>
											<div class="landing-form-meta-link"><?=$domainProtocol?>://<?=$domainName?>/</div>
											<div
												class="landing-form-meta-text"
												id="<?= $template->getFieldId('METAMAIN_DESCRIPTION_NODE') ?>"
											>
												<?=htmlspecialcharsbx($pageFields['METAMAIN_DESCRIPTION']->getValue())?>
											</div>
										</div>
										<?php $template->showField($pageFields['METAMAIN_TITLE'], ['title' => true, 'buttons' => ['copilot']]);?>
										<?php $template->showField($pageFields['METAMAIN_DESCRIPTION'], ['title' => true, 'buttons' => ['copilot']]);?>

										<script>
											BX.ready(function ()
											{
												new BX.Landing.FieldLengthLimited(
													{
														field: BX('<?= $template->getFieldId('METAMAIN_TITLE') ?>'),
														node: BX('<?= $template->getFieldId('METAMAIN_TITLE_NODE') ?>'),
														length: 75,
														isAiAvailable: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_AVAILABLE']) ?>,
														isAiActive: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_ACTIVE']) ?>,
													},
												);
												new BX.Landing.FieldLengthLimited(
													{
														field: BX('<?= $template->getFieldId('METAMAIN_DESCRIPTION') ?>'),
														node: BX('<?= $template->getFieldId('METAMAIN_DESCRIPTION_NODE') ?>'),
														length: 200,
														isAiAvailable: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_AVAILABLE']) ?>,
														isAiActive: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_ACTIVE']) ?>,
													},
												);
											});
										</script>

										<?php if (isset($pageFields['METAMAIN_KEYWORDS'])): ?>
											<?php $template->showField($pageFields['METAMAIN_KEYWORDS'], ['title' => true, 'buttons' => ['copilot']]);?>
										<script>
											BX.ready(function ()
											{
												new BX.Landing.FieldLengthLimited(
													{
														field: BX('<?= $template->getFieldId('METAMAIN_KEYWORDS') ?>'),
														length: null,
														isAiAvailable: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_AVAILABLE']) ?>,
														isAiActive: <?= \CUtil::PhpToJSObject($arResult['AI_TEXT_ACTIVE']) ?>,
													},
												);
											});
										</script>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div data-landing-additional-detail="view">
				<?php endif;?>

				<!--View-->
				<?php if (isset($hooks['VIEW'])): ?>
					<?php $pageFields = $hooks['VIEW']->getPageFields(); ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="view">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['VIEW']->getTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<div class="ui-form-label" data-form-row-hidden>
									<?php $template->showField($pageFields['VIEW_USE'], ['title' => true]); ?>
								</div>
								<div class="ui-form-row-hidden">
									<?php if (isset($pageFields['VIEW_TYPE'])):
										$value = $pageFields['VIEW_TYPE']->getValue();
										$items = $hooks['VIEW']->getItems();
										if (!$value)
										{
											$array = array_keys($items);
											$value = array_shift($array);
										}
										?>
										<div class="landing-form-type-page-wrap">
											<?php foreach ($items as $key => $title): ?>
												<span class="landing-form-type-page landing-form-type-<?=$key?>">
													<input type="radio" <?php
													?>name="fields[ADDITIONAL_FIELDS][VIEW_TYPE]" <?php
													?>class="ui-radio" <?php
													?>id="<?= $template->getFieldId('VIEW_TYPE_' . $key) ?>" <?php
													?><? if ($value === $key) { ?> checked="checked"<? } ?> <?php
													?>value="<?=$key?>"/>
													<label for="<?= $template->getFieldId('VIEW_TYPE_' . $key) ?>">
														<span class="landing-form-type-page-img"></span>
														<span class="landing-form-type-page-title"><?=$title?></span>
													</label>
												</span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				<?php endif;?>

				<!--Template-->
				<?php if ($arResult['TEMPLATES']): ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="layout">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= Loc::getMessage('LANDING_TPL_LAYOUT') ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<div class="ui-form-label" data-form-row-hidden>
									<?php
									$tplRefs = $arResult['TEMPLATES_REF'];
									$saveRefs = [];
									$tplUsed = false;
									if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]))
									{
										$tplUsed = true;
										$areaCount = $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'];
										for ($i = 1; $i <= $areaCount; $i++)
										{
											$saveRefs[] = $i . ':' . ($tplRefs[$i] ?? '0');
										}
									}
									$saveRefs = implode(',', $saveRefs);
									?>
									<input
										type="hidden"
										name="fields[TPL_REF]"
										value="<?= $saveRefs ?>"
										id="<?= $template->getFieldId('LAYOUT_TPLREFS') ?>"
									/>

									<label class="ui-ctl ui-ctl-checkbox">
										<input
											type="checkbox"
											class="ui-ctl-element"
											id="<?= $template->getFieldId('LAYOUT_TPLREFS_USE') ?>"
											<?= $tplUsed ? ' checked ' : '' ?>
										/>
										<div class="ui-ctl-label-text" for="<?= $template->getFieldId('LAYOUT_TPLREFS_USE') ?>">
											<?= Loc::getMessage('LANDING_TPL_LAYOUT_USE') ?>
										</div>
									</label>
								</div>
								<div
									class="ui-form-row-hidden landing-form-page-layout<?= $isMainpageEditor ? ' --main-page' : '' ?>"
									id="<?= $template->getFieldId('PAGE_LAYOUT') ?>"
								>
									<div class="landing-form-layout-select">
										<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl): ?>
											<input <?php
												?>class="layout-switcher <?= $template->getFieldClass('LAYOUT-RADIO_' . ($i + 1)) ?>" <?php
												?>data-layout="<?=$tpl['XML_ID']?>" <?php
												?>type="radio" <?php
												?>name="fields[TPL_ID]" <?php
												?>value="<?=$tpl['ID']?>" <?php
												?>id="<?= $template->getFieldId('LAYOUT-RADIO_' . ($i + 1)) ?>"<?php
												?><?php if ($tpl['ID'] === $row['TPL_ID']['CURRENT']) { ?> checked="checked"<?php } ?>
											>
										<?php endforeach; ?>
										<div class="landing-form-list">
											<div class="landing-form-select-buttons">
												<?php if (!$isMainpageEditor): ?>
													<div class="landing-form-select-prev"></div>
													<div class="landing-form-select-next"></div>
												<?php endif; ?>
											</div>
											<div class="landing-form-list-container">
												<div class="landing-form-list-inner">
													<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl): ?>
														<div class="landing-form-layout-item-img-container">
															<label class="landing-form-layout-item <?
																?>landing-form-layout-item-<?= $tpl['XML_ID'] ?>" <?php
																?>data-block="<?= $tpl['AREA_COUNT'] ?>" <?php
																?>data-layout="<?= $tpl['XML_ID'] ?>" <?php
																?>for="<?= $template->getFieldId('LAYOUT-RADIO_' . ($i + 1)) ?>"
															>
																<div class="landing-form-layout-item-img"></div>
															</label>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										</div>
									</div>
									<div class="landing-form-layout-detail">
										<div class="landing-form-layout-img-container">
											<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl): ?>
												<div class="landing-form-layout-img landing-form-layout-img-<?=$tpl['XML_ID']?>"
													data-layout="<?=$tpl['XML_ID']?>"></div>
											<?php endforeach; ?>
										</div>
										<div class="landing-form-layout-block-container"></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<script>
						BX.ready(function() {
							new BX.Landing.Layout({
								container: BX('<?= $template->getFieldId('PAGE_LAYOUT') ?>'),
								siteId: '<?= $row['SITE_ID']['CURRENT'] ?>',
								landingId: '<?= $row['ID']['CURRENT'] ?>',
								type: '<?= $siteCurrent ? $siteCurrent['TYPE'] : 'PAGE' ?>',
								tplUse: BX('<?= $template->getFieldId('LAYOUT_TPLREFS_USE') ?>'),
								valueField: BX('<?= $template->getFieldId('LAYOUT_TPLREFS') ?>'),
								messages: {
									area: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_LAYOUT_AREA')) ?>'
								},
								<?php if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']])):?>
								areasCount: <?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'] ?>,
								current: '<?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['XML_ID'] ?>',
								<?php else:?>
								areasCount: 0,
								current: 'empty',
								<?php endif;?>
								<?php if (isset($arResult['TEMPLATES_REF_DEFAULT'])):?>
								defaultValues: <?= CUtil::PhpToJSObject($arResult['TEMPLATES_REF_DEFAULT']) ?>,
								<?php endif;?>
							});
						});
					</script>
				<?php endif;?>

				<!--Analytics-->
				<?php if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])): ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="metrika">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA') ?>
							</div>
						</div>
						<div class="ui-form-content">
							<?php
							$gaCounterFields = $hooks['GACOUNTER']->getPageFields();
							$fieldId = $template->getFieldId('GACOUNTER');
							if (!$gaCounterFields['GACOUNTER_CLICK_TYPE']->getValue())
							{
								$gaCounterFields['GACOUNTER_CLICK_TYPE']->setValue('text');
							}
							?>
							<div class="ui-form-row landing-form-gacounter" id="<?= $fieldId ?>">
								<?php
								$isLocked = $hooks['GACOUNTER']->isLocked();
								?>
								<div class="ui-form-label landing-form-gacounter-use-js <?= $isLocked ? ' landing-form-label__locked' : ''?>"<?= $isLocked ? '' : 'data-form-row-hidden'?>>
									<?php $template->showField($gaCounterFields['GACOUNTER_USE'], ['title' => true]);?>
									<?php
									if ($isLocked)
									{
										echo Restriction\Manager::getLockIcon(
											Restriction\Hook::getRestrictionCodeByHookCode('GACOUNTER'),
											[$fieldId]
										);
									}
									?>
								</div>
								<div class="ui-form-row-hidden">
									<div class="ui-form-row">
										<div class="ui-form-label">
											<?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA_COUNTER') ?>
										</div>
										<?php $template->showField($gaCounterFields['GACOUNTER_COUNTER']); ?>
										<div class="ui-form-label landing-form-gacounter-send-js" data-form-row-hidden>
											<label class="ui-ctl ui-ctl-checkbox">
												<input type="checkbox" class="ui-ctl-element">
												<div class="ui-ctl-label-text">
													<?=$gaCounterFields['GACOUNTER_SEND_CLICK']->getLabel()?>
												</div>
											</label>
										</div>
										<div class="ui-form-row-hidden">
											<?php $template->showField($gaCounterFields['GACOUNTER_CLICK_TYPE']); ?>
										</div>
										<?php $template->showField($gaCounterFields['GACOUNTER_SEND_SHOW'], ['title' => true]); ?>
									</div>
									<div class="ui-form-row">
										<div class="ui-form-label">
											<?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA_COUNTER_GA4') ?>
										</div>
										<?php $template->showField($gaCounterFields['GACOUNTER_COUNTER_GA4']); ?>
									</div>
								</div>
							</div>
							<?php
							$template->showFieldWithToggle('GTM', ['restrictionCode' => 'GACOUNTER']);
							if ($availableOnlyForZoneRu)
							{
								$template->showFieldWithToggle('YACOUNTER', ['restrictionCode' => 'GACOUNTER']);
							}
							?>
							<script>
								BX.ready(function ()
								{
									new BX.Landing.ExternalMetrika(
										BX('<?=$template->getFieldId('GACOUNTER_USE')?>'),
										BX('<?=$template->getFieldId('GACOUNTER_SEND_CLICK')?>'),
										BX('<?=$template->getFieldId('GACOUNTER_SEND_SHOW')?>')
									);
								});
							</script>
						</div>
					</div>
				<?php endif;?>

				<!--Pixels-->
				<?php if (isset($hooks['PIXELFB'], $hooks['PIXELVK'])): ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="pixel">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= Loc::getMessage('LANDING_TPL_HOOK_PIXEL') ?>
							</div>
						</div>
						<div class="ui-form-content">
							<?php
							$zone = '';
							if (Loader::includeModule('bitrix24'))
							{
								$zone = \CBitrix24::getPortalZone();
							}
							elseif (
								file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/lang/ru")
								&& !file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/lang/ua")
							)
							{
								$zone = 'ru';
							}
							if ($zone !== 'ru')
							{
								$template->showFieldWithToggle('PIXELFB');
							}

							if ($availableOnlyForZoneRu)
							{
								$template->showFieldWithToggle('PIXELVK');
							}
							?>
						</div>
					</div>
				<?php endif;?>

				<!--MetaRobots-->
				<?php if (isset($hooks['METAROBOTS'])): ?>
					<?php $pageFields = $hooks['METAROBOTS']->getPageFields(); ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="index">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['METAROBOTS']->getTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<?php
								if (isset($pageFields['METAROBOTS_INDEX']))
								{
									if (!$pageFields['METAROBOTS_INDEX']->getValue())
									{
										$pageFields['METAROBOTS_INDEX']->setValue('Y');
									}
									$template->showField($pageFields['METAROBOTS_INDEX'], ['title' => true]);
									?>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				<?php endif;?>

				<!--HTML-->
				<?php if (isset($hooks['HEADBLOCK'])): ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="html">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['HEADBLOCK']->getTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<?php $template->showFieldWithToggle('HEADBLOCK'); ?>
						</div>
					</div>
				<?php endif;?>

				<!--CSS-->
				<?php if (isset($hooks['CSSBLOCK'])): ?>
					<?php $pageFields = $hooks['CSSBLOCK']->getPageFields(); ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="css">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $hooks['CSSBLOCK']->getTitle() ?>
							</div>
						</div>
						<div class="ui-form-content">
							<div class="ui-form-row">
								<div class="ui-form-label" data-form-row-hidden>
									<?php $template->showField($pageFields['CSSBLOCK_USE'], ['title' => true]);?>
								</div>
								<div class="ui-form-row-hidden">
									<div class="ui-form-row">
										<?php $template->showField($pageFields['CSSBLOCK_CODE']); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endif;?>

				<!--SITEMAP-->
				<?php if (!$isIntranet && !$isFormEditor && !$isMainpageEditor && !$isSMN): ?>
					<div class="ui-form-row landing-form-additional-row" data-landing-additional-detail="sitemap">
						<div class="ui-form-label">
							<div class="ui-ctl-label-text">
								<?= $row['SITEMAP']['TITLE'] ?>
							</div>
						</div>
						<div class="ui-form-content">
							<label class="ui-ctl ui-ctl-checkbox">
								<input type="hidden" name="fields[SITEMAP]" value="N">
								<input type="checkbox"
									class="ui-ctl-element ui-field-sitemap"
									name="fields[SITEMAP]"
									value="Y"<? if ($row['SITEMAP']['CURRENT'] == 'Y') { ?> checked="checked"<? } ?> />
								<div class="ui-ctl-label-text" for="checkbox-sitemap">
									<?= Loc::getMessage('LANDING_TPL_ACTION_ADD_IN_SITEMAP')?>
								</div>
							</label>
						</div>
					</div>
				<?php endif;?>
			</div>
		<?php endif;?>
		<?php
		// for complex component landing.settings not need buttons. If isAjax will be incorrect - need add other flag for landgin.settings
		if (!$isAjax)
		{
			$buttonSave = [
				'TYPE' => 'save',
				'ID' => 'landing-save-btn',
				'NAME' => 'submit',
				'CAPTION' => Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['LANDING_ID'] ? 'SAVE' : 'ADD')),
				'VALUE' => Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD')),
			];
			$buttonCancel = [
				'TYPE' => 'cancel',
				'CAPTION' => Loc::getMessage('LANDING_TPL_BUTTON_CANCEL'),
				'LINK' => $arParams['PAGE_URL_LANDINGS'],
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
	BX.ready(function()
	{
		BX.UI.Hint.init(BX('landing-page-set-form'));
		new BX.UI.LayoutForm({container: BX('landing-page-set-form')});
		new BX.Landing.ToggleAdditionalFields(BX('landing-page-set-form'));
		new BX.Landing.EditTitleForm({
			node: BX('<?= $template->getFieldId('EDITABLE_TITLE') ?>'),
			additionalWidth: 600,
			isEventTargetNode: true,
		});
	});
</script>
