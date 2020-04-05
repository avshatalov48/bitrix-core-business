<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class YaCounter extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_YACOUNTER_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_YACOUNTER_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_YACOUNTER_PLACEHOLDER')
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
			Manager::setPageView('AfterHeadOpen',
'<!-- Yandex.Metrika counter -->
<script type="text/javascript" data-skip-moving="true">
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(' . $counter . ', "init", {
        id:' . $counter . ',
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true,
		trackHash:true
   });
</script>
<!-- /Yandex.Metrika counter -->'
			);
			Manager::setPageView(
				'AfterBodyOpen',
				'<noscript><div><img src="https://mc.yandex.ru/watch/' . $counter . '" style="position:absolute; left:-9999px;" alt="" /></div></noscript>'
			);
		}
	}
}
