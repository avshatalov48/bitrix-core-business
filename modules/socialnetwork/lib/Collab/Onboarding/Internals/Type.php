<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals;

use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Converter\PromotionConverter;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\Converter\PromotionListConverter;

enum Type: string
{
	// When create collab
	case MembersNotInvitedOneDay = 'MembersNotInvitedOneDay'; //aha-moment
	case MembersNotInvitedTwoDays = 'MembersNotInvitedTwoDays'; //system message
	case MembersNotInvitedFourDays = 'MembersNotInvitedFourDays'; //aha-moment
	case MembersNotInvitedFiveDays = 'MembersNotInvitedFiveDays'; //system message

	// When invite guest
	case CollaberNotAcceptInvitationOneDay = 'CollaberNotAcceptInvitationOneDay'; //notification
	case CollaberNotAcceptInvitationTwoDays = 'CollaberNotAcceptInvitationTwoDays'; //system message

	// When invite anyone, but guest must accept invitation
	case StartChattingOneDay = 'StartChattingOneDay'; //system message
	case CreatedTaskOrMeetingOrFileThreeDays = 'CreatedTaskOrMeetingOrFileThreeDays'; //aha-moment

	public static function createFromPromotionId(string $promoId): ?self
	{
		return PromotionConverter::getInstance()->convertToOnboardingType($promoId);
	}

	public static function toPromotions(): ?PromotionList
	{
		return PromotionListConverter::getInstance()
			->convertOnboardingTypes(...self::cases())
		;
	}
}
