<?php

namespace Bitrix\Lists\Api\Service\IBlockService;

use Bitrix\Iblock\SectionTable;
use Bitrix\Lists\Api\Data\IBlockService\IBlockElementsToGet;
use Bitrix\Lists\Api\Data\IBlockService\IBlockElementToAdd;
use Bitrix\Lists\Api\Data\IBlockService\IBlockElementToUpdate;
use Bitrix\Lists\Api\Data\IBlockService\IBlockToGet;
use Bitrix\Lists\Api\Data\IBlockService\IBlockToGetById;
use Bitrix\Lists\Api\Request\IBlockService\AddIBlockElementRequest;
use Bitrix\Lists\Api\Request\IBlockService\GetIBlockDefaultFieldsRequest;
use Bitrix\Lists\Api\Request\IBlockService\UpdateIBlockElementRequest;
use Bitrix\Lists\Api\Response\IBlockService\AddIBlockElementResponse;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockByIdResponse;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockDefaultFieldsResponse;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockElementFieldsResponse;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockElementListResponse;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockListResponse;
use Bitrix\Lists\Api\Response\IBlockService\UpdateIBlockElementResponse;
use Bitrix\Lists\Api\Response\Response;
use Bitrix\Lists\Service\Param;
use Bitrix\Lists\Workflow\Starter;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

final class IBlockService
{
	protected AccessService $accessService;
	protected IBlockDataService $dataService;

	private string $iBlockTypeId;
	private int $socNetGroupId;
	private bool $isBpFeatureEnabled;

	private static array $cache = [];

	public function __construct(
		Param $parameters,
		AccessService $accessService,
	)
	{
		$parameters->checkRequiredInputParams(['IBLOCK_TYPE_ID', 'SOCNET_GROUP_ID']);
		if ($parameters->hasErrors())
		{
			$firstError = $parameters->getErrors()[0];

			throw new ArgumentException($firstError->getMessage());
		}

		$this->accessService = $accessService;
		$this->dataService = new IBlockDataService();

		$this->iBlockTypeId = $parameters->getParams()['IBLOCK_TYPE_ID'];
		$this->socNetGroupId = $parameters->getParams()['SOCNET_GROUP_ID'];

		$this->isBpFeatureEnabled = (
			Loader::includeModule('bizproc')
			&& \CLists::isBpFeatureEnabled($this->iBlockTypeId) === true
		);
	}

	public function getIBlockById(IBlockToGetById $iBlockToGetById): GetIBlockByIdResponse
	{
		$response = new GetIBlockByIdResponse();

		if ($iBlockToGetById->needCheckPermissions())
		{
			$response->fillFromResponse($this->accessService->canUserReadIBlock($iBlockToGetById->getIBlockId()));
		}

		if ($response->isSuccess() && Loader::includeModule('iblock'))
		{
			$response->setIBlock(\CIBlock::GetArrayByID($iBlockToGetById->getIBlockId()) ?: []);
		}

		return $response;
	}

	public function getIBlockList(IBlockToGet $iBlockToGet): GetIBlockListResponse
	{
		$response = new GetIBlockListResponse();

		if ($iBlockToGet->needCheckPermissions())
		{
			$response->fillFromResponse($this->accessService->canUserReadIBlockList());
		}

		if ($response->isSuccess() && Loader::includeModule('iblock'))
		{
			$filter =
				$iBlockToGet->getFilter()
					->setIBLockTypeId($this->iBlockTypeId)
					->setSocNetGroupId($this->socNetGroupId)
			;
			if (!$filter->hasField('CHECK_PERMISSIONS'))
			{
				$filter->setCheckPermission(!$this->accessService->isAdminPermission($response->getPermission()));
			}

			$iBlocks = [];
			$iterator = \CIBlock::GetList($iBlockToGet->getOrder(), $filter->getOrmFilter());
			while ($item = $iterator->Fetch())
			{
				$iBlocks[] = $item;
			}

			$response->setIBlocks($iBlocks);
		}

		return $response;
	}

