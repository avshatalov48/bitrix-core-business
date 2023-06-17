<?php

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Model\StoreDocumentElement;
use Bitrix\Catalog\StoreDocumentBarcodeTable;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\Url\InventoryManagementSourceBuilder;
use Bitrix\Catalog\v2\Integration\UI\EntityEditor\StoreDocumentProvider;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;
use Bitrix\Main;
use Bitrix\UI;
use Bitrix\Crm\Integration\DocumentGeneratorManager;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\StoreDocumentArrival;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\StoreDocumentStoreAdjustment;
use Bitrix\Crm\Integration\DocumentGenerator\DataProvider\StoreDocumentMoving;
use Bitrix\Catalog\v2\Contractor;
use Bitrix\Crm\Settings\EntityEditSettings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Main\Loader::includeModule('catalog');
Main\Loader::includeModule('currency');
Main\Loader::includeModule('sale');

class CatalogStoreDocumentDetailComponent extends CBitrixComponent implements Controllerable
{
	public const COLLECT_RIGHT_COLUMN_EVENT = 'DocumentCard:onCollectRightColumnContent';
	public const CONDUCT_FAILURE_AFTER_SAVE_EVENT = 'DocumentCard:onConductFailureAfterSave';

	/** @var int $documentId */
	private $documentId;
	/** @var string $documentType */
	private $documentType;
	/** @var array $document */
	private $document;
	/** @var AccessController */
	private $accessController;
	private bool $canSelectDocumentType = true;

