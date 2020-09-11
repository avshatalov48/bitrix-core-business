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

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Restriction;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

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
$row = $arResult['SITE'];
$hooks = $arResult['HOOKS'];
$tplRefs = $arResult['TEMPLATES_REF'];
$isIntranet = $arResult['IS_INTRANET'];
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$isSMN = $row['TYPE']['CURRENT'] == 'SMN';
$domain = isset($arResult['DOMAINS'][$row['DOMAIN_ID']['CURRENT']])
		? $arResult['DOMAINS'][$row['DOMAIN_ID']['CURRENT']]
		: [];

// title
if ($arParams['SITE_ID'])
{
	Manager::setPageTitle($component->getMessageType('LANDING_TPL_TITLE_EDIT'));
}
else
{
	Manager::setPageTitle($component->getMessageType('LANDING_TPL_TITLE_ADD'));
}

// assets
\CJSCore::init([
	'color_picker', 'landing_master', 'action_dialog',
	'access', 'sidepanel'
]);
\Bitrix\Main\UI\Extension::load('ui.buttons');
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/landing-forms.js');

$this->getComponent()->initAPIKeys();

// view-functions
include 'template_class.php';
$template = new Template($arResult);

// some url
$uriSave = new \Bitrix\Main\Web\Uri(\htmlspecialcharsback(POST_FORM_ACTION_URI));
$uriSave->addParams(array(
	'action' => 'save'
));
$uriDomain = new \Bitrix\Main\Web\Uri(
	str_replace('#site_edit#', $row['ID']['CURRENT'], $arParams['PAGE_URL_SITE_DOMAIN'])
);
$uriDomain->addParams(array(
	'tab' => '__tab__',
	'IFRAME' => 'Y'
));
$uriCookies = new \Bitrix\Main\Web\Uri(
	str_replace('#site_edit#', $row['ID']['CURRENT'], $arParams['PAGE_URL_SITE_COOKIES'])
);
$uriCookies->addParams([
	'IFRAME' => 'Y'
]);

// access selector
if ($arResult['SHOW_RIGHTS'])
{
	$tasksStr = '<select name="fields[RIGHTS][TASK_ID][#inc#][]" multiple="multiple" size="7" class="ui-select">';
	foreach ($arResult['ACCESS_TASKS'] as $task)
	{
		$tasksStr .= '<option value="' . $task['ID'] . '">' .
					 \htmlspecialcharsbx('['.$task['ID'].'] '.$task['TITLE']) .
					 '</option>';
	}
	$tasksStr .= '</select>';
	$accessCodes = [];
}
?>
<script type="text/javascript">
	BX.ready(function(){
		var editComponent = new BX.Landing.EditComponent();
		top.window['landingSettingsSaved'] = false;
		<?if ($arParams['SUCCESS_SAVE']):?>
		top.window['landingSettingsSaved'] = true;
		top.BX.onCustomEvent('BX.Landing.Filter:apply');
		editComponent.actionClose();
		top.BX.Landing.UI.Tool.ActionDialog.getInstance().close();
		<?endif;?>
		BX.Landing.Env.createInstance({
			params: {type: '<?= $arParams['TYPE'];?>'}
		});
	});
</script>

<?
if ($arParams['SUCCESS_SAVE'])
{
	if ($request->get('IFRAME') != 'Y')
	{
		$this->getComponent()->refresh([], ['action']);
	}
	return;
}
?>

<form method="post" action="/bitrix/tools/landing/ajax.php?action=Site::uploadFile" enctype="multipart/form-data" id="landing-form-favicon-form">
	<?= bitrix_sessid_post();?>
	<input type="hidden" name="data[id]" value="<?= $arParams['SITE_ID'];?>" />
	<input type="file" name="picture" id="landing-form-favicon-input" style="display: none;" />
</form>

