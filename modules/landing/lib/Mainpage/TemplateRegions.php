<?php

declare(strict_types=1);

namespace Bitrix\Landing\Mainpage;

use Bitrix\Main\Application;

enum TemplateRegions: string
{
	//Templates::Enterprise
	case EnterpriseWestAr = 'alaio.vibe_enterprise_west_ar';
	case EnterpriseWestBr = 'alaio.vibe_enterprise_west_br';
	case EnterpriseWestDe = 'alaio.vibe_enterprise_west_de';
	case EnterpriseWestEn = 'alaio.vibe_enterprise_west_en';
	case EnterpriseWestFr = 'alaio.vibe_enterprise_west_fr';
	case EnterpriseWestId = 'alaio.vibe_enterprise_west_id';
	case EnterpriseWestIt = 'alaio.vibe_enterprise_west_it';
	case EnterpriseWestJa = 'alaio.vibe_enterprise_west_ja';
	case EnterpriseWestKz = 'alaio.vibe_enterprise_west_kz';
	case EnterpriseWestLa = 'alaio.vibe_enterprise_west_la';
	case EnterpriseWestMs = 'alaio.vibe_enterprise_west_ms';
	case EnterpriseWestPl = 'alaio.vibe_enterprise_west_pl';
	case EnterpriseWestTh = 'alaio.vibe_enterprise_west_th';
	case EnterpriseWestTr = 'alaio.vibe_enterprise_west_tr';
	case EnterpriseWestVn = 'alaio.vibe_enterprise_west_vn';
	//for zones 'cn', 'tc', 'sc'
	case EnterpriseChineseEn = 'alaio.vibe_enterprise_chinese_en';
	case EnterpriseChineseSc = 'alaio.vibe_enterprise_chinese_sc';
	case EnterpriseChineseTc = 'alaio.vibe_enterprise_chinese_tc';
	//for zones 'ru', 'kz'
	case EnterpriseRu = 'bitrix.vibe_enterprise_ru';
	//for zone 'by'
	case EnterpriseBy = 'bitrix.vibe_enterprise_by';
	//Templates::Automation
	case AutomationRu = 'bitrix.vibe_automation_ru';
	case AutomationEn = 'alaio.vibe_automation_en';
	case AutomationDe = 'alaio.vibe_automation_de';
	case AutomationLa = 'alaio.vibe_automation_la';
	case AutomationBr = 'alaio.vibe_automation_br';
	case AutomationFr = 'alaio.vibe_automation_fr';
	case AutomationPl = 'alaio.vibe_automation_pl';
	case AutomationIt = 'alaio.vibe_automation_it';
	case AutomationTr = 'alaio.vibe_automation_tr';
	case AutomationJa = 'alaio.vibe_automation_ja';
	case AutomationVn = 'alaio.vibe_automation_vn';
	case AutomationAr = 'alaio.vibe_automation_ar';
	case AutomationId = 'alaio.vibe_automation_id';
	case AutomationKz = 'alaio.vibe_automation_kz';
	case AutomationMs = 'alaio.vibe_automation_ms';
	case AutomationTh = 'alaio.vibe_automation_th';
	case AutomationChineseSc = 'alaio.vibe_automation_chinese_sc';
	case AutomationChineseTc = 'alaio.vibe_automation_chinese_tc';
	case AutomationChineseEn = 'alaio.vibe_automation_chinese_en';
	//Templates::Collaboration
	case CollaborationRu = 'bitrix.vibe_collaboration_ru';
	case CollaborationEn = 'alaio.vibe_collaboration_en';
	case CollaborationDe = 'alaio.vibe_collaboration_de';
	case CollaborationLa = 'alaio.vibe_collaboration_la';
	case CollaborationBr = 'alaio.vibe_collaboration_br';
	case CollaborationFr = 'alaio.vibe_collaboration_fr';
	case CollaborationPl = 'alaio.vibe_collaboration_pl';
	case CollaborationIt = 'alaio.vibe_collaboration_it';
	case CollaborationTr = 'alaio.vibe_collaboration_tr';
	case CollaborationJa = 'alaio.vibe_collaboration_ja';
	case CollaborationVn = 'alaio.vibe_collaboration_vn';
	case CollaborationAr = 'alaio.vibe_collaboration_ar';
	case CollaborationId = 'alaio.vibe_collaboration_id';
	case CollaborationKz = 'alaio.vibe_collaboration_kz';
	case CollaborationMs = 'alaio.vibe_collaboration_ms';
	case CollaborationTh = 'alaio.vibe_collaboration_th';
	case CollaborationChineseSc = 'alaio.vibe_collaboration_chinese_sc';
	case CollaborationChineseTc = 'alaio.vibe_collaboration_chinese_tc';
	case CollaborationChineseEn = 'alaio.vibe_collaboration_chinese_en';
	//Templates::Boards
	case BoardsRu = 'bitrix.vibe_boards_ru';
	case BoardsEn = 'alaio.vibe_boards_en';
	case BoardsDe = 'alaio.vibe_boards_de';
	case BoardsLa = 'alaio.vibe_boards_la';
	case BoardsBr = 'alaio.vibe_boards_br';
	case BoardsFr = 'alaio.vibe_boards_fr';
	case BoardsPl = 'alaio.vibe_boards_pl';
	case BoardsIt = 'alaio.vibe_boards_it';
	case BoardsTr = 'alaio.vibe_boards_tr';
	case BoardsJa = 'alaio.vibe_boards_ja';
	case BoardsVn = 'alaio.vibe_boards_vn';
	case BoardsAr = 'alaio.vibe_boards_ar';
	case BoardsId = 'alaio.vibe_boards_id';
	case BoardsKz = 'alaio.vibe_boards_kz';
	case BoardsMs = 'alaio.vibe_boards_ms';
	case BoardsTh = 'alaio.vibe_boards_th';
	case BoardsChineseSc = 'alaio.vibe_boards_chinese_sc';
	case BoardsChineseTc = 'alaio.vibe_boards_chinese_tc';
	case BoardsChineseEn = 'alaio.vibe_boards_chinese_en';
	//Templates::Booking
	case BookingRu = 'bitrix.vibe_booking_ru';
	case BookingEn = 'alaio.vibe_booking_en';
	case BookingDe = 'alaio.vibe_booking_de';
	case BookingLa = 'alaio.vibe_booking_la';
	case BookingBr = 'alaio.vibe_booking_br';
	case BookingFr = 'alaio.vibe_booking_fr';
	case BookingPl = 'alaio.vibe_booking_pl';
	case BookingIt = 'alaio.vibe_booking_it';
	case BookingTr = 'alaio.vibe_booking_tr';
	case BookingJa = 'alaio.vibe_booking_ja';
	case BookingVn = 'alaio.vibe_booking_vn';
	case BookingAr = 'alaio.vibe_booking_ar';
	case BookingId = 'alaio.vibe_booking_id';
	case BookingKz = 'alaio.vibe_booking_kz';
	case BookingMs = 'alaio.vibe_booking_ms';
	case BookingTh = 'alaio.vibe_booking_th';
	case BookingChineseSc = 'alaio.vibe_booking_chinese_sc';
	case BookingChineseTc = 'alaio.vibe_booking_chinese_tc';
	case BookingChineseEn = 'alaio.vibe_booking_chinese_en';

