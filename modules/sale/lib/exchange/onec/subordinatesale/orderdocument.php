<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


class OrderDocument extends \Bitrix\Sale\Exchange\OneC\OrderDocument
{
	/**
	 * @return array
	 */
	static public function getFieldsInfo()
	{
		 return array_merge(
			parent::getFieldsInfo(),
			array('SUBORDINATES'=>array())
		);
	}

	/**
	 * @param array $document
	 * @return array
	 */
	static public function prepareFieldsData(array $document)
	{
		$result = parent::prepareFieldsData($document);

		foreach(static::getFieldsInfo() as $k=>$v)
		{
			switch($k)
			{
				case 'SUBORDINATES':
					$result[$k] = self::resolveSubordinateDocuments($document);
					break;
			}
		}
		return $result;
	}

	/**
	 * @param $value
	 * @return array|null
	 */
	protected static function resolveSubordinateDocuments($value)
	{
		$result = null;
		$message = self::getMessage();

		if(is_array($value["#"][$message["SALE_EXPORT_SUBORDINATES"]][0]["#"][$message["SALE_EXPORT_SUBORDINATE"]][0]["#"]))
		{
			$rawSubordinates = $value["#"][$message["SALE_EXPORT_SUBORDINATES"]][0]["#"][$message["SALE_EXPORT_SUBORDINATE"]];

			foreach ($rawSubordinates as $raw)
			{
				$documentTypeId = self::resolveRawDocumentTypeId($raw);
				$document = DocumentFactory::create($documentTypeId);
				$result[] = $document::prepareFieldsData($raw);
			}
		}
		return $result;
	}

	static protected function unitFieldsInfo(&$info)
	{
		$info['ITEMS']['FIELDS']['BASE_UNIT'] = array(
			'TYPE' => 'string'
		);
	}

	/**
	 * @param array $fields
	 * @param int $level
	 * @return string
	 */
	protected function outputXml(array $fields, $level = 0)
	{
		$xml = parent::outputXml($fields, $level);

		foreach ($fields as $name=>$value)
		{
			if(is_array($value))
			{
				switch ($name)
				{
					case 'SUBORDINATES':
						if(is_array($value) && count($value)>0)
						{
							$xml .= $this->openNodeDirectory($level, 'SUBORDINATES');
							foreach ($value as $v)
							{
								$xml .= $this->openNodeDirectory($level+1, 'SUBORDINATE');
								$typeId = static::resolveDocumentTypeId($v['OPERATION']);
								if($typeId>0)
								{
									$document = DocumentFactory::create($typeId);
									$document->setFields($v);
									$xml .= $document->output($level+2);
								}

								$xml .= $this->closeNodeDirectory($level+1, 'SUBORDINATE');
							}
							$xml .= $this->closeNodeDirectory($level, 'SUBORDINATES');
						}
						break;
				}
			}
		}
		return $xml;
	}
}