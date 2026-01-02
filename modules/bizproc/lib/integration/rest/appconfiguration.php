<?php

namespace Bitrix\Bizproc\Integration\Rest;

use Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable;
use Bitrix\Bizproc\Script;
use Bitrix\Bizproc\Script\Entity\ScriptTable;
use Bitrix\Crm\Automation\Trigger\Entity\TriggerTable;
use Bitrix\Crm\Integration\Rest\Configuration\Helper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Rest\Configuration\Setting;
use CBPDocument;
use CCrmOwnerType;
use Exception;

class AppConfiguration
{
	const ENTITY_BIZPROC_MAIN = 'BIZPROC_MAIN';
	const ENTITY_BIZPROC_CRM_TRIGGER = 'BIZPROC_CRM_TRIGGER';
	const ENTITY_BIZPROC_SCRIPT = 'BIZPROC_SCRIPT';

	const OWNER_ENTITY_TYPE_BIZPROC = 'BIZPROC';
	const OWNER_ENTITY_TYPE_TRIGGER = 'TRIGGER';
	const OWNER_ENTITY_TYPE_BIZPROC_SCRIPT = 'BIZPROC_SCRIPT';

	private static $entityList = [
		self::ENTITY_BIZPROC_MAIN => 500,
		self::ENTITY_BIZPROC_CRM_TRIGGER => 600,
		self::ENTITY_BIZPROC_SCRIPT => 700,
	];
	private static $customDealMatch = '/^C([0-9]+):/';
	private static $accessModules = ['crm'];
	private static $context = '';
	private static $accessManifest = [
		'total',
		'bizproc_crm',
		'automated_solution',
	];

	public static function getEntityList()
	{
		return static::$entityList;
	}

	public static function onEventExportController(Event $event)
	{
		$result = null;

		$code = $event->getParameter('CODE');
		$itemCode = $event->getParameter('ITEM_CODE');
		if (!static::$entityList[$code])
		{
			return null;
		}

		$option = $event->getParameters();
		if (
			$code !== self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $option, static::$accessManifest)
		)
		{
			return null;
		}

		// Checking automated solution mode before initializing crm helper.
		$manifestCode = $option['MANIFEST']['CODE'] ?? '';
		$isAutomatedSolutionMode = str_starts_with($manifestCode, 'automated_solution');
		if ($isAutomatedSolutionMode && !static::isCrmModuleIncluded())
		{
			return null;
		}

