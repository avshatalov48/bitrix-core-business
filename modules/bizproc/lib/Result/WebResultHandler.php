<?php

namespace Bitrix\Bizproc\Result;

use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;

class WebResultHandler implements DeviceResultHandler
{
	protected string $workflowId;

	public function __construct(string $workflowId)
	{
		$this->workflowId = $workflowId;
	}

	public function handle(RenderedResult $renderedResult = null): array
	{
		static $cache = [];

		$noResult = [
			'text' => Loc::getMessage('BIZPROC_WEB_RESULT_WORKFLOW_NO_RESULT'),
			'status' => \Bitrix\Bizproc\Result\RenderedResult::NO_RESULT,
		];

		if (is_null($renderedResult))
		{
			if (isset($cache[$this->workflowId]))
			{
				return $cache[$this->workflowId];
			}

			$state = WorkflowStateTable::getByPrimary(
				$this->workflowId,
				['select' => ['STARTED_BY', 'MODULE_ID', 'ENTITY', 'DOCUMENT_ID']]
			)->fetchObject();
			if (!$state)
			{
				$cache[$this->workflowId] = $noResult;

				return $cache[$this->workflowId];
			}

			$startedBy = $state->getStartedBy();
			if (empty($startedBy))
			{
				$startedBy = \CCrmBizProcHelper::getDocumentResponsibleId($state->getComplexDocumentId());
			}

			$userName = \CBPViewHelper::getUserFullNameById($startedBy);

			if ($userName)
			{
				$userName = htmlspecialcharsbx($userName);
				$userLink = '[URL=/company/personal/user/' . $startedBy . '/]' . $userName . '[/URL]';
				$text = Loc::getMessage('BIZPROC_WEB_RESULT_WORKFLOW_RESULT_USER', ['#USER#' => $userLink]) ?? '';

				$cache[$this->workflowId] = [
					'text' => \CBPHelper::convertBBtoText($text),
					'status' => \Bitrix\Bizproc\Result\RenderedResult::USER_RESULT,
				];

				return $cache[$this->workflowId];
			}

			$cache[$this->workflowId] = $noResult;

			return $cache[$this->workflowId];
		}

		switch ($renderedResult->status)
		{
			case RenderedResult::BB_CODE_RESULT:
				return [
					'text' => \CBPViewHelper::prepareTaskDescription(
						\CBPHelper::convertBBtoText(
							preg_replace('|\n+|', "\n", trim($renderedResult->text ?? '')),
						)),
					'status' => $renderedResult->status,
				];

			case RenderedResult::USER_RESULT:
				return [
					'text' => \CBPHelper::convertBBtoText(
						Loc::getMessage(
							'BIZPROC_WEB_RESULT_WORKFLOW_RESULT_USER',
								['#USER#' => $renderedResult->text]
							) ?? '',
					),
					'status' => $renderedResult->status,
				];

			case RenderedResult::NO_RIGHTS:
				return [
					'text' => $renderedResult->text ?? '',
					'status' => $renderedResult->status,
				];
			case RenderedResult::NO_RESULT:
				return $noResult;
		}
	}
}
