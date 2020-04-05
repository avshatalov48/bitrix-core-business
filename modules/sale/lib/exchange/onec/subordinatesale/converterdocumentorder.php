<?php

namespace Bitrix\Sale\Exchange\OneC\SubordinateSale;


class ConverterDocumentOrder extends \Bitrix\Sale\Exchange\OneC\ConverterDocumentOrder
{
	protected function getFieldsInfo()
	{
		return OrderDocument::getFieldsInfo();
	}

	public function externalizeItems(array $items, array $info)
	{
		$result = parent::externalizeItems($items, $info);
		foreach ($items as $rowId=>$item)
		{
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch ($name)
				{
					case 'BASE_UNIT':
						$value = self::MEASURE_CODE_DEFAULT;
						break;
				}

				if($value<>'')
				{
					$this->externalizeField($value, $fieldInfo);
					$result[$rowId][$name] = $value;
				}
			}
		}
		return $result;
	}
}