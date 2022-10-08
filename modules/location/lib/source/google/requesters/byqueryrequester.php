<?php

namespace Bitrix\Location\Source\Google\Requesters;

/**
 * class ByQueryRequester
 * @package Bitrix\Location\Source\Google\Requesters
 * todo: it takes into account the location of the client
 */
final class ByQueryRequester extends BaseRequester
{
	protected $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
	protected $defaultFields = 'basic';
	protected $requiredFields = ['query', 'language', 'key'];
	protected $fieldsToEncode = ['query'];

	/**
	 * @param array $data
	 * @return mixed|string
	 */
	protected function makeUrl(array $data): string
	{
		if(empty($data['fields']))
		{
			$data['fields'] = $this->defaultFields;
		}

		return parent::makeUrl($data);
	}
}
