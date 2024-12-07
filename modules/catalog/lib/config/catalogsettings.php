<?php

namespace Bitrix\Catalog\Config;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Catalog\Config\Options\CheckRightsOnDecreaseStoreAmount;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Catalog\Store\EnableWizard\ConditionsChecker;
use Bitrix\Catalog\Store\EnableWizard\OnecAppManager;
use Bitrix\Catalog\Store\EnableWizard\TariffChecker;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\VatTable;
use Bitrix\Crm\Integration\Sale\Reservation;
use Bitrix\Intranet\Settings\AbstractSettings;
use Bitrix\Intranet\Settings\SettingsInterface;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Update\Stepper;
use Bitrix\Catalog\Store\EnableWizard\Manager;

Loader::includeModule('intranet');

final class CatalogSettings extends AbstractSettings
{
	private const OPTION_DEFAULT_SUBSCRIBE = 'default_subscribe';
	private const OPTION_DEFAULT_PRODUCT_VAT_INCLUDED = 'default_product_vat_included';
	private const OPTION_DEFAULT_CAN_BUY_ZERO = 'default_can_buy_zero';
	private const OPTION_DEFAULT_QUANTITY_TRACE = 'default_quantity_trace';
	private const OPTION_PRODUCT_CARD_SLIDER_ENABLED = 'product_card_slider_enabled';

	private const PRODUCT_SLIDER_HELP_LINK_EU = 'https://training.bitrix24.com/support/training/course/index.php?COURSE_ID=178&LESSON_ID=25692';
	private const PRODUCT_SLIDER_HELP_LINK_RU = 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=48&LESSON_ID=25488';

	public function save(): Result
	{
		$result = new Result();

		if (
			Loader::includeModule('crm')
			&& !Manager::isOnecMode()
		)
		{
			if (is_array($this->data['reservationSettings']))
			{
				foreach ($this->data['reservationSettings'] as $entityCode => $reservationSettingsValue)
				{
					Reservation\Config\EntityFactory::make($entityCode)
						->setValues($reservationSettingsValue)
						->save()
					;
				}
			}
		}

		if (isset($this->data['checkRightsOnDecreaseStoreAmount']))
		{
			if ($this->data['checkRightsOnDecreaseStoreAmount'] === 'Y')
			{
				CheckRightsOnDecreaseStoreAmount::set(
					CheckRightsOnDecreaseStoreAmount::ENABLED_VALUE
				);
			}
			else
			{
				CheckRightsOnDecreaseStoreAmount::set(
					CheckRightsOnDecreaseStoreAmount::DISABLED_VALUE
				);
			}
		}

		if (
			!empty($this->data['costPriceCalculationMethod'])
			&& Feature::isStoreBatchEnabled()
			&& !State::isProductBatchMethodSelected()
		)
		{
			CostPriceCalculator::setMethod($this->data['costPriceCalculationMethod']);
			if (!StoreBatchTable::getRow(['select' => ['ID']]))
			{
				Stepper::bindClass(
					'\Bitrix\Catalog\Update\ProductBatchConverter',
					'catalog',
					0
				);
			}
		}

		$catalogOptionSettings = [
			'defaultQuantityTrace' => self::OPTION_DEFAULT_QUANTITY_TRACE,
			'defaultCanBuyZero' => self::OPTION_DEFAULT_CAN_BUY_ZERO,
			'defaultSubscribe' => self::OPTION_DEFAULT_SUBSCRIBE,
			'defaultProductVatIncluded' => self::OPTION_DEFAULT_PRODUCT_VAT_INCLUDED,
		];

		if ($this->canEnableProductCardSlider())
		{
			$catalogOptionSettings['productCardSliderEnabled'] = self::OPTION_PRODUCT_CARD_SLIDER_ENABLED;
		}

		if (isset($this->data['defaultProductVatId']))
		{
			$this->updateDefaultVat((int)$this->data['defaultProductVatId']);
		}

		foreach ($catalogOptionSettings as $key => $optionName)
		{
			if (!isset($this->data[$key]))
			{
				continue;
			}

			Option::set('catalog', $optionName, $this->data[$key]);
		}

		return $result;
	}

