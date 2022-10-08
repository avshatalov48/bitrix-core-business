<?php

namespace Bitrix\Bizproc\Workflow\Template\Packer;

use Bitrix\Bizproc\Automation;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Bizproc\Workflow\Template\SourceType;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Main\Localization\Loc;
use CBPRuntime;
use Bitrix\Main;

class RoboPackage extends BasePacker
{
	public function makePackageData(Tpl $tpl)
	{
		$robotTemplate = Automation\Engine\Template::createByTpl($tpl);

		if ($robotTemplate->isExternalModified())
		{
			return false;
		}

		$robots = $robotTemplate->toArray()['ROBOTS'];

		return [
			'DOCUMENT_TYPE' => $tpl->getDocumentComplexType(),
			'DOCUMENT_STATUS' => $tpl->getDocumentStatus(),
			'NAME' => $tpl->getName(),
			'DESCRIPTION' => $tpl->getDescription(),
			'PARAMETERS' => $tpl->getParameters(),
			'VARIABLES' => $tpl->getVariables(),
			'CONSTANTS' => $tpl->getConstants(),
			'SYSTEM_CODE' => $tpl->getSystemCode(),
			'ORIGINATOR_ID' => $tpl->getOriginatorId(),
			'ORIGIN_ID' => $tpl->getOriginId(),

			'ROBOTS' => $robots,
			'DOCUMENT_FIELDS' => $this->getUsedDocumentFields($tpl),
			'REQUIRED_APPLICATIONS' => $this->getRequiredApplications($tpl),
		];
	}

	public function pack(Tpl $tpl)
	{
		$datum = $this->makePackageData($tpl);

		if (!$datum)
		{
			return (new Result\Pack())->addError(new Main\Error(Loc::getMessage("BIZPROC_WF_TEMPLATE_ROBOPACKAGE_EXTERNAL_MODIFIED")));
		}

		$datum = Main\Web\Json::encode($datum);
		$datum = $this->compress($datum);

		return (new Result\Pack())->setPackage($datum);
	}

	public function unpack($data)
	{
		$result = new Result\Unpack();
		$datumTmp = $this->uncompress($data);

		if (is_string($datumTmp))
		{
			try
			{
				$datumTmp = Main\Web\Json::decode($data);
			}
			catch (Main\ArgumentException $e)
			{
				//do nothing
			}
		}

		if (is_array($datumTmp) && is_array($datumTmp['ROBOTS']))
		{
			$robotTemplate = new Automation\Engine\Template($datumTmp['DOCUMENT_TYPE']);
			try
			{
				$robotTemplate->setRobots($datumTmp['ROBOTS']);
			}
			catch (Main\ArgumentException $e)
			{
				$result->addError(new Main\Error(Loc::getMessage("BIZPROC_WF_TEMPLATE_ROBOPACKAGE_WRONG_DATA")));

				return $result;
			}

			/** @var Tpl $tpl */
			$tpl = WorkflowTemplateTable::createObject();
			$tpl->set('MODULE_ID', $datumTmp['DOCUMENT_TYPE'][0]);
			$tpl->set('ENTITY', $datumTmp['DOCUMENT_TYPE'][1]);
			$tpl->set('DOCUMENT_TYPE', $datumTmp['DOCUMENT_TYPE'][2]);
			$tpl->set('DOCUMENT_STATUS', $datumTmp['DOCUMENT_STATUS']);
			$tpl->set('NAME', $datumTmp['NAME']);
			$tpl->set('DESCRIPTION', $datumTmp['DESCRIPTION']);
			$tpl->set('TEMPLATE', $robotTemplate->getActivities());
			$tpl->set('PARAMETERS', $datumTmp['PARAMETERS']);
			$tpl->set('VARIABLES', $datumTmp['VARIABLES']);
			$tpl->set('CONSTANTS', $datumTmp['CONSTANTS']);
			$tpl->set('SYSTEM_CODE', $datumTmp['SYSTEM_CODE']);
			$tpl->set('ORIGINATOR_ID', $datumTmp['ORIGINATOR_ID']);
			$tpl->set('ORIGIN_ID', $datumTmp['ORIGIN_ID']);

			return $result->setTpl($tpl)
				->setDocumentFields($datumTmp['DOCUMENT_FIELDS'])
				->setRequiredApplications($datumTmp['REQUIRED_APPLICATIONS']);
		}

		$result->addError(new Main\Error(Loc::getMessage("BIZPROC_WF_TEMPLATE_ROBOPACKAGE_WRONG_DATA")));

		return $result;
	}

	private function getUsedDocumentFields(Tpl $tpl)
	{
		$usedFieldKeys = $tpl->findUsedSourceKeys(SourceType::DocumentField);

		if (!$usedFieldKeys)
		{
			return [];
		}

		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->GetDocumentFields($tpl->getDocumentComplexType(), true);

		$result = [];

		foreach ($usedFieldKeys as $fieldKey)
		{
			if (
				mb_strtoupper(mb_substr($fieldKey, -10)) !== '_PRINTABLE'
				&&
				isset($documentFields[$fieldKey])
			)
			{
				$result[$fieldKey] = $documentFields[$fieldKey];
			}
		}

		return $result;
	}

	private function getRequiredApplications(Tpl $tpl)
	{
		$types = $tpl->getUsedActivityTypes();
		$apps = [];

		foreach ($types as $type)
		{
			if (mb_strpos($type, 'rest_') === 0)
			{
				$apps[] = $type;
			}
		}

		//TODO get app external id`s

		return $apps;
	}
}