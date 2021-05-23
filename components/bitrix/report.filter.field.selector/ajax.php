<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Report\Internals\Controller;
use Bitrix\Main\Error;



require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::IncludeModule('report') || !\Bitrix\Main\Application::getInstance()->getContext()
		->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class ReportFilterFieldSelectorController extends Controller
{
	const ERROR_USER_FIELD_NOT_FOUND = 'REPORT_FFSAC_22001';

	protected function listActions()
	{
		return array(
			'LoadEnumerationValues' => array(
				'method' => array('POST')
			)
		);
	}

	protected function processActionLoadEnumerationValues()
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		$this->checkRequiredPostParams(array('entityId', 'fieldName'));
		if($this->errorCollection->count())
		{
			$this->sendJsonErrorResponse();
		}

		$userTypeId = 'enumeration';
		$entityId = $this->request->getPost('entityId');
		$fieldName = $this->request->getPost('fieldName');
		$res = CUserTypeEntity::GetList(
			array(),
			array('ENTITY_ID' => $entityId, 'FIELD_NAME' => $fieldName)
		);
		if (!$field = $res->Fetch())
		{
			$this->errorCollection->add(
				array(
					new Error(
						Loc::getMessage(
							'REPORT_ERR_USER_FIELD_NOT_FOUND',
							array(
								'ENTITY_ID' => $entityId,
								'FIELD_NAME' => $fieldName
							)
						),
						self::ERROR_USER_FIELD_NOT_FOUND
					)
				)
			);
			$this->sendJsonErrorResponse();
		}

		$enumValues = array();
		if (is_array($field) && isset($field['USER_TYPE_ID']) && $field['USER_TYPE_ID'] === $userTypeId)
		{
			$fieldType = $USER_FIELD_MANAGER->getUserType($field['USER_TYPE_ID']);
			if (is_array($fieldType) && isset($fieldType['BASE_TYPE']) && $fieldType['BASE_TYPE'] === 'enum'
				&& isset($fieldType['CLASS_NAME']) && !empty($fieldType['CLASS_NAME'])
				&& is_callable(array($fieldType['CLASS_NAME'], 'GetList')))
			{
				$rsEnum = call_user_func_array(array($fieldType['CLASS_NAME'], 'GetList'), array($field));
				while ($row = $rsEnum->Fetch())
				{
					$enumValues[] = array(
						'id' => $row['ID'],
						'title' => $row['VALUE']
					);
				}
			}
		}

		$this->sendJsonSuccessResponse(array('ITEMS' => $enumValues));
	}
}

$controller = new ReportFilterFieldSelectorController();
$controller->setActionName(
	\Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action')
)->exec();
