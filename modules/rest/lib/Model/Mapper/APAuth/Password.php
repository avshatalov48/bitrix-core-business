<?php

declare(strict_types=1);

namespace Bitrix\Rest\Model\Mapper\APAuth;

use Bitrix\Rest\Entity;
use Bitrix\Rest;
use Bitrix\Rest\Enum;

class Password
{
	public function mapModelToEntity(Rest\APAuth\EO_Password $model): Entity\APAuth\Password
	{
		return new Entity\APAuth\Password(
			id: $model->getId(),
			passwordString: $model->getPassword(),
			userId: $model->getUserId(),
			type: Enum\APAuth\PasswordType::from($model->getType()),
			title: $model->getTitle(),
			comment: $model->getComment(),
			createdAt: $model->getDateCreate(),
		);
	}

	public function mapEntityToModel(Entity\APAuth\Password $password): Rest\APAuth\EO_Password
	{
		$model = new Rest\APAuth\EO_Password();
		$model->setPassword($password->getPasswordString());
		$model->setTitle($password->getTitle());
		$model->setComment($password->getComment());
		$model->setUserId($password->getUserId());
		$model->setType($password->getType()->value);
		$model->setDateCreate($password->getCreatedAt());

		return $model;
	}
}
