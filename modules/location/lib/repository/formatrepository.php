<?php

namespace Bitrix\Location\Repository;

use Bitrix\Location\Entity;
use Bitrix\Location\Repository\Format\DataCollection;
use Bitrix\Location\Entity\Format\Converter\ArrayConverter;

/**
 * Class FormatRepository
 * @package Bitrix\Location\Repository
 */
class FormatRepository
{
	/** @var DataCollection  */
	protected $dataCollection;

	/**
	 * FormatRepository constructor.
	 * @param array $params
	 */
	public function __construct(array $params = [])
	{
		if(isset($params['dataCollection']) && is_subclass_of($params['dataCollection'], DataCollection::class))
		{
			$this->dataCollection = $params['dataCollection'];
		}
		else
		{
			$this->dataCollection = DataCollection::class;
		}
	}

	/**
	 * @param string $language
	 * @return array
	 */
	public function findAll(string $language): array
	{
		$result = [];

		foreach ($this->dataCollection::getAll($language) as $data)
		{
			$result[] = ArrayConverter::convertFromArray($data, $language);
		}

		return $result;
	}

	/**
	 * @param string $code
	 * @param string $languageId
	 * @return FormatRepository|null
	 */
	public function findByCode(string $code, string $languageId):? Entity\Format
	{
		$data = $this->dataCollection::getByCode($code, $languageId);
		return is_array($data) ? ArrayConverter::convertFromArray($data, $languageId) : null;
	}
}