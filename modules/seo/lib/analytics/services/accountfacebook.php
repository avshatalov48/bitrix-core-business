<?php

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\Result;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Error;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Json;
use \Bitrix\Seo\Analytics\Account;
use Bitrix\Seo\Analytics\Internals\Page;
use Bitrix\Seo\Retargeting\Services\ResponseFacebook;
use Bitrix\Seo\Retargeting\Response;

class AccountFacebook extends Account
{
	const TYPE_CODE = 'facebook';

	public function getList()
	{
		$response = $this->request->send(array(
			'methodName' => 'analytics.account.list',
			'parameters' => array()
		));

		return $response;
	}

	public function getProfile()
	{
		$response = $this->getRequest()->getClient()->get(
			'https://graph.facebook.com/me?fields=id,name,picture,link&access_token=' .
			urlencode($this->getRequest()->getAuthAdapter()->getToken())
		);

		if ($response)
		{
			$response = Json::decode($response);
			if (is_array($response))
			{
				return array(
					'ID' => $response['id'] ?? null,
					'NAME' => $response['name'] ?? null,
					'LINK' => '',
					'PICTURE' => $response['picture']['data']['url'] ?? null,
				);
			}
		}


		return null;
	}

	/**
	 * Get expenses.
	 *
	 * @param mixed $accountId Facebook Ad Account Id.
	 * @param Date|null $dateFrom Date from.
	 * @param Date|null $dateTo Date to.
	 * @return ResponseFacebook
	 */
	public function getExpenses($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		$parameters = [
			'ACCOUNT_ID' => $accountId,
		];
		if($dateFrom && $dateTo)
		{
			$parameters['DATE_FROM'] = $dateFrom->format('Ymd');
			$parameters['DATE_TO'] = $dateTo->format('Ymd');
		}
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.get',
			'parameters' => $parameters,
		]);

		$data = $response->getData();
		$expenses = new Expenses();
		$expenses->add([
			'impressions' => $data['impressions'],
			'clicks' => $data['clicks'],
			'actions' => $data['actions'],
			'cpc' => $data['cpc'],
			'cpm' => $data['cpm'],
			'spend' => $data['spend'],
			'currency' => $data['currency'],
		]);

		$response = (new ResponseFacebook());
		$response->setData(['expenses' => $expenses]);

		return $response;
	}

	protected function prepareExpensesData($data)
	{
		return $data;
	}

	/**
	 * Return true if it has expenses report.
	 *
	 * @return bool
	 */
	public function hasExpensesReport()
	{
		return true;
	}

	/**
	 * Get expenses report.
	 *
	 * @param mixed $accountId Facebook Ad Account Id.
	 * @param Date|null $dateFrom Date from.
	 * @param Date|null $dateTo Date to.
	 * @return Result
	 */
	public function getExpensesReport($accountId, Date $dateFrom = null, Date $dateTo = null)
	{
		if (mb_substr($accountId, 0, 4) === 'act_')
		{
			$accountId = mb_substr($accountId, 4);
		}

		$parameters = [
			'ACCOUNT_ID' => $accountId,
		];
		if($dateFrom && $dateTo)
		{
			$parameters['DATE_FROM'] = $dateFrom->format('Ymd');
			$parameters['DATE_TO'] = $dateTo->format('Ymd');
		}
		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.report',
			'parameters' => $parameters,
		]);

		return $response;
	}

	/**
	 * @param string|null $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 *
	 * @return Result
	 */
	public function getDailyExpensesReport(?string $accountId, ?Date $dateFrom, ?Date $dateTo): Result
	{
		if (mb_substr($accountId, 0, 4) === 'act_')
		{
			$accountId = mb_substr($accountId, 4);
		}

		$parameters = [
			'ACCOUNT_ID' => $accountId,
			'LEVEL' => 'campaign',
		];

		if ($dateFrom && $dateTo)
		{
			$parameters['DATE_FROM'] = $dateFrom->format('Ymd');
			$parameters['DATE_TO'] = $dateTo->format('Ymd');
		}

		$response = $this->getRequest()->send([
			'methodName' => 'analytics.expenses.report',
			'parameters' => $parameters,
			'streamTimeout' => static::LOAD_DAILY_EXPENSES_TIMEOUT,
		]);

		$result = new Result();
		$response->getData();

		if (!$response->isSuccess())
		{
			$result->addErrors($response->getErrors());

			return $result;
		}

		$data = $response->getData();
		$result->setData(['expenses' => Helpers\ExpensesAdapter::translateExpensesReportToDailyExpenses($data)]);

		return $result;
	}

	public function hasDailyExpensesReport(): bool
	{
		return true;
	}

	/**
	 * Return true if it has public pages.
	 *
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
		$response = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'act_'.$accountId.'/promote_pages',
			'fields' => [
				'fields' => 'id,name,about,cover,emails,phone',
			]
		]);

		if($response->isSuccess())
		{
			$pages = [];
			$data = $response->getData();
			foreach($data as $page)
			{
				$pages[] = new Page([
					'id' => $page['id'],
					'name' => $page['name'],
					'about' => $page['about'],
					'image' => $page['cover']['source'],
					'phone' => $page['phone'],
					'email' => $page['emails'],
				]);
			}
			$response->setData($pages);
		}

		return $response;
	}

	/**
	 * @param $publicPageId
	 * @param array $params
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function updatePublicPage($publicPageId, array $params)
	{
		$result = new ResponseFacebook();
		$fields = [];
		if(isset($params['phone']))
		{
			$fields['phone'] = $params['phone'];
		}
		if(isset($params['email']))
		{
			$fields['emails'] = $params['email'];
		}

		$response = $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => $publicPageId,
			'fields' => $fields,
		]);
		if(!$response->isSuccess())
		{
			$result->addErrors($response->getErrors());
		}

		$response = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => $publicPageId.'/call_to_actions',
			'fields' => [
				'fields' => 'id,type'
			],
		]);
		if($response->isSuccess())
		{
			$callToAction = $response->getData();
			if($callToAction['type'] == 'CALL_NOW' && isset($fields['phone']))
			{
				$response = $this->getRequest()->send([
					'method' => 'POST',
					'endpoint' => $callToAction['id'],
					'fields' => [
						'intl_number_with_plus' => $fields['phone']
					]
				]);
			}
			elseif($callToAction == 'EMAIL' && isset($fields['emails']))
			{
				$response = $this->getRequest()->send([
					'method' => 'POST',
					'endpoint' => $callToAction['id'],
					'fields' => [
						'email_address' => $fields['emails']
					]
				]);
			}
		}

		return $response;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		// get all ads
		// get current ad creative for each
		// create new ad creative for each with new url_tags
		// update each ad with new creative
		$result = new ResponseFacebook();
		if(empty($params))
		{
			return $result;
		}
		if(!empty($params['url_tags']))
		{
			$updateAdResult = $this->updateAdUrlTags($accountId, $params['url_tags']);
			if(!$updateAdResult->isSuccess())
			{
				$result->addErrors($updateAdResult->getErrors());
			}
		}

		if($this->hasPublicPages() && !empty($params['phone']) || !empty($params['email']))
		{
			foreach($publicPageIds as $publicPageId)
			{
				$updatePageResult = $this->updatePublicPage($publicPageId, $params);
				if(!$updatePageResult->isSuccess())
				{
					$result->addErrors($updatePageResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param $accountId
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getAds($accountId)
	{
		$adSetResult = $this->getAdSetIds($accountId);
		if($adSetResult->isSuccess())
		{
			$adSetIds = $adSetResult->getData();
			if(empty($adSetIds))
			{
				return $adSetResult;
			}
		}
		else
		{
			return $adSetResult;
		}
		$fields = [
			'fields' => 'id,adset_id,campaign_id,creative',
		];

		$adsResult = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'act_'.$accountId.'/ads',
			'fields' => $fields,
		]);
		if($adsResult->isSuccess())
		{
			$ads = $adsResult->getData();
			$result = [];
			foreach($ads as $ad)
			{
				if(in_array($ad['adset_id'], $adSetIds))
				{
					$result[] = $ad;
				}
			}
			$adsResult->setData($result);
		}

		return $adsResult;
	}

	/**
	 * @param $creativeId
	 * @return Response
	 */
	protected function getAdCreative($creativeId)
	{
		$fields = [
			'fields' => 'id,account_id,actor_id,adlabels,applink_treatment,asset_feed_spec,body,branded_content_sponsor_page_id,'.
				'call_to_action_type,effective_instagram_story_id,effective_object_story_id,image_crops,image_hash,'.
				'image_url,instagram_actor_id,instagram_permalink_url,instagram_story_id,link_og_id,link_url,'.
				'messenger_sponsored_message,name,object_id,object_story_id,object_story_spec,object_type,object_url,'.
				'platform_customizations,portrait_customizations,product_set_id,recommender_settings,status,template_url,'.
				'template_url_spec,thumbnail_url,title,url_tags,use_page_actor_override,video_id',
		];

		return $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => $creativeId,
			'fields' => $fields,
		]);
	}

	/**
	 * @param $accountId
	 * @param $adId
	 * @param array $creative
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function updateAdCreative($accountId, $adId, array $creative)
	{
		unset($creative['id']);
		foreach($creative as $key => $value)
		{
			if(is_array($value))
			{
				$creative[$key] = Json::encode($value);
			}
		}

		$response = $this->getRequest()->send([
			'method' => 'POST',
			'endpoint' => 'act_'.$accountId.'/adcreatives',
			'fields' => $creative,
		]);

		if($response->isSuccess())
		{
			$data = $response->getData();
			if(isset($data['id']))
			{
				$response = $this->getRequest()->send([
					'method' => 'POST',
					'enpoint' => $adId,
					'fields' => ['creative' => $data['id']],
				]);
			}
			else
			{
				$response->addError(new Error('Could not find id after Ad Creative add'));
			}
		}

		return $response;
	}

	/**
	 * @param $accountId
	 * @param array $urlParams
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function updateAdUrlTags($accountId, array $urlParams)
	{
		$result = $this->getAds($accountId);
		if(!$result->isSuccess() || empty($result->getData()))
		{
			return $result;
		}

		$ads = $result->getData();
		foreach($ads as $ad)
		{
			if(!isset($ad['creative']) || !isset($ad['creative']['id']))
			{
				continue;
			}
			$getAdCreativeResult = $this->getAdCreative($ad['creative']['id']);
			if($getAdCreativeResult->isSuccess())
			{
				$creative = $getAdCreativeResult->getData();
				$currentUrlParams = $this->parseUrlParams($creative['url_tags']);
				$creative['url_tags'] = http_build_query($this->mergeUrlParams($currentUrlParams, $urlParams));

				$updateAdCreativeResult = $this->updateAdCreative($accountId, $ad['id'], $creative);
				if(!$updateAdCreativeResult->isSuccess())
				{
					$result->addErrors($updateAdCreativeResult->getErrors());
				}
			}
			else
			{
				$result->addErrors($getAdCreativeResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $string
	 * @return array
	 */
	protected function parseUrlParams($string)
	{
		$result = [];

		if(empty($string))
		{
			return $result;
		}

		$pairs = explode('&', $string);
		foreach($pairs as $pair)
		{
			list($name, $value) = explode('=', $pair);
			$result[$name] = urldecode($value);
		}

		return $result;
	}

	/**
	 * @param array $currentParams
	 * @param array $newParams
	 * @return array
	 */
	protected function mergeUrlParams(array $currentParams, array $newParams)
	{
		foreach($newParams as $name => $value)
		{
			if(empty($value))
			{
				if(isset($currentParams[$name]))
				{
					unset($currentParams[$name]);
				}
			}
			else
			{
				$currentParams[$name] = $value;
			}
		}

		return $currentParams;
	}

	/**
	 * @return array
	 */
	protected function getPublisherPlatforms()
	{
		return ['facebook', 'messenger', 'audience_network'];
	}

	/**
	 * @param $accountId
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAdSetIds($accountId)
	{
		$response = $this->getRequest()->send([
			'method' => 'GET',
			'endpoint' => 'act_'.$accountId.'/adsets',
			'fields' => [
				'fields' => 'id,name,targeting'
			],
		]);
		if($response->isSuccess())
		{
			$data = $response->getData();
			$facebook = $instagram = [];
			foreach($data as $adSet)
			{
				$all[] = $adSet['id'];
				if(
					isset($adSet['targeting']) && is_array($adSet['targeting']) &&
					isset($adSet['targeting']['publisher_platforms']) && is_array($adSet['targeting']['publisher_platforms']) &&
					count($adSet['targeting']['publisher_platforms']) == 1 && reset($adSet['targeting']['publisher_platforms']) == 'instagram'
				)
				{
					$instagram[] = $adSet['id'];
				}
				else
				{
					$facebook[] = $adSet['id'];
				}
			}
			if(static::TYPE_CODE === 'instagram')
			{
				$result = $instagram;
			}
			else
			{
				$result = $facebook;
			}
			$response->setData($result);
		}

		return $response;
	}
}
