<?php

namespace Bitrix\Sale\Rest\Entity;

use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

abstract class Base
{
	const TYPE_INT = 'integer';
	const TYPE_FLOAT = 'double';
	const TYPE_STRING = 'string';
	const TYPE_CHAR = 'char';
	const TYPE_LIST = 'list';
	const TYPE_TEXT = 'text';
	const TYPE_FILE = 'file';
	const TYPE_DATE = 'date';
	const TYPE_DATETIME = 'datetime';
	const TYPE_DATATYPE = 'datatype';

	abstract public function getFields();

	public function prepareFieldInfos($fields)
	{
		$result = [];
		if (is_array($fields))
		{
			foreach($fields as $name => $info)
			{
				$attributs = isset($info['ATTRIBUTES']) ? $info['ATTRIBUTES'] : [];

				if(in_array(Attributes::Hidden, $attributs, true))
				{
					continue;
				}

				$result[$name] = array(
					'TYPE' => $info['TYPE'],
					'IS_REQUIRED' => in_array(Attributes::Required, $attributs, true),
					'IS_READ_ONLY' => in_array(Attributes::ReadOnly, $attributs, true),
					'IS_IMMUTABLE' => in_array(Attributes::Immutable, $attributs, true)
				);
			}
		}

		return $result;
	}

