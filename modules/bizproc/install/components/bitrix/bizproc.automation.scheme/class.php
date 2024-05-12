<?php

use Bitrix\Bizproc\Automation\Engine\TemplateScope;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::includeModule('bizproc'))
{
	ShowError(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_MODULE_NOT_INSTALLED_MSGVER_1'));
	return;
}

class BizprocAutomationSchemeComponent
	extends \Bitrix\Bizproc\Automation\Component\Base
	implements \Bitrix\Main\Engine\Contract\Controllerable
{
	/** @var string */
	private $action;
	/** @var array */
	private $documentType;
	/**@var \Bitrix\Bizproc\Automation\Engine\TemplatesScheme */
	private $scheme;

	public function configureActions(): array
	{
		return [];
	}

	private function init()
	{
		$this->action = $this->arParams['action'];
		$this->documentType = $this->arParams['documentType'];

		if (!$this->checkCurrentUserRights($this->documentType, $this->arParams['documentCategory']))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_RIGHTS_ERROR'))
			);
		}
		$this->arParams['robotNames'] = $this->arParams['robotNames'] ?? [];
		if (!is_array($this->arParams['triggerNames'] ?? null))
		{
			$this->arParams['triggerNames'] = [];
		}
		foreach ($this->arParams['triggerNames'] as $index => $name)
		{
			$this->arParams['triggerNames'][$index] = (int)$name;
		}

		$knownActions = ['copy', 'move'];
		if (!in_array($this->action, $knownActions, true))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_UNKNOWN_ACTION'))
			);
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();
		$target = $documentService->createAutomationTarget($this->documentType);

		if (is_null($target))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_UNKNOWN_DOCUMENT_MSGVER_1'))
			);
		}
		else
		{
			$this->scheme = $target->getTemplatesScheme();
			if (is_null($this->scheme))
			{
				$this->errorCollection->setError(
					new Error(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_SCHEME_ERROR_MSGVER_1'))
				);
			}
		}
	}

	public function executeComponent()
	{
		$this->init();

		if (!$this->getErrors())
		{
			if ($this->arParams['robotNames'] && !$this->arParams['triggerNames'])
			{
				$locShard = $this->action === 'copy' ? '_1' : '';
			}
			elseif ($this->arParams['triggerNames'] && !$this->arParams['robotNames'])
			{
				$locShard = '_TRIGGERS';
			}
			else
			{
				$locShard = '_ROBOTS_AND_TRIGGERS';
			}

			$this->arResult['locShard'] = $locShard;
			$this->arResult['action'] = mb_strtoupper($this->action);
			$this->arResult['templatesScheme'] = $this->signScheme();
			$this->includeComponentTemplate();
		}
		else
		{
			$this->includeComponentTemplate('error');
		}
	}

	private function signScheme(): array
	{
		$signedScheme = [];
		foreach ($this->scheme->toArray() as $scope)
		{
			$scope['DocumentType']['Type'] = CBPDocument::signDocumentType($scope['DocumentType']['Type']);
			$signedScheme[] = $scope;
		}

		return $signedScheme;
	}

	public function listKeysSignedParameters(): array
	{
		return ['documentType', 'documentCategory', 'templateStatus', 'action', 'robotNames', 'triggerNames'];
	}

	public function copyMoveAction(array $dstScope): ?array
	{
		$this->init();

		$result = new Result();
		if (!$this->getErrors())
		{
			$srcScope = new TemplateScope(
				$this->documentType,
				$this->arParams['documentCategory'],
				$this->arParams['templateStatus']
			);

			$dstScope['DocumentType']['Type'] = CBPDocument::unSignDocumentType($dstScope['DocumentType']['Type']);
			$dstScope = TemplateScope::fromArray($this->normalizeRawTemplateScope($dstScope));

			if (is_null($dstScope))
			{
				$result->addError(new Error('Cant create template scope'));
			}
			elseif (!$this->checkCurrentUserRights($dstScope->getComplexDocumentType(), $dstScope->getCategoryId()))
			{
				$result->addError(new Error(Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_RIGHTS_ERROR')));
			}
			else
			{
				$result = $this->launchTunnelAction($srcScope, $dstScope);
			}
		}
		else
		{
			$result->addErrors($this->getErrors());
		}

		if ($result->isSuccess())
		{
			return $result->getData();
		}
		else
		{
			$this->errorCollection->add($result->getErrors());
		}

		return null;
	}

	private function launchTunnelAction(TemplateScope $srcScope, TemplateScope $dstScope): Result
	{
		$creationResult = $this->scheme->createTemplatesTunnel($srcScope, $dstScope);
		$isAutomationAvailable =
			$this->scheme->isAutomationAvailable($srcScope->getComplexDocumentType())
			&& $this->scheme->isAutomationAvailable($dstScope->getComplexDocumentType())
		;
		if ($creationResult->isSuccess() && $isAutomationAvailable)
		{
			/** @var \Bitrix\Bizproc\Automation\Engine\TemplatesTunnel $tunnel */
			$tunnel = $creationResult->getData()['templatesTunnel'];
			$currentUserId = \Bitrix\Main\Engine\CurrentUser::get()->getId();

			$originalTemplates = [
				static::getTemplateViewData($dstScope->getTemplate()->toArray(), $dstScope->getComplexDocumentType()),
			];
			switch ($this->action)
			{
				case 'copy':
					$robotResult = $tunnel->copyRobots($this->arParams['robotNames'], $currentUserId);
					$triggerResult = $tunnel->copyTriggers($this->arParams['triggerNames']);
					break;

				case 'move':
					$originalTemplates[] = static::getTemplateViewData(
						$srcScope->getTemplate()->toArray(),
						$srcScope->getComplexDocumentType()
					);

					$robotResult = $tunnel->moveRobots($this->arParams['robotNames'], $currentUserId);
					$triggerResult = $tunnel->moveTriggers($this->arParams['triggerNames']);

					break;

				default:
					$robotResult = new Result();
					$triggerResult = new Result();
					break;
			}

			$robotsNames = [];
			$triggerNames = [];
			if ($robotResult->isSuccess())
			{
				foreach ($robotResult->getData() as $direction => $robots)
				{
					$robotsNames[$direction] = [];
					foreach ($robots as $robot)
					{
						$robotsNames[$direction][] = $robot->getName();
					}
				}
				$robotsNames['restoreData'] = $originalTemplates;
			}
			if ($triggerResult->isSuccess())
			{
				/**
				 * @var  string $direction
				 * @var  \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger[] | \Bitrix\Crm\Automation\Trigger\Entity\TriggerObject[] $triggers
				 */
				foreach ($triggerResult->getData() as $direction => $triggers)
				{
					$triggerNames[$direction] = [];
					foreach ($triggers as $trigger)
					{
						$triggerNames[$direction][] = $trigger->getId();
					}
				}

				/**
				 * @var \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger[] | \Bitrix\Crm\Automation\Trigger\Entity\TriggerObject[] $triggersForRecovery
				 * @var \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger[] | \Bitrix\Crm\Automation\Trigger\Entity\TriggerObject[] $triggersForDeletion
				 */
				$triggersForRecovery = [];
				$triggersForDeletion = [];
				switch ($this->action)
				{
					case 'copy':
						$triggersForDeletion = $triggerResult->getData()['copied'] ?? [];
						break;

					case 'move':
						$triggersForRecovery = $triggerResult->getData()['original'] ?? [];
						$triggersForDeletion = $triggerResult->getData()['moved'] ?? [];
						break;
				}

				$originalTriggers = [];
				foreach ($triggersForRecovery as $trigger)
				{
					$rawTrigger = $trigger->getValues();
					unset($rawTrigger['ID']);

					$originalTriggers[] = $rawTrigger;
				}
				foreach ($triggersForDeletion as $trigger)
				{
					$rawTrigger = $trigger->getValues();
					$rawTrigger['DELETED'] = 'Y';

					$originalTriggers[] = $rawTrigger;
				}

				$this->createTarget()->prepareTriggersToShow($originalTriggers);
				$triggerNames['restoreData'] = $originalTriggers;
			}

			$result = new Result();
			$result->addErrors($robotResult->getErrors());
			$result->addErrors($triggerResult->getErrors());
			if ($result->isSuccess())
			{
				$result->setData([
					'robots' => $robotsNames,
					'triggers' => $triggerNames,
				]);
			}

			return $result;
		}

		return $creationResult;
	}

	private function createTarget(): \Bitrix\Bizproc\Automation\Target\BaseTarget
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		return $documentService->createAutomationTarget($this->documentType);
	}

	private function normalizeRawTemplateScope(array $scope): array
	{
		return [
			'DocumentType' => $scope['DocumentType'] ?? [],
			'Category' => $scope['Category'] ?? [],
			'Status' => $scope['Status'] ?? [],
		];
	}

	private function checkCurrentUserRights(array $complexDocumentType, $documentCategoryId): bool
	{
		return CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateAutomation,
			\Bitrix\Main\Engine\CurrentUser::get()->getId(),
			$complexDocumentType,
			['DocumentCategoryId' => $documentCategoryId]
		);
	}
}