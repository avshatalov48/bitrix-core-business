<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex\Dto;

use Bitrix\Main;

final class Report
{
	public function __construct(
		public readonly Main\Type\Date $date,
		public readonly int $adId,
		public ?string $adName = null,
		public readonly int $adGroupId,
		public string $adGroupName,
		public readonly int $campaignId,
		public string $campaignName,
		public readonly int $impressions,
		public readonly int $clicks,
		public readonly float $cost,
		public readonly int $conversions,
		public readonly float $avgCpc,
		public ?string $utmSource = null,
		public ?string $utmMedium = null,
		public ?string $utmCampaign = null,
		public ?string $utmContent = null,
	)
	{}

	public static function makeFromArray(array $fields): self
	{
		return new self(
			date: new Main\Type\Date($fields['Date'], 'Y-m-d'),
			adId: (int)$fields['AdId'],
			adName: $fields['AdName'] ?? null,
			adGroupId: (int)$fields['AdGroupId'],
			adGroupName: $fields['AdGroupName'] ?? null,
			campaignId: (int)$fields['CampaignId'],
			campaignName: $fields['CampaignName'] ?? null,
			impressions: (int)$fields['Impressions'],
			clicks: (int)$fields['Clicks'],
			cost: (float)$fields['Cost'],
			conversions: (int)$fields['Conversions'],
			avgCpc: (float)$fields['AvgCpc'],
			utmSource: $fields['UtmSource'] ?? null,
			utmMedium: $fields['UtmMedium'] ?? null,
			utmCampaign: $fields['UtmCampaign'] ?? null,
			utmContent: $fields['UtmContent'] ?? null,
		);
	}
}
