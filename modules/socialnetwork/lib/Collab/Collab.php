<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab;

use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\ShowHistoryOption;
use Bitrix\Socialnetwork\Collab\Property\Feature;
use Bitrix\Socialnetwork\Collab\Property\Option;
use Bitrix\Socialnetwork\Collab\Property\Permission;
use Bitrix\Socialnetwork\Item\Workgroup;

class Collab extends Workgroup
{
	public function setAdditionInfo(string $key, ?Arrayable $value): static
	{
		$this->fields['ADDITIONAL_INFO'][$key] = $value;

		return $this;
	}

	public function getAdditionalInfo(): array
	{
		return $this->fields['ADDITIONAL_INFO'] ?? [];
	}

	public function setOptions(Option ...$options): void
	{
		$this->fields['OPTIONS'] = $options;
	}

	/** @return Option[] */
	public function getOptions(): array
	{
		return $this->fields['OPTIONS'] ?? [];
	}

	public function getOptionValue(string $name): mixed
	{
		foreach ($this->getOptions() as $option)
		{
			if ($option->name === $name)
			{
				return $option->value;
			}
		}

		return null;
	}

	public function setFeatures(Feature ...$features): void
	{
		$this->fields['FEATURES'] = $features;
	}

	/** @return Feature[] */
	public function getFeatures(): array
	{
		return $this->fields['FEATURES'] ?? [];
	}

	public function setPermissions(Permission ...$permissions): void
	{
		$this->fields['PERMISSIONS'] = $permissions;
	}

	/** @return Permission[] */
	public function getPermissions(): array
	{
		return $this->fields['PERMISSIONS'] ?? [];
	}

	/** @see Converter::$format */
	public function toJson($options = 0): array
	{
		$data = parent::toArray();

		$data['OPTIONS'] = $this->mapProperty(...$this->getOptions());
		$data['FEATURES'] = $this->mapProperty(...$this->getFeatures());
		$data['ADDITIONAL_INFO'] = $this->mapAdditionalInfo();

		$converter = Converter::toJson();

		$data = $converter->process($data);

		$data[$converter->process('PERMISSIONS')] = $this->mapProperty(...$this->getPermissions());

		return $data;
	}

	public function toArray(): array
	{
		$data = parent::toArray();

		$data['OPTIONS'] = $this->mapProperty(...$this->getOptions());
		$data['FEATURES'] = $this->mapProperty(...$this->getFeatures());
		$data['PERMISSIONS'] = $this->mapProperty(...$this->getPermissions());
		$data['ADDITIONAL_INFO'] = $this->mapAdditionalInfo();

		return $data;
	}

	protected function mapProperty(Arrayable ...$args): array
	{
		$data = [];
		foreach ($args as $arg)
		{
			$data = array_merge($data, $arg->toArray());
		}

		return $data;
	}

	protected function mapAdditionalInfo(): array
	{
		$info = [];

		foreach ($this->getAdditionalInfo() as $key => $value)
		{
			$info[$key] = $value?->toArray();
		}

		return $info;
	}
}