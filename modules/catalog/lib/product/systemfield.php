<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\LanguageTable,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\ORM,
	Bitrix\Main\TaskTable,
	Bitrix\Main\Text,
	Bitrix\Catalog,
	Bitrix\Highloadblock as Highload;

final class SystemField
{
	public const CODE_MARKING_CODE_GROUP = 'MARKING_CODE_GROUP';

	private const FIELD_PREFIX = 'UF_';

	private const STORAGE_TABLE_NAME_PREFIX = 'b_hlsys_';

	private const STORAGE_NAME_PREFIX = 'PRODUCT_';

	private const FIELD_ID_PREFIX = 'product_';

	private const FIELD_NAME_PREFIX = 'PRODUCT_';

	/** @var bool */
	private static $highloadInclude = null;

	/** @var bool */
	private static $bitrix24Include = null;

	private static $storageList = [];

	private static $languages = [];

	private static $dictionary = [];

	private static $reverseDictionary = [];

	/** @var array */
	private static $currentFieldSet = null;

	/**
	 * @return string
	 */
	public static function execAgent()
	{
		$result = '';
		$createResult = self::create();
		if (!$createResult->isSuccess())
		{
			$result = '\Bitrix\Catalog\Product\SystemField::execAgent();';
		}
		return $result;
	}

	/**
	 * @return Main\Result
	 */
	public static function create()
	{
		$result = new Main\Result();

		self::$currentFieldSet = null;

		$fieldResult = self::createMarkingCodeGroup();
		if (!$fieldResult->isSuccess())
		{
			$result->addErrors($fieldResult->getErrors());
		}

		unset($fieldResult);

		return $result;
	}

	/**
	 * @return void
	 */
	public static function delete()
	{
		self::$currentFieldSet = null;
	}

	/**
	 * @return array
	 */
	public static function getFieldList()
	{
		if (self::$currentFieldSet === null)
		{
			self::$currentFieldSet = [];

			self::initStorageList();

			$userField = new \CUserTypeEntity();
			$iterator = $userField->GetList(
				[],
				[
					'ENTITY_ID' => Catalog\ProductTable::getUfId(),
					'FIELD_NAME' => self::$storageList[self::CODE_MARKING_CODE_GROUP]['UF_FIELD']
				]
			);
			$row = $iterator->Fetch();
			unset($iterator, $userField);
			if (!empty($row))
			{
				self::$currentFieldSet[self::CODE_MARKING_CODE_GROUP] = self::$storageList[self::CODE_MARKING_CODE_GROUP]['UF_FIELD'];
			}
			unset($row);

		}
		return self::$currentFieldSet;
	}

