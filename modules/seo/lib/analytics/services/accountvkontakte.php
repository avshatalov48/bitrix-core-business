<?

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Analytics\Internals\Page;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting\Services\ResponseVkontakte;
use Bitrix\Seo\Retargeting\IRequestDirectly;

class AccountVkontakte extends \Bitrix\Seo\Analytics\Account implements IRequestDirectly
{
	const TYPE_CODE = 'vkontakte';

	const MAX_ADS_EDIT = 20;
	const CURRENCY_CODE = 'RUB';

	protected static $listRowMap = array(
		'ID' => 'ACCOUNT_ID',
		'NAME' => 'NAME',
	);

	public function getList()
	{
		// https://vk.com/dev/ads.getAccounts

		$result =  $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'ads.getAccounts',
		]);
		if ($result->isSuccess())
		{
			$list = [];
			while ($item = $result->fetch())
			{
				if ($item['ACCOUNT_TYPE'] === 'general')
				{
					$list[] = $item;
				}
			}
			$result->setData($list);
		}

		return $result;
	}

	public function getProfile()
	{
		$response = $this->getRequest()->send(array(
			'method' => 'GET',
			'endpoint' => 'users.get',
			'fields' => array(
				//'user_ids' => array(),
				'fields' => 'photo_50,screen_name'
			)
		));


		if ($response->isSuccess())
		{
			$data = $response->fetch();
			return array(
				'ID' => $data['ID'],
				'NAME' => $data['FIRST_NAME'] . ' ' . $data['LAST_NAME'],
				'LINK' => 'https://vk.com/' . $data['SCREEN_NAME'],
				'PICTURE' => $data['PHOTO_50'],
			);
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param mixed $accountId VK Ad Account Id.
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Response
	 */
	public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		$result = new ResponseVkontakte();
		$fields = [
			'account_id' => $accountId,
			'ids_type' => 'office',
			'ids' => $accountId,
		];

		if($dateFrom && $dateTo)
		{
			$fields['period'] = 'day';
			$fields['date_from'] = $dateFrom->format('Y-m-d');
			$fields['date_to'] = $dateTo->format('Y-m-d');
		}
		else
		{
			$fields['period'] = 'overall';
			$fields['date_from'] = '0';
			$fields['date_to'] = '0';
		}
		$response = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'ads.getStatistics',
			'fields' => $fields,
		]);
		if($response->isSuccess())
		{
			$data = $response->getData();
			if (isset($data[0]))
			{
				$data = $data[0];
			}
			$expenses = new Expenses();
			foreach($data['stats'] as $stat)
			{
				$expenses->add([
					'impressions' => $stat['impressions'],
					'clicks' => $stat['clicks'],
					'actions' => $stat['clicks'],
					'spend' => $stat['spent'],
					'currency' => static::CURRENCY_CODE,
				]);
			}
			$result->setData(['expenses' => $expenses]);
		}
		else
		{
			$result->addErrors($response->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function hasPublicPages()
	{
		return true;
	}

	/**
	 * @param $accountId
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getPublicPages($accountId)
	{
		$adsLayoutResult = $this->getAdsLayout($accountId);
		if(!$adsLayoutResult->isSuccess())
		{
			return $adsLayoutResult;
		}
		$groupIDs = [];
		$ads = $adsLayoutResult->getData();
		foreach($ads as $ad)
		{
			if(isset($ad['group_id']))
			{
				$groupIDs[] = $ad['group_id'];
			}
		}

		$response = $this->getGroups($groupIDs);
		if(!$response->isSuccess())
		{
			return $response;
		}
		$result = [];
		$groups = $response->getData();
		foreach($groups as $page)
		{
			$result[] = new Page([
				'id' => $page['id'],
				'name' => $page['name'],
				'about' => $page['description'],
				'image' => $page['photo_200'],
				'phone' => $page['phone'],
			]);
		}
		$response->setData($result);

		return $response;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		$result = new ResponseVkontakte();
		if(empty($params))
		{
			return $result;
		}
		$result = $this->getAdsLayout($accountId);
		if(!$result->isSuccess() || empty($result->getData()))
		{
			return $result;
		}

		$data = [];
		$ads = $result->getData();
		$groupIDs = $postIDs = [];
		foreach($ads as $ad)
		{
			if(isset($ad['group_id']) && in_array($ad['group_id'], $publicPageIds))
			{
				$groupIDs[] = $ad['group_id'];
			}
			if(isset($ad['post_id']))
			{
				$postIDs[] = $ad['post_id'];
			}
			if(!empty($params['url_tags']) && isset($ad['link_domain']) && !empty($ad['link_domain']) && !empty($ad['link_url']))
			{
				$url = new Uri($ad['link_url']);
				$url->addParams($params['url_tags']);
				$data[] = [
					'ad_id' => $ad['id'],
					'link_url' => $url->getUri(),
				];
			}

			if(count($data) == self::MAX_ADS_EDIT)
			{
				$editAdsResult = $this->editAds($accountId, $data);
				if(!$editAdsResult->isSuccess())
				{
					$result->addErrors($editAdsResult->getErrors());
				}
				$data = [];
			}
		}

		if(!empty($data))
		{
			$editAdsResult = $this->editAds($accountId, $data);
			if(!$editAdsResult->isSuccess())
			{
				$result->addErrors($editAdsResult->getErrors());
			}
		}

		//edit groups
		if(!empty($groupIDs))
		{
			$result = $this->editGroupAnalyticParams($groupIDs, $params);
			if(!$result->isSuccess())
			{
				return $result;
			}
		}
		//edit posts
		if(!empty($postIDs))
		{
			$result = $this->editPostAnalyticParams($postIDs, $params);
		}

		return $result;
	}

	/**
	 * @param $accountId
	 * @param array $data
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function editAds($accountId, array $data)
	{
		return $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'ads.updateAds',
			'fields' => [
				'account_id' => $accountId,
				'data' => Json::encode($data)
			]
		]);
	}

	/**
	 * @param $accountId
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getAdsLayout($accountId)
	{
		$fields = [
			'account_id' => $accountId,
			'include_deleted' => '0',
		];

		$response = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'ads.getAdsLayout',
			'fields' => $fields,
		]);

		if($response->isSuccess())
		{
			$ads = $response->getData();
			foreach($ads as &$ad)
			{
				if(isset($ad['link_url']))
				{
					$parsedData = $this->parseVkUrl($ad['link_url']);
					if($parsedData)
					{
						$ad += $parsedData;
					}
				}
			}
			$response->setData($ads);
		}

		return $response;
	}

	/**
	 * @param array $groupIDs
	 * @param array $params
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function editGroupAnalyticParams(array $groupIDs, array $params)
	{
		$result = $this->getGroups(array_values($groupIDs));
		if(!$result->isSuccess())
		{
			return $result;
		}
		$groups = $result->getData();
		foreach($groups as $group)
		{
			$data = [];
			if(isset($params['url_tags']) && !empty($params['url_tags']) && isset($group['site']))
			{
				$uri = new Uri($group['site']);
				$uri->addParams($params['url_tags']);
				$data['website'] = $uri->getUri();
			}
			if(isset($params['phone']) && !empty($params['phone']) && $group['phone'] != $params['phone'])
			{
				$data['phone'] = $params['phone'];
			}
			if(!empty($data))
			{
				$data['id'] = $group['id'];
				$response = $this->getRequest()->send([
					'method' => 'POST',
					'endpoint' => 'groups.edit',
					'fields' => $data,
				]);
				if(!$response->isSuccess())
				{
					$result->addErrors($response->getErrors());
				}
			}

			if(isset($params['url_tags']) && !empty($params['url_tags']) && isset($group['links']) && is_array($group['links']) && !empty($group['links']))
			{
				foreach($group['links'] as $link)
				{
					$url = new Uri($link['url']);
					$url->addParams($params['url_tags']);
					if($url->getUri() != $link['url'])
					{
						$response = $this->getRequest()->send([
							'method' => 'POST',
							'emdpoint' => 'groups.deleteLink',
							'fields' => [
								'group_id' => $group['id'],
								'link_id' => $link['id'],
							]
						]);
						if($response->isSuccess())
						{
							$response = $this->getRequest()->send([
								'method' => 'POST',
								'endpoint' => 'groups.addLink',
								'fields' => [
									'group_id' => $group['id'],
									'text' => $link['desc'],
									'link' => $url->getUri(),
								]
							]);
						}
						if(!$response->isSuccess())
						{
							$result->addErrors($response->getErrors());
						}
					}
				}
			}

			// todo add here edit call_to_action button
		}

		return $result;
	}

	/**
	 * @param array $groupIDs
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getGroups(array $groupIDs)
	{
		return $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'groups.getById',
			'fields' => [
				'group_ids' => implode(',', $groupIDs),
				'fields' => 'name,type,id,links,site,status,description,phone'
			]
		]);
	}

	/**
	 * @param string $url
	 * @return array|false
	 */
	protected function parseVkUrl($url)
	{
		if(preg_match('#vk\.com\/wall-(\d+)_(\d+)#', $url, $matches) && count($matches) > 2)
		{
			return [
				'group_id' => $matches[1],
				'post_id' => '-'.$matches[1].'_'.$matches[2],
			];
		}

		return false;
	}

	/**
	 * @param array $postIDs
	 * @param array $params
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function editPostAnalyticParams(array $postIDs, array $params)
	{
		$result = $this->getPosts($postIDs);
		if(!$result->isSuccess())
		{
			return $result;
		}

		$posts = $result->getData();
		foreach($posts as $post)
		{
			if($post['post_type'] == 'post_ads' && isset($post['attachments']) && is_array($post['attachments']) && count($post['attachments']) == 1)
			{
				$attachment = reset($post['attachments']);
				if($attachment['type'] != 'link' || mb_strpos($attachment['link']['url'], 'vk.com') !== false)
				{
					continue;
				}
				$data = [];
				if(isset($params['phone']) && !empty($params['phone']) && mb_strpos($attachment['link']['url'], 'tel:') === 0)
				{
					$data = [
						'owner_id' => $post['owner_id'],
						'post_id' => $post['id'],
						'attachments' => 'tel:'.$params['phone'],
					];
				}
				elseif(isset($params['url_tags']) && !empty($params['url_tags']))
				{
					$url = new Uri($attachment['link']['url']);
					$url->addParams($params['url_tags']);
					if($url->getUri() != $attachment['link']['url'])
					{
						$data = [
							'owner_id' => $post['owner_id'],
							'post_id' => $post['id'],
							'attachments' => $url->getUri(),
						];
					}
				}
				if(!empty($data))
				{
					$response = $this->getRequest()->send([
						'method' => 'POST',
						'endpoint' => 'wall.editAdsStealth',
						'fields' => $data,
					]);
					if(!$response->isSuccess())
					{
						$result->addErrors($response->getErrors());
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $postIDs
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getPosts(array $postIDs)
	{
		return $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'wall.getById',
			'fields' => [
				'posts' => implode(',', array_values($postIDs)),
				'fields' => 'id,attachments',
			]
		]);
	}
}