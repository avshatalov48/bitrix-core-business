<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Lists\Api\Request\ServiceFactory\AddElementRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetElementUrlRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockFieldsRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockInfoRequest;
use Bitrix\Lists\Api\Response\ServiceFactory\AddElementResponse;
use Bitrix\Lists\Api\Service\ServiceFactory\AccessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ProcessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Lists\UI\Fields\Field;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;

class ListsElementCreationGuideAjaxController extends \Bitrix\Main\Engine\Controller
{
	private const TOKEN_SALT = 'lists_elementCreationGuide';
	protected const WHITE_LIST_FILL_CONSTANTS_URL = [
		'/bizproc/userprocesses/',
	];

	protected function getDefaultPreFilters(): array
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\Csrf(),
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Scope(ActionFilter\Scope::NOT_REST),
		];
	}

	public function configureActions(): array
	{
		return [
			'getListAdmin' => [
				'+prefilters' => [
					new ActionFilter\ContentType([ActionFilter\ContentType::JSON]),
				],
			],
			'notifyAdmin' => [
				'+prefilters' => [
					new ActionFilter\ContentType([ActionFilter\ContentType::JSON]),
				],
			],
		];
	}

	public function getListAdminAction(string $signedParameters): ?array
	{
		$unsignedParameters = $this->unSignParameters($signedParameters);
		if ($unsignedParameters === null || !$this->includeModules())
		{
			return null;
		}

		$accessService = $this->getAccessService($unsignedParameters);
		if (!$accessService)
		{
			return null;
		}

		$iBlockId = $unsignedParameters['IBLOCK_ID'];
		$canUserAddElement = $accessService->canUserAddElement(0, $iBlockId);

		if (!$canUserAddElement->isSuccess())
		{
			$this->addErrors($canUserAddElement->getErrors());

			return null;
		}

		$iBlockRights = new CIBlockRights($iBlockId);
		$iBlockFullRightId = array_search('iblock_full', $iBlockRights::GetRightsList(false), true);
		if ($iBlockFullRightId === false)
		{
			return null;
		}

		$iBlockFullRightId = (int)$iBlockFullRightId;

		$ids = [];
		foreach ($iBlockRights->GetRights() as $right)
		{
			if ((int)$right['TASK_ID'] === $iBlockFullRightId && str_starts_with($right['GROUP_CODE'], 'U'))
			{
				$ids[] = mb_substr($right['GROUP_CODE'], 1);
			}
		}

		$usersFromGroup1 = CUser::GetList('ID', 'asc', ['GROUPS_ID' => 1, 'ACTIVE' => 'Y'], ['FIELDS' => 'ID']);
		while ($user = $usersFromGroup1->Fetch())
		{
			$ids[] = $user['ID'] ?? 0;
		}

		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($ids);

		$admins = [];
		if ($ids)
		{
			$nameFormat = CSite::GetNameFormat(false);
			$userFields = ['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PERSONAL_PHOTO'];
			$users = CUser::GetList('id', 'asc', ['ID' => implode('|', $ids)], ['FIELDS' => $userFields]);
			while ($user = $users->Fetch())
			{
				$file = null;
				if (is_numeric($user['PERSONAL_PHOTO']))
				{
					$file = CFile::ResizeImageGet(
						(int)$user['PERSONAL_PHOTO'],
						['width' => 58, 'height' => 58],
						BX_RESIZE_IMAGE_EXACT,
						false
					);
				}

				$admins[] = [
					'id' => (int)($user['ID'] ?? 0),
					'name' => \CUser::FormatName($nameFormat, $user, false, false),
					'img' => $file ? $file['src'] : null,
				];
			}
		}

		return [
			'admins' => $admins,
			'ids' => $ids,
			'canNotify' => !empty($unsignedParameters['FILL_CONSTANTS_URL']),
		];
	}

	public function notifyAdminAction(string $signedParameters, int $adminId): ?array
	{
		if (!Loader::includeModule('im'))
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_IM_NOT_INSTALLED_1')));

			return null;
		}

		$result = $this->getListAdminAction($signedParameters);
		if ($result === null)
		{
			return null;
		}

		if (!isset($result['ids']) || !in_array($adminId, $result['ids'], true))
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_INCORRECT_ADMIN_ID')));

			return null;
		}

		$unsignedParameters = $this->unSignParameters($signedParameters);

		if (empty($unsignedParameters['FILL_CONSTANTS_URL']))
		{
			return null;
		}

		$service = $this->getService($unsignedParameters);
		if (!$service)
		{
			return null;
		}

		$iBlockInfoResponse = $service->getIBlockInfo(new GetIBlockInfoRequest($unsignedParameters['IBLOCK_ID'], false));
		if (!$iBlockInfoResponse->isSuccess())
		{
			$this->addErrors($iBlockInfoResponse->getErrors());

			return null;
		}

		$iBlockInfo = $iBlockInfoResponse->getIBlock();

		$messageId = CIMNotify::Add([
			'TO_USER_ID' => $adminId,
			'FROM_USER_ID' => $this->getCurrentUserId(),
			'NOTIFY_TYPE' => IM_NOTIFY_FROM,
			'NOTIFY_MODULE' => 'lists',
			'NOTIFY_TAG' => 'LISTS|NOTIFY_ADMIN|' . $adminId . '|' . $this->getCurrentUserId(),
			'NOTIFY_MESSAGE' => Loc::getMessage(
				'LISTS_ELEMENT_CREATION_GUIDE_AJAX_NOTIFY_MESSAGE',
				[
					'#URL#' => (new \Bitrix\Main\Web\Uri($unsignedParameters['FILL_CONSTANTS_URL']))->getUri(),
					'#NAME#' => $iBlockInfo['NAME'],
				],
			),
		]);

		return ['success' => (bool)$messageId];
	}

	public function setConstantsAction(string $signedParameters, array $templateIds): ?array
	{
		if (!Loader::includeModule('bizproc'))
		{
			return ['success' => true];
		}

		$unsignedParameters = $this->unSignParameters($signedParameters);
		if ($unsignedParameters === null || !$this->includeModules())
		{
			return null;
		}

		if (!$templateIds)
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_EMPTY_REQUIRED_TEMPLATE_IDS')));

			return null;
		}

		$accessService = $this->getAccessService($unsignedParameters);
		if (!$accessService)
		{
			return null;
		}

		$iBlockId = $unsignedParameters['IBLOCK_ID'];
		if (!$accessService->canUserEditIBlock($iBlockId)->isSuccess())
		{
			$this->addError($accessService::getAccessDeniedError());

			return null;
		}

		$service = $this->getService($unsignedParameters);
		if (!$service)
		{
			return null;
		}

		$iBlockInfo = (
			$service
				->getIBlockInfo(new GetIBlockInfoRequest($iBlockId, false))
				->getIBlock()
		);

		$workflowService = new \Bitrix\Lists\Api\Service\WorkflowService($iBlockInfo);

		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($templateIds);
		$constants = [];
		foreach ($workflowService->getDocumentTypeStates() as $state)
		{
			$templateId = (int)$state['TEMPLATE_ID'];
			if (!in_array($templateId, $templateIds, true))
			{
				continue;
			}

			$constants[$templateId] = $this->getWFParametersFromRequest($templateId, $state['TEMPLATE_CONSTANTS']);
		}

		$response = $workflowService->setConstants($constants);
		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		return ['success' => true];
	}

	public function createAction(string $signedParameters, int $time): ?array
	{
		$unsignedParameters = $this->unSignParameters($signedParameters);
		if ($unsignedParameters === null || !$this->includeModules())
		{
			return null;
		}

		$service = $this->getService($unsignedParameters);
		if (!$service)
		{
			return null;
		}

		$iBlockId = $unsignedParameters['IBLOCK_ID'];
		$readOnlyFields = $unsignedParameters['READ_ONLY_SEQUENCE_VALUES'];
		$readOnlyFields['IBLOCK_ID'] = $iBlockId;

		$preparedFields = $this->prepareFields($readOnlyFields, $service);
		if ($preparedFields === null)
		{
			return null;
		}

		$sectionId = $this->getRequest()->getPost('IBLOCK_SECTION_ID');

		$addElementRequest = new AddElementRequest(
			$iBlockId,
			is_numeric($sectionId) ? $sectionId : 0,
			$preparedFields,
			$this->getCurrentUserId() ?? 0,
			true,
			true,
			$this->getWFParameters($service, $iBlockId),
			max($time, 0),
		);

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$response = $service->addElement($addElementRequest);
		}
		catch (SqlQueryException)
		{
			$response = new AddElementResponse();
			$response->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_ADD_INTERNAL_ERROR')));
		}

		if (!$response->isSuccess())
		{
			$conn->rollbackTransaction();
			$this->addErrors($response->getErrors());

			return null;
		}
		else
		{
			$conn->commitTransaction();
		}

		$liveFeedUrl = Option::get('lists', 'livefeed_url');
		if ($liveFeedUrl)
		{
			$liveFeedUrl =
				(new \Bitrix\Main\Web\Uri($liveFeedUrl))
					->addParams([
						'livefeed' => 'y',
						'list_id' => $iBlockId,
						'element_id' => (int)$response->getId()
					])
					->getUri()
			;
		}

		return [
			'success' => true,
			'elementUrl' => $liveFeedUrl,
		];
	}

	private function includeModules(): bool
	{
		if (Loader::includeModule('lists'))
		{
			return true;
		}

		$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_LISTS_NOT_INSTALLED_1')));

		return false;
	}

	private function unSignParameters(string $sign): ?array
	{
		$signer = new Signer();
		try
		{
			$unsigned = $signer->unsign($sign, self::TOKEN_SALT);
		}
		catch (BadSignatureException $e)
		{
			$this->addError(new \Bitrix\Main\Error($e->getMessage()));

			return null;
		}

		$exploded = explode('|', $unsigned);
		if (!$exploded)
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_BAD_SIGNED_PARAMETERS_1')));

			return null;
		}

		$params = $exploded[0] ?? '';
		$readOnlySequenceValues = $exploded[2] ?? null;

		if (!$params || !is_string($params))
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_BAD_SIGNED_PARAMETERS_1')));

			return null;
		}

		try
		{
			$params = Json::decode($params);
		}
		catch (ArgumentException)
		{}

		$iBlockTypeId = $params['iBlockTypeId'] ?? null;
		$iBlockId = $params['iBlockId'] ?? null;

		if ($iBlockTypeId && $iBlockId && is_numeric($iBlockId))
		{
			if ($readOnlySequenceValues && is_string($readOnlySequenceValues))
			{
				try
				{
					$readOnlySequenceValues = Json::decode($readOnlySequenceValues);
				}
				catch (ArgumentException $e)
				{}
			}

			$fillConstantsUrl = '';
			if (is_string($params['fillConstantsUrl'] ?? null))
			{
				foreach (self::WHITE_LIST_FILL_CONSTANTS_URL as $url)
				{
					if (str_starts_with($params['fillConstantsUrl'], $url))
					{
						$fillConstantsUrl = $params['fillConstantsUrl'];

						break;
					}
				}
			}

			return [
				'IBLOCK_TYPE_ID' => $iBlockTypeId,
				'IBLOCK_ID' => (int)$iBlockId,
				'FILL_CONSTANTS_URL' => $fillConstantsUrl,
				'SOCNET_GROUP_ID' => 0,
				'READ_ONLY_SEQUENCE_VALUES' => $readOnlySequenceValues ?? [],
			];
		}

		$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_BAD_SIGNED_PARAMETERS_1')));

		return null;
	}

	private function getCurrentUserId(): ?int
	{
		return CurrentUser::get()?->getId();
	}

	private function prepareFields(array $readOnlyValues, ServiceFactory $service): ?array
	{
		$response = $service->getIBlockFields(new GetIBlockFieldsRequest(
			$readOnlyValues['IBLOCK_ID'],
			true,
			false,
			false
		));
		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		$request = $this->getRequest();
		$preparedFields = [];
		foreach ($response->getAll() as $id => $property)
		{
			$field = new Field($property);
			if (
				!$field->isShowInAddForm()
				|| (
					($field->getType() !== 'N:Sequence' && $field->getId() !== 'ACTIVE_FROM')
					&& $field->isAddReadOnlyField()
				)
			)
			{
				continue;
			}

			$value = array_key_exists($id, $readOnlyValues) ? $readOnlyValues[$id] : $request->getPost($id);

			if (\CListFieldTypeList::IsField($id))
			{
				$preparedFields[$id] = (
					in_array($id, ['PREVIEW_PICTURE', 'DETAIL_PICTURE'], true)
						? $request->getFile($id)
						: $value
				);

				continue;
			}

			if ($field->getPropertyType() === 'F')
			{
				$value = [];
				if (!empty($request->getFile($id)))
				{
					CFile::ConvertFilesToPost($request->getFile($id), $value);
				}
			}

			if (!is_array($value))
			{
				$value = (array)$value;
			}

			$preparedFields[$id] = [];
			foreach ($value as $key => $realValue)
			{
				$preparedFields[$id][$key] = (
					is_array($realValue) && isset($realValue['VALUE'])
						? $realValue['VALUE']
						: $realValue
				);
			}
		}

		return $preparedFields;
	}

	private function getWFParameters(ServiceFactory $service, int $iBlockId): array
	{
		if (!Loader::includeModule('bizproc'))
		{
			return [];
		}

		$wfParameters = [];

		$workflowService = new \Bitrix\Lists\Api\Service\WorkflowService(
			$service->getIBlockInfo(new GetIBlockInfoRequest($iBlockId, false))->getIBlock()
		);
		$states = $workflowService->getDocumentStates($workflowService->getComplexDocumentId(0));
		foreach ($states as $state)
		{
			$parameters = $state['TEMPLATE_PARAMETERS'] ?? [];
			if (!empty($parameters))
			{
				$templateId = (int)$state['TEMPLATE_ID'];
				$wfParameters[$templateId] = $this->getWFParametersFromRequest($templateId, $parameters);
			}
		}

		return $wfParameters;
	}

	private function getWFParametersFromRequest(int $templateId, array $parameters): array
	{
		$request = $this->getRequest();

		$values = [];
		foreach ($parameters as $id => $property)
		{
			$key = 'bizproc' . $templateId . '_' . $id;

			if ($property['Type'] === \Bitrix\Bizproc\FieldType::FILE)
			{
				$file = $request->getFile($key);
				$values[$id] = null;
				if (!empty($file) && isset($file['name']))
				{
					if (is_array($file['name']))
					{
						$values[$id] = [];
						CFile::ConvertFilesToPost($request->getFile($key), $values[$id]);
					}
					else
					{
						$values[$id] = $file;
					}
				}

				continue;
			}

			$values[$id] = $request->getPost($key);
		}

		return $values;
	}

	private function getService(array $unsignedParameters): ?ServiceFactory
	{
		$iBlockTypeId = $unsignedParameters['IBLOCK_TYPE_ID'];
		if ($iBlockTypeId !== ProcessService::getIBlockTypeId())
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_UNSUPPORTED_IBLOCK_TYPE_ID')));

			return null;
		}

		$service = ServiceFactory::getServiceByIBlockTypeId(
			$iBlockTypeId,
			$this->getCurrentUserId() ?? 0,
			$unsignedParameters['SOCNET_GROUP_ID']
		);
		if (!$service)
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_UNSUPPORTED_IBLOCK_TYPE_ID')));

			return null;
		}

		return $service;
	}

	private function getAccessService(array $unsignedParameters): ?AccessService
	{
		$iBlockTypeId = $unsignedParameters['IBLOCK_TYPE_ID'];
		if ($iBlockTypeId !== ProcessService::getIBlockTypeId())
		{
			$this->addError(new \Bitrix\Main\Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_AJAX_UNSUPPORTED_IBLOCK_TYPE_ID')));

			return null;
		}

		return new AccessService(
			$this->getCurrentUserId() ?? 0,
			new \Bitrix\Lists\Service\Param([
				'IBLOCK_TYPE_ID' => $iBlockTypeId,
				'IBLOCK_ID' => $unsignedParameters['IBLOCK_ID'],
				'SOCNET_GROUP_ID' => $unsignedParameters['SOCNET_GROUP_ID'],
			])
		);
	}
}
