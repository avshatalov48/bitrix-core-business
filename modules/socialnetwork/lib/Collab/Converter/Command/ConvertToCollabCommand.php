<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Command;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabFeatures;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabSiteIds;
use Bitrix\Socialnetwork\Collab\Control\Option\Type\WhoCanInviteOption;
use Bitrix\Socialnetwork\Collab\Converter\Handler\AbstractHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\SendChatMessageHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\SetCollabOptionsHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\SetDefaultThemeHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\SetFeaturesHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\UpdateChatHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\UpdateGroupHandler;
use Bitrix\Socialnetwork\Collab\Converter\Handler\UpdateGroupTypeHandler;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Property\Feature;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Command\ValueObject\Features;
use Bitrix\Socialnetwork\Control\Command\ValueObject\FeaturesPermissions;
use Bitrix\Socialnetwork\Control\Enum\ViewMode;
use Bitrix\Socialnetwork\Integration\Tasks\Flow\FlowService;
use Bitrix\Socialnetwork\Item\Workgroup\AvatarType;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Provider\FeatureProvider;

class ConvertToCollabCommand extends AbstractConverterCommand
{
	public const ERROR_CODE_WRONG_TYPE = 10001;
	public const ERROR_CODE_HAS_FLOWS = 10002;
	private const TURNED_OFF_FEATURES = [
		'forum' => [
			'full' => SONET_ROLES_NONE,
			'newtopic' => SONET_ROLES_NONE,
			'answer' => SONET_ROLES_NONE,
		],
		'photo' => [
			'write'=> SONET_ROLES_NONE,
		],
		'blog' => [
			'premoderate_post' => SONET_ROLES_NONE,
			'write_post' => SONET_ROLES_NONE,
			'moderate_post' => SONET_ROLES_NONE,
			'full_post' => SONET_ROLES_NONE,
			'premoderate_comment' => SONET_ROLES_NONE,
			'write_comment' => SONET_ROLES_NONE,
			'moderate_comment' => SONET_ROLES_NONE,
			'full_comment' => SONET_ROLES_NONE,
		],
		'landing_knowledge' => [
			'edit' => SONET_ROLES_NONE,
			'sett' => SONET_ROLES_NONE,
			'delete' => SONET_ROLES_NONE,
		],
		'group_lists' => [
			'write' => SONET_ROLES_NONE,
		],
	];

	/** @return array<AbstractHandler> */
	public function getHandlers(): array
	{
		$updateGroupCommand = (new UpdateCommand())
			->setSiteIds(CollabSiteIds::createWithDefaultValue())
			->setViewMode(ViewMode::SECRET)
			->setAvatarType(AvatarType::None)
			->setId($this->group->getId())
			->setInitiatorId($this->getInitiatorId())
			->setFeatures($this->prepareFeatures())
			->setPermissions(FeaturesPermissions::create(self::TURNED_OFF_FEATURES))
		;

		$collabOptions = CollabOptions::create([WhoCanInviteOption::NAME => $this->group->getInitiatePermission()]);

		return [
			new UpdateGroupHandler($updateGroupCommand),
			new UpdateGroupTypeHandler(Type::Collab),
			new SetFeaturesHandler($this->prepareFeatures()),
			new SetDefaultThemeHandler(),
			new SetCollabOptionsHandler($collabOptions),
			new UpdateChatHandler(),
			new SendChatMessageHandler(ActionType::ConvertGroupToCollab),
			new SendChatMessageHandler(ActionType::ConvertGroupToCollabRich),
		];
	}

	private function prepareFeatures(): Features
	{
		$features = CollabFeatures::createWithDefaultValue();
		/** @var array<Feature> $currentFeatures */
		$currentFeatures = FeatureProvider::getInstance()->getFeatures($this->group->getId());
		foreach ($currentFeatures as $feature)
		{
			if ($feature->isActive)
			{
				$features->add($feature->feature);
			}
		}

		return $features;
	}

	public function validateGroup(): Result
	{
		$result = new Result();

		if ($this->group->getType() !== Type::Group)
		{
			$result->addError(new Error('Only groups can be converted into collabs', self::ERROR_CODE_WRONG_TYPE));
		}

		if ((new FlowService())->doesGroupHaveFlows($this->group->getId()))
		{
			$result->addError(new Error('The group should not have flows', self::ERROR_CODE_HAS_FLOWS));
		}

		return $result;
	}
}