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
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => 'Google Tag Manager'
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => 'Google Tag Manager',
				'placeholder' => Loc::getMessage('LANDING_HOOK_GTM_PLACEHOLDER')
			))
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			Manager::setPageView('AfterHeadOpen',
				'<!-- Google Tag Manager --><script data-skip-moving="true">' .
				'(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({' .
				'\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});' .
				'var f=d.getElementsByTagName(s)[0],j=d.createElement(s),' .
				'dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;' .
				'j.src=\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;' .
				'f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\'' . $counter . '\');' .
				'</script><!-- End Google Tag Manager -->');
			Manager::setPageView('AfterBodyOpen', '<!-- Google Tag Manager (noscript) --><noscript>' .
				'<iframe src="https://www.googletagmanager.com/ns.html?id=' . $counter . '" ' .
				'height="0" width="0" style="display:none;visibility:hidden"></iframe>' .
				'</noscript><!-- End Google Tag Manager (noscript) -->');
		}
	}
}
