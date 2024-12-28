<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Option;

use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\CanGuestCopyTextOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\CanGuestScreenshotOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\ManageMessagesOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\ShowHistoryOption;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\WhoCanInviteOption;

final class OptionFactory
{
	public const DEFAULT_OPTIONS = [
		ManageMessagesOption::NAME => ManageMessagesOption::DEFAULT_VALUE,
		WhoCanInviteOption::NAME => WhoCanInviteOption::DEFAULT_VALUE,
		ShowHistoryOption::NAME => ShowHistoryOption::DEFAULT_VALUE,
		CanGuestCopyTextOption::NAME => CanGuestCopyTextOption::DEFAULT_VALUE,
		CanGuestScreenshotOption::NAME => CanGuestScreenshotOption::DEFAULT_VALUE,
	];
	/**
	 * @throws ObjectNotFoundException
	 */
	public static function createOption(
		string $name,
		string $value,
	): AbstractOption
	{
		return match ($name)
		{
			ManageMessagesOption::NAME => new ManageMessagesOption($value),
			WhoCanInviteOption::NAME => new WhoCanInviteOption($value),
			ShowHistoryOption::NAME => new ShowHistoryOption($value),
			CanGuestCopyTextOption::NAME => new CanGuestCopyTextOption($value),
			CanGuestScreenshotOption::NAME => new CanGuestScreenshotOption($value),
			default => throw new ObjectNotFoundException(),
		};
	}
}
