<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockFieldsRequest;
use Bitrix\Lists\Api\Request\ServiceFactory\GetIBlockInfoRequest;
use Bitrix\Lists\Api\Service\ServiceFactory\AccessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ProcessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Lists\Api\Service\WorkflowService;
use Bitrix\Lists\Service\Param;
use Bitrix\Lists\UI\Fields\Field;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class ListsElementCreationGuide extends CBitrixComponent implements Errorable
{
	use ErrorableImplementation;

	private const TOKEN_SALT = 'lists_elementCreationGuide';
	protected const WHITE_LIST_FILL_CONSTANTS_URL = [
		'/bizproc/userprocesses/',
	];

	private ServiceFactory $service;
	private AccessService $accessService;
	private WorkflowService $workflowService;

	private array $iBlock = [];
	/** @var \Bitrix\Lists\UI\Fields\Field[] $iBlockFields */
	private array $iBlockFields = [];
	private array $readOnlyCalculatedValues = [];

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
	}

	public function executeComponent()
	{
		if (
			!$this->includeModules()
			|| !$this->checkRequiredParameters()
			|| !$this->checkRights()
			|| !$this->loadIBlockInfo()
			|| !$this->loadFields()
		)
		{
			return $this->showErrorsTemplate();
		}

		$this->arResult = [
			'iBlockInfo' => [
				'name' => $this->iBlock['NAME'] ?? '',
				'description' => $this->getParsedDescription(),
			],
			'fields' => $this->iBlockFields,
			'elementData' => $this->getElementData(),
			'signedParameters' => $this->getSign(),

			'bizproc' => [
				'parameterDocumentType' => $this->getWorkflowService()->getComplexDocumentType(),
				'averageDuration' => $this->getAverageTemplateDuration(),
				'statesOnStartUp' => $this->getWorkflowStatesOnStartUp(),
				'canUserTuningStates' => $this->getAccessService()->canUserEditIBlock($this->getIBlockId())->isSuccess(),
				'statesToTuning' => $this->getWorkflowStatesToTuning(),
			],
		];

		return $this->includeComponentTemplate();
	}

	private function includeModules(): bool
	{
		if (!Loader::includeModule('lists'))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_LISTS_MODULE_NOT_INSTALLED_1'))
			);

			return false;
		}

		return true;
	}

	private function checkRequiredParameters(): bool
	{
		if (!is_string($this->getIBlockTypeId()) || $this->getIBlockTypeId() === '')
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_EMPTY_IBLOCK_TYPE_ID'))
			);

			return false;
		}

		if($this->getIBlockTypeId() !== ProcessService::getIBlockTypeId())
		{
			// now only for processes
			$this->errorCollection->setError(
				new Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_INCORRECT_IBLOCK_TYPE_ID'))
			);

			return false;
		}

		if (!is_numeric($this->arParams['IBLOCK_ID']) || ((int)$this->arParams['IBLOCK_ID'] <= 0))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_EMPTY_IBLOCK_ID'))
			);

			return false;
		}

		return true;
	}

	private function checkRights(): bool
	{
		$response = $this->getAccessService()->canUserAddElement(0, $this->getIBlockId());

		if (!$response->isSuccess())
		{
			$this->errorCollection->add($response->getErrors());

			return false;
		}

		return true;
	}

	private function loadIBlockInfo(): bool
	{
		$response = $this->getService()->getIBlockInfo(
			new GetIBlockInfoRequest((int)$this->arParams['IBLOCK_ID'], false)
		);

		if (!$response->isSuccess())
		{
			$this->errorCollection->add($response->getErrors());

			return false;
		}

		$iBlock = $response->getIBlock();
		if (empty($iBlock))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_IBLOCK_NOT_FOUND'))
			);

			return false;
		}

		$this->iBlock = $iBlock;

		return true;
	}

	private function loadFields(): bool
	{
		$response = $this->getService()->getIBlockFields(
			new GetIBlockFieldsRequest(
				$this->getIBlockId(),
				true,
				true,
				false,
			)
		);
		if (!$response->isSuccess())
		{
			$this->errorCollection->add($response->getErrors());

			return false;
		}

		$fields = [];
		foreach ($response->getAll() as $id => $property)
		{
			if ($id === 'IBLOCK_SECTION_ID')
			{
				// skip field is no sections
				if ($property['HAS_SECTIONS'] === 'N' || empty($property['ENUM_VALUES']))
				{
					continue;
				}
			}

			$field = new Field($this->applyHacks($id, $property));
			if ($field->isShowInAddForm())
			{
				$fields[$field->getId()] = $field;
			}
		}

		$this->iBlockFields = $fields;
		$this->hasFieldsToShow = !empty($fields);

		return true;
	}

	private function applyHacks(string $id, array $property): array
	{
		if ($id === 'IBLOCK_SECTION_ID')
		{
			$enumValues = [
				'' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_IBLOCK_SECTION_UPPER_LEVEL'),
			];
			foreach ($property['ENUM_VALUES'] as $sectionId => $section)
			{
				$enumValues[$sectionId] = str_repeat(' . ', $section['DEPTH_LEVEL']) . $section['~NAME'];
			}

			$property['ENUM_VALUES'] = $enumValues;
		}

		return $property;
	}

	private function getParsedDescription(): string
	{
		$textParser = new \CTextParser();
		$textParser->allow = [
			'HTML' => 'N',
			'USER' => 'N',
			'ANCHOR' => 'Y',
			'BIU' => 'Y',
			'IMG' => 'N',
			'QUOTE' => 'N',
			'CODE' => 'N',
			'FONT' => 'Y',
			'LIST' => 'Y',
			'SMILES' => 'N',
			'NL2BR' => 'Y',
			'VIDEO' => 'N',
			'TABLE' => 'N',
			'CUT_ANCHOR' => 'N',
			'ALIGN' => 'N',
		];

		return $textParser->convertText($this->iBlock['DESCRIPTION'] ?? '');
	}

	private function getElementData(): array
	{
		$data = [
			'NAME' => Loc::getMessage('LISTS_ELEMENT_CREATION_GUIDE_NAME_DEFAULT'),
			'IBLOCK_SECTION_ID' => '', // todo: use arParams
		];

		foreach ($this->iBlockFields as $field)
		{
			$data[$field->getId()] = $field->getDefaultValue();

			if ($field->isAddReadOnlyField() && ($field->getType() === 'N:Sequence' || $field->getId() === 'ACTIVE_FROM'))
			{
				$this->readOnlyCalculatedValues[$field->getId()] = $data[$field->getId()];
			}
		}

		return $data;
	}

	private function getSign(): string
	{
		$params = [
			'iBlockTypeId' => $this->getIBlockTypeId(),
			'iBlockId' => $this->getIBlockId(),
		];

		$fillConstantsUrl = $this->getFillConstantsUrl();
		if ($fillConstantsUrl)
		{
			$params['fillConstantsUrl'] = $fillConstantsUrl;
		}

		$value = \Bitrix\Main\Web\Json::encode($params);
		if ($this->readOnlyCalculatedValues)
		{
			$value .= '|' . \Bitrix\Main\Web\Json::encode($this->readOnlyCalculatedValues);
		}

		return (new \Bitrix\Main\Security\Sign\Signer())->sign($value, self::TOKEN_SALT);
	}

	private function getWorkflowStatesToTuning(): array
	{
		return array_map(
			static fn($state) => [
				'name' => $state['TEMPLATE_NAME'],
				'templateId' => (int)$state['TEMPLATE_ID'],
				'fields' => $state['TEMPLATE_CONSTANTS'],
			],
			$this->getWorkflowService()->getNotTunedDocumentTypeStates()
		);
	}

	private function getWorkflowStatesOnStartUp(): array
	{
		return array_map(
			static fn($state) => [
				'name' => $state['TEMPLATE_NAME'],
				'templateId' => (int)$state['TEMPLATE_ID'],
				'fields' => $state['TEMPLATE_PARAMETERS'],
			],
			$this->workflowService->getDocumentStatesWithParameters(0)
		);
	}

	private function getAverageTemplateDuration(): ?int
	{
		if (Loader::includeModule('bizproc'))
		{
			$response = $this->getService()->getAverageIBlockTemplateDuration(
				new GetAverageIBlockTemplateDurationRequest(
					$this->getIBlockId(),
					CBPDocumentEventType::Create,
					false,
					false,
				)
			);
			if ($response->isSuccess())
			{
				return $response->getAverageDuration();
			}
		}

		return null;
	}

	private function showErrorsTemplate()
	{
		$this->arResult= [
			'errors' => $this->getErrors(),
		];

		return $this->includeComponentTemplate('error');
	}

	private function getService(): ServiceFactory
	{
		if (!isset($this->service))
		{
			$this->service = ServiceFactory::getServiceByIBlockTypeId(
				$this->getIBlockTypeId(),
				$this->getCurrentUserId() ?? 0,
			);
		}

		return $this->service;
	}

	private function getAccessService(): AccessService
	{
		if (!isset($this->accessService))
		{
			$this->accessService = new AccessService(
				$this->getCurrentUserId() ?? 0,
				new Param([
					'IBLOCK_TYPE_ID' => $this->getIBlockTypeId(),
					'IBLOCK_ID' => $this->getIblockId(),
					'SOCNET_GROUP_ID' => 0,
				])
			);
		}

		return $this->accessService;
	}

	private function getWorkflowService(): WorkflowService
	{
		if (!isset($this->workflowService))
		{
			$this->workflowService = new WorkflowService($this->iBlock);
		}

		return $this->workflowService;
	}

	private function getIBlockTypeId()
	{
		return $this->arParams['IBLOCK_TYPE_ID'];
	}

	private function getIBlockId()
	{
		return $this->arParams['IBLOCK_ID'];
	}

	private function getFillConstantsUrl(): string
	{
		$fillConstantsUrl = $this->arParams['FILL_CONSTANTS_URL'];
		if (is_string($fillConstantsUrl))
		{
			foreach (self::WHITE_LIST_FILL_CONSTANTS_URL as $url)
			{
				if (str_starts_with($fillConstantsUrl, $url))
				{
					return $fillConstantsUrl;
				}
			}
		}

		return '';
	}

	private function getCurrentUserId(): ?int
	{
		return \Bitrix\Main\Engine\CurrentUser::get()?->getId();
	}
}
