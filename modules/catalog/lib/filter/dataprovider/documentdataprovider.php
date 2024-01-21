<?php

namespace Bitrix\Catalog\Filter\DataProvider;

use Bitrix\Catalog;
use Bitrix\Catalog\Document\Type\StoreDocumentArrivalTable;
use Bitrix\Catalog\Document\Type\StoreDocumentDeductTable;
use Bitrix\Catalog\Document\Type\StoreDocumentMovingTable;
use Bitrix\Catalog\Document\Type\StoreDocumentStoreAdjustmentTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\v2\Contractor\Provider\IProvider;
use Bitrix\Main\Grid\Column;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\v2\Contractor\Provider\Manager;
use Bitrix\Main\Filter\EntityDataProvider;
use Bitrix\Main\Filter\Settings;

\CBitrixComponent::includeComponentClass('bitrix:catalog.store.document.list');

class DocumentDataProvider extends EntityDataProvider
{
	private $mode;

	private Settings $settings;

	private static $fieldsOrder = [
		\CatalogStoreDocumentListComponent::ARRIVAL_MODE => [
			'ID',
			'TITLE',
			'DOC_NUMBER',
			'DOC_TYPE',
			'DATE_DOCUMENT',
			'STATUS',
			'DATE_CREATE',
			'DATE_MODIFY',
			'DATE_STATUS',
			'STATUS_BY',
			'RESPONSIBLE_ID',
			'CREATED_BY',
			'MODIFIED_BY',
			'CONTRACTOR_ID',
			'TOTAL',
			'STORES',
		],
		\CatalogStoreDocumentListComponent::MOVING_MODE => [
			'ID',
			'TITLE',
			'DOC_NUMBER',
			'DOC_TYPE',
			'DATE_DOCUMENT',
			'STATUS',
			'DATE_CREATE',
			'DATE_MODIFY',
			'DATE_STATUS',
			'STATUS_BY',
			'RESPONSIBLE_ID',
			'CREATED_BY',
			'MODIFIED_BY',
			'TOTAL',
			'STORES_FROM',
			'STORES_TO',
		],
		\CatalogStoreDocumentListComponent::DEDUCT_MODE => [
			'ID',
			'TITLE',
			'DOC_NUMBER',
			'DOC_TYPE',
			'DATE_DOCUMENT',
			'STATUS',
			'DATE_CREATE',
			'DATE_MODIFY',
			'DATE_STATUS',
			'STATUS_BY',
			'RESPONSIBLE_ID',
			'CREATED_BY',
			'MODIFIED_BY',
			'TOTAL',
			'STORES',
		],
		\CatalogStoreDocumentListComponent::OTHER_MODE => [
			'ID',
			'TITLE',
			'DATE_DOCUMENT',
			'STATUS',
			'DATE_CREATE',
			'DATE_MODIFY',
			'DATE_STATUS',
			'STATUS_BY',
			'CREATED_BY',
			'MODIFIED_BY',
		],
	];

	private static $fields;

	/** @var IProvider|null */
	private ?IProvider $contractorsProvider;

