<?php

declare(strict_types=1);

namespace Bitrix\Rest\Model\Mapper\APAuth;

use Bitrix\Rest;
use Bitrix\Rest\Entity;

class PasswordCollection
{
	public function map(Rest\APAuth\EO_Password_Collection $modelCollection): Entity\Collection\APAuth\PasswordCollection
	{
		$modelMapper = new Password();
		$passwordCollection = new Entity\Collection\APAuth\PasswordCollection();

		foreach ($modelCollection as $model)
		{
			$passwordCollection->add($modelMapper->mapModelToEntity($model));
		}

		return $passwordCollection;
	}
}
