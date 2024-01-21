<?php
namespace Bitrix\Main\UserField;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;

class Dispatcher
{
	/**
	 * @var Dispatcher
	 */
	protected static $instance;

	protected $languageId = LANGUAGE_ID;

	protected $fieldList = array();
	protected $validateFieldList = array();
	protected $userFieldList = array();
	protected $storedFieldSignature = array();

	protected $result = array();

	/**
	 * @var IDisplay
	 */
	protected $view = null;

	/**
	 * @var SignatureManager
	 */
	protected $signatureManager;

	/**
	 * @var ErrorCollection
	 */
	protected $errorCollection;

	/**
	 * @var AssetCollector
	 */
	protected $assetCollectior;

	/**
	 * Singleton
	 *
	 * @return Dispatcher
	 */
	public static function instance()
	{
		if(!static::$instance)
		{
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct()
	{
		$this->errorCollection = new ErrorCollection();
		$this->assetCollectior = new AssetCollector();
	}

	/**
	 * Sets current language of fields data
	 *
	 * @param $languageId
	 */
	public function setLanguage($languageId)
	{
		$this->languageId = $languageId;
	}

	protected function validateFieldChangeRequest($fieldInfo)
	{
		if(!$this->checkFieldDescription($fieldInfo))
		{
			return false;
		}

		if(!SignatureHelperCreate::validateSignature($this->getSignatureManager(), $fieldInfo, $fieldInfo['SIGNATURE']))
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_SIGNATURE'), $fieldInfo['FIELD']);

			return false;
		}

		return true;
	}

	public function createField($fieldInfo)
	{
		if(!$this->validateFieldChangeRequest($fieldInfo))
		{
			return false;
		}

		$fieldId = $this->createNewField($fieldInfo);

		if($fieldId)
		{
			if($fieldInfo['USER_TYPE_ID'] === \CUserTypeEnum::USER_TYPE_ID)
			{
				$this->createNewFieldEnumValues($fieldId, $fieldInfo);
			}

			$addFieldInfo = array(
				'ENTITY_ID' => $fieldInfo['ENTITY_ID'],
				'FIELD' => $fieldInfo['FIELD'],
				'CONTEXT' => $fieldInfo['CONTEXT'] ?? null,
			);

			$addFieldInfo['SIGNATURE'] = $this->getSignature($addFieldInfo);

			$this->storeFieldSignature($addFieldInfo['ENTITY_ID'], $addFieldInfo['FIELD'], $addFieldInfo['SIGNATURE']);

			$this->addField($addFieldInfo);
		}

		return $fieldId > 0;
	}

	public function editField($fieldInfo)
	{
		if(!$this->validateFieldChangeRequest($fieldInfo))
		{
			return false;
		}

		$currentFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

		$fieldId = $this->updateField($fieldInfo);
		if($fieldId)
		{
			if($currentFieldInfo['USER_TYPE_ID'] === \CUserTypeEnum::USER_TYPE_ID)
			{
				$this->updateFieldEnumValues($fieldId, $fieldInfo);
			}

			$this->reloadUserFieldInfo($fieldInfo['ENTITY_ID']);

			$addFieldInfo = array(
				'ENTITY_ID' => $fieldInfo['ENTITY_ID'],
				'ENTITY_VALUE_ID' => $fieldInfo['ENTITY_VALUE_ID'],
				'FIELD' => $fieldInfo['FIELD'],
				'VALUE' => $fieldInfo['VALUE'],
				'CONTEXT' => $fieldInfo['CONTEXT'],
			);

			$addFieldInfo['SIGNATURE'] = $this->getSignature($addFieldInfo);

			$this->storeFieldSignature($addFieldInfo['ENTITY_ID'], $addFieldInfo['FIELD'], $addFieldInfo['SIGNATURE']);

			$this->addField($addFieldInfo);
		}

		return true;

	}

	public function deleteField($fieldInfo)
	{
		if(!$this->validateFieldChangeRequest($fieldInfo))
		{
			return false;
		}

		$userFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

		if(!$userFieldInfo)
		{
			$this->addError('Field not found', $fieldInfo['FIELD']);
		}
		else
		{
			$userTypeEntity = new \CUserTypeEntity();
			$deleteResult = $userTypeEntity->delete($userFieldInfo['ID']);

			if(!$deleteResult)
			{
				$this->getErrorFromApplicationException($fieldInfo);

				return false;
			}

			$this->reloadUserFieldInfo($fieldInfo['ENTITY_ID']);

			return true;
		}

		return false;
	}

	public function validateField($fieldInfo)
	{
		$userFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

		if(!$userFieldInfo)
		{
			$this->addError('Field not found', $fieldInfo['FIELD']);
		}
		else
		{
			if(!isset($this->validateFieldList[$fieldInfo['ENTITY_ID']]))
			{
				$this->validateFieldList[$fieldInfo['ENTITY_ID']] = array();
			}

			if(!isset($this->validateFieldList[$fieldInfo['ENTITY_ID']][$fieldInfo['ENTITY_VALUE_ID']]))
			{
				$this->validateFieldList[$fieldInfo['ENTITY_ID']][$fieldInfo['ENTITY_VALUE_ID']] = array();
			}

			$this->validateFieldList[$fieldInfo['ENTITY_ID']][$fieldInfo['ENTITY_VALUE_ID']][$fieldInfo['FIELD']] = $fieldInfo['VALUE'];
		}
	}

	protected function processValidate()
	{
		global $APPLICATION, $USER_FIELD_MANAGER;

		foreach($this->validateFieldList as $entityId => $entityFields)
		{
			foreach($entityFields as $entityValueId => $fieldList)
			{
				$validateResult = $USER_FIELD_MANAGER->CheckFields($entityId, $entityValueId, $fieldList);

				if(!$validateResult)
				{
					$e = $APPLICATION->GetException();
					/**
					 * @var \CAdminException $e
					 */
					$errorMessages = $e->GetMessages();

					foreach($errorMessages as $message)
					{
						$this->addError($message['text'], $message['id']);
					}
				}
			}
		}

		$this->result = array_merge($this->result, $this->getValidateErrorList());
		$this->clearErrorList();
	}

	protected function createNewField(array $fieldInfo)
	{
		$userTypeEntity = new \CUserTypeEntity();
		$fieldId = $userTypeEntity->add(array(
			'ENTITY_ID' => $fieldInfo['ENTITY_ID'],
			'FIELD_NAME' => $fieldInfo['FIELD'],
			'USER_TYPE_ID' => $fieldInfo['USER_TYPE_ID'],
			'MULTIPLE' => $fieldInfo['MULTIPLE'],
			'MANDATORY' => $fieldInfo['MANDATORY'],
			'SHOW_FILTER' => $fieldInfo['SHOW_FILTER'] ?? null,
			'SHOW_IN_LIST' => $fieldInfo['SHOW_IN_LIST'] ?? null,
			'EDIT_IN_LIST' => $fieldInfo['EDIT_IN_LIST'] ?? null,
			'IS_SEARCHABLE' => $fieldInfo['IS_SEARCHABLE'] ?? null,
			'SETTINGS' => $fieldInfo['SETTINGS'] ?? null,
			'EDIT_FORM_LABEL' => $this->checkLabel($fieldInfo['EDIT_FORM_LABEL'] ?? null),
			'LIST_COLUMN_LABEL' => $this->checkLabel($fieldInfo['LIST_COLUMN_LABEL'] ?? null),
			'LIST_FILTER_LABEL' => $this->checkLabel($fieldInfo['LIST_FILTER_LABEL'] ?? null),
			'ERROR_MESSAGE' => $this->checkLabel($fieldInfo['ERROR_MESSAGE'] ?? null),
			'HELP_MESSAGE' => $this->checkLabel($fieldInfo['HELP_MESSAGE'] ?? null),
			'CONTEXT_PARAMS' => $fieldInfo['CONTEXT_PARAMS'] ?? null,
		));

		if(!$fieldId)
		{
			$this->getErrorFromApplicationException($fieldInfo);
		}

		return $fieldId;
	}

	protected function updateField(array $fieldInfo)
	{
		$userFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

		if(is_array($userFieldInfo))
		{
			$updateField = array();

			if(array_key_exists('MANDATORY', $fieldInfo))
			{
				$updateField['MANDATORY'] = $fieldInfo['MANDATORY'];
			}

			if(array_key_exists('SHOW_FILTER', $fieldInfo))
			{
				$updateField['SHOW_FILTER'] = $fieldInfo['SHOW_FILTER'];
			}

			if(array_key_exists('SHOW_IN_LIST', $fieldInfo))
			{
				$updateField['SHOW_IN_LIST'] = $fieldInfo['SHOW_IN_LIST'];
			}

			if(array_key_exists('EDIT_IN_LIST', $fieldInfo))
			{
				$updateField['EDIT_IN_LIST'] = $fieldInfo['EDIT_IN_LIST'];
			}

			if(array_key_exists('IS_SEARCHABLE', $fieldInfo))
			{
				$updateField['IS_SEARCHABLE'] = $fieldInfo['IS_SEARCHABLE'];
			}

			if(array_key_exists('EDIT_FORM_LABEL', $fieldInfo))
			{
				$updateField['EDIT_FORM_LABEL'] = $this->checkLabel($fieldInfo['EDIT_FORM_LABEL']);
			}

			if(array_key_exists('LIST_COLUMN_LABEL', $fieldInfo))
			{
				$updateField['LIST_COLUMN_LABEL'] = $this->checkLabel($fieldInfo['LIST_COLUMN_LABEL']);
			}

			if(array_key_exists('LIST_FILTER_LABEL', $fieldInfo))
			{
				$updateField['LIST_FILTER_LABEL'] = $this->checkLabel($fieldInfo['LIST_FILTER_LABEL']);
			}

			if(array_key_exists('ERROR_MESSAGE', $fieldInfo))
			{
				$updateField['ERROR_MESSAGE'] = $this->checkLabel($fieldInfo['ERROR_MESSAGE']);
			}

			if(array_key_exists('HELP_MESSAGE', $fieldInfo))
			{
				$updateField['HELP_MESSAGE'] = $this->checkLabel($fieldInfo['HELP_MESSAGE']);
			}

			if(array_key_exists('SETTINGS', $fieldInfo) && is_array($fieldInfo['SETTINGS']))
			{
				$updateField['SETTINGS'] = array_merge($userFieldInfo['SETTINGS'], $fieldInfo['SETTINGS']);
			}

			if(!empty($updateField))
			{
				$userTypeEntity = new \CUserTypeEntity();
				$updateResult = $userTypeEntity->update($userFieldInfo['ID'], $updateField);

				if(!$updateResult)
				{
					$this->getErrorFromApplicationException($fieldInfo);
					return false;
				}
			}
		}

		return $userFieldInfo['ID'];
	}

	protected function createNewFieldEnumValues($fieldId, $fieldInfo)
	{
		if(array_key_exists('ENUM', $fieldInfo) && is_array($fieldInfo['ENUM']))
		{
			$enumValuesManager = new \CUserFieldEnum();

			$enum = array_values($fieldInfo['ENUM']);
			$enumValues = array();
			foreach($enum as $key => $valueDescription)
			{
				if(is_array($valueDescription))
				{
					$enumValues['n'.$key] = array(
						'XML_ID' => $valueDescription['XML_ID'],
						'VALUE' => $valueDescription['VALUE'],
						'DEF' => $valueDescription['DEF'] === 'Y' ? 'Y' : 'N',
						'SORT' => $valueDescription['SORT'],
					);
				}
			}
			$setEnumResult = $enumValuesManager->setEnumValues($fieldId, $enumValues);
			if(!$setEnumResult)
			{
				$this->getErrorFromApplicationException($fieldInfo);
			}
		}
	}

	protected function updateFieldEnumValues($fieldId, $fieldInfo)
	{
		if(
			array_key_exists('ENUM', $fieldInfo) && is_array($fieldInfo['ENUM']))
		{
			$currentFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

			$deletedEnum = array();
			$storedEnum = array();
			$updatedEnum = array();

			if(is_array($currentFieldInfo['ENUM']))
			{
				foreach($currentFieldInfo['ENUM'] as $enumItem)
				{
					$storedEnum[$enumItem['ID']] = $enumItem;
					$deletedEnum[$enumItem['ID']] = true;
				}
			}

			$countAdded = 0;
			foreach($fieldInfo['ENUM'] as $enumItem)
			{
				if(is_array($enumItem))
				{
					if(array_key_exists('ID', $enumItem))
					{
						if(empty($enumItem['XML_ID']))
						{
							$enumItem['XML_ID'] = $storedEnum[$enumItem['ID']]['XML_ID'];
						}

						unset($deletedEnum[$enumItem['ID']]);
					}
					$itemKey = $enumItem['ID'] > 0
						? $enumItem['ID']
						: 'n'.($countAdded++);

					$itemDescription = array(
						'VALUE' => $enumItem['VALUE'],
						'DEF' => $enumItem['DEF'] === 'Y' ? 'Y' : 'N',
						'SORT' => $enumItem['SORT'],
					);

					if($enumItem['XML_ID'] <> '')
					{
						$itemDescription['XML_ID'] = $enumItem['XML_ID'];
					}

					if(intval($enumItem['SORT']) > 0)
					{
						$itemDescription['SORT'] = $enumItem['SORT'];
					}

					$updatedEnum[$itemKey] = $itemDescription;
				}
			}

			foreach($deletedEnum as $deletedId => $t)
			{
				$updatedEnum[$deletedId] = array(
					'ID' => $deletedId,
					'DEL' => 'Y'
				);
			}

			$enumValuesManager = new \CUserFieldEnum();
			$setEnumResult = $enumValuesManager->setEnumValues($fieldId, $updatedEnum);

			if(!$setEnumResult)
			{
				$this->getErrorFromApplicationException($fieldInfo);
			}
		}
	}

	protected function getErrorFromApplicationException($fieldInfo)
	{
		global $APPLICATION;

		$ex = $APPLICATION->GetException();
		if($ex)
		{
			$this->addError($ex->GetString(), $fieldInfo['FIELD']);
		}
	}

	protected function checkLabel($label)
	{
		return is_array($label) ? $label : array($this->languageId => $label);
	}

	protected function storeFieldSignature($entityId, $field, $signature)
	{
		$this->storedFieldSignature[$entityId.'|'.$field] = $signature;
	}

	protected function getStoredFieldSignature($entityId, $field)
	{
		return $this->storedFieldSignature[$entityId.'|'.$field] ?? null;
	}

	/**
	 * Adds field to the processing list
	 *
	 * Input array format:
	 * array(
	 *   'ENTITY_ID' => field's entity code: USER, CRM_CONTACT, etc. Required
	 *   'FIELD' => field code. Required
	 *   'VALUE' => field value
	 *   'ENTITY_VALUE_ID' => field's item id, may be needed for enum type fields
	 *   'SIGNATURE' => field's data signature got from \Bitrix\Main\UserField\Dispatcher::getSignature. Required.
	 * )
	 *
	 * @param array $field
	 */
	public function addField($field)
	{
		$this->fieldList[] = $field;
	}

	/**
	 * Returns processing result
	 *
	 * array(
	 *   'FIELD' => array(
	 *     FIELD_NAME => array(
	 *       'FIELD' => array(field description)),
	 *       'HTML' => field_html,
	 *     ),
	 *   ),
	 *   'ERROR' => array(
	 *     list of processing error messages
	 *   ),
	 *   'ASSET' => array(
	 *     list of js,css,etc needed for field functioning
	 *   ),
	 * );
	 *
	 * @return array
	 */
	public function getResult()
	{
		$this->assetCollectior->startAssetCollection();

		if(!empty($this->validateFieldList))
		{
			$this->processValidate();
		}

		foreach($this->fieldList as $fieldInfo)
		{
			$this->processField($fieldInfo);
		}

		$result = array(
			'FIELD' => $this->result,
			'ERROR' => $this->getErrorList(),
			'ASSET' => $this->assetCollectior->getCollectedAssets(),
		);

		$this->reset();

		return $result;
	}

	/**
	 * Sets current view object
	 *
	 * @param IDisplay $view
	 */
	public function setView(IDisplay $view)
	{
		$this->view = $view;
	}

	/**
	 * Returns current view object
	 *
	 * @return IDisplay
	 * @throws ObjectNotFoundException
	 */
	public function getView()
	{
		if(!$this->view)
		{
			throw new ObjectNotFoundException(Loc::getMessage('MAIN_UF_DISPATCHER_EXCEPTION_VIEW'));
		}

		return $this->view;
	}

	/**
	 * @return SignatureManager
	 */
	public function getSignatureManager()
	{
		if(!$this->signatureManager)
		{
			$this->setDefaultSignatureManager();
		}

		return $this->signatureManager;
	}

	/**
	 * @param SignatureManager $signatureManager
	 */
	public function setSignatureManager(SignatureManager $signatureManager)
	{
		$this->signatureManager = $signatureManager;
	}

	/**
	 * Processes single field and returns its result
	 *
	 * @param array $fieldInfo
	 *
	 * @return bool
	 */
	protected function processField(array $fieldInfo)
	{
		if(!$this->checkFieldDescription($fieldInfo))
		{
			return false;
		}

		if(!SignatureHelper::validateSignature($this->getSignatureManager(), $fieldInfo, $fieldInfo['SIGNATURE']))
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_SIGNATURE'), $fieldInfo['FIELD']);

			return false;
		}

		$userFieldInfo = $this->getUserFieldInfo($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);

		if(!$userFieldInfo)
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_FIELD_NOT_FOUND'), $fieldInfo['FIELD']);

			return false;
		}

