<?php

namespace Bitrix\Bizproc\Integration\Crm;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateSettingsTable;
use Bitrix\Main\Event;

class CategoryEventListener
{
	const CRM_MODULE = 'crm';
	const CRM_ENTITY_DEAL = 'CCrmDocumentDeal';

	public static function dealCategoryOnBeforeDelete(Event $event): void
	{
		$categoryId = $event->getParameter('id')['ID'];

		$templateIds = WorkflowTemplateTable::getIdsByDocument([
			self::CRM_MODULE,
			self::CRM_ENTITY_DEAL,
			'DEAL'
		]);

		if (!empty($templateIds))
		{
			WorkflowTemplateSettingsTable::deleteSettingsByFilter([
				'=NAME' => WorkflowTemplateSettingsTable::SHOW_CATEGORY_PREFIX . $categoryId,
				'@TEMPLATE_ID' => $templateIds,
			]);
		}
	}

	public static function itemCategoryOnBeforeDelete(Event $event): void
	{
		$categoryId = $event->getParameter('id')['ID'];

		$data = \Bitrix\Crm\Model\ItemCategoryTable::getRow([
			'filter' => ['ID' => $categoryId],
			'select' => ['ENTITY_TYPE_ID'],
		]);

		if (!empty($data))
		{
			$templateIds = WorkflowTemplateTable::getIdsByDocument([
				self::CRM_MODULE,
				'Bitrix\Crm\Integration\BizProc\Document\Dynamic',
				'DYNAMIC_' . $data['ENTITY_TYPE_ID'],
			]);

			if (!empty($templateIds))
			{
				WorkflowTemplateSettingsTable::deleteSettingsByFilter([
					'=NAME' => WorkflowTemplateSettingsTable::SHOW_CATEGORY_PREFIX . $categoryId,
					'@TEMPLATE_ID' => $templateIds,
				]);
			}
		}
	}
}
