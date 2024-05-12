<?php

namespace Bitrix\Lists\Api\Service\ServiceFactory;

use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Lists\Api\Data\IBlockService\IBlockElementFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockElementsToGet;
use Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter;
use Bitrix\Lists\Api\Data\IBlockService\IBlockToGet;
use Bitrix\Lists\Api\Data\IBlockService\IBlockToGetById;
use Bitrix\Lists\Api\Request\IBlockService\AddIBlockElementRequest;
use Bitrix\Lists\Api\Request\IBlockService\GetIBlockDefaultFieldsRequest;
use Bitrix\Lists\Api\Request\IBlockService\UpdateIBlockElementRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\AddElementRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetElementDetailInfoRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockFieldsRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockInfoRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetListRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\UpdateElementRequest;
use Bitrix\Lists\Api\Response\IBlockService\GetIBlockElementFieldsResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\AddElementResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetAverageIBlockTemplateDurationResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetCatalogResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetElementDetailInfoResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetIBlockFieldsResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetIBlockInfoResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\GetListResponse;
use Bitrix\Lists\Api\Response\ServiceFactory\UpdateElementResponse;
use Bitrix\Lists\Api\Service\IBlockService\IBlockService;
use Bitrix\Lists\Service\Param;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;

abstract class ServiceFactory
{
	protected AccessService $accessService;
	protected IBlockService $iBlockService;
	protected DataService $dataService;

	public static function getServiceByIBlockTypeId(
		string $iBlockTypeId,
		int $currentUserId,
		int $socNetGroupId = 0,
	): ProcessService|ListService|SocNetListService|IBlockListService|null
	{
		if (empty($iBlockTypeId) || $currentUserId <= 0 || $socNetGroupId < 0)
		{
			return null;
		}

		$param = new Param([
			'IBLOCK_TYPE_ID' => $iBlockTypeId,
			'IBLOCK_ID' => false,
			'SOCNET_GROUP_ID' => $socNetGroupId,
		]);

		$accessService = new AccessService($currentUserId, $param);
		$iBlockService = new IBlockService(
			$param,
			new \Bitrix\Lists\Api\Service\IBlockService\AccessService($currentUserId, $param)
		);
		$dataService = new DataService();

		if ($iBlockTypeId === ListService::getIBlockTypeId())
		{
			return new ListService($accessService, $iBlockService, $dataService);
		}

		if ($iBlockTypeId === ProcessService::getIBlockTypeId())
		{
			return new ProcessService($accessService, $iBlockService, $dataService);
		}

		if ($socNetGroupId > 0 && $iBlockTypeId === SocNetListService::getIBlockTypeId())
		{
			return (
				(new SocNetListService($accessService, $iBlockService, $dataService))
					->setSocNetGroupId($socNetGroupId)
			);
		}

		$listsPermissions = \CLists::GetPermission();
		if (array_key_exists($iBlockTypeId, $listsPermissions))
		{
			return (
				(new IBlockListService($accessService, $iBlockService, $dataService))
					->setIBlockTypeId($iBlockTypeId)
			);
		}

		return null;
	}

	abstract public static function getIBlockTypeId(): string;

	private function __construct(
		AccessService $accessService,
		IBlockService $iBlockService,
		DataService $dataService
	)
	{
		$this->accessService = $accessService;
		$this->iBlockService = $iBlockService;
		$this->dataService = $dataService;
	}

	public function getInnerIBlockTypeId(): string
	{
		return static::getIBlockTypeId();
	}

	public function checkIBlockTypePermission(): \Bitrix\Lists\Api\Response\CheckPermissionsResponse
	{
		return $this->accessService->checkIBlockTypePermission();
	}

	public function getCatalog(): GetCatalogResponse
	{
		$response = new GetCatalogResponse();

		$checkPermissionResponse = $this->accessService->canUserReadCatalog();
		$response->fillFromResponse($checkPermissionResponse);

		if ($response->isSuccess())
		{
			$filter =
				(new IBlockListFilter())
					->setActive(true)
					->setIBLockTypeId($this->getInnerIBlockTypeId())
					->setCheckPermission(!$this->accessService->isAdminPermission($response->getPermission()))
			;
			$this->fillCatalogFilter($filter);

			$iBlockToGet =
				(new IBlockToGet($filter))
					->disableCheckPermissions()
			;

			$iBlockListResult = $this->iBlockService->getIBlockList($iBlockToGet);
			$response
				->addErrors($iBlockListResult->getErrors())
				->setCatalog($iBlockListResult->getIBlocks())
			;
		}

		return $response;
	}

	abstract protected function fillCatalogFilter(IBlockListFilter $filter): void;

