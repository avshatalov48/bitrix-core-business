<?php

namespace Bitrix\Catalog\Filter\DataProvider;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Crm;

class AgentContractDataProvider extends Main\Filter\EntityDataProvider
{
	/** @var Catalog\v2\Contractor\Provider\IProvider|null */
	private ?Catalog\v2\Contractor\Provider\IProvider $contractorsProvider;

	private static array $fieldsOrder = [
		'ID',
		'TITLE',
		'CONTRACTOR_ID',
		'DATE_MODIFY',
		'DATE_CREATE',
		'MODIFIED_BY',
		'CREATED_BY',
	];

	private static array $fields;

	public function __construct()
	{
		$this->contractorsProvider = Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
			Catalog\v2\Contractor\Provider\Manager::PROVIDER_STORE_DOCUMENT
		);

		static::$fields = [
			'ID' => [
				'id' => 'ID',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_ID'),
				'default' => false,
				'type' => 'number',
				'sort' => 'ID',
			],
			'TITLE' => [
				'id' => 'TITLE',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_TITLE'),
				'default' => true,
				'sort' => 'TITLE',
			],
			'CONTRACTOR_ID' => [
				'id' => 'CONTRACTOR_ID',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_CONTRACTOR_ID'),
				'partial' => true,
				'type' => 'entity_selector',
				'default' => true,
			],
			'DATE_MODIFY' => [
				'id' => 'DATE_MODIFY',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_DATE_MODIFY'),
				'default' => true,
				'type' => 'date',
				'data' => [
					'exclude' => [
						Main\UI\Filter\DateType::TOMORROW,
						Main\UI\Filter\DateType::NEXT_DAYS,
						Main\UI\Filter\DateType::NEXT_WEEK,
						Main\UI\Filter\DateType::NEXT_MONTH,
					],
				],
				'sort' => 'DATE_MODIFY',
			],
			'DATE_CREATE' => [
				'id' => 'DATE_CREATE',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_DATE_CREATE'),
				'default' => true,
				'type' => 'date',
				'data' => [
					'exclude' => [
						Main\UI\Filter\DateType::TOMORROW,
						Main\UI\Filter\DateType::NEXT_DAYS,
						Main\UI\Filter\DateType::NEXT_WEEK,
						Main\UI\Filter\DateType::NEXT_MONTH,
					],
				],
				'sort' => 'DATE_CREATE',
			],
			'MODIFIED_BY' => [
				'id' => 'MODIFIED_BY',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_MODIFIED_BY'),
				'default' => true,
				'type' => 'entity_selector',
				'partial' => true,
			],
			'CREATED_BY' => [
				'id' => 'CREATED_BY',
				'name' => Main\Localization\Loc::getMessage('AGENT_CATALOG_CONTRACT_DATA_PROVIDER_CREATED_BY'),
				'default' => true,
				'type' => 'entity_selector',
				'partial' => true,
			],
		];
	}

	public function getSettings()
	{
		// TODO: Implement getSettings() method.
	}

	public function prepareFields()
	{
		$fields = [
			'ID' => $this->createField('ID', [
				'default' => false,
				'type' => 'number',
			]),
			'TITLE' => $this->createField('TITLE', [
				'default' => true,
			]),
			'DATE_MODIFY' => $this->createField('DATE_MODIFY', [
				'default' => true,
				'type' => 'date',
				'data' => [
					'exclude' => [
						Main\UI\Filter\DateType::TOMORROW,
						Main\UI\Filter\DateType::NEXT_DAYS,
						Main\UI\Filter\DateType::NEXT_WEEK,
						Main\UI\Filter\DateType::NEXT_MONTH,
					],
				],
			]),
			'DATE_CREATE' => $this->createField('DATE_CREATE', [
				'default' => true,
				'type' => 'date',
				'data' => [
					'exclude' => [
						Main\UI\Filter\DateType::TOMORROW,
						Main\UI\Filter\DateType::NEXT_DAYS,
						Main\UI\Filter\DateType::NEXT_WEEK,
						Main\UI\Filter\DateType::NEXT_MONTH,
					],
				],
			]),
			'MODIFIED_BY' => $this->createField('MODIFIED_BY', [
				'default' => true,
				'type' => 'entity_selector',
				'partial' => true,
			]),
			'CREATED_BY' => $this->createField('CREATED_BY', [
				'default' => true,
				'type' => 'entity_selector',
				'partial' => true,
			]),
		];

		if (Main\Loader::includeModule('crm'))
		{
			$fields['PRODUCTS'] = $this->createField('PRODUCTS', [
				'partial' => true,
				'type' => 'entity_selector',
			]);

			$fields['SECTIONS'] = $this->createField('SECTIONS', [
				'partial' => true,
				'type' => 'entity_selector',
			]);
		}

		if ($this->contractorsProvider)
		{
			$contractorsFields = $this->contractorsProvider::getDocumentsGridFilterFields();
			foreach ($contractorsFields as $contractorsField)
			{
				$fields[$contractorsField['CODE']] = $this->createField(
					$contractorsField['CODE'],
					$contractorsField['PARAMS']
				);
			}
		}
		else
		{
			$fields['CONTRACTOR_ID'] = $this->createField('CONTRACTOR_ID', [
				'partial' => true,
				'type' => 'entity_selector',
				'default' => true,
			]);
		}

		return $fields;
	}

	protected function getFieldName($fieldID)
	{
		return Main\Localization\Loc::getMessage("AGENT_CATALOG_CONTRACT_DATA_PROVIDER_{$fieldID}");
	}

	public function prepareFieldData($fieldID)
	{
		$userFields = ['MODIFIED_BY', 'CREATED_BY'];
		if (in_array($fieldID, $userFields, true))
		{
			return $this->getUserEntitySelectorParams($fieldID . '_filter', ['fieldName' => $fieldID]);
		}

		if ($fieldID === 'CONTRACTOR_ID')
		{
			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => 'agent_contract_contractor_filter',
						'entities' => [
							[
								'id' => 'contractor',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							]
						],
						'dropdownMode' => false,
					],
				],
			];
		}

		if (
			$this->contractorsProvider
			&& $this->contractorsProvider::isDocumentsGridFilterFieldSupported($fieldID)
		)
		{
			return $this->contractorsProvider::getDocumentsGridFilterFieldData($fieldID);
		}

		if ($fieldID === 'PRODUCTS')
		{
			$options = [
				'iblockId' => Crm\Product\Catalog::getDefaultId(),
				'basePriceId' => Crm\Product\Price::getBaseId(),
			];

			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => 'agent_contract_product_filter',
						'entities' => [
							[
								'id' => 'agent-contractor-product',
								'options' => $options,
							],
							[
								'id' => 'agent-contractor-product-variation',
								'options' => $options,
							],
						],
						'dropdownMode' => false,
					],
				],
			];
		}

		if ($fieldID === 'SECTIONS')
		{
			$options = [
				'iblockId' => Crm\Product\Catalog::getDefaultId(),
				'basePriceId' => Crm\Product\Price::getBaseId(),
			];

			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => 'agent_contract_section_filter',
						'entities' => [
							[
								'id' => 'agent-contractor-section',
								'options' => $options,
							],
						],
						'dropdownMode' => false,
					],
				],
			];
		}

		return null;
	}

	public function getGridColumns(): array
	{
		$columns = [];
		$fieldsOrder = self::$fieldsOrder;
		foreach ($fieldsOrder as $field)
		{
			$columns[] = self::$fields[$field];
		}

		return $columns;
	}
}
