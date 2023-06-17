<?php

namespace Bitrix\Main\Discount;

use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('sale'))
{
	return;
}

class UserConditionControl extends \CSaleCondCtrlComplex
{
	const ENTITY_USER_ID = 'CondMainUserId';
	const ENTITY_USER_GROUP_ID = 'BX:CondMainUserGroupId';

	public static function onBuildDiscountConditionInterfaceControls()
	{
		return new EventResult(
			EventResult::SUCCESS,
			static::getControlDescr(),
			'main'
		);
	}

	public static function getControlDescr()
	{
		$description = parent::getControlDescr();
		$description['SORT'] = 700;
		return $description;
	}

	public static function getClassName()
	{
		return get_called_class();
	}

	public static function getControlShow($params)
	{
		$result = array(
			'controlgroup' => true,
			'group' =>  false,
			'label' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_GROUP_NAME'),
			'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
			'children' => array()
		);

		foreach (static::getControls() as $control)
		{
			$result['children'][] = array(
				'controlId' => $control['ID'],
				'group' => false,
				'label' => $control['LABEL'],
				'showIn' => static::getShowIn($params['SHOW_IN_GROUPS']),
				'control' => array(
					$control['PREFIX'],
					static::getLogicAtom($control['LOGIC']),
					static::getValueAtom($control['JS_VALUE'])
				)
			);
		}

		return $result;
	}

	public static function checkBasket(array $order, array $userIds, $type)
	{
		if(empty($order['USER_ID']))
		{
			return false;
		}

		$orderUserId = (int)$order['USER_ID'];
		if ($type === 'Equal')
		{
			return in_array($orderUserId, $userIds);
		}
		elseif($type === 'Not')
		{
			return !in_array($orderUserId, $userIds);
		}

		return false;
	}

	public static function generate($oneCondition, $params, $control, $subs = false)
	{
		$result = '';
		if (is_string($control))
		{
			$control = static::getControls($control);
		}
		$error = !is_array($control);

		$values = array();
		if (!$error)
		{
			$values = static::check($oneCondition, $oneCondition, $control, false);
			$error = ($values === false);
		}

		if (!$error)
		{
			switch ($control['ID'])
			{
				case self::ENTITY_USER_ID:
					$stringArray = 'array('.implode(',', $values['value']).')';
					$type = $oneCondition['logic'];
					$result = static::getClassName() . "::checkBasket({$params['ORDER']}, $stringArray, '{$type}')";
					break;
				case self::ENTITY_USER_GROUP_ID:
					$result = self::generateOrderConditions($oneCondition, $params, $control, $values);
					break;
			}
		}

		return $result;
	}

	/**
	 * @param bool|string $controlId
	 *
	 *@return array|bool
	 */
	public static function getControls($controlId = false)
	{
		$controlList = array(
			self::ENTITY_USER_ID => array(
				'ID' => self::ENTITY_USER_ID,
				'EXECUTE_MODULE' => 'sale',
				'MODULE_ID' => 'main',
				'MODULE_ENTITY' => 'main',
				'ENTITY' => 'USER',
				'FIELD' => 'USER_ID',
				'FIELD_TYPE' => 'int',
				'MULTIPLE' => 'N',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_ID'),
				'PREFIX' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_PREFIX'),
				'LOGIC' => static::getLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'userPopup',
					'popup_url' => self::getAdminSection().'user_search.php',
					'popup_params' => array(
						'lang' => LANGUAGE_ID,
					),
					'param_id' => 'n',
					'show_value' => 'Y',
					'user_load_url' => '/bitrix/admin/sale_discount_edit.php?lang=' . LANGUAGE_ID,
					'coreUserInfo' => 'Y',
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'user'
				)
			),
		);

