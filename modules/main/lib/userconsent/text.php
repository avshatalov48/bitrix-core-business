<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\BinaryString;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Text
 * @package Bitrix\Main\UserConsent
 */
class Text
{
	/**
	 * Replace template by data.
	 *
	 * @param string $template Template text.
	 * @param array $data Replace data.
	 * @param bool $isCut Is cut areas if empty data.
	 * @return string
	 */
	public static function replace($template, array $data, $isCut = false)
	{
		$from = array();
		$to = array();

		$dataTmp = array();
		foreach ($data as $key => $value)
		{
			$key = mb_strtolower($key);
			if (is_array($value))
			{
				$value = self::formatArrayToText($value);
			}
			else
			{
				$value = (string) $value;
			}

			$dataTmp[$key] = $value;
		}
		$data = $dataTmp;

		foreach ($data as $key => $value)
		{
			$from[] = '%' . $key . '%';
			$to[] = $value;
		}

		$template = str_replace($from, $to, $template);

		if ($isCut)
		{
			$template = self::cut($template, $data);
		}

		return $template;
	}

	protected static function cut($template, array $data)
	{
		$from = array();
		$to = array();

		$matchResult = preg_match_all('/\%cut\.([A-Za-z0-9_\.]*)\.(start|end)\%/', $template, $matches, PREG_OFFSET_CAPTURE);
		if (!$matchResult)
		{
			return $template;
		}

		$cut = array();
		foreach ($matches[0] as $key => $match)
		{
			$tag = $match[0];
			$pos = $match[1];
			$var = $matches[1][$key][0];
			$mod = $matches[2][$key][0];

			$from[] = $tag;
			$to[] = '';

			if (!isset($cut[$var]))
			{
				$cut[$var] = array('start' => array(), 'end' => array());
			}

			if (!isset($cut[$var][$mod]))
			{
				continue;
			}

			if ($mod == 'end')
			{
				$pos += BinaryString::getLength($tag);
			}

			$cut[$var][$mod][] = $pos;
		}

		$items = array();
		foreach ($cut as $key => $item)
		{

			foreach ($item['start'] as $index => $position)
			{
				if (!isset($item['end'][$index]))
				{
					continue;
				}

				$sortBy = $item['end'][$index];
				$items[$sortBy] = array(
					'key' => $key,
					'start' => $item['start'][$index],
					'end' => $item['end'][$index]
				);
			}
		}

		krsort($items);
		foreach ($items as $item)
		{
			if (!empty($data[$item['key']]))
			{
				continue;
			}

			$start = $item['start'];
			$end = $item['end'];
			if ($start <= 0 || $end <= 0)
			{
				continue;
			}

			$template = BinaryString::getSubstring($template, 0, $start) . BinaryString::getSubstring($template, $end);
		}

		return str_replace($from, $to, $template);
	}

	/**
	 * Format array to text.
	 *
	 * @param array $list List.
	 * @return string
	 */
	public static function formatArrayToText(array $list)
	{
		$result = array();
		$num = 0;
		$count = count($list);

		foreach ($list as $item)
		{
			$num++;
			$isLast = $num >= $count;
			$result[] = '- ' . $item . ($isLast ? '.': ';');
		}

		return implode("\n", $result);
	}
}