	public function getSettableFields()
	{
		return array_keys(
			$this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]])
		);
	}

	public function getListFieldInfo(array $fieldsInfo, $params=[])
	{
		$list = [];

		$filter = is_set($params, 'filter')?$params['filter']:[];
		$ignoredAttributes = is_set($filter, 'ignoredAttributes')?$filter['ignoredAttributes']:[];
		$ignoredFields = is_set($filter, 'ignoredFields')?$filter['ignoredFields']:[];
		$skipFields = is_set($filter, 'skipFields')?$filter['skipFields']:[];

		foreach ($fieldsInfo as $name=>$info)
		{
			if(in_array($name, $ignoredFields))
			{
				continue;
			}
			elseif(in_array($name, $skipFields) == false)
			{
				if(isset($info['ATTRIBUTES']))
				{
					$skipAttr = array_intersect($ignoredAttributes, $info['ATTRIBUTES']);
					if(!empty($skipAttr))
					{
						continue;
					}
				}
			}

			$list[$name] = $info;
		}

		return $list;
	}

	protected function isNewItem($fields)
	{
		return (isset($fields['ID']) === false);
	}

	//region convert keys to snake case
	public function convertKeysToSnakeCaseFields($fields)
	{
		return $this->convertKeysToSnakeCase($fields);
	}

	public function convertKeysToSnakeCaseSelect($fields)
	{
		$converter = new Converter(Converter::VALUES | Converter::TO_SNAKE | Converter::TO_UPPER| Converter::TO_SNAKE_DIGIT);
		$items = $converter->process($fields);
		return $this->converterValuesProcessOnAfter($items);
	}

	public function convertKeysToSnakeCaseFilter($fields)
	{
		return $this->convertKeysToSnakeCase($fields);
	}

	public function convertKeysToSnakeCaseOrder($fields)
	{
		$result = [];

		$converter = new Converter(Converter::VALUES | Converter::TO_UPPER);
		$converterForKey = new Converter(Converter::KEYS | Converter::TO_SNAKE | Converter::TO_UPPER | Converter::TO_SNAKE_DIGIT);

		foreach ($converter->process($fields) as $key=>$value)
		{
			$result[$converterForKey->process($key)] = $value;
		}
		return $this->converterKeysProcessOnAfter($result);
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		return $arguments;
	}

	protected function convertKeysToSnakeCase($data): array
	{
		$converter = new Converter(Converter::KEYS | Converter::RECURSIVE | Converter::TO_SNAKE | Converter::TO_UPPER | Converter::TO_SNAKE_DIGIT);
		$items = $converter->process($data);
		return $this->converterKeysProcessOnAfter($items);
	}

	private function converterKeysProcessOnAfter($items): array
	{
		$result = [];
		foreach ($items as $key=>$item)
		{
			$result[$this->resolveFieldName($key)] = $item;
		}
		return $result;
	}

	private function converterValuesProcessOnAfter($items): array
	{
		$result = [];
		foreach ($items as $key=>$item)
		{
			$result[$key] = $this->resolveFieldName($item);
		}
		return $result;
	}

	private function resolveFieldName($name)
	{
		if ($name === 'ID_1_C')
		{
			return 'ID_1C';
		}

		if ($name === 'VERSION_1_C')
		{
			return 'VERSION_1C';
		}

		if ($name === 'UPDATED_1_C')
		{
			return 'UPDATED_1C';
		}

		return $name;
	}
	//endregion

	//region internalize fields
	/**
	 * @param $name
	 * @param $arguments
	 * @throws NotImplementedException
	 * @return array
	 */
	public function internalizeArguments($name, $arguments)
	{
		throw new NotImplementedException('Internalize arguments. The method '.$name.' is not implemented.');
	}

	public function internalizeFieldsList($arguments)
	{
		$fieldsInfo = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden]]]);

		$filter = isset($arguments['filter']) ? $this->internalizeFilterFields($arguments['filter'], $fieldsInfo):[];
		$select = isset($arguments['select']) ? $this->internalizeSelectFields($arguments['select'], $fieldsInfo):[];
		$order = isset($arguments['order']) ? $this->internalizeOrderFields($arguments['order'], $fieldsInfo):[];

		return [
			'filter'=>$filter,
			'select'=>$select,
			'order'=>$order,
		];
	}

	public function internalizeFieldsAdd($fields)
	{
		return $this->internalizeFields(
			$fields,
			$this->getListFieldInfo(
				$this->getFields(),
				['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]
			)
		);
	}

	public function internalizeFieldsUpdate($fields)
	{
		return $this->internalizeFields(
			$fields,
			$this->getListFieldInfo(
				$this->getFields(),
				['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]
			)
		);
	}

	/**
	 * @param $fields
	 * @param array $fieldsInfo
	 * @return array
	 * @throws NotImplementedException
	 */
	public function internalizeFieldsModify($fields)
	{
		throw new NotImplementedException('The method internalizeFieldsModify is not implemented.');
	}

	public function internalizeFieldsTryAdd($fields)
	{
		return $this->internalizeFieldsAdd($fields);
	}

	public function internalizeFieldsTryUpdate($fields)
	{
		return $this->internalizeFieldsUpdate($fields);
	}

	public function internalizeFieldsTryModify($fields)
	{
		return $this->internalizeFieldsModify($fields);
	}

	protected function internalizeFieldValue($value, $info)
	{
		$result = new Result();

		$type = $info['TYPE'] ?? '';

		if($type === self::TYPE_DATE || $type === self::TYPE_DATETIME)
		{
			if($value === '')
			{
				$date = '';
			}
			else
			{
				$time = strtotime($value);
				$date = ($time) ? \Bitrix\Main\Type\DateTime::createFromTimestamp($time):'';
			}

			if($date instanceof Date)
			{
				$value = $date;
			}
			else
			{
				$result->addError(new Error('internalize data field error', 0));
			}
		}
		elseif($type === self::TYPE_FILE)
		{
			//InternalizeFileField()
		}
		$result->addData([$value]);

		return $result;
	}

	protected function internalizeFields($fields, array $fieldsInfo)
	{
		$result = [];

		foreach ($fields as $name=>$value)
		{
			$info = isset($fieldsInfo[$name]) ? $fieldsInfo[$name]:null;
			if(!$info)
			{
				continue;
			}

			$r = $this->internalizeFieldValue($value, $info);
			if($r->isSuccess())
			{
				$value = current($r->getData());
			}
			else
			{
				continue;
			}

			$result[$name] = $value;
		}
		return $result;
	}

	protected function internalizeFilterFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		if(is_array($fields) && count($fields)>0)
		{
			$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden]]]);

			foreach ($fields as $rawName=>$value)
			{
				$field = \CSqlUtil::GetFilterOperation($rawName);

				$info = isset($listFieldsInfo[$field['FIELD']]) ? $listFieldsInfo[$field['FIELD']]:null;
				if(!$info)
				{
					continue;
				}

				$r = $this->internalizeFieldValue($value, $info);
				if($r->isSuccess())
				{
					$value = current($r->getData());
				}
				else
				{
					continue;
				}

				$operation = mb_substr($rawName, 0, mb_strlen($rawName) - mb_strlen($field['FIELD']));
				if(isset($info['FORBIDDEN_FILTERS'])
					&& is_array($info['FORBIDDEN_FILTERS'])
					&& in_array($operation, $info['FORBIDDEN_FILTERS'], true))
				{
					continue;
				}

				$result[$rawName]=$value;
			}
		}
		return $result;
	}

	protected function internalizeSelectFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden]]]);

		if(empty($fields) || in_array('*', $fields, true))
		{
			$result = array_keys($listFieldsInfo);
		}
		else
		{
			foreach ($fields as $name)
			{
				$info = isset($listFieldsInfo[$name]) ? $listFieldsInfo[$name]:null;
				if(!$info)
				{
					continue;
				}

				$result[] = $name;
			}
		}

		return $result;
	}

	protected function internalizeOrderFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		if(is_array($fields)
			&& count($fields)>0)
		{
			$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden]]]);

			foreach ($fields as $field=>$order)
			{
				$info = isset($listFieldsInfo[$field]) ? $listFieldsInfo[$field]:null;
				if(!$info)
				{
					continue;
				}

				$result[$field]=$order;
			}
		}

		return $result;
	}

	public function rewriteFieldsList($arguments)
	{
		$filter = isset($arguments['filter']) ? $this->rewriteFilterFields($arguments['filter']):[];
		$select = isset($arguments['select']) ? $this->rewriteSelectFields($arguments['select']):[];
		$order = isset($arguments['order']) ? $this->rewriteOrderFields($arguments['order']):[];

		return [
			'filter'=>$filter,
			'select'=>$select,
			'order'=>$order,
		];
	}

	protected function rewriteSelectFields($fields)
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();

		foreach ($fields as $name)
		{
			$fieldsIsAlias = isset($rewriteFields[$name]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$name]['REFERENCE_FIELD']))
				{
					$result[$name] = $rewriteFields[$name]['REFERENCE_FIELD'];
				}
			}
			else
			{
				$result[] = $name;
			}
		}

		return $result;
	}

	protected function rewriteFilterFields($fields)
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();


		foreach ($fields as $rawName=>$value)
		{
			$field = \CSqlUtil::GetFilterOperation($rawName);

			$fieldsIsAlias = isset($rewriteFields[$field['FIELD']]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$field['FIELD']]['REFERENCE_FIELD']))
				{
					$originalName = $rewriteFields[$field['FIELD']]['REFERENCE_FIELD'];
					$operation = mb_substr($rawName, 0, mb_strlen($rawName) - mb_strlen($field['FIELD']));
					$result[$operation.$originalName] = $value;
				}
			}
			else
			{
				$result[$rawName] = $value;
			}
		}

		return $result;
	}

	protected function rewriteOrderFields($fields)
	{
		$result = [];
		$rewriteFields = $this->getRewritedFields();

		foreach ($fields as $name=>$value)
		{
			$fieldsIsAlias = isset($rewriteFields[$name]);

			if($fieldsIsAlias)
			{
				if(isset($rewriteFields[$name]['REFERENCE_FIELD']))
				{
					$result[$rewriteFields[$name]['REFERENCE_FIELD']] = $value;
				}
			}
			else
			{
				$result[$name] = $value;
			}
		}

		return $result;
	}

	/**
	 * @throws NotImplementedException
	 * @return array
	 */
	protected function getRewritedFields()
	{
		return [];
	}

	protected function internalizeListFields($list, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden]]]);

		if(is_array($list) && count($list)>0)
		{
			foreach ($list as $k=>$item)
			{
				$result[$k] = $this->internalizeFields($item, $listFieldsInfo);
			}
		}
		return $result;
	}

	//endregion

	// region externalize fields
	public function externalizeFields($fields)
	{
        if (!is_array($fields))
        {
            return [];
        }

        $result = [];

        $fieldsInfo = $this->getListFieldInfo(
            $this->getFields(),
            [
                'filter' => [
                    'ignoredAttributes' => [
                        Attributes::Hidden,
                    ],
                ],
            ]
        );

        foreach ($fields as $name => $value)
        {
            $info = $fieldsInfo[$name] ?? null;
            if (!$info)
            {
                continue;
            }

            $type = $info['TYPE'] ?? '';
            $hasValue = isset($value) && $value !== '';

            switch ($type)
            {
                case DataType::TYPE_STRING:
                case DataType::TYPE_CHAR:
                case DataType::TYPE_TEXT:
                    $value = (string)$value;
                    break;
                case DataType::TYPE_FLOAT:
                    $value = $hasValue ? (float)$value : null;
                    break;
                case DataType::TYPE_INT:
                    $value = $hasValue ? (int)$value : null;
                    break;
                case DataType::TYPE_DATE:
                    if ($hasValue)
                    {
                        $time = strtotime($value);
                        $value = $time ? Date::createFromTimestamp($time) : null;
                    }
                    else
                    {
                        $value = null;
                    }
                    break;
                case DataType::TYPE_DATETIME:
                    if ($hasValue)
                    {
                        $time = strtotime($value);
                        $value = $time ? DateTime::createFromTimestamp($time) : null;
                    }
                    else
                    {
                        $value = null;
                    }
                    break;
                case DataType::TYPE_DATATYPE:
                case DataType::TYPE_LIST:
                    break;
                default:
                    $value = null;
                    break;
            }

            $result[$name] = $value;
        }

		return $result;
	}

	public function externalizeListFields($list)
	{
		$result = [];
		if(is_array($list) && count($list)>0)
		{
			foreach($list as $k=>$fields)
				$result[$k] = $this->externalizeFields($fields);
		}
		return $result;
	}

	/**
	 * @param $fields
	 * @throws NotImplementedException
	 * @return array
	 */
	protected function externalizeFieldsModify($fields)
	{
		throw new NotImplementedException('The method externalizeFieldsModify is not implemented.');
	}

	public function externalizeFieldsTryModify($fields)
	{
		return $this->externalizeFieldsModify($fields);
	}

	/**
	 * @param $name
	 * @param $fields
	 * @throws NotImplementedException
	 * @return array
	 */
	public function externalizeResult($name, $fields)
	{
		throw new NotImplementedException('Externalize result. The method '.$name.' is not implemented.');
	}
	// endregion

	//region convert keys to camel case
	public function convertKeysToCamelCase($fields)
	{
		return Converter::toJson()
			->process($fields);
	}
	// endregion

	//region check fields
	public function checkFieldsAdd($fields)
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsAdd($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));

		return $r;
	}

	public function checkFieldsUpdate($fields)
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsUpdate($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));

		return $r;
	}

	public function checkFieldsModify($fields)
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsModify($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(' ', $required->getErrorMessages())));

		return $r;
	}

	public function checkArguments($name, $arguments)
	{
		return new Result();
	}

	protected function checkRequiredFieldsAdd($fields)
	{
		return $this->checkRequiredFields($fields, $this->getListFieldInfo(
			$this->getFields(),
			['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]
		));
	}

	protected function checkRequiredFieldsUpdate($fields)
	{
		return $this->checkRequiredFields($fields, $this->getListFieldInfo(
			$this->getFields(),
			['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]
		));
	}

	/**
	 * @param $fields
	 * @throws NotImplementedException
	 * @return Result
	 */
	protected function checkRequiredFieldsModify($fields)
	{
		throw new NotImplementedException('The method checkFieldsModify is not implemented.');
	}

	protected function checkRequiredFields($fields, array $fieldsInfo, $params=[])
	{
		$r = new Result();

		$addRequiredFields = is_set($params, '+required') ? $params['+required']:[];
		$delRequiredFields = is_set($params, '-required') ? $params['-required']:[];

		foreach ($this->prepareFieldInfos($fieldsInfo) as $name=>$info)
		{
			if(in_array($name, $delRequiredFields))
			{
				continue;
			}
			elseif($info['IS_REQUIRED'] == 'Y' || in_array($name, $addRequiredFields))
			{
				if(!is_set($fields, $name))
					$r->addError(new Error($this->convertKeysToCamelCase($name)));
			}
		}

		return $r;
	}
	//endregion
}
