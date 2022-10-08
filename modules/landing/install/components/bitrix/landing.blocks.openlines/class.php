<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Landing;
use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Socialservices\ApClient;
use Bitrix\Crm\UI\Webpack\Button;

/**
 * Class LandingBlocksOlComponent
 */
class LandingBlocksOlComponent extends \CBitrixComponent
{
	/**
	 * Base executable method.
	 * @return void
	 * @noinspection PhpMissingParentCallCommonInspection
	 */
	public function executeComponent(): void
	{
		// @hack: in this component we not init b24 button. We assume that it exists on the page.
		// If no button - widget will not work :(

		$this->arParams['ERRORS'] = [];
		$this->arParams['EDIT_MODE'] = Landing\Landing::getEditMode() ? 'Y' : 'N';

		if ($this->checkWidgetId())
		{
			$widgetsData = $this->getWidgetsForButton($this->arParams['BUTTON_ID']);
			$this->arParams['WIDGETS'] = $widgetsData['widgets'];
			$this->arParams['IS_MOBILE'] = $this->isMobile();
			$this->prepareWidgetsToPrint();
		}
		$this->includeComponentTemplate();
	}

	/**
	 * @return bool true if OK, false - if some error
	 */
	protected function checkWidgetId(): bool
	{
		if (!isset($this->arParams['BUTTON_ID']) || !$this->arParams['BUTTON_ID'])
		{
			if (empty(B24button::getButtonsData()))
			{
				$title = Loc::getMessage('LANDING_CMP_OL_NO_BUTTON');
				$text = '';
				if (Landing\Manager::isB24() && Loader::includeModule('crm'))
				{
					$link = '/crm/button/';
					$text = Loc::getMessage(
						'LANDING_CMP_OL_NO_BUTTON_CP',
						[
							'#LINK1#' => '<a href="'
								. $link
								. '" target="_blank" class="landing-trusted-link">',
							'#LINK2#' => '</a>',
						]
					);
				}
				else if (Landing\Manager::isB24Connector())
				{
					$link1 = '/bitrix/admin/module_admin.php';
					$link2 = '/bitrix/admin/b24connector_buttons.php?lang=' . LANGUAGE_ID;
					$text = Loc::getMessage(
						'LANDING_CMP_OL_NO_BUTTON_SM',
						[
							'#LINK1#' => '<a target="_blank" class="landing-trusted-link" href="' . $link1 . '">',
							'#LINK2#' => '</a>',
							'#LINK3#' => '<a target="_blank" class="landing-trusted-link" href="' . $link2 . '">',
							'#LINK4#' => '</a>',
						]
					);
				}
				$this->arParams['ERRORS'][] = [
					'title' => $title,
					'text' => $text,
				];
			}
			// something wrong
			else
			{
				$this->arParams['ERRORS'][] = [
					'title' => Loc::getMessage('LANDING_CMP_OL_NO_BUTTON_ID'),
					'text' => Loc::getMessage('LANDING_CMP_OL_NO_BUTTON_ID'),
				];
			}

			return false;
		}

		$errorData = [];
		
		if ($this->arParams["SITE_TYPE"] !== 'crm_forms')
		{
			$errorData['title'] = Loc::getMessage('LANDING_CMP_OL_BUTTON_NO_CHOOSE_SITE_1');
			$errorData['text'] = Loc::getMessage('LANDING_CMP_OL_BUTTON_NO_CHOOSE_SITE_TEXT_1');
			$urlParams = 'SITE_EDIT';
		}
		else
		{
			$errorData['title'] = Loc::getMessage('LANDING_CMP_OL_BUTTON_NO_CHOOSE_PAGE_1');
			$errorData['text'] = Loc::getMessage('LANDING_CMP_OL_BUTTON_NO_CHOOSE_PAGE_TEXT_1');
			$urlParams = 'LANDING_EDIT';
		}
		$errorData['button']['onclick'] = 'BX.PreventDefault(); BX.SidePanel.Instance.open(landingParams[\'PAGE_URL_LANDING_SETTINGS\'] + \'?PAGE=';
		$errorData['button']['onclick'] .= $urlParams . '\' + \'#b24widget\');';

		if ($this->arParams['BUTTON_ID'] === 'N')
		{
			$this->arParams['ERRORS'][] = [
				'title' => $errorData['title'],
				'text' => $errorData['text'],
				'button' => [
					'text' => Loc::getMessage('LANDING_CMP_OL_BUTTON_NO_CHOOSE_BUTTON'),
					'href' => '',
					'onclick' => $errorData['button']['onclick'],
				],
			];

			return false;
		}

		return true;
	}

	/**
	 * Get all channels for widget by ID
	 * @param $buttonId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getWidgetsForButton($buttonId): array
	{
		$widgets = [];

		if (Landing\Manager::isB24() && Loader::includeModule('crm'))
		{
			$button = Button::instance($buttonId);
			$button->configure();
			foreach ($button->getWidgets() as $widget)
			{
				$widgets['widgets'][] = $widget;
			}
		}

		// site manager
		elseif (Landing\Manager::isB24Connector())
		{
			$client = ApClient::init();
			if ($client)
			{
				$resWidgets = $client->call(
					'crm.button.widgets.get',
					[
						'ID' => $buttonId,
					]
				);
				if (empty($resWidgets['error']))
				{
					if (isset($resWidgets['result']) && is_array($resWidgets['result']))
					{
						$widgets = $resWidgets['result'];
					}
				}
				elseif ($resWidgets['error'] === 'ERROR_METHOD_NOT_FOUND')
				{
					$this->arParams['ERRORS'][] = [
						'title' => Loc::getMessage('LANDING_CMP_OL_BUTTON_REST_ERROR'),
						'text' => Loc::getMessage('LANDING_CMP_OL_BUTTON_REST_ERROR_DESC'),
					];
				}
				else
				{
					$this->arParams['ERRORS'][] = [
						'title' => $resWidgets['error'],
						'text' => $resWidgets['error_description'],
					];
				}
			}
		}

		return $widgets;
	}

	protected static function getAvailableChannelTypes(): array
	{
		return ['openline'];
	}

	protected function prepareWidgetsToPrint(): void
	{
		foreach ($this->arParams['WIDGETS'] as $key => $widget)
		{
			if (!in_array($widget['type'], self::getAvailableChannelTypes(), true))
			{
				unset($this->arParams['WIDGETS'][$key]);
				continue;
			}
			$classList = 'landing-b24-widget-button-social-item ' . implode(' ', $widget['classList']) . ' ';
			$classList = trim(str_replace(['ui-icon ', 'connector-icon-45 '], '', $classList));
			$this->arParams['WIDGETS'][$key]['classList'] = $classList;
			$this->arParams['WIDGETS'][$key]['title'] =
				HtmlFilter::encode(strip_tags($this->arParams['WIDGETS'][$key]['title']));
		}
	}

	protected function isMobile()
	{
		return false !== strpos($_SERVER['HTTP_USER_AGENT'], "Android")
			|| preg_match('#\biPhone.*Mobile|\biPod|\biPad#', $_SERVER['HTTP_USER_AGENT']);
	}
}