	public function getElementDetailInfo(IBlockElementsToGet $iBlockElementsToGet): GetIBlockElementListResponse
	{
		$response = new GetIBlockElementListResponse();
		$filter = $iBlockElementsToGet->getFilter();

		$elementId = $filter->getFieldValue('ID');
		$iBlockId = $filter->getFieldValue('IBLOCK_ID');
		if ($elementId === null || is_array($elementId) || $iBlockId === null)
		{
			// todo: loc
			return $response->addError(new Error('required parameters'));
		}

		if ($iBlockElementsToGet->isCheckPermissionsEnabled())
		{
			$sectionId = $filter->getFieldValue('SECTION_ID') ?? 0;
			$response->fillFromResponse(
				$this->accessService->canUserReadElement((int)$elementId,  (int)$sectionId, (int)$iBlockId)
			);
		}

		if ($response->isSuccess())
		{
			$iBlockElementsToGet->disableCheckPermissions();
			if (!$iBlockElementsToGet->getFilter()->hasField('CHECK_PERMISSIONS'))
			{
				$iBlockElementsToGet->getFilter()->setCheckPermission(false);
			}

			if ((int)$elementId === 0)
			{
				return $response->setElements([['ID' => 0]]);
			}

			return $this->getIBlockElementList($iBlockElementsToGet);
		}

		return $response;
	}

	public function getIBlockElementList(IBlockElementsToGet $iBlockElementsToGet): GetIBlockElementListResponse
	{
		$response = new GetIBlockElementListResponse();

		$filter = $iBlockElementsToGet->getFilter();
		$iBlockId = $filter->hasField('IBLOCK_ID') ? (int)$filter->getFieldValue('IBLOCK_ID') : null;
		if ($iBlockElementsToGet->isCheckPermissionsEnabled())
		{
			$response->fillFromResponse($this->accessService->canUserReadElementList($iBlockId));
		}

		$elements = [];
		if ($response->isSuccess() && Loader::includeModule('iblock'))
		{
			$filter->setIBlockType($this->iBlockTypeId);
			if (!$filter->hasField('CHECK_PERMISSIONS'))
			{
				$filter->setCheckPermission(!$this->accessService->isAdminPermission($response->getPermission()));
			}

			$iterator = \CIBlockElement::GetList(
				$iBlockElementsToGet->getOrder(),
				$filter->getOrmFilter(),
				false,
				$iBlockElementsToGet->getNavigation(),
				$iBlockElementsToGet->getSelect()
			);

			while ($element = $iterator->Fetch())
			{
				$elementId = (int)$element['ID'];

				$propertiesValues = [];
				if ($iBlockId && $iBlockElementsToGet->isNeedLoadProps())
				{
					$propertiesValues = $this->loadIBlockElementPropertiesValues($iBlockId, $elementId);
				}

				$workflowStateInfo = [];
				if ($this->isBpFeatureEnabled && $iBlockElementsToGet->isNeedLoadWorkflowState())
				{
					$workflowStateInfo = $this->loadWorkflowStateInfo($elementId);
				}

				$elements[] = array_merge($element, $propertiesValues, $workflowStateInfo);
			}
		}

		$response->setElements($elements);

		return $response;
	}

	private function loadIBlockElementPropertiesValues(int $iBlockId, int $elementId): array
	{
		if (!Loader::includeModule('iblock'))
		{
			return [];
		}

		$result = [];
		$order = ['sort' => 'asc', 'id' => 'asc', 'enum_sort' => 'asc', 'value_id' => 'asc'];
		$filter = ['ACTIVE' => 'Y', 'EMPTY' => 'N'];
		$iterator = \CIBlockElement::GetProperty($iBlockId, $elementId, $order, $filter);
		while ($property = $iterator->Fetch())
		{
			$id = 'PROPERTY_' . $property['ID'];
			if (!array_key_exists($id, $result))
			{
				$result[$id] = [];
			}

			$result[$id][$property['PROPERTY_VALUE_ID']] = $property['VALUE'];
		}

		return $result;
	}

