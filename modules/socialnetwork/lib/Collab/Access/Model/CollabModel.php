<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Collab\Access\Model;

use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Site\Site;

final class CollabModel extends GroupModel
{
	protected array $members = [];
	protected array $addMembers = [];
	protected array $addModeratorMembers = [];
	protected array $addInvitedMembers = [];
	protected array $deleteMembers = [];
	protected array $deleteModeratorMembers = [];
	protected array $deleteInvitedMembers = [];

	public function getDeleteMembers(): array
	{
		return $this->deleteMembers;
	}

	public function setDeleteMembers(array $deleteMembers): CollabModel
	{
		$this->deleteMembers = $deleteMembers;

		return $this;
	}

	public function getDeleteInvitedMembers(): array
	{
		return $this->deleteInvitedMembers;
	}

	public function setDeleteInvitedMembers(array $deleteInvitedMembers): CollabModel
	{
		$this->deleteInvitedMembers = $deleteInvitedMembers;

		return $this;
	}

	public function setAddMembers(array $addMembers): CollabModel
	{
		$this->addMembers = $addMembers;

		return $this;
	}

	public function setAddModeratorMembers(array $addModeratorMembers): CollabModel
	{
		$this->addModeratorMembers = $addModeratorMembers;

		return $this;
	}

	public function setAddInvitedMembers(array $addInvitedMembers): CollabModel
	{
		$this->addInvitedMembers = $addInvitedMembers;

		return $this;
	}

	public function getDeleteModeratorMembers(): array
	{
		return $this->deleteModeratorMembers;
	}

	public function setDeleteModeratorMembers(array $deleteModeratorMembers): self
	{
		$this->deleteModeratorMembers = $deleteModeratorMembers;

		return $this;
	}

	public function getAddMembers(): array
	{
		return $this->addMembers;
	}

	public function getAddModeratorMembers(): array
	{
		return $this->addModeratorMembers;
	}

	public function getAddInvitedMembers(): array
	{
		return $this->addInvitedMembers;
	}

	public function getAllAddMembers(): array
	{
		return array_merge($this->getAddMembers(), $this->getAddModeratorMembers(), $this->getAddInvitedMembers());
	}

	public function hasAddMembers(): bool
	{
		return !empty($this->getAllAddMembers());
	}

	public function getMembers(): array
	{
		$this->members ??= $this->getDomainObject()?->getUserMemberIds() ?? [];

		return $this->members;
	}

	protected function getRegistry(): GroupRegistry
	{
		return CollabRegistry::getInstance();
	}

	protected function getDefaultSiteIds(): array
	{
		return Site::getInstance()->getCollabSiteIds();
	}
}