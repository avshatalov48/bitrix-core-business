<?php

namespace Bitrix\Iblock\Property\Type;

use Bitrix\Iblock\PropertyTable;
use CIBlockProperty;

final class PropertyTypeSettings
{
	private string $propertyType;
	private ?string $userType;
	private array $setValues = [];
	private array $showedFields = [];
	private array $hiddenFields = [];
	private ?string $settingsHtml = null;
	private ?string $defaultValueHtml = null;

	/**
	 * @param string $propertyType
	 * @param string|null $userType
	 */
	public function __construct(string $propertyType, ?string $userType = null)
	{
		$this->propertyType = $propertyType;
		$this->userType = $userType;

		$this->initHiddenFields();
	}

	/**
	 * Property type (is simple).
	 *
	 * If property with user type (as `S:HTML`), this method returns `S`.
	 *
	 * @return string
	 */
	public function getPropertyType(): string
	{
		return $this->propertyType;
	}

	/**
	 * Property user type.
	 *
	 * @return string|null
	 */
	public function getUserType(): ?string
	{
		return $this->userType;
	}

	/**
	 * Initialization of fields that should not be displayed in the editor.
	 *
	 * @return void
	 */
	private function initHiddenFields(): void
	{
		if ($this->propertyType === PropertyTable::TYPE_ELEMENT)
		{
			$this->hiddenFields[] = 'SEARCHABLE';
			$this->hiddenFields[] = 'WITH_DESCRIPTION';
			$this->hiddenFields[] = 'COL_COUNT';
			$this->hiddenFields[] = 'DEFAULT_VALUE';
		}
		elseif ($this->propertyType === PropertyTable::TYPE_SECTION)
		{
			$this->hiddenFields[] = 'SEARCHABLE';
			$this->hiddenFields[] = 'WITH_DESCRIPTION';
			$this->hiddenFields[] = 'COL_COUNT';
			$this->hiddenFields[] = 'DEFAULT_VALUE';
		}
		elseif ($this->propertyType === PropertyTable::TYPE_LIST)
		{
			$this->hiddenFields[] = 'WITH_DESCRIPTION';
			$this->hiddenFields[] = 'MULTIPLE_CNT';
			$this->hiddenFields[] = 'COL_COUNT';
			$this->hiddenFields[] = 'DEFAULT_VALUE';
		}
		elseif ($this->propertyType === PropertyTable::TYPE_FILE)
		{
			$this->hiddenFields[] = 'FILTERABLE';
			$this->hiddenFields[] = 'MULTIPLE_CNT';
			$this->hiddenFields[] = 'SMART_FILTER';
			$this->hiddenFields[] = 'DISPLAY_TYPE';
			$this->hiddenFields[] = 'DISPLAY_EXPANDED';
			$this->hiddenFields[] = 'FILTER_HINT';
			$this->hiddenFields[] = 'DEFAULT_VALUE';
			$this->hiddenFields[] = 'ROW_COUNT';
		}
		elseif ($this->userType === PropertyTable::USER_TYPE_HTML)
		{
			$this->hiddenFields[] = 'MULTIPLE';
		}

		// hidden for NOT need type

		if ($this->propertyType !== PropertyTable::TYPE_LIST)
		{
			$this->hiddenFields[] = 'LIST_TYPE';
		}

		if ($this->propertyType !== PropertyTable::TYPE_FILE)
		{
			$this->hiddenFields[] = 'FILE_TYPE';
		}

		if (
			!in_array($this->propertyType, [PropertyTable::TYPE_SECTION, PropertyTable::TYPE_ELEMENT], true)
			&& $this->userType !== PropertyTable::USER_TYPE_SKU
		)
		{
			$this->hiddenFields[] = 'LINK_IBLOCK_ID';
		}
	}

	public function appendShowedFields(array $fields): void
	{
		array_push($this->showedFields, ...$fields);
	}

	public function appendHiddenFields(array $fields): void
	{
		foreach ($fields as $name)
		{
			$this->hiddenFields[] = $name;

			if ($name === 'SMART_FILTER')
			{
				$this->hiddenFields[] = 'DISPLAY_TYPE';
				$this->hiddenFields[] = 'DISPLAY_EXPANDED';
				$this->hiddenFields[] = 'FILTER_HINT';
			}
		}
	}

	/**
	 * Factory method for creating and filling an instance with html settings or editor fields.
	 *
	 * @param string $propertyType
	 * @param string $userType
	 * @param array $propertyFields
	 * @param string|null $htmlName
	 *
	 * @return self
	 */
	public static function createByUserType(string $propertyType, string $userType, array $propertyFields, ?string $htmlName = 'USER_TYPE_SETTINGS'): self
	{
		$self = new self($propertyType, $userType);
		$userTypeFields = CIBlockProperty::GetUserType($userType);

		$excludeSettingsHtmlUserTypes = [
			PropertyTable::USER_TYPE_DIRECTORY,
		];

		if (
			isset($userTypeFields["GetSettingsHTML"])
			&& !in_array($userType, $excludeSettingsHtmlUserTypes, true)
			&& is_callable($userTypeFields["GetSettingsHTML"])
		)
		{
			$config = [];
			$htmlCode = call_user_func_array(
				$userTypeFields["GetSettingsHTML"],
				[
					$propertyFields,
					[
						'NAME' => $htmlName,
					],
					&$config,
				]
			);

			if (!empty($htmlCode) && is_string($htmlCode))
			{
				$self->settingsHtml = $htmlCode;
			}

			if (isset($config['SHOW']) && is_array($config['SHOW']))
			{
				$self->appendShowedFields($config['SHOW']);
			}

			if (isset($config['HIDE']) && is_array($config['HIDE']))
			{
				$self->appendHiddenFields($config['HIDE']);
			}

			if (isset($config['SET']) && is_array($config['SET']))
			{
				$self->setValues = $config['SET'];
			}
		}

		if (isset($userTypeFields["GetPropertyFieldHtml"]) && is_callable($userTypeFields["GetPropertyFieldHtml"]))
		{
			$htmlCode = call_user_func_array(
				$userTypeFields["GetPropertyFieldHtml"],
				[
					$propertyFields,
					[
						'VALUE' => $propertyFields['DEFAULT_VALUE'] ?? null,
						'DESCRIPTION' => '',
					],
					[
						'VALUE' => 'DEFAULT_VALUE',
						'DESCRIPTION' => '',
						'MODE' => 'EDIT_FORM',
						'FORM_NAME' => '',
					],
				]
			);

			if (!empty($htmlCode) && is_string($htmlCode))
			{
				$self->defaultValueHtml = $htmlCode;
			}
		}

		return $self;
	}

	/**
	 * Checking whether the field is displayed.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function isShownField(string $fieldName): bool
	{
		$hide = false;

		if (!empty($this->hiddenFields))
		{
			$hide =
				in_array($fieldName, $this->hiddenFields, true)
				&& !in_array($fieldName, $this->showedFields, true)
			;
		}

		return !$hide;
	}

	public function getSettingsHtml(): ?string
	{
		return $this->settingsHtml;
	}

	public function getDefaultValueHtml(): ?string
	{
		return $this->defaultValueHtml;
	}

	public function getSetValues(): array
	{
		return $this->setValues;
	}
}