	/**
	 * @param array &$row
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function convertRow(array &$row)
	{
		if (!self::initHighloadBlock())
			return;
		if (!isset($row[self::CODE_MARKING_CODE_GROUP]))
			return;
		if (!isset(self::$dictionary[self::CODE_MARKING_CODE_GROUP]))
			self::$dictionary[self::CODE_MARKING_CODE_GROUP] = [];
		$id = (int)$row[self::CODE_MARKING_CODE_GROUP];
		if ($id <= 0)
		{
			$row[self::CODE_MARKING_CODE_GROUP] = null;
			return;
		}
		if (!isset(self::$dictionary[self::CODE_MARKING_CODE_GROUP][$id]))
		{
			self::$dictionary[self::CODE_MARKING_CODE_GROUP][$id] = false;
			$storage = self::$storageList[self::CODE_MARKING_CODE_GROUP];
			$entity = Highload\HighloadBlockTable::compileEntity($storage['NAME']);
			$entityDataClass = $entity->getDataClass();
			$iterator = $entityDataClass::getList([
				'select' => ['ID', 'UF_XML_ID'],
				'filter' => ['=ID' => $id]
			]);
			$data = $iterator->fetch();
			if (!empty($data) && isset($data['UF_XML_ID']))
			{
				self::$dictionary[self::CODE_MARKING_CODE_GROUP][$id] = $data['UF_XML_ID'];
			}
			unset($data, $iterator);
			unset($storage);
		}
		if (self::$dictionary[self::CODE_MARKING_CODE_GROUP][$id] !== false)
		{
			$row[self::CODE_MARKING_CODE_GROUP] = self::$dictionary[self::CODE_MARKING_CODE_GROUP][$id];
		}
		else
		{
			$row[self::CODE_MARKING_CODE_GROUP] = null;
		}
		unset($id);
	}

	public static function prepareRow(array &$row)
	{
		self::initStorageList();
		$fieldList = static::getFieldList();
		if (
			isset($fieldList[self::CODE_MARKING_CODE_GROUP])
			&& array_key_exists(self::CODE_MARKING_CODE_GROUP, $row)
		)
		{
			$value = null;
			if ($row[self::CODE_MARKING_CODE_GROUP] !== null && self::initHighloadBlock())
			{
				$xmlId = $row[self::CODE_MARKING_CODE_GROUP];
				if (!isset(self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP]))
				{
					self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP] = [];
				}
				if (!isset(self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP][$xmlId]))
				{
					self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP][$xmlId] = false;
					$storage = self::$storageList[self::CODE_MARKING_CODE_GROUP];
					$entity = Highload\HighloadBlockTable::compileEntity($storage['NAME']);
					$entityDataClass = $entity->getDataClass();
					$iterator = $entityDataClass::getList([
						'select' => ['ID', 'UF_XML_ID'],
						'filter' => ['=UF_XML_ID' => $xmlId]
					]);
					$data = $iterator->fetch();
					if (!empty($data) && isset($data['ID']))
					{
						self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP][$xmlId] = (int)$data['ID'];
					}
					unset($data, $iterator);
					unset($storage);
				}
				if (self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP][$xmlId] !== false)
				{
					$value = self::$reverseDictionary[self::CODE_MARKING_CODE_GROUP][$xmlId];
				}
			}
			$row[self::$storageList[self::CODE_MARKING_CODE_GROUP]['UF_FIELD']] = $value;

			unset($row[self::CODE_MARKING_CODE_GROUP]);
		}
	}

	/**
	 * @return array|null
	 */
	public static function getGroupActions()
	{
		$result = [];

		$row = self::getMarkingCodeGroupAction();
		if (!empty($row))
			$result[] = $row;

		return (!empty($result) ? $result : null);
	}

	/**
	 * @param string $fieldId
	 * @return array|null
	 */
	public static function getGroupActionRequest(string $fieldId)
	{
		$value = Main\Context::getCurrent()->getRequest()->get(self::getFormRowFieldName($fieldId));
		return ($value === null ? null : [$fieldId => $value]);
	}

	/**
	 * @param ORM\Event $event
	 * @return ORM\EventResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function handlerHighloadBlockBeforeDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult();

		if (self::allowedMarkingCodeGroup())
		{
			$primary = $event->getParameter('primary');
			if (!empty($primary))
			{
				$iterator = Highload\HighloadBlockTable::getList([
					'filter' => $primary
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (!empty($row))
				{
					if ($row['NAME'] == self::getStorageName(self::CODE_MARKING_CODE_GROUP))
					{
						$result->addError(new ORM\EntityError(
							Loc::getMessage(
								'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_CANNOT_DELETE_HIGHLOADBLOCK',
								['#NAME#' => Loc::getMessage('STORAGE_MARKING_CODE_GROUP_TITLE')]
							)
						));
					}
				}
				unset($row);
			}
			unset($primary);
		}

		return $result;
	}

	/**
	 * @param ORM\Event $event
	 * @return ORM\EventResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function handlerHighloadBlockBeforeUpdate(ORM\Event $event)
	{
		$result = new ORM\EventResult();

		if (self::allowedMarkingCodeGroup())
		{
			$primary = $event->getParameter('primary');
			$fields = $event->getParameter('fields');
			if (!empty($primary))
			{
				$iterator = Highload\HighloadBlockTable::getList([
					'filter' => $primary
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (!empty($row))
				{
					if ($row['NAME'] == self::getStorageName(self::CODE_MARKING_CODE_GROUP))
					{
						if (
							(isset($fields['NAME']) && $row['NAME'] != $fields['NAME'])
							|| (isset($fields['TABLE_NAME']) && $row['TABLE_NAME'] != $fields['TABLE_NAME'])
						)
						{
							$result->addError(new ORM\EntityError(
								Loc::getMessage(
									'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_CANNOT_UPDATE_HIGHLOADBLOCK',
									['#NAME#' => Loc::getMessage('STORAGE_MARKING_CODE_GROUP_TITLE')]
								)
							));
						}
					}
				}
				unset($row);
			}
			unset($primary);
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	private static function isExistHighloadBlock()
	{
		return Main\IO\Directory::isDirectoryExists(
			Main\Application::getDocumentRoot().'/bitrix/modules/highloadblock/'
		);
	}

	/**
	 * @return bool
	 */
	private static function checkHighloadBlock()
	{
		$result = self::initHighloadBlock();
		if (!$result)
			self::highloadBlockAlert();
		return $result;
	}

