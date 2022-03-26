<?php

namespace Bitrix\Main\ORM\Annotations;

use Bitrix\Main\Authentication\Context;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Type\Dictionary;

/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

trait AnnotationTrait
{
	/**
	 * @param Entity $entity
	 * @param false  $separateTable
	 *
	 * @return array|string
	 */
	public static function annotateEntity(Entity $entity, $ufOnly = false, $separateTable = false)
	{
		$entityNamespace = trim($entity->getNamespace(), '\\');
		$dataClass = $entity->getDataClass();

		$objectClass = $entity->getObjectClass();
		$objectClassName = $entity->getObjectClassName();
		$objectDefaultClassName = Entity::getDefaultObjectClassName($entity->getName());

		$collectionClass = $entity->getCollectionClass();
		$collectionClassName = $entity->getCollectionClassName();
		$collectionDefaultClassName = Entity::getDefaultCollectionClassName($entity->getName());

		$code = [];
		$objectCode = [];
		$collectionCode = [];

		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**"; // start class annotations
		$code[] = "\t * {$objectClassName}";
		$code[] = "\t * @see {$dataClass}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		foreach ($entity->getFields() as $field)
		{
			$objectFieldCode = [];
			$collectionFieldCode = [];

			if ($ufOnly && !($field instanceof UserTypeField))
			{
				continue;
			}

			if ($field instanceof ScalarField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateScalarField($field);
			}
			elseif ($field instanceof UserTypeField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateUserType($field);
			}
			elseif ($field instanceof ExpressionField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateExpression($field);
			}
			elseif ($field instanceof Reference)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateReference($field);
			}
			elseif ($field instanceof OneToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateOneToMany($field);
			}
			elseif ($field instanceof ManyToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateManyToMany($field);
			}

			$objectCode = array_merge($objectCode, $objectFieldCode);
			$collectionCode = array_merge($collectionCode, $collectionFieldCode);
		}

		// return if there is no fields to annotate (e.g. empty uf)
		if ($ufOnly && empty($objectCode))
		{
			return null;
		}

		$code = array_merge($code, $objectCode);

		if (!$ufOnly)
		{
			// common class methods
			$code[] = "\t *";
			$code[] = "\t * Common methods:";
			$code[] = "\t * ---------------";
			$code[] = "\t *";
			$code[] = "\t * @property-read \\".Entity::class." \$entity";
			$code[] = "\t * @property-read array \$primary";
			$code[] = "\t * @property-read int \$state @see \\".State::class;
			$code[] = "\t * @property-read \\".Dictionary::class." \$customData";
			$code[] = "\t * @property \\".Context::class." \$authContext";
			$code[] = "\t * @method mixed get(\$fieldName)";
			$code[] = "\t * @method mixed remindActual(\$fieldName)";
			$code[] = "\t * @method mixed require(\$fieldName)";
			$code[] = "\t * @method bool has(\$fieldName)";
			$code[] = "\t * @method bool isFilled(\$fieldName)";
			$code[] = "\t * @method bool isChanged(\$fieldName)";
			$code[] = "\t * @method {$objectClass} set(\$fieldName, \$value)";
			$code[] = "\t * @method {$objectClass} reset(\$fieldName)";
			$code[] = "\t * @method {$objectClass} unset(\$fieldName)";
			$code[] = "\t * @method void addTo(\$fieldName, \$value)";
			$code[] = "\t * @method void removeFrom(\$fieldName, \$value)";
			$code[] = "\t * @method void removeAll(\$fieldName)";
			$code[] = "\t * @method \\".Result::class." delete()";
			$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
			$code[] = "\t * @method mixed[] collectValues(\$valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, \$fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)";
			$code[] = "\t * @method \\".AddResult::class."|\\".UpdateResult::class."|\\".Result::class." save()";
			$code[] = "\t * @method static {$objectClass} wakeUp(\$data)";
		}

		//$code[] = "\t *";
		//$code[] = "\t * for parent class, @see \\".EntityObject::class;
		// xTODO we can put path to the original file here
		$code[] = "\t */"; // end class annotations
		$code[] = "\tclass {$objectDefaultClassName} {";
		$code[] = "\t\t/* @var {$dataClass} */";
		$code[] = "\t\tstatic public \$dataClass = '{$dataClass}';";
		$code[] = "\t\t/**";
		$code[] = "\t\t * @param bool|array \$setDefaultValues";
		$code[] = "\t\t */";
		$code[] = "\t\tpublic function __construct(\$setDefaultValues = true) {}";
		$code[] = "\t}"; // end class

