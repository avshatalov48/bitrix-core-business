<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset\Templates;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Entity\Segment;
use Bitrix\Sender\Integration\Sender\Holiday;
use Bitrix\Sender\Internals\PrettyDate;
use Bitrix\Sender\Templates\Category;
use Bitrix\Sender\UI;
use Bitrix\Sender\Templates\Type;
use Bitrix\Sender\Dispatch;

Loc::loadMessages(__FILE__);

/**
 * Class Rc
 * @package Bitrix\Sender\Preset\Templates
 */
class Rc
{
	const IMAGE_DIR = '/images/sender/preset/events/';

	/**
	 * Return base templates.
	 *
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @param string|null $messageCode Message code.
	 * @return array
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null, $messageCode = null)
	{
		if($templateType && $templateType !== 'BASE')
		{
			return array();
		}
		if($messageCode && !in_array($messageCode, self::getProvidedMessageCodes()))
		{
			return array();
		}

		return self::getTemplates($templateId, $messageCode = null);
	}

	private static function getProvidedMessageCodes()
	{
		return [
			'rc_lead',
			'rc_deal',
		];
	}

	private static function getListByType()
	{
		$list = [
			[
				'CODE' => 'birthday',
				'SEGMENT_CODES' => ['case_crm_client_birthday', 'case_crm_lead_birthday'],
				'HINT' => Loc::getMessage('SENDER_PRESET_TEMPLATE_RC_HINT_EVERY_DAY'),
				'DISPATCH' => [
					'METHOD_CODE' => Dispatch\Method::SCHEDULE,
					'TIMES_OF_DAY' => '09:00',
					'DAYS_OF_WEEK' => "1,2,3,4,5,6,7",
				]
			],
			[
				'CODE' => 'nps',
				'SEGMENT_CODES' => ['case_crm_client_aft_deal_clo'],
				'HINT' => Loc::getMessage('SENDER_PRESET_TEMPLATE_RC_HINT_NPS'),
				'DISPATCH' => [
					'METHOD_CODE' => Dispatch\Method::SCHEDULE,
					'TIMES_OF_DAY' => '09:00',
					'DAYS_OF_WEEK' => "1,2,3,4,5,6,7",
				]
			],
		];

		foreach ($list as $index => $item)
		{
			$code = strtoupper($item['CODE']);
			$msgPrefix = 'SENDER_PRESET_TEMPLATE_RC_' . $code . '_';
			foreach (['NAME', 'DESC', 'TITLE', 'TEXT'] as $key)
			{
				$item[$key] = Loc::getMessage($msgPrefix . $key);
			}

			$list[$index] = $item;
		}

		foreach (Holiday::getList() as $holiday)
		{
			$code = $holiday->getCode();
			$name = $holiday->getName();
			$formattedDate = $holiday->formatDate();

			$item = [
				'CODE' => $holiday->getCode(),
				'SEGMENT_CODES' => ["case_crm_client_$code"],
				'HINT' => Loc::getMessage(
					'SENDER_PRESET_TEMPLATE_RC_HINT_ONE_DAY',
					[
						'%run_date%' => PrettyDate::formatDate($holiday->getDateFrom()),
						'%date_from%' => PrettyDate::formatDate($holiday->getDateFrom()),
						'%date_to%' => PrettyDate::formatDate($holiday->getDateTo()),
					]
				),
				'DISPATCH' => [
					'METHOD_CODE' => Dispatch\Method::SCHEDULE,
					'TIMES_OF_DAY' => '09:00',
					'DAYS_OF_WEEK' => "1,2,3,4,5,6,7",
					'DAYS_OF_MONTH' => $holiday->getDay(),
					'MONTHS_OF_YEAR' => $holiday->getMonth(),
				]
			];

			$msgPrefix = "SENDER_PRESET_TEMPLATE_RC_HOLIDAY_";
			foreach (['NAME', 'DESC', 'TITLE', 'TEXT'] as $key)
			{
				$item[$key] = Loc::getMessage(
					$msgPrefix . $key,
					[
						'%holiday_name%' => $name,
						'%holiday_date%' => $formattedDate,
					]
				);
				$item[$key] = Texts::replace($item[$key]);
			}

			$list[] = $item;
		}

		return $list;
	}

	private static function getTemplates($templateId = null, $messageCode = null)
	{
		$messageCodes = $messageCode ? array($messageCode) : self::getProvidedMessageCodes();

		$result = [
			[
				'ID' => 'empty',
				'TYPE' => Type::getCode(Type::BASE),
				'CATEGORY' => Category::getCode(Category::CASES),
				'MESSAGE_CODE' => $messageCodes,
				'VERSION' => 2,
				'HOT' => false,
				'ICON' => BX_ROOT . self::IMAGE_DIR . "empty.png",

				'NAME' => Loc::getMessage('SENDER_PRESET_TEMPLATE_RC_EMPTY'),
				'DESC' => Loc::getMessage('SENDER_PRESET_TEMPLATE_RC_EMPTY_DESC'),
				'FIELDS' => [
					'COMMENT' => [
						'CODE' => 'COMMENT',
						'VALUE' => '',
					]
				],
			]
		];
		foreach (self::getListByType() as $item)
		{
			$originalCode = strtolower($item['CODE']);
			$code = 'rc_' . strtolower($item['CODE']);
			if($templateId && $code !== $templateId)
			{
				continue;
			}

			$segmentTiles = UI\TileView::create();
			$segments = Segment::getList([
				'select' => ['ID', 'NAME'],
				'filter' => ['=CODE' => $item['SEGMENT_CODES']]
			]);
			foreach ($segments as $segment)
			{
				$segmentTiles->addTile($segment['ID'], $segment['NAME']);
			}

			$result[] = array(
				'ID' => $code,
				'TYPE' => Type::getCode(Type::BASE),
				'CATEGORY' => Category::getCode(Category::CASES),
				'MESSAGE_CODE' => $messageCodes,
				'VERSION' => 2,
				'HOT' => $item['HOT'],
				'ICON' => BX_ROOT . self::IMAGE_DIR . "$originalCode.png",

				'NAME' => $item['NAME'],
				'DESC' => $item['DESC'],
				'HINT' => $item['HINT'],
				'FIELDS' => [
					'TITLE' => [
						'CODE' => 'TITLE',
						'VALUE' => $item['TITLE'],
					],
					'COMMENT' => [
						'CODE' => 'COMMENT',
						'VALUE' => $item['TEXT'],
					],
					'ALWAYS_ADD' => [
						'CODE' => 'ALWAYS_ADD',
						'VALUE' => 'Y',
					],
				],
				'SEGMENTS' => $segmentTiles->getTiles(),
				'DISPATCH' => $item['DISPATCH'],
			);
		}

		return $result;
	}
}