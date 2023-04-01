<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset\Templates;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Message;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class Texts
 * @package Bitrix\Sender\Preset\Templates
 */
class Texts
{
	const IMAGE_DIR = '/images/sender/preset/template_v2/';

	/**
	 * Get codes.
	 *
	 * @return array|null
	 */
	public static function getCodes()
	{
		return array(
			'hot_unplanned',
			'unplanned',
			'hot_lead2client',
			'lead2client',
			'hot_inc_profits',
			'inc_profits',
			'hot_brand2brain',
			'brand2brain',
			'hot_repeated',
			'repeated',
			'hot_push2deal',
			'push2deal',
			'hot_wakeup',
			'wakeup',
			'hot_satisfy',
			'satisfy',
			'hot_birthday',
			'birthday',
			'hot_inform_event',
			'inform_event',
			'hot_invite2event',
			'invite2event',
		);
	}

	/**
	 * Get texts list by type.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public static function getListByType($type)
	{
		$result = array();
		foreach (self::getCodes() as $code)
		{
			$item = self::getByCode($code);
			if (empty($item['TYPES'][$type]))
			{
				continue;
			}

			$fields = $item['TYPES'][$type];
			unset($item['TYPES']);
			foreach ($fields as $key => $value)
			{
				$item[$key] = $value;
			}

			$result[] = $item;
		}

		return $result;
	}

	/**
	 * Get replace.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public static function replace($text = '')
	{
		static $replace = null;
		if ($replace === null)
		{
			$card = array();
			foreach (Integration\EventHandler::onSenderCompanyCard() as $card)
			{
				if (!isset($card['DATA']) || !is_array($card['DATA']))
				{
					continue;
				}
				$card = $card['DATA'];

				if (!$card['COMPANY_NAME'])
				{
					continue;
				}

				break;
			}

			if (!isset($card['COMPANY_NAME']) || !$card['COMPANY_NAME'])
			{
				$card['COMPANY_NAME'] = Loc::getMessage('SENDER_PRESET_TEMPLATE_DEFAULT_COMPANY');
			}

			if (!isset($card['PHONE']) || !$card['PHONE'])
			{
				$card['PHONE'] = Loc::getMessage('SENDER_PRESET_TEMPLATE_DEFAULT_PHONE');
			}

			$replace = array(
				'%COMPANY%' => $card['COMPANY_NAME'],
				'%PHONE_FORMATTED%' => $card['PHONE'],
				'%PHONE%' => preg_replace('/[^\d]/', '', $card['PHONE']),
			);
		}

		if (count($replace) === 0)
		{
			return $text;
		}

		return str_replace(array_keys($replace), array_values($replace), $text);
	}

	protected static function getMessage($code)
	{
		return self::replace(Loc::getMessage($code));
	}

	/**
	 * Get texts by code.
	 *
	 * @param string $code Code.
	 * @return array|null
	 */
	public static function getByCode($code)
	{
		$dictionary = array(
			Message\iBase::CODE_MAIL => array(
				'SUBJECT',
				'TEXT_HEAD',
				'TEXT_BODY'
			),
			Message\iBase::CODE_SMS => array(
				'TEXT'
			),
			Message\iBase::CODE_IM => array(
				'TEXT'
			),
			Message\iBase::CODE_AUDIO_CALL => array(
				'AUDIO_FILE'
			),
		);

		$isHot = mb_strpos($code, 'hot_') === 0;
		$iconPath = $isHot? mb_substr($code, 4) : $code;
		$iconPath = BX_ROOT . self::IMAGE_DIR . $iconPath . '.png';

		$code = mb_strtoupper($code);
		$result = array(
			'CODE' => $code,
			'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_' . $code . '_NAME'),
			'DESC' => Loc::getMessage('SENDER_PRESET_TEMPLATE_' . $code . '_DESC'),
			'HOT' => $isHot,
			'ICON' => $iconPath,
			'TYPES' => array(),
		);

		foreach ($dictionary as $type => $keys)
		{
			if (isset($result['TYPES'][$type])
				&& !is_array($result['TYPES'][$type]))
			{
				$result['TYPES'][$type] = array();
			}

			foreach ($keys as $key)
			{
				$msgId = 'SENDER_PRESET_' . $type . '_' . $code . '_' . $key;
				$result['TYPES'][$type][$key] = self::getMessage(mb_strtoupper($msgId));
			}
		}

		return (mb_strlen($result['NAME']) && !empty($result['TYPES'])) ? $result : null;
	}
}