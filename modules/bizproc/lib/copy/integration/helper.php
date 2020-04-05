<?php
namespace Bitrix\Bizproc\Copy\Integration;

use Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable;

class Helper
{
	private $documentType;

	public function __construct($documentType)
	{
		$this->documentType = $documentType;
	}

	public function getWorkflowTemplateIds()
	{
		$templateIds = [];
		$queryResult = \CBPWorkflowTemplateLoader::getList(
			[], ["DOCUMENT_TYPE" => $this->documentType], false, false, ["ID"]);
		while ($template = $queryResult->fetch())
		{
			$templateIds[] = $template["ID"];
		}
		return $templateIds;
	}

	public function getTriggerIds()
	{
		$triggerIds = [];

		$queryResult = TriggerTable::getList([
			"select" => ["ID"],
			"filter" => [
				"=MODULE_ID" => $this->documentType[0],
				"=ENTITY" => $this->documentType[1],
				"=DOCUMENT_TYPE" => $this->documentType[2]
			]
		]);
		while ($trigger = $queryResult->fetch())
		{
			$triggerIds[] = $trigger["ID"];
		}

		return $triggerIds;
	}
}