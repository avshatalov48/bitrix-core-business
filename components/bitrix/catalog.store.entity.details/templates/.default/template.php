<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

global $APPLICATION;

$title =
	$arResult['FORM']['ENTITY_DATA']['TITLE']
	?: $arResult['FORM']['ENTITY_DATA']['ADDRESS']
	?: Loc::getMessage('CATALOG_STORE_DETAILS_CREATION_TITLE')
;
$APPLICATION->SetTitle($title);

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-background');

if (!empty($arResult['ERROR']))
{
	require __DIR__ .'/include/errors.php';
	return;
}

Extension::load([
	'catalog.entity-card',
]);

Toolbar::deleteFavoriteStar();

$tabs = [
	[
		'id' => 'main',
		'name' => Loc::getMessage('TAB_GENERAL_TITLE'),
		'enabled' => true,
		'active' => true,
	],
];

$guid = $arResult['FORM']['GUID'];
$containerId = "{$guid}_CONTAINER";
$tabMenuContainerId = "{$guid}_TABS_MENU";
$tabContainerId = "{$guid}_TABS";
?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="catalog-entity-wrap catalog-wrapper">
	<?php if (count($tabs) > 1): ?>
	<div class="catalog-entity-section catalog-entity-section-tabs ui-entity-stream-section-planned-above-overlay">
		<ul id="<?=htmlspecialcharsbx($tabMenuContainerId)?>" class="catalog-entity-section-tabs-container">
			<?php
			foreach ($tabs as $tab)
			{
				$className = 'catalog-entity-section-tab';

				if (isset($tab['active']) && $tab['active'])
				{
					$className .= ' catalog-entity-section-tab-current';
				}
				elseif (isset($tab['enabled']) && !$tab['enabled'])
				{
					$className .= ' catalog-entity-section-tab-disabled';
				}
				?>
				<li data-tab-id="<?=htmlspecialcharsbx($tab['id'])?>" class="<?= $className ?>">
					<a class="catalog-entity-section-tab-link" href="#"><?=htmlspecialcharsbx($tab['name'])?></a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<?php endif; ?>

	<div id="<?=htmlspecialcharsbx($tabContainerId)?>" style="position: relative;">
		<?php
		foreach ($tabs as $tab)
		{
			$tabId = $tab['id'];
			$className = 'catalog-entity-section catalog-entity-section-info';
			$style = '';

			if ($tab['active'] !== true)
			{
				$className .= ' catalog-entity-section-tab-content-hide catalog-entity-section-above-overlay';
				$style = 'style="display: none;"';
			}
			?>
			<div data-tab-id="<?=htmlspecialcharsbx($tabId)?>" class="<?=$className?>" <?=$style?>>
				<?php
				$file = new File(__DIR__ . "/tabs/{$tabId}.php");
				if ($file->isExists())
				{
					include $file->getPath();
				}
				else
				{
					echo "Unknown tab {{$tabId}}.";
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
