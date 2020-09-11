<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GTM extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('GTM');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => 'Google Tag Manager'
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => 'Google Tag Manager',
				'placeholder' => Loc::getMessage('LANDING_HOOK_GTM_PLACEHOLDER'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
					 		Loc::getMessage('LANDING_HOOK_DETAIL_HELP') .
					  '</a>'
					: ''
			))
		);
	}

	/**
	 * Enable only in high plan.
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
		return !\Bitrix\Landing\Restriction\Manager::isAllowed(
			'limit_sites_google_analytics'
		);
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
		if ($this->execCustom())
		{
			return;
		}

		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			Cookies::addCookieScript(
				'gtm',
				'(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});
				var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),
				dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';
				j.async=true;
				j.src=\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;
				f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\'' . $counter . '\');'
			);
			Manager::setPageView(
				'Noscript',
				'<noscript>
					<iframe src="https://www.googletagmanager.com/ns.html?id=' . $counter . '" height="0" width="0" style="display:none;visibility:hidden"></iframe>
				</noscript>');
		}
	}
}
