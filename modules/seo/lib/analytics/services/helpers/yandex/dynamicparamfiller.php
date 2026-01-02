<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex;

final class DynamicParamFiller
{
	public static function fill(Dto\Report $report, string $href): void
	{
		if (empty($href))
		{
			return;
		}

		$dynamicParams = self::parseDynamicParams($href);
		$dynamicParamValues = [];

		foreach ($dynamicParams as $dynamicParam)
		{
			$dynamicParamValues[] = self::getDynamicParamValue($report, $dynamicParam);
		}

		$href = str_replace($dynamicParams, $dynamicParamValues, $href);

		$utmExtractor = new UtmExtractor($href);
		$report->utmSource = $utmExtractor->getUtmSource();
		$report->utmMedium = $utmExtractor->getUtmMedium();
		$report->utmCampaign = $utmExtractor->getUtmCampaign();
		$report->utmContent = $utmExtractor->getUtmContent();
	}

	private static function parseDynamicParams(string $href): array
	{
		$matches = [];
		preg_match_all('/{(.+?)}/', $href, $matches);

		return $matches[0] ?? [];
	}

	private static function getDynamicParamValue(Dto\Report $report, string $dynamicParam): mixed
	{
		return match ($dynamicParam)
		{
			'{ad_id}', '{banner_id}' => $report->adId,
			'{campaign_name}' => mb_substr($report->campaignName, 0, 255),
			'{campaign_name_lat}' => mb_substr(\CUtil::translit($report->campaignName, 'en'), 0, 255),
			'{campaign_id}', '{campaignid}' => $report->campaignId,
			'{gbid}', '{adgroupid}' => $report->adGroupId,
			default => $dynamicParam,
		};
	}
}
