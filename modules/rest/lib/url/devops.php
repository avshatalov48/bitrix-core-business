<?php

namespace Bitrix\Rest\Url;

class DevOps extends Base
{
	protected $directory = 'devops/';
	protected $pages = [
		'index' => '',
		'statistic' => 'statistic/',
		'list' => 'list/',
		'section' => 'section/#SECTION_CODE#/',
		'edit' => 'edit/#ELEMENT_CODE#/#ID#/',
		'iframe' => 'iframe/',
	];

	public function getIndexUrl()
	{
		return $this->getUrl('index');
	}

	public function getStatisticUrl()
	{
		return $this->getUrl('statistic');
	}

	public function getListUrl()
	{
		return $this->getUrl('list');
	}

	public function getIframeUrl($query = null)
	{
		$params = null;
		if(!is_null($query))
		{
			$params = [
				'query' => $query
			];
		}

		return $this->getUrl('iframe', null, null, $params);
	}

	public function getIntegrationSectionUrl($code = null)
	{
		$replace = null;
		$subject = null;

		if(!is_null($code))
		{
			$replace = [
				'#SECTION_CODE#'
			];
			$subject = [
				$code
			];
		}

		return $this->getUrl(
			'section',
			$replace,
			$subject
		);
	}

	public function getIntegrationEditUrl($id = null, $elementCode = null)
	{
		$replace = null;
		$subject = null;

		if(!is_null($id) && !is_null($elementCode))
		{
			$replace = [
				'#ID#',
				'#ELEMENT_CODE#'
			];
			$subject = [
				$id,
				$elementCode
			];
		}

		return $this->getUrl(
			'edit',
			$replace,
			$subject
		);
	}

}