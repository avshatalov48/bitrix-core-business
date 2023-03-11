<?php

namespace Bitrix\Catalog\Url;

class InventoryManagementSourceBuilder
{
	private const INVENTORY_MANAGEMENT_SOURCE_PARAM_NAME = 'inventoryManagementSource';

	private static ?InventoryManagementSourceBuilder $instance = null;
	private ?string $inventoryManagementSource;

	public static function getInstance(): InventoryManagementSourceBuilder
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		$param =
			\Bitrix\Main\Context::getCurrent()
				->getRequest()
				->get(self::INVENTORY_MANAGEMENT_SOURCE_PARAM_NAME)
		;

		$this->inventoryManagementSource = $param ? urlencode((string)$param) : null;
	}

	/**
	 * Return inventory management source name
	 *
	 * @return string
	 */
	public function getInventoryManagementSource(): ?string
	{
		return $this->inventoryManagementSource;
	}

	/**
	 * Add inventory management source param to uri
	 *
	 * @param string $uri
	 * @return string
	 */
	public function addInventoryManagementSourceParam(string $uri): string
	{
		if (!$this->inventoryManagementSource)
		{
			return $uri;
		}

		$uriEntity = new \Bitrix\Main\Web\Uri($uri);
		$uriEntity->addParams([self::INVENTORY_MANAGEMENT_SOURCE_PARAM_NAME => $this->inventoryManagementSource]);

		return $uriEntity->getUri();
	}
}
