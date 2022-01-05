<?php

namespace Bitrix\Seo\Retargeting\Services;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Json;
use Bitrix\Seo\Retargeting\PagingInterface;
use \Bitrix\Seo\Retargeting\Response;

Loc::loadMessages(__FILE__);

class ResponseFacebook extends Response implements PagingInterface
{
	public const TYPE_CODE = 'facebook';

	/** @var array $pagingData */
	protected $pagingData;

	/**
	 * Parse response.
	 *
	 * @param array|string $data Data.
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function parse($data) : void
	{
		$parsed = is_array($data) ? $data : Json::decode($data);

		if ($parsed['error'])
		{
			$errorText = (isset($parsed['error']['error_user_msg']) && $parsed['error']['error_user_msg'])
				? $parsed['error']['error_user_msg']
				: $parsed['error']['message']
			;

			if ($errorText === '(#100) The parameter follow_up_action_url is required')
			{
				$errorText = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR_URL_REQUIRED');
			}
			if ($errorText === 'To create or edit a Custom Audience made from a customer list, your admin needs to add this ad account to a business.')
			{
				$errorText = Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR_ADD_TO_BUSINESS');
			}

			$this->addError(
				new Error(
					Loc::getMessage('SEO_RETARGETING_SERVICE_RESPONSE_FACEBOOK_ERROR') . ': ' . $errorText,
					$parsed['error']['code']
				)
			);
		}

		if ($parsed['data'])
		{
			$this->setData($parsed['data']);
		}
		elseif (!isset($parsed['error']))
		{
			$this->setData($parsed);
		}

		if (isset($parsed['paging']))
		{
			$this->pagingData = $parsed['paging'];
		}
	}

	/**
	 * @inherit
	 * @param array $params
	 *
	 * @return array|null
	 */
	public function prepareRequestParams(array $params) : ?array
	{
		if (isset($this->pagingData['next'], $this->pagingData['cursors']['after']))
		{
			$params['parameters']['params']['after'] = $this->pagingData['cursors']['after'];

			return $params;
		}

		return null;
	}
}