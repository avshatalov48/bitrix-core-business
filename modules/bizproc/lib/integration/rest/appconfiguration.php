<?php

namespace Bitrix\Bizproc\Integration\Rest;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Crm\Automation\Trigger\Entity\TriggerTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use CBPDocument;
use Exception;

Loc::loadMessages(__FILE__);

class AppConfiguration
{
	private static $entityList = [
		'BIZPROC_MAIN' => 500,
		'BIZPROC_CRM_TRIGGER' => 600,
	];
	private static $customDealMatch = '/^C([0-9]+):/';
	private static $accessModules = ['crm'];
	private static $context;
	private static $accessManifest = [
		'total',
		'bizproc_crm'
	];

	public static function getEntityList()
	{
		return static::$entityList;
	}

	public static function onEventExportController(Event $event)
	{
		$result = null;
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return $result;
		}

		$manifest = $event->getParameter('MANIFEST');
		$access = array_intersect($manifest['USES'], static::$accessManifest);
		if(!$access)
		{
			return $result;
		}

		try
		{
			if(static::checkRequiredParams($code))
			{
				$step = $event->getParameter('STEP');
				switch ($code)
				{
					case 'BIZPROC_MAIN':
						$result = static::exportBizproc($step);
						break;
					case 'BIZPROC_CRM_TRIGGER':
						$result = static::exportCrmTrigger($step);
						break;
				}
			}
		}
		catch (Exception $e)
		{
			$result['NEXT'] = false;
			$result['ERROR_ACTION'] = $e->getMessage();
			$result['ERROR_MESSAGES'] = Loc::getMessage(
				'BIZPROC_ERROR_CONFIGURATION_EXPORT_EXCEPTION',
				[
					'#CODE#' => $code
				]
			);
		}

