<?php
namespace Bitrix\Iblock\Copy\Implement\Children;

use Bitrix\Bizproc\Copy\Implement\WorkflowTemplate as WorkflowTemplateImplementer;
use Bitrix\Bizproc\Copy\Integration\Helper as BizprocHelper;
use Bitrix\Bizproc\Copy\WorkflowTemplate as WorkflowTemplateCopier;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Result;

class Workflow implements Child
{
	protected $iblockTypeId;

	private $result;

	public function __construct($iblockTypeId)
	{
		$this->iblockTypeId = $iblockTypeId;

		$this->result = new Result();
	}

	/**
	 * Copies iblock child.
	 * @param int $iblockId Iblock id.
	 * @param int $copiedIblockId Copied iblock id.
	 * @return Result
	 */
	public function copy($iblockId, $copiedIblockId): Result
	{
		$documentType = $this->getDocumentType($iblockId);
		$newDocumentType = $this->getDocumentType($copiedIblockId);

		$bizprocHelper = new BizprocHelper($documentType);
		$templateIdsToCopy = $bizprocHelper->getWorkflowTemplateIds();

		$implementer = $this->getImplementer($newDocumentType);
		$copier = $this->getCopier($implementer);
		$this->result = $copier->copy($this->getContainerCollection($templateIdsToCopy));

		return $this->result;
	}

	protected function getDocumentType(int $iblockId): array
	{
		return ["iblock", \CIBlockDocument::class, "iblock_".$iblockId];
	}

	private function getImplementer(array $documentType)
	{
		return new WorkflowTemplateImplementer($documentType);
	}

	private function getCopier(WorkflowTemplateImplementer $implementer)
	{
		return new WorkflowTemplateCopier($implementer);
	}

	private function getContainerCollection(array $templateIdsToCopy)
	{
		$containerCollection = new ContainerCollection();

		foreach ($templateIdsToCopy as $templateId)
		{
			$containerCollection[] = new Container($templateId);
		}

		return $containerCollection;
	}
}