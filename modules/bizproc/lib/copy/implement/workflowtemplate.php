<?php
namespace Bitrix\Bizproc\Copy\Implement;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class WorkflowTemplate
{
	const WORKFLOW_TEMPLATE_COPY_ERROR = 'WORKFLOW_TEMPLATE_COPY_ERROR';

	protected $targetDocumentType = [];
	protected $mapStatusIdsCopiedDocument = [];

	protected $result;

	public function __construct($targetDocumentType = [], $mapStatusIdsCopiedDocument = [])
	{
		$this->targetDocumentType = $targetDocumentType;
		$this->mapStatusIdsCopiedDocument = $mapStatusIdsCopiedDocument;

		$this->result = new Result();
	}

	public function getFields($workflowTemplateId)
	{
		$queryResult = \CBPWorkflowTemplateLoader::getList([], ['ID' => $workflowTemplateId]);
		return (($fields = $queryResult->fetch()) ? $fields : []);
	}

	public function prepareFieldsToCopy(array $fields)
	{
		if (isset($fields['ID']))
		{
			unset($fields['ID']);
		}

		if ($this->targetDocumentType)
		{
			$fields['DOCUMENT_TYPE'] = $this->targetDocumentType;
		}

		if (array_key_exists($fields['DOCUMENT_STATUS'], $this->mapStatusIdsCopiedDocument))
		{
			$fields['DOCUMENT_STATUS'] = $this->mapStatusIdsCopiedDocument[$fields['DOCUMENT_STATUS']];
		}

		return $fields;
	}

	public function add(array $fields)
	{
		$result = false;

		if ($fields)
		{
			$result = \CBPWorkflowTemplateLoader::add($fields, true);
		}

		if (!$result)
		{
			$this->result->addError(
				new Error('Failed to copy workflow template', self::WORKFLOW_TEMPLATE_COPY_ERROR)
			);
		}

		return $result;
	}
}
