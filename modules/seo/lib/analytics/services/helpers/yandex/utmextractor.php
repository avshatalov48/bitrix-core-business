<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex;

final class UtmExtractor
{
	private array $utm = [];

	public function __construct(
		private readonly string $url,
	)
	{
		$this->extract();
	}

	private function extract(): void
	{
		$allowedKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
		$parts = parse_url($this->url);

		if (isset($parts['query']))
		{
			parse_str($parts['query'], $query);
			$this->utm = array_intersect_key($query, array_flip($allowedKeys));
		}
	}

	public function getUtmSource(): ?string
	{
		return $this->utm['utm_source'] ?? null;
	}

	public function getUtmMedium(): ?string
	{
		return $this->utm['utm_medium'] ?? null;
	}

	public function getUtmCampaign(): ?string
	{
		return $this->utm['utm_campaign'] ?? null;
	}

	public function getUtmContent(): ?string
	{
		return $this->utm['utm_content'] ?? null;
	}
}
