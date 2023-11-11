<?php

namespace Bitrix\Landing\History;

use Bitrix\Landing\History\Action\BaseAction;
use Bitrix\Landing\History\Action\AddBlockAction;
use Bitrix\Landing\History\Action\AddCardAction;
use Bitrix\Landing\History\Action\ChangeAnchorAction;
use Bitrix\Landing\History\Action\EditAttributesAction;
use Bitrix\Landing\History\Action\EditEmbedAction;
use Bitrix\Landing\History\Action\EditIconAction;
use Bitrix\Landing\History\Action\EditImgAction;
use Bitrix\Landing\History\Action\EditLinkAction;
use Bitrix\Landing\History\Action\EditMapAction;
use Bitrix\Landing\History\Action\EditStyleAction;
use Bitrix\Landing\History\Action\EditTextAction;
use Bitrix\Landing\History\Action\RemoveBlockAction;
use Bitrix\Landing\History\Action\RemoveCardAction;
use Bitrix\Landing\History\Action\ReplaceLanding;
use Bitrix\Landing\History\Action\SortBlockAction;
use Bitrix\Landing\History\Action\UpdateContentAction;
use Bitrix\Landing\History\Action\ChangeNodeNameAction;
use Bitrix\Landing\History\Action\UpdateDynamicAction;
use Bitrix\Landing\History\Action\MultiplyAction;
use Bitrix\Landing\History\Action\RemoveNodeAction;
use Bitrix\Landing\History\Action\AddNodeAction;

/**
 * Factory for create actions
 */
class ActionFactory
{
	protected const UNDO = 'UNDO';
	protected const REDO = 'REDO';
	protected const ACTIONS = [
		'ADD_BLOCK' => [
			self::UNDO => RemoveBlockAction::class,
			self::REDO => AddBlockAction::class,
		],
		'REMOVE_BLOCK' => [
			self::UNDO => AddBlockAction::class,
			self::REDO => RemoveBlockAction::class,
		],
		'SORT_BLOCK' => [
			self::UNDO => SortBlockAction::class,
			self::REDO => SortBlockAction::class,
		],
		'ADD_CARD' => [
			self::UNDO => RemoveCardAction::class,
			self::REDO => AddCardAction::class,
		],
		'REMOVE_CARD' => [
			self::UNDO => AddCardAction::class,
			self::REDO => RemoveCardAction::class,
		],
		'EDIT_TEXT' => [
			self::UNDO => EditTextAction::class,
			self::REDO => EditTextAction::class,
		],
		'EDIT_MAP' => [
			self::UNDO => EditMapAction::class,
			self::REDO => EditMapAction::class,
		],
		'EDIT_EMBED' => [
			self::UNDO => EditEmbedAction::class,
			self::REDO => EditEmbedAction::class,
		],
		'EDIT_IMG' => [
			self::UNDO => EditImgAction::class,
			self::REDO => EditImgAction::class,
		],
		'EDIT_ICON' => [
			self::UNDO => EditIconAction::class,
			self::REDO => EditIconAction::class,
		],
		'EDIT_LINK' => [
			self::UNDO => EditLinkAction::class,
			self::REDO => EditLinkAction::class,
		],
		'EDIT_STYLE' => [
			self::UNDO => EditStyleAction::class,
			self::REDO => EditStyleAction::class,
		],
		'EDIT_ATTRIBUTES' => [
			self::UNDO => EditAttributesAction::class,
			self::REDO => EditAttributesAction::class,
		],
		'UPDATE_CONTENT' => [
			self::UNDO => UpdateContentAction::class,
			self::REDO => UpdateContentAction::class,
		],
		'CHANGE_ANCHOR' => [
			self::UNDO => ChangeAnchorAction::class,
			self::REDO => ChangeAnchorAction::class,
		],
		'CHANGE_NODE_NAME_ACTION' => [
			self::UNDO => ChangeNodeNameAction::class,
			self::REDO => ChangeNodeNameAction::class,
		],
		'UPDATE_DYNAMIC' => [
			self::UNDO => UpdateDynamicAction::class,
			self::REDO => UpdateDynamicAction::class,
		],
		'MULTIPLY' => [
			self::UNDO => MultiplyAction::class,
			self::REDO => MultiplyAction::class,
		],
		'ADD_NODE' => [
			self::UNDO => RemoveNodeAction::class,
			self::REDO => AddNodeAction::class,
		],
		'REMOVE_NODE' => [
			self::UNDO => AddNodeAction::class,
			self::REDO => RemoveNodeAction::class,
		],
		'REPLACE_LANDING' => [
			self::UNDO => ReplaceLanding::class,
			self::REDO => ReplaceLanding::class,
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
		$direction = self::getDirectionName($undo);
		$class = self::ACTIONS[$actionName][$direction];

		if (class_exists($class))
		{
			return $class;
		}

		return null;
	}

	/**
	 * Get name of direction by bool
	 * @param bool|null $undo default false (redo)
	 * @return string
	 */
	public static function getDirectionName(?bool $undo = false): string
	{
		return $undo ? self::UNDO : self::REDO;
	}

	/**
	 * No need processing steps if there are equal. Compare for match it
	 * @param array $prevStep array of step data
	 * @param array $nextStep array of step data
	 * @return bool true if steps are equal
	 */
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