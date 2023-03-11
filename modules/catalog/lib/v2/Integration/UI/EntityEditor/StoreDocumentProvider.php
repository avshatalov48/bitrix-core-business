<?php

namespace Bitrix\Catalog\v2\Integration\UI\EntityEditor;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\StoreDocumentFileTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\v2\Contractor;
use Bitrix\Catalog\v2\Integration\UI\EntityEditor\Product\StoreDocumentProductPositionRepository;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\UI\EntityEditor\BaseProvider;
use CCurrencyLang;

class StoreDocumentProvider extends BaseProvider
{
	protected const DEFAULT_TYPE = StoreDocumentTable::TYPE_ARRIVAL;
	protected const GUID_PREFIX = 'STORE_DOCUMENT_DETAIL_';
	protected const ENTITY_TYPE_NAME = 'store_document';
	protected const PATH_TO_USER_PROFILE = '/company/personal/user/#user_id#/';

	protected $document;
	protected $config;

	/** @var Contractor\Provider\IProvider|null */
	protected ?Contractor\Provider\IProvider $contractorsProvider;

	private function __construct(array $documentFields, array $config = [])
	{
		$this->document = $documentFields;
		$this->config = $config;
		$this->contractorsProvider = Contractor\Provider\Manager::getActiveProvider();
	}

	/**
	 * @inheritDoc
	 */
	public function getConfigId(): string
	{
		return 'store_document_details';
	}

	/**
	 * @param array $documentFields
	 * @param array $config
	 * @return static
	 */
	public static function createByArray(array $documentFields, array $config = []): self
	{
		return new static($documentFields, $config);
	}

	/**
	 * @param int $id
	 * @param array $config
	 * @return static
	 */
	public static function createById(int $id, array $config = []): self
	{
		$provider = new static(['ID' => $id], $config);
		$provider->loadDocument();

		return $provider;
	}

	/**
	 * @param string $type
	 * @param array $config
	 * @return static
	 */
	public static function createByType(string $type, array $config = []): self
	{
		return new static(['DOC_TYPE' => $type], $config);
	}

	protected function getDocumentId(): ?int
	{
		return $this->document['ID'] ?? null;
	}

	protected function getDocumentType(): string
	{
		return $this->document['DOC_TYPE'] ?? static::DEFAULT_TYPE;
	}

	protected function isNewDocument(): bool
	{
		return $this->getDocumentId() === null;
	}

	protected function loadDocument(): void
	{
		if (!$this->isNewDocument())
		{
			$document = StoreDocumentTable::getList([
				'select' => [
					'*',
					'CONTRACTOR_REF_' => 'CONTRACTOR',
				],
				'filter' => [
					'=ID' => $this->getDocumentId(),
				],
			])->fetch();

			$this->document = $document ?: [];
		}
	}

	public function getGUID(): string
	{
		return static::GUID_PREFIX . $this->getDocumentType();
	}

	public function getEntityId(): ?int
	{
		return $this->getDocumentId();
	}

	public function getEntityTypeName(): string
	{
		return static::ENTITY_TYPE_NAME;
	}

	public function getEntityFields(): array
	{
		static $fields = [];

		$documentType = $this->getDocumentType();
		if (!isset($fields[$documentType]))
		{
			$documentTypeFields = $this->getDocumentFields();
			$fields[$documentType] = $this->getAdditionalFieldKeys($documentTypeFields);
		}

		return $fields[$documentType];
	}

	protected function getDocumentFields(): array
	{
		return array_merge($this->getDocumentCommonFields(), $this->getDocumentSpecificFields());
	}