	public function getElementList(GetListRequest $request): GetListResponse
	{
		$response = new GetListResponse();

		$checkPermissionResult = $this->accessService->canUserGetElementList();
		$response->fillFromResponse($checkPermissionResult);

		if ($response->isSuccess())
		{
			$filter =
				(IBlockElementFilter::initializeFromArray($request->filter->getOrmFilter()))
					->setIBlockType($this->getInnerIBlockTypeId())
					->setCheckPermission(!$this->accessService->isCanReadPermission($response->getPermission()))
			;
			$this->fillElementListFilter($filter);

			$iBlockElementsToGet =
				(new IBlockElementsToGet(
					$filter,
					$request->sort,
					$request->offset,
					$request->limit,
					$request->additionalSelectFields
				))
					->disableCheckPermissions()
					->setIsNeedLoadWorkflowStateInfo() // todo: to GetListOptions
			;

			$getIBlockElementListResult = $this->iBlockService->getIBlockElementList($iBlockElementsToGet);
			$response
				->addErrors($getIBlockElementListResult->getErrors())
				->setElements($getIBlockElementListResult->getElements())
			;
		}

		return $response;
	}

	abstract protected function fillElementListFilter(IBlockElementFilter $filter): void;

	public function getIBlockInfo(GetIBlockInfoRequest $request): GetIBlockInfoResponse
	{
		$response = new GetIBlockInfoResponse();

		if ($request->iBlockId <= 0)
		{
			return $response->addError($this->dataService::getWrongIBlockError());
		}

		if ($request->needCheckPermissions)
		{
			$checkPermissionResult = $this->accessService->canUserReadIBlock($request->iBlockId);
			$response->fillFromResponse($checkPermissionResult);
		}

		if ($response->isSuccess())
		{
			$iBlockToGetById =
				(new IBlockToGetById($request->iBlockId))
					->disableCheckPermissions()
			;

			$iBlockResponse = $this->iBlockService->getIBlockById($iBlockToGetById);
			$response->addErrors($iBlockResponse->getErrors());

			if ($iBlockResponse->isSuccess())
			{
				$response->setIBlock($iBlockResponse->getIBlock());
			}
		}

		return $response;
	}

	public function getElementDetailInfo(GetElementDetailInfoRequest $request): GetElementDetailInfoResponse
	{
		$response = new GetElementDetailInfoResponse();

		$elementToGetDetailInfo = $this->dataService->getElementToGetDetailInfoObject($request, $response);
		if ($elementToGetDetailInfo)
		{
			$elementId = $elementToGetDetailInfo->getElementId();
			$iBlockId = $elementToGetDetailInfo->getIBlockId();

			if ($elementToGetDetailInfo->isNeedCheckPermissions())
			{
				$sectionId = $elementToGetDetailInfo->getSectionId();
				$checkElementPermission = $this->accessService->canUserReadElement($elementId, $sectionId, $iBlockId);
				$response->fillFromResponse($checkElementPermission);
			}

			if ($response->isSuccess())
			{
				$filter =
					(new IBlockElementFilter())
						->setIBlockType($this->getInnerIBlockTypeId())
						->setIBlockId($elementToGetDetailInfo->getIBlockId())
						->setId($elementId)
						->setShowNew(true)
				;
				$this->fillElementDetailInfoFilter($filter);

				$iBlockElementsToGet =
					(new IBlockElementsToGet(
						filter: $filter,
						limit: 1,
						additionalSelectFields: $elementToGetDetailInfo->getAdditionalSelectFields()
					))
						->disableCheckPermissions()
						->setIsNeedLoadWorkflowStateInfo(false)
				;

				$elementListResponse = $this->iBlockService->getElementDetailInfo($iBlockElementsToGet);
				$response->addErrors($elementListResponse->getErrors());

				if ($response->isSuccess())
				{
					$response
						->setInfo($elementListResponse->hasElements() ? $elementListResponse->getElements()[0] : [])
					;
				}
			}
		}

		return $response;
	}

	abstract protected function fillElementDetailInfoFilter(IBlockElementFilter $filter): void;

	public function getIBlockFields(GetIBlockFieldsRequest $request): GetIBlockElementFieldsResponse
	{
		$response = new GetIBlockFieldsResponse();
		$iBlockId = $request->iBlockId;

		if ($iBlockId <= 0)
		{
			return $response->addError($this->dataService::getWrongIBlockError());
		}

		if ($request->needCheckPermissions)
		{
			$checkPermissionsResponse = $this->accessService->canUserReadIBlock($iBlockId);
			$response->fillFromResponse($checkPermissionsResponse);
		}

		if ($response->isSuccess())
		{
			$iBlockFieldsResponse = $this->iBlockService->getIBlockFields(
				$iBlockId,
				false,
				$request->loadEnumValues
			);
			$response->addErrors($iBlockFieldsResponse->getErrors());

			$fields = $iBlockFieldsResponse->getFields();
			if ($request->loadDefaultFields)
			{
				$iBlockDefaultFieldsRequest = new GetIBlockDefaultFieldsRequest($iBlockId, false);
				$defaultFieldsRequest = $this->iBlockService->getIBlockDefaultFields($iBlockDefaultFieldsRequest);
				$response->addErrors($defaultFieldsRequest->getErrors());

				$defaultFields = $defaultFieldsRequest->getDefaultFields();
				if ($defaultFields)
				{
					$fields = array_merge($fields, $defaultFields);
				}
			}

			$response->setFields($fields);
			$response->setProps($iBlockFieldsResponse->getProps());
		}

		return $response;
	}

