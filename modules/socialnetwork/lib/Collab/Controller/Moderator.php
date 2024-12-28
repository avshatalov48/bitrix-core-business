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
use Bitrix\Socialnetwork\Collab\Controller\Dto\Moderator\AddModeratorDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\Moderator\DeleteModeratorDto;
use Bitrix\Socialnetwork\Collab\Controller\Filter\BooleanPostFilter;
use Bitrix\Socialnetwork\Collab\Controller\Filter\IntranetUserFilter;
use Bitrix\Socialnetwork\Collab\Controller\Trait\GetCollabIdBySourceTrait;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;

class Moderator extends Controller
{
	use GetCollabIdBySourceTrait;

	protected int $userId;
	protected CollabMemberFacade $memberFacade;

	public function getAutoWiredParameters(): array
	{
		return [
			new Parameter(
				AddModeratorDto::class,
				function (): AddModeratorDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['groupId'] = $this->resolveCollabId($request, 'groupId');

					return AddModeratorDto::createFromRequest($requestData);
				}
			),
			new Parameter(
				DeleteModeratorDto::class,
				function (): DeleteModeratorDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['groupId'] = $this->resolveCollabId($request, 'groupId');

					return DeleteModeratorDto::createFromRequest($requestData);
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
	 * @restMethod socialnetwork.collab.Moderator.add
	 */
	public function addAction(AddModeratorDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (empty($dto->moderatorMembers))
		{
			return null;
		}

		$command = (new MembersCommand())
			->setMembers($dto->moderatorMembers)
			->setInitiatorId($this->userId)
			->setGroupId($dto->groupId)
		;

		$model = CollabModel::createFromId($dto->groupId)
			->setAddModeratorMembers($dto->moderatorMembers);

		$accessController = CollabAccessController::getInstance($this->userId);

		if (!$accessController->check(CollabDictionary::SET_MODERATOR, $model))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = $this->memberFacade->addModerators($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getCollab();
	}

	/**
	 * @restMethod socialnetwork.collab.Moderator.delete
	 */
	public function deleteAction(DeleteModeratorDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (empty($dto->moderatorMembers))
		{
			return null;
		}

		$command = (new MembersCommand())
			->setMembers($dto->moderatorMembers)
			->setInitiatorId($this->userId)
			->setGroupId($dto->groupId)
		;

		$model = CollabModel::createFromId($dto->groupId)
			->setDeleteModeratorMembers($dto->moderatorMembers);

		$accessController = CollabAccessController::getInstance($this->userId);

		if (!$accessController->check(CollabDictionary::EXCLUDE_MODERATOR, $model))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = $this->memberFacade->deleteModerators($command);
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
	}
}