<?php
namespace Bitrix\SocialNetwork\Integration\UI\EntitySelector;

use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\RecentItem;
use Bitrix\UI\EntitySelector\Tab;

/**
 * Class ProjectRolesProvider
 *
 * @package Bitrix\SocialNetwork\Integration\UI\EntitySelector
 */
class ProjectRolesProvider extends BaseProvider
{
	private $entityId = 'project-roles';

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options['projectId'] = $options['projectId'];
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function getItems(array $ids): array
	{
		return [];
	}

	public function getSelectedItems(array $ids): array
	{
		return [];
	}

	public function fillDialog(Dialog $dialog): void
	{
		$groupId = $this->getOption('projectId');

		$group = Workgroup::getById($groupId);

		if ($group && $group->isScrumProject())
		{
			$dialog->addItems($this->makeScrumRoles());
		}
		else
		{
			$dialog->addItems($this->makeRoles());
		}

		$dialog->addTab(new Tab([
			'id' => $this->entityId,
			'title' => Loc::getMessage('SES_PROJECT_ROLES_TAB_TITLE'),
			'stub' => true
		]));
	}

	private function makeScrumRoles(): array
	{
		$roles = [];

		$projectId = $this->getOption('projectId');

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_OWNER,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_SCRUM_OWNER_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		$roles[] = new Item(
			[
				'id' => $projectId . '_M',
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_SCRUM_MASTER_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_MODERATOR,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_SCRUM_MODERATOR_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_USER,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_SCRUM_EMPLOYER_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		return $roles;
	}

	private function makeRoles(): array
	{
		$roles = [];

		$projectId = $this->getOption('projectId');

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_OWNER,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_OWNER_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_MODERATOR,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_MODERATOR_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		$roles[] = new Item(
			[
				'id' => $projectId . '_' . SONET_ROLES_USER,
				'entityId' => $this->entityId,
				'entityType' => 'role',
				'title' => Loc::getMessage('SES_PROJECT_EMPLOYER_ROLE'),
				'tabs' => ['project-roles']
			]
		);

		return $roles;
	}
}