	protected function getDocumentCommonFields(): array
	{
		return [
			[
				'name' => 'ID',
				'title' => static::getFieldTitle('ID'),
				'type' => 'number',
				'editable' => false,
				'required' => false,
			],
			[
				'name' => 'TITLE',
				'title' => static::getFieldTitle('TITLE'),
				'type' => 'text',
				'editable' => true,
				'required' => false,
				'isHeading' => true,
				'visibilityPolicy' => 'edit',
				'placeholders' => [
					'creation' => $this->getDefaultDocumentTitle(),
				],
			],
			[
				'name' => 'DATE_CREATE',
				'title' => static::getFieldTitle('DATE_CREATE'),
				'type' => 'datetime',
				'editable' => false,
				'visibilityPolicy' => 'view',
			],
			[
				'name' => 'CREATED_BY',
				'title' => static::getFieldTitle('CREATED_BY'),
				'type' => 'user',
				'editable' => false,
			],
			[
				'name' => 'RESPONSIBLE_ID',
				'title' => static::getFieldTitle('RESPONSIBLE_ID'),
				'type' => 'user',
				'editable' => true,
				'required' => true,
			],
			array_merge(
				[
					'name' => 'TOTAL_WITH_CURRENCY',
					'editable' => in_array(
						$this->getDocumentType(),
						[
							StoreDocumentTable::TYPE_ARRIVAL,
							StoreDocumentTable::TYPE_STORE_ADJUSTMENT
						],
						true
					),
				],
				$this->isNewDocument()
					? $this->getTotalInfoControlForNewDocument()
					: $this->getTotalInfoControlForExistingDocument()
			),
			[
				'name' => 'DATE_MODIFY',
				'title' => static::getFieldTitle('DATE_MODIFY'),
				'type' => 'datetime',
				'editable' => false,
				'visibilityPolicy' => 'view',
			],
			[
				'name' => 'MODIFIED_BY',
				'title' => static::getFieldTitle('MODIFIED_BY'),
				'type' => 'user',
				'editable' => false,
				'visibilityPolicy' => 'view',
			],
			[
				'name' => 'DATE_STATUS',
				'title' => static::getFieldTitle('DATE_STATUS'),
				'type' => 'datetime',
				'editable' => false,
				'visibilityPolicy' => 'view',
			],
			[
				'name' => 'STATUS_BY',
				'title' => static::getFieldTitle('STATUS_BY'),
				'type' => 'user',
				'editable' => false,
				'visibilityPolicy' => 'view',
			],
			[
				'name' => 'DOCUMENT_PRODUCTS',
				'title' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_DOCUMENT_PRODUCTS_2'),
				'type' => 'product_row_summary',
				'editable' => false,
			],
		];
	}

	protected function getTotalInfoControlForNewDocument(): array
	{
		return [
			'title' => static::getFieldTitle('CURRENCY'),
			'type' => 'list',
			'data' => [
				'items' => $this->prepareCurrencyList(),
			]
		];
	}

	protected function getTotalInfoControlForExistingDocument(): array
	{
		return [
			'title' => static::getFieldTitle('TOTAL_WITH_CURRENCY'),
			'type' => 'money',
			'data' => [
				'largeFormat' => true,
				'affectedFields' => ['CURRENCY', 'TOTAL'],
				'amount' => 'TOTAL',
				'amountReadOnly' => true,
				'currency' => [
					'name' => 'CURRENCY',
					'items' => $this->prepareCurrencyList(),
				],
				'formatted' => 'FORMATTED_TOTAL',
				'formattedWithCurrency' => 'FORMATTED_TOTAL_WITH_CURRENCY',
			],
		];
	}