	private function loadWorkflowStateInfo(int $elementId): array
	{
		$documentState =
			$elementId > 0
				? $this->getActualElementState(\BizprocDocument::getDocumentComplexId($this->iBlockTypeId, $elementId))
				: null
		;

		return [
			'WORKFLOW_STATE' => $documentState ? $documentState['STATE_TITLE'] : '',
			'STARTED_BY' => $documentState ? $documentState['STARTED_BY'] : '',
			'WORKFLOW_STATE_ID' => $documentState ? $documentState['ID'] : '',
		];
	}

	private function getActualElementState(array $documentId): ?array
	{
		if (Loader::includeModule('bizproc'))
		{
			$state = \CBPDocument::getActiveStates($documentId, 1);
			if ($state)
			{
				return array_shift($state);
			}

			$ids = \CBPStateService::getIdsByDocument($documentId, 1);
			if ($ids)
			{
				return \CBPStateService::getWorkflowState(array_shift($ids));
			}
		}

		return null;
	}

	// todo: VO
	public function getIBlockFields(int $iBlockId, bool $isEnableCheckPermissions, bool $loadEnumValues): GetIBlockElementFieldsResponse
	{
		$response = new GetIBlockElementFieldsResponse();

		if ($isEnableCheckPermissions)
		{
			$response->fillFromResponse($this->accessService->canUserReadIBlock($iBlockId));
			if (!$response->isSuccess())
			{
				return $response;
			}
		}

		$list = new \CList($iBlockId);

		$all = [];
		$fields = [];
		$props = [];
		foreach ($list->GetFields() as $fieldId => $property)
		{
			if ($loadEnumValues)
			{
				$property['ENUM_VALUES'] = [];

				if ($property['TYPE'] === 'L')
				{
					$property = $this->loadEnumValuesByTypeL($property);
				}
			}

			$all[$fieldId] = $property;
			if ($list->is_field($fieldId))
			{
				$fields[$fieldId] = $property;
			}
			else
			{
				$props[$fieldId] = $property;
			}
		}

		return $response->setFields($fields)->setProps($props)->setAll($all);
	}

	private function loadEnumValuesByTypeL(array $property): array
	{
		$queryObject = \CIBlockProperty::getPropertyEnum($property['ID']);
		while($enum = $queryObject->fetch())
		{
			if ($enum['DEF'] === 'Y')
			{
				if (is_array($property['DEFAULT_VALUE']))
				{
					$property['DEFAULT_VALUE'][] = $enum['ID'];
				}
				elseif (empty($property['DEFAULT_VALUE']))
				{
					$property['DEFAULT_VALUE'] = $enum['ID'];
				}
				else
				{
					$property['DEFAULT_VALUE'] = (array)$property['DEFAULT_VALUE'];
					$property['DEFAULT_VALUE'][] = $enum['ID'];
				}
			}
			$property['ENUM_VALUES'][] = $enum;
		}

		return $property;
	}

