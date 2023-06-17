<?php

namespace Bitrix\Landing\History;

use Bitrix\Landing\History\Action\BaseAction;

class ActionFactory
{
	public const UNDO = 'UNDO';
	public const REDO = 'REDO';
	protected const ACTIONS_NAMESPACE = '\\Action\\';
	protected const ACTIONS = [
		'ADD_BLOCK' => [
			self::UNDO => 'RemoveBlockAction',
			self::REDO => 'AddBlockAction',
		],
		'REMOVE_BLOCK' => [
			self::UNDO => 'AddBlockAction',
			self::REDO => 'RemoveBlockAction',
		],
		'SORT_BLOCK' => [
			self::UNDO => 'SortBlockAction',
			self::REDO => 'SortBlockAction',
		],
		'ADD_CARD' => [
			self::UNDO => 'RemoveCardAction',
			self::REDO => 'AddCardAction',
		],
		'REMOVE_CARD' => [
			self::UNDO => 'AddCardAction',
			self::REDO => 'RemoveCardAction',
		],
		'EDIT_TEXT' => [
			self::UNDO => 'EditTextAction',
			self::REDO => 'EditTextAction',
		],
		'EDIT_MAP' => [
			self::UNDO => 'EditMapAction',
			self::REDO => 'EditMapAction',
		],
		'EDIT_EMBED' => [
			self::UNDO => 'EditEmbedAction',
			self::REDO => 'EditEmbedAction',
		],
		'EDIT_IMG' => [
			self::UNDO => 'EditImgAction',
			self::REDO => 'EditImgAction',
		],
		'EDIT_ICON' => [
			self::UNDO => 'EditIconAction',
			self::REDO => 'EditIconAction',
		],
		'EDIT_LINK' => [
			self::UNDO => 'EditLinkAction',
			self::REDO => 'EditLinkAction',
		],
		'EDIT_STYLE' => [
			self::UNDO => 'EditStyleAction',
			self::REDO => 'EditStyleAction',
		],
		'UPDATE_CONTENT' => [
			self::UNDO => 'UpdateContentAction',
			self::REDO => 'UpdateContentAction',
		],
		'CHANGE_ANCHOR' => [
			self::UNDO => 'ChangeAnchorAction',
			self::REDO => 'ChangeAnchorAction',
		],
		'CHANGE_NODE_NAME_ACTION' => [
			self::UNDO => 'ChangeNodeNameAction',
			self::REDO => 'ChangeNodeNameAction',
		],
		'UPDATE_DYNAMIC' => [
			self::UNDO => 'UpdateDynamicAction',
			self::REDO => 'UpdateDynamicAction',
		],
		'MULTIPLY' => [
			self::UNDO => 'MultiplyAction',
			self::REDO => 'MultiplyAction',
		],
		'ADD_NODE' => [
			self::UNDO => 'RemoveNodeAction',
			self::REDO => 'AddNodeAction',
		],
		'REMOVE_NODE' => [
			self::UNDO => 'AddNodeAction',
			self::REDO => 'RemoveNodeAction',
		],
	];
	public const MULTIPLY_ACTION_NAME = 'MULTIPLY';
	protected const COMPARE_METHOD = 'compareParams';

	/**
	 * @param string $actionName - just available actions
	 * @param bool|null $undo - if need redo - false
	 * @return BaseAction|null
	 */
	public static function getAction(string $actionName, ?bool $undo = false): ?BaseAction
	{
		if (self::isActionExists($actionName))
		{
			$class = self::getActionClass($actionName, $undo);
			if ($class)
			{
				return new $class();
			}
		}

		// todo: null or err?
		return null;
	}

	/**
	 * Check correctly action name
	 * @param string $actionName
	 * @return bool
	 */
	public static function isActionExists(string $actionName): bool
	{
		return array_key_exists($actionName, self::ACTIONS);
	}

	/**
	 * Get action class by direction (undo or redo)
	 * @param string $actionName
	 * @param bool $undo - if need redo - false
	 * @return string|null
	 */
	public static function getActionClass(string $actionName, ?bool $undo = false): ?string
	{
		$direction = $undo ? self::UNDO : self::REDO;
		$class =
			__NAMESPACE__
			. self::ACTIONS_NAMESPACE
			. str_replace('_', '', self::ACTIONS[$actionName][$direction]);
		if (class_exists($class))
		{
			return $class;
		}

		// todo: null or error?
		return null;
	}

	public static function compareSteps(array $prevStep, array $nextStep): bool
	{
		if (
			$prevStep['ENTITY_TYPE'] === $nextStep['ENTITY_TYPE']
			&& $prevStep['ENTITY_ID'] === $nextStep['ENTITY_ID']
			&& $prevStep['ACTION'] === $nextStep['ACTION']
		)
		{
			$class = self::getActionClass($prevStep['ACTION']);
			if ($class && is_callable([$class, self::COMPARE_METHOD]))
			{
				return call_user_func(
					[$class, self::COMPARE_METHOD],
					$prevStep['ACTION_PARAMS'],
					$nextStep['ACTION_PARAMS']
				);
			}
		}

		return false;
	}
}