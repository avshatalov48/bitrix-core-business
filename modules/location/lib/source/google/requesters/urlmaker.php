<?php

namespace Bitrix\Location\Source\Google\Requesters;

use Bitrix\Main\ArgumentNullException;

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
	 * @return string
	 * @throws ArgumentNullException
	 */
	public function make(array $params, string $url, array $required = []): string
	{
		$this->checkRequiredFields($params, $required);
		return $this->buildQuery($params, $url);
	}

	/**
	 * @param array $params
	 * @param string $url
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
}