	public function getIBlockDefaultFields(GetIBlockDefaultFieldsRequest $request): GetIBlockDefaultFieldsResponse
	{
		$response = new GetIBlockDefaultFieldsResponse();
		$iBlockId = $request->iBlockId;
		if ($iBlockId <= 0)
		{
			return $response->addError(new Error(Loc::getMessage('LISTS_LIB_API_IBLOCK_SERVICE_ERROR_WRONG_IBLOCK')));
		}

		if ($request->needCheckPermissions)
		{
			$response->fillFromResponse($this->accessService->canUserReadIBlock($iBlockId));
		}

		if ($response->isSuccess())
		{
			$iBlockToGet = (new IBlockToGetById($iBlockId))->disableCheckPermissions();
			$iBlock = $this->getIBlockById($iBlockToGet)->getIBlock();

			if ($iBlock && Loader::includeModule('iblock'))
			{
				if (!isset(self::$cache[$iBlockId]))
				{
					self::$cache[$iBlockId] = [];
				}

				if (!isset(self::$cache[$iBlockId]['hasSections']))
				{
					$section = (
						SectionTable::query()
							->setSelect(['ID'])
							->where('IBLOCK_ID', $iBlockId)
							->setLimit(1)
							->exec()
							->fetchObject()
					);

					self::$cache[$iBlockId]['hasSections'] = (bool)$section;
				}

				if (
					self::$cache[$iBlockId]['hasSections']
					&& $request->loadEnumValues
					&& !isset(self::$cache[$iBlockId]['sections'])
				)
				{
					$sections = [];
					$iterator = \CIBlockSection::GetTreeList(['IBLOCK_ID' => $iBlockId]);
					while ($section = $iterator->GetNext())
					{
						$sectionId = (int)$section['ID'];
						$sections[$sectionId] = $section;
					}

					self::$cache[$iBlockId]['sections'] = $sections;
				}

				$fields = [
					'IBLOCK_SECTION_ID' => [
						'FIELD_ID' => 'IBLOCK_SECTION_ID',
						'NAME' => $iBlock['SECTION_NAME'] ?? '',
						'IS_REQUIRED' => 'N',
						'MULTIPLE' => 'N',
						'DEFAULT_VALUE' => 0,
						'TYPE' => 'G',
						'SETTINGS' => [
							'SHOW_ADD_FORM' => 'Y',
							'SHOW_EDIT_FORM' => 'Y',
							'ADD_READ_ONLY_FIELD' => 'N',
							'EDIT_READ_ONLY_FIELD' => 'N',
						],
						'LINK_IBLOCK_ID' => $iBlockId,
						'HAS_SECTIONS' => self::$cache[$iBlockId]['hasSections'] ? 'Y' : 'N',
						'ENUM_VALUES' => (
							$request->loadEnumValues && isset(self::$cache[$iBlockId]['sections'])
								? self::$cache[$iBlockId]['sections']
								: []
						),
					],
				];

				$response->setDefaultFields($fields);
			}
		}

		return $response;
	}

	public function addIBlockElement(AddIBlockElementRequest $request): AddIBlockElementResponse
	{
		$response = new AddIBlockElementResponse();

		$elementToAdd = $this->dataService->getIBlockElementToAddObject($request, $response);
		if (!$elementToAdd)
		{
			return $response;
		}

		$iBlockId = $elementToAdd->getIBlockId();

		if ($request->needCheckPermissions && $response->isSuccess())
		{
			$response->fillFromResponse(
				$this->accessService->canUserAddElement($elementToAdd->getSectionId(), $iBlockId)
			);
		}

		if ($response->isSuccess() && Loader::includeModule('iblock'))
		{
			$element = $this->prepareElementValuesToUpsert($elementToAdd, $response);
			$wfStarter =
				$response->isSuccess() && $request->needStartWorkflows
					? $this->getWfStarter($response, $elementToAdd, $request->wfParameterValues)
					: null
			;

			if ($response->isSuccess())
			{
				$iBlockElement = new \CIBlockElement();
				$id = $iBlockElement->Add($element, false, true, true);
				$elementId = (int)(is_scalar($id) ? $id : 0);

				$response->setId($elementId);
				if ($elementId <= 0)
				{
					$response->addErrors($this->separateUpsertErrors($iBlockElement->LAST_ERROR));
				}

				if ($wfStarter && $elementId > 0)
				{
					$wfStarter->setTimeToStart($request->timeToStart)->setElementId($elementId);
					$startWorkflowResult = $wfStarter->run(true);
					$response->addErrors($startWorkflowResult->getErrors());
				}
			}
		}

		return $response;
	}

