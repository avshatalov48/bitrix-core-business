<?php
namespace Bitrix\Main\Filter;

use Bitrix\Main\UserField\Types\EnumType;

class EntityUFDataProvider extends DataProvider
{
	/** @var EntitySettings|null */
	protected $settings = null;
	protected $ufReserved = [];

	function __construct(EntitySettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Get Settings
	 * @return EntitySettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Get user field entity ID.
	 * @return string
	 */
	public function getUserFieldEntityID()
	{
		return $this->settings->getUserFieldEntityID();
	}

	/**
	 * Get custom fields defined for entity
	 * @return array
	 */
	protected function getUserFields()
	{
		global $USER_FIELD_MANAGER;

		static $result = [];

		$entityId = $this->getUserFieldEntityID();
		if (empty($entityId))
		{
			return [];
		}

		if (!isset($result[$entityId]))
		{
			$result[$entityId] = $USER_FIELD_MANAGER->getUserFields($entityId, 0, LANGUAGE_ID, false);
			$result[$entityId] = $this->postFilterFields($result[$entityId]);
		}
		return $result[$entityId];
	}

	/**
	 * Prepare field list.
	 * @return Field[]
	 */
	public function prepareFields()
	{
		$result = [];
		foreach($this->getUserFields() as $fieldName => $userField)
		{
			if (
				$userField['SHOW_FILTER'] === 'N'
				|| $userField['USER_TYPE']['BASE_TYPE'] === 'file'
			)
			{
				continue;
			}

			$typeID = $userField['USER_TYPE']['USER_TYPE_ID'];
			//$isMultiple = isset($userField['MULTIPLE']) && $userField['MULTIPLE'] === 'Y';

			$fieldLabel = $userField['LIST_FILTER_LABEL'] ?? '';
			if($fieldLabel === '')
			{
				if(isset($userField['LIST_COLUMN_LABEL']))
				{
					$fieldLabel = $userField['LIST_COLUMN_LABEL'];
				}
				elseif(isset($userField['EDIT_FORM_LABEL']))
				{
					$fieldLabel = $userField['EDIT_FORM_LABEL'];
				}
			}
			if ($fieldLabel == '')
			{
				$fieldLabel = $fieldName;
			}

			if($typeID === 'employee')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'entity_selector',
						'name' => $fieldLabel,
						'partial' => true,
						'subtype' => $typeID,
					]
				);
			}
			elseif($typeID === 'string' || $typeID === 'url' || $typeID === 'address' || $typeID === 'money')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'text',
						'name' => $fieldLabel,
						'data' => [
							'additionalFilter' => [
								'isEmpty',
								'hasAnyValue',
							],
						],
						'subtype' => $typeID,
					]
				);
				continue;
			}
			elseif($typeID === 'integer' || $typeID === 'double')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'number',
						'name' => $fieldLabel,
						'data' => [
							'additionalFilter' => [
								'isEmpty',
								'hasAnyValue',
							],
						],
						'subtype' => $typeID,
					]
				);
				continue;
			}
			elseif($typeID === 'boolean')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'checkbox',
						'name' => $fieldLabel,
						'data' => ['valueType' => 'numeric'],
						'subtype' => $typeID,
					]
				);
			}
			elseif($typeID === 'datetime' || $typeID === 'date')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'date',
						'name' => $fieldLabel,
						'data' =>
						[
							'time' => $typeID === 'datetime',
							'additionalFilter' => [
								'isEmpty',
								'hasAnyValue',
							],
						],
						'subtype' => $typeID,
					]
				);
			}
			elseif(
				($typeID === 'enumeration' || $typeID === 'crm_status')
				&& isset($userField['SETTINGS']['DISPLAY'])
				&& $userField['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_DIALOG
			)
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'entity_selector',
						'name' => $fieldLabel,
						'partial' => true,
						'subtype' => $typeID,
					]
				);
			}
			elseif(
				$typeID === 'enumeration'
				|| $typeID === 'crm_status'
				|| $typeID === 'iblock_element'
				|| $typeID === 'iblock_section'
			)
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'list',
						'name' => $fieldLabel,
						'partial' => true,
						'subtype' => $typeID,
					]
				);
			}
			elseif($typeID === 'crm')
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'dest_selector',
						'name' => $fieldLabel,
						'partial' => true,
						'subtype' => $typeID,
					]
				);
			}
			else
			{
				$result[$fieldName] = $this->createField(
					$fieldName,
					[
						'type' => 'custom',
						'name' => $fieldLabel,
						'data' => ['value' => ''],
						'subtype' => $typeID,
					]
				);
			}
		}

		return $result;
	}

	/**
	 * Prepare complete field data for specified field.
	 * @param string $fieldID Field ID.
	 * @return array|null
	 */
	public function prepareFieldData($fieldID)
	{
		$userFields = $this->getUserFields();
		if(!isset($userFields[$fieldID]))
		{
			return null;
		}

		$userField = $userFields[$fieldID];
		$typeID = $userField['USER_TYPE']['USER_TYPE_ID'];
		$isMultiple = isset($userField['MULTIPLE']) && $userField['MULTIPLE'] === 'Y';
		$ID = $userField['ID'];
		if ($typeID === 'employee')
		{
			return [
				'params' => [
					'multiple' => $isMultiple ? 'Y' : 'N',
					'dialogOptions' => [
						'height' => 200,
						'context' => 'CRM_UF_FILTER_' . $fieldID,
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'inviteEmployeeLink' => false,
									'intranetUsersOnly' => true,
								]
							],
						],
						'showAvatars' => true,
						'dropdownMode' => false,
					],
				],
			];
		}
		elseif($typeID === 'enumeration')
		{
			$entity = new \CUserFieldEnum();
			$dbResult = $entity->GetList(array('SORT' => 'ASC'), array('USER_FIELD_ID' => $ID));

			$items = array();
			if(is_object($dbResult))
			{
				while($ary = $dbResult->Fetch())
				{
					$items[$ary['ID']] = $ary['VALUE'];
				}
			}

			if ($userField['SETTINGS']['DISPLAY'] === EnumType::DISPLAY_DIALOG)
			{
				$dialogItems = [];
				foreach ($items as $itemId => $itemTitle)
				{
					$dialogItems[] = [
						'id' => $itemId,
						'entityId' =>  $userField['FIELD_NAME'],
						'title' => $itemTitle,
						'tabs' => $userField['FIELD_NAME'],
					];
				}
				return [
					'params' => [
						'multiple' => 'Y',
						'dialogOptions' => [
							'items' => $dialogItems,
							'height' => 200,
							'dropdownMode' => true,
							'compactView' => true,
							'tabs' => [
								[
									'id' => $userField['FIELD_NAME'],
									'title' => $userField['EDIT_FORM_LABEL'],
								],
							],
						],
					],
				];
			}

			return array(
				'params' => array('multiple' => 'Y'),
				'items' => $items
			);
		}
		elseif($typeID === 'iblock_element')
		{
			$entity = new \CUserTypeIBlockElement();
			$dbResult = $entity->GetList($userField);

			$items = array();
			if(is_object($dbResult))
			{
				$count = 0;
				while($ary = $dbResult->Fetch())
				{
					$items[$ary['ID']] = $ary['NAME'];

					if(++$count > 500)
					{
						break;
					}
				}
			}

			return array(
				'params' => array('multiple' => 'Y'),
				'items' => $items
			);
		}
		elseif($typeID === 'iblock_section')
		{
			$entity = new \CUserTypeIBlockSection();
			$dbResult = $entity->GetList($userField);

			$items = array();
			if(is_object($dbResult))
			{
				$count = 0;
				while($ary = $dbResult->Fetch())
				{
					$items[$ary['ID']] = isset($ary['DEPTH_LEVEL']) && $ary['DEPTH_LEVEL']  > 1
						? str_repeat('. ', ($ary['DEPTH_LEVEL'] - 1)).$ary['NAME'] : $ary['NAME'];

					if(++$count > 500)
					{
						break;
					}
				}
			}

			return array(
				'params' => array('multiple' => 'Y'),
				'items' => $items
			);
		}
		elseif($typeID === 'crm')
		{
			$settings = isset($userField['SETTINGS']) && is_array($userField['SETTINGS'])
				? $userField['SETTINGS'] : array();

			$entityTypeNames = array();
			$supportedEntityTypeNames = array(
				\CCrmOwnerType::LeadName,
				\CCrmOwnerType::DealName,
				\CCrmOwnerType::ContactName,
				\CCrmOwnerType::CompanyName
			);
			foreach($supportedEntityTypeNames as $entityTypeName)
			{
				if(isset($settings[$entityTypeName]) && $settings[$entityTypeName] === 'Y')
				{
					$entityTypeNames[] = $entityTypeName;
				}
			}

			$destSelectorParams = array(
				'apiVersion' => 3,
				'context' => 'CRM_UF_FILTER_ENTITY',
				'contextCode' => 'CRM',
				'useClientDatabase' => 'N',
				'enableAll' => 'N',
				'enableDepartments' => 'N',
				'enableUsers' => 'N',
				'enableSonetgroups' => 'N',
				'allowEmailInvitation' => 'N',
				'allowSearchEmailUsers' => 'N',
				'departmentSelectDisable' => 'Y',
				'enableCrm' => 'Y',
				'multiple' => ($isMultiple ? 'Y' : 'N'),
				'convertJson' => 'Y'
			);

			$entityTypeCounter = 0;
			foreach($entityTypeNames as $entityTypeName)
			{
				switch($entityTypeName)
				{
					case \CCrmOwnerType::LeadName:
						$destSelectorParams['enableCrmLeads'] = 'Y';
						$destSelectorParams['addTabCrmLeads'] = 'Y';
						$entityTypeCounter++;
						break;
					case \CCrmOwnerType::DealName:
						$destSelectorParams['enableCrmDeals'] = 'Y';
						$destSelectorParams['addTabCrmDeals'] = 'Y';
						$entityTypeCounter++;
						break;
					case \CCrmOwnerType::ContactName:
						$destSelectorParams['enableCrmContacts'] = 'Y';
						$destSelectorParams['addTabCrmContacts'] = 'Y';
						$entityTypeCounter++;
						break;
					case \CCrmOwnerType::CompanyName:
						$destSelectorParams['enableCrmCompanies'] = 'Y';
						$destSelectorParams['addTabCrmCompanies'] = 'Y';
						$entityTypeCounter++;
						break;
					default:
				}
			}
			if ($entityTypeCounter <= 1)
			{
				$destSelectorParams['addTabCrmLeads'] = 'N';
				$destSelectorParams['addTabCrmDeals'] = 'N';
				$destSelectorParams['addTabCrmContacts'] = 'N';
				$destSelectorParams['addTabCrmCompanies'] = 'N';
			}

			return array(
				'params' => $destSelectorParams
			);
		}
		elseif($typeID === 'crm_status')
		{
			$items = array();
			if(isset($userField['SETTINGS'])
				&& is_array($userField['SETTINGS'])
				&& isset($userField['SETTINGS']['ENTITY_TYPE'])
			)
			{
				$entityType = $userField['SETTINGS']['ENTITY_TYPE'];
				if($entityType !== '')
				{
					$items = \CCrmStatus::GetStatusList($entityType);
				}
			}

			return array(
				'params' => array('multiple' => 'Y'),
				'items' => $items
			);
		}
		return null;
	}

	/**
	 * Create filter field.
	 * @param string $fieldID Field ID.
	 * @param array|null $params Field parameters (optional).
	 * @return Field
	 */
	protected function createField($fieldID, array $params = null)
	{
		return new Field($this, $fieldID, $params);
	}

	public function getUfReserved()
	{
		return $this->ufReserved;
	}

	protected function postFilterFields(array $fields)
	{
		foreach ($this->getUfReserved() as $ufId)
		{
			if (isset($fields[$ufId]))
			{
				unset($fields[$ufId]);
			}
		}

		return $fields;
	}

}
