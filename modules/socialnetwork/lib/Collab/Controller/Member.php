<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;
use Bitrix\Socialnetwork\Collab\Control\Member\CollabMemberFacade;
use Bitrix\Socialnetwork\Collab\Controller\Dto\Member\AddMemberDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\Member\DeleteMemberDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\Member\LeaveMemberDto;
use Bitrix\Socialnetwork\Collab\Controller\Filter\BooleanPostFilter;
use Bitrix\Socialnetwork\Collab\Controller\Filter\IntranetUserFilter;
use Bitrix\Socialnetwork\Collab\Controller\Trait\GetCollabIdBySourceTrait;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;

class Member extends Controller
{
	use GetCollabIdBySourceTrait;

	protected int $userId;
	protected CollabMemberFacade $memberFacade;
	protected CollabAccessController $accessController;

	public function getAutoWiredParameters(): array
	{
		return [
			new Parameter(
				AddMemberDto::class,
				function (): AddMemberDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['groupId'] = $this->resolveCollabId($request, 'groupId');

					return AddMemberDto::createFromRequest($requestData);
				}
			),
			new Parameter(
				DeleteMemberDto::class,
				function (): DeleteMemberDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['groupId'] = $this->resolveCollabId($request, 'groupId');

					return DeleteMemberDto::createFromRequest($requestData);
				}
			),
			new Parameter(
				LeaveMemberDto::class,
				function (): LeaveMemberDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['groupId'] = $this->resolveCollabId($request, 'groupId');

					return LeaveMemberDto::createFromRequest($requestData);
				}
			),
		];
	}

	public function configureActions(): array
	{
		return [
			'add' => [
				'+prefilters' => [
					new BooleanPostFilter(),
				],
			],
			'leave' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
			'delete' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			]
		];
	}

	/**
	 * @restMethod socialnetwork.collab.Member.add
	 */
	public function addAction(AddMemberDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (empty($dto->members))
		{
			return null;
		}

		$command = (new MembersCommand())
			->setMembers($dto->members)
			->setInitiatorId($this->userId)
			->setGroupId($dto->groupId);

		$model = CollabModel::createFromId($dto->groupId)
			->setAddMembers($dto->members);

		if (!$this->accessController->check(CollabDictionary::INVITE, $model))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = $this->memberFacade->add($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getCollab();
	}

	/**
	 * @restMethod socialnetwork.collab.Member.delete
	 */
	public function deleteAction(DeleteMemberDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (empty($dto->members))
		{
			return null;
		}

		$command = (new MembersCommand())
			->setMembers($dto->members)
			->setInitiatorId($this->userId)
			->setGroupId($dto->groupId);

		$model = CollabModel::createFromId($dto->groupId)
			->setDeleteMembers($dto->members);

		if (!$this->accessController->check(CollabDictionary::EXCLUDE, $model))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = $this->memberFacade->delete($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getCollab();
	}

	public function leaveAction(LeaveMemberDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		$deleteMembers = ['U' . $this->userId];

		$command = (new MembersCommand())
			->setMembers($deleteMembers)
			->setInitiatorId($this->userId)
			->setGroupId($dto->groupId);

		$model = CollabModel::createFromId($dto->groupId)
			->setDeleteMembers($deleteMembers);

		if (!$this->accessController->check(CollabDictionary::LEAVE, $model))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = $this->memberFacade->delete($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getCollab();
	}

	protected function init(): void
	{
		parent::init();

		$this->memberFacade = ServiceLocator::getInstance()->get('socialnetwork.collab.member.facade');
		$this->userId = (int)CurrentUser::get()->getId();
		$this->accessController = CollabAccessController::getInstance($this->userId);
	}
}