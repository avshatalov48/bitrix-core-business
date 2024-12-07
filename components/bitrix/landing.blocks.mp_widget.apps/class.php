<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetApps extends LandingBlocksMainpageWidgetBase
{
	private const PATH = 'https://dl.bitrix24.com/b24/bitrix24_desktop';

	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_TITLE_MOBILE' => '--widget-color-title-mobile',
		'COLOR_TITLE_DESKTOP' => '--widget-color-title-desktop',
		'COLOR_TEXT_MOBILE' => '--widget-color-text-mobile',
		'COLOR_TEXT_DESKTOP' => '--widget-color-text-desktop',
		'COLOR_BUTTON_MOBILE' => '--widget-color-button-mobile',
		'COLOR_BUTTON_MOBILE_V2' => '--widget-color-button-mobile-v2',
		'COLOR_BUTTON_TEXT_MOBILE' => '--widget-color-button-text-mobile',
		'COLOR_BUTTON_DESKTOP' => '--widget-color-button-desktop',
		'COLOR_BUTTON_DESKTOP_V2' => '--widget-color-button-desktop-v2',
		'COLOR_BUTTON_TEXT_DESKTOP' => '--widget-color-button-text-desktop',
	];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TITLE_MOBILE', Loc::getMessage('LANDING_WIDGET_CLASS_APPS_MOBILE_TITLE'));
		$this->checkParam('TITLE_DESKTOP', Loc::getMessage('LANDING_WIDGET_CLASS_APPS_DESKTOP_TITLE'));
		//style params
		$this->checkParam('COLOR_TITLE_MOBILE', '#333333');
		$this->checkParam('COLOR_TITLE_DESKTOP', '#333333');
		$this->checkParam('COLOR_TEXT_MOBILE', '#6a737f');
		$this->checkParam('COLOR_TEXT_DESKTOP', '#6a737f');
		$this->checkParam('COLOR_BUTTON_MOBILE', '#2fc6f6');
		$this->checkParam('COLOR_BUTTON_MOBILE_V2', 'var(--primary)');
		$this->checkParam('COLOR_BUTTON_TEXT_MOBILE', '#ffffff');
		$this->checkParam('COLOR_BUTTON_DESKTOP', '#2fc6f6');
		$this->checkParam('COLOR_BUTTON_DESKTOP_V2', 'var(--primary)');
		$this->checkParam('COLOR_BUTTON_TEXT_DESKTOP', '#ffffff');

		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}

		$this->getData();

		parent::executeComponent();
	}

	protected function getData(): void
	{
		$this->arResult['TITLE_MOBILE'] = $this->arParams['TITLE_MOBILE'];
		$this->arResult['TITLE_DESKTOP'] = $this->arParams['TITLE_DESKTOP'];

		$os = $this->getOS();
		if ($os !== null)
		{
			$this->arResult['OS'] = $os;
			$ext = $this->getExtension($os);
			if ($ext !== null)
			{
				$this->arResult['DESKTOP_APP_LINK'] = self::PATH . $ext;
			}
		}

		$osName = $this->getOSName($os);
		if (isset($osName))
		{
			$this->arResult['OS_NAME'] = $osName;
		}
	}

	protected function getOS(): string|null
	{
		$osArray = [
			'WIN' => 'Win',
			'MAC' => '(Mac_PowerPC)|(Macintosh)|(Mac OS X)',
			'LIN' => '(Linux)|(X11)',
		];

		foreach ($osArray as $os => $pattern)
		{
			if (preg_match('/' . $pattern . '/i', $_SERVER['HTTP_USER_AGENT']))
			{
				return $os;
			}
		}

		return null;
	}

	protected function getExtension($os): string|null
	{
		if ($os === 'MAC')
		{
			return '.dmg';
		}
		if ($os === 'WIN')
		{
			return '.exe';
		}
		if ($os === 'LIN')
		{
			return '.deb';
		}

		return null;
	}

	protected function getOSName($os): string|null
	{
		if ($os === 'MAC')
		{
			return 'MacOS';
		}

		if ($os === 'WIN')
		{
			return 'Windows';
		}

		if ($os === 'LIN')
		{
			return 'Linux';
		}

		return null;
	}
}
