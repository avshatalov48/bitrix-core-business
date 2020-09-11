<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class HeadBlock extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Last inserted code to the site or to the page.
	 * @var string
	 */
	protected static $lastInsertedCode = null;

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_USE2')
			)),
			'CODE' => new Field\Textarea('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CODE'),
				'help' => Loc::getMessage('LANDING_HOOK_HEADBLOCK_CODE_HELP3'),
				'placeholder' => '<script>
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];
</script>'
			))
		);
	}

	/**
	 * Gets last inserted code.
	 * @return string
	 */
	public static function getLastInsertedCode()
	{
		return self::$lastInsertedCode;
	}

	/**
	 * Enable only in high plan or not.
	 * @return boolean
	 */
	public function isFree()
	{
		return false;
	}

	/**
	 * Locked or not current hook in free plan.
	 * @return bool
	 */
	public function isLocked()
	{
		return $this->isLockedFeature();
	}

	/**
	 * Locked or not current hook in free plan.
	 * @return bool
	 */
	public static function isLockedFeature()
	{
		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$checkFeature = \Bitrix\Landing\Restriction\Manager::isAllowed(
				'limit_sites_html_js'
			);
			if ($checkFeature)
			{
				return false;
			}
			$dateCreate = \Bitrix\Main\Config\Option::get(
				'main', '~controller_date_create'
			);
			// for all portals early than 01.07.2019, feature are available
			if ($dateCreate < 1562000000)
			{
				// this option will be set after downgrade in bitrix24
				return Manager::getOption('html_disabled', 'N') == 'Y';
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_HEADBLOCK_NAME2');
	}

	/**
	 * Get sort of block (execute order).
	 * @return int
	 */
	public function getSort()
	{
		return 500;
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->isLocked())
		{
			return false;
		}

		if ($this->issetCustomExec())
		{
			return true;
		}

		return $this->fields['USE']->getValue() == 'Y';
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
		if ($this->isLocked())
		{
			return;
		}

		if ($this->execCustom())
		{
			return;
		}

		$code = trim($this->fields['CODE']);

		if ($code != '')
		{
			self::$lastInsertedCode = $code;
			$code = str_replace(
				'<script',
				'<script data-skip-moving="true"', $code
			);
			\Bitrix\Main\Page\Asset::getInstance()->addString($code);
		}
	}
}
