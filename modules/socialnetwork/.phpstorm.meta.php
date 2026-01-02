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
		'socialnetwork.onboarding.job.repository',
		'socialnetwork.onboarding.job.cache',
		'socialnetwork.onboarding.batch.job.executor',
		'socialnetwork.onboarding.queue.service',
		'socialnetwork.onboarding.queue.provider',
		'socialnetwork.onboarding.promotion.service',
		'socialnetwork.collab.converter.service',
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
				'socialnetwork.onboarding.job.repository' => \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\JobRepository::class,
				'socialnetwork.onboarding.job.cache' => \Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache\JobCache::class,
				'socialnetwork.onboarding.batch.job.executor' => \Bitrix\Socialnetwork\Collab\Onboarding\Execution\Executor\BatchJobExecutor::class,
				'socialnetwork.onboarding.queue.service' => \Bitrix\Socialnetwork\Collab\Onboarding\Service\QueueService::class,
				'socialnetwork.onboarding.queue.provider' => \Bitrix\Socialnetwork\Collab\Onboarding\Provider\QueueProvider::class,
				'socialnetwork.onboarding.promotion.service' => \Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\PromotionService::class,
				'socialnetwork.collab.converter.service' => \Bitrix\Socialnetwork\Collab\Converter\ConverterService::class,
			]
		)
	);
}