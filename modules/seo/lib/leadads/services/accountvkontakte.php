<?php

namespace Bitrix\Seo\LeadAds\Services;

use Bitrix\Seo\LeadAds;
use Bitrix\Seo\LeadAds\Account;
use Bitrix\Seo\Retargeting\Response;

/**
 * Class AccountVkontakte
 *
 * @package Bitrix\Seo\LeadAds\Services
 */
class AccountVkontakte extends Account
{
	public const TYPE_CODE = LeadAds\Service::TYPE_VKONTAKTE;

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
			'parameters' => [
				'fields' => 'photo_50,screen_name'
			]
		]);

		if ($response->isSuccess() && $data = $response->fetch())
		{

			return [
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://vk.com/' . $data['SCREEN_NAME'],
				'PICTURE' => $data['PHOTO_50'],
			];
		}

		return null;
	}
}