	/** @var Contractor\Provider\IProvider|null */
	private ?Contractor\Provider\IProvider $contractorsProvider;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->accessController = AccessController::getCurrent();
		$this->contractorsProvider = Contractor\Provider\Manager::getActiveProvider();
	}

	public function onPrepareComponentParams($arParams)
	{
		if (isset($arParams['DOCUMENT_ID']))
		{
			$this->documentId = (int)$arParams['DOCUMENT_ID'] ?: null;
		}
		if (isset($arParams['DOCUMENT_TYPE']))
		{
			$this->documentType = $arParams['DOCUMENT_TYPE'];

			if (
				$this->isNew()
				&& in_array($this->documentType, [StoreDocumentTable::TYPE_ARRIVAL, StoreDocumentTable::TYPE_STORE_ADJUSTMENT], true)
			)
			{
				$this->replaceDocumentTypeToAccessibleType();
			}
		}
		if (!isset($arParams['PATH_TO']))
		{
			$arParams['PATH_TO'] = [];
		}

		return parent::onPrepareComponentParams($arParams);
	}

	/**
	 * Replace selected type in params of component to accessible document type.
	 *
	 * Checks access only `modify` right, because the type change is relevant only at creation.
	 *
	 * @return void
	 */
	private function replaceDocumentTypeToAccessibleType(): void
	{
		$replaces = [
			StoreDocumentTable::TYPE_ARRIVAL => StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT => StoreDocumentTable::TYPE_ARRIVAL,
		];

		foreach ($replaces as $baseType => $anotherType)
		{
			$can = $this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $baseType);
			if (!$can)
			{
				$this->canSelectDocumentType = false;

				// change current type
				if ($this->documentType === $baseType)
				{
					$can = $this->accessController->checkByValue(ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY, $anotherType);
					if ($can)
					{
						$this->documentType = $anotherType;
					}
				}
			}
		}
	}

	protected function listKeysSignedParameters()
	{
		return [
			'DOCUMENT_ID',
			'DOCUMENT_TYPE',
			'PATH_TO',
		];
	}

	public function executeComponent()
	{
		$this->checkParams();
		$this->loadDocument();
		if (!empty($this->arResult['ERROR_MESSAGES']))
		{
			$this->includeComponentTemplate();
			return;
		}
		$this->initializeDocumentFields();

		$this->arResult['INCLUDE_CRM_ENTITY_EDITOR'] = Contractor\Provider\Manager::isActiveProviderByModule('crm');
		$this->arResult['GUID'] = $this->arResult['FORM']['GUID'];
		$this->arResult['TOOLBAR_ID'] = "toolbar_store_document_{$this->documentId}";
		$this->arResult['IS_MAIN_CARD_READ_ONLY'] = $this->arResult['FORM']['READ_ONLY'];
		$this->arResult['DOCUMENT_TYPE'] = $this->getDocumentType();
		$this->arResult['FOCUSED_TAB'] = $this->request->get('focusedTab');

		$this->setDropdownTypes();

		$this->getAdditionalEntityEditorActions();

		$this->collectRightColumnContent();

		$this->checkIfInventoryManagementIsUsed();

		$this->arResult['BUTTONS'] = $this->getToolbarButtons();

		$this->arResult['INVENTORY_MANAGEMENT_SOURCE'] =
			InventoryManagementSourceBuilder::getInstance()->getInventoryManagementSource()
		;

		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	private function getToolbarButtons(): array
	{
		$result = [];

		$documentType2ProviderMap = [
			StoreDocumentTable::TYPE_ARRIVAL => StoreDocumentArrival::class,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT => StoreDocumentStoreAdjustment::class,
			StoreDocumentTable::TYPE_MOVING => StoreDocumentMoving::class,
		];

		$isDocumentButtonAvailable = (
			Main\Loader::includeModule('crm')
			&& DocumentGeneratorManager::getInstance()->isDocumentButtonAvailable()
			&& isset($this->arResult['DOCUMENT']['ID'])
			&& (int)$this->arResult['DOCUMENT']['ID'] > 0
			&& isset($this->arResult['DOCUMENT']['DOC_TYPE'])
			&& isset($documentType2ProviderMap[$this->arResult['DOCUMENT']['DOC_TYPE']])
		);
		if ($isDocumentButtonAvailable)
		{
			$result[] = [
				'TEXT' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_DOCUMENT_BUTTON'),
				'TYPE' => 'crm-document-button',
				'PARAMS' => DocumentGeneratorManager::getInstance()->getDocumentButtonParameters(
					$documentType2ProviderMap[$this->arResult['DOCUMENT']['DOC_TYPE']],
					$this->arResult['DOCUMENT']['ID']
				),
			];
		}

		return $result;
	}

	private function checkParams()
	{
		if (!$this->documentId && !$this->documentType)
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_DOCUMENT_TYPE_NOT_SPECIFIED_ERROR');
		}
	}

	private function getDocumentType()
	{
		if ($this->documentType)
		{
			return $this->documentType;
		}

		$this->loadDocument();
		return $this->document['DOC_TYPE'];
	}

	private function initializeDocumentFields(): void
	{
		$this->arResult['DOCUMENT'] = $this->document;

		$editorProvider = $this->getEditorProvider();
		$this->arResult['FORM'] = $editorProvider->getFields();
		$this->addPreloadedFields();
	}

	private function addPreloadedFields(): void
	{
		$preloadedDocumentFields = $this->arParams['PRELOADED_FIELDS']['DOCUMENT_FIELDS'] ?? null;
		if (!is_array($preloadedDocumentFields) || empty($preloadedDocumentFields))
		{
			return;
		}

		if (isset($preloadedDocumentFields['TOTAL'], $preloadedDocumentFields['CURRENCY']))
		{
			$total = $preloadedDocumentFields['TOTAL'];
			$currency = $preloadedDocumentFields['CURRENCY'];
			$preloadedDocumentFields['FORMATTED_TOTAL'] = CCurrencyLang::CurrencyFormat($total, $currency, false);
			$preloadedDocumentFields['FORMATTED_TOTAL_WITH_CURRENCY'] = CCurrencyLang::CurrencyFormat($total, $currency);
		}

		$this->arResult['FORM']['ENTITY_DATA'] = array_merge($this->arResult['FORM']['ENTITY_DATA'], $preloadedDocumentFields);
	}

	private function getEditorProvider(): StoreDocumentProvider
	{
		if ($this->document)
		{
			return StoreDocumentProvider::createByArray($this->document);
		}

		return StoreDocumentProvider::createByType($this->getDocumentType());
	}

	private function loadDocument()
	{
		if (!$this->checkDocumentBaseRights())
		{
			if (Main\Loader::includeModule('crm'))
			{
				$this->arResult['ERROR_MESSAGES'][] = [
					'TITLE' => Loc::getMessage('CATALOG_STORE_DOCUMENT_ERR_ACCESS_DENIED'),
					'HELPER_CODE' => 15955386,
					'LESSON_ID' => 25010,
					'COURSE_ID' => 48,
				];
			}
			else
			{
				$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_VIEW_RIGHTS_ERROR');
			}
			return;
		}

		if (!$this->documentId)
		{
			return;
		}

		$document = StoreDocumentTable::getList([
			'select' => [
				'*',
				'CONTRACTOR_REF_' => 'CONTRACTOR',
			],
			'filter' => [
				'=ID' => $this->documentId,
			],
		])->fetch();
		if ($document)
		{
			$document = $this->fillDefaultDocumentFields($document);
			$this->document = $document;
			$this->documentType = $document['DOC_TYPE'];
		}
		else
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_DOCUMENT_NOT_FOUND_ERROR');
		}

		if (!$this->checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_VIEW_RIGHTS_ERROR');
		}
	}

	private function fillDefaultDocumentFields($document)
	{
		$resultDocument = $document;
		if (!isset($resultDocument['TITLE']))
		{
			$resultDocument['TITLE'] = StoreDocumentTable::getTypeList(true)[$resultDocument['DOC_TYPE']];
		}

		return $resultDocument;
	}

	private function checkDocumentBaseRights(): bool
	{
		return
			$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
			&& $this->accessController->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
			;
	}

	private function checkDocumentReadRights(): bool
	{
		return
			$this->checkDocumentBaseRights()
			&& $this->accessController->checkByValue(
				ActionDictionary::ACTION_STORE_DOCUMENT_VIEW,
				$this->getDocumentType()
			)
			;
	}

	private function checkDocumentWriteRights(): bool
	{
		return
			$this->checkDocumentBaseRights()
			&& $this->accessController->checkByValue(
				ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
				$this->getDocumentType()
			)
			;
	}

	private function checkDocumentConductRights(): bool
	{
		return
			$this->checkDocumentBaseRights()
			&& $this->accessController->checkByValue(
				ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT,
				$this->getDocumentType()
			)
			;
	}

	private function checkDocumentCancelRights(): bool
	{
		return
			$this->checkDocumentBaseRights()
			&& $this->accessController->checkByValue(
				ActionDictionary::ACTION_STORE_DOCUMENT_CANCEL,
				$this->getDocumentType()
			)
			;
	}

	public function saveAction($fields = []): array
	{
		if (!$this->checkDocumentWriteRights())
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_WRITE_RIGHTS_ERROR'),
			];
		}

		if (!$fields)
		{
			$fields = $this->request->get('data') ?: [];
		}

		$saveResult = $this->saveDocument($fields);

		if (!$saveResult->isSuccess())
		{
			$errorMessage = implode('<br>', $saveResult->getErrorMessages());
			return [
				'ERROR' => $errorMessage,
			];
		}

		$entityId = $saveResult->getData()['ENTITY_ID'];
		if ($entityId)
		{
			$result = [
				'ENTITY_ID' => $entityId,
			];

			if ($this->isNew())
			{
				$result['REDIRECT_URL'] = $this->getUrlToDocumentDetail($entityId);
			}
			else
			{
				$result['ENTITY_DATA'] = $this->getEntityDataForResponse();
			}

			return $result;
		}

		return [
			'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_SAVE_ERROR'),
		];
	}

	public function saveAndConductAction($fields = []): array
	{
		if (!$this->checkDocumentWriteRights() || !$this->checkDocumentConductRights())
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_WRITE_RIGHTS_ERROR'),
			];
		}

		if (!$fields)
		{
			$fields = $this->request->get('data') ?: [];
		}

		$saveResult = $this->saveDocument($fields);

		if (!$saveResult->isSuccess())
		{
			$errorMessage = implode('<br>', $saveResult->getErrorMessages());
			return [
				'ERROR' => $errorMessage,
			];
		}

		if (
			$this->getDocumentType() === StoreDocumentTable::TYPE_ARRIVAL
			|| $this->getDocumentType() ===	StoreDocumentTable::TYPE_STORE_ADJUSTMENT
		)
		{
			$decodedProducts = $this->decodeProducts($fields['DOCUMENT_PRODUCTS']);
			if (is_array($decodedProducts))
			{
				$this->updateBarcodes($decodedProducts);
			}
		}

		$entityId = $saveResult->getData()['ENTITY_ID'];

		if ($entityId)
		{
			$closeSliderOnSave = true;

			$userId = Main\Engine\CurrentUser::get()->getId();

			global $APPLICATION;
			$APPLICATION->ResetException();

			$isConducted = CCatalogDocs::conductDocument($entityId, $userId);

			if (!$isConducted)
			{
				if ($this->isNew())
				{
					$closeSliderOnSave = false;
					$eventParameters = [
						'DOCUMENT_ID' => $entityId,
						'USER_ID' => $userId,
						'ERROR_MESSAGE' =>
							$APPLICATION->GetException()
								? $APPLICATION->GetException()->GetString()
								: Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_CONDUCT_ERROR')
						,
					];

					$event = new Main\Event(
						'catalog',
						self::CONDUCT_FAILURE_AFTER_SAVE_EVENT,
						$eventParameters
					);
					$event->send();
				}
				else
				{
					$errorMessage = $APPLICATION->GetException() ? $APPLICATION->GetException()->GetString() : '';
					if ($errorMessage)
					{
						return [
							'ERROR' => $errorMessage,
						];
					}

					return [
						'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_CONDUCT_ERROR'),
					];
				}
			}

			return [
				'ENTITY_ID' => $entityId,
				'REDIRECT_URL' => $this->getUrlToDocumentDetail($entityId, $closeSliderOnSave),
				'EVENT_PARAMS' => [
					'showNotificationOnClose' => $isConducted ? 'Y' : 'N',
				],
			];
		}

		return [
			'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_SAVE_ERROR'),
		];
	}

	public function conductAction(): array
	{
		if (!$this->checkDocumentConductRights())
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_WRITE_RIGHTS_ERROR'),
			];
		}

		$userId = Main\Engine\CurrentUser::get()->getId();

		global $APPLICATION;
		$APPLICATION->ResetException();

		if (
			$this->getDocumentType() === StoreDocumentTable::TYPE_ARRIVAL
			|| $this->getDocumentType() ===	StoreDocumentTable::TYPE_STORE_ADJUSTMENT
		)
		{
			$documentProducts = StoreDocumentBarcodeTable::getList([
				'filter' => [
					'=DOCUMENT_ELEMENT.DOC_ID' => $this->documentId,
				],
				'select' => ['BARCODE', 'SKU_ID' => 'DOCUMENT_ELEMENT.ELEMENT_ID']
			])->fetchAll();


			$this->updateBarcodes($documentProducts);
		}

		$isConducted = CCatalogDocs::conductDocument($this->documentId, $userId);

		if (!$isConducted)
		{
			$errorMessage = $APPLICATION->GetException() ? $APPLICATION->GetException()->GetString() : '';
			if ($errorMessage)
			{
				return [
					'ERROR' => $errorMessage,
				];
			}

			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_CONDUCT_ERROR'),
			];
		}

		return [
			'REDIRECT_URL' => $this->getUrlToDocumentDetail($this->documentId, true),
			'EVENT_PARAMS' => [
				'showNotificationOnClose' => 'Y',
			],
		];
	}

	public function cancelConductAction(): array
	{
		if (!$this->checkDocumentCancelRights())
		{
			return [
				'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_NO_WRITE_RIGHTS_ERROR'),
			];
		}

		global $APPLICATION;
		$APPLICATION->ResetException();

		$isCancelled = CCatalogDocs::cancellationDocument($this->documentId);

		if (!$isCancelled && $APPLICATION->GetException())
		{
			return [
				'ERROR' => $APPLICATION->GetException()->GetString(),
			];
		}

		if ($isCancelled)
		{
			return [
				'REDIRECT_URL' => $this->getUrlToDocumentDetail($this->documentId),
			];
		}

		return [
			'ERROR' => Loc::getMessage('CATALOG_STORE_DOCUMENT_DETAIL_CANCEL_ERROR'),
		];
	}

	/**
	 * @param $fields
	 * @return Main\Result
	 */
	private function saveDocument($fields): Main\Result
	{
		$contractorProviderSaveResult = null;
		if ($this->contractorsProvider)
		{
			$contractorProviderSaveResult = $this->contractorsProvider::onBeforeDocumentSave($fields);
		}

		$result = new Main\Result();
		$entityId = null;
		if ($this->isNew())
		{
			$addDocumentResult = $this->addDocument($fields);
			if ($addDocumentResult->isSuccess())
			{
				$entityId = (int)$addDocumentResult->getData()['ENTITY_ID'];
			}
			else
			{
				$result->addErrors($addDocumentResult->getErrors());
			}
		}
		else
		{
			$updateDocumentResult = $this->updateDocument($fields);
			if ($updateDocumentResult->isSuccess())
			{
				$entityId = (int)$updateDocumentResult->getData()['ENTITY_ID'];
			}
			else
			{
				$result->addErrors($updateDocumentResult->getErrors());
			}
		}

		if (!$result->isSuccess())
		{
			if ($this->contractorsProvider)
			{
				$this->contractorsProvider::onAfterDocumentSaveFailure($entityId, $contractorProviderSaveResult);
			}

			return $result;
		}

		if ($this->contractorsProvider)
		{
			$this->contractorsProvider::onAfterDocumentSaveSuccess(
				$entityId,
				$contractorProviderSaveResult,
				[
					'entityEditorSettings' => new EntityEditSettings($this->getEditorProvider()->getConfigId()),
				]
			);
		}

		if ($result->isSuccess())
		{
			$result->setData(['ENTITY_ID' => $entityId]);
		}

		return $result;
	}

	private function addDocument($fields): Main\Result
	{
		global $APPLICATION;
		$result = new Main\Result();

		$prepareFieldsResult = $this->prepareFieldsToAdd($fields);
		if (!$prepareFieldsResult->isSuccess())
		{
			$result->addErrors($prepareFieldsResult->getErrors());
			return $result;
		}
		$fieldsToSave = $prepareFieldsResult->getData()['PREPARED_FIELDS'];

		$APPLICATION->ResetException();
		$entityId = CCatalogDocs::add($fieldsToSave);
		if ($APPLICATION->GetException())
		{
			$result->addError(new Main\Error($APPLICATION->GetException()->GetString()));
			return $result;
		}

		$result->setData(['ENTITY_ID' => $entityId]);
		return $result;
	}

	private function updateDocument($fields): Main\Result
	{
		global $APPLICATION;
		$result = new Main\Result();

		$prepareFieldsResult = $this->prepareFieldsToUpdate($fields);
		if (!$prepareFieldsResult->isSuccess())
		{
			$result->addErrors($prepareFieldsResult->getErrors());
			return $result;
		}
		$fieldsToUpdate = $prepareFieldsResult->getData()['PREPARED_FIELDS'];

		$APPLICATION->ResetException();
		CCatalogDocs::update($this->documentId, $fieldsToUpdate['GENERAL']);
		if ($APPLICATION->GetException())
		{
			$result->addError(new Main\Error($APPLICATION->GetException()->GetString()));
			return $result;
		}

		if (array_key_exists('ELEMENT', $fieldsToUpdate))
		{
			$this->updateElements($fieldsToUpdate['ELEMENT']);
		}

		$result->setData(['ENTITY_ID' => $this->documentId]);

		return $result;
	}

	private function updateBarcodes(array $fields): void
	{
		$barcodeElementMap = [];
		foreach ($fields as $field)
		{
			$barcode = null;
			if (isset($field['DOC_BARCODE']) && !empty($field['DOC_BARCODE']))
			{
				$barcode = $field['DOC_BARCODE'];
			}
			elseif (isset($field['BARCODE']) && !empty($field['BARCODE']))
			{
				$barcode = $field['BARCODE'];
			}
			$skuId = (int)($field['SKU_ID']);
			if ($skuId > 0 && $barcode)
			{
				$barcodeElementMap[$barcode] = $skuId;
			}
		}

		if (empty($barcodeElementMap))
		{
			return;
		}

		$existedBarcodeRaw = Catalog\StoreBarcodeTable::getList([
			'filter' => ['=BARCODE' => array_keys($barcodeElementMap)],
			'select' => ['BARCODE']
		]);

		while ($barcodeData = $existedBarcodeRaw->fetch())
		{
			$barcode = $barcodeData['BARCODE'];
			if (isset($barcodeElementMap[$barcode]))
			{
				unset($barcodeElementMap[$barcode]);
			}
		}

		foreach ($barcodeElementMap as $barcode => $skuId)
		{
			CCatalogStoreBarCode::add([
				'PRODUCT_ID' => $skuId,
				'BARCODE' => $barcode,
				'STORE_ID' => 0,
				'CREATED_BY' => Main\Engine\CurrentUser::get()->getId(),
				'MODIFIED_BY' => Main\Engine\CurrentUser::get()->getId(),
			]);
		}
	}

	private function prepareFieldsToAdd(array $fields): Main\Result
	{
		$result = new Main\Result();
		$preparedFields = [];

		foreach ($fields as $fieldName => $value)
		{
			$prepareFieldResult = $this->prepareFieldByName($fieldName, $value);
			if ($prepareFieldResult->isSuccess() && isset($prepareFieldResult->getData()['PREPARED_VALUE']))
			{
				$preparedFields[$fieldName] = $prepareFieldResult->getData()['PREPARED_VALUE'];
			}
			else
			{
				$result->addErrors($prepareFieldResult->getErrors());
			}
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		if (isset($preparedFields['TOTAL']))
		{
			unset($preparedFields['TOTAL']);
		}

		if (empty($preparedFields['CURRENCY']))
		{
			if ($this->isNew() && isset($preparedFields['TOTAL_WITH_CURRENCY']))
			{
				$preparedFields['CURRENCY'] = $preparedFields['TOTAL_WITH_CURRENCY'];
			}
			else
			{
				$preparedFields['CURRENCY'] = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
			}
		}
		$preparedFields['DOC_TYPE'] = $this->getDocumentType();
		$preparedFields['SITE_ID'] = $this->getSiteId();
		$preparedFields['CREATED_BY'] = Main\Engine\CurrentUser::get()->getId();
		$preparedFields['MODIFIED_BY'] = Main\Engine\CurrentUser::get()->getId();

		if ($fields['DOCUMENT_PRODUCTS'])
		{
			$products = $this->decodeProducts($fields['DOCUMENT_PRODUCTS']);
			$element = [];
			$prepareElementResult = $this->prepareElementField($products);
			if ($prepareElementResult->isSuccess())
			{
				$element = $prepareElementResult->getData()['ELEMENTS'];
			}
			else
			{
				$result->addErrors($prepareElementResult->getErrors());
				return $result;
			}
			$preparedFields['ELEMENT'] = $element;
			$preparedFields['TOTAL'] = $this->calculateDocumentTotalFromElement($element);
		}

		$validFieldNames = array_keys(StoreDocumentTable::getEntity()->getFields());
		$validFieldNames = array_merge($validFieldNames, ['ELEMENT', 'DOCUMENT_FILES']);
		$preparedFields = array_intersect_key($preparedFields, array_flip($validFieldNames));

		$preparedFields = $this->prepareFilesToUpdate($preparedFields);

		$result->setData(['PREPARED_FIELDS' => $preparedFields]);

		return $result;
	}

	private function prepareFieldsToUpdate(array $fields): Main\Result
	{
		$result = new Main\Result();
		$preparedFields = [];
		$generalFields = [];

		foreach ($fields as $fieldName => $value)
		{
			$prepareFieldResult = $this->prepareFieldByName($fieldName, $value);
			if ($prepareFieldResult->isSuccess() && isset($prepareFieldResult->getData()['PREPARED_VALUE']))
			{
				$generalFields[$fieldName] = $prepareFieldResult->getData()['PREPARED_VALUE'];
			}
			else
			{
				$result->addErrors($prepareFieldResult->getErrors());
			}
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		if (isset($generalFields['TOTAL']))
		{
			unset($generalFields['TOTAL']);
		}

		$generalFields['MODIFIED_BY'] = Main\Engine\CurrentUser::get()->getId();

		if (array_key_exists('DOCUMENT_PRODUCTS', $fields))
		{
			$products = $this->decodeProducts($fields['DOCUMENT_PRODUCTS']);
			$element = [];
			$prepareElementResult = $this->prepareElementField($products);
			if ($prepareElementResult->isSuccess())
			{
				$element = $prepareElementResult->getData()['ELEMENTS'];
			}
			else
			{
				$result->addErrors($prepareElementResult->getErrors());
				return $result;
			}
			$preparedFields['ELEMENT'] = $element;
			unset($generalFields['DOCUMENT_PRODUCTS']);
			$generalFields['TOTAL'] = $this->calculateDocumentTotalFromElement($element);
		}

		$generalFields = $this->prepareFilesToUpdate($generalFields);

		$validFieldNames = array_keys(StoreDocumentTable::getEntity()->getFields());
		$validFieldNames = array_merge($validFieldNames, ['ELEMENT', 'DOCUMENT_FILES']);
		$generalFields = array_intersect_key($generalFields, array_flip($validFieldNames));

		$preparedFields['GENERAL'] = $generalFields;

		$result->setData(['PREPARED_FIELDS' => $preparedFields]);

		return $result;
	}

	private function prepareFilesToUpdate(array $fields): array
	{
		$filesExists = isset($fields['DOCUMENT_FILES']) && is_array($fields['DOCUMENT_FILES']);
		$filesDelete = isset($fields['DOCUMENT_FILES_del']) && is_array($fields['DOCUMENT_FILES_del']);
		if ($filesExists || $filesDelete)
		{
			$result = [];
			if ($filesExists)
			{
				$fileList = $fields['DOCUMENT_FILES'];
				Main\Type\Collection::normalizeArrayValuesByInt($fileList, false);
				$fileList = \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
					'document_files_uploader',
					$fileList
				);
				foreach ($fileList as $id)
				{
					$result[$id] = (string)$id;
				}
			}

			if ($filesDelete)
			{
				$deleteList = $fields['DOCUMENT_FILES_del'];
				Main\Type\Collection::normalizeArrayValuesByInt($deleteList, false);
				foreach ($deleteList as $id)
				{
					$result[$id] = 'delete' . $id;
				}
			}

			$fields['DOCUMENT_FILES'] = array_values($result);
			unset($result);
		}

		return $fields;
	}

	private function prepareFieldByName($fieldName, $value): Main\Result
	{
		// $result = $value;
		$result = new Main\Result();
		$preparedValue = $value;
		if (!empty($preparedValue))
		{
			switch ($fieldName)
			{
				case 'DATE_DOCUMENT':
				case 'ITEMS_ORDER_DATE':
				case 'ITEMS_RECEIVED_DATE':
					if (Main\Type\DateTime::isCorrect($value))
					{
						$preparedValue = Main\Type\DateTime::createFromUserTime($value);
					}
					else
					{
						$result->addError(
							new Main\Error(
								Loc::getMessage(
									'CATALOG_STORE_DOCUMENT_DETAIL_INCORRECT_VALUE',
									['#FIELD_NAME#' => StoreDocumentProvider::getFieldTitle($fieldName)]
								)
							)
						);
						return $result;
					}
			}
		}

		$result->setData(['PREPARED_VALUE' => $preparedValue]);

		return $result;
	}

	private function prepareBarcodes($products): array
	{
		$productIds = array_unique(array_column($products, 'SKU_ID'));
		$existingBarcodes = [];
		$existingBarcodesRes = \Bitrix\Catalog\StoreBarcodeTable::getList([
			'select' => ['BARCODE', 'PRODUCT_ID'],
			'filter' => ['PRODUCT_ID' => $productIds],
		]);
		while ($existingBarcode = $existingBarcodesRes->fetch())
		{
			$productId = $existingBarcode['PRODUCT_ID'];
			if (!isset($existingBarcodes[$productId]))
			{
				$existingBarcodes[$productId] = [];
			}
			$existingBarcodes[$productId][] = $existingBarcode['BARCODE'];
		}

		$result = [];

		foreach ($products as $product)
		{
			$productId = $product['SKU_ID'];
			$newBarcode = $product['BARCODE'];
			if (is_array($existingBarcodes[$productId]) && in_array($newBarcode, $existingBarcodes[$productId]))
			{
				continue;
			}

			if (!isset($result[$productId]))
			{
				$result[$productId] = [];
			}
			$result[$productId][] = $newBarcode;
		}

		return $result;
	}

	public function configureActions()
	{
		return [];
	}

	private function isNew(): bool
	{
		return $this->documentId === null;
	}

	private function setDropdownTypes()
	{
		$dropdownRequiringTypes = [
			StoreDocumentTable::TYPE_ARRIVAL,
			StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
		];
		if (!$this->canSelectDocumentType || !in_array($this->getDocumentType(), $dropdownRequiringTypes))
		{
			$this->arResult['DROPDOWN_TYPES'] = [];
			return;
		}

		switch ($this->getDocumentType())
		{
			case StoreDocumentTable::TYPE_ARRIVAL:
			case StoreDocumentTable::TYPE_STORE_ADJUSTMENT:
				$this->arResult['DROPDOWN_TYPES'] = [
					StoreDocumentTable::TYPE_ARRIVAL,
					StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
				];
				return;
			default:
				$this->arResult['DROPDOWN_TYPES'] = [];
				return;
		}
	}

	/**
	 * Products of document.
	 *
	 * @return array
	 */
	private function getDocumentProducts(): array
	{
		if (!$this->documentId)
		{
			return [];
		}

		$result = [];

		$rows = StoreDocumentElementTable::getList([
			'filter' => [
				'=DOC_ID' => $this->documentId,
			],
		]);
		foreach ($rows as $row)
		{
			$id = (int)$row['ID'];
			$result[$id] = $row;
		}

		return $result;
	}

	/**
	 * Checks whether the fields of the element have changed.
	 *
	 * @param array $old
	 * @param array $new
	 *
	 * @return bool
	 */
	private function isChangedElement(array $old, array $new): bool
	{
		$checkedFields = [
			'STORE_FROM' => 'intval',
			'STORE_TO' => 'intval',
			'ELEMENT_ID' => 'intval',
			'AMOUNT' => 'floatval',
			'PURCHASING_PRICE' => 'floatval',
			'BASE_PRICE' => 'floatval',
			'BASE_PRICE_EXTRA' => 'floatval',
			'BASE_PRICE_EXTRA_RATE' => 'strval',
		];

		foreach ($checkedFields as $name => $typeCallback)
		{
			$oldValue =  call_user_func($typeCallback, $old[$name] ?? null);
			$newValue =  call_user_func($typeCallback, $new[$name] ?? null);

			if ($oldValue !== $newValue)
			{
				return true;
			}
		}

		return false;
	}

	private function decodeProducts($encodedProducts)
	{
		return CUtil::JsObjectToPhp($encodedProducts);
	}

	private function prepareElementField($products): Main\Result
	{
		$result = new Main\Result();

		$elements = [];
		$documentBarcodesMap = [];
		$existElements = $this->getDocumentProducts();

		if (!isset($products))
		{
			$products = [];
		}

		foreach ($products as $product)
		{
			if (!$product['SKU_ID'])
			{
				$result->addError(new Main\Error(Loc::getMessage('CATALOG_STORE_DOCUMENT_NO_PRODUCT')));
				return $result;
			}
			$elementFields = [
				'AMOUNT' => $product['AMOUNT'],
				'ELEMENT_ID' => $product['SKU_ID'],
				'PURCHASING_PRICE' => $product['PURCHASING_PRICE'],
				'BASE_PRICE' => $product['BASE_PRICE'],
				'BASE_PRICE_EXTRA' => $product['BASE_PRICE_EXTRA'],
				'BASE_PRICE_EXTRA_RATE' => $product['BASE_PRICE_EXTRA_RATE'],
			];

			if (isset($product['DOC_BARCODE']) && !empty($product['DOC_BARCODE']))
			{
				$elementFields['BARCODE'] = [
					$product['DOC_BARCODE'],
				];
				$documentBarcodesMap[] = [
					'SKU_ID' => (int)$product['SKU_ID'],
					'BARCODE' => $product['DOC_BARCODE'],
				];
			}
			elseif (isset($product['BARCODE']) && !empty($product['BARCODE']))
			{
				$elementFields['BARCODE'] = [
					$product['BARCODE'],
				];
				$documentBarcodesMap[] = [
					'SKU_ID' => (int)$product['SKU_ID'],
					'BARCODE' => $product['BARCODE'],
				];
			}

			if (!$this->isNew())
			{
				$elementFields['DOC_ID'] = $this->documentId;
			}

			$elementFields['STORE_TO'] = null;
			$elementFields['STORE_FROM'] = null;
			switch ($this->getDocumentType())
			{
				case StoreDocumentTable::TYPE_ARRIVAL:
				case StoreDocumentTable::TYPE_STORE_ADJUSTMENT:
					$elementFields['STORE_TO'] = $product['STORE_TO'];
					break;
				case StoreDocumentTable::TYPE_MOVING:
					$elementFields['STORE_FROM'] = $product['STORE_FROM'];
					$elementFields['STORE_TO'] = $product['STORE_TO'];
					break;
				case StoreDocumentTable::TYPE_DEDUCT:
					$elementFields['STORE_FROM'] = $product['STORE_FROM'];
					break;
			}

			$existElement = $existElements[$product['ID']] ?? null;
			if (
				$existElement
				&& $this->isChangedElement($existElement, $elementFields)
				&& $elementFields['STORE_TO'] !== null
				&& $elementFields['STORE_FROM'] !== null
			)
			{
				$can = $this->accessController->check(
					ActionDictionary::ACTION_STORE_VIEW,
					StoreDocumentElement::createFromArray($elementFields)
				);
				if (!$can)
				{
					$message = Loc::getMessage('CATALOG_STORE_DOCUMENT_ELEMENT_STORE_ACCESS_DENIED', [
						'#PRODUCT_NAME#' => $product['NAME'] ?? $product['ELEMENT_ID'],
					]);
					$result->addError(
						new Main\Error($message)
					);
					continue;
				}
			}

			$elements[] = $elementFields;
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		$barcodeFilter = array_unique(array_column($documentBarcodesMap, 'BARCODE'));
		if ($barcodeFilter)
		{
			$productBarcodes = [];
			$barcodeProductRaw = \Bitrix\Catalog\StoreBarcodeTable::getList([
				'filter' => ['=BARCODE' => $barcodeFilter],
				'select' => ['PRODUCT_ID', 'BARCODE'],
			]);

			while ($productBarcode = $barcodeProductRaw->fetch())
			{
				$productBarcodes[$productBarcode['BARCODE']] = (int)$productBarcode['PRODUCT_ID'];
			}

			$existedBarcodes = [];
			foreach ($documentBarcodesMap as $documentBarcode)
			{
				$barcode = $documentBarcode['BARCODE'];
				if (
					!empty($barcode)
					&& isset($productBarcodes[$barcode])
					&& $productBarcodes[$barcode] > 0
					&& $documentBarcode['SKU_ID'] !== $productBarcodes[$barcode]
				)
				{
					$existedBarcodes[] = $barcode;
				}
			}

			$existedBarcodes = array_unique($existedBarcodes);
			foreach ($existedBarcodes as $existedBarcode)
			{
				$result->addError(
					new Main\Error(
						Loc::getMessage(
							'CATALOG_STORE_DOCUMENT_BARCODE_EXIST_ERROR',
							['#BARCODE#' => htmlspecialcharsbx($existedBarcode)]
						)
					)
				);
			}
		}

		$result->setData(['ELEMENTS' => $elements]);

		return $result;
	}

	private function calculateDocumentTotalFromElement(array $element): float
	{
		$result = 0.0;

		$priceType = 'PURCHASING_PRICE';
		foreach ($element as $product)
		{
			$result += PriceMaths::roundPrecision((float)$product[$priceType] * (float)$product['AMOUNT']);
		}

		return PriceMaths::roundPrecision($result);
	}

	private function getUrlToDocumentDetail($documentId, $addCloseOnSaveParam = false): string
	{
		$pathToDocumentDetailTemplate = $this->arParams['PATH_TO']['DOCUMENT'] ?? '';
		if ($pathToDocumentDetailTemplate === '')
		{
			return $pathToDocumentDetailTemplate;
		}

		$pathToDocumentDetail = str_replace('#DOCUMENT_ID#', $documentId, $pathToDocumentDetailTemplate);

		if ($addCloseOnSaveParam)
		{
			$pathToDocumentDetail .= '?closeOnSave=Y';
		}

		return
			InventoryManagementSourceBuilder::getInstance()->addInventoryManagementSourceParam($pathToDocumentDetail)
			;
	}

	private function clearElementsForDocument()
	{
		$elements = \Bitrix\Catalog\StoreDocumentElementTable::getList([
			'select' => ['ID'],
			'filter' => ['DOC_ID' => $this->documentId]
		])->fetchAll();
		foreach ($elements as $element)
		{
			CCatalogStoreDocsElement::delete($element["ID"]);
			$barcodesDb = StoreDocumentBarcodeTable::getList(['select' => ['ID'], 'filter' => ['DOC_ELEMENT_ID' => $element['ID']]]);
			while ($barcode = $barcodesDb->fetch())
			{
				CCatalogStoreDocsBarcode::delete($barcode['ID']);
			}
		}
	}

	private function updateElements($elementsToUpdate)
	{
		$this->clearElementsForDocument();
		foreach ($elementsToUpdate as $element)
		{
			$docElementId = CCatalogStoreDocsElement::add($element);

			if (!empty($element['BARCODE']))
			{
				$this->updateBarcodesForDocsElement($docElementId, $element['BARCODE']);
			}
		}
	}

	private function updateBarcodesForDocsElement($docElementId, $barcodes)
	{
		foreach($barcodes as $barcode)
		{
			CCatalogStoreDocsBarcode::add(['BARCODE' => $barcode, 'DOC_ELEMENT_ID' => $docElementId]);
		}
	}

	private function collectRightColumnContent()
	{
		$eventParameters = [
			'DOCUMENT_ID' => $this->documentId,
			'DOCUMENT_TYPE' => $this->getDocumentType(),
			'DOCUMENT_FIELDS' => $this->document,
		];

		$event = new Main\Event(
			'catalog',
			self::COLLECT_RIGHT_COLUMN_EVENT,
			$eventParameters
		);
		$event->send();

		if ($event->getResults())
		{
			$this->arResult['RIGHT_COLUMN'] = [];
			foreach ($event->getResults() as $eventResult)
			{
				$componentInfo = $eventResult->getParameters();
				$this->arResult['RIGHT_COLUMN']['COMPONENT_NAME'] = $componentInfo['COMPONENT_NAME'];
				$this->arResult['RIGHT_COLUMN']['COMPONENT_TEMPLATE'] = $componentInfo['COMPONENT_TEMPLATE'];
				$this->arResult['RIGHT_COLUMN']['COMPONENT_PARAMS'] = $componentInfo['COMPONENT_PARAMS'];

				break;
			}
		}
	}

	private function getEntityDataForResponse(): array
	{
		$this->loadDocument();
		$entityData = $this->getEditorProvider()->getEntityData();

		foreach ($entityData as $key => $field)
		{
			if (is_null($field))
			{
				unset($entityData[$key]);
				continue;
			}

			if ($field instanceof Main\Type\Date)
			{
				$entityData[$key] = $field->toString();
			}
		}

		return $entityData;
	}

	private function checkIfInventoryManagementIsUsed()
	{
		$this->arResult['IS_CONDUCT_LOCKED'] = !\Bitrix\Catalog\Component\UseStore::isUsed();
		if ($this->arResult['IS_CONDUCT_LOCKED'])
		{
			$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
			$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');
			$this->arResult['MASTER_SLIDER_URL'] = $sliderPath;
		}
		else
		{
			$this->arResult['MASTER_SLIDER_URL'] = null;
		}
	}

	public function sendAnalyticsAction()
	{
		return null;
	}

	private function getAdditionalEntityEditorActions()
	{
		$additionalActions = [];
		$customToolPanelButtons = [];

		if ($this->checkDocumentConductRights())
		{
			if ($this->checkDocumentWriteRights())
			{
				$additionalActions[] = [
					'ID' => 'SAVE_AND_CONDUCT',
					'ACTION' => 'saveAndConduct',
					'ACTION_TYPE' => UI\EntityEditor\Action::ACTION_TYPE_SAVE,
				];

				$customToolPanelButtons[] = [
					'ID' => 'SAVE_AND_CONDUCT',
					'TEXT' => Loc::getMessage('CATALOG_STORE_DOCUMENT_SAVE_AND_CONDUCT_BUTTON'),
					'ACTION_ID' => 'SAVE_AND_CONDUCT',
					'CLASS' => 'ui-btn-light-border',
				];
			}

			$additionalActions[] = [
				'ID' => 'CONDUCT',
				'ACTION' => 'conduct',
				'ACTION_TYPE' => UI\EntityEditor\Action::ACTION_TYPE_DIRECT,
			];

			$customToolPanelButtons[] = [
				'ID' => 'CONDUCT',
				'TEXT' => Loc::getMessage('CATALOG_STORE_DOCUMENT_CONDUCT_BUTTON'),
				'ACTION_ID' => 'CONDUCT',
				'CLASS' => 'ui-btn-light-border',
			];
		}

		if ($this->checkDocumentCancelRights())
		{
			$additionalActions[] = [
				'ID' => 'CANCEL_CONDUCT',
				'ACTION' => 'cancelConduct',
				'ACTION_TYPE' => UI\EntityEditor\Action::ACTION_TYPE_DIRECT,
			];

			$customToolPanelButtons[] = [
				'ID' => 'CANCEL_CONDUCT',
				'TEXT' => Loc::getMessage('CATALOG_STORE_DOCUMENT_CANCEL_CONDUCT_BUTTON'),
				'ACTION_ID' => 'CANCEL_CONDUCT',
				'CLASS' => 'ui-btn-light-border',
			];
		}

		$this->arResult['FORM']['ENABLE_TOOL_PANEL'] = !empty($customToolPanelButtons) || $this->checkDocumentWriteRights();
		$this->arResult['FORM']['ADDITIONAL_ACTIONS'] = $additionalActions;
		$this->arResult['FORM']['CUSTOM_TOOL_PANEL_BUTTONS'] = $customToolPanelButtons;

		$viewButtons = $this->document && $this->document['STATUS'] === 'Y' ? ['CANCEL_CONDUCT'] : ['CONDUCT'];

		$this->arResult['FORM']['TOOL_PANEL_BUTTONS_ORDER'] = [
			'VIEW' => $viewButtons,
			'EDIT' => [
				UI\EntityEditor\Action::DEFAULT_ACTION_BUTTON_ID, 'SAVE_AND_CONDUCT', 'CANCEL',
			],
		];
	}
}
