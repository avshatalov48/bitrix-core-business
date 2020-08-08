<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\Context;

/**
 * Class EnumUfComponent
 */
class EnumUfComponent extends BaseUfComponent
{
	public const MAX_OPTION_LENGTH = 40;

	protected static function getUserTypeId(): string
	{
		return EnumType::USER_TYPE_ID;
	}

	/**
	 * @return array
	 */
	protected function getFieldValue(): array
	{
		if(
			!$this->additionalParameters['bVarsFromForm']
			&&
			!isset($this->additionalParameters['VALUE'])
		)
		{
			if(
				isset($this->userField['ENTITY_VALUE_ID'], $this->userField['ENUM'])
				&&
				$this->userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				$value = ($this->userField['MULTIPLE'] === 'Y' ? [] : null);
				foreach($this->userField['ENUM'] as $enum)
				{
					if($enum['DEF'] === 'Y')
					{
						if($this->userField['MULTIPLE'] === 'Y')
						{
							$value[] = $enum['ID'];
						}
						else
						{
							$value = $enum['ID'];
							break;
						}
					}
				}
			}
			else
			{
				$value = $this->userField['VALUE'];
			}
		}
		elseif(isset($this->additionalParameters['VALUE']))
		{
			$value = $this->additionalParameters['VALUE'];
		}
		else
		{
			$value = Context::getCurrent()->getRequest()->get($this->userField['FIELD_NAME']);
		}

		return self::normalizeFieldValue($value);
	}
}