		return $result;
	}

	public static function onEventClearController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return null;
		}
		$result = null;

		try
		{
			if(static::checkRequiredParams($code))
			{
				$option = $event->getParameters();
				switch ($code)
				{
					case 'BIZPROC_MAIN':
						$result = static::clearBizproc($option);
						break;
					case 'BIZPROC_CRM_TRIGGER':
						$result = static::clearCrmTrigger($option);
						break;
				}
			}
		}
		catch (Exception $e)
		{
			$result['NEXT'] = false;
			$result['ERROR_ACTION'] = $e->getMessage();
			$result['ERROR_MESSAGES'] = Loc::getMessage(
				'BIZPROC_ERROR_CONFIGURATION_CLEAR_EXCEPTION',
				[
					'#CODE#' => $code
				]
			);
		}

		return $result;
	}

	public static function onEventImportController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if(!static::$entityList[$code])
		{
			return null;
		}
		$result = null;

		try
		{
			if(static::checkRequiredParams($code))
			{
				$data = $event->getParameters();
				switch ($code)
				{
					case 'BIZPROC_MAIN':
						$result = static::importBizproc($data);
						break;
					case 'BIZPROC_CRM_TRIGGER':
						$result = static::importCrmTrigger($data);
						break;
				}
			}
		}
		catch (Exception $e)
		{
			$result['NEXT'] = false;
			$result['ERROR_ACTION'] = $e->getMessage();
			$result['ERROR_MESSAGES'] = Loc::getMessage(
				'BIZPROC_ERROR_CONFIGURATION_IMPORT_EXCEPTION',
				[
					'#CODE#' => $code
				]
			);
		}

		return $result;
	}

	/**
	 * @param $type string of event
	 * @throws SystemException
	 * @return boolean
	 */
	private static function checkRequiredParams($type)
	{
		$return = true;
		if($type == 'BIZPROC_MAIN')
		{
			if(
				!class_exists('\Bitrix\Bizproc\Workflow\Template\Packer\Bpt')
				|| !method_exists('Bitrix\Bizproc\Workflow\Template\Packer\Bpt', 'makePackageData')
			)
			{
				throw new SystemException('not available bizproc');
			}
		}
		elseif($type == 'BIZPROC_CRM_TRIGGER')
		{
			if(!Loader::IncludeModule('crm'))
			{
				throw new SystemException('need install module: crm');
			}
		}

		return $return;
	}

	//region bizproc
	private static function exportBizproc($step)
	{
		$result = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false
		];
		$res = WorkflowTemplateTable::getList(
			[
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [
					'=MODULE_ID' => static::$accessModules
				],
				'limit' => 1,
				'offset' => $step
			]
		);
		if($tpl = $res->fetchObject())
		{
			$result['NEXT'] = $step;
			if(in_array($tpl->getModuleId(), static::$accessModules))
			{
				$result['FILE_NAME'] = $step;
				$packer = new \Bitrix\Bizproc\Workflow\Template\Packer\Bpt();
				$data =  $packer->makePackageData($tpl);
				$result['CONTENT'] = [
					'ID' => $tpl->getId(),
					'MODULE_ID' => $tpl->getModuleId(),
					'ENTITY' => $tpl->getEntity(),
					'DOCUMENT_TYPE' => $tpl->getDocumentType(),
					'DOCUMENT_STATUS' => $tpl->getDocumentStatus(),
					'NAME' => $tpl->getName(),
					'AUTO_EXECUTE' => $tpl->getAutoExecute(),
					'DESCRIPTION' => $tpl->getDescription(),
					'SYSTEM_CODE' => $tpl->getSystemCode(),
					'ORIGINATOR_ID' => $tpl->getOriginatorId(),
					'ORIGIN_ID' => $tpl->getOriginId(),
					'TEMPLATE_DATA' => $data
				];
			}
		}

		return $result;
	}

	private static function clearBizproc($option)
	{
		$result = [
			'NEXT' => false
		];
		$clearFull = $option['CLEAR_FULL'];
		$prefix = $option['PREFIX_NAME'];
		$pattern = '/^\('.$prefix.'\)/';

		$res = WorkflowTemplateTable::getList(
			[
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [
					'=MODULE_ID' => static::$accessModules,
					'>ID' => $option['NEXT']
				],
				'select' => ['*']
			]
		);
		$errorsTmp = [];
		while($item = $res->Fetch())
		{
			$result['NEXT'] = $item['ID'];

			if(!$clearFull && $item['DOCUMENT_TYPE'] == 'DEAL')
			{
				//dont off old custom deal robot
				$matches = [];
				preg_match(static::$customDealMatch, $item['DOCUMENT_STATUS'], $matches, PREG_OFFSET_CAPTURE);
				if(!empty($matches))
				{
					continue;
				}
			}

			if($clearFull || !empty($item['DOCUMENT_STATUS']))
			{
				CBPDocument::DeleteWorkflowTemplate(
					$item['ID'],
					[
						$item['MODULE_ID'],
						$item['ENTITY'],
						$item['DOCUMENT_TYPE']
					],
					$errorsTmp
				);
			}
			else
			{
				$name = $item['NAME'];
				if($prefix != '' && preg_match($pattern, $name) === 0)
				{
					$name = "($prefix) ".$name;
				}
				CBPDocument::UpdateWorkflowTemplate(
					$item['ID'],
					[
						$item['MODULE_ID'],
						$item['ENTITY'],
						$item['DOCUMENT_TYPE']
					],
					[
						'ACTIVE' => 'N',
						'AUTO_EXECUTE' => \CBPDocumentEventType::None,
						'NAME' => $name
					],
					$errorsTmp
				);
			}
		}

		return $result;
	}

	private static function importBizproc($importData)
	{
		$result = true;
		if (!isset($importData['CONTENT']['DATA']))
		{
			return $result;
		}
		$item = $importData['CONTENT']['DATA'];
		if(
			in_array($item['MODULE_ID'], static::$accessModules)
			&& Loader::includeModule($item['MODULE_ID'])
			&& class_exists($item['ENTITY'])
		)
		{
			if (is_subclass_of($item['ENTITY'], '\\IBPWorkflowDocument'))
			{
				$item['TEMPLATE_DATA'] = is_array($item['TEMPLATE_DATA']) ? @serialize($item['TEMPLATE_DATA']) : $item['TEMPLATE_DATA'];

				try
				{
					$code = static::$context.'_xml_'.intVal($item['ID']);
					$id = \CBPWorkflowTemplateLoader::ImportTemplate(
						0,
						[
							$item['MODULE_ID'],
							$item['ENTITY'],
							$item['DOCUMENT_TYPE']
						],
						$item['AUTO_EXECUTE'],
						$item['NAME'],
						isset($item['DESCRIPTION']) ? (string) $item['DESCRIPTION'] : '',
						$item['TEMPLATE_DATA'],
						$code
					);

					if($id > 0 && $item['DOCUMENT_STATUS'])
					{
						if($item['DOCUMENT_TYPE'] == 'DEAL' && isset($importData['RATIO']['CRM_STATUS']))
						{
							$statusRatio = $importData['RATIO']['CRM_STATUS'];
							$item['DOCUMENT_STATUS'] = preg_replace_callback(
								static::$customDealMatch,
								function($matches) use ($statusRatio)
								{
									if(!empty($statusRatio[$matches[1]]))
									{
										$matches[0] = str_replace($matches[1], $statusRatio[$matches[1]], $matches[0]);
									}
									return $matches[0];
								},
								$item['DOCUMENT_STATUS']
							);
						}
						\CBPWorkflowTemplateLoader::update(
							$id,
							[
								'DOCUMENT_STATUS' => $item['DOCUMENT_STATUS'],
							]
						);
					}
				}
				catch (\Exception $e)
				{
					$result['ERROR_ACTION'] = $e->getMessage();
					$result['ERROR_MESSAGES'] = Loc::getMessage(
						'BIZPROC_ERROR_CONFIGURATION_IMPORT_EXCEPTION_BP'
					);
				}
			}
		}

		return $result;
	}
	//end region bizproc

	//region trigger
	private static function exportCrmTrigger($step)
	{
		$result = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false
		];

		$res = TriggerTable::getList(
			[
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [],
				'select' => ['*'],
				'limit' => 1,
				'offset' => $step
			]
		);
		if($item = $res->Fetch())
		{
			$result['FILE_NAME'] = $step;
			$result['CONTENT'] = $item;
			$result['NEXT'] = $step;
		}

		return $result;
	}

	private static function clearCrmTrigger($option)
	{
		$result = [
			'NEXT' => false
		];
		$clearFull = $option['CLEAR_FULL'];

		$res = TriggerTable::getList(
			[
				'order' => [
					'ID' => 'ASC'
				],
				'filter' => [
					'>ID' => $option['NEXT']
				],
				'limit' => 10
			]
		);
		while ($item = $res->Fetch())
		{
			$result['NEXT'] = $item['ID'];
			if (!$clearFull && $item['ENTITY_TYPE_ID'] == \CCrmOwnerType::Deal)
			{
				//dont off old custom deal trigger
				$matches = [];
				preg_match(static::$customDealMatch, $item['ENTITY_STATUS'], $matches, PREG_OFFSET_CAPTURE);
				if (!empty($matches))
				{
					continue;
				}
			}
			TriggerTable::delete($item['ID']);
		}

		return $result;
	}

	private static function importCrmTrigger($importData)
	{
		$result = true;
		if (!isset($importData['CONTENT']['DATA']))
		{
			return $result;
		}
		$item = $importData['CONTENT']['DATA'];

		if(
			isset($item['NAME'])
			&& isset($item['CODE'])
			&& isset($item['ENTITY_TYPE_ID'])
			&& isset($item['ENTITY_STATUS'])
		)
		{
			if ($item['ENTITY_TYPE_ID'] == \CCrmOwnerType::Deal)
			{
				$statusRatio = $importData['RATIO']['CRM_STATUS'];
				$item['ENTITY_STATUS'] = preg_replace_callback(
					static::$customDealMatch,
					function($matches) use ($statusRatio)
					{
						if(!empty($statusRatio[$matches[1]]))
						{
							$matches[0] = str_replace($matches[1], $statusRatio[$matches[1]], $matches[0]);
						}
						return $matches[0];
					},
					$item['ENTITY_STATUS']
				);
			}
			$saveData = [
				'NAME' => $item['NAME'],
				'CODE' => $item['CODE'],
				'ENTITY_TYPE_ID' => $item['ENTITY_TYPE_ID'],
				'ENTITY_STATUS' => $item['ENTITY_STATUS'],
				'APPLY_RULES' => is_array($item['APPLY_RULES']) ? $item['APPLY_RULES'] : null
			];
			TriggerTable::add($saveData);
		}

		return $result;
	}
	//end region trigger
}