		// compatibility with default classes
		if (strpos($objectClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultObjectClassName = Entity::getDefaultObjectClassName($entity->getName());

			// no need anymore as far as custom class inherits EO_
			//$code[] = "\tclass_alias('{$objectClass}', '{$entityNamespace}\\{$defaultObjectClassName}');";
		}

		$code[] = "}"; // end namespace

		// annotate collection class
		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**";
		$code[] = "\t * {$collectionClassName}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		$code = array_merge($code, $collectionCode);

		if (!$ufOnly)
		{
			$code[] = "\t *";
			$code[] = "\t * Common methods:";
			$code[] = "\t * ---------------";
			$code[] = "\t *";
			$code[] = "\t * @property-read \\".Entity::class." \$entity";
			$code[] = "\t * @method void add({$objectClass} \$object)";
			$code[] = "\t * @method bool has({$objectClass} \$object)";
			$code[] = "\t * @method bool hasByPrimary(\$primary)";
			$code[] = "\t * @method {$objectClass} getByPrimary(\$primary)";
			$code[] = "\t * @method {$objectClass}[] getAll()";
			$code[] = "\t * @method bool remove({$objectClass} \$object)";
			$code[] = "\t * @method void removeByPrimary(\$primary)";
			$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
			$code[] = "\t * @method static {$collectionClass} wakeUp(\$data)";
			$code[] = "\t * @method \\".Result::class." save(\$ignoreEvents = false)";
			$code[] = "\t * @method void offsetSet() ArrayAccess";
			$code[] = "\t * @method void offsetExists() ArrayAccess";
			$code[] = "\t * @method void offsetUnset() ArrayAccess";
			$code[] = "\t * @method void offsetGet() ArrayAccess";
			$code[] = "\t * @method void rewind() Iterator";
			$code[] = "\t * @method {$objectClass} current() Iterator";
			$code[] = "\t * @method mixed key() Iterator";
			$code[] = "\t * @method void next() Iterator";
			$code[] = "\t * @method bool valid() Iterator";
			$code[] = "\t * @method int count() Countable";
		}

		// xTODO we can put path to the original file here
		$code[] = "\t */";
		$code[] = "\tclass {$collectionDefaultClassName} implements \ArrayAccess, \Iterator, \Countable {";
		$code[] = "\t\t/* @var {$dataClass} */";
		$code[] = "\t\tstatic public \$dataClass = '{$dataClass}';";
		$code[] = "\t}"; // end class

		// compatibility with default classes
		if (strpos($collectionClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultCollectionClassName = Entity::getDefaultCollectionClassName($entity->getName());

			// no need anymore as far as custom class inherits EO_
			//$code[] = "\tclass_alias('{$entityNamespace}\\{$collectionClassName}', '{$entityNamespace}\\{$defaultCollectionClassName}');";
		}

		$code[] = "}"; // end namespace

		if (!$ufOnly)
		{
			// annotate Table class
			$dataClassName = $entity->getName().'Table';
			$queryClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Query';
			$resultClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Result';
			$entityClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Entity';

			$code[] = "namespace {$entityNamespace} {"; // start namespace

			if (!$separateTable)
			{
				$code[] = "\t/**";
			}

			$codeTable = [];
			$codeTable[] = " * @method static {$queryClassName} query()";
			$codeTable[] = " * @method static {$resultClassName} getByPrimary(\$primary, array \$parameters = [])";
			$codeTable[] = " * @method static {$resultClassName} getById(\$id)";
			$codeTable[] = " * @method static {$resultClassName} getList(array \$parameters = [])";
			$codeTable[] = " * @method static {$entityClassName} getEntity()";
			$codeTable[] = " * @method static {$objectClass} createObject(\$setDefaultValues = true)";
			$codeTable[] = " * @method static {$collectionClass} createCollection()";
			$codeTable[] = " * @method static {$objectClass} wakeUpObject(\$row)";
			$codeTable[] = " * @method static {$collectionClass} wakeUpCollection(\$rows)";

			if (!$separateTable)
			{
				// add tabs
				foreach ($codeTable as $i => $line)
				{
					$codeTable[$i] = "\t".$line;
				}

				$code = array_merge($code, $codeTable);
				$code[] = "\t */";
				$code[] = "\tclass {$dataClassName} extends \\".DataManager::class." {}";
			}

			// annotate Query class
			$code[] = "\t/**";
			$code[] = "\t * Common methods:";
			$code[] = "\t * ---------------";
			$code[] = "\t *";
			$code[] = "\t * @method {$resultClassName} exec()";
			$code[] = "\t * @method {$objectClass} fetchObject()";
			$code[] = "\t * @method {$collectionClass} fetchCollection()";
			$code[] = "\t *";
			$code[] = "\t * Custom methods:";
			$code[] = "\t * ---------------";
			$code[] = "\t *";

			foreach (get_class_methods($dataClass) as $method)
			{
				// search for with* methods
				if (substr($method, 0, 4) === 'with')
				{
					$reflectionMethod = new ReflectionMethod($dataClass, $method);

					if ($reflectionMethod->isStatic())
					{
						$arguments = [];

						// get parameters except the first one (query itself)
						foreach (array_slice($reflectionMethod->getParameters(), 1) as $parameter)
						{
							$arguments[] = '$'.$parameter->getName();
						}

						$argumentsMeta = join(', ', $arguments);

						$code[] = "\t * @see {$dataClass}::{$method}()";
						$code[] = "\t * @method {$queryClassName} {$method}({$argumentsMeta})";
					}
				}
			}

			$code[] = "\t */";
			$code[] = "\tclass {$queryClassName} extends \\".Query::class." {}";

			// annotate Result class
			$code[] = "\t/**";
			$code[] = "\t * @method {$objectClass} fetchObject()";
			$code[] = "\t * @method {$collectionClass} fetchCollection()";
			$code[] = "\t */";
			$code[] = "\tclass {$resultClassName} extends \\".\Bitrix\Main\ORM\Query\Result::class." {}";

			// annotate Entity class
			$code[] = "\t/**";
			$code[] = "\t * @method {$objectClass} createObject(\$setDefaultValues = true)";
			$code[] = "\t * @method {$collectionClass} createCollection()";
			$code[] = "\t * @method {$objectClass} wakeUpObject(\$row)";
			$code[] = "\t * @method {$collectionClass} wakeUpCollection(\$rows)";
			$code[] = "\t */";
			$code[] = "\tclass {$entityClassName} extends \\".Entity::class." {}";

			$code[] = "}"; // end namespace
		}

		if (!$separateTable)
		{
			return join("\n", $code);
		}
		else
		{
			return [
				join("\n", $codeTable),
				join("\n", $code)
			];
		}
	}

	public static function annotateScalarField(ScalarField $field)
	{
		// TODO no setter if it is reference-elemental (could expressions become elemental?)

		$objectClass = $field->getEntity()->getObjectClass();
		$getterDataType = $field->getGetterTypeHint();
		$setterDataType = $field->getSetterTypeHint();
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$getterDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$setterDataType}|\\".SqlExpression::class." \${$lName})";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$collectionCode[] = "\t * @method {$getterDataType}[] get{$uName}List()";

