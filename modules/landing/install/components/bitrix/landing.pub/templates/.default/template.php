<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

\Bitrix\Landing\Manager::getApplication()->setTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
	return;
}

$arResult['LANDING']->view();

$enableHook = Manager::checkFeature(Manager::FEATURE_ENABLE_ALL_HOOKS);
if ($enableHook)
{
	$hooksSite = \Bitrix\Landing\Hook::getForSite($arResult['LANDING']->getSiteId());
}

// set meta og:image
$metaOG = Manager::getPageView('MetaOG');
if (strpos($metaOG, '"og:image"') === false)
{
	Manager::setPageView('MetaOG',
		'<meta name="og:image" content="' . $arResult['LANDING']->getPreview() . '" />'
	);
}
?>
<?if (!$enableHook || isset($hooksSite['COPYRIGHT']) && $hooksSite['COPYRIGHT']->enabled()):?>
<div class="bitrix-footer">
	<?if (Manager::isB24()):?>
	<span class="bitrix-footer-text"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_0');?></span>
	<img class="bitrix-footer-logo" src="<?= $this->getFolder()?>/images/<?= in_array(Manager::getZone(), array('ru', 'ua', 'en')) ? LANGUAGE_ID : 'en'?>.svg" alt="<?= Loc::getMessage('LANDING_TPL_COPY_NAME');?>">
	<span class="bitrix-footer-text">&mdash; <?= Loc::getMessage('LANDING_TPL_COPY_REVIEW');?></span>
	<a class="bitrix-footer-link" target="_blank" href="https://<?= $arResult['DOMAIN'];?>/?<?= $arResult['ADV_CODE'];?>"><?= Loc::getMessage('LANDING_TPL_COPY_LINK');?></a>
	<?else:?>
		<span class="bitrix-footer-text"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_0');?></span>
		<a href="https://www.1c-bitrix.ru/?<?= $arResult['ADV_CODE'];?>" target="_blank" class="bitrix-footer-link"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_1');?></a>
	<?endif;?>
</div>
<?endif;?>