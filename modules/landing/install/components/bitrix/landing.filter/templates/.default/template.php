<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Help;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\UI;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */
/** @var LandingFilterComponent $component */

// init
Loc::loadMessages(__FILE__);
\CJSCore::init(array('sidepanel', 'action_dialog', 'loader'));
Extension::load([
	'ui.hint',
	'ui.toolbar',
	'ai.copilot-promo-popup',
]);

if ($arResult['FATAL'])
{
	return;
}

// some vars
$toolbarParams = [];
$isDeleted = $component::isDeleted();

$uriAjax = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
$uriAjax->addParams(array(
	'IS_AJAX' => 'Y',
	$arResult['NAVIGATION_ID'] => $arResult['CURRENT_PAGE'],
));
$uriAjax->deleteParams([
	LandingBaseComponent::NAVIGATION_ID,
]);
$toolbarParams['landingAjaxPath'] = $uriAjax->getUri();

// title
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'pagetitle-toolbar-field-view');
Asset::getInstance()->addJs($this->GetFolder() . '/script.js');

// template
$isBitrix24Template = false;
if (defined('SITE_TEMPLATE_ID'))
{
	$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';
}
?>

<?php
if (!$isBitrix24Template)
{
	Toolbar::hideTitle();
	$APPLICATION->IncludeComponent("bitrix:ui.toolbar", '', []);
}
?>

<?php
// CREATE BUTTON
if (isset($arParams['BUTTONS']) && is_array($arParams['BUTTONS']))
{
	$button = array_shift($arParams['BUTTONS']);
	if (isset($button['LINK'], $button['TITLE']))
	{
		$createButton = new UI\Buttons\CreateButton([
			'id' => 'landing-create-element',
			'text' => HtmlFilter::encode($button['TITLE']),
		]);
		$toolbarParams['landingCreateButtonId'] = $createButton->getUniqId();

		$isButtonDisabled = isset($button['DISABLED']) && $button['DISABLED'] === true;
		if ($isButtonDisabled)
		{
			$hint = Loc::getMessage('LANDING_TPL_CREATE_BUTTON_HINT');
			if ($helpUrl = Help::getHelpUrl('SHOP1C'))
			{
				$hint .= '<br>';
				$hint .= "<a href=\"{$helpUrl}\">";
				$hint .= Loc::getMessage('LANDING_TPL_CREATE_BUTTON_HINT_LINK_TEXT');
				$hint .= '</a>';
			}
			$createButton->addDataAttribute('hint', $hint);
			$createButton->addDataAttribute('hint-no-icon');
			$createButton->addDataAttribute('hint-html');
			$createButton->addDataAttribute('hint-interactivity');

			$createButton->setDisabled();
		}
		elseif ($isDeleted)
		{
			$createButton->setDisabled();
		}
		else
		{
			$createButton->setLink($button['LINK']);
		}

		if (!empty($arParams['BUTTONS']))
		{
			$createButtonOptions = [];
			foreach ($arParams['BUTTONS'] as $button)
			{
				if (isset($button['LINK'], $button['TITLE']))
				{
					$createButtonOptions[] = [
						'href' => $button['LINK'],
						'text' => $button['TITLE'],
					];
				}
			}
			if (!empty($createButtonOptions))
			{
				$createButton
					->setIcon(UI\Buttons\Icon::ADD)
					->setDropdown()
					->setMenu([
						'autoHide' => true,
						'closeEsc' => true,
						'offsetLeft' => 20,
						'angle' => true,
						'items' => $createButtonOptions,
					])
				;
			}
		}

		Toolbar::addButton($createButton, ButtonLocation::AFTER_TITLE);
	}
}

// FILTER
$filterId = \CUtil::jsEscape($arParams['FILTER_ID']);
$toolbarParams['filterId'] = $filterId;
$filterOptions = [
	'FILTER_ID' => $filterId,
	'GRID_ID' => $arParams['FILTER_ID'],
	'FILTER' => $arResult['FILTER'],
	'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
	'ENABLE_LABEL' => true,
	'ENABLE_LIVE_SEARCH' => true,
	'RESET_TO_DEFAULT_MODE' => true,
];
Toolbar::addFilter($filterOptions);

