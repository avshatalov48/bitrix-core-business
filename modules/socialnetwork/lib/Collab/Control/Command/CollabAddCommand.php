<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command;

use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabFeatures;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabFeaturesPermissions;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabOptions;
use Bitrix\Socialnetwork\Collab\Control\Command\ValueObject\CollabSiteIds;
use Bitrix\Socialnetwork\Collab\Control\Mapper\Field\InitiatePermissionMapper;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\Override;
use Bitrix\Socialnetwork\Control\Command\ValueObject\Features;
use Bitrix\Socialnetwork\Control\Command\ValueObject\FeaturesPermissions;
use Bitrix\Socialnetwork\Control\Command\ValueObject\SiteIds;
use Bitrix\Socialnetwork\Control\Enum\ViewMode;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\Control\Mapper\Field\ViewModeMapper;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

/**
 * @method self setOptions(CollabOptions $options)
 * @method CollabOptions getOptions()
 */
#[AccessController(CollabAccessController::class)]
class CollabAddCommand extends AddCommand
{
	#[Map('VISIBLE', ViewModeMapper::class)]
	#[Map('OPENED', ViewModeMapper::class)]
	protected ViewMode $viewMode = ViewMode::SECRET;

	#[Map('TYPE')]
	protected Type $type = Type::Collab;

	#[Validatable]
	#[Override(CollabFeatures::class)]
	protected Features $features;

	#[Override(CollabFeaturesPermissions::class)]
	protected FeaturesPermissions $permissions;

	#[Validatable]
	#[Map('SITE_ID')]
	#[Override(CollabSiteIds::class)]
	protected SiteIds $siteIds;

	#[Validatable]
	#[Map('INITIATE_PERMS', InitiatePermissionMapper::class)]
	protected CollabOptions $options;

	public function addOption(AbstractOption $option): static
	{
		if (!isset($this->options))
		{
			$this->options = new CollabOptions();
		}

		$this->options->addOption($option);

		return $this;
	}
}
