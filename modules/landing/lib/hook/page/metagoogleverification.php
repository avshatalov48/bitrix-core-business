<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MetaGoogleVerification extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('META_GOOGLE_VERIFICATION');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => 'Google Search Console'
			)),
			'META' => new Field\Text('META', array(
				'title' => 'Google Search Console',
				'placeholder' => '<meta name="google-site-verification" content="9fe8a037d323d50a5faed82923c1438f" />',
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
					 		Loc::getMessage('LANDING_HOOK_DETAIL_HELP') .
					  '</a>'
					: ''
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

		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return bool
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

		$meta = trim($this->fields['META']);

		if (preg_match('#^<meta\s+name="google-site-verification"\s+content="[a-z0-9_\-]+"\s+/*>$#i', $meta))
		{
			Manager::setPageView('BeforeHeadClose', $meta);
		}
	}
}
