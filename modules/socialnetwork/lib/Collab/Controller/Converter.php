<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\Socialnetwork\Collab\Controller\Dto\CollabGetDto;
use Bitrix\Socialnetwork\Collab\Controller\Dto\Converter\ConvertToCollabDto;
use Bitrix\Socialnetwork\Collab\Controller\Filter\IntranetUserFilter;
use Bitrix\Socialnetwork\Collab\Converter\Command\ConvertToCollabCommand;
use Bitrix\Socialnetwork\Collab\Converter\ConverterFeature;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\GroupDictionary;

class Converter extends Controller
{
	protected int $userId;
	protected CollabAccessController $accessController;
	protected Collab $collabController;

	protected function init(): void
	{
		parent::init();

		$this->userId = (int)CurrentUser::get()->getId();
		$this->accessController = CollabAccessController::getInstance($this->userId);
		$this->collabController = new Collab();
	}

	public function getAutoWiredParameters(): array
	{
		return [
			new Parameter(
				ConvertToCollabDto::class,
				function (): ConvertToCollabDto {
					$request = $this->getRequest();

					return ConvertToCollabDto::createFromRequest($request);
				}
			),
		];
	}

	public function configureActions(): array
	{
		return [
			'convertToCollab' => [
				'+prefilters' => [
					new IntranetUserFilter(),
				],
			],
		];
	}

	public function validateGroupAction(int $id): ?array
	{
		if (!ConverterFeature::isOn())
		{
			$this->addError(new Error('Feature is not available'));

			return null;
		}

		$group = Workgroup::getById($id);
		if (!($group instanceof Workgroup))
		{
			$this->addError(new Error('Group not found'));

			return null;
		}

		if (!GroupAccessController::can($this->userId, GroupDictionary::CONVERT, $id))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		/** @var ConvertToCollabCommand $command */
		$command = (new ConvertToCollabCommand())
			->setGroup($group)
			->setInitiatorId($this->userId)
		;

		$validationResult = $command->validateGroup();

		if (!$validationResult->isSuccess())
		{
			$this->addErrors($validationResult->getErrors());
		}

		return [
			'isValid' => $validationResult->isSuccess(),
		];
	}

	/**
	 * @restMethod socialnetwork.collab.Converter.convertToCollab
	 */
	public function convertToCollabAction(ConvertToCollabDto $dto): ?\Bitrix\Socialnetwork\Collab\Collab
	{
		if (!ConverterFeature::isOn())
		{
			$this->addError(new Error('Feature is not available'));

			return null;
		}

		$group = Workgroup::getById($dto->id);
		if (!($group instanceof Workgroup))
		{
			$this->addError(new Error('Group not found'));

			return null;
		}

		if (!GroupAccessController::can($this->userId, GroupDictionary::CONVERT, $dto->id))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$command = (new ConvertToCollabCommand())
			->setGroup($group)
			->setInitiatorId($this->userId)
		;

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.converter.service');
		/** @var ConvertToCollabCommand $command */
		$converterResult = $service->convert($command);

		if (!$converterResult->isSuccess())
		{
			$this->addErrors($converterResult->getErrors());

			return null;
		}

		$getDto = new CollabGetDto($converterResult->getEntityAfter()->getId());

		return $this->collabController->getAction($getDto);
	}
}
