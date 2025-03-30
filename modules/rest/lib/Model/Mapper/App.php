<?php

declare(strict_types=1);

namespace Bitrix\Rest\Model\Mapper;

use Bitrix\Rest\Entity;
use Bitrix\Rest\EO_App;

class App
{
	public function mapModelToEntity(EO_App $model): Entity\App
	{
		return new Entity\App(
			clientId: $model->getClientId(),
			code: $model->getCode(),
			active: $model->getInstalled(),
			installed: $model->getInstalled(),
			url: $model->getUrl(),
			scope: $model->getScope(),
			status: $model->getStatus(),
			id: $model->getId(),
			urlDemo: $model->getUrlDemo(),
			urlInstall: $model->getUrlInstall(),
			version: $model->getVersion(),
			dateFinish: $model->getDateFinish(),
			isTrialled: $model->getIsTrialed(),
			sharedKey: $model->getSharedKey(),
			clientSecret: $model->getClientSecret(),
			appName: $model->getAppName(),
			access: $model->getAccess(),
			aplicationToken: $model->getApplicationToken(),
			mobile: $model->getMobile(),
			userInstall: $model->getUserInstall(),
			urlSettings: $model->getUrlSettings()
		);
	}

	public function mapEntityToModel(Entity\App $application): EO_App
	{
		$model = new EO_App();
		$model->setId($application->getId());
		$model->setClientId($application->getClientId());
		$model->setCode($application->getCode());
		$model->setActive($application->isActive());
		$model->setInstalled($application->isInstalled());
		$model->setUrl($application->getUrl());
		$model->setScope($application->getScope());
		$model->setStatus($application->isStatus());
		$model->setUrlDemo($application->getUrlDemo());
		$model->setUrlInstall($application->getUrlInstall());
		$model->setVersion($application->getVersion());
		$model->setDateFinish($application->getDateFinish());
		$model->setIsTrialed($application->getIsTrialled());
		$model->setSharedKey($application->getSharedKey());
		$model->setClientSecret($application->getClientSecret());
		$model->setAppName($application->getAppName());
		$model->setAccess($application->getAccess());
		$model->setApplicationToken($application->getAplicationToken());
		$model->setMobile($application->getMobile());
		$model->setUserInstall($application->getUserInstall());
		$model->setUrlSettings($application->getUrlSettings());

		return $model;
	}
}