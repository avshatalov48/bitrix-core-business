<?php

namespace Bitrix\Bizproc\Workflow\Template;

class SourceType
{
	const DocumentField = 'Document';
	const Parameter = 'Parameter';
	const Variable = 'Variable';
	const Constant = 'Constant';
	const GlobalConstant = 'GlobalConst';
	const GlobalVariable = 'GlobalVar';
	const System = 'System';
	const Activity = 'Activity';
	const CalcFunction = 'Function';

	public static function isType($type)
	{
		$ref = new \ReflectionClass(__CLASS__);
		$constants = array_flip($ref->getConstants());

		return (isset($constants[$type]));
	}

	public static function getObjectSourceType($objectName, $fieldName): ?array
	{
		if (mb_substr($fieldName, -10) === '_printable')
		{
			$fieldName = mb_substr($fieldName, 0, -10);
		}

		if ($objectName === static::DocumentField)
		{
			return [static::DocumentField, $fieldName];
		}
		elseif ($objectName === 'Template' || $objectName === static::Parameter)
		{
			return [static::Parameter, $fieldName];
		}
		elseif ($objectName === static::Variable)
		{
			return [static::Variable, $fieldName];
		}
		elseif ($objectName === static::Constant)
		{
			return [static::Constant, $fieldName];
		}
		elseif ($objectName === static::GlobalConstant)
		{
			return [static::GlobalConstant, $fieldName];
		}
		elseif ($objectName === static::GlobalVariable)
		{
			return [static::GlobalVariable, $fieldName];
		}
		elseif (in_array($objectName, ['Workflow', 'User', static::System]))
		{
			// ['System', 'User', 'Id'] => User:Id
			return [static::System, $objectName, $fieldName];
		}
		elseif ($objectName)
		{
			// ['Activity', 'A49308_81258_73524_17187', 'TITLE'] => A49308_81258_73524_17187:TITLE
			return [static::Activity, $objectName, $fieldName];
		}

		return null;
	}
}