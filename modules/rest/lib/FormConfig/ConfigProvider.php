<?php

namespace Bitrix\Rest\FormConfig;

class ConfigProvider
{
	public function __construct(private ConfigStoreInterface $store)
	{
	}

	public function get(): array
	{
		return $this->store->provide();
	}
}