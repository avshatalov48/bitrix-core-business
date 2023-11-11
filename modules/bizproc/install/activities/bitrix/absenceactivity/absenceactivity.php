<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

class CBPAbsenceActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'AbsenceUser' => '',
			'AbsenceName' => '',
			'AbsenceDesrc' => '',
			'AbsenceState' => '',
			'AbsenceFinishState' => '',
			'AbsenceType' => '',
			'AbsenceFrom' => '',
			'AbsenceTo' => '',
			'AbsenceSiteId' => '',
		];
	}

	public function execute()
	{
		if (!CModule::IncludeModule('intranet'))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		/* TODO: hotfix, should be fixed late. SiteId should be the ordinary parameter */
		$sss = $this->AbsenceSiteId;
		if (empty($sss))
		{
			$sss = false;
		}

		$absenceIblockId = COption::GetOptionInt('intranet', 'iblock_absence', 0, $sss);
		if ($absenceIblockId <= 0)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $this->workflow->GetService('DocumentService');

		$arAbsenceUserTmp = $this->AbsenceUser;
		$arAbsenceUser = CBPHelper::ExtractUsers($arAbsenceUserTmp, $documentId, false);

		$arAbsenceTypes = [];
		$dbTypeRes = CIBlockPropertyEnum::GetList(
			['SORT' => 'ASC', 'VALUE' => 'ASC'],
			['IBLOCK_ID' => $absenceIblockId, 'PROPERTY_ID' => 'ABSENCE_TYPE']
		);
		while ($arTypeValue = $dbTypeRes->GetNext())
		{
			$arAbsenceTypes[$arTypeValue['XML_ID']] = $arTypeValue['ID'];
		}

		$name = CBPHelper::stringify($this->AbsenceName);
		$absenceDescription = CBPHelper::stringify($this->AbsenceDesrc);

		$activeFrom = current(CBPHelper::flatten($this->AbsenceFrom));
		$activeTo = current(CBPHelper::flatten($this->AbsenceTo));
		$enableTimeZone = false;

		//if $activeFrom and $activeTo without Time, turn off TimeZone
		if (mb_strlen($activeFrom) <= 10 && mb_strlen($activeTo) <= 10 && CTimeZone::Enabled())
		{
			CTimeZone::Disable();
			$enableTimeZone = true;
		}

		foreach ($arAbsenceUser as $absenceUser)
		{
			$arFields = [
				'ACTIVE' => 'Y',
				'IBLOCK_ID' => $absenceIblockId,
				'ACTIVE_FROM' => $activeFrom,
				'ACTIVE_TO' => $activeTo,
				'NAME' => $name,
				'PREVIEW_TEXT' => $absenceDescription,
				'PREVIEW_TEXT_TYPE' => 'text',
				'PROPERTY_VALUES' => [
					'USER' => $absenceUser,
					'STATE' => $this->AbsenceState,
					'FINISH_STATE' => $this->AbsenceFinishState,
					'ABSENCE_TYPE' => $arAbsenceTypes[$this->AbsenceType],
				],
			];

			$el = new CIBlockElement();
			$el->Add($arFields);
		}

		if ($enableTimeZone)
		{
			CTimeZone::Enable();
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (!array_key_exists('AbsenceUser', $arTestProperties) || empty($arTestProperties['AbsenceUser']))
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'AbsenceUser',
				'message' => Loc::getMessage('BPSNMA_EMPTY_ABSENCEUSER'),
			];
		}
		if (!array_key_exists('AbsenceName', $arTestProperties) || empty($arTestProperties['AbsenceName']))
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'AbsenceName',
				'message' => Loc::getMessage('BPSNMA_EMPTY_ABSENCENAME'),
			];
		}
		if (!array_key_exists('AbsenceFrom', $arTestProperties) || $arTestProperties['AbsenceFrom'] == '')
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'AbsenceFrom',
				'message' => Loc::getMessage('BPSNMA_EMPTY_ABSENCEFROM'),
			];
		}
		if (!array_key_exists('AbsenceTo', $arTestProperties) || $arTestProperties['AbsenceTo'] == '')
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'AbsenceTo',
				'message' => Loc::getMessage('BPSNMA_EMPTY_ABSENCETO'),
			];
		}

		if (!$user || !$user->isAdmin())
		{
			$absenceIblockId = COption::GetOptionInt('intranet', 'iblock_absence', 0);
			$iblockPerm = CIBlock::GetPermission($absenceIblockId);
			if ($iblockPerm < 'W')
			{
				$arErrors[] = ['code' => 'perm', 'message' => Loc::getMessage('BPAA2_NO_PERMS')];
			}
		}

		return array_merge($arErrors, parent::validateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = '',
		$form = null,
		$siteId = ''
	)
	{
		$runtime = CBPRuntime::getRuntime();

		$arMap = [
			'AbsenceUser' => 'absence_user',
			'AbsenceName' => 'absence_name',
			'AbsenceDesrc' => 'absence_desrc',
			'AbsenceFrom' => 'absence_from',
			'AbsenceTo' => 'absence_to',
			'AbsenceState' => 'absence_state',
			'AbsenceFinishState' => 'absence_finish_state',
			'AbsenceType' => 'absence_type',
		];

		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity['Properties']))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity['Properties']))
					{
						if ($k == 'AbsenceUser')
						{
							$arCurrentValues[$arMap[$k]] = CBPHelper::usersArrayToString(
								$arCurrentActivity['Properties'][$k],
								$arWorkflowTemplate,
								$documentType
							);
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity['Properties'][$k];
						}
					}
					else
					{
						$arCurrentValues[$arMap[$k]] = '';
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
				{
					$arCurrentValues[$arMap[$k]] = '';
				}
			}
		}

		$absenceIblockId = COption::GetOptionInt('intranet', 'iblock_absence', 0, $siteId);
		$arAbsenceTypes = [];
		$dbTypeRes = CIBlockPropertyEnum::GetList(
			['SORT' => 'ASC', 'VALUE' => 'ASC'],
			['IBLOCK_ID' => $absenceIblockId, 'PROPERTY_ID' => 'ABSENCE_TYPE']
		);
		while ($arTypeValue = $dbTypeRes->GetNext())
		{
			$arAbsenceTypes[$arTypeValue['XML_ID']] = $arTypeValue['VALUE'];
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'formName' => $formName,
				'arAbsenceTypes' => $arAbsenceTypes,
				'absenceSiteId' => $siteId,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];

		$runtime = CBPRuntime::getRuntime();

		$arMap = [
			'absence_user' => 'AbsenceUser',
			'absence_name' => 'AbsenceName',
			'absence_desrc' => 'AbsenceDesrc',
			'absence_from' => 'AbsenceFrom',
			'absence_to' => 'AbsenceTo',
			'absence_state' => 'AbsenceState',
			'absence_finish_state' => 'AbsenceFinishState',
			'absence_type' => 'AbsenceType',
			'absence_site_id' => 'AbsenceSiteId',
		];

		$arProperties = [];
		foreach ($arMap as $key => $value)
		{
			if ($key == 'absence_user')
			{
				continue;
			}
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties['AbsenceUser'] = CBPHelper::usersStringToArray(
			$arCurrentValues['absence_user'],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arErrors = self::validateProperties(
			$arProperties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}
}
