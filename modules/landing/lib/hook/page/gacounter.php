<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
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
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('GACOUNTER');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_GACOUNTER_PLACEHOLDER_1')
			)),
			'COUNTER_GA4' => new Field\Text('COUNTER_GA4', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_GACOUNTER_PLACEHOLDER_2'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
							Loc::getMessage('LANDING_HOOK_DETAIL_HELP') .
						'</a>'
					: ''
			)),
			'SEND_CLICK' => new Field\Checkbox('SEND_CLICK', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_SEND_CLICK')
			)),
			'CLICK_TYPE' => new Field\Select('CLICK_TYPE', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_CLICK_TYPE'),
				'options' => [
					'href' => Loc::getMessage('LANDING_HOOK_GACOUNTER_CLICK_TYPE_HREF'),
					'text' => Loc::getMessage('LANDING_HOOK_GACOUNTER_CLICK_TYPE_TEXT'),
				]
			)),
			'SEND_SHOW' => new Field\Checkbox('SEND_SHOW', array(
				'title' => Loc::getMessage('LANDING_HOOK_GACOUNTER_SEND_SHOW'),
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

		if ($this->fields['USE']->getValue() != 'Y')
		{
			return;
		}

		//Universal Analytics
		$this->setCounter($this->fields['COUNTER']);
		//Google Analytics 4
		$this->setCounter($this->fields['COUNTER_GA4']);

		// send analytics
		$sendData = [];
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
			$clickType = $this->fields['CLICK_TYPE']->getValue();
			if (!$clickType)
			{
				$clickType = 'text';
			}
			if ($clickType)
			{
				\Bitrix\Landing\Manager::setPageView(
					'BodyTag',
					' data-event-tracker-label-from="' . \htmlspecialcharsbx($clickType) . '"'
				);
			}
		}
	}

	/**
	 * Sets counter to the page.
	 * @param string $counter Counter code.
	 * @return void
	 */
	public static function setCounter(string $counter): void
	{
		$counter = \htmlspecialcharsbx(trim($counter));
		$counter = \CUtil::jsEscape($counter);

		if (!$counter)
		{
			return;
		}

		Manager::setPageView(
			'AfterHeadOpen',
			'<script async 
					src="https://www.googletagmanager.com/gtag/js?id=' . $counter . '" 
					data-skip-moving="true"
				></script>'
		);
		Cookies::addCookieScript(
			'ga',
			'window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments)};
				gtag("js", new Date());
				gtag("config", "' . $counter . '");'
		);
	}
}
