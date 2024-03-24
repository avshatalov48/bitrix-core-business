<?php

namespace Bitrix\Catalog\Integration\Sale\Cashbox\EventHandlers;

use Bitrix\Main;
use Bitrix\Catalog;

class Check
{
	private const TYPE_INDIVIDUAL = 'individual';
	private const TYPE_COMPANY = 'company';

	/**
	 * Event handler for \Bitrix\Sale\Cashbox\AbstractCheck::EVENT_ON_CHECK_PREPARE_DATA event
	 *
	 * @param $result
	 * @param $type
	 * @return array
	 */
	public static function onSaleCheckPrepareData($result, $type): array
	{
		if (isset($result['PRODUCTS']))
		{
			/**
			 * [
			 * 		product_id_1,
			 * 		product_id_2,
			 * 		...
			 * 		product_id_n
			 * ]
			 */
			$productIdsFromCheck = self::getProductIdsFromCheck($result['PRODUCTS']);

			/**
			 * [
			 * 		product_id_1 = [
			 * 			parent_id = parent_product_id_value
			 * 			sections = [
			 * 				section_1,
			 * 				section_n,
			 * 			]
			 * 		],
			 * 		product_id_n = ...
			 * ]
			 */
			$productsInfo = self::getProductsInfo($productIdsFromCheck);

			/**
			 * [
			 * 		'products' = [
			 * 			parent_id_1 = contract_id_1
			 * 			parent_id_n = contract_id_n
			 * 		],
			 * 		'sections' = [
			 * 			section_id_1 = contract_id_1
			 * 			section_id_n = contract_id_n
			 * 		]
			 * ]
			 */
			$contractIds = self::getContractIdsByProductsInfo($productsInfo);
			$contractIdList = array_unique(array_merge(
				array_values($contractIds['products']),
				array_values($contractIds['sections'])
			));

			/**
			 * [
			 *		contracts = [
			 * 			contract_id = [
			 * 				individual = id,
			 * 				company = id
			 * 			]
			 * 		]
			 * 		individuals = [
			 * 			contractor_id_1 = [
			 * 				data... phone, inn, address, ect
			 * 			],
			 * 			...
			 * 		],
			 * 		companies = [
			 * 			contractor_id_2 = [
			 * 				data... phone, inn, address, ect
			 * 			],
			 * 			...
			 * 		]
			 * ]
			 */
			$contractorData = self::getContractorDataByContractIds($contractIdList);

			foreach ($result['PRODUCTS'] as $index => $product)
			{
				$productId = isset($product['PRODUCT_ID']) && (int)$product['PRODUCT_ID'] > 0
					? (int)$product['PRODUCT_ID']
					: null
				;

				if ($productId)
				{
					if (!isset($productsInfo[$productId]))
					{
						continue;
					}

					$contractId = null;

					$productInfo = $productsInfo[$productId];
					foreach ($productInfo['sections'] as $sectionId)
					{
						if (isset($contractIds['sections'][$sectionId]))
						{
							$contractId = $contractIds['sections'][$sectionId];
							break;
						}
					}

					if (!$contractId)
					{
						$contractId = $contractIds['products'][$productInfo['parent_id']] ?? null;
					}

					if (!$contractId)
					{
						continue;
					}

					$contractor = $contractorData['contracts'][$contractId] ?? null;
					if ($contractor)
					{
						$company = $contractor['company'] ?? null;
						$individual = $contractor['individual'] ?? null;

						if ($company && !empty($contractorData[self::TYPE_COMPANY][$company]))
						{
							$result['PRODUCTS'][$index]['SUPPLIER_INFO'] = self::getSupplierInfo(
								$contractorData[self::TYPE_COMPANY][$company]
							);
						}
						elseif ($individual && !empty($contractorData[self::TYPE_INDIVIDUAL][$individual]))
						{
							$result['PRODUCTS'][$index]['SUPPLIER_INFO'] = self::getSupplierInfo(
								$contractorData[self::TYPE_INDIVIDUAL][$individual]
							);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Returns unique product id from check products
	 *
	 * @param array $products
	 * @return array
	 */
	private static function getProductIdsFromCheck(array $products): array
	{
		return array_unique(array_filter(array_column($products, 'PRODUCT_ID')));
	}

	private static function getProductsInfo(array $productIdsFromCheck): array
	{
		$result = [];

		$repositoryFacade = Catalog\v2\IoC\ServiceContainer::getRepositoryFacade();

		foreach ($productIdsFromCheck as $productIdFromCheck)
		{
			$product = $repositoryFacade->loadProduct($productIdFromCheck);
			if ($product)
			{
				$result[$productIdFromCheck] = [
					'parent_id' => $product->getId(),
					'sections' => $product->getSectionCollection()->getValues(),
				];
			}
			else
			{
				$variation = $repositoryFacade->loadVariation($productIdFromCheck);
				if ($variation)
				{
					$parent = $variation->getParent();
					if ($parent)
					{
						$product = $repositoryFacade->loadProduct($parent->getId());
						if ($product)
						{
							$result[$productIdFromCheck] = [
								'parent_id' => $product->getId(),
								'sections' => $product->getSectionCollection()->getValues(),
							];
						}
					}
				}
			}
		}

		return $result;
	}

	private static function getContractIdsByProductsInfo(array $productsInfo): array
	{
		$result = [
			'products' => [],
			'sections' => [],
		];

		$productIds = [];
		$sectionIds = [];

		foreach ($productsInfo as $productInfo)
		{
			$productIds[] = $productInfo['parent_id'];
			$sectionIds[] = $productInfo['sections'];
		}

		$sectionIds = array_unique(array_merge(...$sectionIds));

		$agentProductIterator = Catalog\AgentProductTable::getList([
			'select' => ['CONTRACT_ID', 'PRODUCT_ID', 'PRODUCT_TYPE'],
			'filter' => [
				'LOGIC' => 'OR',
				[
					'=PRODUCT_ID' => $productIds,
					'=PRODUCT_TYPE' => Catalog\AgentProductTable::PRODUCT_TYPE_PRODUCT,
				],
				[
					'=PRODUCT_ID' => $sectionIds,
					'=PRODUCT_TYPE' => Catalog\AgentProductTable::PRODUCT_TYPE_SECTION,
				]

			],
			'order' => ['ID' => 'ASC'], // DESC ?
			// group ?
		]);
		while ($agentProduct = $agentProductIterator->fetch())
		{
			if ($agentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_PRODUCT)
			{
				$result['products'][$agentProduct['PRODUCT_ID']] = (int)$agentProduct['CONTRACT_ID'];
			}
			elseif ($agentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_SECTION)
			{
				$result['sections'][$agentProduct['PRODUCT_ID']] = (int)$agentProduct['CONTRACT_ID'];
			}
		}

		return $result;
	}

	/**
	 * Gets contractors data by product ids
	 *
	 * @param array $contractIds
	 * @return array
	 */
	private static function getContractorDataByContractIds(array $contractIds): array
	{
		$contractorsProvider = Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
			Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
		);
		if ($contractorsProvider)
		{
			return self::getProviderContractorDataByContractIds($contractorsProvider, $contractIds);
		}

		return self::getCatalogContractorDataByContractIds($contractIds);
	}

	private static function getCatalogContractorDataByContractIds(array $contractIds): array
	{
		$result = [
			'contracts' => [],
			'individual' => [],
			'company' => [],
		];

		$contractorIterator = Catalog\AgentContractTable::getList([
			'select' => [
				'ID',
				'CONTRACTOR_ID',
				'CONTRACTOR_PERSON_TYPE' => 'CONTRACTOR.PERSON_TYPE',
				'CONTRACTOR_PERSON_NAME' => 'CONTRACTOR.PERSON_NAME',
				'CONTRACTOR_PERSON_LASTNAME' => 'CONTRACTOR.PERSON_LASTNAME',
				'CONTRACTOR_COMPANY' => 'CONTRACTOR.COMPANY',
				'CONTRACTOR_PHONE' => 'CONTRACTOR.PHONE',
				'CONTRACTOR_ADDRESS' => 'CONTRACTOR.ADDRESS',
				'CONTRACTOR_INN' => 'CONTRACTOR.INN',
			],
			'filter' => [
				'=ID' => $contractIds,
			],
		]);

		while ($contractor = $contractorIterator->fetch())
		{
			$personName = '';
			if (!empty($contractor['CONTRACTOR_PERSON_TYPE']))
			{
				$personName = $contractor['CONTRACTOR_PERSON_TYPE'];
			}

			if (!empty($contractor['CONTRACTOR_PERSON_LASTNAME']))
			{
				$personName = $personName
					? $personName . ' ' . $contractor['CONTRACTOR_PERSON_LASTNAME']
					: $contractor['CONTRACTOR_PERSON_LASTNAME']
				;
			}

			$type = self::TYPE_INDIVIDUAL;
			if ($contractor['CONTRACTOR_PERSON_TYPE'] === Catalog\ContractorTable::TYPE_COMPANY)
			{
				$type = self::TYPE_COMPANY;
			}

			$phone = $contractor['CONTRACTOR_PHONE'];
			if ($phone)
			{
				$phone = preg_split('/[.,;]+/', $phone);
				$phone = array_filter(array_map('trim', $phone));
			}
			else
			{
				$phone = [];
			}

			$result['contracts'][$contractor['ID']][$type] = $contractor['CONTRACTOR_ID'];
			$result[$type][$contractor['CONTRACTOR_ID']] = [
				'TYPE' => $type,
				'PERSON_NAME' => $personName,
				'COMPANY_NAME' => $contractor['CONTRACTOR_COMPANY'],
				'PHONES' => $phone,
				'INN' => $contractor['CONTRACTOR_INN'],
			];
		}

		return $result;
	}

	private static function getProviderContractorDataByContractIds(Catalog\v2\Contractor\Provider\IProvider $provider, array $contractIds): array
	{
		$result = [
			'contracts' => [],
			'individual' => [],
			'company' => [],
		];

		if (!Main\Loader::includeModule('crm'))
		{
			return $result;
		}

		foreach ($contractIds as $contractId)
		{
			$contractor = $provider::getContractorByDocumentId($contractId);
			if ($contractor)
			{
				$type = self::TYPE_INDIVIDUAL;
				if ($contractor instanceof \Bitrix\Crm\Integration\Catalog\Contractor\Company)
				{
					$type = self::TYPE_COMPANY;
				}

				$result['contracts'][$contractId][$type] = $contractor->getId();
				$result[$type][$contractor->getId()] = [
					'TYPE' => $type,
					'PERSON_NAME' => $contractor->getContactPersonFullName(),
					'COMPANY_NAME' => $contractor->getName(),
					'PHONES' => [$contractor->getPhone()],
					'INN' => $contractor->getInn(),
				];
			}
		}

		return $result;
	}

	/**
	 * Gets data for supplier_info's tag
	 *
	 * @param array $contractor
	 * @return array
	 */
	private static function getSupplierInfo(array $contractor): array
	{
		$name = ($contractor['TYPE'] === self::TYPE_INDIVIDUAL)
			? $contractor['PERSON_NAME']
			: $contractor['COMPANY_NAME']
		;

		$phone = $contractor['PHONES'] ?: [];

		return [
			'PHONES' => $phone,
			'NAME' => $name,
			'INN' => $contractor['INN'],
		];
	}
}