	/**
	 * @return bool
	 */
	private static function initHighloadBlock()
	{
		if (self::$highloadInclude === null)
			self::$highloadInclude = Loader::includeModule('highloadblock');
		return self::$highloadInclude;
	}

	/**
	 * @return void
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function highloadBlockAlert()
	{
		if (
			!self::initBitrix24()
			&& self::isExistHighloadBlock()
			&& !ModuleManager::isModuleInstalled('highloadblock')
		)
		{
			$iterator = \CAdminNotify::GetList([], ['MODULE_ID' => 'catalog', 'TAG' => 'HIGHLOADBLOCK_ABSENT']);
			while ($row = $iterator->Fetch())
			{
				\CAdminNotify::Delete($row['ID']);
			}
			unset($row, $iterator);

			$defaultLang = '';
			$messages = [];
			$iterator = LanguageTable::getList([
				'select' => ['ID', 'DEF'],
				'filter' => ['=ACTIVE' => 'Y']
			]);
			while ($row = $iterator->fetch())
			{
				if ($defaultLang == '')
					$defaultLang = $row['ID'];
				if ($row['DEF'] == 'Y')
					$defaultLang = $row['ID'];
				$languageId = $row['ID'];
				Loc::loadLanguageFile(
					__FILE__,
					$languageId
				);
				$messages[$languageId] = Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOADBLOCK_ABSENT',
					['#LANGUAGE_ID#' => $languageId],
					$languageId
				);
			}
			unset($languageId, $row, $iterator);

			if (!empty($messages))
			{
				\CAdminNotify::Add([
					'MODULE_ID' => 'catalog',
					'TAG' => 'HIGHLOADBLOCK_ABSENT',
					'ENABLE_CLOSE' => 'Y',
					'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
					'MESSAGE' => $messages[$defaultLang],
					'LANG' => $messages
				]);
			}
			unset($messages, $defaultLang);
		}
	}

	/**
	 * @return bool
	 */
	private static function initBitrix24()
	{
		if (self::$bitrix24Include === null)
			self::$bitrix24Include = Loader::includeModule('bitrix24');
		return self::$bitrix24Include;
	}

	/**
	 * @return void
	 */
	private static function initStorageList()
	{
		if (!empty(self::$storageList))
			return;
		self::$storageList[self::CODE_MARKING_CODE_GROUP] = [
			'TABLE_NAME' => self::getStorageTableName(self::CODE_MARKING_CODE_GROUP),
			'NAME' => self::getStorageName(self::CODE_MARKING_CODE_GROUP),
			'UF_FIELD' => self::FIELD_PREFIX.'PRODUCT_GROUP'
		];
	}

