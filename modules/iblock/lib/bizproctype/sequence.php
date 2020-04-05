<?
namespace Bitrix\Iblock\BizprocType;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Loader;

if (Loader::requireModule("bizproc"))
{
	class Sequence extends UserTypeProperty
	{
		public static function getType()
		{
			return FieldType::INT;
		}

		/**
		 * @param FieldType $fieldType Document field type.
		 * @param array $field Form field.
		 * @param mixed $value Field value.
		 * @param bool $allowSelection Allow selection flag.
		 * @param int $renderMode Control render mode.
		 * @return string
		 */
		public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
		{
			return self::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
		}

		/**
		 * @param FieldType $fieldType Document field type.
		 * @param array $field Form field.
		 * @param mixed $value Field value.
		 * @param bool $allowSelection Allow selection flag.
		 * @param int $renderMode Control render mode.
		 * @return string
		 */
		public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
		{
			$typeValue = [];
			if (!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
			{
				$value = [$value];
			}

			foreach ($value as $v)
			{
				if (!\CBPActivity::isExpression($v))
				{
					$typeValue[] = $v;
				}
			}

			$controls = [];
			foreach ($typeValue as $k => $v)
			{
				$singleField = $field;
				$singleField["Index"] = $k;
				$controls[] = self::renderControlSingle($fieldType, $singleField, $v, $allowSelection, $renderMode);
			}

			return static::wrapCloneableControls($controls, static::generateControlName($field));
		}

		/**
		 * Low-level control rendering method
		 * @param FieldType $fieldType
		 * @param array $field
		 * @param mixed $value
		 * @param bool $allowSelection
		 * @param int $renderMode
		 * @return string - HTML rendering
		 */
		protected static function renderControl(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
		{
			$name = static::generateControlName($field);
			$controlId = static::generateControlId($field);
			$className = static::generateControlClassName($fieldType, $field);

			$options = $fieldType->getOptions();

			$iblockId = self::getIblockId($fieldType);
			$propertyId = 0;
			$queryObject = \CIBlockProperty::getByID(substr($field["Field"], strlen("PROPERTY_")), $iblockId);
			if ($property = $queryObject->fetch())
			{
				$propertyId = $property["ID"];
			}

			if ($value)
			{
				$value = (int) $value;
			}
			else
			{
				$sequence = new \CIBlockSequence($iblockId, $propertyId);
				$value = $sequence->getCurrent();
			}

			$readonly = ((isset($options["write"]) && $options["write"] == "Y") ? "" : "readonly");

			return '<input '.htmlspecialcharsbx($readonly).' type="text" class="'.
				htmlspecialcharsbx($className).'" size="40" id="'.htmlspecialcharsbx($controlId).'" name="'
				.htmlspecialcharsbx($name).'" value="'.htmlspecialcharsbx((string) $value).'"/>';
		}

		private static function getIblockId(FieldType $fieldType)
		{
			$documentType = $fieldType->getDocumentType();
			$type = explode('_', $documentType[2]);
			return intval($type[1]);
		}
	}
}