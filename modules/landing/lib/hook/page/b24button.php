<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Crm\SiteButton;
use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Socialservices\ApClient;

Loc::loadMessages(__FILE__);

class B24button extends \Bitrix\Landing\Hook\Page
{
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
		elseif (Manager::isB24Connector())
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
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$items += $this->getButtons();
		}


		return array(
			'CODE' => new Field\Select('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_B24BUTTONCODE'),
				'options' => $items
			)),
			'COLOR' => new Field\Select('COLOR', array(
				'title' => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR'),
				'options' => array(
					'site' => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_SITE'),
					'button' => Loc::getMessage('LANDING_HOOK_B24BUTTONCOLOR_BUTTON')
				)
			))
		);
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

		return trim($this->fields['CODE']) != '';
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
			\Bitrix\Landing\Manager::setPageView(
				'BeforeBodyClose',
				'<script data-skip-moving="true">
					(function(w,d,u,b){ \'use strict\';
					var s=d.createElement(\'script\');var r=(Date.now()/1000|0);s.async=1;s.src=u+\'?\'+r;
					var h=d.getElementsByTagName(\'script\')[0];h.parentNode.insertBefore(s,h);
				})(window,document,\'' . $code . '\');
				</script>'
			);
			if ($this->fields['COLOR'] != 'button')
			{
				\Bitrix\Landing\Manager::setPageView(
					'BodyClass',
					'landing-b24button-use-style'
				);
			}
		}
	}
}
