<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex\Dto;

use Bitrix\Main\Type\Dictionary;

final class ReportCollection extends Dictionary
{
	/**
	 * @param Report $value
	 *
	 * @return $this
	 */
	public function addItem(Report $value): self
	{
		$this->values[] = $value;

		return $this;
	}

	/**
	 * @param $name
	 * @param Report | null $value
	 *
	 * @return void
	 */
	public function set($name, $value = null)
	{
		if ($value instanceof Report)
		{
			parent::set($name, $value);
		}
	}

	public function getUniqCampaignIds(): array
	{
		$ids = [];

		/** @var Report $report */
		foreach ($this->values as $report)
		{
			$ids[$report->campaignId] = $report->campaignId;
		}

		return array_values($ids);
	}
}
