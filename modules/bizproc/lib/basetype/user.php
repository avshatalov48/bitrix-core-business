<?php
namespace Bitrix\Bizproc\BaseType;

use Bitrix\Main;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Automation;

/**
 * Class User
 * @package Bitrix\Bizproc\BaseType
 */
class User extends Base
{

	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::USER;
	}

	/**
	 * Get formats list.
	 * @return array
	 */
	public static function getFormats()
	{
		$formats = parent::getFormats();
		$formats['friendly'] = array(
			'callable' =>'formatValueFriendly',
			'separator' => ', ',
		);
		return $formats;
	}

	/**
	 * Normalize single value.
	 *
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @return mixed Normalized value
	 */
	public static function toSingleValue(FieldType $fieldType, $value)
	{
		if (is_array($value))
		{
			reset($value);
			$value = current($value);
		}

		return $value;
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValuePrintable(FieldType $fieldType, $value)
	{
		if (!is_array($value))
			$value = array($value);

		return \CBPHelper::usersArrayToString($value, null, $fieldType->getDocumentType());
	}

	/**
	 * @param FieldType $fieldType
	 * @param $value
	 * @return string
	 */
	protected static function formatValueFriendly(FieldType $fieldType, $value)
	{
		if (!is_array($value))
			$value = array($value);

		return \CBPHelper::usersArrayToString($value, null, $fieldType->getDocumentType(), false);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param mixed $value Field value.
	 * @param string $toTypeClass Type class name.
	 * @return null|mixed
	 */
	public static function convertTo(FieldType $fieldType, $value, $toTypeClass)
	{
		/** @var Base $toTypeClass */
		$type = $toTypeClass::getType();
		switch ($type)
		{
			case FieldType::DOUBLE:
			case FieldType::INT:
				$value = (string)$value;
				if (mb_strpos($value, 'user_') === 0)
					$value = mb_substr($value, mb_strlen('user_'));
				$value = (int)$value;
				break;
			case FieldType::STRING:
			case FieldType::TEXT:
			case FieldType::USER:
				$value = (string)$value;
				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * Return conversion map for current type.
	 * @return array Map.
	 */
	public static function getConversionMap()
	{
		return array(
			array(
				FieldType::DOUBLE,
				FieldType::INT,
				FieldType::STRING,
				FieldType::TEXT,
				FieldType::USER
			)
		);
	}

	/**
	 * @param FieldType $fieldType
	 * @param array $field
	 * @param mixed $value
	 * @param bool $allowSelection
	 * @param int $renderMode
	 * @return string
	 */
	protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		if ($value !== null && !is_array($value))
		{
			if (self::isRawValue($value))
			{
				$errors = [];
				$value = \CBPHelper::UsersStringToArray($value, $fieldType->getDocumentType(), $errors);
			}
			else
			{
				$value = [$value];
			}
		}

		$isPublic = ($renderMode & FieldType::RENDER_MODE_PUBLIC);

		$valueString = \CBPHelper::usersArrayToString($value, null, $fieldType->getDocumentType());

		if ($allowSelection && !$isPublic)
		{
			return static::renderControlSelector($field, $valueString, 'combine', '', $fieldType);
		}

		if ($isPublic)
		{
			\CUtil::InitJSCore(['bp_user_selector']);
			$name = static::generateControlName($field);
			$controlId = static::generateControlId($field);

			$settings = $fieldType->getSettings();

			$config = [
				'valueInputName' => $name,
				'value' => $valueString,
				'items' => $value ? static::getSelectedItems($value, $settings) : [],
				'multiple' => $fieldType->isMultiple(),
				'required' => $fieldType->isRequired(),
			];

			if ($settings)
			{
				$config += $settings;
			}

			$groups = \CBPRuntime::GetRuntime()
				->GetService('DocumentService')
				->GetAllowableUserGroups($fieldType->getDocumentType(), true);

			if ($groups)
			{
				$config['groups'] = [];
				foreach ($groups as $id => $groupName)
				{
					if (!$groupName || mb_strpos($id, 'group_') === 0)
					{
						continue;
					}

					$config['groups'][] = [
						'id' => preg_match('/^[0-9]+$/', $id) ? 'G'.$id : $id,
						'name' => $groupName
					];
				}
			}

			$controlIdJs = \CUtil::JSEscape($controlId);
			$controlIdHtml = htmlspecialcharsbx($controlId);
			$configHtml = htmlspecialcharsbx(Main\Web\Json::encode($config));
			$className = htmlspecialcharsbx(static::generateControlClassName($fieldType, $field));
			$property = $fieldType->getProperty();
			$property['Type'] = static::getType();
			$propertyHtml = htmlspecialcharsbx(Main\Web\Json::encode($property));

			return <<<HTML
				<script>
					BX.ready(function(){
						var c = document.getElementById('{$controlIdJs}');
						if (c)
						{
							BX.Bizproc.FieldType.initControl(c.parentNode, JSON.parse(c.dataset.property));
						}
					});
				</script>
				<div id="{$controlIdHtml}" data-role="user-selector" data-property="{$propertyHtml}" data-config="{$configHtml}" class="{$className}"></div>
HTML;
		}

		$renderResult = parent::renderControl($fieldType, $field, $valueString, $allowSelection, $renderMode);
		$renderResult .= static::renderControlSelector($field, null, false, '', $fieldType);

		return $renderResult;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		return static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param mixed $value Field value.
	 * @param bool $allowSelection Allow selection flag.
	 * @param int $renderMode Control render mode.
	 * @return string
	 */
	public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
	{
		return static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param array $request Request data.
	 * @return array|null
	 */
	protected static function extractValue(FieldType $fieldType, array $field, array $request)
	{
		$value = parent::extractValue($fieldType, $field, $request);
		$result = null;

		if (is_string($value) && $value !== '')
		{
			$errors = [];
			$result = \CBPHelper::usersStringToArray($value, $fieldType->getDocumentType(), $errors);
			if (sizeof($errors) > 0)
			{
				static::addErrors($errors);
			}
		}

		return $result;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param array $request Request data.
	 * @return null|string
	 */
	public static function extractValueSingle(FieldType $fieldType, array $field, array $request)
	{
		static::cleanErrors();
		$result = static::extractValue($fieldType, $field, $request);

		return is_array($result)? array_shift($result) : $result;
	}

	/**
	 * @param FieldType $fieldType Document field type.
	 * @param array $field Form field.
	 * @param array $request Request data.
	 * @return array|null
	 */
	public static function extractValueMultiple(FieldType $fieldType, array $field, array $request)
	{
		static::cleanErrors();
		return static::extractValue($fieldType, $field, $request);
	}

	public static function externalizeValue(FieldType $fieldType, $context, $value)
	{
		$useExtraction = $fieldType->getSettings()['ExternalExtract'] ?? false;

		if ($context === FieldType::VALUE_CONTEXT_JN_MOBILE)
		{
			$useExtraction = true;
		}

		if ($useExtraction && $value)
		{
			$docId = $fieldType->getDocumentId() ?: $fieldType->getDocumentType();

			return \CBPHelper::ExtractUsers($value, $docId, true);
		}

		return parent::externalizeValue($fieldType, $context, $value);
	}

	public static function externalizeValueMultiple(FieldType $fieldType, $context, $value)
	{
		$useExtraction = $fieldType->getSettings()['ExternalExtract'] ?? false;

		if ($context === FieldType::VALUE_CONTEXT_JN_MOBILE)
		{
			$useExtraction = true;
		}

		if ($useExtraction && $value)
		{
			$docId = $fieldType->getDocumentId() ?: $fieldType->getDocumentType();
			return \CBPHelper::ExtractUsers($value, $docId);
		}

		return parent::externalizeValueMultiple($fieldType, $context, $value);
	}

	public static function internalizeValue(FieldType $fieldType, $context, $value)
	{
		if ($context === FieldType::VALUE_CONTEXT_JN_MOBILE && is_numeric($value))
		{
			$value = 'user_' . $value;
		}

		return parent::internalizeValue($fieldType, $context, $value);
	}

	private static function getSelectedItems(array $value, array $settings = []): ?array
	{
		if (!class_exists(\Bitrix\UI\EntitySelector\Dialog::class))
		{
			return null;
		}

		$mapCallback = function ($value)
		{
			if ($value && strpos($value, 'user_') === 0)
			{
				return ['user', \CBPHelper::StripUserPrefix($value)];
			}
			if ($value && strpos($value, 'group_d') === 0)
			{
				return ['department', preg_replace('|[^0-9]+|', '', $value)];
			}

			return null;
		};

		$preselectedItems = array_filter(array_map($mapCallback, $value));

		if (!$preselectedItems)
		{
			return [];
		}

		$options = [];

		if (!empty($settings['allowEmailUsers']))
		{
			$options['entities'] = [
				[
					'id' => 'user',
					'options' => [
						'emailUsers' => true,
						'myEmailUsers' => true,
					]
				]
			];
		}

		return \Bitrix\UI\EntitySelector\Dialog::getSelectedItems($preselectedItems, $options)->toArray();
	}

	public static function validateValueSingle($value, FieldType $fieldType)
	{
		$value = static::toSingleValue($fieldType, $value);

		$value = trim($value);

		$isUser = (mb_strpos($value, 'user_') !== false);
		if ($isUser)
		{
			return $value;
		}

		$isGroup = (mb_strpos($value, 'group_') !== false);
		if ($isGroup)
		{
			return $value;
		}

		$isNumber = preg_match('#^[0-9]+$#', $value);
		if ($isNumber)
		{
			return $value;
		}

		$isDocumentGroup = Automation\Helper::isDocumentUserGroup($value, $fieldType->getDocumentType());
		if ($isDocumentGroup)
		{
			return $value;
		}

		return null;
	}

	public static function validateValueMultiple($value, FieldType $fieldType): array
	{
		$value = parent::validateValueMultiple($value, $fieldType);
		$value = array_filter($value, static fn($v) => ($v !== null));

		return array_values(array_unique($value));
	}

	public static function convertPropertyToView(FieldType $fieldType, int $viewMode, array $property): array
	{
		if ($viewMode === FieldType::RENDER_MODE_JN_MOBILE && $fieldType->getValue())
		{
			$value = \CBPHelper::flatten($fieldType->getValue());
			$value = array_values(array_filter($value, fn($v) => strpos($v, 'user_') !== false));

			$property['Settings'] = ['entityList' => static::getSelectedItems($value)];
			$property['Type'] = static::getType();
		}

		return parent::convertPropertyToView($fieldType, $viewMode, $property);
	}

	private static function isRawValue($value): bool
	{
		return (
			is_string($value)
			&& !is_numeric($value)
			&& strpos($value, 'user_') === false
			&& strpos($value, 'group_') === false
		);
	}
}
