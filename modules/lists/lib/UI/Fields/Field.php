<?php

namespace Bitrix\Lists\UI\Fields;

class Field
{
	protected array $property;
	protected array $settings;

	public function __construct(array $property)
	{
		$this->property = $property;
		$this->settings = (
			isset($this->property['SETTINGS']) && is_array($this->property['SETTINGS'])
				? $this->property['SETTINGS']
				: []
		);
	}

	public function getId(): string
	{
		return (string)$this->property['FIELD_ID'];
	}

	public function getIntId(): int
	{
		return (int)$this->property['ID'];
	}

	public function getSort(): int
	{
		return (int)$this->property['SORT'];
	}

	public function getName(): string
	{
		return (string)$this->property['NAME'];
	}

	public function getType()
	{
		return $this->property['TYPE'];
	}

	public function isRequired(): bool
	{
		return $this->property['IS_REQUIRED'] === 'Y';
	}

	public function isMultiple(): bool
	{
		return $this->property['MULTIPLE'] === 'Y';
	}

	public function getDefaultValue(): mixed
	{
		$defaultValue = $this->property['DEFAULT_VALUE'];

		if (\CListFieldTypeList::IsField($this->getId()))
		{
			if ($this->getId() === 'ACTIVE_FROM')
			{
				if ($defaultValue === '=now')
				{
					return ConvertTimeStamp(time() + \CTimeZone::GetOffset(), 'FULL');
				}

				if ($defaultValue === '=today')
				{
					return ConvertTimeStamp(time() + \CTimeZone::GetOffset(), "SHORT");
				}

				return '';
			}

			if ($this->getId() === 'PREVIEW_PICTURE' || $this->getId() === 'DETAIL_PICTURE')
			{
				return '';
			}

			return $defaultValue;
		}

		if (array_key_exists('GetPublicEditHTML', $this->getPropertyUserType()) || $this->getType() === 'F')
		{
			if ($this->getType() === 'N:Sequence' && empty($defaultValue))
			{
				$seq = new \CIBlockSequence($this->getIBlockId(), $this->getIntId());
				$defaultValue = $seq->GetNext();
			}

			return [
				'n0' => [
					'VALUE' => $defaultValue ?: '',
					'DESCRIPTION' => '',
				],
			];
		}

		if ($this->getType() === 'G' || $this->getType() === 'E' || $this->getType() === 'L')
		{
			return is_array($defaultValue) ? $defaultValue : [$defaultValue];
		}

		$value = [
			'n0' => ['VALUE' => $defaultValue, 'DESCRIPTION' => ''],
		];

		if ($defaultValue !== '' && $this->isMultiple())
		{
			$value['n1'] = ['VALUE' => '', 'DESCRIPTION' => ''];
		}

		return $value;
	}

	public function getProperty(): array
	{
		return $this->property;
	}

	public function getPropertyType(): ?string
	{
		return is_string($this->property['PROPERTY_TYPE'] ?? null) ? $this->property['PROPERTY_TYPE'] : null;
	}

	public function getSettings(): array
	{
		return $this->settings;
	}

	private function getPropertyUserType(): array
	{
		return is_array($this->property['PROPERTY_USER_TYPE'] ?? null) ? $this->property['PROPERTY_USER_TYPE'] : [];
	}

	private function getIBlockId(): int
	{
		return (int)$this->property['IBLOCK_ID'];
	}

	private function getUserTypeSettings(): array
	{
		return isset($this->property['USER_TYPE_SETTINGS']) ? (array)$this->property['USER_TYPE_SETTINGS'] : [];
	}

	public function isShowInAddForm(): bool
	{
		if (in_array($this->getId(), ['DATE_CREATE', 'TIMESTAMP_X', 'CREATED_BY', 'MODIFIED_BY']))
		{
			return false;
		}

		if (!isset($this->settings['SHOW_ADD_FORM']))
		{
			return true;
		}

		return $this->settings['SHOW_ADD_FORM'] === 'Y';
	}

	public function isShowInEditForm(): bool
	{
		if (!isset($this->settings['SHOW_EDIT_FORM']))
		{
			return true;
		}

		return $this->settings['SHOW_EDIT_FORM'] === 'Y';
	}

	public function isAddReadOnlyField(): bool
	{
		if ($this->getType() === 'N:Sequence')
		{
			if (isset($this->getUserTypeSettings()['write']))
			{
				return $this->getUserTypeSettings()['write'] === 'N';
			}

			return true;
		}

		return isset($this->settings['ADD_READ_ONLY_FIELD']) && $this->settings['ADD_READ_ONLY_FIELD'] === 'Y';
	}

	public function isEditReadOnlyField(): bool
	{
		if ($this->getType() === 'N:Sequence')
		{
			if (isset($this->getUserTypeSettings()['write']))
			{
				return $this->getUserTypeSettings()['write'] === 'N';
			}

			return true;
		}

		return isset($this->settings['EDIT_READ_ONLY_FIELD']) && $this->settings['EDIT_READ_ONLY_FIELD'] === 'Y';
	}
}
