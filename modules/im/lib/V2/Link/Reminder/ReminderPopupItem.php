<?php

namespace Bitrix\Im\V2\Link\Reminder;

use Bitrix\Im\V2\Rest\PopupDataItem;

class ReminderPopupItem implements PopupDataItem
{
	// todo refactor this. replace with lazy load
	private ReminderCollection $reminders;

	public function __construct($reminders = null)
	{
		if (!$reminders instanceof ReminderCollection)
		{
			$this->reminders = new ReminderCollection();
		}
		else
		{
			$this->reminders = $reminders;
		}

		if ($reminders instanceof ReminderItem)
		{
			if ($this->reminders[$reminders->getId()] === null)
			{
				$this->reminders->add($reminders);
			}
		}
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			foreach ($item->reminders as $reminder)
			{
				if (!isset($this->reminders[$reminder->getId()]))
				{
					$this->reminders->add($reminder);
				}
			}
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'reminders';
	}

	public function toRestFormat(array $option = []): array
	{
		$option['WITHOUT_MESSAGES'] = 'Y';
		return $this->reminders->toRestFormat($option);
	}
}