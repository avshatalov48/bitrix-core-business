<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Catalog;

Main\Loader::includeModule('catalog');

class CatalogAgentContractDetail
	extends \CBitrixComponent
	implements Main\Engine\Contract\Controllerable, Main\Errorable
{
	use Main\ErrorableImplementation;

	private const PATH_TO_USER_PROFILE_DEFAULT = '/company/personal/user/#USER_ID#/';

	/** @var Catalog\v2\Contractor\Provider\IProvider|null */
	private ?Catalog\v2\Contractor\Provider\IProvider $contractorsProvider;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->contractorsProvider = Catalog\v2\Contractor\Provider\Manager::getActiveProvider(
			Catalog\v2\Contractor\Provider\Manager::PROVIDER_AGENT_CONTRACT
		);
	}

	public function configureActions()
	{
		return [];
	}

	public function onPrepareComponentParams($arParams)
	{
		$this->errorCollection = new Main\ErrorCollection();

		$arParams['ID'] = (int)($arParams['ID'] ?? 0);
		$arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);

		return parent::onPrepareComponentParams($arParams);
	}

	private function checkRequiredParams(): bool
	{
		if ($this->arParams['IBLOCK_ID'] <= 0)
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_IBLOCK_NOT_FOUND');
			return false;
		}

		return true;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'ID',
			'IBLOCK_ID',
			'PATH_TO',
		];
	}

	private function initResult(): void
	{
		$this->arResult = [
			'ID' => 0,
			'TITLE' => '',
			'IBLOCK_ID' => 0,
			'ENTITY_ID' => 0,
			'ENTITY_FIELDS' => [],
			'ENTITY_CONFIG' => [],
			'ENTITY_DATA' => [],
			'ENTITY_CONTROLLERS' => [],
			'INITIAL_MODE' => 'view',
			'ERROR_MESSAGES' => [],
		];
	}

	private function prepareResult(): void
	{
		$this->arResult['ID'] = $this->arParams['ID'];
		$this->arResult['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];

		$fields = $this->getFields();

		$documentData = [];
		if ($this->arResult['ID'] > 0)
		{
			$documentData = $this->loadDocument($this->arResult['ID']);
			$this->arResult['TITLE'] = $documentData['TITLE'];
		}

		$this->prepareFormData($fields, $documentData);

		$this->arResult['INCLUDE_CRM_ENTITY_EDITOR'] = Catalog\v2\Contractor\Provider\Manager::isActiveProviderByModule(
			Catalog\v2\Contractor\Provider\Manager::PROVIDER_STORE_DOCUMENT, 'crm'
		);
	}

	private function prepareFormData(array $fields, array $documentData = []): void
	{
		$this->arResult['ENTITY_ID'] = $this->arParams['ID'];
		$this->arResult['ENTITY_FIELDS'] = $this->getEntityFields($fields, $documentData);
		$this->arResult['ENTITY_CONFIG'] = $this->getEntityConfig();
		$this->arResult['ENTITY_DATA'] = $this->getEntityData($documentData);
		$this->arResult['ENTITY_CONTROLLERS'] = $this->getEntityControllers();

		$this->arResult['INITIAL_MODE'] = $this->arParams['ID'] > 0 ? 'view' : 'edit';
	}

	private function getFields(): array
	{
		return [
			'ID' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_ID'),
			'TITLE' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_TITLE'),
			'PRODUCT_LIST' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_PRODUCT_LIST'),
			'SECTION_LIST' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_SECTION_LIST'),
			'DATE_MODIFY' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_DATE_MODIFY'),
			'DATE_CREATE' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_DATE_CREATE'),
			'MODIFIED_BY' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_MODIFIED_BY'),
			'CREATED_BY' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_CREATED_BY'),
			'FILES' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_FILES'),
		];
	}

	private function getEntityFields(array $fields, array $documentData): array
	{
		$result = [];

		$result[] = [
			'id' => 'ID',
			'title' => $fields['ID'],
			'name' => 'ID',
			'type' => 'number',
			'isDragEnabled' => false,
			'editable' => false,
		];

		$result[] = [
			'id' => 'TITLE',
			'title' => $fields['TITLE'],
			'name' => 'TITLE',
			'type' => 'text',
			'isDragEnabled' => false,
			'editable' => true,
			'isHeading' => true,
			'visibilityPolicy' => 'edit',
			'placeholders' => [
				'creation' => $this->getTitle(),
			],
		];

		$result[] = [
			'id' => 'FILES',
			'title' => $fields['FILES'],
			'name' => 'FILES',
			'type' => 'file',
			'isDragEnabled' => false,
			'editable' => true,
			'showAlways' => true,
			'data' => [
				'multiple' => true,
				'maxFileSize' => \CUtil::Unformat(ini_get('upload_max_filesize')),
			]
		];

		$result[] = $this->getContractorEntityField();

		$result[] = [
			'id' => 'PRODUCT_LIST',
			'title' => $fields['PRODUCT_LIST'],
			'name' => 'PRODUCT_LIST',
			'type' => 'productSet',
			'data' => [
				'entityList' => 'ENTITY_PRODUCT_LIST',
				'iblockId' => 'IBLOCK_ID',
			],
			'isDragEnabled' => false,
			'editable' => true,
			'optionFlags' => 1,
		];

		$result[] = [
			'id' => 'SECTION_LIST',
			'title' => $fields['SECTION_LIST'],
			'name' => 'SECTION_LIST',
			'type' => 'sectionSet',
			'data' => [
				'entityList' => 'ENTITY_SECTION_LIST',
				'iblockId' => 'IBLOCK_ID',
			],
			'isDragEnabled' => false,
			'editable' => true,
			'optionFlags' => 1,
		];

		if ($this->arParams['ID'] > 0)
		{
			$result[] = [
				'id' => 'DATE_MODIFY',
				'title' => $fields['DATE_MODIFY'],
				'name' => 'DATE_MODIFY',
				'type' => 'text',
				'isDragEnabled' => false,
				'editable' => false,
			];

			$result[] = [
				'id' => 'DATE_CREATE',
				'title' => $fields['DATE_CREATE'],
				'name' => 'DATE_CREATE',
				'type' => 'text',
				'isDragEnabled' => false,
				'editable' => false,
			];

			if (!empty($documentData['MODIFIED_BY']))
			{
				$result[] = [
					'name' => 'MODIFIED_BY',
					'title' => $fields['MODIFIED_BY'],
					'type' => 'user',
					'isDragEnabled' => false,
					'editable' => false,
					'data' => [
						'enableEditInView' => false,
						'formated' => 'MODIFIED_BY_FORMATTED_NAME',
						'position' => 'MODIFIED_BY_WORK_POSITION',
						'photoUrl' => 'MODIFIED_BY_PHOTO_URL',
						'showUrl' => 'PATH_TO_MODIFIED_BY_USER',
						'pathToProfile' => $this->getUserPersonalUrlTemplate(),
					],
				];
			}

			if (!empty($documentData['CREATED_BY']))
			{
				$result[] = [
					'name' => 'CREATED_BY',
					'title' => $fields['CREATED_BY'],
					'type' => 'user',
					'isDragEnabled' => false,
					'editable' => false,
					'data' => [
						'enableEditInView' => false,
						'formated' => 'CREATED_BY_FORMATTED_NAME',
						'position' => 'CREATED_BY_WORK_POSITION',
						'photoUrl' => 'CREATED_BY_PHOTO_URL',
						'showUrl' => 'PATH_TO_CREATED_BY_USER',
						'pathToProfile' => $this->getUserPersonalUrlTemplate(),
					],
				];
			}

		}

		return $result;
	}

	private function getEntityConfig(): array
	{
		$config = [
			[
				'title' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_SECTION_COMMON'),
				'name' => 'common',
				'type' => 'section',
				'elements' => [
					['name' => 'TITLE'],
					['name' => 'FILES'],
					['name' => 'CONTRACTOR_ID'],
				],
				'data' => [
					'isChangeable' => false,
					'isRemovable' => false,
				],
			],
			[
				'title' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_SECTION_PRODUCTS'),
				'name' => 'products',
				'type' => 'section',
				'elements' => [
					['name' => 'PRODUCT_LIST'],
					['name' => 'SECTION_LIST'],
				],
				'data' => [
					'isChangeable' => false,
					'isRemovable' => false,
				],
			],
		];

		if ($this->arParams['ID'] > 0)
		{
			$config[] = [
				'title' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_SECTION_ADDITIONALLY'),
				'name' => 'additionally',
				'type' => 'section',
				'elements' => [
					['name' => 'DATE_CREATE'],
					['name' => 'CREATED_BY'],
					['name' => 'DATE_MODIFY'],
					['name' => 'MODIFIED_BY'],
				],
				'data' => [
					'isChangeable' => false,
					'isRemovable' => false,
					'enableToggling' => false,
				],
			];
		}

		return $config;
	}

	private function getEntityData(array $documentData = []): array
	{
		$entityData = [
			'ID' => $this->arResult['ID'],
			'TITLE' => '',
			'IBLOCK_ID' => $this->arResult['IBLOCK_ID'],
			'CONTRACTOR_ID' => null,
			'DATE_MODIFY' => null,
			'DATE_CREATE' => null,
			'MODIFIED_BY' => null,
			'CREATED_BY' => null,
		];

		if ($documentData)
		{
			$entityData['TITLE'] = htmlspecialcharsbx($documentData['TITLE']);
			$entityData['CONTRACTOR_ID'] = $documentData['CONTRACTOR_ID'];

			if ($documentData['DATE_MODIFY'] instanceof Main\Type\DateTime)
			{
				$entityData['DATE_MODIFY'] = $documentData['DATE_MODIFY']->toString();
			}

			if ($documentData['DATE_CREATE'] instanceof Main\Type\DateTime)
			{
				$entityData['DATE_CREATE'] = $documentData['DATE_CREATE']->toString();
			}

			if ($documentData['MODIFIED_BY'])
			{
				$entityData['MODIFIED_BY'] = $documentData['MODIFIED_BY'];
			}

			if ($documentData['CREATED_BY'])
			{
				$entityData['CREATED_BY'] = $documentData['CREATED_BY'];
			}

			if ($documentData['MODIFIED_BY'])
			{
				$entityData = array_merge($entityData, $this->getUserDataToEntity($documentData['MODIFIED_BY'], 'MODIFIED_BY'));
			}
			
			if ($documentData['CREATED_BY'])
			{
				$entityData = array_merge($entityData, $this->getUserDataToEntity($documentData['CREATED_BY'], 'CREATED_BY'));
			}

			$entityData['FILES'] = $documentData['FILES'];
		}

		return array_merge(
			$entityData,
			$this->getContractorEntityData($documentData),
			$this->getProductsEntityData($documentData),
		);
	}

	private function getEntityControllers(): array
	{
		return [
			[
				'name' => 'AGENT_CONTRACT',
				'type' => 'agent_contract',
				'config' => [],
			],
		];
	}

	private function loadDocument(int $id): array
	{
		$agentContractResult = Catalog\v2\AgentContract\Manager::get($id);
		return $agentContractResult->getData();
	}

	private function getProductsEntityData(array $documentData): array
	{
		$productList = [];
		$sectionList = [];
		$entityEditorProductList = [];
		$entityEditorSectionList = [];

		if (isset($documentData['PRODUCTS']))
		{
			foreach ($documentData['PRODUCTS'] as $documentProduct)
			{
				$product = [
					'ID' => $documentProduct['ID'],
					'PRODUCT_ID' => $documentProduct['PRODUCT_ID'],
					'PRODUCT_TYPE' => $documentProduct['PRODUCT_TYPE'],
					'IMAGE' => $documentProduct['IMAGE'],
				];

				$entityEditorProduct =  [
					'PRODUCT_ID' => $documentProduct['PRODUCT_ID'],
					'PRODUCT_TYPE' => $documentProduct['PRODUCT_TYPE'],
					'PRODUCT_NAME' => $documentProduct['PRODUCT_NAME'],
					'IMAGE' => $documentProduct['IMAGE'],
				];

				if ($documentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_PRODUCT)
				{
					$productList[] = $product;
					$entityEditorProductList[] = $entityEditorProduct;
				}
				elseif ($documentProduct['PRODUCT_TYPE'] === Catalog\AgentProductTable::PRODUCT_TYPE_SECTION)
				{
					$sectionList[] = $product;
					$entityEditorSectionList[] = $entityEditorProduct;
				}
			}
		}

		return [
			'PRODUCT_LIST' => $productList,
			'ENTITY_PRODUCT_LIST' => $entityEditorProductList,
			'SECTION_LIST' => $sectionList,
			'ENTITY_SECTION_LIST' => $entityEditorSectionList,
		];
	}

	private function getContractorEntityField(): array
	{
		return [
			'id' => 'CONTRACTOR_ID',
			'name' => 'CONTRACTOR_ID',
			'title' => Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_CONTRACTOR'),
			'type' =>
				$this->contractorsProvider
				? $this->contractorsProvider::getEditorFieldType()
				: 'contractor',
			'isDragEnabled' => false,
			'editable' => true,
			'required' => true,
			'data' =>
				$this->contractorsProvider
				? $this->contractorsProvider::getEditorFieldData()
				: [
					'contractorName' => 'CONTRACTOR_NAME',
				],
		];
	}

	private function getTitle(): string
	{
		return Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_FIELD_TITLE_PLACEHOLDER');
	}

	/**
	 * @param array $document
	 * @return array
	 */
	private function getContractorEntityData(array $documentData): array
	{
		return $this->contractorsProvider
			? $this->getProviderContractorEntityData($documentData)
			: [
				'CONTRACTOR_NAME' => $this->getContractorName($documentData)
			]
		;
	}

	private function getProviderContractorEntityData(array $documentData): array
	{
		return $this->contractorsProvider::getEditorEntityData((int)($documentData['ID'] ?? 0));
	}

	private function getContractorName(array $documentData): string
	{
		$contractorName = '';

		if (isset($documentData['CONTRACTOR_ID']) && $documentData['CONTRACTOR_ID'] > 0)
		{
			$contractor = Catalog\ContractorTable::getRow([
				'select' => ['PERSON_NAME', 'COMPANY'],
				'filter' => ['=ID' => $documentData['CONTRACTOR_ID']],
			]);

			if ($contractor)
			{
				if (!empty($contractor['COMPANY']))
				{
					$contractorName = $contractor['COMPANY'];
				}
				elseif (!empty($contractor['PERSON_NAME']))
				{
					$contractorName = $contractor['PERSON_NAME'];
				}
			}
		}

		return $contractorName;
	}

	protected function getUserDataToEntity(int $userId, string $userReferenceName): array
	{
		$result = [];

		$user = Main\UserTable::getRow([
			'select' => [
				'ID', 'LOGIN', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'TITLE', 'PERSONAL_PHOTO', 'WORK_POSITION', 'IS_REAL_USER',
			],
			'filter' => ['=ID' => $userId]
		]);

		if (is_array($user))
		{
			$result[$userReferenceName . '_LOGIN'] = $user['LOGIN'];
			$result[$userReferenceName . '_NAME'] = $user['NAME'] ?? '';
			$result[$userReferenceName . '_SECOND_NAME'] = $user['SECOND_NAME'] ?? '';
			$result[$userReferenceName . '_LAST_NAME'] = $user['LAST_NAME'] ?? '';
			$result[$userReferenceName . '_PERSONAL_PHOTO'] = $user['PERSONAL_PHOTO'] ?? '';
			$result[$userReferenceName . '_FORMATTED_NAME'] =
				\CUser::FormatName(
					\CSite::GetNameFormat(),
					$user,
					true,
					false
				)
			;
		}

		$photoId = isset($result[$userReferenceName . '_PERSONAL_PHOTO'])
			? (int)$result[$userReferenceName . '_PERSONAL_PHOTO']
			: 0
		;

		if ($photoId > 0)
		{
			$fileInfo = \CFile::ResizeImageGet(
				$photoId,
				[
					'width' => 60,
					'height'=> 60,
				],
				BX_RESIZE_IMAGE_EXACT
			);
			if (is_array($fileInfo) && isset($fileInfo['src']))
			{
				$result[$userReferenceName . '_PHOTO_URL'] = $fileInfo['src'];
			}
		}

		$result['PATH_TO_' . $userReferenceName . '_USER'] =
			\CComponentEngine::MakePathFromTemplate(
				$this->getUserPersonalUrlTemplate(),
				[
					'USER_ID' => $userId,
					'ID' => $userId,
					'user_id' => $userId,
				]
			);

		return $result;
	}

	private function getUserPersonalUrlTemplate(): string
	{
		return Main\Config\Option::get('intranet', 'path_user', self::PATH_TO_USER_PROFILE_DEFAULT, $this->getSiteId());
	}

	private function checkModules(): bool
	{
		if (!Main\Loader::includeModule('catalog'))
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_MODULE_CATALOG_NOT_FOUND');
			return false;
		}

		return true;
	}

	private function checkPermission(): bool
	{
		if (!Catalog\v2\AgentContract\AccessController::check())
		{
			$this->arResult['ERROR_MESSAGES'][] = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_PERMISSION_DENIED');
			return false;
		}

		return true;
	}

	public function executeComponent()
	{
		if (
			$this->checkModules()
			&& $this->checkPermission()
			&& $this->checkRequiredParams()
		)
		{
			$this->initResult();
			$this->prepareResult();
		}

		$this->includeComponentTemplate();
	}

	public function saveAction(array $data = []): array
	{
		if (!Catalog\v2\AgentContract\AccessController::check())
		{
			$error = Main\Localization\Loc::getMessage('CATALOG_AGENT_CONTRACT_DETAIL_PERMISSION_DENIED');
			$this->errorCollection->setError(new Main\Error($error));
			$response['ERROR'] = $error;
			return $response;
		}

		if (empty($data))
		{
			$requestData = $this->request->get('data');
			$data = ($requestData && is_array($requestData)) ? $requestData : [];
		}

		$fields = [];

		if (!empty($data['TITLE']))
		{
			$fields['TITLE'] = $data['TITLE'];
		}

		$fields['FILES'] = $data['FILES'];

		if (!empty($data['CONTRACTOR_ID']))
		{
			$fields['CONTRACTOR_ID'] = $data['CONTRACTOR_ID'];
		}

		$products = $data['PRODUCT_LIST'] ?: [];
		if ($products)
		{
			$products = \CUtil::JsObjectToPhp($products);
		}

		$sections = $data['SECTION_LIST'] ?: [];
		if ($sections)
		{
			$sections = \CUtil::JsObjectToPhp($sections);
		}

		$productFields = array_merge($products, $sections);
		$productFields = array_map(
			static function ($productField) {
				$productField['PRODUCT_TYPE'] = mb_strtoupper($productField['PRODUCT_TYPE']);
				return $productField;
			},
			$productFields
		);

		$contractorProviderSaveResult = null;
		if ($this->contractorsProvider)
		{
			$clientData = $data['CLIENT_DATA'] ?: [];
			if ($clientData)
			{
				$contractorProviderSaveResult = $this->contractorsProvider::onBeforeDocumentSave(
					$fields + ['CLIENT_DATA' => $clientData]
				);
			}
		}

		$response = [];

		$id = (int)($this->arParams['ID'] ?? 0);
		if ($id > 0)
		{
			$fields['FILES_del'] = $data['FILES_del'] ?? [];
			$result = Catalog\v2\AgentContract\Manager::update($id, $fields, $productFields);
		}
		else
		{
			$result = Catalog\v2\AgentContract\Manager::add($fields, $productFields);
			if ($result->isSuccess())
			{
				$data = $result->getData();
				$id = $data['ID'];
				$response['REDIRECT_URL'] = $this->getDetailComponentPath($id);
			}
		}

		if ($result->isSuccess())
		{
			if ($this->contractorsProvider && $contractorProviderSaveResult)
			{
				$this->contractorsProvider::onAfterDocumentSaveSuccess(
					$id,
					$contractorProviderSaveResult
				);
			}

			$documentData = $this->loadDocument($id);
			$response += [
				'ENTITY_ID' => $id,
				'ENTITY_DATA' => $this->getEntityData($documentData),
			];
		}
		else
		{
			if ($this->contractorsProvider && $contractorProviderSaveResult)
			{
				$this->contractorsProvider::onAfterDocumentSaveFailure($id, $contractorProviderSaveResult);
			}

			$this->errorCollection->add($result->getErrors());
			$response['ERROR'] = implode('<br>', $result->getErrorMessages());
		}

		return $response;
	}

	private function getDetailComponentPath(int $id): string
	{
		$pathToPaymentDetailTemplate = $this->arParams['PATH_TO']['DETAIL'] ?? '';
		if ($pathToPaymentDetailTemplate === '')
		{
			return $pathToPaymentDetailTemplate;
		}

		return str_replace('#AGENT_CONTRACT_ID#', $id, $pathToPaymentDetailTemplate);
	}
}