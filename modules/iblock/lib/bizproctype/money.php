<?php
namespace Bitrix\Iblock\BizprocType;

use Bitrix\Main,
	Bitrix\Bizproc\FieldType,
	Bitrix\Main\Localization\Loc,
	Bitrix\Currency\Integration\IblockMoneyProperty,
	Bitrix\Currency\CurrencyTable,
	Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;

if (Loader::requireModule('bizproc'))
{
	class Money extends UserTypeProperty
	{
		protected static function formatValuePrintable(FieldType $fieldType, $value)
		{
			$explode = is_string($value) ? explode(IblockMoneyProperty::SEPARATOR, $value) : array();
			$currentValue = $explode[0] ? $explode[0] : '';
			$currentCurrency = $explode[1] ? $explode[1] : '';

			if(!$currentCurrency)
				return intval($currentValue) ? $currentValue : '';

			if(CurrencyManager::isCurrencyExist($currentCurrency))
			{
				$format = \CCurrencyLang::getCurrencyFormat($currentCurrency);
				$separators = \CCurrencyLang::getSeparators();
				$thousandsSep = $separators[$format['THOUSANDS_VARIANT']];
				$currentValue = number_format($currentValue, $format['DECIMALS'], $format['DEC_POINT'], $thousandsSep);
				if($format['THOUSANDS_VARIANT'] == \CCurrencyLang::SEP_NBSPACE)
					$currentValue = str_replace(' ', '&nbsp;', $currentValue);
				return preg_replace('/(^|[^&])#/', '${1}'.$currentValue, $format['FORMAT_STRING']);
			}

			return $currentValue;
		}

		/**
		 * @param FieldType $fieldType Document field object.
		 * @param array $field Form field information.
		 * @param mixed $value Field value.
		 * @param bool $allowSelection Allow selection flag.
		 * @param int $renderMode Control render mode.
		 * @return string
		 */
		public static function renderControlSingle(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
		{
			$selectorValue = null;
			if(\CBPActivity::isExpression($value))
			{
				$selectorValue = $value;
				$value = null;
			}

			$property = static::getUserType($fieldType);

			if(!empty($property['GetPublicEditHTML']))
			{
				$fieldName = static::generateControlName($field);
				$renderResult = call_user_func_array(
					$property['GetPublicEditHTML'],
					array(
						array(
							'IBLOCK_ID' => self::getIblockId($fieldType),
							'USER_TYPE_SETTINGS' => $fieldType->getOptions(),
							'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N',
							'IS_REQUIRED' => $fieldType->isRequired() ? 'Y' : 'N',
							'PROPERTY_USER_TYPE' => $property
						),
						array('VALUE' => $value),
						array(
							'FORM_NAME' => $field['Form'],
							'VALUE' => $fieldName,
							'DESCRIPTION' => '',
						),
						true
					)
				);
			}
			else
			{
				$renderResult = static::renderControl($fieldType, $field, $value, $allowSelection, $renderMode);
			}

			if($allowSelection)
			{
				$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
			}

			return $renderResult;
		}

		/**
		 * @param FieldType $fieldType Document field object.
		 * @param array $field Form field information.
		 * @param mixed $value Field value.
		 * @param bool $allowSelection Allow selection flag.
		 * @param int $renderMode Control render mode.
		 * @return string
		 */
		public static function renderControlMultiple(FieldType $fieldType, array $field, $value, $allowSelection, $renderMode)
		{
			$selectorValue = null;
			$typeValue = array();
			if(!is_array($value) || is_array($value) && \CBPHelper::isAssociativeArray($value))
				$value = array($value);

			foreach ($value as $v)
			{
				if (\CBPActivity::isExpression($v))
					$selectorValue = $v;
				else
					$typeValue[] = $v;
			}
			// need to show at least one control
			if(empty($typeValue))
				$typeValue[] = null;

			$controls = array();

			$property = static::getUserType($fieldType);

			if(!empty($property['GetPublicEditHTML']))
			{
				foreach($typeValue as $k => $v)
				{
					$singleField = $field;
					$singleField['Index'] = $k;
					$fieldName = static::generateControlName($singleField);
					$controls[] = call_user_func_array(
						$property['GetPublicEditHTML'],
						array(
							array(
								'IBLOCK_ID' => self::getIblockId($fieldType),
								'USER_TYPE_SETTINGS' => $fieldType->getOptions(),
								'MULTIPLE' => $fieldType->isMultiple() ? 'Y' : 'N',
								'IS_REQUIRED' => $fieldType->isRequired() ? 'Y' : 'N',
								'PROPERTY_USER_TYPE' => $property
							),
							array('VALUE' => $v),
							array(
								'FORM_NAME' => $singleField['Form'],
								'VALUE' => $fieldName,
								'DESCRIPTION' => '',
							),
							true
						)
					);
				}
			}
			else
			{
				foreach($typeValue as $k => $v)
				{
					$singleField = $field;
					$singleField['Index'] = $k;
					$controls[] = static::renderControl(
						$fieldType,
						$singleField,
						$v,
						$allowSelection,
						$renderMode
					);
				}
			}

			$renderResult = static::wrapCloneableControls($controls, static::generateControlName($field));

			if($allowSelection)
			{
				$renderResult .= static::renderControlSelector($field, $selectorValue, true, '', $fieldType);
			}

			return $renderResult;
		}

		/**
		 * @param array $controls
		 * @param string $wrapperId
		 * @return string
		 */
		protected static function wrapCloneableControls(array $controls, $wrapperId)
		{
			$wrapperId = Main\Text\HtmlFilter::encode((string)$wrapperId);
			$renderResult = '<table width="100%" border="0" cellpadding="2" cellspacing="2" id="BizprocCloneable_'
				.$wrapperId.'">';

			foreach($controls as $control)
			{
				$renderResult .= '<tr><td>'.$control.'</td></tr>';
			}
			$renderResult .= '</table>';

			$separator = Main\Text\HtmlFilter::encode((string)IblockMoneyProperty::SEPARATOR);
			$listCurrency = array();
			$queryObject = CurrencyTable::getList(array(
				'select' => array(
					'CURRENCY',
					'BASE',
					'NAME' => 'CURRENT_LANG_FORMAT.FULL_NAME',
					'FORMAT' => 'CURRENT_LANG_FORMAT.FORMAT_STRING',
					'DEC_POINT' => 'CURRENT_LANG_FORMAT.DEC_POINT',
					'THOUSANDS_VARIANT' => 'CURRENT_LANG_FORMAT.THOUSANDS_VARIANT',
					'DECIMALS' => 'CURRENT_LANG_FORMAT.DECIMALS',
				),
				'filter' => array(),
				'order' => array('SORT' => 'ASC', 'CURRENCY' => 'ASC')
			));
			$separators = \CCurrencyLang::getSeparators();
			while($currency = $queryObject->fetch())
			{
				$currency['SEPARATOR'] = $separators[$currency['THOUSANDS_VARIANT']];
				$currency['SEPARATOR_STRING'] = $currency['DEC_POINT'];
				$currency['SEPARATOR_STRING'] .= ($currency['THOUSANDS_VARIANT'] == \CCurrencyLang::SEP_SPACE
					|| $currency['THOUSANDS_VARIANT'] == \CCurrencyLang::SEP_NBSPACE) ?
					Loc::getMessage('CIMP_SEPARATOR_SPACE') : $currency['SEPARATOR'];
				$listCurrency[$currency['CURRENCY']] = $currency;
			}

			$renderResult .= '<script>
			function cloneTypeControlMoney(tableID, wrapperId, separator, listCurrency)
			{
				var tbl = document.getElementById(tableID);
				var cnt = tbl.rows.length;
				var oRow = tbl.insertRow(cnt);
				var oCell = oRow.insertCell(0);
				var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
				var p = 0, s, e, n;
				while (true)
				{
					s = sHTML.indexOf(\'[n\', p);
					if (s < 0)
						break;
					e = sHTML.indexOf(\']\', s);
					if (e < 0)
						break;
					n = parseInt(sHTML.substr(s + 2, e - s));
					sHTML = sHTML.substr(0, s) + \'[n\' + (++n) + \']\' + sHTML.substr(e + 1);
					p = s + 1;
				}
				var regExp = new RegExp(\'data-id=".+?"\', \'g\'), oldId, newId = BX.util.getRandomString(6).toLowerCase();
				var match = sHTML.match(regExp);
				if(match) match = match[0].match(/"([^"]*)"/i);
				if(match) oldId = match[1];
				sHTML = sHTML.replace(new RegExp(oldId, \'g\'), newId);
				oCell.innerHTML = sHTML;
				if(BX.HandlerMoneyField) {
					var handlerMoneyField = new BX.HandlerMoneyField({
						randomString: newId,
						defaultSeparator: separator,
						listCurrency: listCurrency
					});
				}
			};
		</script>';

			$renderResult .= '<input type="button" value="'.Loc::getMessage('BPDT_BASE_ADD')
				.'" onclick="cloneTypeControlMoney(\'BizprocCloneable_'
				.$wrapperId.'\', \''.$wrapperId.'\', \''.$separator.'\', '.
				htmlspecialcharsbx(\CUtil::PhpToJSObject($listCurrency))
				.')"/><br />';

			return $renderResult;
		}

		private static function getIblockId(FieldType $fieldType)
		{
			$documentType = $fieldType->getDocumentType();
			$type = explode('_', $documentType[2]);
			return intval($type[1]);
		}

		/** @inheritdoc */
		public static function compareValues($valueA, $valueB)
		{
			if (
				mb_strpos($valueA, '|') === false
				|| mb_strpos($valueB, '|') === false
				|| !Main\Loader::includeModule('currency')
			)
			{
				return parent::compareValues($valueA, $valueB);
			}

			list($sumA, $currencyA) = explode('|', $valueA);
			list($sumB, $currencyB) = explode('|', $valueB);

			$sumA = (double) $sumA;
			$sumB = (double) $sumB;

			if (!$currencyA)
			{
				$currencyA = CurrencyManager::getBaseCurrency();
			}
			if (!$currencyB)
			{
				$currencyB = CurrencyManager::getBaseCurrency();
			}

			if ($currencyA !== $currencyB && $sumB > 0)
			{
				$sumB = self::convertMoney($sumB, $currencyB, $currencyA);
			}

			return parent::compareValues($sumA, $sumB);
		}

		private static function convertMoney($sum, $srcCurrencyId, $dstCurrencyId)
		{
			$result = \CCurrencyRates::ConvertCurrency($sum, $srcCurrencyId, $dstCurrencyId);

			$decimals = 2;
			$formatInfo = \CCurrencyLang::GetCurrencyFormat($dstCurrencyId);
			if(isset($formatInfo['DECIMALS']))
			{
				$decimals = intval($formatInfo['DECIMALS']);
			}

			$result = round($result, $decimals);
			return $result;
		}
	}
}