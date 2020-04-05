<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

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
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
		return $request->get('landing_mode') == 'preview';
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
'<!-- Yandex.Metrika counter -->
<script type="text/javascript" data-skip-moving="true">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter' . $counter . ' = new Ya.Metrika({
                    id:' . $counter . ',
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true,
                    trackHash:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/' . $counter . '" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->'
			);
		}
	}
}
