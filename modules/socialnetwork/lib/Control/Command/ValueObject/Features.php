<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\ValueObject;

use Bitrix\Socialnetwork\ValueObjectInterface;
use CSocNetAllowed;

class Features implements ValueObjectInterface, CreateWithDefaultValueInterface, CreateObjectInterface
{
	protected array $features = [];

	public static function create(mixed $data): static
	{
		$value = new static();

		foreach ($data as $featureName => $isActive)
		{
			$value->features[$featureName] = $isActive;
		}

		return $value;
	}

	public static function createWithDefaultValue(): static
	{
		$value = new static();

		$allowedFeatures =  array_keys(CSocNetAllowed::getAllowedFeatures());
		$inactiveFeaturesList = ['forum', 'photo', 'search', 'group_lists', 'wiki'];

		foreach ($allowedFeatures as $featureName)
		{
			$value->features[$featureName] = !in_array($featureName, $inactiveFeaturesList, true);
		}

		return $value;
	}

	public function add(string $featureName): static
	{
		$this->features[$featureName] = $featureName;

		return $this;
	}

	public function delete(string $featureName): static
	{
		unset($this->features[$featureName]);

		return $this;
	}

	public function getValue(): array
	{
		return $this->features;
	}
}