<?php

namespace Bitrix\Catalog\v2\Integration\Seo\Facebook;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Collection;

final class FacebookAgent
{
	public const AGENT_NAME = '\Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookAgent::executeProductUpdate();';

	public static function executeProductUpdate(): string
	{
		if (
			!Loader::includeModule('crm')
			&& !Loader::includeModule('iblock')
		)
		{
			return '';
		}

		if (!self::getFacebookFacade()->isExportAvailable() || !self::getFacebookFacade()->hasAuth())
		{
			return '';
		}

		if (self::checkRequirements())
		{
			$lastModifiedProducts = self::getLastModifiedProductIds();
			if (!empty($lastModifiedProducts))
			{
				self::getFacebookFacade()->refreshExportedProducts($lastModifiedProducts);
			}
		}

		return self::AGENT_NAME;
	}

	public static function registerCatalogFacebookAgent(): void
	{
		$agent = \CAgent::GetList(
			['ID' => 'DESC'],
			["MODULE_ID" => 'catalog', "NAME" => self::AGENT_NAME]
		);

		if (!$agent->Fetch())
		{
			\CTimeZone::Disable();
			\CAgent::AddAgent(
				self::AGENT_NAME,
				'catalog',
				'Y',
				300,
				'',
				'Y',
				\ConvertTimeStamp(time() + 300, 'FULL'),
			);
			\CTimeZone::Enable();
		}
	}

	public static function unregisterCatalogFacebookAgent(): void
	{
		$agent = \CAgent::GetList(
			['ID' => 'DESC'],
			["MODULE_ID" => 'catalog', "NAME" => self::AGENT_NAME]
		);

		if ($agent->Fetch())
		{
			\CAgent::RemoveAgent(
				self::AGENT_NAME,
				'catalog'
			);
		}
	}

	private static function checkRequirements(): bool
	{
		$facebookFacade = self::getFacebookFacade();
		if ($facebookFacade)
		{
			return
				$facebookFacade
					->checkRequirements()
					->isSuccess()
				;
		}

		return false;
	}

	private static function getLastModifiedProductIds(): array
	{
		$productIds = [];
		$elementsIterator = \Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable::getList([
			'order' => ['ID' => 'ASC'],
			'select' => ['ID', 'PRODUCT_ID', 'TIMESTAMP_X'],
			'filter' => [
				'SERVICE_ID' => 'facebook',
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'IBLOCK_ELEMENT',
					\Bitrix\Iblock\ElementTable::class,
					[
						'=this.PRODUCT_ID' => 'ref.ID',
						'<this.TIMESTAMP_X' => 'ref.TIMESTAMP_X',
					],
					[
						'join_type' => 'INNER',
					]
				),
			]
		]);
		while ($element = $elementsIterator->fetch())
		{
			$productIds[] = (int)$element['PRODUCT_ID'];
		}

		return $productIds;
	}

	private static function getFacebookFacade(): FacebookFacade
	{
		return ServiceContainer::get('integration.seo.facebook.facade', [
			'iblockId' => self::getProductIblockId(),
		]);
	}

	private static function getProductIblockId(): int
	{
		return \CCrmCatalog::EnsureDefaultExists() ?: 0;
	}
}