		if (!$field->isPrimary())
		{
			$objectCode[] = "\t * @method {$getterDataType} remindActual{$uName}()";
			$objectCode[] = "\t * @method {$getterDataType} require{$uName}()";

			$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
			$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

			$objectCode[] = "\t * @method {$getterDataType} fill{$uName}()";
			$collectionCode[] = "\t * @method {$getterDataType}[] fill{$uName}()";
		}

		return [$objectCode, $collectionCode];
	}

	public static function annotateUserType(UserTypeField $field)
	{
		// no setter
		$objectClass = $field->getEntity()->getObjectClass();

		/** @var ScalarField $scalarFieldClass */
		$scalarFieldClass = $field->getValueType();
		$dataType = (new $scalarFieldClass('TMP'))->getSetterTypeHint();
		$dataType = $field->isMultiple() ? $dataType.'[]' : $dataType;
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		list($objectCode, $collectionCode) = static::annotateExpression($field);

		// add setter
		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$dataType} \${$lName})";

		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateExpression(ExpressionField $field)
	{
		// no setter
		$objectClass = $field->getEntity()->getObjectClass();

		$scalarFieldClass = $field->getValueType();
		$dataType = (new $scalarFieldClass('TMP'))->getGetterTypeHint();
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$dataType} get{$uName}()";
		$objectCode[] = "\t * @method {$dataType} remindActual{$uName}()";
		$objectCode[] = "\t * @method {$dataType} require{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";

		$collectionCode[] = "\t * @method {$dataType}[] get{$uName}List()";

		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$objectCode[] = "\t * @method {$dataType} fill{$uName}()";
		$collectionCode[] = "\t * @method {$dataType}[] fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateReference(Reference $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionClass = $field->getEntity()->getCollectionClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();

		$getterTypeHint = $field->getGetterTypeHint();
		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$getterTypeHint} get{$uName}()";
		$objectCode[] = "\t * @method {$getterTypeHint} remindActual{$uName}()";
		$objectCode[] = "\t * @method {$getterTypeHint} require{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$setterTypeHint} \$object)";
		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$collectionCode[] = "\t * @method {$getterTypeHint}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionClass} get{$uName}Collection()";

		$objectCode[] = "\t * @method {$getterTypeHint} fill{$uName}()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateOneToMany(OneToMany $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$objectCode[] = "\t * @method void addTo{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionDataType} get{$uName}Collection()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateManyToMany(ManyToMany $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$objectCode[] = "\t * @method void addTo{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionDataType} get{$uName}Collection()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function tryToFindEntity($entityClass)
	{
		$entityClass = Entity::normalizeEntityClass($entityClass);

		if (!class_exists($entityClass))
		{
			// try to find remote entity
			$classParts = array_values(array_filter(
				explode('\\', strtolower($entityClass))
			));

			if ($classParts[0] == 'bitrix')
			{
				$moduleName = $classParts[1];
			}
			else
			{
				$moduleName = $classParts[0].'.'.$classParts[1];
			}

			if (!Loader::includeModule($moduleName) || !class_exists($entityClass))
			{
				return false;
			}
		}

		if ((new \ReflectionClass($entityClass))->isAbstract())
		{
			return false;
		}

		return true;
	}

	protected static function getFieldNameCamelCase($fieldName)
	{
		$upperFirstName = StringHelper::snake2camel($fieldName);
		$lowerFirstName = lcfirst($upperFirstName);

		return [$lowerFirstName, $upperFirstName];
	}
}
