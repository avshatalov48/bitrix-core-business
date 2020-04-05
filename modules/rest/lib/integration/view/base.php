<?php


namespace Bitrix\Rest\Integration\View;


use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

abstract class Base
{
	abstract public function getFields();

	final public function prepareFieldInfos($fields)
	{
		$result = [];
		foreach($fields as $name => $info)
		{
			$attributs = isset($info['ATTRIBUTES']) ? $info['ATTRIBUTES'] : [];

			if(in_array(Attributes::HIDDEN, $attributs, true))
			{
				continue;
			}

			$result[$name] = $this->prepareFieldAttributs($info, $attributs);
		}

		return $result;
	}

	protected function prepareFieldAttributs($info, $attributs)
	{
		return array(
			'TYPE' => $info['TYPE'],
			'IS_REQUIRED' => in_array(Attributes::REQUIRED, $attributs, true),
			'IS_READ_ONLY' => in_array(Attributes::READONLY, $attributs, true),
			'IS_IMMUTABLE' => in_array(Attributes::IMMUTABLE, $attributs, true)
		);
	}

	final public function getListFieldInfo(array $fieldsInfo, $params=[])
	{
		$list = [];

		$filter = isset($params['filter'])?$params['filter']:[];
		$ignoredAttributes = isset($filter['ignoredAttributes'])?$filter['ignoredAttributes']:[];
		$ignoredFields = isset($filter['ignoredFields'])?$filter['ignoredFields']:[];
		$skipFields = isset($filter['skipFields'])?$filter['skipFields']:[];

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

	//region convert keys to snake case
	final public function convertKeysToSnakeCaseFields($fields)
	{
		return $this->convertKeysToSnakeCase($fields);
	}

	final public function convertKeysToSnakeCaseSelect($fields)
	{
		$converter = new Converter(Converter::VALUES | Converter::TO_SNAKE | Converter::TO_SNAKE_DIGIT | Converter::TO_UPPER);
		return $converter->process($fields);
	}

	final public function convertKeysToSnakeCaseFilter($fields)
	{
		return $this->convertKeysToSnakeCase($fields);
	}

	final public function convertKeysToSnakeCaseOrder($fields)
	{
		$result = [];

		$converter = new Converter(Converter::VALUES | Converter::TO_UPPER);
		$converterForKey = new Converter(Converter::KEYS | Converter::TO_SNAKE | Converter::TO_SNAKE_DIGIT | Converter::TO_UPPER);

		foreach ($converter->process($fields) as $key=>$value)
		{
			$result[$converterForKey->process($key)] = $value;
		}

		return $result;
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		return $arguments;
	}

	final protected function convertKeysToSnakeCase($data)
	{
		$converter = new Converter(Converter::KEYS | Converter::RECURSIVE | Converter::TO_SNAKE | Converter::TO_SNAKE_DIGIT | Converter::TO_UPPER);
		return $converter->process($data);
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

	public function internalizeFieldsList($arguments, $fieldsInfo=[])
	{
		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		$fieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

		$filter = isset($arguments['filter']) ? $this->internalizeFilterFields($arguments['filter'], $fieldsInfo):[];
		$select = isset($arguments['select']) ? $this->internalizeSelectFields($arguments['select'], $fieldsInfo):[];
		$order = isset($arguments['order']) ? $this->internalizeOrderFields($arguments['order'], $fieldsInfo):[];

		return [
			'filter'=>$filter,
			'select'=>$select,
			'order'=>$order,
		];
	}

	public function internalizeFieldsAdd($fields, $fieldsInfo=[])
	{
		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		return $this->internalizeFields(
			$fields,
			$this->getListFieldInfo(
				$fieldsInfo,
				['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY]]]
			)
		);
	}

	public function internalizeFieldsUpdate($fields, $fieldsInfo=[])
	{
		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		return $this->internalizeFields(
			$fields,
			$this->getListFieldInfo(
				$fieldsInfo,
				['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE]]]
			)
		);
	}

	/**
	 * @param $fields
	 * @param array $fieldsInfo
	 * @return array
	 * @throws NotImplementedException
	 */
	final protected function internalizeFields($fields, array $fieldsInfo)
	{
		$result = [];

		foreach ($fields as $name=>$value)
		{
			$info = isset($fieldsInfo[$name]) ? $fieldsInfo[$name]:null;
			if(!$info)
			{
				continue;
			}

			$r = $this->internalizeValue($value, $info);

			if($r->isSuccess() === false)
			{
				continue;
			}

			$result[$name] = $r->getData()[0];
		}
		return $result;
	}

	final protected function internalizeValue($value, $info)
	{
		$r = new Result();

		$type = isset($info['TYPE']) ? $info['TYPE']:'';

		if($type === DataType::TYPE_FLOAT)
		{
			$value = floatval($value);
		}
		elseif($type === DataType::TYPE_INT)
		{
			$value = (int)$value;
		}
		elseif($type === DataType::TYPE_DATETIME)
		{
			$date = $this->internalizeDateTimeValue($value);

			if($date->isSuccess())
			{
				$value = $date->getData()[0];
			}
			else
			{
				$r->addErrors($date->getErrors());
			}
		}
		elseif($type === DataType::TYPE_DATE)
		{
			$date = $this->internalizeDateValue($value);

			if($date->isSuccess())
			{
				$value = $date->getData()[0];
			}
			else
			{
				$r->addErrors($date->getErrors());
			}
		}
		elseif($type === DataType::TYPE_FILE)
		{
			$value = $this->internalizeFileValue($value);
		}
		else
		{
			$r = $this->internalizeExtendedTypeValue($value, $info);
			if($r->isSuccess())
			{
				$value = $r->getData()[0];
			}
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateValue($value)
	{
		$r = new Result();

		$date = $this->internalizeDate($value);

		if($date instanceof Date)
		{
			$value = $date;
		}
		else
		{
			$r->addError(new Error('Wrong type deta'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateTimeValue($value)
	{
		$r = new Result();

		$date = $this->internalizeDateTime($value);

		if($date instanceof DateTime)
		{
			$value = $date;
		}
		else
		{
			$r->addError(new Error('Wrong type datetime'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	final protected function internalizeDate($value)
	{
		if($value === '')
		{
			$date = '';
		}
		else
		{
			$time = strtotime($value);
			$date = ($time) ? \Bitrix\Main\Type\Date::createFromTimestamp($time):'';
		}
		return $date;
	}

	final protected function internalizeDateTime($value)
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
		return $date;
	}

	final protected function internalizeFileValue($value)
	{
		$result = [];

		$remove = isset($value['REMOVE']) && is_string($value['REMOVE']) && strtoupper($value['REMOVE']) === 'Y';
		$data = isset($value['FILE_DATA']) ? $value['FILE_DATA'] : [];

		$data = $this->parserFileValue($data);

		$content = isset($data['CONTENT']) ? $data['CONTENT']:'';
		$name = isset($data['NAME']) ? $data['NAME']:'';

		if(is_string($content) && $content !== '')
		{
			// Add/replace file
			$fileInfo = \CRestUtil::saveFile($content, $name);
			if(is_array($fileInfo))
			{
				$result = $fileInfo;
			}
		}
		elseif($remove)
		{
			// Remove file
			$result = ['del'=>'Y'];
		}

		return  $result;
	}

	protected function internalizeExtendedTypeValue($value, $info)
	{
		$r = new Result();

		$r->setData([$value]);

		return $r;
	}

	final protected function parserFileValue(array $data)
	{
		$count = count($data);

		if($count > 1)
		{
			$name = $data[0];
			$content = $data[1];
		}
		elseif($count === 1)
		{
			$name = '';
			$content = $data[0];
		}
		else
		{
			$name = '';
			$content = '';
		}

		return ['CONTENT'=>$content, 'NAME'=>$name];
	}

	final protected function internalizeFilterFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		if(is_array($fields) && count($fields)>0)
		{
			$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

			foreach ($fields as $rawName=>$value)
			{
				$field = \CSqlUtil::GetFilterOperation($rawName);

				$info = isset($listFieldsInfo[$field['FIELD']]) ? $listFieldsInfo[$field['FIELD']]:null;
				if(!$info)
				{
					continue;
				}

				$r = $this->internalizeValue($value, $info);

				if($r->isSuccess() === false)
				{
					continue;
				}

				$operation = substr($rawName, 0, strlen($rawName) - strlen($field['FIELD']));
				if(isset($info['FORBIDDEN_FILTERS'])
					&& is_array($info['FORBIDDEN_FILTERS'])
					&& in_array($operation, $info['FORBIDDEN_FILTERS'], true))
				{
					continue;
				}

				$result[$rawName] = $r->getData()[0];
			}
		}

		return $result;
	}

	final protected function internalizeSelectFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

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

	final protected function internalizeOrderFields($fields, array $fieldsInfo)
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;

		if(is_array($fields)
			&& count($fields)>0)
		{
			$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

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

	final protected function internalizeListFields($list, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		$listFieldsInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

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
	final protected function externalizeValue($name, $value, $fields, $fieldsInfo)
	{
		$r = new Result();

		$type = isset($fieldsInfo[$name]['TYPE']) ? $fieldsInfo[$name]['TYPE']:'';

		if(empty($value))
		{
			$value = null;
		}
		else
		{
			if($type === DataType::TYPE_FLOAT)
			{
				$value = floatval($value);
			}
			elseif($type === DataType::TYPE_INT)
			{
				$value = (int)$value;
			}
			elseif($type === DataType::TYPE_DATE)
			{
				$date = $this->externalizeDateValue($value);

				if($date->isSuccess())
				{
					$value = $date->getData()[0];
				}
				else
				{
					$r->addErrors($date->getErrors());
				}
			}
			elseif($type === DataType::TYPE_DATETIME)
			{
				$date = $this->externalizeDateTimeValue($value);

				if($date->isSuccess())
				{
					$value = $date->getData()[0];
				}
				else
				{
					$r->addErrors($date->getErrors());
				}
			}
			elseif($type === DataType::TYPE_FILE)
			{
				$value = $this->externalizeFileValue($name, $value, $fields);
			}
			else
			{
				$r = $this->externalizeExtendedTypeValue($name, $value, $fields, $fieldsInfo);
				if($r->isSuccess())
				{
					$value = $r->getData()[0];
				}
			}
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	final protected function externalizeFields($fields, $fieldsInfo)
	{
		$result = [];

		if(is_array($fields) && count($fields)>0)
		{
			foreach($fields as $name => $value)
			{
				$info = isset($fieldsInfo[$name]) ? $fieldsInfo[$name] : null;
				if(!$info)
				{
					continue;
				}

				$r = $this->externalizeValue($name, $value, $fields, $fieldsInfo);

				if($r->isSuccess() === false)
				{
					continue;
				}

				$result[$name] = $r->getData()[0];
			}
		}
		return $result;
	}

	final protected function externalizeDateValue($value)
	{
		$r = new Result();

		$time = strtotime($value);
		$value = ($time) ? \Bitrix\Main\Type\Date::createFromTimestamp($time):'';

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	final protected function externalizeDateTimeValue($value)
	{
		$r = new Result();

		$time = strtotime($value);
		$value = ($time) ? \Bitrix\Main\Type\DateTime::createFromTimestamp($time):'';

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	/**
	 * @param $name
	 * @param $value
	 * @return string
	 * @throws NotImplementedException
	 */
	protected function externalizeFileValue($name, $value, $fields)
	{
		throw new NotImplementedException('Externalize file. The method externalizeFile is not implemented.');
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $fields
	 * @param $fieldsInfo
	 * @return Result
	 */
	protected function externalizeExtendedTypeValue($name, $value, $fields, $fieldsInfo)
	{
		$r = new Result();

		$r->setData([$value]);

		return $r;
	}

	public function externalizeListFields($list, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		$listFieldInfo = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]);

		if(is_array($list) && count($list)>0)
		{
			foreach($list as $k=>$fields)
				$result[$k] = $this->externalizeFields($fields, $listFieldInfo);
		}
		return $result;
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

	public function externalizeFieldsGet($fields, $fieldsInfo=[])
	{
		$fieldsInfo = empty($fieldsInfo) ? $this->getFields():$fieldsInfo;

		return $this->externalizeFields(
			$fields,
			$this->getListFieldInfo(
				$fieldsInfo,
				['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN]]]
			)
		);
	}
	// endregion

	//region convert keys to camel case
	final public function convertKeysToCamelCase($fields)
	{
		return Converter::toJson()
			->process($fields);
	}
	// endregion

	//region check fields
	final public function checkFieldsAdd($fields)
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsAdd($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));

		return $r;
	}

	final public function checkFieldsUpdate($fields)
	{
		$r = new Result();

		$required = $this->checkRequiredFieldsUpdate($fields);
		if(!$required->isSuccess())
			$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));

		return $r;
	}

	public function checkFieldsList($arguments)
	{
		return new Result();
	}

	public function checkArguments($name, $arguments)
	{
		return new Result();
	}

	final protected function checkRequiredFieldsAdd($fields)
	{
		return $this->checkRequiredFields($fields, $this->getListFieldInfo(
			$this->getFields(),
			['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY]]]
		));
	}

	final protected function checkRequiredFieldsUpdate($fields)
	{
		return $this->checkRequiredFields($fields, $this->getListFieldInfo(
			$this->getFields(),
			['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE]]]
		));
	}

	final protected function checkRequiredFields($fields, array $fieldsInfo, $params=[])
	{
		$r = new Result();

		$addRequiredFields = isset($params['+required']) ? $params['+required']:[];
		$delRequiredFields = isset($params['-required']) ? $params['-required']:[];

		foreach ($this->prepareFieldInfos($fieldsInfo) as $name=>$info)
		{
			if(in_array($name, $delRequiredFields))
			{
				continue;
			}
			elseif($info['IS_REQUIRED'] == 'Y' || in_array($name, $addRequiredFields))
			{
				if(!isset($fields[$name]))
					$r->addError(new Error($this->convertKeysToCamelCase($name)));
			}
		}

		return $r;
	}
	//endregion
}