// RECYCLE
Toolbar::addButton(
	new UI\Buttons\Button([
		'id' => 'landing-recycle-bin',
		'color' => UI\Buttons\Color::LIGHT_BORDER,
		'text' => Loc::getMessage('LANDING_TPL_RECYCLE_BIN'),
		'click' => new UI\Buttons\JsHandler('BX.Landing.Component.Filter.onRecycleBinClick'),
	])
);

// SETTINGS
if ($arParams['SETTING_LINK'])
{
	// for compatibility
	if (!is_array($arParams['SETTING_LINK']))
	{
		$arParams['SETTING_LINK'] = [
			[
				'TITLE' => '',
				'LINK' => $arParams['SETTING_LINK'],
			],
		];
	}

	$links = [];
	foreach ($arParams['SETTING_LINK'] as $settingsLink)
	{
		if (isset($settingsLink['DELIMITER']) && $settingsLink['DELIMITER'] === true)
		{
			$links[] = [
				'delimiter' => true,
			];

			continue;
		}

		if (
			!isset($settingsLink['LINK'], $settingsLink['TITLE'])
			|| $settingsLink['LINK'] === ''
			|| $settingsLink['TITLE'] === ''
		)
		{
			continue;
		}

		$link = [
			'href' => $settingsLink['LINK'],
			'text' => $settingsLink['TITLE'],
		];
		if (isset($settingsLink['DATASET']) && is_array($settingsLink['DATASET']))
		{
			$link['dataset'] = $settingsLink['DATASET'];
		}

		$links[] = $link;
	}

	$toolbarParams['landingSettingsButtons'] = $links;

	Toolbar::addButton(
		new UI\Buttons\Button([
			'id' => 'landing-menu-settings',
			'color' => UI\Buttons\Color::LIGHT_BORDER,
			'icon' => UI\Buttons\Icon::SETTINGS,
			'click' => new UI\Buttons\JsHandler('BX.Landing.Component.Filter.onSettingsClick'),
		])
	);
}

// FOLDER
if ($arParams['FOLDER_SITE_ID'])
{
	$createFolderButton = new UI\Buttons\Button([
		'id' => 'landing-create-folder',
		'color' => UI\Buttons\Color::LIGHT_BORDER,
		'icon' => UI\Buttons\Icon::ADD_FOLDER,
		'click' => new UI\Buttons\JsHandler('BX.Landing.Component.Filter.onFolderCreateClick'),
		'dataset' => [
			'type' => $arParams['TYPE'],
			'action' => \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_CREATE_FOLDER_ACTION')),
			'siteId' => $arParams['FOLDER_SITE_ID'],
			'folderId' => $arParams['FOLDER_ID'],
		],
	]);
	$toolbarParams['landingCreateFolderButtonId'] = $createFolderButton->getUniqId();
	if ($isDeleted)
	{
		$createFolderButton->setDisabled();
	}
	else
	{
		$toolbarParams['canCreateFolder'] = true;
	}
	Toolbar::addButton($createFolderButton);
}
?>

<?php
// AI site first popup
$isNeedShowSiteAIPopup = false;
$option = \CUserOptions::GetOption('landing', 'site-ai-popup');
if (!isset($option['isShow']))
{
	$isNeedShowSiteAIPopup = true;
}
if (
	$arParams['TYPE'] === 'PAGE'
	&& $isNeedShowSiteAIPopup
	&& \Bitrix\Landing\Copilot\Manager::isAvailable()
)
{
	$toolbarParams['landingShowSiteAIPopup'] = true;
}
?>

<script>
	BX.ready(() =>
	{
		new BX.Landing.Component.Filter(<?= Json::encode($toolbarParams) ?>);

		const container = document.querySelector('.ui-toolbar');
		if (container)
		{
			BX.UI.Hint.init(container);
		}
	});
</script>
