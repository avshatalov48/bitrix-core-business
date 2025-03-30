<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class StringType
 * @package Bitrix\Main\UserField\Types
 */
class StringType extends BaseType
{
	public const
		USER_TYPE_ID = 'string',
		RENDER_COMPONENT = 'bitrix:main.field.string';

	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_STRING_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
		];
	}

	/**
	 * This function is called when new properties are added. We only support mysql data types.
	 *
	 * This function is called to construct the SQL column creation query
	 * to store non-multiple property values.
	 * Values of multiple properties are not stored in rows, but in columns
	 * (as in infoblocks) and the type of such a field in the database is always text
	 *
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\TextField('x'));
	}

	/**
	 * This function is called before saving the property metadata to the database.
	 *
	 * It should 'clear' the array with the settings of the instance of the property type.
	 * In order to accidentally / intentionally no one wrote down any garbage there.
	 *
	 * @param array $userField An array describing the field. Warning! this description of the field has not yet been saved to the database!
	 * @return array An array that will later be serialized and stored in the database.
	 */
	public static function prepareSettings(array $userField): array
	{
		$size = (int)($userField['SETTINGS']['SIZE'] ?? 0);
		$rows = (int)($userField['SETTINGS']['ROWS'] ?? 0);
		$min = (int)($userField['SETTINGS']['MIN_LENGTH'] ?? 0);
		$max = (int)($userField['SETTINGS']['MAX_LENGTH'] ?? 0);

		$regExp = '';
		if (
			is_array($userField['SETTINGS'])
			&& !empty($userField['SETTINGS']['REGEXP'])
			//Checking the correctness of the regular expression entered by the user
			&& @preg_match($userField['SETTINGS']['REGEXP'], null) !== false
		)
		{
			$regExp = $userField['SETTINGS']['REGEXP'];
		}

		return [
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'ROWS' => ($rows <= 1 ? 1 : ($rows > 50 ? 50 : $rows)),
			'REGEXP' => $regExp,
			'MIN_LENGTH' => $min,
			'MAX_LENGTH' => $max,
			'DEFAULT_VALUE' => is_array($userField['SETTINGS']) ? ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '') : '',
		];
	}

	/**
	 * @param null|array $userField
	 * @param array $additionalSettings
	 * @return array
	 */
	public static function getFilterData(?array $userField, array $additionalSettings): array
	{
		return [
			'id' => $additionalSettings['ID'],
			'name' => $additionalSettings['NAME'],
			'filterable' => ''
		];
	}

	/**
	 * This function is validator.
	 * Called from the CheckFields method of the $ USER_FIELD_MANAGER object,
	 * which can be called from the Add / Update methods of the property owner entity.
	 * @param array $userField
	 * @param string|array $value
	 * @return array
	 */
	public static function checkFields(array $userField, $value): array
	{
		$fieldName = HtmlFilter::encode(
			$userField['EDIT_FORM_LABEL'] <> ''
				? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
		);

		if (is_array($value))
		{
			return [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::getMessage('USER_TYPE_STRING_VALUE_IS_MULTIPLE',
					[
						'#FIELD_NAME#' => $fieldName,
					]
				),
			];
		}

		$msg = [];

		if($value != '' && mb_strlen($value) < $userField['SETTINGS']['MIN_LENGTH'])
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::GetMessage('USER_TYPE_STRING_MIN_LEGTH_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MIN_LENGTH#' => $userField['SETTINGS']['MIN_LENGTH']
					]
				)
			];
		}
		if(
			$userField['SETTINGS']['MAX_LENGTH'] > 0
			&& mb_strlen($value) > $userField['SETTINGS']['MAX_LENGTH']
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::GetMessage('USER_TYPE_STRING_MAX_LEGTH_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MAX_LENGTH#' => $userField['SETTINGS']['MAX_LENGTH']
					]
				),
			];
		}

		if(
			!empty($userField['SETTINGS']['REGEXP'])
			&& (string) $value !== ''
			&& !preg_match($userField['SETTINGS']['REGEXP'] . 'u', $value)
		)
		{
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => ($userField['ERROR_MESSAGE'] != '' ?
					$userField['ERROR_MESSAGE'] :
					Loc::GetMessage('USER_TYPE_STRING_REGEXP_ERROR',
						[
							'#FIELD_NAME#' => $fieldName
						]
					)
				),
			];
		}

		return $msg;
	}

	/**
	 * This function should return a representation of the field value for the search.
	 * It is called from the OnSearchIndex method of the object $ USER_FIELD_MANAGER,
	 * which is also called the update function of the entity search index.
	 * For multiple values, the VALUE field is an array.
	 * @param array $userField
	 * @return string|null
	 */
	public static function onSearchIndex(array $userField): ?string
	{
		if(is_array($userField['VALUE']))
		{
			$result = implode('\r\n', $userField['VALUE']);
		}
		else
		{
			$result = $userField['VALUE'];
		}

		return $result;
	}

	//<editor-fold desc="Events and methods..."  defaultstate="collapsed">
	/**
	 * You can register the onBeforeGetPublicView event handler
	 * and customize the display by manipulating the metadata of a custom property.
	 * \Bitrix\Main\EventManager::getInstance()->addEventHandler(
	 * 'main',
	 * 'onBeforeGetPublicView',
	 * array('CUserTypeString', 'onBeforeGetPublicView')
	 * );
	 * You can do the same for editing:
	 * onBeforeGetPublicEdit (EDIT_COMPONENT_NAME и EDIT_COMPONENT_TEMPLATE)
	 */
	/*
		public static function onBeforeGetPublicView($event)
		{
			$params = $event->getParameters();
			$arUserField = &$params[0];
			$arAdditionalParameters = &$params[1];
			if ($arUserField['USER_TYPE_ID'] == 'string')
			{
				$arUserField['VIEW_COMPONENT_NAME'] = 'my:system.field.view';
				$arUserField['VIEW_COMPONENT_TEMPLATE'] = 'string';
			}
		}
	*/

	/**
	 * You can register the onGetPublicView event handler
	 * and display the property as you need.
	 * \Bitrix\Main\EventManager::getInstance()->addEventHandler(
	 * 'main',
	 * 'onGetPublicView',
	 * array('CUserTypeString', 'onGetPublicView')
	 * );
	 * You can do the same for editing: onGetPublicEdit
	 */
	/*
		public static function onGetPublicView($event)
		{
			$params = $event->getParameters();
			$arUserField = $params[0];
			$arAdditionalParameters = $params[1];
			if ($arUserField['USER_TYPE_ID'] == 'string')
			{
				$html = 'demo string';
				return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, $html);
			}
		}
	*/

	/**
	 * You can register the onAfterGetPublicView event handler
	 * and modify the html before displaying it.
	 * \Bitrix\Main\EventManager::getInstance()->addEventHandler(
	 * 'main',
	 * 'onAfterGetPublicView',
	 * array('CUserTypeString', 'onAfterGetPublicView')
	 * );
	 * You can do the same for editing: onAfterGetPublicEdit
	 */
	/*
		public static function onAfterGetPublicView($event)
		{
			$params = $event->getParameters();
			$arUserField = $params[0];
			$arAdditionalParameters = $params[1];
			$html = &$params[2];
			if ($arUserField['USER_TYPE_ID'] == 'string')
			{
				$html .= '!';
			}
		}
	*/

	/**
	 * This function is called before storing the values in the database.
	 * Called from the Update method of the $ USER_FIELD_MANAGER object.
	 * For multiple values, the function is called several times.
	 * @param array $arUserField
	 * @param $value
	 * @return string
	 */
	/*	static function OnBeforeSave($arUserField, $value)
	{
		if(strlen($value)>0)
			return ''.round(doubleval($value), $arUserField['SETTINGS']['PRECISION']);
	}*/
	//</editor-fold>
}
