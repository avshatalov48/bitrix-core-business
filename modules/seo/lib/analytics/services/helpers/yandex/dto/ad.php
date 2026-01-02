<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex\Dto;

final class Ad
{
	public function __construct(
		public readonly int $id,
		public readonly ?string $title,
		public readonly ?string $href,
	)
	{}

	public static function makeFromArray(array $fields): self
	{
		[$title, $href] = match($fields['Type'])
		{
			'TEXT_AD' => [$fields['TextAd']['Title'] ?? '', $fields['TextAd']['Href'] ?? ''],
			'IMAGE_AD' => ['', $fields['TextImageAd']['Href'] ?? ''],
			default => ['', ''],
		};

		return new self(
			id: (int)$fields['Id'],
			title: (string)($title ?? ''),
			href: (string)($href ?? ''),
		);
	}
}
