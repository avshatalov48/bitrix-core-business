<?php
namespace Bitrix\Lists\Copy\Implement\Children;

use Bitrix\Iblock\Copy\Implement\Children\Workflow as IblockWorkflow;
use Bitrix\Lists\BizprocDocumentLists;

class Workflow extends IblockWorkflow
{
	protected function getDocumentType(int $iblockId): array
	{
		$entity = ($this->iblockTypeId == "bitrix_processes" ? \BizprocDocument::class : BizprocDocumentLists::class);
		return ["lists", $entity, "iblock_".$iblockId];
	}
}