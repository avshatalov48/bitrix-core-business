<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$this->setFrameMode(true);

\Bitrix\Landing\Manager::setPageTitle(
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

\Bitrix\Main\UI\Extension::load(
	\Bitrix\Landing\Config::get('js_core_public')
);
?>

<?ob_start(); ?>
<?if (!$enableHook || isset($hooksSite['COPYRIGHT']) && $hooksSite['COPYRIGHT']->enabled()):?>
<div class="bitrix-footer">
	<?if (Manager::isB24()):?>
		<span class="bitrix-footer-text">
			<?
			$fullCopy = Loc::getMessage('LANDING_TPL_COPY_FULL');
			$logo = '<img src="' .
						$this->getFolder() . '/images/' .
						(in_array(Manager::getZone(), array('ru', 'ua', 'en')) ? LANGUAGE_ID : 'en') .
						'.svg" alt="' . Loc::getMessage('LANDING_TPL_COPY_NAME') . '">';
			if ($fullCopy)
			{
				echo str_replace(
					'#LOGO#',
					$logo,
					$fullCopy
				);
			}
			else
			{
				echo Loc::getMessage('LANDING_TPL_COPY_NAME_0') . ' ';
				echo $logo;
				echo ' &mdash; ';
				echo Loc::getMessage('LANDING_TPL_COPY_REVIEW');
			}
			?>
		</span>
		<a class="bitrix-footer-link" target="_blank" href="https://<?= $arResult['DOMAIN'];?><?= $arResult['COPY_LINK'];?>?<?= $arResult['ADV_CODE'];?>"><?= Loc::getMessage('LANDING_TPL_COPY_LINK');?></a>
		<?else:?>
		<span class="bitrix-footer-text"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_0');?></span>
		<a href="https://www.1c-bitrix.ru/?<?= $arResult['ADV_CODE'];?>" target="_blank" class="bitrix-footer-link"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_1');?></a>
	<?endif;?>
</div>
<?endif;?>
<?
$footer = ob_get_contents();
ob_end_clean();
Manager::setPageView('BeforeBodyClose', $footer);
?>