	protected function getDocumentSpecificFields(): array
	{
		$fields = [];

		switch ($this->getDocumentType())
		{
			case StoreDocumentTable::TYPE_ARRIVAL:
				$fields = [
					[
						'name' => 'DOC_NUMBER',
						'title' => static::getFieldTitle('DOC_NUMBER'),
						'type' => 'text',
						'editable' => true,
						'showAlways' => true,
					],
					[
						'name' => 'DATE_DOCUMENT',
						'title' => static::getFieldTitle('DATE_DOCUMENT'),
						'type' => 'datetime',
						'editable' => true,
						'data' => [
							'enableTime' => false,
						],
					],
					$this->getContractorField(),
					[
						'name' => 'ITEMS_ORDER_DATE',
						'title' => static::getFieldTitle('ITEMS_ORDER_DATE'),
						'type' => 'datetime',
						'editable' => true,
						'data' => [
							'enableTime' => false,
						],
					],
					[
						'name' => 'ITEMS_RECEIVED_DATE',
						'title' => static::getFieldTitle('ITEMS_RECEIVED_DATE'),
						'type' => 'datetime',
						'editable' => true,
						'data' => [
							'enableTime' => false,
						],
					],
					[
						'name' => 'DOCUMENT_FILES',
						'title' => static::getFieldTitle('DOCUMENT_FILES'),
						'type' => 'file',
						'editable' => true,
						'showAlways' => true,
						'data' => [
							'multiple' => true,
							'maxFileSize' => \CUtil::Unformat(ini_get('upload_max_filesize')),
						]
					],
				];
				break;
			case StoreDocumentTable::TYPE_DEDUCT:
				$fields = [
					[
						'name' => 'DOC_NUMBER',
						'title' => static::getFieldTitle('DOC_NUMBER'),
						'type' => 'text',
						'editable' => true,
						'showAlways' => false,
					],
					[
						'name' => 'DATE_DOCUMENT',
						'title' => static::getFieldTitle('DATE_DOCUMENT'),
						'type' => 'datetime',
						'editable' => true,
						'showAlways' => false,
						'data' => [
							'enableTime' => false,
						],
					],
				];
				break;
			case StoreDocumentTable::TYPE_MOVING:
				$fields = [
					[
						'name' => 'DOC_NUMBER',
						'title' => static::getFieldTitle('DOC_NUMBER'),
						'type' => 'text',
						'editable' => true,
						'showAlways' => false,
					],
					[
						'name' => 'DATE_DOCUMENT',
						'title' => static::getFieldTitle('DATE_DOCUMENT'),
						'type' => 'datetime',
						'editable' => true,
						'showAlways' => false,
						'data' => [
							'enableTime' => false,
						],
					],
				];
				break;
		}

		return $fields;
	}

