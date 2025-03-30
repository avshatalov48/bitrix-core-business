<?php

declare(strict_types=1);

namespace Bitrix\Rest\Model\Mapper;

use Bitrix\Rest\Entity;
use Bitrix\Rest\Preset\EO_Integration;

class Integration
{
	public function mapModelToEntity(EO_Integration $model): Entity\Integration
	{
		return new Entity\Integration(
			elementCode: $model->getElementCode(),
			title: $model->getTitle(),
			id: $model->getId(),
			userId: $model->getUserId(),
			passwordId: $model->getPasswordId(),
			appId: $model->getAppId(),
			scope: $model->getScope(),
			widgetList: $model->getWidgetList(),
			outgoingEvents: $model->getOutgoingEvents(),
			outgoingNeeded: is_null($model->getOutgoingNeeded()) ? null : ($model->getOutgoingNeeded() === 'Y'),
			outgoingHandler: $model->getOutgoingHandlerUrl(),
			widgetNeeded: is_null($model->getWidgetNeeded()) ? null : ($model->getWidgetNeeded() === 'Y'),
			widgetHandler: $model->getWidgetHandlerUrl(),
			applicationToken: $model->getApplicationToken(),
			applicationNeeded: is_null($model->getApplicationNeeded()) ? null : ($model->getApplicationNeeded() === 'Y'),
			onlyApi: is_null($model->getApplicationOnlyApi()) ? null : ($model->getApplicationOnlyApi() === 'Y'),
			botId: $model->getBotId(),
			botHandlerUrl: $model->getBotHandlerUrl(),
		);
	}

	public function mapEntityToModel(Entity\Integration $integration): EO_Integration
	{
		$model = new EO_Integration();
		$model->setId($integration->getId());
		$model->setElementCode($integration->getElementCode());
		$model->setTitle($integration->getTitle());
		$model->setUserId($integration->getUserId());
		$model->setPasswordId($integration->getPasswordId());
		$model->setAppId($integration->getAppId());
		$model->setScope($integration->getScope());
		$model->setWidgetList($integration->getWidgetList());
		$model->setOutgoingEvents($integration->getOutgoingEvents());
		$model->setOutgoingNeeded(is_null($integration->getOutgoingNeeded()) ? null : ($integration->getOutgoingNeeded() ? 'Y' : 'N'));
		$model->setOutgoingHandlerUrl($integration->getOutgoingHandler());
		$model->setWidgetNeeded(is_null($integration->getWidgetNeeded()) ? null : ($integration->getWidgetNeeded() ? 'Y' : 'N'));
		$model->setWidgetHandlerUrl($integration->getWidgetHandler());
		$model->setApplicationToken($integration->getApplicationToken());
		$model->setApplicationNeeded(is_null($integration->getApplicationNeeded()) ? null : ($integration->getApplicationNeeded() ? 'Y' : 'N'));
		$model->setApplicationOnlyApi(is_null($integration->getOnlyApi()) ? null : ($integration->getOnlyApi() ? 'Y' : 'N'));
		$model->setBotId($integration->getBotId());
		$model->setBotHandlerUrl($integration->getBotHandlerUrl());

		return $model;
	}

	public function mapArrayToEntity(array $data): Entity\Integration
	{
		return new Entity\Integration(
			elementCode: $data['ELEMENT_CODE'] ?? null,
			title: $data['TITLE'],
			id: isset($data['ID']) ? (int)$data['ID'] : null,
			userId: isset($data['USER_ID']) ? (int)$data['USER_ID'] : null,
			passwordId: isset($data['PASSWORD_ID']) ? (int)$data['PASSWORD_ID'] : null,
			appId: isset($data['APP_ID']) ? (int)$data['APP_ID'] : null,
			scope: isset($data['SCOPE']) && is_array($data['SCOPE']) ? $data['SCOPE'] : null,
			widgetList: isset($data['WIDGET_LIST']) && is_array($data['WIDGET_LIST']) ? $data['WIDGET_LIST'] : null,
			outgoingEvents: isset($data['OUTGOING_EVENTS']) && is_array($data['OUTGOING_EVENTS']) ? $data['OUTGOING_EVENTS'] : null,
			outgoingNeeded: empty($data['OUTGOING_NEEDED']) ? null : $data['OUTGOING_NEEDED'] === 'Y',
			outgoingHandler: $data['OUTGOING_HANDLER_URL'] ?? null,
			widgetNeeded: empty($data['WIDGET_NEEDED']) ? null : $data['WIDGET_NEEDED'] === 'Y',
			widgetHandler: $data['WIDGET_HANDLER_URL'] ?? null,
			applicationToken: $data['APPLICATION_TOKEN'] ?? null,
			applicationNeeded: empty($data['APPLICATION_NEEDED']) ? null : $data['APPLICATION_NEEDED'] === 'Y',
			onlyApi: empty($data['APPLICATION_ONLY_API']) ? null : $data['APPLICATION_ONLY_API'] === 'Y',
			botId: isset($data['BOT_ID']) ? (int)$data['BOT_ID'] : null,
			botHandlerUrl: $data['BOT_HANDLER_URL'] ?? null,
		);
	}
}