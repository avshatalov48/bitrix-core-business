<?php

use Bitrix\Main\Loader;

Loader::registerClassAliases([
	'Bitrix\Main\Entity\Field\IReadable' => 'Bitrix\Main\ORM\Fields\IReadable',
	'Bitrix\Main\Entity\Field\IStorable' => 'Bitrix\Main\ORM\Fields\IStorable',
	'Bitrix\Main\Entity\BooleanField' => 'Bitrix\Main\ORM\Fields\BooleanField',
	'Bitrix\Main\Entity\DateField' => 'Bitrix\Main\ORM\Fields\DateField',
	'Bitrix\Main\Entity\DatetimeField' => 'Bitrix\Main\ORM\Fields\DatetimeField',
	'Bitrix\Main\Entity\EnumField' => 'Bitrix\Main\ORM\Fields\EnumField',
	'Bitrix\Main\Entity\ExpressionField' => 'Bitrix\Main\ORM\Fields\ExpressionField',
	'Bitrix\Main\Entity\IntegerField' => 'Bitrix\Main\ORM\Fields\IntegerField',
	'Bitrix\Main\Entity\FloatField' => 'Bitrix\Main\ORM\Fields\FloatField',
	'Bitrix\Main\Entity\StringField' => 'Bitrix\Main\ORM\Fields\StringField',
	'Bitrix\Main\Entity\TextField' => 'Bitrix\Main\ORM\Fields\TextField',
	'Bitrix\Main\Entity\CryptoField' => 'Bitrix\Main\ORM\Fields\CryptoField',
	'Bitrix\Main\Entity\ReferenceField' => 'Bitrix\Main\ORM\Fields\Relations\Reference',
	'Bitrix\Main\Entity\ScalarField' => 'Bitrix\Main\ORM\Fields\ScalarField',
	'Bitrix\Main\Entity\Field' => 'Bitrix\Main\ORM\Fields\Field',
	'Bitrix\Main\Entity\FieldError' => 'Bitrix\Main\ORM\Fields\FieldError',

	'Bitrix\Main\Entity\IValidator' => 'Bitrix\Main\ORM\Fields\Validators\IValidator',
	'Bitrix\Main\Entity\Validator\Base' => 'Bitrix\Main\ORM\Fields\Validators\Validator',
	'Bitrix\Main\Entity\Validator\Date' => 'Bitrix\Main\ORM\Fields\Validators\DateValidator',
	'Bitrix\Main\Entity\Validator\Enum' => 'Bitrix\Main\ORM\Fields\Validators\EnumValidator',
	'Bitrix\Main\Entity\Validator\Foreign' => 'Bitrix\Main\ORM\Fields\Validators\ForeignValidator',
	'Bitrix\Main\Entity\Validator\Length' => 'Bitrix\Main\ORM\Fields\Validators\LengthValidator',
	'Bitrix\Main\Entity\Validator\Range' => 'Bitrix\Main\ORM\Fields\Validators\RangeValidator',
	'Bitrix\Main\Entity\Validator\RegExp' => 'Bitrix\Main\ORM\Fields\Validators\RegExpValidator',
	'Bitrix\Main\Entity\Validator\Unique' => 'Bitrix\Main\ORM\Fields\Validators\UniqueValidator',

	'Bitrix\Main\Entity\INosqlPrimarySelector' => 'Bitrix\Main\ORM\Query\INosqlPrimarySelector',
	'Bitrix\Main\Entity\NosqlPrimarySelector' => 'Bitrix\Main\ORM\Query\NosqlPrimarySelector',
	'Bitrix\Main\Entity\Query' => 'Bitrix\Main\ORM\Query\Query',
	'Bitrix\Main\Entity\QueryChain' => 'Bitrix\Main\ORM\Query\Chain',
	'Bitrix\Main\Entity\QueryChainElement' => 'Bitrix\Main\ORM\Query\ChainElement',

	'Bitrix\Main\Entity\Query\Filter\Expression\Base' => 'Bitrix\Main\ORM\Query\Filter\Expressions\Expression',
	'Bitrix\Main\Entity\Query\Filter\Expression\Column' => 'Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression',
	'Bitrix\Main\Entity\Query\Filter\Expression\NullEx' => 'Bitrix\Main\ORM\Query\Filter\Expressions\NullExpression',

	'Bitrix\Main\Entity\DataManager' => 'Bitrix\Main\ORM\Data\DataManager',
	'Bitrix\Main\Entity\Result' => 'Bitrix\Main\ORM\Data\Result',
	'Bitrix\Main\Entity\AddResult' => 'Bitrix\Main\ORM\Data\AddResult',
	'Bitrix\Main\Entity\UpdateResult' => 'Bitrix\Main\ORM\Data\UpdateResult',
	'Bitrix\Main\Entity\DeleteResult' => 'Bitrix\Main\ORM\Data\DeleteResult',

	'Bitrix\Main\Entity\Query\Filter\Condition' => 'Bitrix\Main\ORM\Query\Filter\Condition',
	'Bitrix\Main\Entity\Query\Filter\ConditionTree' => 'Bitrix\Main\ORM\Query\Filter\ConditionTree',
	'Bitrix\Main\Entity\Query\Filter\Helper' => 'Bitrix\Main\ORM\Query\Filter\Helper',
	'Bitrix\Main\Entity\Query\Filter\Operator' => 'Bitrix\Main\ORM\Query\Filter\Operator',

	'Bitrix\Main\Entity\Query\Expression' => 'Bitrix\Main\ORM\Query\Expression',
	'Bitrix\Main\Entity\Query\Join' => 'Bitrix\Main\ORM\Query\Join',
	'Bitrix\Main\Entity\Query\Union' => 'Bitrix\Main\ORM\Query\Union',
	'Bitrix\Main\Entity\Query\UnionCondition' => 'Bitrix\Main\ORM\Query\UnionCondition',

	'Bitrix\Main\Entity\Base' => 'Bitrix\Main\ORM\Entity',
	'Bitrix\Main\Entity\EntityError' => 'Bitrix\Main\ORM\EntityError',
	'Bitrix\Main\Entity\Event' => 'Bitrix\Main\ORM\Event',
	'Bitrix\Main\Entity\EventResult' => 'Bitrix\Main\ORM\EventResult',

	'Bitrix\Main\ORM\UField' => 'Bitrix\Main\Entity\UField',
	'Bitrix\Main\Type\ArrayHelper' => 'Bitrix\Main\Type\Collection',
]);

Loader::registerAutoLoadClasses(
	"main", ["Bitrix\\Main\\Entity\\UField" => "include/deprecated/ufield.php"]
);
