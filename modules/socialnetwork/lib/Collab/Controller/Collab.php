<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\Engine\AutoWire\ValidationParameter;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\Socialnetwork\Collab\Control\CollabResult;
use Bitrix\Socialnetwork\Collab\Control\CollabService;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabAddCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Decorator\CorrectModeratorDecorator;
use Bitrix\Socialnetwork\Collab\Control\Decorator\RequirementDecorator;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabDeleteDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabAddDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabGetDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabUpdateDto;
use Bitrix\Socialnetwork\Collab\Controller\Filter\FeatureFilter;
use Bitrix\Socialnetwork\Collab\Controller\Filter\IntranetUserFilter;
use Bitrix\Socialnetwork\Collab\Controller\Trait\GetCollabIdBySourceTrait;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Control\Decorator\AccessDecorator;
use Bitrix\Socialnetwork\Provider\FileProvider;
use Bitrix\Socialnetwork\Provider\UserProvider;

class Collab extends Controller
{
	use GetCollabIdBySourceTrait;

	protected CollabService $service;
	protected CollabRegistry $registry;
	protected CollabProvider $provider;

	protected int $userId;

	public function getAutoWiredParameters(): array
	{
		return [
			new Parameter(
				CollabAddDto::class,
				function (): CollabAddDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['id'] = $this->resolveCollabId($request, 'id');

					return CollabAddDto::createFromRequest($requestData);
				}
			),
			new ValidationParameter(
				CollabUpdateDto::class,
				function (): CollabUpdateDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['id'] = $this->resolveCollabId($request, 'id');

					return CollabUpdateDto::createFromRequest($requestData);
				}
			),
			new ValidationParameter(
				CollabGetDto::class,
				function (): CollabGetDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['id'] = $this->resolveCollabId($request, 'id');

					return CollabGetDto::createFromRequest($requestData);
				}
			),
			new ValidationParameter(
				CollabDeleteDto::class,
				function (): CollabDeleteDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['id'] = $this->resolveCollabId($request, 'id');

					return CollabDeleteDto::createFromRequest($requestData);
				}
			),
		];
	}

	public function configureActions(): array
	{
		return [
			'add' => [
				'+prefilters' => [
					new IntranetUserFilter(),
					new FeatureFilter(),
				],
			],
			'update' => [
				'+prefilters' => [
					new IntranetUserFilter(),
					new FeatureFilter(),
				],
			],
			'delete' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
		];
	}

	/**
	 * @restMethod socialnetwork.collab.collab.get
	 */
	public function getAction(CollabGetDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (!CollabAccessController::can($this->userId, CollabDictionary::VIEW, $dto->id))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$collab = $this->provider->getCollab((int)$dto->id);
		if ($collab === null)
		{
			return null;
		}

		$memberIds = array_keys($collab->getMemberIdsWithRole());
		$users = UserProvider::getInstance()->enrich($memberIds);

		$collab->setAdditionInfo('users', $users);

		$imageId = $collab->getImageId();
		$file = FileProvider::getInstance()->get($imageId);

		$collab->setAdditionInfo('image', $file);

		return $collab;
	}

	/**
	 * @restMethod socialnetwork.collab.collab.add
	 */
	public function addAction(CollabAddDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		try
		{
			$command = CollabAddCommand::createFromArray($dto)->setInitiatorId($this->userId);
		}
		catch (ArgumentException $e)
		{
			$this->addError(Error::createFromThrowable($e));

			return null;
		}

		/** @var CollabResult $result */
		$result = (new RequirementDecorator(new AccessDecorator(new CorrectModeratorDecorator($this->service))))->add($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$collabId = (int)$result->getCollab()?->getId();
		$getDto = new CollabGetDto($collabId);

		return $this->getAction($getDto);
	}

	/**
	 * @restMethod socialnetwork.collab.collab.update
	 */
	public function updateAction(CollabUpdateDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		try
		{
			$command = CollabUpdateCommand::createFromArray($dto)->setInitiatorId($this->userId);
		}
		catch (ArgumentException $e)
		{
			$this->addError(Error::createFromThrowable($e));

			return null;
		}

		$result = (new AccessDecorator($this->service))->update($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$collabId = (int)$result->getCollab()?->getId();
		$getDto = new CollabGetDto($collabId);

		return $this->getAction($getDto);
	}

	/**
	 * @restMethod socialnetwork.collab.collab.delete
	 */
	public function deleteAction(CollabDeleteDto $dto): ?bool
	{
		try
		{
			$command = CollabDeleteCommand::createFromArray($dto)->setInitiatorId($this->userId);
		}
		catch (ArgumentException $e)
		{
			$this->addError(Error::createFromThrowable($e));

			return null;
		}

		$result = (new AccessDecorator($this->service))->delete($command);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return true;
	}

	protected function init(): void
	{
		parent::init();

		$this->service = ServiceLocator::getInstance()->get('socialnetwork.collab.service');
		$this->registry = CollabRegistry::getInstance();
		$this->provider = CollabProvider::getInstance();

		$this->userId = (int)CurrentUser::get()->getId();
	}
}