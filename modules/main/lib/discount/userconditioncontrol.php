<?php

namespace Bitrix\Main\Discount;

use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule('sale'))
{
	return;
}

class UserConditionControl extends \CSaleCondCtrlComplex
{
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
		$mxResult = '';
		if (is_string($control))
		{
			$control = static::getControls($control);
		}
		$boolError = !is_array($control);

		$values = array();
		if (!$boolError)
		{
			$values = static::check($oneCondition, $oneCondition, $control, false);
			$boolError = (false === $values);
		}

		if (!$boolError && $control['ID'] === 'CondMainUserId')
		{
			$stringArray = 'array(' . implode(',', array_map('intval', $values['value'])) . ')';
			$type = $oneCondition['logic'];

			$mxResult = static::getClassName() . "::checkBasket({$params['ORDER']}, $stringArray, '{$type}')";
		}

		return $mxResult;
	}

	/**
	 * @param bool|string $controlId
	 *
	 *@return array|bool
	 */
	public static function getControls($controlId = false)
	{
		$controlList = array(
			'CondMainUserId' => array(
				'ID' => 'CondMainUserId',
				'EXECUTE_MODULE' => 'sale',
				'MODULE_ID' => 'main',
				'MODULE_ENTITY' => 'main',
				'ENTITY' => 'USER',
				'FIELD' => 'ID',
				'FIELD_TYPE' => 'int',
				'MULTIPLE' => 'Y',
				'GROUP' => 'N',
				'LABEL' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_ID'),
				'PREFIX' => Loc::getMessage('SALE_USER_CONDITION_CONTROL_FIELD_USER_PREFIX'),
				'LOGIC' => static::getLogic(array(BT_COND_LOGIC_EQ, BT_COND_LOGIC_NOT_EQ)),
				'JS_VALUE' => array(
					'type' => 'userPopup',
					'popup_url' => '/bitrix/admin/user_search.php',
					'popup_params' => array(
						'lang' => LANGUAGE_ID,
					),
					'param_id' => 'n',
					'show_value' => 'Y',
					'user_load_url' => '/bitrix/admin/sale_discount_edit.php?lang=' . LANGUAGE_ID,
				),
				'PHP_VALUE' => array(
					'VALIDATE' => 'user'
				)
			)
		);

		if (false === $controlId)
		{
			return $controlList;
		}
		elseif (isset($controlList[$controlId]))
		{
			return $controlList[$controlId];
		}
		else
		{
			return false;
		}
	}

	public static function getShowIn($arControls)
	{
		return array(\CSaleCondCtrlGroup::getControlID());
	}
}