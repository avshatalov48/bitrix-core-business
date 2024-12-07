<?php

namespace Bitrix\Bizproc\Workflow\Template\Packer;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Bizproc\Workflow\Template\Tpl;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use CBPDocument;
use CBPRuntime;

class Bpt extends BasePacker
{
	const DIRECTION_EXPORT = 0;
	const DIRECTION_IMPORT = 1;

	protected $useCompression = true;

	public function enableCompression()
	{
		$this->useCompression = true;
	}

	public function disableCompression()
	{
		$this->useCompression = false;
	}

	public function makePackageData(Tpl $tpl)
	{
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFieldsTmp = $documentService->GetDocumentFields($tpl->getDocumentComplexType(), true);
		$documentFieldsAliasesMap = CBPDocument::getDocumentFieldsAliasesMap($documentFieldsTmp);

		$documentFields = [];
		$len = mb_strlen("_PRINTABLE");
		foreach ($documentFieldsTmp as $k => $v)
		{
			if (mb_strtoupper(mb_substr($k, -$len)) != "_PRINTABLE")
			{
				$documentFields[$k] = $v;
			}
		}

		$tplData = $tpl->collectValues();

		if ($documentFieldsAliasesMap)
		{
			self::replaceTemplateDocumentFieldsAliases($tplData['TEMPLATE'], $documentFieldsAliasesMap);
			self::replaceVariablesDocumentFieldsAliases($tplData['PARAMETERS'], $documentFieldsAliasesMap);
			self::replaceVariablesDocumentFieldsAliases($tplData['VARIABLES'], $documentFieldsAliasesMap);
			self::replaceVariablesDocumentFieldsAliases($tplData['CONSTANTS'], $documentFieldsAliasesMap);
		}

		return [
			"VERSION" => 2,
			"TEMPLATE" => $tplData["TEMPLATE"],
			"PARAMETERS" => $tplData["PARAMETERS"],
			"VARIABLES" => $tplData["VARIABLES"],
			"CONSTANTS" => $tplData["CONSTANTS"],
			"DOCUMENT_FIELDS" => $documentFields,
		];
	}

	public function pack(Tpl $tpl)
	{
		$datum = $this->makePackageData($tpl);
		$datum = serialize($datum);

		if ($this->useCompression)
		{
			$datum = $this->compress($datum);
		}

		return (new Result\Pack())->setPackage($datum);
	}

	public function unpack($data)
	{
		$result = new Result\Unpack();

		$datumTmp = \CheckSerializedData($data) ? @unserialize($data, ['allowed_classes' => false]) : null;

		if (!is_array($datumTmp) || is_array($datumTmp) && !array_key_exists("TEMPLATE", $datumTmp))
		{
			$datumTmp = $this->uncompress($data);
			$datumTmp = \CheckSerializedData($datumTmp) ? @unserialize($datumTmp, ['allowed_classes' => false]) : null;
		}

		if (!is_array($datumTmp) || is_array($datumTmp) && !array_key_exists("TEMPLATE", $datumTmp))
		{
			$result->addError(new Main\Error(Loc::getMessage("BIZPROC_WF_TEMPLATE_BPT_WRONG_DATA")));
			return $result;
		}

		if (array_key_exists("VERSION", $datumTmp) && $datumTmp["VERSION"] == 2)
		{
			$datumTmp["CONSTANTS"] = $datumTmp["CONSTANTS"] ?? [];
		}

		/** @var Tpl $tpl */
		$tpl = WorkflowTemplateTable::createObject();
		$tpl->set('TEMPLATE', $datumTmp['TEMPLATE']);
		$tpl->set('PARAMETERS', $datumTmp['PARAMETERS']);
		$tpl->set('VARIABLES', $datumTmp['VARIABLES']);
		$tpl->set('CONSTANTS', $datumTmp['CONSTANTS']);
		$result->setTpl($tpl);

		if (is_array($datumTmp['DOCUMENT_FIELDS'] ?? null))
		{
			$result->setDocumentFields($datumTmp['DOCUMENT_FIELDS']);
		}

		return $result;
	}

	private static function replaceTemplateDocumentFieldsAliases(&$template, $aliases)
	{
		foreach ($template as &$activity)
		{
			self::replaceActivityDocumentFieldsAliases($activity, $aliases);
			if (is_array($activity["Children"] ?? null))
			{
				self::replaceTemplateDocumentFieldsAliases($activity['Children'], $aliases);
			}
		}
	}

	private static function replaceActivityDocumentFieldsAliases(&$activity, $aliases)
	{
		if (!is_array($activity['Properties']))
			return;

		foreach ($activity['Properties'] as $key => $value)
		{
			$activity['Properties'][$key] = self::replaceValueDocumentFieldsAliases($value, $aliases);
			// Replace field conditions
			if ($activity['Type'] === 'IfElseBranchActivity' && $key === 'fieldcondition')
			{
				$activity['Properties'][$key] = self::replaceFieldConditionsDocumentFieldsAliases(
					$activity['Properties'][$key],
					$aliases
				);
			}
		}
	}

	private static function replaceVariablesDocumentFieldsAliases(&$variables, $aliases)
	{
		if (!is_array($variables))
			return;

		foreach ($variables as $key => &$variable)
		{
			$variable['Default'] = self::replaceValueDocumentFieldsAliases($variable['Default'], $aliases);
			//Type Internalselect use options as link to document field.
			if (
				isset($variable['Options'])
				&& is_scalar($variable['Options'])
				&& array_key_exists($variable['Options'], $aliases)
			)
			{
				$variable['Options'] = $aliases[$variable['Options']];
			}
		}
	}

	private static function replaceValueDocumentFieldsAliases($value, $aliases)
	{
		if (is_array($value))
		{
			$replacesValue = array();
			foreach ($value as $key => $val)
			{
				if (array_key_exists($key, $aliases))
					$key = $aliases[$key];

				$replacesValue[$key] = self::replaceValueDocumentFieldsAliases($val, $aliases);
			}

			if (
				sizeof($replacesValue) == 2
				&& isset($replacesValue[0])
				&& $replacesValue[0] == 'Document'
				&& isset($replacesValue[1])
				&& array_key_exists($replacesValue[1], $aliases)
			)
			{
				$replacesValue[1] = $aliases[$replacesValue[1]];
			}
			$value = $replacesValue;
		}
		else
		{
			foreach ($aliases as $search => $replace)
			{
				$value = preg_replace('#(\{=\s*Document\s*\:\s*)'.$search.'#i', '\\1'.$replace, $value);
			}
		}

		return $value;
	}

	private static function replaceFieldConditionsDocumentFieldsAliases($conditions, $aliases)
	{
		foreach ($conditions as $key => $condition)
		{
			if (array_key_exists($condition[0], $aliases))
				$conditions[$key][0] = $aliases[$condition[0]];
		}

		return $conditions;
	}
}