		if (!Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$labels = array(
				BT_COND_LOGIC_EQ => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_GROUP_EQ'),
				BT_COND_LOGIC_NOT_EQ => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_GROUP_NOT_EQ'),
			);

			$userGroups = array();
			$iterator = Main\GroupTable::getList(array(
				'select' => array('ID', 'NAME', 'C_SORT'),
				'order' => array('C_SORT' => 'ASC', 'NAME' => 'ASC')
			));
			while ($row = $iterator->fetch())
				$userGroups[$row['ID']] = $row['NAME'];
			unset($row, $iterator);
			$controlList[self::ENTITY_USER_GROUP_ID] = array(
				'ID' => self::ENTITY_USER_GROUP_ID,
				'EXECUTE_MODULE' => 'all',
				'MODULE_ID' => 'main',
				'MODULE_ENTITY' => 'main',
				'ENTITY' => 'USER_GROUPS',
				'FIELD' => 'USER_GROUPS',
				'FIELD_TYPE' => 'int',
				'MULTIPLE' => 'Y',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_GROUP_ID'),
				'PREFIX' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_GROUP_PREFIX'),
				'LOGIC' => static::getLogicEx(array_keys($labels), $labels),
				'JS_VALUE' => array(
					'type' => 'select',
					'multiple' => 'Y',
					'values' => $userGroups,
					'show_value' => 'Y'
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'list'
				)
			);
			unset($userGroups, $labels);
		}

		return static::searchControl($controlList, $controlId);
	}

	public static function getShowIn($arControls)
	{
		return array(\CSaleCondCtrlGroup::getControlID());
	}

	private static function generateOrderConditions(array $oneCondition, array $params, array $control, $values)
	{
		$result = '';

		$logic = static::SearchLogic($values['logic'], $control['LOGIC']);
		if (!empty($logic['OP'][$control['MULTIPLE']]))
		{
			$joinOperator = '';
			$multi = false;
			if (isset($control['JS_VALUE']['multiple']) && $control['JS_VALUE']['multiple'] == 'Y')
			{
				$multi = true;
				$joinOperator = ($logic['MULTI_SEP'] ?? ' && ');
			}
			$field = $params['ORDER'].'[\''.$control['FIELD'].'\']';
			switch ($control['FIELD_TYPE'])
			{
				case 'int':
				case 'double':
					if (!$multi)
					{
						$result = str_replace(
							array('#FIELD#', '#VALUE#'),
							array($field, $values['value']),
							$logic['OP'][$control['MULTIPLE']]
						);
					}
					else
					{
						$list = array();
						foreach ($values['value'] as $item)
						{
							$list[] = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($field, $item),
								$logic['OP'][$control['MULTIPLE']]
							);
						}
						$result = '(('.implode(')'.$joinOperator.'(', $list).'))';
						unset($list, $item);
					}
					break;
				case 'char':
				case 'string':
				case 'text':
					if (!$multi)
					{
						$result = str_replace(
							array('#FIELD#', '#VALUE#'),
							array($field, '"'.EscapePHPString($values['value']).'"'),
							$logic['OP'][$control['MULTIPLE']]
						);
					}
					else
					{
						$list = array();
						foreach ($values['value'] as $item)
						{
							$list[] = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($field, '"'.EscapePHPString($item).'"'),
								$logic['OP'][$control['MULTIPLE']]
							);
						}
						$result = '(('.implode(')'.$joinOperator.'(', $list).'))';
						unset($list, $item);
					}
					break;
				case 'date':
				case 'datetime':
					if (!$multi)
					{
						$result = str_replace(
							array('#FIELD#', '#VALUE#'),
							array($field, $values['value']),
							$logic['OP'][$control['MULTIPLE']]
						);
					}
					else
					{
						$list = array();
						foreach ($values['value'] as $item)
						{
							$list[] = str_replace(
								array('#FIELD#', '#VALUE#'),
								array($field, $item),
								$logic['OP'][$control['MULTIPLE']]
							);
						}
						$result = '(('.implode(')'.$joinOperator.'(', $list).'))';
						unset($list, $item);
					}
					break;
			}
			if ($result !== '')
				$result = 'isset('.$field.') && '.$result;
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private static function getAdminSection()
	{
		//TODO: need use \CAdminPage::getSelfFolderUrl, but in general it is impossible now
		return (defined('SELF_FOLDER_URL') ? SELF_FOLDER_URL : '/bitrix/admin/');
	}
}