<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Crm\SiteButton\Preset;

Loc::loadMessages(__FILE__);

class B24button extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Exec or not hook in edit mode.
	 * @return true
	 */
	public function enabledInEditMode()
	{
		return false;
	}
	
	/**
	 * Get script url fromscript-code.
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
	public static function getButtons()
	{
		static $items = null;

		if ($items !== null)
		{
			return $items;
		}

		$items = array();

		// b24 crm
		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
//			if buttons not exist (new portal) - create before
			if (Preset::checkVersion())
			{
				$preset = new Preset();
				$preset->install();
			}
			
			$buttonList = \Bitrix\Crm\SiteButton\Manager::getList(array(
				'select' => array(
					'ID', 'SECURITY_CODE', 'NAME'
				),
				'order' => array(
					'ID' => 'DESC'
				)
			));
			foreach ($buttonList as $button)
			{
				$key = self::getScriptUrl($button['SCRIPT']);
				$items[$key] = $button['NAME'];
			}
		}
		// site manager
		elseif (
			\Bitrix\Main\Loader::includeModule('b24connector') &&
			\Bitrix\Main\Loader::includeModule('socialservices')
		)
		{
			$client = \Bitrix\Socialservices\ApClient::init();
			if ($client)
			{
				$res = $client->call('crm.button.list');
				if (isset($res['result']) && is_array($res['result']))
				{
					foreach ($res['result'] as $button)
					{
						$key = self::getScriptUrl($button['SCRIPT']);
						if ($key)
						{
							$items[$key] = \htmlspecialcharsbx($button['NAME']);
						}
					}
				}
			}
		}

		return $items;
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
		return trim($this->fields['CODE']) != '';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		if ($code != 'N')
		{
			\Bitrix\Main\Page\Asset::getInstance()->addString(
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
