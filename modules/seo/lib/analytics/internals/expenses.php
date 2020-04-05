<?php

namespace Bitrix\Seo\Analytics\Internals;

class Expenses
{
	protected $data;

	/**
	 * Expenses constructor.
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->prepareData($data);
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function add(array $data)
	{
		foreach($this->getNumericFieldNames() as $name)
		{
			if(isset($data[$name]))
			{
				$value = $data[$name];
				if (is_array($value))
				{
					$value = array_sum(array_map(
						function ($value)
						{
							return is_numeric($value) ? $value : 0;
						},
						array_column($value, 'value')
					));
				}
				if (is_numeric($value))
				{
					$this->data[$name] += $value;
				}
			}
		}
		if(isset($data['currency']) && empty($this->data['currency']))
		{
			$this->data['currency'] = $data['currency'];
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getImpressions()
	{
		return $this->data['impressions'];
	}

	/**
	 * @return int
	 */
	public function getClicks()
	{
		return $this->data['clicks'];
	}

	/**
	 * @return int
	 */
	public function getActions()
	{
		return $this->data['actions'];
	}

	/**
	 * @return float
	 */
	public function getCpc()
	{
		return $this->data['cpc'];
	}

	/**
	 * This is cost per 1000 impressions.
	 *
	 * @return float
	 */
	public function getCpm()
	{
		return $this->data['cpm'];
	}

	/**
	 * @return float
	 */
	public function getSpend()
	{
		return $this->data['spend'];
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->data['currency'];
	}

	/**
	 * @param array $data
	 */
	protected function prepareData(array $data)
	{
		$this->data = [
			'impressions' => 0,
			'clicks' => 0,
			'actions' => 0,
			'cpc' => 0,
			'cpm' => 0,
			'spend' => 0,
			'currency' => '',
		];

		$this->data = array_merge($this->data, $data);
	}

	/**
	 * @return array
	 */
	protected function getNumericFieldNames()
	{
		return ['impressions', 'clicks', 'actions', 'cpc', 'cpm', 'spend'];
	}
}