<?php

namespace Bitrix\Seo\Webmaster\Engine;

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Retargeting;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Webmaster;

/**
 * Class MediaVkontakte
 */
class Google extends Retargeting\BaseApiObject
{
	public const TYPE_CODE = Webmaster\Service::TYPE_GOOGLE;

	/**
	 * Get list of added sites with statuses
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSites(): Response
	{
		return $this->getRequest()->send([
			'methodName' => 'webmaster.sites.get',
		]);
	}

	/**
	 * Add site to webmaster
	 * @param string $site - url of site with protocol
	 * @return Response
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addSite(string $site): Response
	{
		return $this->getRequest()->send([
			'methodName' => 'webmaster.sites.add',
			'parameters' => [
				'site' => HtmlFilter::encode($site),
			],
		]);
	}

	/**
	 * Get token-string for naming verify file
	 * @param array $data - site object
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getVerifyToken(array $data): Response
	{
		return $this->getRequest()->send([
			'methodName' => 'webmaster.verify.token.get',
			'parameters' => [
				'data' => Json::encode($data),
			],
		]);
	}

	/**
	 * Pass site to verify
	 * @param array $data - site object
	 * @return Response
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function verifySite(array $data): Response
	{
		return $this->getRequest()->send([
			'methodName' => 'webmaster.verify.site',
			'parameters' => [
				'data' => Json::encode($data),
			],
		]);
	}
}