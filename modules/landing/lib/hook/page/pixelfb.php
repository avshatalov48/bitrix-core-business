<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class PixelFb extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_PLACEHOLDER2')
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
'<!-- Facebook Pixel Code -->
<script data-skip-moving="true">
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,\'script\',
  \'https://connect.facebook.net/en_US/fbevents.js\');
  fbq(\'init\', \'' . $counter . '\');
  fbq(\'track\', \'PageView\');
</script>
<!-- End Facebook Pixel Code -->'
			);
			Manager::setPageView(
				'AfterBodyOpen',
				'<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' .
				$counter . '&ev=PageView&noscript=1"/></noscript>'
			);
		}
	}
}
