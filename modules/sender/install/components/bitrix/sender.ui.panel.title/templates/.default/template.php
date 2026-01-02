<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Buttons\JsCode;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var \CBitrixComponentTemplate $this */
Extension::load(['ui.feedback.form']);
\Bitrix\Main\Loader::includeModule('ui');

if (defined('ADMIN_SECTION') && ADMIN_SECTION === true && $_REQUEST['IFRAME'] !== 'Y')
{
	$APPLICATION->IncludeComponent(
		"bitrix:ui.toolbar",
		'',
		[]
	);
	Toolbar::hideTitle();
	Toolbar::deleteFavoriteStar();
}


foreach ($arParams['LIST'] as $item)
{
	if($item['type'] === 'filter' && $item['params'])
	{
		Toolbar::addFilter($item['params']);
	}
	elseif($item['type'] === 'buttons')
	{
		Extension::load("ui.buttons");
		Extension::load("ui.buttons.icons");

		foreach($item['list'] as $button)
		{
			if(empty($button))
			{
				continue;
			}

			$button['id'] = $button['id'] ?? '';
			$button['href'] = $button['href'] ?? '';
			$button['class'] = $button['class'] ?? '';
			$button['caption'] = $button['caption'] ?? '';
			$button['visible'] = !isset($button['visible']) || $button['visible'];

			if($button['type'] === 'list')
			{
				$button['location'] = $button['location'] ?? ButtonLocation::AFTER_TITLE;
				$listButton = new Button([
					"color" => Color::SUCCESS,
					"text" => $button['caption'],
					"classList" => [$button['class']],
				]);
				$listButton->setDropdown();
				$listButton->addAttribute('id', $button['id']);
				$listButton->addAttribute('style', $button['visible'] ? '' : 'display: none;');

				Toolbar::addButton($listButton, $button['location']);
			}
			elseif($button['type'] === 'settings')
			{
				$button['id'] = 'sender-ui-buttons-settings';
				$button['location'] = $button['location'] ?? ButtonLocation::RIGHT;

				$settingsButton = new Button([
					"color" => Color::LIGHT_BORDER,
					"icon" => Icon::SETTING,
					"text" => $button['caption'],
					"link" => $button['href'],
					"classList" => [$button['class']],
				]);
				$settingsButton->addAttribute('id', $button['id']);
				$settingsButton->addAttribute('style', $button['visible'] ? '' : 'display: none;');

				Toolbar::addButton($settingsButton, $button['location']);

				?>
				<script>
					const button = BX('sender-ui-buttons-settings');
					var popup = BX.PopupMenu.create(
						'sender-ui-buttons-settings',
						button,
						[{
							id: 'export',
							text: '<?=htmlspecialcharsbx(Loc::getMessage('SENDER_UI_BUTTON_PANEL_EXPORT'))?>',
							onclick() {
								let s = window.location.href;
								s += window.location.href.includes('?') ? '&' : '?';
								s += 'export=csv&ncc=1';
								window.location = s;
								popup.close();
							},
						}],
					);
					BX.bind(button, 'click', popup.show.bind(popup));
				</script>
				<?php
			}
			elseif($button['type'] === 'add')
			{
				$button['location'] = $button['location'] ?? ButtonLocation::AFTER_TITLE;
				$onClick = !empty($button['onclick'])
					? htmlspecialcharsbx($button['onclick'])
					: sprintf("BX.Sender.Page.open('%s'); return false;", CUtil::JSEscape(htmlspecialcharsbx($button['href'] ?? '')))
				;
				$addButton = new Button([
					"text" => $button['caption'],
					"link" => $button['href'],
					"color" => Color::SUCCESS,
					"classList" => [$button['class']],
				]);
				$addButton->addAttribute('id', $button['id']);
				$addButton->addAttribute('onclick', $onClick);
				$addButton->addAttribute('style', $button['visible'] ? '' : 'display: none;');

				Toolbar::addButton($addButton, $button['location']);
			}
			elseif($button['type'] === 'abuses')
			{
				if(!Integration\Bitrix24\Service::isPortal())
				{
					continue;
				}

				$button['caption'] = $button['caption'] ?: Loc::getMessage('SENDER_UI_BUTTON_PANEL_ABUSES');
				$button['counter'] = $button['counter'] ?? Model\AbuseTable::getCountOfNew();
				$button['location'] = $button['location'] ?? ButtonLocation::RIGHT;
				$button['id'] = $button['id'] ?? '';
				$href = sprintf("BX.Sender.Page.open('%s'); return false;", CUtil::JSEscape(htmlspecialcharsbx($button['href'])));

				$abusesButton = new Button([
					"link" => $button['href'],
					"icon" => Icon::INFO,
					"color" => Color::LIGHT_BORDER,
					"classList" => [$button['class']],
				]);
				$abusesButton->addAttribute('title', $button['caption']);
				$abusesButton->addAttribute('id', $button['id']);
				$abusesButton->addAttribute('onclick', $href);
				$abusesButton->setCounter($button['counter']);

				Toolbar::addButton($abusesButton, $button['location']);
				?>
				<script>
					BX.ready(function() {
						top.BX.addCustomEvent('onSenderAbuseCountReset', () => {
							BX.remove(BX('sender-abuse-counter'));
						});
					});
				</script>
				<?php
			}
			elseif($button['type'] === 'feedback')
			{
				if(!Integration\Bitrix24\Service::isCloud())
				{
					continue;
				}

				$button['location'] = $button['location'] ?? ButtonLocation::RIGHT;
				$feedbackButton = new Button([
					"text" => Loc::getMessage('SENDER_UI_BUTTON_PANEL_FEEDBACK'),
					"link" => $button['href'],
					"color" => Color::LIGHT_BORDER,
				]);
				$feedbackButton->addAttribute('id', 'SENDER_BUTTON_FEEDBACK');
				$feedbackButton->addAttribute('style', $button['visible'] ? '' : 'display: none;');
				Toolbar::addButton($feedbackButton, $button['location']);
				\CJSCore::Init('sender_b24_feedback');

				?>
				<script>
					BX.ready(function() {
						BX.ready(function () {
							BX.Sender.B24Feedback.init(
								<?=Json::encode(['b24_plan' => \CBitrix24::getLicenseType(), 'b24_zone' => \CBitrix24::getPortalZone(),])?>
							);
						})
					});
				</script>
				<?php
			}
			elseif($button['type'] === 'ui-feedback')
			{
				$feedbackParams = Json::encode($button['feedbackParams']);

				Toolbar::addButton([
					"color" => Color::LIGHT_BORDER,
					"click" => new JsCode(
						"BX.UI.Feedback.Form.open({$feedbackParams});"
					),
					"text" => Loc::getMessage('SENDER_UI_BUTTON_PANEL_FEEDBACK'),
				]);
			}
			else
			{
				$defaultButton = new Button([
					"text" => $button['caption'],
					"link" => $button['href'],
					"color" => Color::LIGHT_BORDER,
					"classList" => [$button['class']],
				]);
				if($button['href'] && !empty($button['sliding']))
				{
					$defaultButton->addAttribute('onclick', sprintf(
						"BX.Sender.Page.open('%s'); return false;",
						CUtil::JSEscape(htmlspecialcharsbx($button['href']))
					));
				}
				$defaultButton->addAttribute('id', $button['id']);
				$defaultButton->addAttribute('style', $button['visible'] ? '' : 'display: none;');
				Toolbar::addButton($defaultButton, $button['location']);
			}
		}
	}
}
