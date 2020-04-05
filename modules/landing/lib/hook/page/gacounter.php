<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GaCounter extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_GACOUNTER_PLACEHOLDER')
			)),
			'SEND_CLICK' => new Field\Checkbox('SEND_CLICK', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_SEND_CLICK')
			)),
			'SEND_SHOW' => new Field\Checkbox('SEND_SHOW', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_SEND_SHOW')
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
	 * Exec or not hook in edit mode.
	 * @return true
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
		$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
		$counter = \CUtil::jsEscape($counter);
		if ($counter)
		{
			\Bitrix\Main\Page\Asset::getInstance()->addString(
'<!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=' . $counter . '" data-skip-moving="true"></script>
<script type="text/javascript" data-skip-moving="true">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments)};
  gtag(\'js\', new Date());

  gtag(\'config\', \'' . $counter . '\');
</script>'
			);
		}
		// send analytics
		$sendData = array();
		if ($this->fields['SEND_CLICK']->getValue() == 'Y')
		{
			$sendData[] = 'click';
		}
		if ($this->fields['SEND_SHOW']->getValue() == 'Y')
		{
			$sendData[] = 'show';
		}
		if (!empty($sendData))
		{
			\Bitrix\Landing\Manager::setPageView(
				'BodyTag',
				' data-event-tracker=\'' . json_encode($sendData) . '\''
			);
		}
	}
}
