<?php

namespace Bitrix\Location\Source\Google\Requesters;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Text\Encoding;

/**
 * Class UrlMaker
 * @package Bitrix\Location\Source\Google\Requesters
 */
class UrlMaker
{
	/**
	 * @param array $params
	 * @param string $url
	 * @param array $required
	 * @param array $encode
	 * @return string
	 * @throws ArgumentNullException
	 */
	public function make(array $params, string $url, array $required = [], array $encode = []): string
	{
		$this->checkRequiredFields($params, $required);
		$params = $this->encodeFields($params, $encode);
		return $this->buildQuery($params, $url);
	}

	/**
	 * @param string $params
	 * @param array $url
	 * @return string
	 */
	protected function buildQuery(array $params, string $url): string
	{
		$query = '';

		foreach($params as $key => $value)
		{
			if($query <> '')
			{
				$query .= '&';
			}

			$query .= $key.'='.$value;
		}

		return $url.'?'.$query;
	}

	/**
	 * @param array $params
	 * @param array $requiredFields
	 * @throws ArgumentNullException
	 */
	protected function checkRequiredFields(array $params, array $requiredFields): void
	{
		foreach($requiredFields as $field)
		{
			if(!isset($params[$field]))
			{
				throw new ArgumentNullException('data['.$field.']');
			}
		}
	}

	/**
	 * @param array $params
	 * @param array $fieldsToEncode
	 * @return array Data with encoded fields
	 * @throws ArgumentNullException
	 */
	protected function encodeFields(array $params, array $fieldsToEncode): array
	{
		foreach($fieldsToEncode as $field)
		{
			if(!isset($params[$field]))
			{
				throw new ArgumentNullException('Field "'.$field.'" is absent');
			}

			if(ToUpper(SITE_CHARSET) !== 'UTF-8')
			{
				$params[$field] = urlencode(
					Encoding::convertEncoding(
						$params[$field],
						SITE_CHARSET,
						'UTF-8'
					));
			}
		}

		return $params;
	}
}