<?php
namespace Bitrix\Bizproc\Automation\Script;

use Bitrix\Main;
use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;

class Manager
{
	public static function getListByPlacement($placement)
	{
		$list = WorkflowTemplateTable::getList(
			[
				'select' => [
					'ID', 'NAME', 'ORIGINATOR_ID', 'ORIGIN_ID', 'SYSTEM_CODE', 'DOCUMENT_STATUS',
					'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION', 'IS_MODIFIED', 'PARAMETERS'
				],
				'filter' => [
					'=DOCUMENT_STATUS' => 'SCRIPT:'.$placement,
					'=AUTO_EXECUTE' => \CBPDocumentEventType::Script
				],
			]
		)->fetchAll();

		//prepare data
		static::appendAppNames($list);

		return $list;
	}

	public static function getById($id)
	{
		$tpl = WorkflowTemplateTable::getList(
			[
				'select' => [
					'ID', 'ORIGINATOR_ID', 'ORIGIN_ID', 'SYSTEM_CODE', 'DOCUMENT_STATUS',
					'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION', 'IS_MODIFIED', 'PARAMETERS'
				],
				'filter' => [
					'=ID' => $id,
					'=AUTO_EXECUTE' => \CBPDocumentEventType::Script
				],
			]
		)->fetch();

		return $tpl;
	}

	public static function createScript($fields, $userId = 1)
	{
		$documentType = [$fields['MODULE_ID'], $fields['ENTITY'], $fields['DOCUMENT_TYPE']];
		$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType);
		$template->setDocumentStatus($fields['DOCUMENT_STATUS']);
		$template->setExecuteType(\CBPDocumentEventType::Script);

		$template->save([], $userId);
		$tplId = $template->getId();

		return static::getById($tplId);
	}

	public static function editScript($id, array $fields)
	{
		if ($fields && $tpl = static::getById($id))
		{
			$wl = array_fill_keys([
				'ORIGINATOR_ID', 'ORIGIN_ID', 'SYSTEM_CODE', 'DOCUMENT_STATUS',
				'NAME', 'DESCRIPTION',
			], true);

			$fields = array_intersect_key($wl, $fields);

			return (WorkflowTemplateTable::update($tpl['ID'], $fields))->isSuccess();
		}
		return false;
	}

	public static function deleteScript($id)
	{
		try
		{
			\CBPWorkflowTemplateLoader::getLoader()->deleteTemplate($id);
			return true;
		}
		catch (\Exception $e)
		{
		}
		return false;
	}

	private static function appendAppNames(array &$list)
	{
		//todo: get app names
	}
}