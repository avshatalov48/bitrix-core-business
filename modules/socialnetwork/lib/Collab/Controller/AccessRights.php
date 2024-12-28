<?php

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
use Bitrix\Socialnetwork\Collab\Control\CollabService;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabGetDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabUpdateDto;
use Bitrix\Socialnetwork\Collab\Controller\Filter\IntranetUserFilter;
use Bitrix\Socialnetwork\Collab\Controller\Trait\GetCollabIdBySourceTrait;
use Bitrix\Socialnetwork\Control\Decorator\AccessDecorator;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Provider\FeatureProvider;

class AccessRights extends Controller
{
	use GetCollabIdBySourceTrait;

	protected CollabService $service;
	protected GroupRegistry $registry;

	protected int $userId;

	public function getAutoWiredParameters(): array
	{
		return [
			new Parameter(
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
		];
	}

	public function configureActions(): array
	{
		return [
			'saveRights' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
			'getAddForm' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
			'getEditForm' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
		];
	}

	/**
	 * @restMethod socialnetwork.collab.AccessRights.saveRights
	 */
	public function saveRightsAction(CollabUpdateDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
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

		return $this->forward(Collab::class, 'get');
	}

	/**
	 * @restMethod socialnetwork.collab.AccessRights.getEditForm
	 */
	public function getAddFormAction(): ?array
	{
		if (!CollabAccessController::can($this->userId, CollabDictionary::CREATE))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$featureProvider = FeatureProvider::getInstance();

		return [
			'ownerId' => $this->userId,
			'permissionsLabels' => $featureProvider->getPermissionLabels(),
			'rightsPermissionsLabels' => $featureProvider->getRightsPermissionLabels(),
			'optionsLabels' => $featureProvider->getOptionLabels(),
			'permissions' => $featureProvider->getAllDefaultPermissions(),
		];
	}

	/**
	 * @restMethod socialnetwork.collab.AccessRights.getEditForm
	 */
	public function getEditFormAction(CollabGetDto $dto): ?array
	{
		if (!CollabAccessController::can($this->userId, CollabDictionary::UPDATE, $dto->id))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$collab = $this->forward(Collab::class, 'get');
		if ($collab === null)
		{
			return null;
		}

		$data = $collab->toJson();
		$data['rightsPermissionsLabels'] = FeatureProvider::getInstance()->getRightsPermissionLabels();
		$data['permissionsLabels'] = FeatureProvider::getInstance()->getPermissionLabels();
		$data['optionsLabels'] = FeatureProvider::getInstance()->getOptionLabels();

		return $data;
	}

	protected function init(): void
	{
		parent::init();

		$this->service = ServiceLocator::getInstance()->get('socialnetwork.collab.service');
		$this->registry = GroupRegistry::getInstance();

		$this->userId = (int)CurrentUser::get()->getId();
	}
}
