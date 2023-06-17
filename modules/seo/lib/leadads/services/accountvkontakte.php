<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Random;
use Bitrix\Seo\WebHook;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\LeadAds\Account;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Service;
use PhpParser\Node\Expr\Isset_;

/**
 * Class AccountVkontakte
 *
 * @package Bitrix\Seo\LeadAds\Services
 */
class AccountVkontakte extends Account
{
	public const TYPE_CODE = LeadAds\Service::TYPE_VKONTAKTE;

	public const RESOURCE_LEAD = 'LEAD';

	public const URL_ACCOUNT_LIST = 'https://vk.com/groups?tab=admin';

	public const URL_INFO = 'https://vk.com/page-19542789_53868676';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	/**
	 * Get row by id.
	 *
	 * @param string $id ID.
	 *
	 * @return array|mixed|null
	 */
	public function getRowById(string $id)
	{
		$list = $this->getList();
		while ($row = $list->fetch())
		{
			if ($row['ID'] === $id)
			{
				return $row;
			}
		}

		return null;
	}

	/**
	 * Get list.
	 *
	 * @return Response
	 */
	public function getList(): Response
	{
		// https://vk.com/dev/groups.get
		$response = $this->getRequest()->send(array(
			'methodName' => 'leadads.groups.list',
			'parameters' => array(
				'fields' => 'id,name',
				'extended' => 1,
				'filter' => 'admin'
			)
		));
		$items = $response->getData();
		$items = (!empty($items['items']) && is_array($items['items'])) ? $items['items'] : [];

		$response->setData($items);

		return $response;
	}

	/**
	 * Get profile.
	 *
	 * @return array|null
	 */
	public function getProfile(): ?array
	{
		$response = $this->getRequest()->send([
			'methodName' => 'leadads.profile',
			'parameters' => []
		]);

		if ($response->isSuccess() && $data = $response->fetch())
		{
			$result = [
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
			];

			$result['LINK'] = 'https://ads.vk.com/hq/leadforms/';
			$result['PICTURE'] = (Context::getCurrent()->getRequest()->isHttps() ? 'https' : 'http')
				. '://'
				.  Context::getCurrent()->getServer()->getHttpHost() . '/bitrix/images/seo/integration/vklogo.svg';

			return $result;
		}

		return null;
	}

	public function checkNewAuthInfo(): bool
	{
		$response = $this->checkAuthInfo();
		if (!$response->isSuccess())
		{
			return true;
		}
		$data = $response->getData();
		if (isset($data['hasAuthInfo']) && isset($data['hasProfile']))
		{
			if (!$data['hasAuthInfo'])
			{
				return true;
			}

			return $data['hasProfile'];
		}

		return true;
	}

	public function logout()
	{
		$response = $this->unsubscribeToLeadAdding();
		if ($response->isSuccess())
		{
			$data = $response->getData();
			if (isset($data['id']) && isset($data['type']))
			{
				$this->unregisterCode((int)$response->getData()['id'], $response->getData()['type']);
			}

			if (isset($data['form_ids']) && is_array($data['form_ids']))
			{
				return (new Result())->setData(['formIds' => $data['form_ids']]);
			}
		}

		return new Result();
	}

	public function loginCompletion()
	{
		$response = $this->subcribeToLeadAdding(self::RESOURCE_LEAD);
		if ($response->isSuccess())
		{
			return $this->registerCode((int)$response->getData()['id'], self::RESOURCE_LEAD);
		}

		return (new Result())->addError(new Error('Failed to perform all actions after authorization'));
	}

	public function hasPageAccount()
	{
		return false;
	}

	protected function subcribeToLeadAdding(string $resource): Response
	{
		$remoteServiceUrl = Service::SERVICE_URL;
		$webhookQueryParams = http_build_query(
			[
				'code' => LeadAds\Service::getEngineCode(static::TYPE_CODE),
				'action' => 'web_hook',
			]
		);

		return $this->getRequest()->send([
			'methodName' => 'leadads.subscribe.lead.add',
			'parameters' => [
				'resource' => $resource,
				'callback_url' => "{$remoteServiceUrl}/register/?{$webhookQueryParams}",
			]
		]);
	}

	private function registerCode(int $confirmCode, string $resource)
	{
		$response = new Retargeting\Services\ResponseVkontakte();

		$isRegistered = $this->registerWebHook($confirmCode,
			[
				'SECURITY_CODE' => Random::getString(32),
				'CONFIRMATION_CODE' => $resource,
			]
		);

		if (!$isRegistered)
		{
			$response->addError(new Error('Can not register web hook.'));

			return $response;
		}

		return $response;
	}

	protected function registerWebHook($confirmCode, array $parameters = []): bool
	{
		return WebHook\Service::create(
			\Bitrix\Seo\LeadAds\Service::getEngineCode(static::TYPE_CODE),
			$confirmCode
		)->register($parameters);
	}

	protected function unsubscribeToLeadAdding()
	{
		return $this->getRequest()->send([
			'methodName' => 'leadads.unsubscribe.lead.add',
			'parameters' => []
		]);
	}

	protected function unregisterCode(int $externalId, string $type)
	{
		$webHook = $this->getWebHookByExternalId($externalId, $type);
		if ($webHook)
		{
			WebHook\Internals\WebHookTable::delete($webHook['ID']);
		}

		return new Result();
	}

	protected function getWebHookByExternalId($externalId, $type)
	{
		return WebHook\Internals\WebHookTable::getRow([
			'filter' => [
				'EXTERNAL_ID' => $externalId,
				'TYPE' => $type,
			]
		]);
	}

	protected function checkAuthInfo()
	{
		return $this->getRequest()->send([
			'methodName' => 'leadads.account.check.auth',
			'parameters' => []
		]);
	}
}
