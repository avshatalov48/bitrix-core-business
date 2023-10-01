<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Script\Entity\ScriptTable;
use Bitrix\Bizproc\Script\Queue;
use Bitrix\Bizproc\Script\StartScriptResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Script\Manager;

class Script extends Base
{
	public const START_STATUS_NOT_PERMITTED = 'NOT_PERMITTED';
	public const START_STATUS_NOT_EXISTS = 'NOT_EXISTS';
	public const START_STATUS_NO_DOCUMENTS = 'NO_DOCUMENTS';
	public const START_STATUS_FILL_PARAMETERS = 'FILL_PARAMETERS';
	public const START_STATUS_INVALID_PARAMETERS = 'INVALID_PARAMETERS';
	public const START_STATUS_QUEUED = 'QUEUED';

	public function startAction($scriptId, array $documentIds, array $parameters = [])
	{
		$userId = $this->getCurrentUser()->getId();
		$documentIds = array_unique($documentIds);

		if (!Manager::checkDocumentIdsLimit($documentIds))
		{
			return [
				'status' => static::START_STATUS_NOT_PERMITTED,
				'error' => Loc::getMessage(
					'BIZPROC_CONTROLLER_SCRIPT_ERROR_DOCUMENT_ID_LIMIT',
					[
						'#LIMIT#' => Manager::getDocumentIdLimit(),
						'#SELECTED#' => count($documentIds),
					]
				)
			];
		}

		$script = Manager::getById($scriptId);
		if (!$script)
		{
			return [
				'status' => static::START_STATUS_NOT_EXISTS,
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}
		if (!$script->getActive())
		{
			return [
				'status' => static::START_STATUS_NOT_PERMITTED,
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_START_INACTIVE')
			];
		}

		if (!Manager::checkQueuesCount($scriptId))
		{
			return [
				'status' => static::START_STATUS_NOT_PERMITTED,
				'error' => Loc::getMessage(
					'BIZPROC_CONTROLLER_SCRIPT_ERROR_QUEUES_LIMIT',
					[
						'#LIMIT#' => Manager::getQueuesLimit(),
						'#CNT#' => Manager::getActiveQueueCountByScriptId($scriptId),
					]
				)
			];
		}

		$script->fill('WORKFLOW_TEMPLATE');
		$tpl = $script->getWorkflowTemplate();
		if (!$tpl)
		{
			return [
				'status' => static::START_STATUS_NOT_EXISTS,
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NO_TEMPLATE')
			];
		}

		$templateParameters = $tpl->getParameters();
		if ($templateParameters)
		{
			$parameters = $this->grabParameters($templateParameters, $parameters);

			if (empty($parameters))
			{
				return [
					'status' => static::START_STATUS_FILL_PARAMETERS,
					'parameters' => self::convertTemplateParameters($templateParameters, $tpl->getDocumentComplexType()),
					'documentType' => $tpl->getDocumentComplexType(),
					'scriptName' => $script->getName(),
				];
			}
		}

		$result = Manager::startScript($scriptId, $userId, $documentIds, $parameters);
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if ($error->getCode() === StartScriptResult::CODE_NOT_ENOUGH_RIGHTS)
			{
				return [
					'status' => static::START_STATUS_NOT_PERMITTED,
					'error' => $error->getMessage(),
				];
			}
			if ($error->getCode() === StartScriptResult::CODE_INVALID_PARAMETERS)
			{
				return [
					'status' => static::START_STATUS_INVALID_PARAMETERS,
					'error' => $error->getMessage(),
				];
			}

			return [
				'error' => $error->getMessage(),
			];
		}

		return [
			'status' => static::START_STATUS_QUEUED,
			'queueId' => $result->getData()['queueId'],
		];
	}