		if (
			$code === self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $option, ['bizproc_script'])
			&& !$isAutomatedSolutionMode
		)
		{
			return null;
		}

		try
		{
			if (static::checkRequiredParams($code))
			{
				$step = $event->getParameter('STEP');

				// Check automated solution mode params
				if ($isAutomatedSolutionMode)
				{
					if (!(new Helper())->checkAutomatedSolutionModeExportParams($option))
					{
						return null;
					}
				}

				switch ($code)
				{
					case self::ENTITY_BIZPROC_MAIN:
						$result = static::exportBizproc($step, $option);
						break;
					case self::ENTITY_BIZPROC_CRM_TRIGGER:
						$result = static::exportCrmTrigger($step, $option);
						break;
					case self::ENTITY_BIZPROC_SCRIPT:
						$result = static::exportScript($step, $event->getParameter('NEXT'), $itemCode, $option);
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
					'#CODE#' => $code,
				]
			);
		}

		return $result;
	}

	public static function onEventClearController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if (!static::$entityList[$code])
		{
			return null;
		}
		$option = $event->getParameters();

		if (!static::checkAutomatedSolutionModeClearParams($option))
		{
			return null;
		}

		if (
			$code !== self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $option, static::$accessManifest)
		)
		{
			return null;
		}

		if (
			$code === self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $option, ['bizproc_script'])
		)
		{
			return null;
		}

		$result = null;

		try
		{
			if (static::checkRequiredParams($code))
			{
				switch ($code)
				{
					case self::ENTITY_BIZPROC_MAIN:
						$result = static::clearBizproc($option);
						break;
					case self::ENTITY_BIZPROC_CRM_TRIGGER:
						$result = static::clearCrmTrigger($option);
						break;
					case self::ENTITY_BIZPROC_SCRIPT:
						$result = static::clearScript($option);
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
					'#CODE#' => $code,
				]
			);
		}

		return $result;
	}

	public static function onEventImportController(Event $event)
	{
		$code = $event->getParameter('CODE');
		if (!static::$entityList[$code])
		{
			return null;
		}
		$data = $event->getParameters();

		if (!static::checkAutomatedSolutionModeImportParams($data))
		{
			return null;
		}

		if (
			$code !== self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $data, static::$accessManifest)
		)
		{
			return null;
		}

		if (
			$code === self::ENTITY_BIZPROC_SCRIPT
			&& !Manifest::isEntityAvailable('', $data, ['bizproc_script'])
		)
		{
			return null;
		}

		$result = null;
		$userId = (int)$event->getParameter('USER_ID');

		$contextUser = $event->getParameter('CONTEXT_USER');
		$setting = new Setting($contextUser);
		$app = $setting->get(Setting::SETTING_APP_INFO);
		$appId = (int)$app['ID'];

		try
		{
			if (static::checkRequiredParams($code))
			{
				switch ($code)
				{
					case self::ENTITY_BIZPROC_MAIN:
						$result = static::importBizproc($data);
						break;
					case self::ENTITY_BIZPROC_CRM_TRIGGER:
						$result = static::importCrmTrigger($data);
						break;
					case self::ENTITY_BIZPROC_SCRIPT:
						$result = static::importScript($data, $userId, $appId);
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
					'#CODE#' => $code,
				]
			);
		}

		return $result;
	}

	private static function isCrmModuleIncluded(): bool
	{
		return Loader::includeModule('crm');
	}

	/**
	 * @param $type string of event
	 * @return boolean
	 * @throws SystemException
	 */
	private static function checkRequiredParams($type)
	{
		if ($type == self::ENTITY_BIZPROC_CRM_TRIGGER)
		{
			if (!static::isCrmModuleIncluded())
			{
				throw new SystemException('need install module: crm');
			}
		}

		return true;
	}

	private static function exportCrmDynamicTypesInfo(array $params = []): array
	{
		$result = [];

		if (!static::isCrmModuleIncluded())
		{
			return $result;
		}

		$helper = new Helper();
		$result = $helper->exportCrmDynamicTypesInfo(['automatedSolutionModeParams' => $params]);

		return $result;
	}

	private static function checkAutomatedSolutionModeClearParams(array $params): bool
	{
		if (self::isCrmModuleIncluded())
		{
			return (new Helper())->checkAutomatedSolutionModeClearParams($params);
		}
		else
		{
			$manifestCode = $params['IMPORT_MANIFEST']['CODE'] ?? '';
			$isAutomatedSolutionMode = str_starts_with($manifestCode, 'automated_solution');
			if ($isAutomatedSolutionMode)
			{
				return false;
			}
		}

		return true;
	}

	private static function checkAutomatedSolutionModeImportParams(array $params): bool
	{
		if (self::isCrmModuleIncluded())
		{
			return (new Helper())->checkAutomatedSolutionModeImportParams($params);
		}
		else
		{
			$manifestCode = $params['IMPORT_MANIFEST']['CODE'] ?? '';
			$isAutomatedSolutionMode = str_starts_with($manifestCode, 'automated_solution');
			if ($isAutomatedSolutionMode)
			{
				return false;
			}
		}

		return true;
	}

	private static function getDynamicTypeCheckParamsByDocumentType(string $documentType): array
	{

		return  static::getDynamicTypeCheckParamsByEntityTypeId(
			static::getDynamicEntityTypeIdByDocumentType($documentType)
		);
	}

	private static function getDynamicTypeCheckParamsByEntityTypeId(int $entityTypeId): array
	{
		if (!static::isCrmModuleIncluded())
		{
			return [
				'isDynamicType' => false,
				'isDynamicTypeExists' => false,
				'dynamicTypeCustomSectionId' => 0,
			];
		}

		return (new Helper())->getDynamicTypeCheckExportParamsByEntityTypeId($entityTypeId);
	}

	private static function checkDynamicTypeExportConditions(array $params): bool
	{
		if (self::isCrmModuleIncluded())
		{
			return (new Helper())->checkDynamicTypeExportConditions($params);
		}

		return true;
	}

	private static function getAutomatedSolutionModeParams(array $params): array
	{
		if (self::isCrmModuleIncluded())
		{
			return (new Helper())->getAutomatedSolutionModeParams($params);
		}

		return [
			'isAutomatedSolutionMode' => false,
			'isSingleAutomatedSolutionMode' => false,
			'customSectionId' => 0,
		];
	}
	
	private static function getAutomatedSolutionModeImportParams(array $params): array
	{
		if (self::isCrmModuleIncluded())
		{
			return (new Helper())->getAutomatedSolutionModeImportParams($params);
		}

		return [
			'isAutomatedSolutionMode' => false,
			'isSingleAutomatedSolutionMode' => false,
		];
	}

	private static function checkDynamicTypeImportConditions(int $dynamicEntityTypeId, array $importData): bool
	{
		if (self::isCrmModuleIncluded() && $dynamicEntityTypeId > 0)
		{
			return (new Helper())->checkDynamicTypeImportConditions($dynamicEntityTypeId, $importData);
		}

		return false;
	}

	//region bizproc
	private static function exportBizproc($step, array $options = [])
	{
		$result = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false,
		];

		$helper = new Helper();
		$automatedSolutionModeParams = $helper->getAutomatedSolutionModeParams($options);

		$res = WorkflowTemplateTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => [
					'=MODULE_ID' => static::$accessModules,
					'<AUTO_EXECUTE' => \CBPDocumentEventType::Script,
				],
				'limit' => 1,
				'offset' => $step,
			]
		);
		if ($tpl = $res->fetchObject())
		{
			$result['NEXT'] = $step;
			if (in_array($tpl->getModuleId(), static::$accessModules))
			{
				$documentType = $tpl->getDocumentType();
				if (
					$helper->checkDynamicTypeExportConditions(
						array_merge(
							$automatedSolutionModeParams,
							static::getDynamicTypeCheckParamsByDocumentType($documentType)
						)
					)
				)
				{
					$result['FILE_NAME'] = $step;
					$packer = new \Bitrix\Bizproc\Workflow\Template\Packer\Bpt();
					$data = $packer->makePackageData($tpl);
					$result['CONTENT'] = [
						'ID' => $tpl->getId(),
						'MODULE_ID' => $tpl->getModuleId(),
						'ENTITY' => $tpl->getEntity(),
						'DOCUMENT_TYPE' => $documentType,
						'DOCUMENT_STATUS' => $tpl->getDocumentStatus(),
						'NAME' => $tpl->getName(),
						'AUTO_EXECUTE' => $tpl->getAutoExecute(),
						'DESCRIPTION' => $tpl->getDescription(),
						'SYSTEM_CODE' => $tpl->getSystemCode(),
						'ORIGINATOR_ID' => $tpl->getOriginatorId(),
						'ORIGIN_ID' => $tpl->getOriginId(),
						'TEMPLATE_DATA' => $data,
					];
				}

				if (static::isCrmModuleIncluded() && $step === 0)
				{
					$result['FILE_NAME'] = $step;
					$result['CONTENT']['CRM_DYNAMIC_TYPES_INFO'] =
						static::exportCrmDynamicTypesInfo($automatedSolutionModeParams)
					;
				}
			}
		}

		return $result;
	}

	private static function clearBizproc($option)
	{
		$result = [
			'NEXT' => false,
			'OWNER_DELETE' => [],
		];
		$clearFull = $option['CLEAR_FULL'];
		$prefix = $option['PREFIX_NAME'];
		$pattern = '/^\(' . $prefix . '\)/';

		$automatedSolutionModeParams = static::getAutomatedSolutionModeParams($option);

		$res = WorkflowTemplateTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => [
					'=MODULE_ID' => static::$accessModules,
					'>ID' => $option['NEXT'],
					'<AUTO_EXECUTE' => \CBPDocumentEventType::Script,
				],
				'select' => ['*'],
			]
		);
		$errorsTmp = [];
		while ($item = $res->Fetch())
		{
			$result['NEXT'] = $item['ID'];

			$documentType =
				(
					isset($item['DOCUMENT_TYPE'])
					&& is_string($item['DOCUMENT_TYPE'])
					&& $item['DOCUMENT_TYPE'] !== ''
				)
					? $item['DOCUMENT_TYPE']
					: ''
			;

			if (
				!static::checkDynamicTypeExportConditions(
					array_merge(
						$automatedSolutionModeParams,
						static::getDynamicTypeCheckParamsByDocumentType($documentType)
					)
				)
			)
			{
				continue;
			}

			if (!$clearFull && $item['DOCUMENT_TYPE'] == 'DEAL')
			{
				//dont off old custom deal robot
				$matches = [];
				preg_match(static::$customDealMatch, $item['DOCUMENT_STATUS'], $matches, PREG_OFFSET_CAPTURE);
				if (!empty($matches))
				{
					continue;
				}
			}

			if ($clearFull || !empty($item['DOCUMENT_STATUS']))
			{
				CBPDocument::DeleteWorkflowTemplate(
					$item['ID'],
					[
						$item['MODULE_ID'],
						$item['ENTITY'],
						$item['DOCUMENT_TYPE'],
					],
					$errorsTmp
				);
				if (!$errorsTmp)
				{
					$result['OWNER_DELETE'][] = [
						'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_BIZPROC,
						'ENTITY' => $item['ID'],
					];
				}
			}
			else
			{
				$name = $item['NAME'];
				if ($prefix != '' && preg_match($pattern, $name) === 0)
				{
					$name = "($prefix) " . $name;
				}
				CBPDocument::UpdateWorkflowTemplate(
					$item['ID'],
					[
						$item['MODULE_ID'],
						$item['ENTITY'],
						$item['DOCUMENT_TYPE'],
					],
					[
						'ACTIVE' => 'N',
						'AUTO_EXECUTE' => \CBPDocumentEventType::None,
						'NAME' => $name,
					],
					$errorsTmp
				);
			}
		}

		return $result;
	}

	private static function getDynamicTypeReplacementLists(
		array $dynamicTypesInfo,
		array $ratioInfo,
		bool $refresh = false
	): array
	{
		static $replacementLists = null;

		if ($replacementLists === null || $refresh)
		{
			if (static::isCrmModuleIncluded())
			{
				$crmHelper = new Helper();
				$replacementLists = $crmHelper->prepareDynamicTypeReplacementLists($dynamicTypesInfo, $ratioInfo);
			}
		}
		
		return $replacementLists;
	}

	private static function getDynamicTypesInfoForImport(array $importData, array &$importResult): array
	{
		$result = [];

		$codes = [
				self::ENTITY_BIZPROC_MAIN,
				self::ENTITY_BIZPROC_CRM_TRIGGER,
				self::ENTITY_BIZPROC_SCRIPT,
		];

		$actualCode = null;
		foreach ($codes as $code)
		{
			if (
				isset($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
				&& is_array($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
				&& !empty($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
			)
			{
				$actualCode = $code;
				break;
			}
		}

		$code = $actualCode ?? $importData['CODE'];

		if (
			isset($importData['CONTENT']['DATA']['CRM_DYNAMIC_TYPES_INFO'])
			&& is_array($importData['CONTENT']['DATA']['CRM_DYNAMIC_TYPES_INFO'])
			&& !empty($importData['CONTENT']['DATA']['CRM_DYNAMIC_TYPES_INFO'])
			&& !$actualCode
		)
		{
			$result = $importData['CONTENT']['DATA']['CRM_DYNAMIC_TYPES_INFO'];

			// refresh info
			$importResult['RATIO']['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'] = $result;
		}
		elseif (
			isset($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
			&& is_array($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
			&& !empty($importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'])
		)
		{
			// get actual info
			$result = $importData['RATIO'][$code]['CRM_DYNAMIC_TYPES_INFO_FOR_IMPORT'];
		}

		return $result;
	}

	private static function getDynamicEntityTypeIdByDocumentType(string $dynamicEntityTypeName): int
	{
		if (static::isCrmModuleIncluded())
		{
			return (new Helper())->getDynamicEntityTypeIdByEntityTypeName($dynamicEntityTypeName);
		}

		return 0;
	}

	private static function importBizproc($importData)
	{
		$result = [];

		if (!isset($importData['CONTENT']['DATA']))
		{
			return false;
		}
		$dynamicTypesInfo = static::getDynamicTypesInfoForImport($importData, $result);
		$item = $importData['CONTENT']['DATA'];
		if (
			in_array($item['MODULE_ID'], static::$accessModules)
			&& Loader::includeModule($item['MODULE_ID'])
			&& class_exists($item['ENTITY'])
		)
		{
			if (is_subclass_of($item['ENTITY'], '\\IBPWorkflowDocument'))
			{
				$oldDynamicEntityTypeId = static::getDynamicEntityTypeIdByDocumentType($item['DOCUMENT_TYPE']);
				$isDynamicType = ($oldDynamicEntityTypeId > 0);

				$automatedSolutionModeParams = static::getAutomatedSolutionModeImportParams($importData);
				if (!$isDynamicType && $automatedSolutionModeParams['isAutomatedSolutionMode'])
				{
					return $result;
				}

				if ($isDynamicType)
				{
					$crmHelper = new Helper();
					$isSetRatio = (isset($importData['RATIO']) && is_array($importData['RATIO']));
					$newDynamicEntityTypeId = $crmHelper->getDynamicEntityTypeIdByOldEntityTypeId(
						$oldDynamicEntityTypeId,
						$isSetRatio ? $importData['RATIO'] : []
					);
					if (
						!empty($dynamicTypesInfo)
						&& static::checkDynamicTypeImportConditions($newDynamicEntityTypeId, $importData)
					)
					{
						$replacementLists = static::getDynamicTypeReplacementLists(
							$dynamicTypesInfo,
							$isSetRatio ? $importData['RATIO'] : []
						);
						$item['DOCUMENT_TYPE'] =
							static::changeDynamicTypeIdentifiers(
								$oldDynamicEntityTypeId,
								$item['DOCUMENT_TYPE'],
								$importData['RATIO'],
								$replacementLists
							)
						;
						if (is_string($item['DOCUMENT_STATUS']) && $item['DOCUMENT_STATUS'] !== '')
						{
							$item['DOCUMENT_STATUS'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['DOCUMENT_STATUS'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
						if (is_string($item['NAME']) && $item['NAME'] !== '')
						{
							$item['NAME'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['NAME'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
						if(is_array($item['TEMPLATE_DATA']))
						{
							$item['TEMPLATE_DATA'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['TEMPLATE_DATA'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
					}
				}
				else
				{
					if (isset($importData['RATIO']['CRM_STATUS']))
					{
						if (is_array($item['TEMPLATE_DATA']))
						{
							$item['TEMPLATE_DATA'] = static::changeDealCategory(
								$item['TEMPLATE_DATA'],
								$importData['RATIO']['CRM_STATUS']
							);
						}
						if ($item['DOCUMENT_TYPE'] == 'DEAL' && $item['DOCUMENT_STATUS'])
						{
							$item['DOCUMENT_STATUS'] = static::changeDealCategory(
								$item['DOCUMENT_STATUS'],
								$importData['RATIO']['CRM_STATUS']
							);
						}
					}
				}

				try
				{
					$code = static::$context . '_xml_' . intval($item['ID']);
					$id = \CBPWorkflowTemplateLoader::importTemplateFromArray(
						0,
						[
							$item['MODULE_ID'],
							$item['ENTITY'],
							$item['DOCUMENT_TYPE'],
						],
						$item['AUTO_EXECUTE'],
						$item['NAME'],
						isset($item['DESCRIPTION']) ? (string)$item['DESCRIPTION'] : '',
						$item['TEMPLATE_DATA'],
						$code
					);

					if ($id > 0)
					{
						$result['OWNER'] = [
							'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_BIZPROC,
							'ENTITY' => $id,
						];

						if ($item['DOCUMENT_STATUS'])
						{
							\CBPWorkflowTemplateLoader::update(
								$id,
								[
									'DOCUMENT_STATUS' => $item['DOCUMENT_STATUS'],
								]
							);
						}
					}
				}
				catch (Exception $e)
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
	private static function exportCrmTrigger($step, array $options = [])
	{
		$result = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false,
		];

		$helper = new Helper();
		$automatedSolutionModeParams = $helper->getAutomatedSolutionModeParams($options);

		$res = TriggerTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => [],
				'select' => ['*'],
				'limit' => 1,
				'offset' => $step,
			]
		);
		if ($item = $res->Fetch())
		{
			$result['NEXT'] = $step;
			if (
				$helper->checkDynamicTypeExportConditions(
					array_merge(
						$automatedSolutionModeParams,
						static::getDynamicTypeCheckParamsByEntityTypeId((int)($item['ENTITY_TYPE_ID'] ?? 0))
					)
				)
			)
			{
				$result['FILE_NAME'] = $step;
				$result['CONTENT'] = $item;
			}

			if (static::isCrmModuleIncluded() && $step === 0)
			{
				$result['FILE_NAME'] = $step;
				$result['CONTENT']['CRM_DYNAMIC_TYPES_INFO'] =
					static::exportCrmDynamicTypesInfo($automatedSolutionModeParams)
				;
			}
		}

		return $result;
	}

	private static function clearCrmTrigger($option)
	{
		$result = [
			'NEXT' => false,
		];
		$clearFull = $option['CLEAR_FULL'];

		$automatedSolutionModeParams = static::getAutomatedSolutionModeParams($option);

		$res = TriggerTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => [
					'>ID' => $option['NEXT'],
				],
				'limit' => 10,
			]
		);
		while ($item = $res->Fetch())
		{
			$result['NEXT'] = $item['ID'];
			if (
				!static::checkDynamicTypeExportConditions(
					array_merge(
						$automatedSolutionModeParams,
						static::getDynamicTypeCheckParamsByEntityTypeId((int)($item['ENTITY_TYPE_ID'] ?? 0))
					)
				)
			)
			{
				continue;
			}

			if (!$clearFull && $item['ENTITY_TYPE_ID'] == CCrmOwnerType::Deal)
			{
				//dont off old custom deal trigger
				$matches = [];
				preg_match(static::$customDealMatch, $item['ENTITY_STATUS'], $matches, PREG_OFFSET_CAPTURE);
				if (!empty($matches))
				{
					continue;
				}
			}
			$delete = TriggerTable::delete($item['ID']);
			if ($delete->isSuccess())
			{
				$result['OWNER_DELETE'][] = [
					'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_TRIGGER,
					'ENTITY' => $item['ID'],
				];
			}
		}

		return $result;
	}

	private static function importCrmTrigger($importData)
	{
		$result = [];
		if (!isset($importData['CONTENT']['DATA']))
		{
			return false;
		}
		$dynamicTypesInfo = static::getDynamicTypesInfoForImport($importData, $result);
		$item = $importData['CONTENT']['DATA'];
		if (
			isset($item['NAME'])
			&& isset($item['CODE'])
			&& isset($item['ENTITY_TYPE_ID'])
			&& isset($item['ENTITY_STATUS'])
		)
		{
			if (isset($importData['RATIO']['CRM_STATUS']))
			{
				$crmHelper = new Helper();
				$isSetRatio = (isset($importData['RATIO']) && is_array($importData['RATIO']));
				$oldDynamicEntityTypeId = (int)$item['ENTITY_TYPE_ID'];
				$newDynamicEntityTypeId = $crmHelper->getDynamicEntityTypeIdByOldEntityTypeId(
					$oldDynamicEntityTypeId,
					$isSetRatio ? $importData['RATIO'] : []
				);
				$isDynamicType = ($newDynamicEntityTypeId > 0);

				$automatedSolutionModeParams = static::getAutomatedSolutionModeImportParams($importData);
				if (!$isDynamicType && $automatedSolutionModeParams['isAutomatedSolutionMode'])
				{
					return $result;
				}

				if ($isDynamicType)
				{
					$item['ENTITY_TYPE_ID'] = $newDynamicEntityTypeId;
					if (
						!empty($dynamicTypesInfo)
						&& static::checkDynamicTypeImportConditions($newDynamicEntityTypeId, $importData)
					)
					{
						$replacementLists = static::getDynamicTypeReplacementLists(
							$dynamicTypesInfo,
							$isSetRatio ? $importData['RATIO'] : []
						);
						if (isset($item['APPLY_RULES']) && is_array($item['APPLY_RULES']))
						{
							$item['APPLY_RULES'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['APPLY_RULES'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
						if (is_string($item['ENTITY_STATUS']) && $item['ENTITY_STATUS'] !== '')
						{
							$item['ENTITY_STATUS'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['ENTITY_STATUS'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
					}
				}
				else
				{
					if (is_array($item['APPLY_RULES']))
					{
						$item['APPLY_RULES'] = static::changeDealCategory(
							$item['APPLY_RULES'],
							$importData['RATIO']['CRM_STATUS']
						);
					}
					if ($item['ENTITY_TYPE_ID'] == CCrmOwnerType::Deal)
					{
						$item['ENTITY_STATUS'] = static::changeDealCategory(
							$item['ENTITY_STATUS'],
							$importData['RATIO']['CRM_STATUS']
						);
					}
				}
			}

			$saveData = [
				'NAME' => $item['NAME'],
				'CODE' => $item['CODE'],
				'ENTITY_TYPE_ID' => $item['ENTITY_TYPE_ID'],
				'ENTITY_STATUS' => $item['ENTITY_STATUS'],
				'APPLY_RULES' => is_array($item['APPLY_RULES']) ? $item['APPLY_RULES'] : null,
			];

			$res = TriggerTable::add($saveData);
			if ($res->isSuccess())
			{
				$result['OWNER'] = [
					'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_TRIGGER,
					'ENTITY' => $res->getId(),
				];
			}
		}

		return $result;
	}

	//end region trigger

	//region script
	private static function exportScript($step, $nextId, $docType, array $options = [])
	{
		$result = [
			'FILE_NAME' => '',
			'CONTENT' => [],
			'NEXT' => false,
		];

		$nextId = (int)$nextId;

		$filter = [
			'>ID' => $nextId,
			'=MODULE_ID' => static::$accessModules,
		];

		if ($docType)
		{
			$filter['=DOCUMENT_TYPE'] = $docType;
		}

		$helper = new Helper();
		$automatedSolutionModeParams = $helper->getAutomatedSolutionModeParams($options);

		$res = ScriptTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => $filter,
				'limit' => 1,
				'select' => ['ID'],
			]
		);
		if ($tpl = $res->fetch())
		{
			$result['NEXT'] = $tpl['ID'];
			$data = Script\Manager::exportScript($tpl['ID']);
			if ($data)
			{
				$documentType =
					(isset($data['DOCUMENT_TYPE']) && is_string($data['DOCUMENT_TYPE'])) ? $data['DOCUMENT_TYPE'] : ''
				;
				if (
					$helper->checkDynamicTypeExportConditions(
						array_merge(
							$automatedSolutionModeParams,
							static::getDynamicTypeCheckParamsByDocumentType($documentType)
						)
					)
				)
				{
					$result['FILE_NAME'] = $step;
					$result['CONTENT'] = $data;
				}
			}

			if (static::isCrmModuleIncluded() && $step === 0)
			{
				$result['FILE_NAME'] = $step;
				$result['CONTENT']['CRM_DYNAMIC_TYPES_INFO'] =
					static::exportCrmDynamicTypesInfo($automatedSolutionModeParams)
				;
			}
		}

		return $result;
	}

	private static function clearScript($option)
	{
		$result = [
			'NEXT' => false,
			'OWNER_DELETE' => [],
		];

		if (!$option['CLEAR_FULL'])
		{
			return $result;
		}

		$automatedSolutionModeParams = static::getAutomatedSolutionModeParams($option);

		$res = ScriptTable::getList(
			[
				'order' => [
					'ID' => 'ASC',
				],
				'filter' => [
					'>ID' => (int)$option['NEXT'],
					'=MODULE_ID' => static::$accessModules,
				],
				'limit' => 1,
				'select' => ['ID', 'DOCUMENT_TYPE'],
			]
		);

		while ($item = $res->Fetch())
		{
			$result['NEXT'] = $item['ID'];
			$documentType =
				(
					isset($item['DOCUMENT_TYPE'])
					&& is_string($item['DOCUMENT_TYPE'])
					&& $item['DOCUMENT_TYPE'] !== ''
				)
					? $item['DOCUMENT_TYPE']
					: ''
			;

			if (
				!static::checkDynamicTypeExportConditions(
					array_merge(
						$automatedSolutionModeParams,
						static::getDynamicTypeCheckParamsByDocumentType($documentType)
					)
				)
			)
			{
				continue;
			}

			$deletionResult = Script\Manager::deleteScript($item['ID']);

			if ($deletionResult->isSuccess())
			{
				$result['OWNER_DELETE'][] = [
					'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_BIZPROC_SCRIPT,
					'ENTITY' => $item['ID'],
				];
			}
		}

		return $result;
	}
	
	private static function importScript($importData, int $userId, int $appId)
	{
		$result = [];

		if (!isset($importData['CONTENT']['DATA']))
		{
			return false;
		}
		$dynamicTypesInfo = static::getDynamicTypesInfoForImport($importData, $result);
		$item = $importData['CONTENT']['DATA'];
		if (
			in_array($item['MODULE_ID'], static::$accessModules)
			&& Loader::includeModule($item['MODULE_ID'])
			&& class_exists($item['ENTITY'])
		)
		{
			if (is_subclass_of($item['ENTITY'], '\\IBPWorkflowDocument'))
			{
				if (
					isset($importData['RATIO']['CRM_STATUS'])
					&& isset($item['WORKFLOW_TEMPLATE'])
					&& is_array($item['WORKFLOW_TEMPLATE'])
				)
				{
					$oldDynamicEntityTypeId = static::getDynamicEntityTypeIdByDocumentType($item['DOCUMENT_TYPE']);
					$isDynamicType = ($oldDynamicEntityTypeId > 0);

					$automatedSolutionModeParams = static::getAutomatedSolutionModeImportParams($importData);
					if (!$isDynamicType && $automatedSolutionModeParams['isAutomatedSolutionMode'])
					{
						return $result;
					}

					if ($isDynamicType)
					{
						$crmHelper = new Helper();
						$isSetRatio = (isset($importData['RATIO']) && is_array($importData['RATIO']));
						$newDynamicEntityTypeId = $crmHelper->getDynamicEntityTypeIdByOldEntityTypeId(
							$oldDynamicEntityTypeId,
							$isSetRatio ? $importData['RATIO'] : []
						);
						if (
							!empty($dynamicTypesInfo)
							&& static::checkDynamicTypeImportConditions($newDynamicEntityTypeId, $importData)
						)
						{
							$isSetRatio = (isset($importData['RATIO']) && is_array($importData['RATIO']));
							$replacementLists = static::getDynamicTypeReplacementLists(
								$dynamicTypesInfo,
								$isSetRatio ? $importData['RATIO'] : []
							);
							$item['DOCUMENT_TYPE'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['DOCUMENT_TYPE'],
									$importData['RATIO'],
									$replacementLists
								)
							;
							$item['WORKFLOW_TEMPLATE'] =
								static::changeDynamicTypeIdentifiers(
									$oldDynamicEntityTypeId,
									$item['WORKFLOW_TEMPLATE'],
									$importData['RATIO'],
									$replacementLists
								)
							;
						}
					}
					elseif ($item['DOCUMENT_TYPE'] === 'DEAL')
					{
						$item['WORKFLOW_TEMPLATE'] = static::changeDealCategory(
							$item['WORKFLOW_TEMPLATE'],
							$importData['RATIO']['CRM_STATUS']
						);
					}
				}

				if ($appId > 0)
				{
					$item['ORIGINATOR_ID'] = 'REST_APP';
					$item['ORIGIN_ID'] = $appId;
				}

				$importResult = Script\Manager::importScript($item, $userId);

				if ($importResult->isSuccess())
				{
					$result['OWNER'] = [
						'ENTITY_TYPE' => self::OWNER_ENTITY_TYPE_BIZPROC_SCRIPT,
						'ENTITY' => $importResult->getId(),
					];
				}
				else
				{
					$result['ERROR_ACTION'] = $result['ERROR_MESSAGES'] = current($importResult->getErrorMessages());
				}
			}
		}

		return $result;
	}
	//end region script

	private static function changeDynamicTypeIdentifiers(
		int $oldDynamicEntityTypeId,
		array|string $data,
		array $ratio,
		array $replacementLists
	): array|string
	{
		if (!static::isCrmModuleIncluded())
		{
			return $data;
		}

		if (
			is_string($data)
			&& isset($replacementLists['from'])
			&& is_array($replacementLists['from'])
			&& !empty($replacementLists['from'])
			&& isset($replacementLists['to'])
			&& is_array($replacementLists['to'])
			&& !empty($replacementLists['to'])
		)
		{
			$replaceMarkers = [];
			for ($i = 0; $i < count($replacementLists['from']); $i++)
			{
				$replaceMarkers[] = "_{<-rm[$i]->}_";
			}
			$data = str_replace($replacementLists['from'], $replaceMarkers, $data);
			$data = str_replace($replaceMarkers, $replacementLists['to'], $data);
		}
		elseif (is_array($data))
		{
			$crmHelper = new Helper();

			if (
				isset($data['field'])
				&& $data['field'] === 'CATEGORY_ID'
				&& $data['value'] > 0
			)
			{
				$newCategoryId = $crmHelper->getNewDynamicTypeCategoryIdByRatio(
					$oldDynamicEntityTypeId,
					(int)$data['value'],
					$ratio
				);
				if ($newCategoryId > 0)
				{
					$data['value'] = $newCategoryId;
				}
			}

			if (
				isset($data['DynamicTypeId'])
				&& $data['DynamicTypeId'] > 0
			)
			{
				$oldDynamicEntityTypeId = (int)$data['DynamicTypeId'];
				$newEntityTypeIdRatioKey = CCrmOwnerType::DynamicTypePrefixName . $oldDynamicEntityTypeId;
				if (isset($ratio['CRM_DYNAMIC_TYPES'][$newEntityTypeIdRatioKey]))
				{
					$newDynamicEntityTypeId = (int)$ratio['CRM_DYNAMIC_TYPES'][$newEntityTypeIdRatioKey];
					$data['DynamicTypeId'] = $newDynamicEntityTypeId;
					if (isset($data['DynamicEntitiesFields']) && is_array($data['DynamicEntitiesFields']))
					{
						foreach ($data['DynamicEntitiesFields'] as $oldFieldKey => $value)
						{
							$oldFieldPrefix = "{$oldDynamicEntityTypeId}_";
							$oldFieldPrefixLength = strlen($oldFieldPrefix);
							$fieldName = substr($oldFieldKey, $oldFieldPrefixLength);
							if ($oldFieldPrefix === substr($oldFieldKey, 0, $oldFieldPrefixLength))
							{
								$newFieldKey = "{$newDynamicEntityTypeId}_$fieldName";
								if ($fieldName === 'CATEGORY_ID' && $value > 0)
								{
									$oldCategoryId = (int)$value;
									$newCategoryRatioKey = "DT{$oldDynamicEntityTypeId}_$oldCategoryId";
									if (
										isset($ratio['CRM_STATUS'][$newCategoryRatioKey])
										&& $ratio['CRM_STATUS'][$newCategoryRatioKey] > 0
									)
									{
										$value = (int)$ratio['CRM_STATUS'][$newCategoryRatioKey];
									}
								}
								$data['DynamicEntitiesFields'][$newFieldKey] = $value;
								unset($data['DynamicEntitiesFields'][$oldFieldKey]);
							}
						}
					}
				}
			}

			foreach ($data as $key => $value)
			{
				$newKey = static::changeDynamicTypeIdentifiers(
					$oldDynamicEntityTypeId,
					$key,
					$ratio,
					$replacementLists
				);
				if ($newKey !== $key)
				{
					unset($data[$key]);
				}

				if ($newKey === 'CATEGORY_ID')
				{
					if (is_array($value))
					{
						if (isset($value['Options']) && is_array($value['Options']))
						{
							$data[$newKey]['Options'] = [];
							foreach ($value['Options'] as $categoryId => $title)
							{
								$newCategoryId = $crmHelper->getNewDynamicTypeCategoryIdByRatio(
									$oldDynamicEntityTypeId,
									(int)$categoryId,
									$ratio
								);
								if ($newCategoryId > 0)
								{
									$data[$newKey]['Options'][$newCategoryId] = $title;
								}
							}
						}
						else
						{
							$data[$newKey] = static::changeDynamicTypeIdentifiers(
								$oldDynamicEntityTypeId,
								$value,
								$ratio,
								$replacementLists
							);
						}
					}
					elseif (is_string($value))
					{
						$newCategoryId = $crmHelper->getNewDynamicTypeCategoryIdByRatio(
							$oldDynamicEntityTypeId,
							(int)$value,
							$ratio
						);
						if ($newCategoryId > 0)
						{
							$data[$newKey] = $newCategoryId;
						}
						else
						{
							$data[$newKey] = static::changeDynamicTypeIdentifiers(
								$oldDynamicEntityTypeId,
								$value,
								$ratio,
								$replacementLists
							);
						}
					}
					else
					{
						$data[$newKey] = $value;
					}
				}
				elseif (
					($newKey === 'CategoryId' || $newKey === 'category_id')
					&& $value > 0
				)
				{
					$newCategoryId = $crmHelper->getNewDynamicTypeCategoryIdByRatio(
						$oldDynamicEntityTypeId,
						(int)$value,
						$ratio
					);
					if ($newCategoryId > 0)
					{
						$data[$newKey] = $newCategoryId;
					}
				}
				else
				{
					if (is_string($value) || is_array($value))
					{
						$data[$newKey] = static::changeDynamicTypeIdentifiers(
							$oldDynamicEntityTypeId,
							$value,
							$ratio,
							$replacementLists
						);
					}
					else
					{
						$data[$newKey] = $value;
					}
				}
			}
		}

		return $data;
	}

	private static function changeDealCategory($data, $ratio)
	{
		if (!empty($ratio))
		{
			$ratioRegEx = [];
			$ratioReplace = [];
			foreach ($ratio as $oldId => $newId)
			{
				$ratioRegEx[] = '/^C' . $oldId . ':/';
				$ratioReplace[] = 'C' . $newId . ':';
			}
			if (!empty($ratioRegEx))
			{
				$data = static::changeDealCategoryAction($data, $ratioRegEx, $ratioReplace, $ratio);
			}
		}

		return $data;
	}

	private static function changeDealCategoryAction($data, $ratioRegEx, $ratioReplace, $ratio)
	{
		if (is_string($data))
		{
			$data = preg_replace($ratioRegEx, $ratioReplace, $data);
		}
		elseif (is_array($data))
		{
			if (
				isset($data['field'])
				&& $data['field'] == 'CATEGORY_ID'
				&& $data['value'] > 0
				&& $ratio[$data['value']] > 0
			)
			{
				$data['value'] = $ratio[$data['value']];
			}

			foreach ($data as $key => $value)
			{
				$newKey = static::changeDealCategoryAction($key, $ratioRegEx, $ratioReplace, $ratio);
				if ($newKey != $key)
				{
					unset($data[$key]);
				}

				if ($newKey == 'CATEGORY_ID')
				{
					if (is_array($value))
					{
						if (isset($value['Options']) && is_array($value['Options']))
						{
							$data[$newKey]['Options'] = [];
							foreach ($value['Options'] as $dealId => $title)
							{
								if (isset($ratio[$dealId]))
								{
									$data[$newKey]['Options'][$ratio[$dealId]] = $title;
								}
							}
						}
						else
						{
							$data[$newKey] = static::changeDealCategoryAction(
								$value,
								$ratioRegEx,
								$ratioReplace,
								$ratio
							);
						}
					}
					elseif (is_string($value) && isset($ratio[$value]))
					{
						$data[$newKey] = $ratio[$value];
					}
					else
					{
						$data[$newKey] = static::changeDealCategoryAction(
							$value,
							$ratioRegEx,
							$ratioReplace,
							$ratio
						);
					}
				}
				elseif ($newKey == 'CategoryId' && intVal($value) > 0 && !empty($ratio[$value]))
				{
					$data[$newKey] = $ratio[$value];
				}
				else
				{
					$data[$newKey] = static::changeDealCategoryAction(
						$value,
						$ratioRegEx,
						$ratioReplace,
						$ratio
					);
				}
			}
		}

		return $data;
	}
}