	private const CIS_ZONES = ['ru', 'kz', 'by', 'uz'];
	private const DEFAULT_CIS_ZONE = 'ru';
	private const CHINESE_ZONES = ['cn', 'tc', 'sc'];
	private const CONFIG_SECTION_CIS = 'cis';
	private const CONFIG_SECTION_CHINESE = 'chinese';
	private const CONFIG_SECTION_WEST = 'west';
	private const DEFAULT_LANG = 'en';

	/**
	 * Attempt to resolve template based on the provided code.
	 *
	 * @param Templates $code
	 *
	 * @return string|null
	 */
	public static function resolve(Templates $code): ?string
	{
		return self::getTemplateCode($code->value)?->value;
	}

	/**
	 * Get template code for specified template section.
	 *
	 * @param string $code
	 *
	 * @return self|null
	 */
	private static function getTemplateCode(string $code): ?self
	{
		$templateConfig = self::getTemplateConfig($code);
		if (!$templateConfig)
		{
			return null;
		}

		$portalZone = \CBitrix24::getPortalZone();

		//is CIS zone
		if (in_array($portalZone, self::CIS_ZONES, true))
		{
			$cis = $templateConfig[self::CONFIG_SECTION_CIS];

			return $cis[$portalZone] ?? $cis[self::DEFAULT_CIS_ZONE];
		}

		$lang = Application::getInstance()->getContext()->getLanguage();

		//is Chinese zone
		if (in_array($portalZone, self::CHINESE_ZONES, true))
		{
			return $templateConfig[self::CONFIG_SECTION_CHINESE][$lang]
				?? $templateConfig[self::CONFIG_SECTION_CHINESE][self::DEFAULT_LANG];
		}

		return $templateConfig[self::CONFIG_SECTION_WEST][$lang]
			?? $templateConfig[self::CONFIG_SECTION_WEST][self::DEFAULT_LANG];
	}