	public function deleteAction($scriptId)
	{
		$userId = $this->getCurrentUser()->getId();
		$script = Manager::getById($scriptId);

		if (!$script)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		if (!Manager::canUserEditScript($script->getId(), $userId))
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_DELETE_SCRIPT')
			];
		}

		$result = Manager::deleteScript($scriptId);

		if (!$result->isSuccess())
		{
			return ['error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_DELETE_RUNNING_SCRIPT')];
		}

		return ['status' => 'success'];
	}

	public function activateAction(int $scriptId)
	{
		$script = Manager::getById($scriptId);

		if (!$script)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		$userId = $this->getCurrentUser()->getId();
		if (!Manager::canUserEditScript($script->getId(), $userId))
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_UPDATE_SCRIPT')
			];
		}

		Manager::activateScript($scriptId);

		return ['status' => 'success'];
	}

	public function deactivateAction(int $scriptId)
	{
		$script = Manager::getById($scriptId);

		if (!$script)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		$userId = $this->getCurrentUser()->getId();
		if (!Manager::canUserEditScript($script->getId(), $userId))
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_UPDATE_SCRIPT')
			];
		}

		Manager::deactivateScript($scriptId);

		return ['status' => 'success'];
	}

	public function terminateQueueAction(int $queueId)
	{
		$userId = (int)$this->getCurrentUser()->getId();
		$queue = Manager::getQueueById($queueId);

		if (!$queue)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		if ($userId !== $queue->getStartedBy() && !Manager::canUserStartScript($queue->getScriptId(), $userId))
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_TERMINATE')
			];
		}

		if ($queue->getStatus() > Queue\Status::EXECUTING)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_TERMINATE_FINISHED')
			];
		}

		Manager::terminateQueue($queueId, $userId);
		return ['status' => 'success'];
	}

	public function execQueueAction(int $queueId)
	{
		$queue = Manager::getQueueById($queueId);

		if (!$queue)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		//emulate Stepper step
		$stepper = Queue\Stepper::createInstance();
		$option = [];
		$stepper->setOuterParams([$queueId, $queue->getScriptId()]);
		$result = $stepper->execute($option);

		return ['status' => 'success', 'finished' => ($result === $stepper::FINISH_EXECUTION)];
	}

	public function deleteQueueAction(int $queueId)
	{
		$userId = (int)$this->getCurrentUser()->getId();
		$queue = Manager::getQueueById($queueId);

		if (!$queue)
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_NOT_EXISTS')
			];
		}

		if ($userId !== $queue->getStartedBy() && !Manager::canUserStartScript($queue->getScriptId(), $userId))
		{
			return [
				'error' => Loc::getMessage('BIZPROC_CONTROLLER_SCRIPT_CANT_DELETE_QUEUE')
			];
		}

		Manager::deleteQueue($queueId, $userId);
		return ['status' => 'success'];
	}

	private static function convertTemplateParameters(array $parameters, array $documentType): array
	{
		$result = [];
		foreach ($parameters as $id => $parameter)
		{
			$parameter = FieldType::normalizeProperty($parameter);
			$parameter['Id'] = $id;

			if ($parameter['Type'] === 'user')
			{
				$parameter['Default'] = \CBPHelper::UsersArrayToString(
					$parameter['Default'], [], $documentType
				);
			}

			$result[] = $parameter;
		}
		return $result;
	}

	private function getFileParameters(): array
	{
		$parameters = [];

		foreach ($this->request->getFileList()->getValues() as $key => $value)
		{
			if (array_key_exists('name', $value))
			{
				if (is_array($value['name']))
				{
					$ks = array_keys($value["name"]);
					for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
					{
						$ar = array();
						foreach ($value as $k1 => $v1)
							$ar[$k1] = $v1[$ks[$i]];

						$parameters[$key][] = $ar;
					}
				}
				else
				{
					$parameters[$key] = $value;
				}
			}
		}

		return $parameters;
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	private function grabParameters(array $templateParameters, array $parameters): array
	{
		$parameters += $this->getFileParameters();

		foreach (array_keys($templateParameters) as $paramId)
		{
			if (!array_key_exists($paramId, $parameters) && $this->request->getPost($paramId) !== null)
			{
				$parameters[$paramId] = $this->request->getPost($paramId);
			}
		}

		return $parameters;
	}
}