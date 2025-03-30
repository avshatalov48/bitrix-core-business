<?php

declare(strict_types=1);

namespace Bitrix\Rest\Repository\APAuth;

use Bitrix\Rest\Repository;
use Bitrix\Rest\APAuth\PasswordTable;
use Bitrix\Rest\APAuth;
use Bitrix\Rest\Entity;
use Bitrix\Rest\Entity\Collection\APAuth\PasswordCollection;
use Bitrix\Rest\Enum;
use Bitrix\Rest\Model;
use Bitrix\Rest;
use Bitrix\Rest\Contract;

class PasswordRepository implements Contract\Repository\APAuth\PasswordRepository
{
	public function create(Entity\APAuth\Password $password): Entity\APAuth\Password
	{
		$model = $this->mapEntityToModel($password);
		$result = $model->save();

		if (!$result->isSuccess())
		{
			throw new Repository\Exception\CreationFailedException('Failed to create password');
		}

		$password->setId($model->getId());

		return $password;
	}

	public function deleteById(int $id): bool
	{
		return PasswordTable::delete($id)->isSuccess();
	}

	public function getByType(Enum\APAuth\PasswordType $type): PasswordCollection
	{
		$collection = PasswordTable::query()
			->setSelect(['*'])
			->setFilter(['=TYPE' => $type->value])
			->fetchCollection();

		return $this->mapModelCollectionToEntityCollection($collection);
	}

	public function getById(int $id): ?Entity\APAuth\Password
	{
		$model = PasswordTable::getById($id)->fetchObject();

		if ($model)
		{
			return $this->mapModelToEntity($model);
		}

		return null;
	}

	private function mapModelToEntity(APAuth\EO_Password $model): Entity\APAuth\Password
	{
		return (new Model\Mapper\APAuth\Password())->mapModelToEntity($model);
	}

	private function mapModelCollectionToEntityCollection(
		APAuth\EO_Password_Collection $modelCollection
	): Entity\Collection\APAuth\PasswordCollection
	{
		return (new Model\Mapper\APAuth\PasswordCollection())->map($modelCollection);
	}

	private function mapEntityToModel(Entity\APAuth\Password $password): Rest\APAuth\EO_Password
	{
		return (new Model\Mapper\APAuth\Password())->mapEntityToModel($password);
	}
}
