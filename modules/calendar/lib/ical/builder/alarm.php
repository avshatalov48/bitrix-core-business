<?php

namespace Bitrix\Calendar\ICal\Builder;

use Bitrix\Calendar\ICal\Basic\BasicComponent;
use Bitrix\Calendar\ICal\Basic\Content;
use Bitrix\Calendar\ICal\Basic\DatetimePropertyType;
use Bitrix\Calendar\ICal\Basic\PropertyType;
use Bitrix\Calendar\ICal\Basic\TextPropertyType;
use Bitrix\Calendar\ICal\IcsBuilder;
use Bitrix\Calendar\Util;
use Bitrix\Main\Type\DateTime;

final class Alarm extends BasicComponent
{
	public function __construct(
		private readonly string $type,
		private readonly string $value,
		private readonly string $message = '',
	)
	{
	}

	public function getType(): string
	{
		return 'VALARM';
	}

	protected function setContent(): Content
	{
		$content = Content::getInstance($this->getType())
			->textProperty('ACTION', 'DISPLAY')
			->property($this->resolveTriggerProperty())
		;

		if ($this->message)
		{
			$content->textProperty('DESCRIPTION', $this->message);
		}

		return $content;
	}

	private function resolveTriggerProperty(): PropertyType
	{
		$property = null;

		switch ($this->type)
		{
			case 'DURATION':
				$property = new TextPropertyType('TRIGGER', $this->value);
				break;
			case 'DATE-TIME':
				$dateTime = Util::getDateObject(
					date: (new DateTime($this->value, IcsBuilder::UTC_DATETIME_FORMAT))
						->format(IcsBuilder::DEFAULT_DATETIME_FORMAT),
					fullDay: false
				);
				$property = new DatetimePropertyType(
					names: 'TRIGGER',
					dateTime: $dateTime,
					withTime: true,
					withTimezone: false,
					isUTC: true
				);
				break;
		}

		return $property;
	}

	public function getProperties(): array
	{
		return [
			'ACTION',
			'TRIGGER',
		];
	}
}
