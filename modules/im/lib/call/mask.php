<?php
namespace Bitrix\Im\Call;

use Bitrix\Im\Common;
use Bitrix\Main\Localization\Loc;

class Mask
{
	private static $path = '/bitrix/js/im/images/masks';

	public static function get()
	{
		return array_merge(
			self::getBaseList()
		);
	}

	private static function getBaseList()
	{
		$result = [
			[
				'id' => 'bear1',
				'active' => true,
			],
			[
				'id' => 'owl2',
				'active' => true,
			],
			[
				'id' => 'fox1',
				'active' => true,
			],
			[
				'id' => 'panther1',
				'active' => true,
			],
			[
				'id' => 'polebear1',
				'active' => true,
			],
			[
				'id' => 'santa2',
				'active' => true,
			],
			[
				'id' => 'bear2',
				'active' => true,
			],
			[
				'id' => 'owl1',
				'active' => true,
			],
			[
				'id' => 'fox3',
				'active' => true,
			],
			[
				'id' => 'panther2',
				'active' => true,
			],
			[
				'id' => 'polebear2',
				'active' => true,
			],
			[
				'id' => 'santa4',
				'active' => true,
			],
			[
				'id' => 'bear4',
				'active' => true,
			],
			[
				'id' => 'owl3',
				'active' => true,
			],
			[
				'id' => 'fox2',
				'active' => true,
			],
			[
				'id' => 'panther4',
				'active' => true,
			],
			[
				'id' => 'polebear4',
				'active' => true,
			],
			[
				'id' => 'santa1',
				'active' => true,
			],
			[
				'id' => 'bear3',
				'active' => true,
			],
			[
				'id' => 'fox4',
				'active' => true,
			],
			[
				'id' => 'panther3',
				'active' => true,
			],
			[
				'id' => 'polebear3',
				'active' => true,
			],
			[
				'id' => 'santa3',
				'active' => true,
			],
		];

		foreach ($result as &$value)
		{
			$value['title'] = preg_replace_callback("/(\D+)(\d+)/i", static function($matches){
				return Loc::getMessage('IM_CALL_MASK_' . mb_strtoupper($matches[1]) . '_' . $matches[2]);
			}, $value['id']);

			$value['preview'] = self::$path . "/" . $value['id'] . '.png';

			if ($value['active'])
			{
				$version = isset($value['version']) ? '?v' . $value['version']: '';

				$value['mask'] = self::getCdnPath(). "/" . $value['id'] . '.bam.gz' . $version;

				$value['background'] = preg_replace_callback("/(\D+)(\d+)/i", static function($matches){
					return self::getCdnPath() . "/" . $matches[1] . '.jpg';
				}, $value['id']);
			}
			else
			{
				$value['mask'] = '';
				$value['background'] = '';
			}
			unset($value['version']);
		}

		return $result;
	}

	public static function getCdnPath()
	{
		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		if (!empty($settings['call']['mask_url']) && mb_strpos($settings['call']['mask_url'], 'https') === 0)
		{
			return $settings['call']['mask_url'];
		}

		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			$zone = \CBitrix24::getCurrentAreaConfig();
			$domain = 'www' . $zone['DEFAULT_DOMAIN'];
		}
		else
		{
			$zoneCode = LANGUAGE_ID;
			if (in_array(LANGUAGE_ID, ['ru', 'by', 'kz'], true))
			{
				$domain = 'www.bitrix24.' . LANGUAGE_ID;
			}
			else
			{
				$domain = 'www.bitrix24.com';
			}
		}

		return 'https://' . $domain . '/util/mask';
	}
}