	public function addElement(AddElementRequest $request): AddElementResponse
	{
		$response = new AddElementResponse();

		$elementToAdd = $this->dataService->getElementToAddObject($request, $response);
		if ($elementToAdd)
		{
			if ($request->needCheckPermission)
			{
				$checkPermissionsResponse = $this->accessService->canUserAddElement(
					$elementToAdd->getSectionId(),
					$elementToAdd->getIBlockId()
				);
				$response->fillFromResponse($checkPermissionsResponse);
			}

			if ($response->isSuccess())
			{
				$addRequest = new AddIBlockElementRequest(
					$elementToAdd->getIBlockId(),
					$elementToAdd->getSectionId(),
					$elementToAdd->getValues(),
					$elementToAdd->getCreatedBy(),
					$request->needStartWorkflows,
					false,
					$request->wfParameterValues,
					$request->timeToStart,
				);

				$addResponse = $this->iBlockService->addIBlockElement($addRequest);
				$response
					->addErrors($addResponse->getErrors())
					->setId((int)$addResponse->getId())
				;
			}
		}

		return $response;
	}

	public function updateElement(UpdateElementRequest $request): UpdateElementResponse
	{
		$response = new UpdateElementResponse();

		$elementToUpdate = $this->dataService->getElementToUpdateObject($request, $response);
		if ($elementToUpdate)
		{
			if ($request->needCheckPermission)
			{
				$checkPermissionsResponse = $this->accessService->canUserEditElement(
					$elementToUpdate->getElementId(),
					$elementToUpdate->getSectionId(),
					$elementToUpdate->getIBlockId()
				);
				$response->fillFromResponse($checkPermissionsResponse);
			}

			if ($response->isSuccess())
			{
				$updateRequest = new UpdateIBlockElementRequest(
					$elementToUpdate->getElementId(),
					$elementToUpdate->getIBlockId(),
					$elementToUpdate->getSectionId(),
					$elementToUpdate->getValues(),
					$elementToUpdate->getModifiedBy(),
					$request->needStartWorkflows,
					false,
					$request->wfParameterValues,
					$request->timeToStart,
				);
				$updateResponse = $this->iBlockService->updateIBlockElement($updateRequest);
				$response
					->addErrors($updateResponse->getErrors())
					->setIsSuccessElementUpdate($updateResponse->getIsSuccessUpdate())
				;
			}
		}

		return $response;
	}

	/**
	 * calculates the average execution time of the first iBlock template in seconds
	 */
	public function getAverageIBlockTemplateDuration(
		GetAverageIBlockTemplateDurationRequest $request
	): GetAverageIBlockTemplateDurationResponse
	{
		// todo: rights?
		$response = new GetAverageIBlockTemplateDurationResponse();

		if (!\CLists::isBpFeatureEnabled($this->getInnerIBlockTypeId()) || !Loader::includeModule('bizproc'))
		{
			// todo: localization
			return $response->addError(new Error('not supported'));
		}

		$timeToGet = $this->dataService->getAverageTemplateDurationToGetObject($request, $response);
		if (!$timeToGet)
		{
			return $response;
		}

		$templates = $this->getTemplatesByIBlockId($timeToGet->getIBlockId(), $timeToGet->getAutoExecuteType());
		if (!$templates)
		{
			// todo: localization
			return $response->addError(new Error('no templates'));
		}

		$workflowStateService = new WorkflowStateService();
		$averageTimeResult = $workflowStateService->getAverageWorkflowDuration(
			new GetAverageWorkflowDurationRequest($templates[0]['ID'])
		);

		$response->addErrors($averageTimeResult->getErrors());
		if ($averageTimeResult->isSuccess() && $averageTimeResult->getAverageDuration())
		{
			$response->setAverageDuration($averageTimeResult->getAverageDuration());
		}

		return $response;
	}

	private function getTemplatesByIBlockId(int $iBlockId, int $autoExecuteType): array
	{
		$documentType = \BizprocDocument::generateDocumentComplexType($this->getInnerIBlockTypeId(), $iBlockId);

		if (Loader::includeModule('bizproc'))
		{
			return \CBPWorkflowTemplateLoader::searchTemplatesByDocumentType($documentType, $autoExecuteType);
		}

		return [];
	}

}