	public function get(): SettingsInterface
	{
		$data = [];

		// reservation options
		if (Loader::includeModule('crm'))
		{
			$data['reservationEntities'] = $this->getReservationEntities();
		}

		$accessController = AccessController::getCurrent();

		$data = array_merge($data, [
			'costPriceCalculationMethod' => $this->getCostPriceCalculationMethodSetting(),
			// product options
			'checkRightsOnDecreaseStoreAmount' => CheckRightsOnDecreaseStoreAmount::isEnabled() ? 'Y' : 'N',
			'defaultQuantityTrace' => Option::get('catalog', self::OPTION_DEFAULT_QUANTITY_TRACE, 'N'),
			'defaultSubscribe' => Option::get('catalog', self::OPTION_DEFAULT_SUBSCRIBE, 'Y'),
			'defaultProductVatIncluded' => Option::get('catalog', self::OPTION_DEFAULT_PRODUCT_VAT_INCLUDED, 'N'),
			'defaultCanBuyZero' => Option::get('catalog', self::OPTION_DEFAULT_CAN_BUY_ZERO, 'N'),
			'productCardSliderEnabled' => Option::get('catalog', self::OPTION_PRODUCT_CARD_SLIDER_ENABLED, self::isBitrix24() ? 'Y' : 'N'),
			'vats' => $this->getVats(),
			'defaultProductVatId' => $this->getDefaultProductVatId() ?? 0,
			// inventory management options
			'isEnabledInventoryManagement' => State::isEnabledInventoryManagement(),
			'storeControlMode' => Manager::getCurrentMode(),
			'storeControlAvailableModes' => Manager::getAvailableModes(),
			'hasConductedDocumentsOrQuantities' => ConditionsChecker::hasConductedDocumentsOrQuantities(),
			'onecStatusUrl' => OnecAppManager::getStatusUrl(),
			// access and misc. options
			'canEnableProductCardSlider' => $this->canEnableProductCardSlider(),
			'hasAccessToReservationSettings' => $accessController->check(ActionDictionary::ACTION_RESERVED_SETTINGS_ACCESS),
			'hasAccessToCatalogSettings' => $accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS),
			'isStoreBatchUsed' => Feature::isStoreBatchEnabled(),
			'isBitrix24' => self::isBitrix24() ? 'Y' : 'N',
			'isInventoryManagementRestricted' => !Feature::isInventoryManagementEnabled(),
			'is1cRestricted' => TariffChecker::isOnecInventoryManagementRestricted(),
			'hasAccessToChangeCanBuyZero' => $accessController->check(ActionDictionary::ACTION_SELL_NEGATIVE_COMMODITIES_SETTINGS_EDIT),
			'productsCount' => $this->getProductsCount(),
			'busProductCardHelpLink' => self::getBusProductCardHelpLink(),
			'configCatalogSource' => Context::getCurrent()->getRequest()->get('configCatalogSource'),
		]);

		if (!State::isProductBatchMethodSelected())
		{
			$negativeBalanceItem = StoreProductTable::getList([
				'select' => ['ID'],
				'filter' => [
					'<AMOUNT' => 0,
				],
				'limit' => 1,
			])
				->fetch()
			;

			$data['showNegativeStoreAmountPopup'] = !empty($negativeBalanceItem);
			if (!empty($negativeBalanceItem))
			{
				$productGridComponent = 'bitrix:catalog.report.store_stock.products.grid';
				$productGridPath = \CComponentEngine::makeComponentPath($productGridComponent);

				$data['storeBalancePopupLink'] = getLocalPath('components' . $productGridPath . '/slider.php');
			}
		}

