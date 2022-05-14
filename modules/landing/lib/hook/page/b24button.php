<?php
namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing;
use Bitrix\Crm\SiteButton;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;
use Bitrix\Socialservices\ApClient;

Loc::loadMessages(__FILE__);

class B24button extends \Bitrix\Landing\Hook\Page
{
	protected const COLOR_TYPE_BUTTON = 'button';
	protected const COLOR_TYPE_SITE = 'site';
	protected const COLOR_TYPE_CUSTOM = 'custom';
	protected const COLOR_DEFAULT = '#03c1fe';

	/**
	 * Get script url from script-code.
	 * @param string $script Script code.
	 * @return string
	 */
	protected static function getScriptUrl($script)
	{
		if (preg_match('/\}\)\(window,document,\'([^\']+)\'\);/is', $script, $matches))
		{
			return $matches[1];
		}

		return '';
	}

	/**
	 * Get b24 buttons.
	 * @return array
	 */
	public static function getButtons(): array
	{
		static $buttons = null;
		if ($buttons !== null)
		{
			return $buttons;
		}

		$buttons = [];
		foreach (self::getButtonsData() as $button)
		{
			$key = self::getScriptUrl($button['SCRIPT']);
			if ($key)
			{
				$buttons[$key] = \htmlspecialcharsbx($button['NAME']);
			}
		}

		return $buttons;
	}

	/**
	 * Get raw data of b24 buttons
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getButtonsData(): ?array
	{
		static $buttonsData = null;

		if ($buttonsData !== null)
		{
			return $buttonsData;
		}

		$buttonsData = [];

		// b24 crm
		if (Loader::includeModule('crm'))
		{
			// if buttons not exist (new portal) - create before
			if (SiteButton\Preset::checkVersion())
			{
				$preset = new SiteButton\Preset();
				$preset->install();
			}

			$buttonsData = SiteButton\Manager::getList([
				'filter' => ['=ACTIVE' => 'Y'],
				'select' => [
					'ID', 'SECURITY_CODE', 'NAME'
				],
				'order' => [
					'ID' => 'DESC'
				]
			]);
		}
		// site manager
		elseif (Landing\Manager::isB24Connector())
		{
			$client = ApClient::init();
			if ($client)
			{
				$res = $client->call('crm.button.list');
				if (isset($res['result']) && is_array($res['result']))
				{
					$buttonsData = $res['result'];
				}
			}
		}

		return $buttonsData;
	}

	/**
	 * Find button ID by script code
	 * @param $code - script for button
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getButtonIdByCode($code)
	{
		foreach (self::getButtonsData() as $button)
		{
			if ($code === self::getScriptUrl($button['SCRIPT']))
			{
				return $button['ID'];
			}
		}

		return false;
	}

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$items = array(
			'N' => Loc::getMessage('LANDING_HOOK_B24BUTTONCODE_NO')
		);

		// show connectors only for edit
		if ($this->isEditMode())
		{
			$context = Application::getInstance()->getContext();
			$server = $context->getServer();
			$items += $this->getButtons();
		}


		return [
			'CODE' => new Landing\Field\Select('CODE', [
				'title' => Loc::getMessage('LANDING_HOOK_B24BUTTONCODE'),
				'options' => $items
			]),
			'COLOR' => new Landing\Field\Select('COLOR', [
				'title' => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR'),
				'options' => [
					self::COLOR_TYPE_SITE => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_SITE'),
					self::COLOR_TYPE_BUTTON => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_BUTTON'),
					self::COLOR_TYPE_CUSTOM => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_CUSTOM')
				]
			]),
			'COLOR_VALUE' => new Landing\Field\Text('COLOR_VALUE', [
				'title' => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_VALUE'),
				'default' => self::COLOR_DEFAULT,
			])
		];
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		return
			trim($this->fields['CODE']) !== ''
			&& !self::isTelegramWebView();
	}

	/**
	 * Check if page opened from telegram integration
	 * @return bool
	 */
	protected static function isTelegramWebView(): bool
	{
		if (!$application = Application::getInstance())
		{
			return false;
		}
		$session = $application->getSession();
		$request = $application->getContext()->getRequest();
		if ($request->get('tgWebApp') !== null)
		{
			$session->set('tgWebApp', 'Y');

			return true;
		}

		return $session->has('tgWebApp') && $session->get('tgWebApp') === 'Y';
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return boolean
	 */
	public function enabledInEditMode()
	{
		return false;
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		if ($this->execCustom())
		{
			return;
		}

		$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		if ($code != 'N')
		{
			Landing\Manager::setPageView(
				'BeforeBodyClose',
				'<script data-skip-moving="true">
					(function(w,d,u,b){ \'use strict\';
					var s=d.createElement(\'script\');var r=(Date.now()/1000|0);s.async=1;s.src=u+\'?\'+r;
					var h=d.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);
				})(window,document,\'' . $code . '\');
				</script>'
			);

			// set COLOR
			if ($this->fields['COLOR']->getValue() !== self::COLOR_TYPE_BUTTON)
			{
				Landing\Manager::setPageView(
					'BodyClass',
					'landing-b24button-use-style'
				);

				$color =
					(
						$this->fields['COLOR']->getValue() === self::COLOR_TYPE_CUSTOM
						&& !empty($this->fields['COLOR_VALUE']->getValue())
					)
					? Theme::prepareColor($this->fields['COLOR_VALUE']->getValue())
					: 'var(--primary)';

				Page\Asset::getInstance()->addString(
					"<style type=\"text/css\">
							:root {
								--theme-color-b24button: {$color};
							}
						</style>",
					false,
					Page\AssetLocation::BEFORE_CSS
				);
			}
		}
	}

}
