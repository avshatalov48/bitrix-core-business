<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;

class Fonts extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'CODE' => new Field\Textarea('CODE', array())
		);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return trim($this->fields['CODE']->getValue()) != '';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		// @fix for 101643
		$this->fields['CODE'] = str_replace(
			[
				'st yle',
				'onl oad',
				'li nk'
			],
			[
				'style',
				'onload',
				'link'
			],
			$this->fields['CODE']
		);
		\Bitrix\Landing\Manager::setPageView(
			'BeforeHeadClose',
			$this->fields['CODE']
		);
	}
	
	// dbg: speed: for find fonts in content and not adding all
	private static function getCustomIconFonts()
	{
		$pathTemplate24 = '/bitrix/templates/';
		$pathTemplate24 .= Manager::getTemplateId(Manager::getMainSiteId());
		
		return [
			'fontAwesome' => [
				'regexp' => '/fa-\w*/',
				'css' => [$pathTemplate24 . '/assets/vendor/icon-awesome/css/font-awesome.css'],
				'font' => [
					$pathTemplate24 . '/assets/vendor/icon-awesome/fonts/fontawesome-webfont.woff2?v=4.7.0',
					$pathTemplate24 . '/assets/vendor/icon-awesome/fonts/fontawesome-webfont.woff?v=4.7.0',
				],
			],
			'fontEtLine' => [
				'regexp' => '/et-icon-\w*/',
				'css' => [$pathTemplate24 . '/assets/vendor/icon-etlinefont/style.css'],
				'font' => [$pathTemplate24 . '/assets/vendor/icon-etlinefont/fonts/et-line.woff'],
			],
			'fontHS' => [
				'regexp' => '/hs-icon-\w*/',
				'css' => [$pathTemplate24 . '/assets/vendor/icon-hs/style.css'],
				'font' => [$pathTemplate24 . '/assets/vendor/icon-hs/fonts/hs-icons.woff'],
			],
			'fontIconLineSimple' => [
				'regexp' => '/[^-]icon-\w*/',
				'css' => [$pathTemplate24 . '/assets/vendor/icon-line/css/simple-line-icons.css'],
				'font' => [
					$pathTemplate24 . '/assets/vendor/icon-line/fonts/simple-line-icons.woff2?v=2.4.0',
					$pathTemplate24 . '/assets/vendor/icon-line/fonts/simple-line-icons.woff?v=2.4.0',
				],
			],
			'fontIconLinePro' => [
				'regexp' => '/icon-(\w*-){1,2}\d{3}/',
				'css' => [$pathTemplate24 . '/assets/vendor/icon-line-pro/style.css'],
				'font' => [
					$pathTemplate24 . '/assets/vendor/icon-line-pro/christmas/webfont/fonts/cristmas.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/clothes/webfont/fonts/clothes.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/communication/webfont/fonts/communication-48-x-48.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/education/webfont/fonts/education-48.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/electronics/webfont/fonts/electronics.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/finance/webfont/fonts/finance.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/food/webfont/fonts/food-48.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/furniture/webfont/fonts/furniture.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/hotel-restaurant/webfont/fonts/hotel-restaurant.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/media/webfont/fonts/media.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/medical/webfont/fonts/medical-and-health.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/music/webfont/fonts/music.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/real-estate/webfont/fonts/real-estate.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/science/webfont/fonts/science.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/sports/webfont/fonts/sports-48-x-48.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/travel/webfont/fonts/travel.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/weather/webfont/fonts/weather.woff',
					$pathTemplate24 . '/assets/vendor/icon-line-pro/transport/webfont/fonts/transport.woff',
				],
			],
		];
	}
}
