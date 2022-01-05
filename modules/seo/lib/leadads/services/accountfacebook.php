<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Seo\LeadAds\Account;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\Retargeting\Paginator;

class AccountFacebook extends Account
{
	public const TYPE_CODE = LeadAds\Service::TYPE_FACEBOOK;

	public const URL_ACCOUNT_LIST = 'https://www.facebook.com/bookmarks/pages';

	public const URL_INFO = 'https://www.facebook.com/business/a/lead-ads';

	protected static $listRowMap = array(
		'ID' => 'ID',
		'NAME' => 'NAME',
	);

	/**
	 * @return \Bitrix\Seo\Retargeting\Response|mixed|null
	 */
	public function getList()
	{
		$paginator = new Paginator(
			$this->getRequest(),
			[
				'methodName' => 'leadads.accounts.get',
				'parameters' => [
					'fields' => ['id','name','category','access_token','tasks'],
					"params" => [
						"limit" => 50
					]
				],
			]
		);

		$result = null;
		$data = [];

		foreach ($paginator as $request)
		{
			if (!$request->isSuccess())
			{
				return $request;
			}

			foreach ($request->getData() as $item)
			{
				if (array_intersect($item['tasks'] ?? [], ['MODERATE', 'CREATE_CONTENT', 'MANAGE']))
				{
					$data[] = $item;
				}
			}
			$result = $request;
		}

		!$result?:$result->setData($data);

		return $result;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getProfile(): ?array
	{
		$response = $this->getRequest()->send([
			'methodName' => 'leadads.profile.get',
			'parameters' => [
				'fields' => ['id','name','picture','link']
			]
		]);

		if ($response->isSuccess() && $data = $response->fetch())
		{

			return [
				'ID' => $data['ID'],
				'NAME' => $data['NAME'],
				'LINK' => $data['LINK'],
				'PICTURE' => $data['PICTURE'] ? $data['PICTURE']['data']['url'] : null,
			];
		}

		return null;
	}
}