	private function getWfStarter(
		Response $response,
		IBlockElementToAdd|IBlockElementToUpdate $elementToUpsert,
		array $parameters,
	): ?Starter
	{
		$iBlockId = $elementToUpsert->getIBlockId();
		$elementId = $elementToUpsert->getElementId();
		$modifiedBy = $elementToUpsert->getModifiedBy();

		$iBlockInfo = $this->getIBlockById((new IBlockToGetById($iBlockId))->disableCheckPermissions())->getIBlock();

		$wfStarter = new Starter($iBlockInfo, $this->accessService->getUserId());
		$wfStarter->setElementId($elementId);

		if (!$wfStarter->isEnabled())
		{
			return null;
		}

		$isRunnableWfResult = $wfStarter->isRunnable($modifiedBy);
		$response->addErrors($isRunnableWfResult->getErrors());

		if ($isRunnableWfResult->isSuccess())
		{
			$setParametersResult = $wfStarter->setParameters($parameters);
			$response->addErrors($setParametersResult->getErrors());
		}

		return $wfStarter;
	}

	public function updateIBlockElement(UpdateIBlockElementRequest $request): UpdateIBlockElementResponse
	{
		$response = new UpdateIBlockElementResponse();

		$elementToUpdate = $this->dataService->getIBlockElementToUpdateObject($request, $response);
		if (!$elementToUpdate)
		{
			return $response;
		}

		if ($request->needCheckPermissions && $response->isSuccess())
		{
			$response->fillFromResponse(
				$this->accessService->canUserEditElement(
					$elementToUpdate->getElementId(),
					$elementToUpdate->getSectionId(),
					$elementToUpdate->getIBlockId()
				)
			);
		}

		if ($response->isSuccess())
		{
			$element = $this->prepareElementValuesToUpsert($elementToUpdate, $response);
			$wfStarter =
				$response->isSuccess() && $request->needStartWorkflows
					? $this->getWfStarter($response, $elementToUpdate, $request->wfParameterValues)
					: null
			;

			if ($response->isSuccess())
			{
				if ($element)
				{
					$iBlockElement = new \CIBlockElement();
					$result = $iBlockElement->Update($elementToUpdate->getElementId(), $element, false, true, true);

					if ($result)
					{
						$response->setIsSuccessUpdate(true);
					}
					else
					{
						$response
							->addErrors($this->separateUpsertErrors($iBlockElement->LAST_ERROR))
							->setIsSuccessUpdate(false)
						;
					}
				}

				if ($wfStarter && (empty($element) || $response->getIsSuccessUpdate()))
				{
					$wfStarter->setTimeToStart($request->timeToStart);

					if ($wfStarter->hasTemplatesOnStartup())
					{
						$wfStarter->setChangedFields(
							empty($element) ? [] : $elementToUpdate->getChangedFieldsAfterUpdate()
						);
					}

					$startWorkflowResult = $wfStarter->run();
					$response->addErrors($startWorkflowResult->getErrors());
				}
			}
		}

		return $response;
	}

	private function prepareElementValuesToUpsert(
		IBlockElementToAdd|IBlockElementToUpdate $elementToUpsert,
		Response $response
	): array
	{
		$iBlockFields = $this->getIBlockFields($elementToUpsert->getIBlockId(), false, true);
		$response->addErrors($iBlockFields->getErrors());

		$element = [];
		if ($response->isSuccess())
		{
			$result = $elementToUpsert->getElementValues($iBlockFields->getFields(), $iBlockFields->getProps());
			$element = $result->getElementData() ?? [];
			$response->addErrors($result->getErrors());

			if (!$result->getHasChangedFields() && !$result->getHasChangedProps())
			{
				return [];
			}
		}

		return $element;
	}

	private function separateUpsertErrors(string $errorMessages): array
	{
		$errors = [];

		$messages = explode('<br>', $errorMessages);
		foreach ($messages as $message)
		{
			$message = trim($message);
			if ($message)
			{
				$errors[] = new Error($message);
			}
		}

		return $errors;
	}
}
