<?php
namespace Bitrix\Landing\Components\LandingEdit;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE'));

if ($arResult['ERRORS'])
{
	?>
	<div class="landing-message-label error">
		<?= implode("\n", $arResult['ERRORS'])?>
	</div>
	<?
}

if ($arResult['FATAL'])
{
	return;
}

$row = $arResult['LANDING'];
$instance = $arResult['LANDING_INST'];
$meta = $arResult['META'];
$hooks = $arResult['HOOKS'];
$hooksSite = $arResult['HOOKS_SITE'];

if (!$row['SITE_ID']['CURRENT'])
{
	$row['SITE_ID']['CURRENT'] = $arParams['SITE_ID'];
}

if ($row['ID']['CURRENT'])
{
	$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE_EDIT'));
}
else
{
	$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE_ADD'));
}

Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.site_edit/templates/.default/script.js');
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.site_edit/templates/.default/style.css');

include \Bitrix\Landing\Manager::getDocRoot() . '/bitrix/components/bitrix/landing.site_edit/templates/.default/template_class.php';

$template = new Template($arResult);
?>

<form action="<?= POST_FORM_ACTION_URI?>" method="post">
	<input type="hidden" name="fields[SAVE_FORM]" value="Y" />
	<input type="hidden" name="fields[SITE_ID]" value="<?= \htmlspecialcharsbx($row['SITE_ID']['CURRENT'])?>">
	<input type="hidden" name="fields[CODE]" value="<?= \htmlspecialcharsbx($row['CODE']['CURRENT'])?>">
	<?= bitrix_sessid_post()?>

	<div class="landing-info-panel">
		<div class="landing-info-panel-title">
			<input type="text" name="fields[TITLE]" value="<?= $row['TITLE']['CURRENT']?>" placeholder="<?= $row['TITLE']['TITLE']?>">
		</div>
	</div>

	<div class="landing-options landing-options-main">
		<div class="landing-options-item-destination-wrap">
			<div>
				<div class="landing-options-item landing-options-item-destination">
					<span class="landing-options-item-param"><?= $row['ACTIVE']['TITLE']?></span>
					<div class="landing-options-item-inner">
						<div style="display: none;">
							<input type="checkbox" name="fields[ACTIVE]" id="action-public-checkbox" value="Y"<?if ($row['ACTIVE']['CURRENT'] == 'Y') {?> checked="checked"<?}?>>
						</div>
						<span class="landing-options-public-status landing-options-public-status-<?= $row['ACTIVE']['CURRENT'] == 'Y' ? 'active' : 'unactive'?>" <?
							?>id="action-public-status" <?
							?>data-retitle="<?= Loc::getMessage('LANDING_TPL_PUBLIC_MESS_' . ($row['ACTIVE']['CURRENT'] == 'Y' ? 'N' : 'Y'))?>">
							<?= Loc::getMessage('LANDING_TPL_PUBLIC_MESS_' . $row['ACTIVE']['CURRENT'])?>
						</span>
						<button class="landing-options-button" id="action-public" data-retitle="<?= Loc::getMessage('LANDING_TPL_PUBLIC_' . $row['ACTIVE']['CURRENT'])?>">
							<?= Loc::getMessage('LANDING_TPL_PUBLIC_' . ($row['ACTIVE']['CURRENT'] == 'Y' ? 'N' : 'Y'))?>
						</button>
					</div>
				</div>
				<?
				$template->showHookBlock('B24BUTTON', array(
					'desription_hook' => function()
					{
						$settingsLink = '';
						// b24 crm
						if (\Bitrix\Main\ModuleManager::isModuleInstalled('crm'))
						{
							if (file_exists(\Bitrix\Landing\Manager::getDocRoot() . '/crm/button/index.php'))
							{
								$settingsLink = '/crm/button/';
							}
						}
						// site manager
						elseif (\Bitrix\Main\Loader::includeModule('b24connector'))
						{
							$settingsLink = '/bitrix/admin/b24connector_buttons.php';
						}
						if ($settingsLink)
						{
							?>&nbsp;&nbsp;<a href="<?= $settingsLink?>" target="_blank"><?= Loc::getMessage('LANDING_TPL_SETTINGS_LINK')?></a><?
						}
					}
				));
				?>
			</div>
		</div>
	</div>

	<div id="action-additional" class="landing-additional-block" data-block="action-additional-block">
		<div class="landing-additional-alt">
			<div class="landing-additional-alt-more">
				<?= Loc::getMessage('LANDING_TPL_ADDITIONAL');?>
			</div>
			<div class="landing-additional-alt-promo">
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_FAVICON');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_METRIKA');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_BG');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_MAPS');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_SOCIAL');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_SEO');?></span>
				<span class="landing-additional-alt-promo-text"><?= Loc::getMessage('LANDING_TPL_ADDITIONAL_HTMLCSS');?></span>
			</div>
		</div>
	</div>

	<div id="action-additional-block" class="landing-options landing-options-additional" style="display: none;">
		<div class="landing-options-item-destination-wrap">
			<div>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('YACOUNTER');?>
					<?$template->showHookBlock('GACOUNTER');?>
				</div>
				<?if (isset($hooks['BACKGROUND'])):?>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('BACKGROUND', array('group' => true));?>
				</div>
				<?endif;?>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('PADDING');?>
				</div>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('GMAP');?>
				</div>
				<?
				$code = 'METAOG';
				if ($row['ID']['CURRENT'] && isset($hooks[$code])):?>
				<div class="landing-options-item landing-options-item-destination">
					<span class="landing-options-item-param">Social</span>
					<div class="landing-options-item-inner">
						<div class="landing-social-preview">
							<div class="landing-social-description">
								<div class="landing-social-img">
									<img src="/bitrix/images/1.gif" alt="" id="landing-social-picture" />
								</div>
								<script type="text/javascript">
									BX.ready(function(){
										new BX.Landing.EditComponent({
											publicUrl: "<?= \htmlspecialcharsbx($instance->getPublicUrl())?>"
										});
									});
								</script>
								<div class="landing-social-title" id="landing-hook-metaog-title">
									<?//= \htmlspecialcharsbx($meta['og:title'])?>
								</div>
								<div class="landing-social-text" id="landing-hook-metaog-description">
									<?//= \htmlspecialcharsbx($meta['og:description'])?>
								</div>
							</div>
						</div>
						<a href="javascript:void(0);" onclick="BX.remove(this); BX.show(BX('landing-hook-metaog'));">
							<?= Loc::getMessage('LANDING_TPL_ACTION_CHANGE');?>
						</a>
						<div id="landing-hook-metaog" style="display: none;">
							<?$template->showHookBlock('METAOG', array('wrapper' => false));?>
						</div>
					</div>
				</div>
				<?endif;?>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('METAROBOTS');?>
				</div>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('THEME');?>
				</div>
				<div class="landing-options-item-destination-group">
					<?$template->showHookBlock('UP');?>
				</div>
				<?if (isset($hooks['HEADBLOCK']) || isset($hooks['CUSTOMCSS'])):?>
					<div class="landing-options-item landing-options-item-destination">
						<span class="landing-options-item-param"><?= Loc::getMessage('LANDING_TPL_FIELD_HTMLCSS')?></span>
						<div class="landing-options-item-inner">
							<?$template->showHookBlock('HEADBLOCK', array('wrapper' => false));?>
							<?$template->showHookBlock('CUSTOMCSS', array('wrapper' => false));?>
						</div>
					</div>
				<?endif;?>
			</div>
		</div>
	</div>

	<div class="<?if ($_REQUEST['IFRAME'] == 'Y'){?>landing-edit-footer-fixed <?}//tmp?>pinable-block">
		<div class="landing-form-footer-container">
			<button class="webform-small-button webform-small-button-accept">
				<span class="webform-small-button-text">
					<?= Loc::getMessage('LANDING_TPL_BUTTON_' . ($row['ID']['CURRENT'] ? 'SAVE' : 'ADD'))?>
				</span>
			</button>
			<a class="landing-button-link" id="action-close" href="<?= $arParams['PAGE_URL_SITES']?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL')?>
			</a>
		</div>
	</div>

		<?/*$code = 'METAMAIN';?>
		<?if (isset($hooks[$code])):?>
			<?foreach ($hooks[$code]->getPageFields() as $field):?>
			<tr>
				<td><?= $field->getLabel()?>:</td>
				<td>
					<?= $field->viewForm(array(
						'class' => 'content-edit-form-field-input-text',
						'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
					))?>
				</td>
			</tr>
			<?endforeach;?>
			<tr>
				<td></td>
				<td>
					<div id="landing-form-metamain">
						<a href="#"></a>
						<p></p>
					</div>
				</td>
			</tr>
		<?endif;*/?>
</form>