	public function __construct($mode, Settings $settings)
	{
		$this->settings = $settings;
		$this->contractorsProvider = Manager::getActiveProvider(Manager::PROVIDER_STORE_DOCUMENT);

		$this->mode = $mode;
		self::$fields = [
			'ID' => [
				'id' => 'ID',
				'name' => Loc::getMessage('DOCUMENT_ID_NAME'),
				'default' => false,
				'sort' => 'ID',
			],
			'TITLE' => [
				'id' => 'TITLE',
				'name' => Loc::getMessage('DOCUMENT_TITLE_NAME'),
				'default' => true,
				'sort' => 'TITLE',
				'width' => '215',
			],
			'DOC_NUMBER' => [
				'id' => 'DOC_NUMBER',
				'name' => Loc::getMessage('DOCUMENT_DOC_NUMBER_NAME'),
				'default' => $this->mode === \CatalogStoreDocumentListComponent::ARRIVAL_MODE,
				'sort' => 'DOC_NUMBER',
			],
			'DOC_TYPE' => [
				'id' => 'DOC_TYPE',
				'name' => Loc::getMessage('DOCUMENT_TYPE_NAME'),
				'default' => $this->mode === \CatalogStoreDocumentListComponent::ARRIVAL_MODE,
				'sort' => 'DOC_TYPE',
				'type' => Column\Type::LABELS,
			],
			'DATE_DOCUMENT' => [
				'id' => 'DATE_DOCUMENT',
				'name' => Loc::getMessage('DOCUMENT_DATE_DOCUMENT_NAME'),
				'default' => false,
				'sort' => 'DATE_DOCUMENT',
			],
			'STATUS' => [
				'id' => 'STATUS',
				'name' => Loc::getMessage('DOCUMENT_STATUS_NAME'),
				'default' => true,
				'sort' => 'STATUS',
				'type' => Column\Type::LABELS,
			],
			'DATE_CREATE' => [
				'id' => 'DATE_CREATE',
				'name' => Loc::getMessage('DOCUMENT_DATE_CREATE_NAME'),
				'default' => false,
				'sort' => 'DATE_CREATE',
			],
			'DATE_MODIFY' => [
				'id' => 'DATE_MODIFY',
				'name' => Loc::getMessage('DOCUMENT_DATE_MODIFY_NAME'),
				'default' => true,
				'sort' => 'DATE_MODIFY',
			],
			'DATE_STATUS' => [
				'id' => 'DATE_STATUS',
				'name' => Loc::getMessage('DOCUMENT_DATE_STATUS_NAME'),
				'default' => false,
				'sort' => 'DATE_STATUS',
			],
			'STATUS_BY' => [
				'id' => 'STATUS_BY',
				'name' => Loc::getMessage('DOCUMENT_STATUS_BY_NAME'),
				'default' => false,
				'sort' => 'STATUS_BY',
			],
			'RESPONSIBLE_ID' => [
				'id' => 'RESPONSIBLE_ID',
				'name' => Loc::getMessage('DOCUMENT_RESPONSIBLE_ID_NAME'),
				'default' => true,
				'sort' => 'RESPONSIBLE_ID',
			],
			'CREATED_BY' => [
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('DOCUMENT_CREATED_BY_NAME'),
				'default' => false,
				'sort' => 'CREATED_BY',
			],
			'MODIFIED_BY' => [
				'id' => 'MODIFIED_BY',
				'name' => Loc::getMessage('DOCUMENT_MODIFIED_BY_NAME'),
				'default' => false,
				'sort' => 'MODIFIED_BY',
			],
			'CONTRACTOR_ID' => [
				'id' => 'CONTRACTOR_ID',
				'name' => Loc::getMessage('DOCUMENT_CONTRACTOR_ID_NAME'),
				'default' => true,
				'sort' => 'CONTRACTOR_ID',
			],
			'TOTAL' => [
				'id' => 'TOTAL',
				'name' => Loc::getMessage('DOCUMENT_TOTAL_NAME'),
				'default' => true,
				'sort' => 'TOTAL',
			],
			'STORES' => [
				'id' => 'STORES',
				'name' => Loc::getMessage('DOCUMENT_STORES_NAME'),
				'default' => true,
				'sort' => false,
				'type' => Column\Type::LABELS,
			],
			'STORES_FROM' => [
				'id' => 'STORES_FROM',
				'name' => Loc::getMessage('DOCUMENT_STORES_FROM_NAME'),
				'default' => true,
				'sort' => false,
				'type' => Column\Type::LABELS,
			],
			'STORES_TO' => [
				'id' => 'STORES_TO',
				'name' => Loc::getMessage('DOCUMENT_STORES_TO_NAME'),
				'default' => true,
				'sort' => false,
				'type' => Column\Type::LABELS,
			],
		];
	}

	public function getSettings()
	{
		return $this->settings;
	}

	public function prepareFields()
	{
		$fields = [
			'TITLE' => $this->createField('TITLE', [
				'default' => true,
			]),
			'ID' => $this->createField('ID', [
				'type' => 'number',
			]),
			'STATUS' => $this->createField('STATUS', [
				'default' => true,
				'type' => 'list',
				'partial' => true,
			]),
			'DATE_DOCUMENT' => $this->createField('DATE_DOCUMENT', [
				'default' => true,
				'type' => 'date',
			]),
			'DATE_CREATE' => $this->createField('DATE_CREATE', [
				'type' => 'date',
			]),
			'DATE_MODIFY' => $this->createField('DATE_MODIFY', [
				'type' => 'date',
			]),
			'DATE_STATUS' => $this->createField('DATE_STATUS', [
				'type' => 'date',
			]),
			'STATUS_BY' => $this->createField('STATUS_BY', [
				'partial' => true,
				'type' => 'entity_selector',
			]),
			'RESPONSIBLE_ID' => $this->createField('RESPONSIBLE_ID', [
				'default' => true,
				'partial' => true,
				'type' => 'entity_selector',
			]),
			'CREATED_BY' => $this->createField('CREATED_BY', [
				'partial' => true,
				'type' => 'entity_selector',
			]),
			'MODIFIED_BY' => $this->createField('MODIFIED_BY', [
				'partial' => true,
				'type' => 'entity_selector',
			]),
		];

		if (Loader::includeModule('crm'))
		{
			$fields['PRODUCTS'] = $this->createField('PRODUCTS', [
				'partial' => true,
				'type' => 'entity_selector',
			]);
		}

		if ($this->mode !== \CatalogStoreDocumentListComponent::OTHER_MODE)
		{
			$fields['DOC_NUMBER'] = $this->createField('DOC_NUMBER');

			if ($this->mode === \CatalogStoreDocumentListComponent::MOVING_MODE)
			{
				$fields['STORES_FROM'] = $this->createField('STORES_FROM', [
					'partial' => true,
					'default' => true,
					'type' => 'entity_selector',
				]);
				$fields['STORES_TO'] = $this->createField('STORES_TO', [
					'partial' => true,
					'default' => true,
					'type' => 'entity_selector',
				]);
			}
			else
			{
				$fields['STORES'] = $this->createField('STORES', [
					'partial' => true,
					'type' => 'entity_selector',
				]);
			}
		}

		if ($this->mode === \CatalogStoreDocumentListComponent::ARRIVAL_MODE)
		{
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

			$fields['DOC_TYPE'] = $this->createField('DOC_TYPE', [
				'default' => true,
				'type' => 'list',
				'partial' => 'true'
			]);
		}

		return $fields;
	}

