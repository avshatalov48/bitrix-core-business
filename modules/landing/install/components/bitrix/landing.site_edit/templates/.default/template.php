<?php
namespace Bitrix\Landing\Components\LandingEdit;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \CMain $APPLICATION */

use \Bitrix\Main\Page\Asset;
use \Bitrix\Landing\Manager;
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
$domains = $arResult['DOMAINS'];
$tplRefs = $arResult['TEMPLATES_REF'];
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// title
if ($arParams['SITE_ID'])
{
	Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE_EDIT'));
}
else
{
	Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE_ADD'));
}

// assets
\CJSCore::init(array('color_picker', 'landing_master', 'action_dialog'));
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
?>

<script type="text/javascript">
	BX.ready(function(){
		var editComponent = new BX.Landing.EditComponent();
		top.window['landingSettingsSaved'] = false;
		<?if ($arParams['SUCCESS_SAVE']):?>
		top.window['landingSettingsSaved'] = true;
		top.BX.onCustomEvent('BX.Main.Filter:apply');
		editComponent.actionClose();
		<?endif;?>
	});
</script>

<?
if ($arParams['SUCCESS_SAVE'])
{
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
	<input type="hidden" name="fields[TYPE]" value="<?= $arParams['TYPE'];?>" />
	<?= bitrix_sessid_post();?>

	<div class="ui-form-title-block">
		<span class="ui-editable-field" id="ui-editable-title">
			<label class="ui-editable-field-label ui-editable-field-label-js"><?= $row['TITLE']['CURRENT']?></label>
			<input type="text" name="fields[TITLE]" class="ui-input ui-editable-field-input ui-editable-field-input-js" value="<?= $row['TITLE']['CURRENT']?>" placeholder="<?= $row['TITLE']['TITLE']?>" />
			<span class="ui-title-input-btn ui-title-input-btn-js ui-editing-pen"></span>
		</span>
	</div>

	<div class="landing-form-inner-js landing-form-inner">
		<div class="landing-form-table-wrap landing-form-table-wrap-js ui-form-inner">
			<table class="ui-form-table landing-form-table">
				<tr class="landing-form-site-name-fieldset">
					<td class="ui-form-label ui-form-label-align-top"><?= $row['CODE']['TITLE']?></td>
					<td class="ui-form-right-cell">
						<div class="landing-form-site-name-block" id="ui-editable-domain">
							<?if (Manager::isB24()):
								$domainName = isset($domains[$row['DOMAIN_ID']['CURRENT']]['DOMAIN'])
												? $domains[$row['DOMAIN_ID']['CURRENT']]['DOMAIN']
												: $row['DOMAIN_ID']['CURRENT'];
								$puny = new \CBXPunycode;
								$domainNameOriginal = $domainName;
								$domainName = $puny->decode($domainName);
								$zone = Manager::getZone();
								if ($row['TYPE']['CURRENT'] == 'STORE')
								{
									$b24Postfix = $zone == 'by'
												? '.bitrix24shop.by'
												: '.bitrix24.shop';
								}
								else
								{
									$b24Postfix = $zone == 'by'
												? '.bitrix24site.by'
												: '.bitrix24.site';
								}
								$allowedDomains = array(
									'b24' => array(
										'postfix' => $b24Postfix,
										'title' => 'B24 domain'
									),
									'own' => array(
										'postfix' => '',
										'title' => 'Own domain'
									)
								);
								?>
								<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT'];?>" />
								<input type="hidden" name="fields[DOMAIN_ID]" id="ui-domainname-text" value="<?= $domainName;?>" />
								<span class="landing-form-site-name-wrap">
									<span class="landing-form-site-name-label" id="ui-domainname-title"><?= $domainName;?></span>
									<span class="ui-title-input-btn  ui-domain-input-btn-js ui-editing-pen"></span>
								</span>
								<div id="ui-editable-domain-content" class="ui-editable-domain-content" style="display: none;">
								<?
								$wasSelected = false;
								$counter = 0;
								foreach ($allowedDomains as $domainCode => $domainItem):
									$counter++;
									$selected = false;
									$domainNameLocal = $domainName;
									if ($domainItem['postfix'] && (substr($domainNameLocal, -1 * strlen($domainItem['postfix'])) == $domainItem['postfix']))
									{
										$wasSelected = $selected = true;
										$domainNameLocal = substr($domainNameLocal, 0, -1 * strlen($domainItem['postfix']));
									}
									if ($domainCode == 'own' && !$wasSelected)
									{
										$wasSelected = $selected = true;
									}
									?>
										<?if ($domainCode != 'own'):?>
											<div class="ui-control-wrap landing-popup-control-wrap">
												<input type="radio" id="landing-domain-name-<?= $counter;?>" name="DOMAIN_NAME" value="<?= $domainItem['postfix'];?>"<?if ($selected) {?> checked="checked"<?}?> class="ui-radio ui-postfix" />
												<div class="landing-form-domainname-wrap">
													<label class="ui-form-control-label" for="landing-domain-name-<?= $counter;?>"><?= Loc::getMessage('LANDING_TPL_DOMAIN_NAME_' . strtoupper($domainCode));?></label>
													<input type="text" value="<?= $selected ? $domainNameLocal : '';?>" class="ui-input ui-domainname ui-domainname-subdomain" data-postfix="<?= $domainItem['postfix'];?>" />
													<span class="landing-site-name-postfix"><?= $domainItem['postfix'];?></span>
													<span class="landing-site-name-status" id="landing-site-name-status-subdomain"></span>
												</div>
											</div>
										<?elseif ($domainCode == 'own'):?>
											<div class="ui-control-wrap landing-popup-control-wrap">
												<input type="radio" name="DOMAIN_NAME" id="landing-domain-name-<?= $counter;?>" value="<?= $domainItem['postfix'];?>"<?if ($selected) {?> checked="checked"<?}?> class="ui-radio ui-postfix" />
												<div class="landing-form-domainname-wrap">
													<label class="ui-form-control-label" for="landing-domain-name-<?= $counter;?>"><?= Loc::getMessage('LANDING_TPL_DOMAIN_NAME_' . strtoupper($domainCode));?></label>
													<input type="text" id="landing-form-domain-name-field" maxlength="64" value="<?= $selected ? $domainNameLocal : '';?>" class="ui-input ui-domainname" data-postfix="" />
													<span class="landing-site-name-status" id="landing-site-name-status-domain"></span>
												</div>
											</div>
											<div class="landing-alert landing-alert-info">
												<p class="landing-alert-paragraph">
													<?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_ANY_INSTRUCT');?>
												</p>
												<table class="landing-alert-table">
													<tr class="landing-alert-table-header">
														<td>
															<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_1');?></span>
														</td>
														<td>
															<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_2');?></span>
														</td>
														<td>
															<span class="landing-alert-header-text"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_DNS_3');?></span>
														</td>
													</tr>
													<tr class="landing-alert-table-content">
														<td id="landing-form-domain-name-text">
															<?= $domainNameOriginal ? $domainNameOriginal : 'landing.mydomain';?>
														</td>
														<td>CNAME</td>
														<td>lb<?= $b24Postfix;?>.</td>
													</tr>
													<tr class="landing-alert-table-content">
														<td id="landing-form-domain-any-name-text">
															<?= $domainNameOriginal ? $domainNameOriginal : 'landing.mydomain.ru';?>
														</td>
														<td>A</td>
														<td><?= $arResult['IP_FOR_DNS'];?></td>
													</tr>
												</table>
											</div>
											<div class="landing-alert landing-alert-warning">
												<p class="landing-alert-paragraph">
													<i style="display: none;">
																<span id="landing-form-domain-any-name-textAAA" class="landing-form-domain-name-text">
																	</span>.
														IN A
													</i>
												</p>
												<p class="landing-alert-paragraph">
													<strong><?= Loc::getMessage('LANDING_TPL_DOMAIN_ATTENTION');?></strong>
													<?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_AAAA');?>
												</p>
												<?if ($helpUrl = \Bitrix\Landing\Help::getHelpUrl('DOMAIN_EDIT')):?>
													<p class="landing-alert-paragraph">
														<a class="landing-alert-more" href="<?= $helpUrl;?>" target="_blank"><?= Loc::getMessage('LANDING_TPL_DOMAIN_OWN_DOMAIN_HELP');?></a>
													</p>
												<?endif;?>
											</div>
											<?if (!$arResult['CUSTOM_DOMAIN']):?>
											<script type="text/javascript">
												BX.ready(function()
												{
													if (typeof BX.Landing.PaymentAlert !== 'undefined')
													{
														BX.Landing.PaymentAlert({
															nodes: [BX('landing-domain-name-<?= $counter?>'), BX('landing-form-domain-name-field')],
															title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_DENIED_TITLE'));?>',
															message: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_DENIED_TEXT'));?>'
														});
													}
												});
											</script>
											<?endif;?>
										<?endif;?>
								<?endforeach;?>
								</div>
							<?else:?>
								<select name="fields[DOMAIN_ID]" class="ui-select">
									<?foreach ($arResult['DOMAINS'] as $item):?>
										<option value="<?= $item['ID']?>"<?if ($item['ID'] == $row['DOMAIN_ID']['CURRENT']){?> selected="selected"<?}?>>
											<?= \htmlspecialcharsbx($item['DOMAIN'])?>
										</option>
									<?endforeach;?>
								</select>
								<input type="hidden" name="fields[CODE]" value="<?= $row['CODE']['CURRENT']?>" >
							<?endif;?>
						</div>
					</td>
				</tr>
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
				<tr>
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
					<tr>
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
							<span class="landing-additional-alt-promo-wrap">
								<span class="landing-additional-alt-promo-text">Favicon</span>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_BG');?></span>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_METRIKA');?></span>
								<?/*<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_MAPS');?></span>*/?>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_VIEW');?></span>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_LAYOUT');?></span>
								<?if (!empty($arResult['LANG_CODES']) && Manager::isB24() && $row['LANG']):?>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_LANG');?></span>
								<?endif;?>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_404');?></span>
								<?if (isset($hooks['ROBOTS'])):?>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_ROBOTS');?></span>
								<?endif;?>
								<span class="landing-additional-alt-promo-text">HTML/CSS</span>
								<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_OFF');?></span>
							</span>
						</div>
					</td>
				</tr>
				<?if (isset($hooks['FAVICON'])):
					$pageFields = $hooks['FAVICON']->getPageFields();
					?>
				<tr class="landing-form-hidden-row">
					<td class="ui-form-label ui-form-label-align-top"><?= $hooks['FAVICON']->getTitle();?></td>
					<td class="ui-form-right-cell ui-form-right-cell-favicon">
						<div class="landing-form-favicon-wrap">
							<?$favId = (int) $pageFields['FAVICON_PICTURE']->getValue(); ?>
							<img src="<?= $favId > 0 ? \Cfile::getPath($favId) : '/bitrix/images/1.gif';?>" alt="" width="32" id="landing-form-favicon-src" />
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
				<tr class="landing-form-hidden-row">
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
				<?if (isset($hooks['YACOUNTER']) || isset($hooks['GACOUNTER']) || isset($hooks['GTM'])):?>
				<tr class="landing-form-hidden-row">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_METRIKA');?></td>
					<td class="ui-form-right-cell ui-form-right-cell-metrika">
						<?$template->showSimple('GACOUNTER');?>
						<?$template->showSimple('GTM');?>
						<?
						if (Manager::availableOnlyForZone('ru'))
						{
							$template->showSimple('YACOUNTER');
						}
						?>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['PIXELFB']) || isset($hooks['PIXELVK'])):?>
					<tr class="landing-form-hidden-row">
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
				<tr class="landing-form-hidden-row">
					<td class="ui-form-label ui-form-label-align-top"><?= Loc::getMessage('LANDING_TPL_HOOK_GMAP');?></td>
					<td class="ui-form-right-cell ui-form-right-cell-map">
						<?$template->showSimple('GMAP');?>
					</td>
				</tr>
				<?endif;?>
				<?if (isset($hooks['VIEW'])):
					$pageFields = $hooks['VIEW']->getPageFields();
					?>
				<tr class="landing-form-hidden-row">
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
										$value = array_shift(array_keys($items));
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
				<tr class="landing-form-hidden-row">
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
										<input class="layout-switcher" data-layout="without_right" name="fields[TPL_ID]" id="layout-radio-6" type="radio">
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
				<?if (!empty($arResult['LANG_CODES']) && Manager::isB24() && $row['LANG']):?>
					<tr class="landing-form-hidden-row">
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
				<tr class="landing-form-hidden-row">
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
				<?if (isset($hooks['ROBOTS'])):
					$pageFields = $hooks['ROBOTS']->getPageFields();
					?>
				<tr class="landing-form-hidden-row">
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
				<?if (isset($hooks['HEADBLOCK'])):
					$pageFields = $hooks['HEADBLOCK']->getPageFields();
					?>
					<tr class="landing-form-hidden-row">
						<td class="ui-form-label ui-form-label-align-top"><?= $hooks['HEADBLOCK']->getTitle();?></td>
						<td class="ui-form-right-cell">
							<div class="ui-checkbox-hidden-input landing-form-custom-fields">
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
											<?= $pageFields['HEADBLOCK_USE']->getLabel();?>
										</label>
									<?endif;?>
									<?if (isset($pageFields['HEADBLOCK_CODE'])):?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label">
												<div class="ui-form-control-label-title"><?= $pageFields['HEADBLOCK_CODE']->getLabel();?></div>
												<div><?= $pageFields['HEADBLOCK_CODE']->getHelpValue();?></div>
											</div>
											<?
											$pageFields['HEADBLOCK_CODE']->viewForm(array(
												'class' => 'ui-textarea',
												'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
											));
											?>
										</div>
									<?endif;?>
									<?if (isset($pageFields['HEADBLOCK_CSS_CODE'])):?>
										<div class="ui-control-wrap">
											<div class="ui-form-control-label">
												<div class="ui-form-control-label-title"><?= $pageFields['HEADBLOCK_CSS_CODE']->getLabel();?></div>
												<div><?= $pageFields['HEADBLOCK_CSS_CODE']->getHelpValue();?></div>
											</div>
											<?
											$pageFields['HEADBLOCK_CSS_CODE']->viewForm(array(
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
					<tr class="landing-form-hidden-row">
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
				<?if (isset($hooks['COPYRIGHT'])):
				$pageFields = $hooks['COPYRIGHT']->getPageFields();
				if (isset($pageFields['COPYRIGHT_SHOW'])):
				?>
				<tr class="landing-form-hidden-row">
					<td class="ui-form-label"><?= $pageFields['COPYRIGHT_SHOW']->getLabel();?></td>
					<td class="ui-form-right-cell ui-form-field-wrap-align-m">
						<span class="ui-checkbox-block">
							<?
							if (!$pageFields['COPYRIGHT_SHOW']->getValue())
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
						</span>
						<?if (!Manager::checkFeature(Manager::FEATURE_ENABLE_ALL_HOOKS)):?>
						<script type="text/javascript">
							BX.ready(function()
							{
								BX.bind(BX('checkbox-copyright'), 'click', function(e)
								{
									BX.PreventDefault(e);
								});
								if (typeof BX.Landing.PaymentAlert !== 'undefined')
								{
									BX.Landing.PaymentAlert({
										nodes: [BX('checkbox-copyright')],
										title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COPY_DISABLED_TITLE'));?>',
										message: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_COPY_DISABLED_TEXT'));?>'
									});
								}
							});
						</script>
						<?endif;?>
					</td>
				</tr>
				<?
				endif;
			endif;?>
			</table>
		</div>
	</div>

	<div class="<?if ($request->get('IFRAME') == 'Y'){?>landing-edit-footer-fixed <?}?>pinable-block">
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
	BX.ready(function(){
		new BX.Landing.EditTitleForm(BX('ui-editable-title'), 600, true);
		new BX.Landing.ToggleFormFields(BX('landing-site-set-form'));
		new BX.Landing.Favicon();
		new BX.Landing.Custom404();
		new BX.Landing.Custom503();
		new BX.Landing.Copyright();
		new BX.Landing.Metrika();
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
		new BX.Landing.DomainNamePopup({
			messages: {
				title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_POPUP'));?>',
				errorEmpty:'<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_DOMAIN_ERROR_EMPTY'));?>'
			},
			domainId: <?= (int)$row['DOMAIN_ID']['CURRENT'];?>
		});
		new BX.Landing.SaveBtn(BX('landing-save-btn'));
	});

</script>