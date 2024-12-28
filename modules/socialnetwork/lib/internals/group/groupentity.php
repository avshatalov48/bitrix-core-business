<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Internals\Group;

use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class GroupEntity extends EO_Workgroup
{
	public static function wakeUpObject(null|array|Arrayable $data): static
	{
		if ($data instanceof Arrayable)
		{
			$data = $data->toArray();
		}

		if (!is_array($data))
		{
			return new static();
		}

		$fields = static::$dataClass::getEntity()->getFields();

		$wakeUpData = [];
		$customData = [];
		foreach ($data as $field => $value)
		{
			if (array_key_exists($field, $fields))
			{
				if (
					$fields[$field] instanceof UserTypeField
					&& is_array($value)
					&& isset($value['VALUE'])
				)
				{
					$value = $value['VALUE'];
				}

				$wakeUpData[$field] = $value;
			}
			else
			{
				$customData[$field] = $value;
			}
		}

		$object = parent::wakeUp($wakeUpData);
		foreach ($customData as $field => $value)
		{
			$object->customData->set($field, $value);
		}

		return $object;
	}

	public function isProject(): bool
	{
		return $this->typeIs(Type::Project);
	}

	public function isScrum(): bool
	{
		return $this->typeIs(Type::Scrum);
	}

	public function isGroup(): bool
	{
		return $this->typeIs(Type::Group);
	}

	public function typeIs(Type $type): bool
	{
		return $this->getType() === $type->value;
	}
}