	/**
	 * Retrieve the configuration for the specified template code.
	 *
	 * @param string $code
	 *
	 * @return array|null
	 */
	private static function getTemplateConfig(string $code): ?array
	{
		$templatesConfig =  [
			Templates::Enterprise->value => [
				self::CONFIG_SECTION_CIS => [
					'by' => self::EnterpriseBy,
					self::DEFAULT_CIS_ZONE => self::EnterpriseRu,
				],
				self::CONFIG_SECTION_CHINESE => [
					'sc' => self::EnterpriseChineseSc,
					'tc' => self::EnterpriseChineseTc,
					self::DEFAULT_LANG => self::EnterpriseChineseEn,
				],
				self::CONFIG_SECTION_WEST => [
					'ar' => self::EnterpriseWestAr,
					'br' => self::EnterpriseWestBr,
					'de' => self::EnterpriseWestDe,
					'fr' => self::EnterpriseWestFr,
					'id' => self::EnterpriseWestId,
					'it' => self::EnterpriseWestIt,
					'ja' => self::EnterpriseWestJa,
					'kz' => self::EnterpriseWestKz,
					'la' => self::EnterpriseWestLa,
					'ms' => self::EnterpriseWestMs,
					'pl' => self::EnterpriseWestPl,
					'th' => self::EnterpriseWestTh,
					'tr' => self::EnterpriseWestTr,
					'vn' => self::EnterpriseWestVn,
					'ru' => self::EnterpriseWestEn,
					'ua' => self::EnterpriseWestEn,
					self::DEFAULT_LANG => self::EnterpriseWestEn,
				],
			],
			Templates::Automation->value => [
				self::CONFIG_SECTION_CIS => [
					self::DEFAULT_CIS_ZONE => self::AutomationRu,
				],
				self::CONFIG_SECTION_CHINESE => [
					'sc' => self::AutomationChineseSc,
					'tc' => self::AutomationChineseTc,
					self::DEFAULT_LANG => self::AutomationChineseEn,
				],
				self::CONFIG_SECTION_WEST => [
					'de' => self::AutomationDe,
					'la' => self::AutomationLa,
					'br' => self::AutomationBr,
					'fr' => self::AutomationFr,
					'pl' => self::AutomationPl,
					'it' => self::AutomationIt,
					'tr' => self::AutomationTr,
					'ja' => self::AutomationJa,
					'vn' => self::AutomationVn,
					'ar' => self::AutomationAr,
					'id' => self::AutomationId,
					'kz' => self::AutomationKz,
					'ms' => self::AutomationMs,
					'th' => self::AutomationTh,
					self::DEFAULT_LANG => self::AutomationEn,
				],
			],
			Templates::Collaboration->value => [
				self::CONFIG_SECTION_CIS => [
					self::DEFAULT_CIS_ZONE => self::CollaborationRu,
				],
				self::CONFIG_SECTION_CHINESE => [
					'sc' => self::CollaborationChineseSc,
					'tc' => self::CollaborationChineseTc,
					self::DEFAULT_LANG => self::CollaborationChineseEn,
				],
				self::CONFIG_SECTION_WEST => [
					'de' => self::CollaborationDe,
					'la' => self::CollaborationLa,
					'br' => self::CollaborationBr,
					'fr' => self::CollaborationFr,
					'pl' => self::CollaborationPl,
					'it' => self::CollaborationIt,
					'tr' => self::CollaborationTr,
					'ja' => self::CollaborationJa,
					'vn' => self::CollaborationVn,
					'ar' => self::CollaborationAr,
					'id' => self::CollaborationId,
					'kz' => self::CollaborationKz,
					'ms' => self::CollaborationMs,
					'th' => self::CollaborationTh,
					self::DEFAULT_LANG => self::CollaborationEn,
				],
			],
			Templates::Boards->value => [
				self::CONFIG_SECTION_CIS => [
					self::DEFAULT_CIS_ZONE => self::BoardsRu,
				],
				self::CONFIG_SECTION_CHINESE => [
					'sc' => self::BoardsChineseSc,
					'tc' => self::BoardsChineseTc,
					self::DEFAULT_LANG => self::BoardsChineseEn,
				],
				self::CONFIG_SECTION_WEST => [
					'de' => self::BoardsDe,
					'la' => self::BoardsLa,
					'br' => self::BoardsBr,
					'fr' => self::BoardsFr,
					'pl' => self::BoardsPl,
					'it' => self::BoardsIt,
					'tr' => self::BoardsTr,
					'ja' => self::BoardsJa,
					'vn' => self::BoardsVn,
					'ar' => self::BoardsAr,
					'id' => self::BoardsId,
					'kz' => self::BoardsKz,
					'ms' => self::BoardsMs,
					'th' => self::BoardsTh,
					self::DEFAULT_LANG => self::BoardsEn,
				],
			],
			Templates::Booking->value => [
				self::CONFIG_SECTION_CIS => [
					self::DEFAULT_CIS_ZONE => self::BookingRu,
				],
				self::CONFIG_SECTION_CHINESE => [
					'sc' => self::BookingChineseSc,
					'tc' => self::BookingChineseTc,
					self::DEFAULT_LANG => self::BookingChineseEn,
				],
				self::CONFIG_SECTION_WEST => [
					'de' => self::BookingDe,
					'la' => self::BookingLa,
					'br' => self::BookingBr,
					'fr' => self::BookingFr,
					'pl' => self::BookingPl,
					'it' => self::BookingIt,
					'tr' => self::BookingTr,
					'ja' => self::BookingJa,
					'vn' => self::BookingVn,
					'ar' => self::BookingAr,
					'id' => self::BookingId,
					'kz' => self::BookingKz,
					'ms' => self::BookingMs,
					'th' => self::BookingTh,
					self::DEFAULT_LANG => self::BookingEn,
				],
			],
		];

		return $templatesConfig[$code] ?? null;
	}
}
