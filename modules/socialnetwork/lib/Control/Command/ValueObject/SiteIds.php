<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\ValueObject;

use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Socialnetwork\Site\Site;
use Bitrix\Socialnetwork\ValueObjectInterface;

class SiteIds implements ValueObjectInterface, CreateWithDefaultValueInterface, CreateObjectInterface
{
	#[NotEmpty]
	protected array $siteIds = [];

	public static function create(mixed $data): static
	{
		$value = new static();

		$data = is_array($data) ? $data : [$data];

		$value->siteIds = $data;

		return $value;
	}

	public static function createWithDefaultValue(): static
	{
		$value = new static();

		$value->siteIds = [Site::getInstance()->getMainSiteId()];

		return $value;
	}

	public function getValue(): array
	{
		return $this->siteIds;
	}
}