<form action="<?= \htmlspecialcharsbx($uriSave->getUri());?>" method="post" class="ui-form ui-form-gray-padding landing-form-collapsed landing-form-settings" id="landing-site-set-form">
	<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
	<input type="hidden" name="fields[TYPE]" value="<?= $row['TYPE']['CURRENT'];?>" />
	<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'];?>" />
	<?= bitrix_sessid_post();?>

	<div class="ui-form-title-block">
		<span class="ui-editable-field" id="ui-editable-title">
			<label class="ui-editable-field-label ui-editable-field-label-js"><?= $row['TITLE']['CURRENT'];?></label>
			<input type="text" name="fields[TITLE]" class="ui-input ui-editable-field-input ui-editable-field-input-js" value="<?= $row['TITLE']['CURRENT'];?>" placeholder="<?= $row['TITLE']['TITLE'];?>" />
			<span class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen"></span>
		</span>
	</div>

	<div class="landing-form-inner-js landing-form-inner">
		<div class="landing-form-table-wrap landing-form-table-wrap-js ui-form-inner">
			<table class="ui-form-table landing-form-table">
				<?if ($isIntranet):?>
				<tr class="landing-form-site-name-fieldset">
					<td class="ui-form-label ui-form-label-align-top">
						<?= $component->getMessageType('LANDING_TPL_TITLE_ADDRESS_SITE');?>
					</td>
					<td class="ui-form-right-cell">
						<span class="landing-form-site-name-label">
							<?= \Bitrix\Landing\Domain::getHostUrl();?><?= Manager::getPublicationPath();?>
						</span>
						<input type="text" name="fields[CODE]" class="ui-input" value="<?= trim($row['CODE']['CURRENT'], '/');?>" placeholder="<?= $row['TITLE']['TITLE'];?>" />
						<span class="landing-form-site-name-label">/</span>
					</td>
				</tr>
				<?elseif ($domain):?>
				<tr class="landing-form-site-name-fieldset">
					<td class="ui-form-label ui-form-label-align-top"><?= $row['CODE']['TITLE']?></td>
					<td class="ui-form-right-cell">
						<div class="landing-domain">
							<?if (Manager::isB24()):
								$puny = new \CBXPunycode;
								?>
								<span class="landing-domain-name">
									<span class="landing-domain-name-value"><?= $puny->decode($domain['DOMAIN']);?></span>
									<a href="<?= str_replace('__tab__', '', $uriDomain->getUri());?>" class="ui-title-input-btn ui-editing-pen landing-frame-btn"></a>
								</span>
								<?if (!\Bitrix\Landing\Domain::getBitrix24Subdomain($domain['DOMAIN'])):?>
									<?if (\Bitrix\Landing\Domain\Register::isDomainActive($domain['DOMAIN'])):?>
										<div class="landing-domain-status landing-domain-status-active">
											<div class="landing-domain-status-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_ACTIVATION_YES');?></div>
										</div>
									<?else:?>
										<div class="landing-domain-status landing-domain-status-wait">
											<div class="landing-domain-status-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_ACTIVATION_NO');?></div>
											<div class="landing-domain-status-notice"><?= Loc::getMessage('LANDING_TPL_DOMAIN_ACTIVATION_INFO');?></div>
										</div>
									<?endif;?>
								<?elseif ($arResult['REGISTER']->enable()):?>
									<div class="landing-domain-status landing-domain-status-configure">
										<div class="landing-domain-status-title"><?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_TEXT');?></div>
										<a href="<?= str_replace('__tab__', 'provider', $uriDomain->getUri());?>" class="ui-btn ui-btn-primary ui-btn-sm ui-btn-round landing-frame-btn">
											<?= Loc::getMessage('LANDING_TPL_DOMAIN_FREE_BUTTON');?>
										</a>
										<a href="<?= str_replace('__tab__', 'private', $uriDomain->getUri());?>" class="ui-btn ui-btn-light-border ui-btn-sm ui-btn-round landing-frame-btn">
											<?= Loc::getMessage('LANDING_TPL_DOMAIN_PRIVATE_BUTTON');?>
										</a>
									</div>
								<?else:?>
									<div>
										<a href="<?= str_replace('__tab__', 'private', $uriDomain->getUri());?>" class="ui-btn ui-btn-light-border ui-btn-sm ui-btn-round landing-frame-btn">
											<?= Loc::getMessage('LANDING_TPL_DOMAIN_PRIVATE_BUTTON');?>
										</a>
									</div>
									<script>
										BX.ready(function() {
											var domainBlock = document.querySelector('.landing-domain');
											domainBlock.classList.add('landing-domain-own');
										});
									</script>
								<?endif;?>
							<?else:?>
								<select name="fields[DOMAIN_ID]" class="ui-select">
									<?foreach ($arResult['DOMAINS'] as $item):?>
										<option value="<?= $item['ID']?>"<?if ($item['ID'] == $row['DOMAIN_ID']['CURRENT']){?> selected="selected"<?}?>>
											<?= \htmlspecialcharsbx($item['DOMAIN']);?>
										</option>
									<?endforeach;?>
								</select>
							<?endif;?>
						</div>
					</td>
				</tr>
				<?endif;?>
			<?if (isset($hooks['B24BUTTON'])):
				$pageFields = $hooks['B24BUTTON']->getPageFields();
				if (isset($pageFields['B24BUTTON_CODE'])):
				?>
				<tr>
					<td class="ui-form-label"><?= $pageFields['B24BUTTON_CODE']->getLabel();?></td>
					<td class="ui-form-right-cell">
						<div class="landing-form-flex-box">
							<?
							$pageFields['B24BUTTON_CODE']->viewForm(array(
								'class' => 'ui-select',
								'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
							));
							?>
							<?if (ModuleManager::isModuleInstalled('crm')):?>
							<a href="/crm/button/" class="landing-form-input-right" target="_blank">
								<?= Loc::getMessage('LANDING_TPL_ACTION_SETTINGS');?>
							</a>
							<?elseif (ModuleManager::isModuleInstalled('b24connector')):?>
							<a href="/bitrix/admin/b24connector_b24connector.php?lang=<?= LANGUAGE_ID;?>" class="landing-form-input-right" target="_blank">
								<?= Loc::getMessage('LANDING_TPL_ACTION_SETTINGS');?>
							</a>
							<?else:?>
							<a href="/bitrix/admin/module_admin.php?lang=<?= LANGUAGE_ID;?>" class="landing-form-input-right" target="_blank">
								<?= Loc::getMessage('LANDING_TPL_ACTION_INSTALL_B24');?>
							</a>
							<?endif;?>
						</div>
					</td>
				</tr>
				<tr data-landing-main-option="b24widget">
					<td class="ui-form-label"><?= $pageFields['B24BUTTON_COLOR']->getLabel();?></td>
					<td class="ui-form-right-cell">
						<div class="landing-form-flex-box">
							<?
							$pageFields['B24BUTTON_COLOR']->viewForm(array(
								'class' => 'ui-select',
								'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
							));
							?>
						</div>
					</td>
				</tr>
				<?
				endif;
			endif;?>
			<?if (isset($hooks['THEME'])):
				$pageFields = $hooks['THEME']->getPageFields();
				if (isset($pageFields['THEME_CODE'])): ?>
					<tr data-landing-main-option="theme">
						<td class="ui-form-label"><?= $pageFields['THEME_CODE']->getLabel();?></td>
						<td class="ui-form-right-cell">
							<div class="landing-form-flex-box">
								<?
								$selectParams = array();
								$selectParams['id'] = \randString(5);
								$selectParams['value'] = $pageFields['THEME_CODE']->getValue();
								$selectParams['options'] = $pageFields['THEME_CODE']->getOptions();
								// to site not need DEFAULT option
								unset($selectParams['options']['']);
								// if empty - set last element (in SetTheme will be applied last too)
								if (!$hooks['THEME']->enabled()) {
									$lastValue = array_keys($selectParams['options']);
									$selectParams['value'] = end($lastValue);
								}
								?>
								<input
									id="<?=$selectParams['id'];?>_select_color"
									type="hidden"
									name="<?=$pageFields['THEME_CODE']->getName('fields[ADDITIONAL_FIELDS][#field_code#]');?>"
									value="<?= \htmlspecialcharsbx($selectParams['value']);?>"
								/>
								<div class="ui-select select-color-wrap"
									 id="<?=$selectParams['id'];?>_select_color_wrap">
								</div>
								<script>
									var SelectColor = new BX.Landing.SelectColor(<?=\CUtil::PhpToJSObject($selectParams);?>);
									SelectColor.show();
								</script>
							</div>
						</td>
					</tr>
				<? endif; ?>
			<? endif;?>

			<?php if (isset($hooks['THEMEFONTS'])):?>
				<tr data-landing-main-option="themefonts">
					<td class="ui-form-label ui-form-label-align-top"><?=$component->getMessageType('LANDING_TPL_FONTS')?></td>
					<td class="ui-form-right-cell ui-form-right-cell-fonts">
						<?$template->showMultiply('THEMEFONTS', true);?>
					</td>
				</tr>
			<?php endif;?>

			<?if (isset($hooks['UP'])):
				$pageFields = $hooks['UP']->getPageFields();
				if (isset($pageFields['UP_SHOW'])):
				?>
				<tr>
					<td class="ui-form-label"><?= $pageFields['UP_SHOW']->getLabel();?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
							<span class="ui-checkbox-block">
								<?
								echo $pageFields['UP_SHOW']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-up',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
								?>
								<label for="checkbox-up" class="ui-checkbox-label"><?= Loc::getMessage('LANDING_TPL_ACTION_SHOW');?></label>
							</span>
					</td>
				</tr>
				<?
				endif;
			endif;?>
				<tr>
					<td class="ui-form-label"><?= Loc::getMessage('LANDING_TPL_PAGE_INDEX')?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<select name="fields[LANDING_ID_INDEX]" class="ui-select">
							<?foreach ($arResult['LANDINGS'] as $item):
								if ($item['IS_AREA'])
								{
									continue;
								}
								?>
							<option value="<?= $item['ID']?>"<?if ($item['ID'] == $row['LANDING_ID_INDEX']['CURRENT']){?> selected="selected"<?}?>>
								<?= \htmlspecialcharsbx($item['TITLE'])?>
							</option>
							<?endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="ui-form-right-cell ui-form-collapse" colspan="2">
						<div class="ui-form-collapse-block landing-form-collapse-block-js">
							<span class="ui-form-collapse-label"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL');?></span>
							<span class="landing-additional-alt-promo-wrap" id="landing-additional">
								<?if (isset($hooks['FAVICON'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="favicon">Favicon</span>
								<?endif;?>
								<?if (isset($hooks['BACKGROUND'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="background"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_BG');?></span>
								<?endif;?>
								<?if (isset($hooks['METAGOOGLEVERIFICATION']) || isset($hooks['METAYANDEXVERIFICATION'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="verification"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_VERIFICATION');?></span>
								<?endif;?>
								<?if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="metrika"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_METRIKA');?></span>
								<?endif;?>
								<?if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="pixel"><?= Loc::getMessage('LANDING_TPL_HOOK_PIXEL');?></span>
								<?endif;?>
								<?if (isset($hooks['GMAP'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="map_required_key"><?= Loc::getMessage('LANDING_TPL_HOOK_GMAP');?></span>
								<?endif;?>
								<?if (isset($hooks['VIEW'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="view"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_VIEW');?></span>
								<?endif;?>
								<?if ($arResult['TEMPLATES']):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="layout"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_LAYOUT');?></span>
								<?endif;?>
								<?if (!$isIntranet && !empty($arResult['LANG_CODES']) && $row['LANG']):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="lang"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_LANG');?></span>
								<?endif;?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="404"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_404');?></span>
								<?if (isset($hooks['ROBOTS']) && !$isSMN):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="robots"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_ROBOTS');?></span>
								<?endif;?>
								<?if (isset($hooks['SPEED'])):?>
								<span class="landing-additional-alt-promo-text" data-landing-additional-option="speed"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_SPEED');?></span>
								<?endif;?>
								<?if (isset($hooks['HEADBLOCK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="public_html_disallowed">HTML</span>
								<?endif;?>
								<?if (isset($hooks['CSSBLOCK'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="css">CSS</span>
								<?endif;?>
								<?if (!$isIntranet):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="off"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_OFF');?></span>
								<?endif;?>
								<?if (isset($hooks['COOKIES'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="cookies">Cookies</span>
								<?endif;?>
								<?if (isset($hooks['COPYRIGHT'])):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="sign"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_SIGN');?></span>
								<?endif;?>
								<?if ($arResult['SHOW_RIGHTS']):?>
									<span class="landing-additional-alt-promo-text" data-landing-additional-option="access"><?= Loc::getMessage('LANDING_TPL_HOOK_RIGHTS_LABEL');?></span>
								<?endif;?>
							</span>
						</div>
					</td>
				</tr>
				<?if (isset($hooks['FAVICON'])):
					$pageFields = $hooks['FAVICON']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="favicon">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['FAVICON']->getTitle();?></td>
					<td class="ui-form-right-cell ui-form-right-cell-favicon">
						<div class="landing-form-favicon-wrap">
							<?$favId = (int) $pageFields['FAVICON_PICTURE']->getValue();?>
							<img src="<?= $favId > 0 ? \Bitrix\Landing\File::getFilePath($favId) : '/bitrix/images/1.gif';?>" alt="" width="32" id="landing-form-favicon-src" />
						</div>
						<input type="hidden" name="fields[ADDITIONAL_FIELDS][FAVICON_PICTURE]" id="landing-form-favicon-value" value="<?= $favId;?>" />
						<a href="#" id="landing-form-favicon-change">
							<?= Loc::getMessage('LANDING_TPL_HOOK_FAVICON_EDIT');?>
						</a>
						&nbsp;
						<span id="landing-form-favicon-error">(*.png)</span>
					</td>
				</tr>
				<?endif;?>
				
				<?if (isset($hooks['BACKGROUND'])):
					$pageFields = $hooks['BACKGROUND']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="background">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['BACKGROUND']->getTitle();?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input landing-form-page-background">
							<?
							if (isset($pageFields['BACKGROUND_USE']))
							{
								$pageFields['BACKGROUND_USE']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-background-use',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
							}
							?>
							<div class="ui-checkbox-hidden-input-inner">
								<?if (isset($pageFields['BACKGROUND_USE'])):?>
								<label class="ui-checkbox-label" for="checkbox-background-use">
									<?= Loc::getMessage('LANDING_TPL_HOOK_BACKGROUND_USE');?>
								</label>
								<?endif;?>
								<div class="landing-form-wrapper">
									<?
									if (isset($pageFields['BACKGROUND_PICTURE']))
									{
										$template->showPictureJS(
											$pageFields['BACKGROUND_PICTURE'],
											'',
											array(
												'imgId' => 'landing-form-background-field',
												'width' => 2000,
												'height' => 2000,
												'uploadParams' =>
													$row['ID']['CURRENT']
													? array(
														'action' => 'Site::uploadFile',
														'id' => $row['ID']['CURRENT']
													)
													: array(
															//
													)
											)
										);
										?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label"><?= $pageFields['BACKGROUND_PICTURE']->getLabel();?></div>
											<div id="landing-form-background-field" class="landing-background-field"></div>
										</div>
										<?
									}
									?>
									<?if (isset($pageFields['BACKGROUND_POSITION'])):?>
									<div class="ui-control-wrap">
										<div class="ui-form-control-label"><?= $pageFields['BACKGROUND_POSITION']->getLabel();?></div>
										<?
										$pageFields['BACKGROUND_POSITION']->viewForm(array(
											'class' => 'ui-select',
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
									</div>
									<?endif;?>
									<?if (isset($pageFields['BACKGROUND_COLOR'])):
										$value = \htmlspecialcharsbx(trim($pageFields['BACKGROUND_COLOR']->getValue()));
										?>
									<script type="text/javascript">
										BX.ready(function() {
											new BX.Landing.ColorPicker(BX('landing-form-colorpicker'));
										});
									</script>
									<div class="ui-control-wrap">
										<div class="ui-form-control-label"><?= $pageFields['BACKGROUND_COLOR']->getLabel();?></div>
										<div class="ui-colorpicker<?if ($value){?> ui-colorpicker-selected<?}?>" id="landing-form-colorpicker" >
											<span class="ui-colorpicker-color ui-colorpicker-color-js"<?if ($value){?> style="background-color: <?= $value?>;"<?}?>></span>
											<?
											$pageFields['BACKGROUND_COLOR']->viewForm(array(
												'additional' => 'readonly',
												'class' => 'ui-input ui-input-color landing-colorpicker-inp-js',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											));
											?>
											<span class="ui-colorpicker-clear ui-colorpicker-clear"></span>
										</div>
									</div>
									<?endif;?>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['METAGOOGLEVERIFICATION']) || isset($hooks['METAYANDEXVERIFICATION'])):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="verification">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_VERIFICATION');?></td>
					<td class="ui-form-right-cell ui-form-right-cell-verification">
						<?$template->showSimple('METAGOOGLEVERIFICATION');?>
						<?
						if (Manager::availableOnlyForZone('ru'))
						{
							$template->showSimple('METAYANDEXVERIFICATION');
						}
						?>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="metrika">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA');?></td>
					<td class="ui-form-right-cell ui-form-right-cell-metrika">
						<?
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
				</tr>
				<?endif;?>
				<?if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])):?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="pixel">
						<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_PIXEL');?></td>
						<td class="ui-form-right-cell ui-form-right-cell-pixel">
							<?$template->showSimple('PIXELFB');?>
							<?
							if (Manager::availableOnlyForZone('ru'))
							{
								$template->showSimple('PIXELVK');
							}
							?>
						</td>
					</tr>
				<?endif;?>
				<?if (isset($hooks['GMAP'])):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="map_required_key">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_GMAP');?></td>
					<td class="ui-form-right-cell ui-form-right-cell-map">
						<?$template->showSimple('GMAP');?>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['VIEW'])):
					$pageFields = $hooks['VIEW']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="view">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['VIEW']->getTitle();?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input landing-form-type-page-block">
							<?
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
								<?if (isset($pageFields['VIEW_USE'])):?>
								<label class="ui-checkbox-label" for="checkbox-view-use">
									<?= Loc::getMessage('LANDING_TPL_HOOK_VIEW_USE');?>
								</label>
								<?endif;?>
								<?if (isset($pageFields['VIEW_TYPE'])):
									$value = $pageFields['VIEW_TYPE']->getValue();
									$items = $hooks['VIEW']->getItems();
									if (!$value)
									{
										$itemsKeys = array_keys($items);
										$value = array_shift($itemsKeys);
									}
									?>
								<div class="landing-form-type-page-wrap">
									<?foreach ($items as $key => $title):?>
									<span class="landing-form-type-page landing-form-type-<?= $key?>">
										<input type="radio" <?
											?>name="fields[ADDITIONAL_FIELDS][VIEW_TYPE]" <?
											?>class="ui-radio" <?
											?>id="view-type-<?= $key?>" <?
											?><?if ($value == $key){?> checked="checked"<?}?> <?
											?>value="<?= $key;?>" />
										<label for="view-type-<?= $key?>">
											<span class="landing-form-type-page-img"></span>
											<span class="landing-form-type-page-title"><?= $title?></span>
										</label>
									</span>
									<?endforeach;?>
								</div>
								<?endif;?>
							</div>
						</div>
					</td>
				</tr>
				<?endif;?>
				<?if ($arResult['TEMPLATES']):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="layout">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_LAYOUT');?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input ui-checkbox-hidden-input-layout">
							<?
							$saveRefs = '';
							if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]))
							{
								$aCount = $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'];
								for ($i = 1; $i <= $aCount; $i++)
								{
									$saveRefs .= $i . ':' . (isset($tplRefs[$i]) ? $tplRefs[$i] : '0') . ',';
								}
							}
							?>
							<input type="hidden" name="fields[TPL_REF]" value="<?= $saveRefs;?>" id="layout-tplrefs"/>
							<input type="checkbox" class="ui-checkbox" id="layout-tplrefs-check" style="display: none;" checked="checked" />
							<div class="ui-checkbox-hidden-input-inner landing-form-page-layout">
								<div class="landing-form-wrapper">
									<div class="landing-form-layout-select">
										<?foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
										<input class="layout-switcher" data-layout="<?= $tpl['XML_ID'];?>" <?
											?>type="radio" <?
											?>name="fields[TPL_ID]" <?
											?>value="<?= $tpl['ID'];?>" <?
											?>id="layout-radio-<?= $i + 1;?>"<?
											?><?if ($tpl['ID'] == $row['TPL_ID']['CURRENT']){?> checked="checked"<?}?>>
										<?endforeach;?>
										<div class="landing-form-list">
											<div class="landing-form-list-container">
												<div class="landing-form-list-inner">
													<?foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
														<label class="landing-form-layout-item <?
														?><?= (!$row['TPL_ID']['CURRENT'] && $tpl['XML_ID'] == 'empty') ? 'landing-form-layout-item-selected ' : ''?><?
														?>landing-form-layout-item-<?= $tpl['XML_ID'];?>" <?
															   ?>data-block="<?= $tpl['AREA_COUNT'];?>" <?
															   ?>data-layout="<?= $tpl['XML_ID'];?>" <?
															   ?>for="layout-radio-<?= $i + 1;?>">
															<div class="landing-form-layout-item-img"></div>
														</label>
													<?endforeach;?>
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
											<?foreach (array_values($arResult['TEMPLATES']) as $i => $tpl):?>
											<div class="landing-form-layout-img landing-form-layout-img-<?= $tpl['XML_ID'];?>" data-layout="<?= $tpl['XML_ID'];?>"></div>
											<?endforeach;?>
										</div>
										<div class="landing-form-layout-block-container"></div>
									</div>
								</div>
							</div>
						<div class="ui-checkbox-hidden-input">
					</td>
				</tr>
				<?endif;?>
				<?if (!$isIntranet && !empty($arResult['LANG_CODES']) && $row['LANG']):?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="lang">
						<td class="ui-form-label"><?= $row['LANG']['TITLE'];?></td>
						<td class="ui-form-right-cell">
							<div class="landing-form-flex-box">
								<?
								$selectParams = array();
								$selectParams['id'] = \randString(5);
								$selectParams['value'] = $row['LANG']['CURRENT'];
								$selectParams['options'] = $arResult['LANG_CODES'];
								if (!$selectParams['value']) {
									$selectParams['value'] = LANGUAGE_ID;
								}
								?>
								<input
									id="<?= $selectParams['id'];?>_select_lang"
									type="hidden"
									name="fields[LANG]"
									value="<?= \htmlspecialcharsbx($selectParams['value']);?>"
								/>
								<div class="ui-select select-lang-wrap"
									 id="<?= $selectParams['id'];?>_select_lang_wrap">
								</div>
								<script>
									var SelectLang = new BX.Landing.SelectLang(<?=\CUtil::PhpToJSObject($selectParams);?>);
									SelectLang.show();
								</script>
							</div>
						</td>
					</tr>
				<?endif;?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="404">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_PAGE_404')?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input">
							<input type="checkbox" class="ui-checkbox" id="checkbox-404-use"<?
							?> <?if ($row['LANDING_ID_404']['CURRENT']){?> checked="checked"<?}?> />
							<div class="ui-checkbox-hidden-input-inner">
								<label class="ui-checkbox-label" for="checkbox-404-use">
									<?= Loc::getMessage('LANDING_TPL_PAGE_404_USE');?>
								</label>
								<select name="fields[LANDING_ID_404]" class="ui-select" id="landing-form-404-select">
									<option></option>
									<?foreach ($arResult['LANDINGS'] as $item):
										if ($item['IS_AREA'])
										{
											continue;
										}
										?>
									<option value="<?= $item['ID']?>"<?if ($item['ID'] == $row['LANDING_ID_404']['CURRENT']){?> selected="selected"<?}?>>
										<?= \htmlspecialcharsbx($item['TITLE'])?>
									</option>
									<?endforeach;?>
								</select>
							</div>
						</div>
					</td>
				</tr>
				<?if (isset($hooks['ROBOTS']) && !$isSMN):
					$pageFields = $hooks['ROBOTS']->getPageFields();
					?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="robots">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['ROBOTS']->getTitle();?></td>
					<td class="ui-form-right-cell">
						<div class="ui-checkbox-hidden-input landing-form-textarea-block">
							<?
							if (isset($pageFields['ROBOTS_USE']))
							{
								$pageFields['ROBOTS_USE']->viewForm(array(
									'class' => 'ui-checkbox',
									'id' => 'checkbox-robots-use',
									'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
								));
							}
							?>
							<div class="ui-checkbox-hidden-input-inner">
								<?if (isset($pageFields['ROBOTS_USE'])):?>
								<label class="ui-checkbox-label" for="checkbox-robots-use">
									<?= $pageFields['ROBOTS_USE']->getLabel();?>
								</label>
								<?endif;?>
								<?if (isset($pageFields['ROBOTS_CONTENT'])):?>
								<div class="landing-form-textarea-wrap">
									<?
									$pageFields['ROBOTS_CONTENT']->viewForm(array(
										'class' => 'ui-textarea landing-form-textarea',
										'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
									));
									?>
								</div>
								<?endif;?>
							</div>
						</div>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['SPEED'])):
					$pageFields = $hooks['SPEED']->getPageFields();
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="speed">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['SPEED']->getTitle();?></td>
						<td class="ui-form-right-cell">
							<!--							SPEED-->
							<div class="ui-checkbox-block">
								<?php if (isset($pageFields['SPEED_USE_WEBPACK'])):?>
									<div class="ui-checkbox-hidden-input">
									<?php
										if (!$pageFields['SPEED_USE_WEBPACK']->getValue())
										{
											$pageFields['SPEED_USE_WEBPACK']->setValue('Y');
										}
										echo $pageFields['SPEED_USE_WEBPACK']->viewForm(array(
											'class' => 'ui-checkbox',
											'id' => 'checkbox-speed-'.mb_strtolower('USE_WEBPACK'),
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
									?>
										<div class="ui-checkbox-label-wrapper">
											<label for="checkbox-speed-<?=mb_strtolower('USE_WEBPACK')?>" class="ui-checkbox-label">
												<?=$pageFields['SPEED_USE_WEBPACK']->getLabel();?>
											</label>
										</div>
									</div>
								<?php endif;?>

								<?php if (isset($pageFields['SPEED_USE_LAZY'])):?>
									<div class="ui-checkbox-hidden-input">
										<?php
										// todo: can use foreach(hooks) with webpack
										if (!$pageFields['SPEED_USE_LAZY']->getValue())
										{
											$pageFields['SPEED_USE_LAZY']->setValue('Y');
										}
										echo $pageFields['SPEED_USE_LAZY']->viewForm(array(
											'class' => 'ui-checkbox',
											'id' => 'checkbox-speed-'.strtolower('USE_LAZY'),
											'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
										));
										?>
										<div class="ui-checkbox-label-wrapper">
											<label for="checkbox-speed-<?=strtolower('USE_LAZY')?>" class="ui-checkbox-label">
												<?=$pageFields['SPEED_USE_LAZY']->getLabel();?>
											</label>
										</div>
									</div>
								<?php endif;?>
							</div>
							<div class="landing-form-help-link">
								<?= $pageFields['SPEED_USE_WEBPACK']->getHelpValue();?>
							</div>
						</td>
					</tr>
				<?endif;?>
				<?if (isset($hooks['HEADBLOCK'])):
					$pageFields = $hooks['HEADBLOCK']->getPageFields();
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="public_html_disallowed">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['HEADBLOCK']->getTitle();?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-custom-html">
								<?
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
									<?if (isset($pageFields['HEADBLOCK_USE'])):?>
										<label class="ui-checkbox-label" for="checkbox-headblock-use">
											<?= Loc::getMessage('LANDING_TPL_HOOK_HEADBLOCK_USE');?>
										</label>
										<?
										if ($hooks['HEADBLOCK']->isLocked())
										{
											echo Restriction\Manager::getLockIcon(
												Restriction\Hook::getRestrictionCodeByHookCode('HEADBLOCK'),
												['checkbox-headblock-use']
											);
										}
										?>
									<?endif;?>
									<?if (isset($pageFields['HEADBLOCK_CODE'])):?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label">
												<div class="ui-form-control-label-title"><?= $pageFields['HEADBLOCK_CODE']->getLabel();?></div>
												<div><?= $pageFields['HEADBLOCK_CODE']->getHelpValue();?></div>
											</div>
											<?
											$pageFields['HEADBLOCK_CODE']->viewForm(array(
												'id' => 'textarea-headblock-code',
												'class' => 'ui-textarea',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											));
											?>
										</div>
									<?endif;?>
								</div>
							</div>
						</td>
					</tr>
				<?endif;?>
				<?if (isset($hooks['CSSBLOCK'])):
					$pageFields = $hooks['CSSBLOCK']->getPageFields();
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="css">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['CSSBLOCK']->getTitle();?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-custom-css">
								<?
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
									<?if (isset($pageFields['CSSBLOCK_USE'])):?>
										<label class="ui-checkbox-label" for="checkbox-headblock-css">
											<?= Loc::getMessage('LANDING_TPL_HOOK_HEADBLOCK_USE');?>
										</label>
									<?endif;?>
									<?if (isset($pageFields['CSSBLOCK_CODE'])):?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label">
												<div class="ui-form-control-label-title"><?= $pageFields['CSSBLOCK_CODE']->getLabel();?></div>
												<div><?= $pageFields['CSSBLOCK_CODE']->getHelpValue();?></div>
											</div>
											<?
											$pageFields['CSSBLOCK_CODE']->viewForm(array(
												'class' => 'ui-textarea',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											));
											?>
										</div>
									<?endif;?>
								</div>
							</div>
						</td>
					</tr>
				<?endif;?>
				<?if (!$isIntranet):?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="off">
						<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_PAGE_503')?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input">
								<input type="checkbox" class="ui-checkbox" id="checkbox-503-use"<?
								?> <?if ($row['LANDING_ID_503']['CURRENT']){?> checked="checked"<?}?> />
								<div class="ui-checkbox-hidden-input-inner">
									<label class="ui-checkbox-label" for="checkbox-503-use">
										<?= Loc::getMessage('LANDING_TPL_PAGE_503_USE');?>
									</label>
									<select name="fields[LANDING_ID_503]" class="ui-select" id="landing-form-503-select">
										<option></option>
										<?foreach ($arResult['LANDINGS'] as $item):
											if ($item['IS_AREA'])
											{
												continue;
											}
											?>
											<option value="<?= $item['ID']?>"<?if ($item['ID'] == $row['LANDING_ID_503']['CURRENT']){?> selected="selected"<?}?>>
												<?= \htmlspecialcharsbx($item['TITLE'])?>
											</option>
										<?endforeach;?>
									</select>
								</div>
							</div>
						</td>
					</tr>
				<?endif;?>
				<?if (isset($hooks['COOKIES'])):
					$pageFields = $hooks['COOKIES']->getPageFields();
					$agreementId = isset($pageFields['COOKIES_AGREEMENT_ID'])
									? $pageFields['COOKIES_AGREEMENT_ID']->getValue()
									: 0;
					if (!$agreementId)
					{
						$agreementId = $arResult['COOKIES_AGREEMENT']['ID'];
					}
					?>
					<tr class="landing-form-hidden-row" data-landing-additional-detail="cookies">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['COOKIES']->getTitle();?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-cookies">
								<?
								if (isset($pageFields['COOKIES_USE']))
								{
									$pageFields['COOKIES_USE']->viewForm([
										'class' => 'ui-checkbox',
										'id' => 'checkbox-cookies',
										'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
									]);
								}
								?>
								<div class="ui-checkbox-hidden-input-inner">
									<?if (isset($pageFields['COOKIES_USE'])):?>
										<label class="ui-checkbox-label" for="checkbox-cookies">
											<?= $pageFields['COOKIES_USE']->getLabel();?>
										</label>
										<div class="landing-form-cookies-inner">
											<?if ($arResult['SITE_INCLUDES_SCRIPT']):?>
											<div class="landing-alert-site-includes-script">
												<?= Loc::getMessage('LANDING_TPL_HOOK_COOKIES_SCRIPT_WARN');?>
											</div>
											<?endif;?>
											<?$APPLICATION->IncludeComponent(
												'bitrix:landing.userconsent.selector',
												'',
												array(
													'ID' => $agreementId,
													'INPUT_NAME' => 'fields[ADDITIONAL_FIELDS][COOKIES_AGREEMENT_ID]'
												)
											);?>
										</div>
									<?endif;?>
										<a href="<?= $uriCookies->getUri();?>" class="landing-frame-btn landing-frame-btn-cookies"><?= Loc::getMessage('LANDING_TPL_HOOK_COOKIES_EDIT_DESCRIPTIONS');?></a>
									<div class="landing-form-cookies-title"><?= Loc::getMessage('LANDING_TPL_HOOK_COOKIES_VIEW');?></div>
									<div class="landing-form-cookies-settings">
										<div class="landing-form-cookies-settings-preview">
											<div class="landing-form-cookies-settings-type landing-form-cookies-settings-type-simple">
												<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="#FFF" class="landing-form-cookies-settings-preview-svg">
													<path fill-rule="evenodd" d="M7.328.07c.463 0 .917.043 1.356.125.21.04.3.289.228.49a1.5 1.5 0 001.27 1.99h.001a.22.22 0 01.213.243 3.218 3.218 0 003.837 3.453c.18-.035.365.078.384.26A7.328 7.328 0 117.329.07zm.263 10.054a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854zM3.697 7.792a.884.884 0 100 1.769.884.884 0 000-1.769zm5.476-.488a.884.884 0 100 1.768.884.884 0 000-1.768zM5.806 3.628a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854z"/>
												</svg>
											</div>
											<div class="landing-form-cookies-settings-type landing-form-cookies-settings-type-advanced">
												<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="#FFF" class="landing-form-cookies-settings-preview-svg">
													<path fill-rule="evenodd" d="M7.328.07c.463 0 .917.043 1.356.125.21.04.3.289.228.49a1.5 1.5 0 001.27 1.99h.001a.22.22 0 01.213.243 3.218 3.218 0 003.837 3.453c.18-.035.365.078.384.26A7.328 7.328 0 117.329.07zm.263 10.054a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854zM3.697 7.792a.884.884 0 100 1.769.884.884 0 000-1.769zm5.476-.488a.884.884 0 100 1.768.884.884 0 000-1.768zM5.806 3.628a1.427 1.427 0 100 2.854 1.427 1.427 0 000-2.854z"/>
												</svg>
												<span class="landing-form-cookies-settings-preview-text">Cookies</span>
											</div>
										</div>
										<?if (isset($pageFields['COOKIES_COLOR_BG'])):?>
											<span class="landing-form-cookies-settings-label"><?= $pageFields['COOKIES_COLOR_BG']->getLabel();?></span>
											<?$pageFields['COOKIES_COLOR_BG']->viewForm([
												'class' => 'landing-form-cookies-color landing-form-cookies-color-bg',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											]);?>
										<?endif;?>
										<?if (isset($pageFields['COOKIES_COLOR_TEXT'])):?>
										<span class="landing-form-cookies-settings-label"><?= $pageFields['COOKIES_COLOR_TEXT']->getLabel();?></span>
											<?$pageFields['COOKIES_COLOR_TEXT']->viewForm([
												'class' => 'landing-form-cookies-color landing-form-cookies-color-text',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											]);?>
										<?endif;?>
									</div>
									<div class="landing-form-cookies-position">
										<?if (isset($pageFields['COOKIES_POSITION'])):?>
											<div class="landing-form-cookies-position-title"><?= $pageFields['COOKIES_POSITION']->getLabel();?></div>
											<div class="landing-form-cookies-position-inner">
												<input class="landing-form-cookies-position-input" type="radio" name="fields[ADDITIONAL_FIELDS][COOKIES_POSITION]"
													<?= $pageFields['COOKIES_POSITION'] == 'bottom_left' ? ' checked' : '';?> value="bottom_left" id="bottom_left">
												<input class="landing-form-cookies-position-input" type="radio" name="fields[ADDITIONAL_FIELDS][COOKIES_POSITION]"
													<?= $pageFields['COOKIES_POSITION'] == 'bottom_right' ? ' checked' : '';?> value="bottom_right" id="bottom_right">
												<div class="landing-form-cookies-position-list">
													<div class="landing-form-cookies-position-list-inner">
														<label class="landing-form-cookies-position-item landing-form-cookies-position-item-left
														<?= $pageFields['COOKIES_POSITION'] == 'bottom_left' ? ' landing-form-cookies-position-item-selected' : '';?>" for="bottom_left">
															<div class="landing-form-cookies-position-item-img"></div>
														</label>
														<label class="landing-form-cookies-position-item landing-form-cookies-position-item-right
														<?= $pageFields['COOKIES_POSITION'] == 'bottom_right' ? ' landing-form-cookies-position-item-selected' : '';?>" for="bottom_right">
															<div class="landing-form-cookies-position-item-img"></div>
														</label>
													</div>
												</div>
											</div>
										<?endif;?>
									</div>
								</div>
							</div>
						</td>
					</tr>
				<?endif;?>
				<?if (isset($hooks['COPYRIGHT'])):
				$pageFields = $hooks['COPYRIGHT']->getPageFields();
				if (isset($pageFields['COPYRIGHT_SHOW'])):
				?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="sign">
					<td class="ui-form-label"><?= $pageFields['COPYRIGHT_SHOW']->getLabel();?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<span class="ui-checkbox-block ui-checkbox-block-copyright">
							<?
							if (
								!$pageFields['COPYRIGHT_SHOW']->getValue() ||
								$hooks['COPYRIGHT']->isLocked()
							)
							{
								$pageFields['COPYRIGHT_SHOW']->setValue('Y');
							}
							echo $pageFields['COPYRIGHT_SHOW']->viewForm(array(
								'class' => 'ui-checkbox',
								'id' => 'checkbox-copyright',
								'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
							));
							?>
							<label for="checkbox-copyright" class="ui-checkbox-label"><?= Loc::getMessage('LANDING_TPL_ACTION_SHOW');?></label>
							<?
							if ($hooks['COPYRIGHT']->isLocked())
							{
								echo Restriction\Manager::getLockIcon(
									Restriction\Hook::getRestrictionCodeByHookCode('COPYRIGHT'),
									['checkbox-copyright']
								);
							}
							?>
						</span>
					</td>
				</tr>
				<?endif;?>
				<?if ($arResult['SHOW_RIGHTS']):?>
				<tr class="landing-form-hidden-row" data-landing-additional-detail="access">
					<td class="ui-form-label"><?= Loc::getMessage('LANDING_TPL_HOOK_RIGHTS_LABEL');?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<?if (Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE)):?>
						<table width="100%" class="internal" id="landing-rights-table" align="center">
							<tbody>
							<?foreach ($arResult['CURRENT_RIGHTS'] as $i => $right):
								$code = $right['ACCESS_CODE'];
								$accessCodes[] = $code;
								?>
								<tr class="landing-form-rights">
									<td class="landing-form-rights-right">
										<?= $right['ACCESS_PROVIDER'] ? \htmlspecialcharsbx($right['ACCESS_PROVIDER']) . ': ' : '';?>
										<?= \htmlspecialcharsbx($right['ACCESS_NAME']);?>:
									</td>
									<td class="landing-form-rights-left">
										<select name="fields[RIGHTS][TASK_ID][<?= $i;?>][]" multiple="multiple" size="7" class="ui-select">
											<?foreach ($arResult['ACCESS_TASKS'] as $accessTask):?>
												<option value="<?= $accessTask['ID'];?>"<?if (in_array($accessTask['ID'], $right['TASK_ID'])){?> selected="selected"<?}?>>
													<?= \htmlspecialcharsbx('[' . $accessTask['ID']. ']' . $accessTask['TITLE']);?>
												</option>
											<?endforeach;?>
										</select>

										<input type="hidden" name="fields[RIGHTS][ACCESS_CODE][]" value="<?= \htmlspecialcharsbx($code);?>">
										<a href="javascript:void(0);" onclick="deleteAccessRow(this);" data-id="<?= \htmlspecialcharsbx($code);?>" class="landing-form-rights-delete"></a>
									</td>
								</tr>
							<?endforeach;?>
							<tr>
								<td>
									<a href="javascript:void(0)" id="landing-rights-form">
										<?= Loc::getMessage('LANDING_TPL_HOOK_RIGHTS_LABEL_NEW');?>
									</a>
								</td>
							</tr>
							</tbody>
						</table>
						<?else:?>
							<?= Loc::getMessage('LANDING_TPL_HOOK_RIGHTS_PROMO_SALE');?>
						<?endif;?>
					</td>
				</tr>
				<?endif;?>
			<?endif;?>
			</table>
		</div>
	</div>

	<div class="<?if (false && $request->get('IFRAME') == 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
		<div class="landing-form-footer-container">
			<button id="landing-save-btn" type="submit" class="ui-btn ui-btn-success"  name="submit"  value="<?= Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD'));?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_' . ($arParams['SITE_ID'] ? 'SAVE' : 'ADD'));?>
			</button>
			<a class="ui-btn ui-btn-md ui-btn-link"<?if ($request->get('IFRAME') == 'Y'){?> id="action-close" href="#"<?} else {?> href="<?= $arParams['PAGE_URL_SITES']?>"<?}?>>
				<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
			</a>
		</div>
	</div>

</form>

<script type="text/javascript">
	<?if (isset($accessCodes)):?>
	var landingAccessSelected = <?= json_encode(array_fill_keys($accessCodes, true));?>;
	<?endif;?>
	BX.ready(function(){
		new BX.Landing.EditTitleForm(BX('ui-editable-title'), 600, true);
		new BX.Landing.ToggleFormFields(BX('landing-site-set-form'));
		new BX.Landing.Favicon();
		new BX.Landing.Custom404();
		new BX.Landing.Custom503();
		new BX.Landing.Copyright();
		new BX.Landing.Metrika();
		<?if (isset($hooks['COOKIES'])):?>
		new BX.Landing.Cookies();
		<?endif;?>
		new BX.Landing.Layout({
			siteId: '<?= $row['ID']['CURRENT'];?>',
			landingId: -1,
			type: '<?= $arParams['TYPE'];?>',
			messages: {
				area: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_LAYOUT_AREA'));?>'
			}
			<?if (isset($arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']])):?>
			,areasCount: <?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['AREA_COUNT'];?>
			,current: '<?= $arResult['TEMPLATES'][$row['TPL_ID']['CURRENT']]['XML_ID'];?>'
			<?else:?>
			,areasCount: 0
			,current: 'empty'
			<?endif;?>
		});
		<?if ($arResult['SHOW_RIGHTS']):?>
		new BX.Landing.Access({
			select: '<?= \CUtil::jsEscape($tasksStr);?>',
			inc: <?= count($arResult['CURRENT_RIGHTS']);?>,
		});
		<?endif;?>
		new BX.Landing.SaveBtn(BX('landing-save-btn'));
	});

</script>