<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Seo\BusinessSuite\ExtensionFacade;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Landing\Field;
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
		$helpUrl = \Bitrix\Landing\Help::getHelpUrl('PIXEL');
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_USE')
			)),
			'COUNTER' => new Field\Text('COUNTER', array(
				'title' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_COUNTER'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_PIXEL_FB_PLACEHOLDER2'),
				'help' => $helpUrl
					? '<a href="' . $helpUrl . '" target="_blank">' .
					  		Loc::getMessage('LANDING_HOOK_PIXEL_FB_HELP') .
				  		'</a>'
					: ''
			))
		);
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
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		if (!$this->isPage() && Manager::isB24())
		{
			return true;
		}

		return $this->fields['USE']->getValue() == 'Y';
	}

	/**
	 * Returns global business pixel (from SEO module).
	 * @return string|null
	 */
	private static function getBusinessPixelFromSeo(): ?string
	{
		if (\Bitrix\Main\Loader::includeModule('seo'))
		{
			$businessSuite = ExtensionFacade::getInstance();
			if ($businessSuite->isInstalled())
			{
				return $businessSuite->getCurrentInstalls()->getPixel();
			}
		}

		return null;
	}

	/**
	 * Returns global business pixel.
	 * @return string|null
	 */
	private function getBusinessPixel(): ?string
	{
		$pixelStored = Manager::getOption('business_pixel');
		if ($pixelStored === null)
		{
			$pixelStored = $this->getBusinessPixelFromSeo();
			Manager::setOption('business_pixel', $pixelStored);
		}

		return $pixelStored;
	}

	/**
	 * Event handler on business pixel global change.
	 * @return void
	 */
	public static function changeBusinessPixel(): void
	{
		if (self::getBusinessPixelFromSeo() !== Manager::getOption('business_pixel'))
		{
			Option::delete('landing', ['name' => 'business_pixel']);
			Manager::clearCache();
		}
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

		$zone = '';
		if (Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ru")
			&& !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/ua"))
		{
			$zone = 'ru';
		}
		if ($zone === 'ru')
		{
			return;
		}

		$counter = null;
		$businessPixel = $this->getBusinessPixel();

		if ($this->fields['USE']->getValue() === 'Y')
		{
			$counter = \htmlspecialcharsbx(trim($this->fields['COUNTER']));
			$counter = \CUtil::jsEscape($counter);
		}

		if (!$counter || $counter === $businessPixel)
		{
			$counter = $businessPixel;
			$businessPixel = null;
		}

		if ($counter)
		{
			Cookies::addCookieScript(
				'fbp',
				'!function(f,b,e,v,n,t,s)
				{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};
				if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';n.agent=\'plbitrix\';
				n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;s=b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t,s)}(window, document,\'script\',
				\'https://connect.facebook.net/en_US/fbevents.js\');
				fbq(\'init\', \'' . $counter . '\');
				fbq(\'track\', \'PageView\');'.
				($businessPixel
					? "\n				fbq('init', '{$businessPixel}');" .
					  "\n				fbq('track', 'PageView');"
					: '')
			);
			Manager::setPageView(
				'Noscript',
				'<noscript>
					<img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=' .$counter . '&ev=PageView&noscript=1"/>
				</noscript>'
			);
		}
	}
}