		if(isset($fieldInfo['ENTITY_VALUE_ID']))
		{
			$userFieldInfo['ENTITY_VALUE_ID'] = $fieldInfo['ENTITY_VALUE_ID'];
		}

		if(isset($fieldInfo['VALUE']))
		{
			$userFieldInfo['VALUE'] = $fieldInfo['VALUE'];
		}
		else
		{
			unset($userFieldInfo['VALUE']);
		}

		if(isset($fieldInfo['CONTEXT']))
		{
			$userFieldInfo['CONTEXT'] = $fieldInfo['CONTEXT'];
		}

		$view = $this->getView();

		$view->setField($userFieldInfo);

		if (!empty($fieldInfo['ADDITIONAL']) && is_array($fieldInfo['ADDITIONAL']))
		{
			foreach ($fieldInfo['ADDITIONAL'] as $paramName => $paramValue)
			{
				$view->setAdditionalParameter($paramName, $paramValue);
			}
		}

		$this->result[$fieldInfo['FIELD']] = [
			'HTML' => $view->display(),
			'FIELD' => $this->getResultFieldInfo($userFieldInfo),
		];

		$view->clear();
		unset($view);

		return true;
	}

	/**
	 * Prepare for the next use
	 */
	protected function reset()
	{
		$this->fieldList = array();
		$this->result = array();
		$this->errorCollection->clear();
		$this->view = null;
	}

	/**
	 * Adds error message to the current collection
	 *
	 * @param $message
	 * @param int $field
	 */
	protected function addError($message, $field = 0)
	{
		$this->errorCollection->setError(new Error($message, $field));
	}

	/**
	 * Returns currently collected non-fatal errors
	 *
	 * @return array
	 */
	protected function getErrorList()
	{
		$result = array();
		if(!$this->errorCollection->isEmpty())
		{
			/**
			 * @var Error $error
			 */
			foreach($this->errorCollection->toArray() as $error)
			{
				if($error->getCode())
				{
					$result[] = $error->getCode().': '.$error->getMessage();
				}
				else
				{
					$result[] = $error->getMessage();
				}
			}
		}

		return $result;
	}

	protected function getValidateErrorList()
	{
		$result = array();
		if(!$this->errorCollection->isEmpty())
		{
			/**
			 * @var Error $error
			 */
			foreach($this->errorCollection->toArray() as $error)
			{
				if($error->getCode())
				{
					$result[$error->getCode()] = $error->getMessage();
				}
				else
				{
					$result[] = $error->getMessage();
				}
			}
		}

		return $result;
	}

	protected function clearErrorList()
	{
		$this->errorCollection->clear();
	}

	/**
	 * Validates single field description
	 *
	 * @param array $fieldInfo
	 *
	 * @return bool
	 */
	protected function checkFieldDescription($fieldInfo)
	{
		if(!is_array($fieldInfo))
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_FORMAT'));
		}
		elseif(empty($fieldInfo['FIELD']))
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_FORMAT'));
		}
		elseif(empty($fieldInfo['ENTITY_ID']))
		{
			$this->addError(Loc::getMessage('MAIN_UF_DISPATCHER_ERROR_FORMAT', $fieldInfo['FIELD']));
		}
		else
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns userfield metadata
	 *
	 * @param string $entityId Entity ID
	 * @param string $field Field name
	 *
	 * @return array|null
	 */
	protected function getUserFieldInfo($entityId, $field)
	{
		global $USER_FIELD_MANAGER;

		if(!array_key_exists($entityId, $this->userFieldList))
		{
			$this->userFieldList[$entityId] = $USER_FIELD_MANAGER->GetUserFields($entityId, 0, $this->languageId);
		}

		if(
			isset($this->userFieldList[$entityId][$field])
			&& $this->userFieldList[$entityId][$field]['USER_TYPE_ID'] === \CUserTypeEnum::USER_TYPE_ID
			&& !isset($this->userFieldList[$entityId][$field]['ENUM'])
		)
		{
			$this->userFieldList[$entityId][$field]['ENUM'] = array();

			$enumValuesManager = new \CUserFieldEnum();
			$dbRes = $enumValuesManager->GetList(array(), array('USER_FIELD_ID' => $this->userFieldList[$entityId][$field]['ID']));

			while($enumValue = $dbRes->fetch())
			{
				$this->userFieldList[$entityId][$field]['ENUM'][] = array(
					'ID' => $enumValue['ID'],
					'VALUE' => $enumValue['VALUE'],
					'DEF' => $enumValue['DEF'],
					'SORT' => $enumValue['SORT'],
					'XML_ID' => $enumValue['XML_ID'],
				);
			}
		}

		return $this->userFieldList[$entityId][$field];
	}

	/**
	 * Reloads userfield metadata for entity
	 *
	 * @param string $entityId Entity ID
	 */
	protected function reloadUserFieldInfo($entityId)
	{
		unset($this->userFieldList[$entityId]);
	}

	/**
	 * Returns formatted field description for outpup
	 *
	 * @param array $userField Userfield metadata
	 *
	 * @return array
	 */
	protected function getResultFieldInfo(array $userField)
	{
		$fieldInfo = array(
			'ID' => $userField['ID'],
			'USER_TYPE_ID' => $userField['USER_TYPE_ID'],
			'ENTITY_ID' => $userField['ENTITY_ID'],
			'ENTITY_VALUE_ID' => $userField['ENTITY_VALUE_ID'] ?? 0,
			'MANDATORY' => $userField['MANDATORY'],
			'MULTIPLE' => $userField['MULTIPLE'],
			'FIELD' => $userField['FIELD_NAME'],
			'EDIT_FORM_LABEL' => $userField['EDIT_FORM_LABEL'],
			'LIST_COLUMN_LABEL' => $userField['LIST_COLUMN_LABEL'],
			'LIST_FILTER_LABEL' => $userField['LIST_FILTER_LABEL'],
			'HELP_MESSAGE' => $userField['HELP_MESSAGE'],
			'SETTINGS' => $userField['SETTINGS'],
			'SHOW_FILTER' => $userField['SHOW_FILTER'],
			'SHOW_IN_LIST' => $userField['SHOW_IN_LIST'],
			'SORT' => $userField['SORT'],
			'CONTEXT_PARAMS' => $userField['CONTEXT_PARAMS'] ?? [],
		);

		if($userField['USER_TYPE_ID'] === \CUserTypeEnum::USER_TYPE_ID && is_array($userField['ENUM']))
		{
			$fieldInfo['ENUM'] = $userField['ENUM'];
		}

		$storedSignature = $this->getStoredFieldSignature($fieldInfo['ENTITY_ID'], $fieldInfo['FIELD']);
		if($storedSignature)
		{
			$fieldInfo['SIGNATURE'] = $storedSignature;
		}

		return $fieldInfo;
	}

	protected function setDefaultSignatureManager()
	{
		$this->signatureManager = new SignatureManager();
	}

	public function getSignature(array $fieldParam)
	{
		return SignatureHelper::getSignature($this->getSignatureManager(), $fieldParam);
	}

	public function getCreateSignature(array $fieldParam)
	{
		return SignatureHelperCreate::getSignature($this->getSignatureManager(), $fieldParam);
	}
}
