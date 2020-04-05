<?php

/**
 * All classes will be loaded immediately.
 * In case of performance issues need to make alias-on-demand.
 * E.g. Loader::registerClassAlias(original, alias), and call class_alias only when Loader gets request on particular class.
 * UPDATE: Loader idea doesn't work because of type hints and instanceof - alias should be loaded before these constructions.
 */

class_alias('Bitrix\Main\ORM\Fields\IReadable', 'Bitrix\Main\Entity\Field\IReadable');
class_alias('Bitrix\Main\ORM\Fields\IStorable', 'Bitrix\Main\Entity\Field\IStorable');
class_alias('Bitrix\Main\ORM\Fields\BooleanField', 'Bitrix\Main\Entity\BooleanField');
class_alias('Bitrix\Main\ORM\Fields\DateField', 'Bitrix\Main\Entity\DateField');
class_alias('Bitrix\Main\ORM\Fields\DatetimeField', 'Bitrix\Main\Entity\DatetimeField');
class_alias('Bitrix\Main\ORM\Fields\EnumField', 'Bitrix\Main\Entity\EnumField');
class_alias('Bitrix\Main\ORM\Fields\ExpressionField', 'Bitrix\Main\Entity\ExpressionField');
class_alias('Bitrix\Main\ORM\Fields\IntegerField', 'Bitrix\Main\Entity\IntegerField');
class_alias('Bitrix\Main\ORM\Fields\FloatField', 'Bitrix\Main\Entity\FloatField');
class_alias('Bitrix\Main\ORM\Fields\StringField', 'Bitrix\Main\Entity\StringField');
class_alias('Bitrix\Main\ORM\Fields\TextField', 'Bitrix\Main\Entity\TextField');
class_alias('Bitrix\Main\ORM\Fields\CryptoField', 'Bitrix\Main\Entity\CryptoField');
class_alias('Bitrix\Main\ORM\Fields\Relations\Reference', 'Bitrix\Main\Entity\ReferenceField');
class_alias('Bitrix\Main\ORM\Fields\ScalarField', 'Bitrix\Main\Entity\ScalarField');
class_alias('Bitrix\Main\ORM\Fields\Field', 'Bitrix\Main\Entity\Field');
class_alias('Bitrix\Main\ORM\Fields\FieldError', 'Bitrix\Main\Entity\FieldError');

class_alias('Bitrix\Main\ORM\Fields\Validators\IValidator', 'Bitrix\Main\Entity\IValidator');
class_alias('Bitrix\Main\ORM\Fields\Validators\Validator', 'Bitrix\Main\Entity\Validator\Base');
class_alias('Bitrix\Main\ORM\Fields\Validators\DateValidator', 'Bitrix\Main\Entity\Validator\Date');
class_alias('Bitrix\Main\ORM\Fields\Validators\EnumValidator', 'Bitrix\Main\Entity\Validator\Enum');
class_alias('Bitrix\Main\ORM\Fields\Validators\ForeignValidator', 'Bitrix\Main\Entity\Validator\Foreign');
class_alias('Bitrix\Main\ORM\Fields\Validators\LengthValidator', 'Bitrix\Main\Entity\Validator\Length');
class_alias('Bitrix\Main\ORM\Fields\Validators\RangeValidator', 'Bitrix\Main\Entity\Validator\Range');
class_alias('Bitrix\Main\ORM\Fields\Validators\RegExpValidator', 'Bitrix\Main\Entity\Validator\RegExp');
class_alias('Bitrix\Main\ORM\Fields\Validators\UniqueValidator', 'Bitrix\Main\Entity\Validator\Unique');

class_alias('Bitrix\Main\ORM\Query\INosqlPrimarySelector', 'Bitrix\Main\Entity\INosqlPrimarySelector');
class_alias('Bitrix\Main\ORM\Query\NosqlPrimarySelector', 'Bitrix\Main\Entity\NosqlPrimarySelector');
class_alias('Bitrix\Main\ORM\Query\Query', 'Bitrix\Main\Entity\Query');
class_alias('Bitrix\Main\ORM\Query\Chain', 'Bitrix\Main\Entity\QueryChain');
class_alias('Bitrix\Main\ORM\Query\ChainElement', 'Bitrix\Main\Entity\QueryChainElement');

class_alias('Bitrix\Main\ORM\Query\Filter\Expressions\Expression', 'Bitrix\Main\Entity\Query\Filter\Expression\Base');
class_alias('Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression', 'Bitrix\Main\Entity\Query\Filter\Expression\Column');
class_alias('Bitrix\Main\ORM\Query\Filter\Expressions\NullExpression', 'Bitrix\Main\Entity\Query\Filter\Expression\NullEx');

class_alias('Bitrix\Main\ORM\Data\DataManager', 'Bitrix\Main\Entity\DataManager');
class_alias('Bitrix\Main\ORM\Data\Result', 'Bitrix\Main\Entity\Result');
class_alias('Bitrix\Main\ORM\Data\AddResult', 'Bitrix\Main\Entity\AddResult');
class_alias('Bitrix\Main\ORM\Data\UpdateResult', 'Bitrix\Main\Entity\UpdateResult');
class_alias('Bitrix\Main\ORM\Data\DeleteResult', 'Bitrix\Main\Entity\DeleteResult');

class_alias('Bitrix\Main\ORM\Query\Filter\Condition', 'Bitrix\Main\Entity\Query\Filter\Condition');
class_alias('Bitrix\Main\ORM\Query\Filter\ConditionTree', 'Bitrix\Main\Entity\Query\Filter\ConditionTree');
class_alias('Bitrix\Main\ORM\Query\Filter\Helper', 'Bitrix\Main\Entity\Query\Filter\Helper');
class_alias('Bitrix\Main\ORM\Query\Filter\Operator', 'Bitrix\Main\Entity\Query\Filter\Operator');

class_alias('Bitrix\Main\ORM\Query\Expression', 'Bitrix\Main\Entity\Query\Expression');
class_alias('Bitrix\Main\ORM\Query\Join', 'Bitrix\Main\Entity\Query\Join');
class_alias('Bitrix\Main\ORM\Query\Union', 'Bitrix\Main\Entity\Query\Union');
class_alias('Bitrix\Main\ORM\Query\UnionCondition', 'Bitrix\Main\Entity\Query\UnionCondition');

class_alias('Bitrix\Main\ORM\Entity', 'Bitrix\Main\Entity\Base');
class_alias('Bitrix\Main\ORM\EntityError', 'Bitrix\Main\Entity\EntityError');
class_alias('Bitrix\Main\ORM\Event', 'Bitrix\Main\Entity\Event');
class_alias('Bitrix\Main\ORM\EventResult', 'Bitrix\Main\Entity\EventResult');

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"main", ["Bitrix\\Main\\Entity\\UField" => "include/deprecated/ufield.php"]
);

class_alias('Bitrix\Main\Entity\UField', 'Bitrix\Main\ORM\UField');