	protected function getFieldName($fieldID)
	{
		return Loc::getMessage("DOCUMENT_{$fieldID}_NAME");
	}

	/**
	 * @inheritDoc
	 */
	public function prepareFieldData($fieldID)
	{
		if ($fieldID === 'STATUS')
		{
			return [
				'params' => [
					'multiple' => 'Y',
				],
				'items' => StoreDocumentTable::getStatusList(),
			];
		}

		if ($fieldID === 'DOC_TYPE')
		{
			if ($this->mode === \CatalogStoreDocumentListComponent::ARRIVAL_MODE)
			{
				return [
					'params' => [
						'multiple' => 'Y',
					],
					'items' => [
						StoreDocumentTable::TYPE_ARRIVAL => Loc::getMessage('DOCUMENT_TYPE_' . StoreDocumentTable::TYPE_ARRIVAL),
						StoreDocumentTable::TYPE_STORE_ADJUSTMENT => Loc::getMessage('DOCUMENT_TYPE_' . StoreDocumentTable::TYPE_STORE_ADJUSTMENT),
					]
				];
			}
		}

		$userFields = ['RESPONSIBLE_ID', 'STATUS_BY', 'CREATED_BY', 'MODIFIED_BY'];
		if (in_array($fieldID, $userFields))
		{
			return $this->getUserEntitySelectorParams($this->mode . '_' . $fieldID . '_filter', ['fieldName' => $fieldID]);
		}

		if (in_array($fieldID, ['STORES', 'STORES_FROM', 'STORES_TO'], true))
		{
			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => $this->mode . '_store_filter',
						'entities' => [
							[
								'id' => 'store',
								'dynamicLoad' => true,
								'dynamicSearch' => true,
							]
						],
						'dropdownMode' => false,
					],
				],
			];
		}

		if ($fieldID === 'CONTRACTOR_ID')
		{
			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => $this->mode . '_contractor_filter',
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
			return [
				'params' => [
					'multiple' => 'Y',
					'dialogOptions' => [
						'height' => 200,
						'context' => $this->mode . '_product_filter',
						'entities' => [
							[
								'id' => 'product',
								'options' => [
									'iblockId' => \Bitrix\Crm\Product\Catalog::getDefaultId(),
									'basePriceId' => Catalog\GroupTable::getBasePriceTypeId(),
								],
							]
						],
						'dropdownMode' => false,
					],
				],
			];
		}

		return null;
	}

	public function getGridColumns()
	{
		$columns = [];
		$fieldsOrder = self::$fieldsOrder[$this->mode];
		foreach ($fieldsOrder as $field)
		{
			$columns[] = self::$fields[$field];
		}

		switch ($this->mode)
		{
			case \CatalogStoreDocumentListComponent::ARRIVAL_MODE:
				$gridUFManager = new \Bitrix\Main\Grid\Uf\Base(StoreDocumentArrivalTable::getUfId());
				$gridUFManager->addUFHeaders($columns);
				$gridUFManager = new \Bitrix\Main\Grid\Uf\Base(StoreDocumentStoreAdjustmentTable::getUfId());
				$gridUFManager->addUFHeaders($columns);
				break;
			case \CatalogStoreDocumentListComponent::MOVING_MODE:
				$gridUFManager = new \Bitrix\Main\Grid\Uf\Base(StoreDocumentMovingTable::getUfId());
				$gridUFManager->addUFHeaders($columns);
				break;
			case \CatalogStoreDocumentListComponent::DEDUCT_MODE:
				$gridUFManager = new \Bitrix\Main\Grid\Uf\Base(StoreDocumentDeductTable::getUfId());
				$gridUFManager->addUFHeaders($columns);
				break;
		}


		return $columns;
	}
}
