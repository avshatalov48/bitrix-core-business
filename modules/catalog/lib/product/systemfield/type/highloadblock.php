<?php
namespace Bitrix\Catalog\Product\SystemField\Type;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text;
use Bitrix\Highloadblock as Highload;

class HighloadBlock extends Base
{
	protected const TABLE_NAME_PREFIX = 'b_hlsys_';

	protected const NAME_PREFIX = 'PRODUCT_';

	protected static ?bool $highloadInclude = null;

	protected static function checkRequiredModules(): void
	{
		parent::checkRequiredModules();
		if (self::$highloadInclude === null)
		{
			self::$highloadInclude = Loader::includeModule('highloadblock');
			if (!self::$highloadInclude)
			{
				self::addError(Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_MODULE_IS_ABSENT'));
			}
		}
	}

	protected static function internalCreate(array $config): Main\Result
	{
		$storage = static::createStorage($config['HIGHLOADBLOCK']);
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		static::transformExistValues($storage);
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		static::fillValues($storage);
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		$config['HIGHLOADBLOCK'] = $storage;
		$config = static::createLink($config);
		if (!static::isSuccess())
		{
			return static::getErrorResult();
		}

		$result = new Main\Result();
		$result->setData(['ID' => $config['FIELD']['ID']]);

		return $result;
	}

	protected static function verifyConfig(array $config): array
	{
		return parent::verifyConfig($config);
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public static function getTableName(string $code): string
	{
		return self::TABLE_NAME_PREFIX.mb_strtolower($code);
	}

	/**
	 * @param string $code
	 * @return string
	 */
	public static function getName(string $code): string
	{
		return Text\StringHelper::snake2camel(self::NAME_PREFIX.$code);
	}

	public static function getUserTypeId(): ?string
	{
		if (!static::isAllowed())
		{
			return null;
		}

		return \CUserTypeHlblock::USER_TYPE_ID;
	}

	public static function isAllowed(): bool
	{
		static::checkRequiredModules();

		return self::$highloadInclude;
	}

	public static function getDefaultSettings(): ?array
	{
		if (!static::isAllowed())
		{
			return null;
		}

		return [
			'DEFAULT_VALUE' => '',
			'DISPLAY' => \CUserTypeHlblock::DISPLAY_LIST,
			'LIST_HEIGHT' => 1
		];
	}

	public static function getDefaultRights(): ?array
	{
		if (!static::isAllowed())
		{
			return null;
		}

		$result = [
			'G1' => 'W',
			'G2' => 'R'
		];

		if (static::isBitrix24())
		{
			if (Loader::includeModule('crm'))
			{
				$crmAdminGroupId = \CCrmSaleHelper::getShopGroupIdByType(\CCrmSaleHelper::GROUP_CRM_ADMIN);
				if ($crmAdminGroupId !== null)
				{
					$result['G'.$crmAdminGroupId] = 'W';
				}
				$crmManagerGroupId = \CCrmSaleHelper::getShopGroupIdByType(\CCrmSaleHelper::GROUP_CRM_MANAGER);
				if ($crmManagerGroupId)
				{
					$result['G'.$crmManagerGroupId] = 'R';
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $block
	 * @return array|null
	 */
	protected static function createStorage(array $block): ?array
	{
		$storage = static::createStorageTable(
			[
				'NAME' => $block['NAME'],
				'TABLE_NAME' => $block['TABLE_NAME'],
			],
			[
				'ALLOW_UPDATE' => true,
			]
		);
		if (empty($storage))
		{
			return null;
		}
		$block['ID'] = $storage['ID'];
		unset($storage);

		static::setStorageTitle($block);
		if (!static::isSuccess())
		{
			return null;
		}

		static::setStorageRights($block);
		if (!static::isSuccess())
		{
			return null;
		}

		return static::createStorageFields($block);
	}

	/**
	 * @param array $block
	 * @param array $options
	 * @return array|null
	 */
	protected static function createStorageTable(array $block, array $options = []): ?array
	{
		$row = static::getStorageTable($block);
		if (!empty($row))
		{
			if (isset($options['ALLOW_UPDATE']) && $options['ALLOW_UPDATE'] === true)
			{
				$block['ID'] = $row['ID'];
			}
			else
			{
				static::addError(Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_EXIST_HIGHLOADBLOCK',
					['#NAME#' => $block['NAME']]
				));

				return null;
			}
		}
		else
		{
			$internalResult = Highload\HighloadBlockTable::add([
				'NAME' => $block['NAME'],
				'TABLE_NAME' => $block['TABLE_NAME'],
			]);
			if (!$internalResult->isSuccess())
			{
				static::addError(Loc::getMessage(
					'BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_CREATE_HIGHLOADBLOCK',
					[
						'#NAME#' => $block['NAME'],
						'#ERROR#' => implode('; ', $internalResult->getErrorMessages()),
					]
				));

				return null;
			}
			$block['ID'] = (int)$internalResult->getId();
		}

		return $block;
	}

	public static function getStorageTable(array $block): ?array
	{
		$iterator = Highload\HighloadBlockTable::getList([
			'select' => [
				'ID',
				'NAME',
				'TABLE_NAME',
			],
			'filter' => [
				'=NAME' => $block['NAME'],
				'=TABLE_NAME' => $block['TABLE_NAME'],
			],
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
		{
			$row['ID'] = (int)$row['ID'];
			return $row;
		}

		return null;
	}

	/**
	 * @param array $block
	 * @return void
	 */
	protected static function setStorageTitle(array $block): void
	{
		if (!isset($block['ID']))
		{
			static::addError(Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_HIGHLOADBLOCK_ID_ABSENT'));

			return;
		}

		if (!empty($block['TITLES']) && is_array($block['TITLES']))
		{
			self::deleteStorageTitle($block);
			foreach ($block['TITLES'] as $languageId => $title)
			{
				Highload\HighloadBlockLangTable::add([
					'ID' => $block['ID'],
					'LID' => $languageId,
					'NAME' => $title,
				]);
			}
		}
	}

	private static function deleteStorageTitle(array $block): void
	{
		// because HighloadBlockLangTable primary key list was changed
		$entity = Highload\HighloadBlockLangTable::getEntity();
		if (in_array('LID', $entity->getPrimaryArray(), true))
		{
			foreach (array_keys($block['TITLES']) as $languageId)
			{
				Highload\HighloadBlockLangTable::delete([
					'ID' => $block['ID'],
					'LID' => $languageId,
				]);
			}
		}
		else
		{
			Highload\HighloadBlockLangTable::delete([
				'ID' => $block['ID'],
			]);
		}
		unset($entity);
	}

	/**
	 * @param array $block
	 * @return void
	 */
	protected static function setStorageRights(array $block): void
	{
		if (!isset($block['ID']))
		{
			static::addError(Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_HIGHLOADBLOCK_ID_ABSENT'));

			return;
		}

		if (!empty($block['RIGHTS']) && is_array($block['RIGHTS']))
		{
			$tasks = static::getModuleTasks('highloadblock');
			foreach ($block['RIGHTS'] as $accessCode => $role)
			{
				if (!isset($tasks[$role]))
				{
					continue;
				}
				$access = [
					'HL_ID' => $block['ID'],
					'ACCESS_CODE' => $accessCode,
					'TASK_ID' => $tasks[$role],
				];
				$iterator = Highload\HighloadBlockRightsTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=HL_ID' => $access['HL_ID'],
						'=ACCESS_CODE' => $access['ACCESS_CODE'],
					],
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
		}
	}

	/**
	 * @param array $block
	 * @return array|null
	 */
	protected static function createStorageFields(array $block): ?array
	{
		if (!isset($block['ID']))
		{
			static::addError(Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_HIGHLOADBLOCK_ID_ABSENT'));

			return null;
		}

		if (!empty($block['FIELDS']) && is_array($block['FIELDS']))
		{
			$entityId = Highload\HighloadBlockTable::compileEntityId($block['ID']);

			foreach (array_keys($block['FIELDS']) as $index)
			{
				$block['FIELDS'][$index]['ENTITY_ID'] = $entityId;

				$internalResult = static::createUserField($block['FIELDS'][$index]);
				if (!$internalResult->isSuccess())
				{
					static::addError(Loc::getMessage(
						'BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_HIGHLOADBLOCK_CREATE_FIELD',
						[
							'#FIELD#' => $block['FIELDS'][$index]['FIELD_NAME'],
							'#ERROR#' => implode('; ', $internalResult->getErrorMessages()),
						]
					));

					return null;
				}
				$data = $internalResult->getData();
				$block['FIELDS'][$index]['ID'] = (int)$data['ID'];
			}
		}

		return $block;
	}

	protected static function transformExistValues(array $block): void
	{
		if (!empty($block['TRANSFORM_VALUES']) && is_array($block['TRANSFORM_VALUES']))
		{
			$entity = Highload\HighloadBlockTable::compileEntity($block);
			$entityDataClass = $entity->getDataClass();

			$dictionary = $block['TITLES'][LANGUAGE_ID] ?? $block['NAME'];

			foreach ($block['TRANSFORM_VALUES'] as $group)
			{
				$iterator = $entityDataClass::getList([
					'select' => ['ID'],
					'filter' => ['=UF_XML_ID' => $group['OLD_XML_ID']],
				]);
				$row = $iterator->fetch();
				if (!empty($row))
				{
					$internalResult = $entityDataClass::update($row['ID'], ['UF_XML_ID' => $group['NEW_XML_ID']]);
					if (!$internalResult->isSuccess())
					{
						static::addError(Loc::getMessage(
							'BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_TRANFORM_VALUE',
							[
								'#DICTIONARY#' => $dictionary,
								'#ERROR#' => implode('; ', $internalResult->getErrorMessages()),
							]
						));
					}
				}
			}
		}
	}

	protected static function fillValues(array $block): void
	{
		if (!empty($block['VALUES']) && is_array($block['VALUES']))
		{
			$entity = Highload\HighloadBlockTable::compileEntity($block);
			$entityDataClass = $entity->getDataClass();

			$dictionary = $block['TITLES'][LANGUAGE_ID] ?? $block['NAME'];

			foreach ($block['VALUES'] as $group)
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
					static::addError(new Main\Error(
						Loc::getMessage(
							'BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_ERR_ITEM_CREATE',
							[
								'#DICTIONARY#' => $dictionary,
								'#CODE#' => '['.$group['UF_XML_ID'].'] '.$group['UF_NAME'],
							]
						)
					));
				}
			}
		}
	}

	protected static function createLink(array $config): ?array
	{
		$field = static::getPrepareLinkSettings($config);
		$internalResult = static::createUserField($field);
		if (!$internalResult->isSuccess())
		{
			static::addError(implode('; ', $internalResult->getErrorMessages()));

			return null;
		}
		else
		{
			$data = $internalResult->getData();
			$field['ID'] = (int)$data['ID'];
			$config['FIELD'] = $field;

			return $config;
		}
	}

	protected static function getFieldIdbyName(array $fields, string $name): ?int
	{
		$result = null;
		foreach ($fields as $item)
		{
			if ($item['FIELD_NAME'] === $name)
			{
				$result = $item['ID'];
			}
		}

		return $result;
	}

	protected static function getPrepareLinkSettings(array $config): array
	{
		$field = $config['FIELD'];
		$field['SETTINGS']['HLBLOCK_ID'] = $config['HIGHLOADBLOCK']['ID'];
		$showedField = '';
		if (isset($config['FIELD_CONFIG']['HLFIELD_ID']))
		{
			$showedField = (string)static::getFieldIdbyName(
				$config['HIGHLOADBLOCK']['FIELDS'],
				$config['FIELD_CONFIG']['HLFIELD_ID']
			);
		}
		$field['SETTINGS']['HLFIELD_ID'] = $showedField;

		if (!empty($field['SETTINGS']['DEFAULT_VALUE']))
		{
			$entity = Highload\HighloadBlockTable::compileEntity($config['HIGHLOADBLOCK']['ID']);
			$entityDataClass = $entity->getDataClass();

			$rawValues = $field['SETTINGS']['DEFAULT_VALUE'];
			$map = [];
			$iterator = $entityDataClass::getList([
				'select' => [
					'ID',
					'UF_XML_ID',
				],
				'filter' => ['@UF_XML_ID' => $rawValues],
			]);
			while ($row = $iterator->fetch())
			{
				$map[$row['UF_XML_ID']] = (int)$row['ID'];
			}
			unset($row, $iterator);

			if (is_array($rawValues))
			{
				$values = [];
				foreach ($rawValues as $id)
				{
					if (isset($map[$id]))
					{
						$values[] = $map[$id];
					}
				}
				$field['SETTINGS']['DEFAULT_VALUE'] = $values;
				unset($id, $values);
			}
			else
			{
				$field['SETTINGS']['DEFAULT_VALUE'] = ($map[$rawValues] ?? 0);
			}
			unset($rawValues);
		}

		return $field;
	}

	protected static function internalGridAction(array $config): ?array
	{
		if (empty($config['USER_FIELD']) || !is_array($config['USER_FIELD']))
		{
			return null;
		}
		$userField = $config['USER_FIELD'];

		$itemsConfig = [
			'RESULT' => [
				'NAME_WITH_ID' => 'Y',
			],
		];
		if (
			!empty($config['ADDITIONAL_VALUES'])
			&& is_array($config['ADDITIONAL_VALUES'])
		)
		{
			$itemsConfig['ADDITIONAL_VALUES'] = $config['ADDITIONAL_VALUES'];
		}

		$list = static::getItems($userField, $itemsConfig);
		if ($list === null)
		{
			return null;
		}

		$emptyText =
			$userField['MULTIPLE'] === 'Y'
			? Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_MESS_EMPTY_VALUE')
			: ''
		;

		$action = [];
		$action[] = [
			'ACTION' => Main\Grid\Panel\Actions::RESET_CONTROLS
		];
		$action[] = [
			'ACTION' => Main\Grid\Panel\Actions::CREATE,
			'DATA' => [
				[
					'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
					'ID' => $config['VISUAL']['LIST']['ID'],
					'NAME' => $config['VISUAL']['LIST']['NAME'],
					'ITEMS' => $list,
					'MULTIPLE' => $userField['MULTIPLE'],
					'EMPTY_TEXT' => $emptyText,
				],
			],
		];

		$result = [
			'NAME' => $userField['EDIT_FORM_LABEL'] ?? $userField['FIELD_NAME'],
			'VALUE' => $userField['FIELD_NAME'],
			'ONCHANGE' => $action
		];
		unset($action, $list);

		return $result;
	}

	public static function getIdByXmlId(int $hlblockId, array $xmlIds): array
	{
		$result = [];
		$xmlIds = array_filter($xmlIds); // '0' - not valid code
		if (empty($xmlIds))
		{
			return $result;
		}
		$hlblock = Highload\HighloadBlockTable::resolveHighloadblock($hlblockId);
		if ($hlblock === null)
		{
			return $result;
		}
		$entity = Highload\HighloadBlockTable::compileEntity($hlblock);
		$fieldsList = $entity->getFields();
		if (isset($fieldsList['ID']) && isset($fieldsList['UF_XML_ID']))
		{
			$entityDataClass = $entity->getDataClass();
			$iterator = $entityDataClass::getList([
				'select' => [
					'ID',
					'UF_XML_ID',
				],
				'filter' => [
					'@UF_XML_ID' => $xmlIds,
				],
			]);
			while ($value = $iterator->fetch())
			{
				$result[$value['UF_XML_ID']] = (int)$value['ID'];
			}
			unset($value, $iterator);
			unset($entityDataClass);
		}
		unset($fieldsList, $entity);

		return $result;
	}

	public static function getXmlIdById(int $hlblockId, array $ids): array
	{
		$result = [];
		Main\Type\Collection::normalizeArrayValuesByInt($ids);
		if (empty($ids))
		{
			return $result;
		}
		$hlblock = Highload\HighloadBlockTable::resolveHighloadblock($hlblockId);
		if ($hlblock === null)
		{
			return $result;
		}
		$entity = Highload\HighloadBlockTable::compileEntity($hlblock);
		$fieldsList = $entity->getFields();
		if (isset($fieldsList['ID']) && isset($fieldsList['UF_XML_ID']))
		{
			$entityDataClass = $entity->getDataClass();
			$iterator = $entityDataClass::getList([
				'select' => [
					'ID',
					'UF_XML_ID',
				],
				'filter' => [
					'@ID' => $ids,
				],
			]);
			while ($value = $iterator->fetch())
			{
				$result[$value['ID']] = $value['UF_XML_ID'];
			}
			unset($value, $iterator);
			unset($entityDataClass);
		}
		unset($fieldsList, $entity);

		return $result;
	}

	public static function getItems(array $userField, array $config = []): ?array
	{
		if (empty($userField['SETTINGS']) || !is_array($userField['SETTINGS']))
		{
			return null;
		}
		if (!isset($userField['SETTINGS']['HLBLOCK_ID']))
		{
			return null;
		}

		$hlblock = Highload\HighloadBlockTable::resolveHighloadblock($userField['SETTINGS']['HLBLOCK_ID']);
		if ($hlblock === null)
		{
			return null;
		}
		$entity = Highload\HighloadBlockTable::compileEntity($hlblock);
		$fieldsList = $entity->getFields();
		if (!isset($fieldsList['ID']) || !isset($fieldsList['UF_NAME']))
		{
			return null;
		}

		$useIdKey = false;
		$nameWithId = false;
		if (!empty($config['RESULT']) && is_array($config['RESULT']))
		{
			$useIdKey = isset($config['RESULT']['RETURN_FIELD_ID']) && $config['RESULT']['RETURN_FIELD_ID'] === 'Y';
			$nameWithId = isset($config['RESULT']['NAME_WITH_ID']) && $config['RESULT']['NAME_WITH_ID'] === 'Y';
		}

		$items = [];
		if (
			$userField['MANDATORY'] === 'N'
			&& $userField['MULTIPLE'] === 'N'
		)
		{
			$row = [
				'VALUE' => '0',
				'NAME' => Loc::getMessage('BX_CATALOG_PRODUCT_SYSTEMFIELD_HIGHLOADBLOCK_MESS_EMPTY_VALUE'),
			];
			if ($useIdKey)
			{
				$row['ID'] = '0';
			}
			$items[] = $row;
		}

		if (
			!empty($config['ADDITIONAL_ITEMS']['LIST'])
			&& is_array($config['ADDITIONAL_ITEMS']['LIST'])
		)
		{
			$items = array_merge(
				$items,
				$config['ADDITIONAL_ITEMS']['LIST']
			);
		}

		$entityDataClass = $entity->getDataClass();
		$iterator = $entityDataClass::getList([
			'select' => [
				'ID',
				'UF_NAME',
			],
			'order' => [
				'UF_NAME' => 'ASC',
			],
		]);
		while ($value = $iterator->fetch())
		{
			$row = [
				'ID' =>  $value['ID'],
				'VALUE' => $value['ID'],
				'NAME' => ($nameWithId
					? $value['UF_NAME'] . ' [' .$value['ID'] . ']'
					: $value['UF_NAME']
				),
			];
			if ($useIdKey)
			{
				$row['ID'] = $value['ID'];
			}
			$items[] = $row;
		}
		unset($value, $iterator);
		unset($entityDataClass, $entity);

		return $items;
	}
}
