<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity;

use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\CreatedTaskOrMeetingOrFileThreeDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\CollaberNotAcceptInvitationOneDay;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\CollaberNotAcceptInvitationTwoDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\MembersNotInvitedFiveDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\MembersNotInvitedFourDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\MembersNotInvitedOneDay;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\MembersNotInvitedTwoDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\StartChattingOneDay;

final class JobFactory
{
	public static function create(int $collabId, int $userId, string $type): ?AbstractJob
	{
		return match ($type)
		{
			Type::MembersNotInvitedOneDay->value => new MembersNotInvitedOneDay($collabId, $userId),
			Type::MembersNotInvitedTwoDays->value => new MembersNotInvitedTwoDays($collabId, $userId),
			Type::MembersNotInvitedFourDays->value => new MembersNotInvitedFourDays($collabId, $userId),
			Type::MembersNotInvitedFiveDays->value => new MembersNotInvitedFiveDays($collabId, $userId),

			Type::CollaberNotAcceptInvitationOneDay->value => new CollaberNotAcceptInvitationOneDay($collabId, $userId),
			Type::CollaberNotAcceptInvitationTwoDays->value => new CollaberNotAcceptInvitationTwoDays($collabId, $userId),

			Type::CreatedTaskOrMeetingOrFileThreeDays->value => new CreatedTaskOrMeetingOrFileThreeDays($collabId, $userId),
			Type::StartChattingOneDay->value => new StartChattingOneDay($collabId, $userId),

			default => null,
		};
	}
}