		return new self($data);
	}

	/**
	 * Returns the default VAT ID from the default product catalog.
	 *
	 * @return int|null
	 */
	private function getDefaultProductVatId(): ?int
	{
		$defaultProductCatalogId = \Bitrix\Crm\Product\Catalog::getDefaultId();

		if (!$defaultProductCatalogId)
		{
			return null;
		}

		$defaultProductCatalogInfo = ServiceContainer::getIblockInfo($defaultProductCatalogId);

		return $defaultProductCatalogInfo?->getVatId();
	}

	/**
	 * Returns a list of active VAT rates [ID, NAME].
	 *
	 * @return array
	 */
	private function getVats(): array
	{
		$currentVat = (int)$this->getDefaultProductVatId();
		$vatList = [
			[
				'value' => 0,
				'name' => Loc::getMessage("CATALOG_SETTINGS_VAT_NOT_SELECTED"),
				'selected' => $currentVat === 0,
			],
		];
		$hints = [
			0 => Loc::getMessage('CATALOG_SETTINGS_VAT_HINT'),
		];

		$iterator = VatTable::getList([
			'select' => [
				'ID',
				'NAME',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
			],
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
			]
		]);

		while ($row = $iterator->fetch())
		{
			$vatList[] = [
				'value' => $row['ID'],
				'name' => htmlspecialcharsbx($row['NAME']),
				'selected' => $currentVat === (int)$row['ID'],
			];

			$hints[$row['ID']] = Loc::getMessage('CATALOG_SETTINGS_VAT_HINT');
		}

		unset($row, $iterator);

		return [
			'items' => $vatList,
			'hints' => $hints,
			'current' => $currentVat,
		];
	}

	private function getReservationEntities(): array
	{
		$result = [];

		$reservationEntities = Reservation\Config\EntityFactory::makeAllKnown();
		foreach ($reservationEntities as $reservationEntity)
		{
			$result[] = [
				'code' => $reservationEntity::getCode(),
				'name' => $reservationEntity::getName(),
				'settings' => [
					'scheme' => $reservationEntity::getScheme(),
					'values' => $reservationEntity->getValues(),
				],
			];
		}

		return $result;
	}

	private function getCostPriceCalculationMethodSetting(): array
	{
		$currentMethod = CostPriceCalculator::getMethod();
		$methodList = CostPriceCalculator::getMethodList();

		return [
			'items' => [
				[
					'value' => '',
					'name' => Loc::getMessage('CATALOG_SETTINGS_CALCULATION_METHOD_NOT_SELECTED'),
					'selected' => empty($currentMethod),
					'disabled' => true,
					'hidden' => true,
				],
				[
					'value' => CostPriceCalculator::METHOD_AVERAGE,
					'name' => $methodList[CostPriceCalculator::METHOD_AVERAGE],
					'selected' => $currentMethod === CostPriceCalculator::METHOD_AVERAGE,
				],
				[
					'value' => CostPriceCalculator::METHOD_FIFO,
					'name' => $methodList[CostPriceCalculator::METHOD_FIFO],
					'selected' => $currentMethod === CostPriceCalculator::METHOD_FIFO,
				],
			],
			'hints' => [
				CostPriceCalculator::METHOD_AVERAGE => Loc::getMessage('CATALOG_SETTINGS_CALCULATION_METHOD_AVERAGE_HINT'),
				CostPriceCalculator::METHOD_FIFO => Loc::getMessage('CATALOG_SETTINGS_CALCULATION_METHOD_FIFO_HINT'),
			],
			'current' => CostPriceCalculator::getMethod(),
		];
	}

	private function canEnableProductCardSlider(): bool
	{
		if (self::isBitrix24())
		{
			return Option::get('catalog', self::OPTION_PRODUCT_CARD_SLIDER_ENABLED) !== 'Y';
		}

		return true;
	}

	/**
	 * Update the default VAT in the database.
	 *
	 * @param int $defaultProductVatId
	 * @return bool
	 */
	private function updateDefaultVat(int $defaultProductVatId): bool
	{
		try
		{
			$defaultProductCatalogId = \Bitrix\Crm\Product\Catalog::getDefaultId();
			$updateResult = CatalogIblockTable::update(
				$defaultProductCatalogId,
				[
					'VAT_ID' => $defaultProductVatId,
				]
			);
		} catch (\Exception)
		{
			return false;
		}

		if (!$updateResult->isSuccess())
		{
			return false;
		}

		return true;
	}

	private static function isBitrix24(): bool
	{
		static $isBitrix24Included;

		if (!isset($isBitrix24Included))
		{
			$isBitrix24Included = Loader::includeModule('bitrix24');
		}

		return $isBitrix24Included;
	}

	private function getProductsCount(): int
	{
		$result = 0;

		$catalogList = \CCatalogProductSettings::getCatalogList();
		foreach ($catalogList as $catalog)
		{
			$result += $catalog['COUNT'];

		}

		return $result;
	}

	private static function getBusProductCardHelpLink(): string
	{
		if (self::isBitrix24())
		{
			return '';
		}

		if (in_array(Application::getInstance()->getLicense()->getRegion(), ['ru', 'by', 'kz'], true))
		{
			return self::PRODUCT_SLIDER_HELP_LINK_RU;
		}

		return self::PRODUCT_SLIDER_HELP_LINK_EU;
	}
}
