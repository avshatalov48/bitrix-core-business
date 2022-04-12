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
use CJSCore;
use CMain;
use CUtil;
use LandingEditComponent;
use function htmlspecialcharsback;
use function htmlspecialcharsbx;

Loc::loadMessages(__FILE__);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

Extension::load('ui.buttons');

if ($arResult['ERRORS'])
{
	?><div class="landing-message-label error"><?php
	foreach ($arResult['ERRORS'] as $error)
	{
		echo $error . '<br/>';
	}
	?></div>
	<?php
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
$tplRefs = $arResult['TEMPLATES_REF'];
$sites = $arResult['SITES'];
$isIntranet = $arResult['IS_INTRANET'];
$formEditor = $arResult['SPECIAL_TYPE'] == Site\Type::PSEUDO_SCOPE_CODE_FORMS;
$siteCurrent = isset($sites[$row['SITE_ID']['CURRENT']])
				? $sites[$row['SITE_ID']['CURRENT']]
				: null;
$isSMN = $siteCurrent['TYPE'] === 'SMN';

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
CJSCore::init(array('color_picker', 'landing_master'));
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

// special for forms
if ($formEditor)
{
	$hooks = [
		'BACKGROUND' => $hooks['BACKGROUND'],
		'METAOG' => $hooks['METAOG'],
		'YACOUNTER' => $hooks['YACOUNTER'],
		'GACOUNTER' => $hooks['GACOUNTER'],
		'GTM' => $hooks['GTM'],
	];
	$arResult['TEMPLATES'] = [];
}
?>

<script type="text/javascript">
	BX.ready(function()
	{
		var editComponent = new BX.Landing.EditComponent();
		top.window['landingSettingsSaved'] = false;
		<?php if ($arParams['SUCCESS_SAVE']):?>
		top.window['landingSettingsSaved'] = true;
		top.BX.onCustomEvent('BX.Landing.Filter:apply');
		editComponent.actionClose();
		top.BX.Landing.UI.Tool.ActionDialog.getInstance().close();
		<?php endif;?>
		BX.Landing.Env.createInstance({
			params: {type: '<?= $arParams['TYPE'] ?>'}
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

<form action="<?= htmlspecialcharsbx($uriSave->getUri()) ?>" method="post" class="ui-form ui-form-gray-padding landing-form-collapsed landing-form-settings landing-page-set-form" id="landing-page-set-form">
	<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
	<input type="hidden" name="fields[SITE_ID]" value="<?= htmlspecialcharsbx($row['SITE_ID']['CURRENT'])?>">
	<?= bitrix_sessid_post()?>

	<div class="ui-form-title-block">
		<span class="ui-editable-field" id="ui-editable-title">
			<label id="METAOG_TITLE_TEXT" class="ui-editable-field-label ui-editable-field-label-js"><?= $row['TITLE']['CURRENT']?></label>
			<input type="text" id="METAOG_TITLE" name="fields[TITLE]" class="ui-input ui-editable-field-input ui-editable-field-input-js" value="<?= $row['TITLE']['CURRENT']?>" placeholder="<?= $row['TITLE']['TITLE']?>" />
			<span class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen"></span>
		</span>
	</div>

	<div class="landing-form-inner-js landing-form-inner">
		<div class="landing-form-table-wrap landing-form-table-wrap-js ui-form-inner">
			<table class="ui-form-table landing-form-table">
				<tr class="landing-form-site-name-fieldset">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_FIELD_CODE') ?></td>
					<td class="ui-form-right-cell">
						<div class="landing-form-site-name-block">
							<span class="landing-form-site-name-label">
								<?php
								echo $domainName;
								if ($isIntranet)
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
								else if (Manager::isB24())
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
							<input type="<?= ($isIndex || $isFolderIndex) ? 'hidden' : 'text' ?>" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'] ?>" class="ui-input" />
							<?= ($isIndex || $isFolderIndex) ? '' : '<span class="landing-form-site-name-label">/</span>' ?>
							<?php if ($isIndex):?>
								<div class="ui-form-field-description">
									<?= $component->getMessageType('LANDING_TPL_CODE_SETTINGS', [
										'#LINK1#' => $arParams['PAGE_URL_SITE_EDIT'] ? '<a href="' . $arParams['PAGE_URL_SITE_EDIT'] . '">' : '',
										'#LINK2#' => $arParams['PAGE_URL_SITE_EDIT'] ? '</a>' : ''
									]) ?>
								</div>
							<?php elseif ($isFolderIndex):?>
								<div class="ui-form-field-description">
									<?= $component->getMessageType('LANDING_TPL_CODE_FOLDER_SETTINGS', [
										'#LINK1#' => $arParams['PAGE_URL_FOLDER_EDIT'] ? '<a href="' . str_replace('#folder_edit#', $row['FOLDER_ID']['CURRENT'], $arParams['PAGE_URL_FOLDER_EDIT']) . '">' : '',
										'#LINK2#' => $arParams['PAGE_URL_FOLDER_EDIT'] ? '</a>' : ''
									]) ?>
								</div>
							<?php endif;?>
						</div>
					</td>
				</tr>
				<?php if (isset($hooks['METAOG'])):
					$pageFields = $hooks['METAOG']->getPageFields();
					?>
				<tr>
					<td class="ui-form-label ui-form-label-align-top">
						<?= $component->getMessageType('LANDING_FIELD_TITLE_METAOG_NAME') ?>
					</td>
					<td class="ui-form-right-cell">
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
									array(
										'imgId' => 'landing-form-social-img',
										'imgEditId' => 'landing-form-social-img-edit',
										'width' => 1200,
										'height' => 1200,
										'uploadParams' =>
											$row['ID']['CURRENT']
												? array(
												'action' => 'Landing::uploadFile',
												'lid' => $row['ID']['CURRENT']
											)
												: array(
												//
											)
									)
								);
								?>
								<div class="landing-form-social-img-block" id="landing-form-social-img"></div>
								<div class="landing-form-social-img-edit" id="landing-form-social-img-edit"></div>
								<?php endif; ?>
							<div class="landing-form-social-text-block">
								<?php if (isset($pageFields['METAOG_TITLE'])):
								if (!$pageFields['METAOG_TITLE']->getValue())
								{
									$pageFields['METAOG_TITLE']->setValue($meta['og:title']);
								}
								?>
								<script type="text/javascript">
									BX.ready(function()
									{
										new BX.Landing.EditTitleForm(BX('ui-editable-page-title'), 0, true, true);
									});
								</script>
								<div class="landing-form-social-text-title">
									<span class="ui-editable-field" id="ui-editable-page-title">
										<label id="metaog-title-text" class="ui-editable-field-label ui-editable-field-label-js">
											<?= htmlspecialcharsbx($pageFields['METAOG_TITLE']->getValue()) ?>
										</label>
										<?php
										$pageFields['METAOG_TITLE']->viewForm(array(
											'class' => 'ui-input ui-editable-field-input ui-editable-field-input-js',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
											'id' => 'metaog-title-field'
										));
										?>
										<span class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen"></span>
									</span>
								</div>
							<?php endif;?>
								<?php if (isset($pageFields['METAOG_DESCRIPTION'])):
								if (!$pageFields['METAOG_DESCRIPTION']->getValue())
								{
									$pageFields['METAOG_DESCRIPTION']->setValue($meta['og:description']);
								}
								?>
								<script type="text/javascript">
									BX.ready(function()
									{
										new BX.Landing.EditTitleForm(BX('ui-editable-page-text'), 0, true);
									});
								</script>
								<div class="landing-form-social-text">
									<span class="ui-editable-field ui-editable-field-textar-wrap" id="ui-editable-page-text">
										<label class="ui-editable-field-label ui-editable-field-label-js">
											<?= htmlspecialcharsbx($pageFields['METAOG_DESCRIPTION']->getValue()) ?>
										</label>
										<?php
										$pageFields['METAOG_DESCRIPTION']->viewForm(array(
											'class' => 'ui-textarea ui-editable-field-textarea ui-editable-field-input-js',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
										<span class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen"></span>
									</span>
								</div>
							<?php endif;?>
								<?php if (!$isIntranet):?>
								<div class="landing-form-social-site-name"><?= $domainName ?></div>
							<?php endif;?>
							</div>
						</div>
					</td>
				</tr>
				<?php endif;?>

				<tr>
					<td class="ui-form-right-cell ui-form-collapse" colspan="2">
						<div class="ui-form-collapse-block landing-form-collapse-block-js">
							<span class="ui-form-collapse-label"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL') ?></span>
							<span class="landing-additional-alt-promo-wrap">
								<?php if (isset($hooks['METAMAIN'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="meta"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_TAGS') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['VIEW'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="view"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_VIEW') ?></span>
								<?php endif;?>
								<?php if ($arResult['TEMPLATES']):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="layout"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_LAYOUT') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="metrika"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_METRIKA') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="pixel"><?= Loc::getMessage('LANDING_TPL_HOOK_PIXEL') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['METAROBOTS'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="index"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_INDEX') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['HEADBLOCK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="html"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_HTML') ?></span>
								<?php endif;?>
								<?php if (isset($hooks['CSSBLOCK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="css"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_CSS') ?></span>
								<?php endif;?>
								<?php if (!$isIntranet && !$formEditor && !$isSMN):?>
								<span class="landing-additional-alt-promo-text" data-landing-additional-option="sitemap"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_SITEMAP') ?></span>
								<?php endif;?>
							</span>
						</div>
					</td>
				</tr>

				<?php if (isset($hooks['METAMAIN'])):
					$pageFields = $hooks['METAMAIN']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="meta">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['METAMAIN']->getTitle() ?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input landing-form-meta-block">
							<?php
							if (isset($pageFields['METAMAIN_USE']))
							{
								$pageFields['METAMAIN_USE']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-metamain-use',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
							}
							?>
							<div class="ui-checkbox-hidden-input-inner">
								<?php if (isset($pageFields['METAMAIN_USE'])):?>
								<label class="ui-checkbox-label" for="checkbox-metamain-use">
									<?= $pageFields['METAMAIN_USE']->getLabel() ?>
								</label>
								<?php endif;?>
								<div class="landing-form-wrapper">
									<div class="ui-form-field-description">
										<?= $hooks['METAMAIN']->getDescription() ?>
									</div>
								<?php if (
									isset($pageFields['METAMAIN_TITLE']) &&
									isset($pageFields['METAMAIN_DESCRIPTION'])
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
									<script type="text/javascript">
										BX.ready(function()
										{
											BX.Landing.CustomFields([
												{field:BX('landing-meta-title-field'), node:BX('landing-meta-title-text'), length: 75},
												{field:BX('landing-meta-text-field'), node:BX('landing-meta-text'), length: 200}
											]);
										});
									</script>
									<div class="landing-form-meta">
										<div class="landing-form-meta-title" id="landing-meta-title-text">
											<?= htmlspecialcharsbx($pageFields['METAMAIN_TITLE']->getValue()) ?>
										</div>
										<div class="landing-form-meta-link"><?= $domainProtocol?>://<?= $domainName?>/</div>
										<div class="landing-form-meta-text"  id="landing-meta-text">
											<?= htmlspecialcharsbx($pageFields['METAMAIN_DESCRIPTION']->getValue()) ?>
										</div>
									</div>
									<div class="ui-control-wrap">
										<div class="ui-form-control-label"><?= $pageFields['METAMAIN_TITLE']->getLabel() ?></div>
										<?php
										$pageFields['METAMAIN_TITLE']->viewForm(array(
											'class' => 'ui-input',
											'id' => 'landing-meta-title-field',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
									</div>
									<div class="ui-control-wrap">
										<div class="ui-form-control-label"><?= $pageFields['METAMAIN_DESCRIPTION']->getLabel() ?></div>
										<?php
										$pageFields['METAMAIN_DESCRIPTION']->viewForm(array(
											'class' => 'ui-textarea',
											'id' => 'landing-meta-text-field',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
									</div>
									<?php if (isset($pageFields['METAMAIN_KEYWORDS'])):?>
									<div class="ui-control-wrap">
										<div class="ui-form-control-label"><?= $pageFields['METAMAIN_KEYWORDS']->getLabel() ?></div>
										<?php
										$pageFields['METAMAIN_KEYWORDS']->viewForm(array(
											'class' => 'ui-input',
											'id' => 'landing-meta-text-field',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
									</div>
								<?php endif;?>
								<?php endif;?>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<?php endif;?>

				<?if (isset($hooks['VIEW'])):
					$pageFields = $hooks['VIEW']->getPageFields();
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="view">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['VIEW']->getTitle() ?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-type-page-block">
								<?php
								if (isset($pageFields['VIEW_USE']))
								{
									$pageFields['VIEW_USE']->viewForm(array(
										'class' => 'ui-checkbox',
										'id' => 'checkbox-view-use',
										'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
									));
								}
								?>
								<div class="ui-checkbox-hidden-input-inner">
									<?php if (isset($pageFields['VIEW_USE'])):?>
										<label class="ui-checkbox-label" for="checkbox-view-use">
											<?= $pageFields['VIEW_USE']->getLabel() ?>
										</label>
									<?php endif;?>
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
											<?php foreach ($items as $key => $title):?>
												<span class="landing-form-type-page landing-form-type-<?= $key?>">
												<input type="radio" <?php
												?>name="fields[ADDITIONAL_FIELDS][VIEW_TYPE]" <?php
														?>class="ui-radio" <?php
														?>id="view-type-<?= $key?>" <?php
												?><?if ($value === $key){?> checked="checked"<?}?> <?php
														?>value="<?= $key ?>" />
												<label for="view-type-<?= $key?>">
													<span class="landing-form-type-page-img"></span>
													<span class="landing-form-type-page-title"><?= $title?></span>
												</label>
												</span>
											<?php endforeach;?>
										</div>
									<?php endif;?>
								</div>
							</div>
						</td>
					</tr>
				<?php endif;?>
				<?if ($arResult['TEMPLATES']):?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="layout">
						<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_LAYOUT') ?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input ui-checkbox-hidden-input-layout">
								<?php
								$saveRefs = '';
								$tplUsed = false;
								if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]))
								{
									$tplUsed = true;
									$aCount = $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'];
									for ($i = 1; $i <= $aCount; $i++)
									{
										$saveRefs .= $i . ':' . (isset($tplRefs[$i]) ? $tplRefs[$i] : '0') . ',';
									}
								}
								?>
								<input type="hidden" name="fields[TPL_REF]" value="<?= $saveRefs ?>" id="layout-tplrefs"/>
								<input type="checkbox" class="ui-checkbox" id="layout-tplrefs-check"<?if ($tplUsed){?> checked="checked"<?}?> />
								<div class="ui-checkbox-hidden-input-inner landing-form-page-layout">
									<label class="ui-checkbox-label" for="layout-tplrefs-check" id="layout-tplrefs-label"><?= Loc::getMessage('LANDING_TPL_LAYOUT_USE') ?></label>
									<div class="landing-form-wrapper">
										<div class="landing-form-layout-select">
											<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
												<input class="layout-switcher" data-layout="<?= $tpl['XML_ID'] ?>" <?php
												?>type="radio" <?php
												?>name="fields[TPL_ID]" <?php
												?>value="<?= $tpl['ID'] ?>" <?php
												?>id="layout-radio-<?= $i + 1 ?>"<?php
												?><?if ($tpl['ID'] == $row['TPL_ID']['CURRENT']){?> checked="checked"<?}?>>
											<?php endforeach;?>
											<div class="landing-form-list">
												<div class="landing-form-list-container">
													<div class="landing-form-list-inner">
														<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
															<label class="landing-form-layout-item landing-form-layout-item-<?= $tpl['XML_ID'] ?>" <?php
																?>data-block="<?= $tpl['AREA_COUNT'] ?>" <?php
																?>data-layout="<?= $tpl['XML_ID'] ?>" <?php
																?>for="layout-radio-<?= $i + 1 ?>">
																<div class="landing-form-layout-item-img"></div>
															</label>
														<?php endforeach;?>
													</div>
												</div>
												<div class="landing-form-select-buttons">
													<div class="landing-form-select-prev"></div>
													<div class="landing-form-select-next"></div>
												</div>
											</div>
										</div>
										<div class="landing-form-layout-detail">
											<div class="landing-form-layout-img-container">
												<?php foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
													<div class="landing-form-layout-img landing-form-layout-img-<?= $tpl['XML_ID'] ?>" data-layout="<?= $tpl['XML_ID'] ?>"></div>
												<?php endforeach;?>
											</div>
											<div class="landing-form-layout-block-container"></div>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
				<?php endif;?>
				<?php if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="metrika">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA') ?></td>
					<td class="ui-form-right-cell ui-form-right-cell-metrika">
						<?php
						if (isset($hooks['GACOUNTER']))
						{
							$pageFields = $hooks['GACOUNTER']->getPageFields();
							if (!$pageFields['GACOUNTER_CLICK_TYPE']->getValue())
							{
								$pageFields['GACOUNTER_CLICK_TYPE']->setValue('text');
							}
						}
						$template->showSimple('GACOUNTER');
						$template->showSimple('GTM');
						if (Manager::availableOnlyForZone('ru'))
						{
							$template->showSimple('YACOUNTER');
						}
						?>
					</td>
					<script type="text/javascript">
						BX.ready(function()
						{
							new BX.Landing.ExternalMetrika();
						});
					</script>
				</tr>
				<?php endif;?>
				<?php if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])):?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="pixel">
						<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_PIXEL') ?></td>
						<td class="ui-form-right-cell ui-form-right-cell-pixel">
							<?php
							$zone = '';
							if (Loader::includeModule('bitrix24'))
							{
								$zone = \CBitrix24::getPortalZone();
							}
							elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ru")
								&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ua"))
							{
								$zone = 'ru';
							}
							if ($zone !== 'ru')
							{
								$template->showSimple('PIXELFB');
							}
							if (Manager::availableOnlyForZone('ru'))
							{
								$template->showSimple('PIXELVK');
							}
							?>
						</td>
					</tr>
				<?php endif;?>
				<?php if (isset($hooks['METAROBOTS'])):
					$pageFields = $hooks['METAROBOTS']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="index">
					<td class="ui-form-label"><?= $hooks['METAROBOTS']->getTitle() ?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<span class="ui-checkbox-block">
							<?php
							if (isset($pageFields['METAROBOTS_INDEX']))
							{
								if (!$pageFields['METAROBOTS_INDEX']->getValue())
								{
									$pageFields['METAROBOTS_INDEX']->setValue('Y');
								}
								echo $pageFields['METAROBOTS_INDEX']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-metarobots',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
								?>
								<label for="checkbox-metarobots" class="ui-checkbox-label">
									<?= $pageFields['METAROBOTS_INDEX']->getLabel() ?>
								</label>
								<?php
							}
							?>
						</span>
					</td>
				</tr>
				<?php endif;?>
				<?php if (isset($hooks['HEADBLOCK'])):
					$pageFields = $hooks['HEADBLOCK']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="html">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['HEADBLOCK']->getTitle() ?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input landing-form-custom-html">
							<?php
							if (isset($pageFields['HEADBLOCK_USE']))
							{
								$pageFields['HEADBLOCK_USE']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-headblock-use',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
							}
							?>
							<div class="ui-checkbox-hidden-input-inner">
								<?php if (isset($pageFields['HEADBLOCK_USE'])):?>
									<label class="ui-checkbox-label" for="checkbox-headblock-use">
										<?= $pageFields['HEADBLOCK_USE']->getLabel() ?>
									</label>
									<?php
									if ($hooks['HEADBLOCK']->isLocked())
									{
										echo Restriction\Manager::getLockIcon(
											Restriction\Hook::getRestrictionCodeByHookCode('HEADBLOCK'),
											($pageFields['HEADBLOCK_USE'] == 'Y')
											? ['textarea-headblock-code']
											: ['checkbox-headblock-use']
										);
									}
									?>
								<?php endif;?>
								<?php if (isset($pageFields['HEADBLOCK_CODE'])):?>
								<div class="ui-control-wrap">
									<div class="ui-form-control-label">
										<div class="ui-form-control-label-title"><?= $pageFields['HEADBLOCK_CODE']->getLabel() ?></div>
										<div><?= $pageFields['HEADBLOCK_CODE']->getHelpValue() ?></div>
									</div>
									<?php
									$pageFields['HEADBLOCK_CODE']->viewForm(array(
										'id' => 'textarea-headblock-code',
										'class' => 'ui-textarea',
										'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
									));
									?>
								</div>
								<?php endif;?>
							</div>
						</div>
					</td>
				</tr>
				<?php endif;?>
				<?php if (isset($hooks['CSSBLOCK'])):
					$pageFields = $hooks['CSSBLOCK']->getPageFields();
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="css">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['CSSBLOCK']->getTitle() ?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-custom-css">
								<?php
								if (isset($pageFields['CSSBLOCK_USE']))
								{
									$pageFields['CSSBLOCK_USE']->viewForm(array(
										'class' => 'ui-checkbox',
										'id' => 'checkbox-headblock-css',
										'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
									));
								}
								?>
								<div class="ui-checkbox-hidden-input-inner">
									<?php if (isset($pageFields['CSSBLOCK_USE'])):?>
										<label class="ui-checkbox-label" for="checkbox-headblock-css">
											<?= $pageFields['CSSBLOCK_USE']->getLabel() ?>
										</label>
									<?php endif;?>
									<?php if (isset($pageFields['CSSBLOCK_CODE'])):?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label">
												<div class="ui-form-control-label-title"><?= $pageFields['CSSBLOCK_CODE']->getLabel() ?></div>
												<div><?= $pageFields['CSSBLOCK_CODE']->getHelpValue() ?></div>
											</div>
											<?php
											$pageFields['CSSBLOCK_CODE']->viewForm(array(
												'class' => 'ui-textarea',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											));
											?>
										</div>
									<?php endif;?>
								</div>
							</div>
						</td>
					</tr>
				<?php endif;?>
				<?php if (!$isIntranet && !$formEditor && !$isSMN):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="sitemap">
					<td class="ui-form-label"><?= $row['SITEMAP']['TITLE']?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<span class="ui-checkbox-block">
							<input type="hidden" name="fields[SITEMAP]" value="N">
							<input type="checkbox" id="checkbox-sitemap" class="ui-checkbox" name="fields[SITEMAP]" value="Y"<?if ($row['SITEMAP']['CURRENT'] == 'Y'){?> checked="checked"<?}?> />
							<label for="checkbox-sitemap" class="ui-checkbox-label">
								<?= Loc::getMessage('LANDING_TPL_ACTION_ADD_IN_SITEMAP') ?>
							</label>
						</span>
					</td>
				</tr>
				<?php endif;?>
			</table>
		</div>
	</div>

	<div class="<?php if ($request->get('IFRAME') == 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
		<div class="landing-form-footer-container">
			<button id="landing-save-btn" type="submit" class="ui-btn ui-btn-success"  name="submit"  value="<?= Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD')) ?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['LANDING_ID'] ? 'SAVE' : 'ADD'))?>
			</button>
			<a class="ui-btn ui-btn-md ui-btn-link"<?if ($request->get('IFRAME') == 'Y'){?> id="action-close" href="#"<?} else {?> href="<?= $arParams['PAGE_URL_LANDINGS']?>"<?}?>>
				<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL')?>
			</a>
		</div>
	</div>
</form>

<script type="text/javascript">
	BX.ready(function()
	{
		<?if ($arResult['TEMPLATES']):?>
		new BX.Landing.Layout({
			siteId: '<?= $row['SITE_ID']['CURRENT'] ?>',
			landingId: '<?= $row['ID']['CURRENT'] ?>',
			type: '<?= $siteCurrent ? $siteCurrent['TYPE'] : 'PAGE' ?>',
			messages: {
				area: '<?= CUtil::jsEscape(Loc::getMessage('LANDING_TPL_LAYOUT_AREA')) ?>'
			}
			<?php if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']])):?>
			,areasCount: <?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'] ?>
			,current: '<?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['XML_ID'] ?>'
			<?php else:?>
			,areasCount: 0
			,current: 'empty'
			<?php endif;?>
		});
		<?php endif;?>
		new BX.Landing.EditTitleForm(BX('ui-editable-title'), 600, true);
		new BX.Landing.ToggleFormFields(BX('landing-page-set-form'));
		new BX.Landing.SaveBtn(BX('landing-save-btn'));
	});
</script>