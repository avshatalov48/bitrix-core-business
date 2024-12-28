<?php

namespace PHPSTORM_META
{
	registerArgumentsSet(
		'bitrix_socialnetwork_locator_codes',
		'socialnetwork.group.service',
		'socialnetwork.collab.service',
		'socialnetwork.collab.option.service',
		'socialnetwork.collab.activity.service',
		'socialnetwork.collab.log.service',
		'socialnetwork.collab.member.facade',
		'socialnetwork.group.member.service',
		'socialnetwork.collab.invitation.service',
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_socialnetwork_locator_codes'));

	override(
		\Bitrix\Main\DI\ServiceLocator::get(0),
		map(
			[
				'socialnetwork.group.service' => \Bitrix\Socialnetwork\Control\GroupService::class,
				'socialnetwork.collab.service' => \Bitrix\Socialnetwork\Collab\Control\CollabService::class,
				'socialnetwork.collab.option.service' => \Bitrix\Socialnetwork\Collab\Control\Option\OptionService::class,
				'socialnetwork.collab.activity.service' => \Bitrix\Socialnetwork\Collab\Control\Activity\LastActivityService::class,
				'socialnetwork.collab.log.service' => \Bitrix\Socialnetwork\Collab\Control\Log\LogEntryService::class,
				'socialnetwork.collab.member.facade' => \Bitrix\Socialnetwork\Collab\Control\Member\CollabMemberFacade::class,
				'socialnetwork.group.member.service' => \Bitrix\Socialnetwork\Control\Member\GroupMemberService::class,
				'socialnetwork.collab.invitation.service' => \Bitrix\Socialnetwork\Collab\Control\Invite\InvitationService::class,
			]
		)
	);
}