	/**
	 * @return array
	 */
	private static function getLanguages()
	{
		if (empty(self::$languages))
		{
			$iterator = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => ['=ACTIVE' => 'Y']
			]);
			while ($row = $iterator->fetch())
			{
				self::$languages[] = $row['ID'];
			}
			unset($row, $iterator);
		}
		return self::$languages;
	}

	/**
	 * @param string $code
	 * @return string
	 */
	private static function getStorageTableName(string $code)
	{
		return self::STORAGE_TABLE_NAME_PREFIX.''.mb_strtolower($code);
	}

	/**
	 * @param string $code
	 * @return string
	 */
	private static function getStorageName(string $code)
	{
		return Text\StringHelper::snake2camel(self::STORAGE_NAME_PREFIX.$code);
	}

	/**
	 * @param string $code
	 * @return array|null
	 */
	private static function getStorageDescription(string $code)
	{
		self::initStorageList();
		return (isset(self::$storageList[$code]) ? self::$storageList[$code] : null);
	}

	/**
	 * @param string $code
	 * @return array
	 */
	private static function getStorageLangTitles(string $code)
	{
		$result = [];

		$languages = self::getLanguages();
		if (!empty($languages))
		{
			$messageId = 'STORAGE_'.$code.'_TITLE';
			foreach ($languages as $languageId)
			{
				$message = (string)Loc::getMessage($messageId, null, $languageId);
				if ($message !== '')
				{
					$result[$languageId] = $message;
				}
			}
			unset($message, $languageId);
		}
		unset($languages);

		return $result;
	}

	/**
	 * @return Main\Result
	 */
	private static function createMarkingCodeGroup()
	{
		$result = new Main\Result();
		if (!self::allowedMarkingCodeGroup())
		{
			return $result;
		}

		$storage = self::getStorageDescription(self::CODE_MARKING_CODE_GROUP);
		$block = $storage;
		$block['TITLES'] = self::getStorageLangTitles(self::CODE_MARKING_CODE_GROUP);
		$block['RIGHTS'] = [
			'G1' => 'W',
			'G2' => 'R'
		];
		$block['FIELDS'] = self::getMarkingCodeGroupStorageFields();

		$stepResult = self::createHighloadBlock($block);
		if (!$stepResult->isSuccess())
		{
			$errors = $stepResult->getErrorMessages();
			$result->addError(new Main\Error(
				Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_MARKING_CODE_INTERNAL_ERROR',
					['#ERROR#' => implode('; ', $errors)]
				),
				self::CODE_MARKING_CODE_GROUP
			));
			unset($errors);
		}
		else
		{
			$data = $stepResult->getData();
			$storage['ID'] = $data['ID'];
			$storage['FIELDS'] = $data['FIELDS'];
			unset($data);
		}
		unset($stepResult);

		if ($result->isSuccess())
		{
			$stepResult = self::fillMarkingCodeGroups($storage);
			if (!$stepResult->isSuccess())
			{
				$errors = $stepResult->getErrorMessages();
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_MARKING_CODE_INTERNAL_ERROR',
						['#ERROR#' => implode('; ', $errors)]
					),
					self::CODE_MARKING_CODE_GROUP
				));
				unset($errors);
			}
			unset($stepResult);
		}

		if ($result->isSuccess())
		{
			$stepResult = self::createMarkingCodeGroupField($storage);
			if (!$stepResult->isSuccess())
			{
				$errors = $stepResult->getErrorMessages();
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_MARKING_CODE_INTERNAL_ERROR',
						['#ERROR#' => implode('; ', $errors)]
					),
					self::CODE_MARKING_CODE_GROUP
				));
				unset($errors);
			}
			unset($stepResult);
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	private static function allowedMarkingCodeGroup()
	{
		if (!self::initBitrix24())
		{
			$iterator = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => ['=ID' => 'ru', '=ACTIVE' => 'Y']
			]);
			$row = $iterator->fetch();
			unset($iterator);
			if (empty($row))
				return false;
			$iterator = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => ['@ID' => ['ua', 'by', 'kz'], '=ACTIVE' => 'Y'],
				'limit' => 1
			]);
			$row = $iterator->fetch();
			unset($iterator);
			if (!empty($row))
				return false;
			return true;
		}
		else
		{
			return (\CBitrix24::getPortalZone() === 'ru');
		}
	}

	/**
	 * @param array $storage
	 * @return Main\Result
	 */
	private static function createMarkingCodeGroupField(array $storage)
	{
		$result = new Main\Result();

		$settings = [
			'HLBLOCK_ID' => $storage['ID'],
			'HLFIELD_ID' => $storage['FIELDS']['UF_NAME'],
			'DEFAULT_VALUE' => '',
			'DISPLAY' => \CUserTypeHlblock::DISPLAY_LIST,
			'LIST_HEIGHT' => 1
		];
		$languages = self::getLanguages();
		$messageList = [
			'EDIT_FORM_LABEL' => [],
			'LIST_COLUMN_LABEL' => [],
			'LIST_FILTER_LABEL' => []
		];
		foreach ($languages as $languageId)
		{
			$message = (string)Loc::getMessage('MARKING_CODE_GROUP_FIELD_TITLE', null, $languageId);
			if ($message !== '')
			{
				$messageList['EDIT_FORM_LABEL'][$languageId] = $message;
				$messageList['LIST_COLUMN_LABEL'][$languageId] = $message;
				$messageList['LIST_FILTER_LABEL'][$languageId] = $message;
			}
		}
		unset($message, $languageId, $languages);

		$description = [
			'ENTITY_ID' => Catalog\ProductTable::getUfId(),
			'FIELD_NAME' => $storage['UF_FIELD'],
			'USER_TYPE_ID' => \CUserTypeHlblock::USER_TYPE_ID,
			'XML_ID' => self::CODE_MARKING_CODE_GROUP,
			'SORT' => 100,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SHOW_FILTER' => 'S',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'N',
			'SETTINGS' => $settings,
			'EDIT_FORM_LABEL' => $messageList['EDIT_FORM_LABEL'],
			'LIST_COLUMN_LABEL' => $messageList['LIST_COLUMN_LABEL'],
			'LIST_FILTER_LABEL' => $messageList['LIST_FILTER_LABEL']
		];

		$internalResult = self::createUserField($description);

		if (!$internalResult->isSuccess())
		{
			$result->addErrors($internalResult->getErrors());
		}
		else
		{
			$data = $internalResult->getData();
			$result->setData(['ID' => $data['ID']]);
			unset($data);
		}

		unset($description, $messageList, $settings);

		return $result;
	}

	/**
	 * @return array
	 */
	private static function getMarkingCodeGroupStorageFields()
	{
		$result = [];

		$fieldSettings = [
			'XML_ID' => [
				'DEFAULT_VALUE' => '',
				'SIZE' => 16,
				'ROWS' => 1,
				'MIN_LENGTH' => 0,
				'MAX_LENGTH' => 0,
				'REGEXP' => '/^[0-9]{1,16}$/'
			],
			'NAME' => [
				'DEFAULT_VALUE' => '',
				'SIZE' => 100,
				'ROWS' => 1,
				'MIN_LENGTH' => 1,
				'MAX_LENGTH' => 255,
				'REGEXP' => ''
			]
		];

		$languages = self::getLanguages();

		$sort = 100;
		foreach (array_keys($fieldSettings) as $fieldId)
		{
			$messageList = [
				'EDIT_FORM_LABEL' => [],
				'LIST_COLUMN_LABEL' => [],
				'LIST_FILTER_LABEL' => []
			];
			foreach ($languages as $languageId)
			{
				$message = (string)Loc::getMessage('MARKING_CODE_GROUP_UF_FIELD_'.$fieldId, null, $languageId);
				if ($message !== '')
				{
					$messageList['EDIT_FORM_LABEL'][$languageId] = $message;
					$messageList['LIST_COLUMN_LABEL'][$languageId] = $message;
					$messageList['LIST_FILTER_LABEL'][$languageId] = $message;
				}
			}
			unset($message, $languageId);

			$result[] = [
				'FIELD_NAME' => self::FIELD_PREFIX.$fieldId,
				'USER_TYPE_ID' => \CUserTypeString::USER_TYPE_ID,
				'XML_ID' => $fieldId,
				'SORT' => $sort,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'Y',
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
				'SETTINGS' => $fieldSettings[$fieldId],
				'EDIT_FORM_LABEL' => $messageList['EDIT_FORM_LABEL'],
				'LIST_COLUMN_LABEL' => $messageList['LIST_COLUMN_LABEL'],
				'LIST_FILTER_LABEL' => $messageList['LIST_FILTER_LABEL']
			];
			$sort += 100;
		}
		unset($messageList, $fieldId);
		unset($sort);
		unset($languages);
		unset($fieldSettings);

		return $result;

	}

	/**
	 * @param array $storage
	 * @return Main\Result
	 */
	private static function fillMarkingCodeGroups(array $storage)
	{
		$result = new Main\Result();

		$groupCodes = ['02', '03', '05', '5408', '8258', '8721', '9840', '06', '5010', '5137', '5139', '5140'];
		$groupTitles = Loc::loadLanguageFile(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/regionalsystemfields/markingcodegroup.php',
			'ru'
		);

		$internalResult = self::transformMarkingCodes(
			$storage,
			[
				['OLD_XML_ID' => '5048', 'NEW_XML_ID' => '5408']
			]
		);
		if (!$internalResult->isSuccess())
		{
			$result->addErrors($internalResult->getErrors());
		}
		unset($internalResult);


		$groupList = [];
		foreach ($groupCodes as $id)
		{
			$groupList[] = [
				'UF_XML_ID' => $id,
				'UF_NAME' => $groupTitles['MARKING_CODE_GROUP_TYPE_'.$id]
			];
		}
		unset($id, $groupTitles, $groupCodes);

		$internalResult = self::fillHighloadBlock($storage, $groupList);
		unset($groupList);

		if (!$internalResult->isSuccess())
		{
			$result->addErrors($internalResult->getErrors());
		}
		unset($internalResult);

		return $result;
	}

	/**
	 * @param array $block
	 * @param array $values
	 * @return Main\Result
	 */
	private static function transformMarkingCodes(array $block, array $values)
	{
		$result = new Main\Result();

		$entity = Highload\HighloadBlockTable::compileEntity($block);
		$entityDataClass = $entity->getDataClass();

		foreach ($values as $group)
		{
			$iterator = $entityDataClass::getList([
				'select' => ['ID'],
				'filter' => ['=UF_XML_ID' => $group['OLD_XML_ID']]
			]);
			$row = $iterator->fetch();
			if (!empty($row))
			{
				$internalResult = $entityDataClass::update($row['ID'], ['UF_XML_ID' => $group['NEW_XML_ID']]);
				if (!$internalResult->isSuccess())
				{
					$result->addError(new Main\Error(
						Loc::getMessage(
							'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_INTERNAL_ERROR'
						)
					));
				}
				unset($internalResult);
			}
		}
		unset($found, $row, $iterator, $group);
		unset($entityDataClass, $entity);

		return $result;
	}

	/**
	 * @return array|null
	 */
	private static function getMarkingCodeGroupAction()
	{
		self::initStorageList();

		if (!self::initHighloadBlock())
			return null;

		$userField = new \CUserTypeEntity();
		$iterator = $userField->GetList(
			[],
			[
				'ENTITY_ID' => Catalog\ProductTable::getUfId(),
				'FIELD_NAME' => self::$storageList[self::CODE_MARKING_CODE_GROUP]['UF_FIELD']
			]
		);
		$row = $iterator->Fetch();
		unset($iterator);
		if (empty($row))
			return null;

		$description = $userField->GetByID($row['ID']);

		$list = [];
		$list[] = [
			'VALUE' => '0',
			'NAME' => Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_MESS_VALUE_EMPTY')
		];
		$storage = self::$storageList[self::CODE_MARKING_CODE_GROUP];
		$entity = Highload\HighloadBlockTable::compileEntity($storage['NAME']);
		$entityDataClass = $entity->getDataClass();
		$found = false;
		$iterator = $entityDataClass::getList([
			'select' => ['*'],
			'order' => ['ID' => 'ASC']
		]);
		while ($value = $iterator->fetch())
		{
			$found = true;
			$list[] = [
				'VALUE' => $value['ID'],
				'NAME' => $value['UF_NAME']
			];
		}
		unset($value, $iterator);

		if (!$found)
			return null;

		$action = [];
		$action[] = [
			'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
		];
		$action[] = [
			'ACTION' => Main\Grid\Panel\Actions::CREATE,
			'DATA' => [
				[
					'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
					'ID' => self::getFormRowFieldId($storage['UF_FIELD']),
					'NAME' => self::getFormRowFieldName($storage['UF_FIELD']),
					'ITEMS' => $list
				],
			]
		];

		$title = (isset($description['EDIT_FORM_LABEL'][LANGUAGE_ID])
			? $description['EDIT_FORM_LABEL'][LANGUAGE_ID]
			: $storage['UF_FIELD']
		);

		$result = [
			'NAME' => $title,
			'VALUE' => $storage['UF_FIELD'],
			'ONCHANGE' => $action
		];
		unset($action);

		return $result;
	}

	private static function getFormRowFieldName(string $field)
	{
		return self::FIELD_NAME_PREFIX.mb_strtoupper($field);
	}

	/**
	 * @param string $field
	 * @return string
	 */
	private static function getFormRowFieldId(string $field)
	{
		return self::FIELD_ID_PREFIX.mb_strtolower($field).'_id';
	}

	/**
	 * @param array $block
	 * @return Main\Result
	 */
	private static function createHighloadBlock(array $block)
	{
		$result = new Main\Result();

		if (!self::checkHighloadBlock())
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOAD_MODULE_ABSENT')
			));
			return $result;
		}

		$fieldList = [];

		$stepResult = self::createHighloadBlockStorage(
			[
				'NAME' => $block['NAME'],
				'TABLE_NAME' => $block['TABLE_NAME']
			],
			[
				'ALLOW_UPDATE' => true
			]
		);
		if (!$stepResult->isSuccess())
		{
			$result->addErrors($stepResult->getErrors());
		}
		else
		{
			$data = $stepResult->getData();
			$block['ID'] = $data['ID'];
			unset($data);
		}
		unset($stepResult);

		if ($result->isSuccess())
		{
			$stepResult = self::setHighloadBlockTitle($block);
			if (!$stepResult->isSuccess())
			{
				$result->addErrors($stepResult->getErrors());
			}
			unset($stepResult);
		}

		if ($result->isSuccess())
		{
			$stepResult = self::setHighloadBlockRights($block);
			if (!$stepResult->isSuccess())
			{
				$result->addErrors($stepResult->getErrors());
			}
			unset($stepResult);
		}

		if ($result->isSuccess())
		{
			$stepResult = self::setHighloadBlockFields($block);
			if (!$stepResult->isSuccess())
			{
				$result->addErrors($stepResult->getErrors());
			}
			else
			{
				$fieldList = $stepResult->getData();
			}
			unset($stepResult);
		}

		if ($result->isSuccess())
		{
			$result->setData([
				'ID' => $block['ID'],
				'FIELDS' => $fieldList
			]);
		}

		return $result;
	}

	/**
	 * @param array $block
	 * @param array $options
	 * @return Main\Result
	 */
	private static function createHighloadBlockStorage(array $block, array $options = [])
	{
		$result = new Main\Result();

		$iterator = Highload\HighloadBlockTable::getList([
			'select' => ['ID', 'NAME', 'TABLE_NAME'],
			'filter' => ['=NAME' => $block['NAME'], '=TABLE_NAME' => $block['TABLE_NAME']]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
		{
			if (isset($options['ALLOW_UPDATE']) && $options['ALLOW_UPDATE'] === true)
			{
				$block['ID'] = $row['ID'];
			}
			else
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_EXIST_HIGHLOADBLOCK',
						['#NAME#' => $block['NAME']]
					)
				));
				return $result;
			}
		}
		else
		{
			$internalResult = Highload\HighloadBlockTable::add([
				'NAME' => $block['NAME'],
				'TABLE_NAME' => $block['TABLE_NAME']
			]);
			if (!$internalResult->isSuccess())
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_CREATE_HIGHLOADBLOCK',
						['#NAME#' => $block['NAME']]
					)
				));
				return $result;
			}
			$block['ID'] = $internalResult->getId();
			unset($internalResult);
		}

		$result->setData(['ID' => $block['ID']]);

		return $result;
	}

	/**
	 * @param array $block
	 * @return Main\Result
	 */
	private static function setHighloadBlockTitle(array $block)
	{
		$result = new Main\Result();

		if (!isset($block['ID']))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOADBLOCK_ID_ABSENT')
			));
			return $result;
		}

		if (!empty($block['TITLES']) && is_array($block['TITLES']))
		{
			Highload\HighloadBlockLangTable::delete($block['ID']);
			foreach ($block['TITLES'] as $languageId => $title)
			{
				Highload\HighloadBlockLangTable::add([
					'ID' => $block['ID'],
					'LID' => $languageId,
					'NAME' => $title
				]);
			}
			unset($languageId, $title);
		}

		return $result;
	}

	/**
	 * @param array $block
	 * @return Main\Result
	 */
	private static function setHighloadBlockRights(array $block)
	{
		$result = new Main\Result();

		if (!isset($block['ID']))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOADBLOCK_ID_ABSENT')
			));
			return $result;
		}

		if (!empty($block['RIGHTS']) && is_array($block['RIGHTS']))
		{
			$tasks = self::getModuleTasks('highloadblock');
			foreach ($block['RIGHTS'] as $accessCode => $role)
			{
				if (!isset($tasks[$role]))
				{
					continue;
				}
				$access = [
					'HL_ID' => $block['ID'],
					'ACCESS_CODE' => $accessCode,
					'TASK_ID' => $tasks[$role]
				];
				$iterator = Highload\HighloadBlockRightsTable::getList([
					'select' => ['ID'],
					'filter' => ['=HL_ID' => $access['HL_ID'], '=ACCESS_CODE' => $access['ACCESS_CODE']]
				]);
				$row = $iterator->fetch();
				if (!empty($row))
				{
					Highload\HighloadBlockRightsTable::update($row['ID'], $access);
				}
				else
				{
					Highload\HighloadBlockRightsTable::add($access);
				}
			}
			unset($role, $tasks);
		}

		return $result;
	}


	/**
	 * @param string $moduleId
	 * @param array $filter
	 * @return array
	 */
	private static function getModuleTasks(string $moduleId, array $filter = [])
	{
		$result = [];

		$filter['=MODULE_ID'] = $moduleId;

		$iterator = TaskTable::getList([
			'select' => ['ID', 'LETTER'],
			'filter' => $filter
		]);
		while ($row = $iterator->fetch())
		{
			$result[$row['LETTER']] = $row['ID'];
		}
		unset($row, $iterator);

		return $result;
	}

	/**
	 * @param array $block
	 * @return Main\Result
	 */
	private static function setHighloadBlockFields(array $block)
	{
		$result = new Main\Result();

		if (!isset($block['ID']))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOADBLOCK_ID_ABSENT')
			));
			return $result;
		}

		if (!empty($block['FIELDS']) && is_array($block['FIELDS']))
		{
			$list = [];

			$entityId = Highload\HighloadBlockTable::compileEntityId($block['ID']);

			foreach ($block['FIELDS'] as $field)
			{
				$list[$field['FIELD_NAME']] = null;
				$field['ENTITY_ID'] = $entityId;

				$internalResult = self::createUserField($field);
				if (!$internalResult->isSuccess())
				{
					$errors = $internalResult->getErrorMessages();
					$result->addError(new Main\Error(
						Loc::getMessage(
							'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_HIGHLOADBLOCK_CREATE_FIELD',
							['#FIELD#' => $field['FIELD_NAME'], '#ERROR#' => implode('; ', $errors)]
						)
					));
					unset($errors);
				}
				else
				{
					$data = $internalResult->getData();
					$list[$field['FIELD_NAME']] = $data['ID'];
				}
				unset($internalResult);
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
			unset($field);
			unset($entityId);

			$result->setData($list);
			unset($list);
		}

		return $result;
	}

	/**
	 * @param array $block
	 * @param array $values
	 * @return Main\Result
	 */
	private static function fillHighloadBlock(array $block, array $values)
	{
		$result = new Main\Result();

		$entity = Highload\HighloadBlockTable::compileEntity($block);
		$entityDataClass = $entity->getDataClass();

		foreach ($values as $group)
		{
			$iterator = $entityDataClass::getList([
				'select' => ['ID'],
				'filter' => ['=UF_XML_ID' => $group['UF_XML_ID']]
			]);
			$row = $iterator->fetch();
			$found = !empty($row);
			if (!$found)
			{
				$iterator = $entityDataClass::getList([
					'select' => ['ID'],
					'filter' => ['=UF_NAME' => $group['UF_NAME']]
				]);
				$row = $iterator->fetch();
				$found = !empty($row);
			}
			if ($found)
			{
				$internalResult = $entityDataClass::update($row['ID'], $group);
			}
			else
			{
				$internalResult = $entityDataClass::add($group);
			}
			if (!$internalResult->isSuccess())
			{
				$result->addError(new Main\Error(
					Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_DICTIONARY_ITEM_CREATE',
						[
							'#DICTIONARY#' => Loc::getMessage('STORAGE_MARKING_CODE_GROUP_TITLE'),
							'#CODE#' => '['.$group['UF_XML_ID'].'] '.$group['UF_NAME']
						]
					)
				));
			}
		}
		unset($found, $row, $iterator, $group);
		unset($entityDataClass, $entity);

		return $result;
	}

	/**
	 * @param array $field
	 * @return Main\Result
	 */
	private static function createUserField(array $field)
	{
		global $APPLICATION;

		$result = new Main\Result();

		$userField = new \CUserTypeEntity();

		$iterator = $userField->GetList(
			[],
			[
				'ENTITY_ID' => $field['ENTITY_ID'],
				'FIELD_NAME' => $field['FIELD_NAME']
			]
		);
		$row = $iterator->Fetch();
		unset($iterator);
		$id = 0;
		if (!empty($row))
		{
			if ($userField->Update($row['ID'], $field))
			{
				$id = (int)$row['ID'];
			}
		}
		else
		{
			$id = (int)$userField->Add($field);
		}
		unset($row);
		if ($id <= 0)
		{
			$exception = $APPLICATION->GetException();
			$error = ($exception instanceof \CAdminException
				? $exception->GetString()
				: Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_ERR_INTERNAL_ERROR')
			);
			$result->addError(new Main\Error(
				$error,
				$field['FIELD_NAME']
			));
			unset($error, $exception);
		}
		else
		{
			$result->setData(['ID' => $id]);
		}

		return $result;
	}
}