	protected function getDefaultDocumentTitle(string $documentNumber = '')
	{
		return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_TITLE_DEFAULT_NAME_'
			. $this->getDocumentType(), ['%DOCUMENT_NUMBER%' => $documentNumber]);
	}

	protected function getAdditionalFieldKeys($fields): array
	{
		$resultFields = [];

		foreach ($fields as $field)
		{
			$fieldName = $field['name'];
			$fieldType = $field['type'];

			if ($fieldType === 'user')
			{
				$field['data'] = [
					'enableEditInView' => $field['editable'],
					'formated' => $fieldName . '_FORMATTED_NAME',
					'photoUrl' => $fieldName . '_PHOTO_URL',
					'showUrl' => 'PATH_TO_' . $fieldName,
					'pathToProfile' => static::PATH_TO_USER_PROFILE,
				];
			}

			$resultFields[] = $field;
		}

		return $resultFields;
	}

	public function getEntityConfig(): array
	{
		$sectionElements = [
			[
				'name' => 'main',
				'title' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_MAIN_SECTION'),
				'type' => 'section',
				'elements' => $this->getMainSectionElements(),
				'data' => [
					'isRemovable' => 'false',
				],
				'sort' => 100,
			],
		];

		$sectionElements[] = [
			'name' => 'products',
			'title' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_PRODUCTS_SECTION'),
			'type' => 'section',
			'elements' => [
				['name' => 'DOCUMENT_PRODUCTS'],
			],
			'data' => [
				'isRemovable' => 'false',
			],
			'sort' => 200,
		];

		$sectionElements[] = [
			'name' => 'extra',
			'title' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_EXTRA_SECTION'),
			'type' => 'section',
			'elements' => [
				['name' => 'RESPONSIBLE_ID'],
			],
			'data' => [
				'isRemovable' => 'false',
			],
			'sort' => 300,
		];

		Main\Type\Collection::sortByColumn($sectionElements, ['sort' => SORT_ASC]);

		return [
			[
				'name' => 'left',
				'type' => 'column',
				'data' => [
					'width' => 40,
				],
				'elements' => $sectionElements,
			],
		];
	}

	public function getMainSectionElements()
	{
		switch ($this->getDocumentType())
		{
			case StoreDocumentTable::TYPE_ARRIVAL:
				return [
					['name' => 'TITLE'],
					['name' => 'TOTAL_WITH_CURRENCY'],
					['name' => 'CONTRACTOR_ID'],
					['name' => 'DOC_NUMBER'],
					['name' => 'DATE_DOCUMENT'],
					['name' => 'ITEMS_RECEIVED_DATE'],
					['name' => 'DOCUMENT_FILES'],
				];
			case StoreDocumentTable::TYPE_STORE_ADJUSTMENT:
				return [
					['name' => 'TITLE'],
					['name' => 'TOTAL_WITH_CURRENCY'],
				];
			case StoreDocumentTable::TYPE_MOVING:
				return [
					['name' => 'TITLE'],
					['name' => 'TOTAL_WITH_CURRENCY'],
				];
			case StoreDocumentTable::TYPE_DEDUCT:
				return [
					['name' => 'TITLE'],
					['name' => 'TOTAL_WITH_CURRENCY'],
				];
			default:
				return [];
		}
	}

	public function getEntityData(): array
	{
		if ($this->isNewDocument())
		{
			$document = array_fill_keys(array_column($this->getEntityFields(), 'name'), null);
			$document = array_merge($document, [
				'DOC_TYPE' => $this->document['DOC_TYPE'],
				'RESPONSIBLE_ID' => CurrentUser::get()->getId(),
			]);
		}
		else
		{
			$document = $this->document;
		}

		$currency = $this->document['CURRENCY'] ?? null;
		if (!$currency)
		{
			$currency = CurrencyManager::getBaseCurrency();
		}

		if (!isset($document['TOTAL']))
		{
			$document['TOTAL'] = 0;
			$document['CURRENCY'] = $currency;
		}

		$document['FORMATTED_TOTAL'] = CCurrencyLang::CurrencyFormat($document['TOTAL'], $currency, false);
		$document['FORMATTED_TOTAL_WITH_CURRENCY'] = CCurrencyLang::CurrencyFormat($document['TOTAL'],
			$currency);

		if (empty($this->config['skipProducts']))
		{
			$document['DOCUMENT_PRODUCTS'] = $this->getDocumentProductsPreview($document);
		}
		if (empty($this->config['skipFiles']))
		{
			$document['DOCUMENT_FILES'] = $this->getDocumentFiles($document);
		}

		if ($this->shouldPrepareDateFields())
		{
			$dateFields = ['DATE_DOCUMENT', 'ITEMS_ORDER_DATE', 'ITEMS_RECEIVED_DATE'];
			foreach ($dateFields as $dateField)
			{
				if (isset($document[$dateField]) && $document[$dateField] instanceof Main\Type\DateTime)
				{
					$document[$dateField] = new Main\Type\Date($document[$dateField]);
				}
			}
		}

		return $this->getAdditionalDocumentData($document);
	}

	protected function getDocumentFiles(array $document)
	{
		if ($this->isNewDocument())
		{
			return [];
		}

		$files = StoreDocumentFileTable::getList(['select' => ['FILE_ID'], 'filter' => ['DOCUMENT_ID' => $this->document['ID']]])->fetchAll();

		return array_column($files, 'FILE_ID');
	}

	protected function getDocumentProductsPreview(array $document): array
	{
		$documentProductSummaryInfo = $this->getProductSummaryInfo($document);
		$documentProductSummaryInfo['isReadOnly'] = $this->isReadOnly();

		return $documentProductSummaryInfo;
	}

	private function getProductSummaryInfo(array $document): array
	{
		$isNewDocument = $document['ID'] === null;
		if ($isNewDocument)
		{
			return [
				'count' => 0,
				'total' => \CCurrencyLang::CurrencyFormat(0, $document['CURRENCY']),
				'totalRaw' => [
					'amount' => 0,
					'currency' => $document['CURRENCY'],
				],
				'items' => [],
			];
		}

		$storeDocumentProductPositionRepository = StoreDocumentProductPositionRepository::getInstance();
		$productPositionList = $storeDocumentProductPositionRepository->getList($document['ID']);
		foreach ($productPositionList as &$productPosition)
		{
			$productPosition['SUM'] = \CCurrencyLang::CurrencyFormat($productPosition['SUM'], $document['CURRENCY']);
		}

		return [
			'count' => $storeDocumentProductPositionRepository->getCount($document['ID']),
			'total' => \CCurrencyLang::CurrencyFormat($document['TOTAL'], $document['CURRENCY']),
			'totalRaw' => [
				'amount' => $document['TOTAL'],
				'currency' => $document['CURRENCY'],
			],
			'items' => $productPositionList,
		];
	}

	protected function getAdditionalDocumentData(array $document): array
	{
		$userFields = [];

		foreach ($this->getEntityFields() as $field)
		{
			$fieldName = $field['name'];
			$fieldType = $field['type'];

			if ($fieldType === 'user')
			{
				$userId = $document[$field['name']] ?? null;
				if (!$userId && $fieldName === 'CREATED_BY')
				{
					$userId = CurrentUser::get()->getId();
				}

				$userFields[$fieldName] = $userId;
			}
		}

		$document['PATH_TO_USER_PROFILE'] = static::PATH_TO_USER_PROFILE;

		if ($document['DOC_TYPE'] === StoreDocumentTable::TYPE_ARRIVAL)
		{
			$document = array_merge($document, $this->getContractorData($document));
		}

		$uniqueUserIds = array_filter(array_unique(array_values($userFields)));
		if (!empty($uniqueUserIds) && empty($this->config['skipUsers']))
		{
			$document = $this->getAdditionalUserData($document, $userFields, $this->getUsersInfo($uniqueUserIds));
		}
		elseif(!empty($uniqueUserIds) && !empty($document['USER_INFO']))
		{
			$document = $this->getAdditionalUserData($document, $userFields, $document['USER_INFO']);
		}

		return $document;
	}

	/**
	 * @return array
	 */
	protected function getContractorField(): array
	{
		return [
			'name' => 'CONTRACTOR_ID',
			'title' => static::getFieldTitle('CONTRACTOR_ID'),
			'type' => $this->contractorsProvider
				? $this->contractorsProvider::getEditorFieldType()
				: 'contractor',
			'editable' => true,
			'required' => true,
			'data' => $this->contractorsProvider
				? $this->contractorsProvider::getEditorFieldData()
				: [
					'contractorName' => 'CONTRACTOR_NAME',
				],
		];
	}

	/**
	 * @param array $document
	 * @return array
	 */
	protected function getContractorData(array $document): array
	{
		return $this->contractorsProvider
			? $this->contractorsProvider::getEditorEntityData((int)$document['ID'])
			: ['CONTRACTOR_NAME' => $this->getContractorName()];
	}

	/**
	 * @return string
	 */
	protected function getContractorName(): string
	{
		if (!empty($this->document['CONTRACTOR_REF_COMPANY']))
		{
			return $this->document['CONTRACTOR_REF_COMPANY'];
		}

		if (!empty($this->document['CONTRACTOR_REF_PERSON_NAME']))
		{
			return $this->document['CONTRACTOR_REF_PERSON_NAME'];
		}

		return '';
	}

	protected function getUsersInfo(array $userIds): array
	{
		$usersInfo = [];

		$userIds = array_filter(array_unique(array_values($userIds)));

		if (!empty($userIds))
		{
			$userList = UserTable::getList([
				'filter' => ['=ID' => $userIds],
				'select' => [
					'ID',
					'LOGIN',
					'PERSONAL_PHOTO',
					'NAME',
					'SECOND_NAME',
					'LAST_NAME',
					'WORK_POSITION',
				],
			]);
			while ($user = $userList->fetch())
			{
				$usersInfo[$user['ID']] = $user;
			}
		}

		return $usersInfo;
	}

	protected function getAdditionalUserData(array $document, array $userFields, array $usersInfo): array
	{
		foreach ($userFields as $fieldName => $userId)
		{
			if (!$userId)
			{
				continue;
			}

			$user = $usersInfo[$userId];
			$document['PATH_TO_' . $fieldName] = \CComponentEngine::MakePathFromTemplate(
				static::PATH_TO_USER_PROFILE,
				['user_id' => $user['ID']]
			);

			$document[$fieldName . '_FORMATTED_NAME'] = \CUser::FormatName(
				\CSite::GetNameFormat(false),
				[
					'LOGIN' => $user['LOGIN'],
					'NAME' => $user['NAME'],
					'LAST_NAME' => $user['LAST_NAME'],
					'SECOND_NAME' => $user['SECOND_NAME'],
				],
				true,
				false
			);

			if ((int)$user['PERSONAL_PHOTO'] > 0)
			{
				$fileInfo = \CFile::ResizeImageGet(
					(int)$user['PERSONAL_PHOTO'],
					[
						'width' => 60,
						'height' => 60,
					],
					BX_RESIZE_IMAGE_EXACT
				);
				if (isset($fileInfo['src']))
				{
					$document[$fieldName . '_PHOTO_URL'] = $fileInfo['src'];
				}
			}
		}

		return $document;
	}

	public function getEntityControllers(): array
	{
		return [
			[
				'name' => 'PRODUCT_LIST_CONTROLLER',
				'type' => 'catalog_store_document_product_list',
				'config' => [],
			],
			[
				'name' => 'DOCUMENT_CARD_CONTROLLER',
				'type' => 'document_card',
				'config' => [],
			],
		];
	}

	public function isReadOnly(): bool
	{
		/** @var AccessController $accessController */
		$accessController = AccessController::getCurrent();

		return
			!$accessController->checkByValue(
				ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
				$this->getDocumentType()
			)
			|| (
				isset($this->document['STATUS']) && $this->document['STATUS'] === 'Y'
			)
		;
	}

	public function isEntityConfigEditable(): bool
	{
		/** @var AccessController $accessController */
		$accessController = AccessController::getCurrent();

		return AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_DOCUMENT_CARD_EDIT);
	}

	/**
	 * @return array
	 */
	protected function prepareCurrencyList(): array
	{
		$result = [];

		$existingCurrencies = CurrencyTable::getList([
			'select' => [
				'CURRENCY',
				'FULL_NAME' => 'CURRENT_LANG_FORMAT.FULL_NAME',
				'SORT',
			],
			'order' => [
				'BASE' => 'DESC',
				'SORT' => 'ASC',
				'CURRENCY' => 'ASC',
			],
		])->fetchAll();
		foreach ($existingCurrencies as $currency)
		{
			$result[] = $this->prepareCurrencyListItem($currency);
		}

		return $result;
	}

	/**
	 * @param array $currency
	 * @return array
	 */
	protected function prepareCurrencyListItem(array $currency): array
	{
		return [
			'NAME' => $currency['FULL_NAME'],
			'VALUE' => $currency['CURRENCY'],
		];
	}

	public static function getFieldTitle($fieldName)
	{
		switch ($fieldName)
		{
			case 'ID':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_ID');
			case 'TITLE':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_TITLE_ID');
			case 'TOTAL_WITH_CURRENCY':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_TOTAL');
			case 'CURRENCY':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_CURRENCY');
			case 'ITEMS_ORDER_DATE':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_ITEMS_ORDER_DATE_DOCUMENT');
			case 'ITEMS_RECEIVED_DATE':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_ITEMS_RECEIVED_DATE_DOCUMENT');
			case 'DOCUMENT_FILES':
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_DOCUMENT_FILES_2');
			default:
				return Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_FIELD_' . $fieldName);
		}
	}

	protected function shouldPrepareDateFields(): bool
	{
		return true;
	}
}
