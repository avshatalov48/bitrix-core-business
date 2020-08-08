<?
namespace Bitrix\Currency\Integration;

use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Type\RandomSequence;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class IblockMoneyProperty
{
	const USER_TYPE = 'Money';
	const SEPARATOR = '|';

	/**
	 * Returns property type description.
	 *
	 * @return array
	 */
	public static function getUserTypeDescription()
	{
		$className = get_called_class();
		return array(
			'PROPERTY_TYPE' => 'S',
			'USER_TYPE' => self::USER_TYPE,
			'DESCRIPTION' => Loc::getMessage('CIMP_INPUT_DESCRIPTION'),
			'GetPublicEditHTML' => array($className, 'getPublicEditHTML'),
			'GetPublicViewHTML' => array($className, 'getPublicViewHTML'),
			'GetPropertyFieldHtml' => array($className, 'getPropertyFieldHtml'),
			'GetAdminListViewHTML' => array($className, 'getAdminListViewHTML'),
			'GetUIEntityEditorProperty' => array($className, 'GetUIEntityEditorProperty'),
			'getFormattedValue' => array($className, 'getSeparatedValues'),
			'CheckFields' => array($className, 'checkFields'),
			'GetLength' => array($className, 'getLength'),
			'ConvertToDB' => array($className, 'convertToDB'),
			'ConvertFromDB' => array($className, 'convertFromDB'),
			'AddFilterFields' => array($className, 'addFilterFields'),
		);
	}

	/**
	 * Return html for public edit value.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @param array $controlSettings Form data.
	 * @return string
	 */
	public static function getPublicEditHTML($property, $value, $controlSettings)
	{
		return  self::getPropertyFieldHtml($property, $value, $controlSettings);
	}

	/**
	 * Return html for public view value.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @param array $controlSettings Form data.
	 * @return string
	 */
	public static function getPublicViewHTML($property, $value, $controlSettings)
	{
		return self::getAdminListViewHTML($property, $value, $controlSettings);
	}

	/**
	 * The method should return the html display for editing property values in the administrative part.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @param array $controlSettings Form data.
	 * @return string
	 */
	public static function getPropertyFieldHtml($property, $value, $controlSettings)
	{
		$seed = (!empty($controlSettings['VALUE'])) ? $controlSettings['VALUE'] : 'IMPSeed';
		$randomGenerator = new RandomSequence($seed);
		$randString = mb_strtolower($randomGenerator->randString(6));

		$explode = (is_string($value['VALUE']) ? explode(self::SEPARATOR, $value['VALUE']) : []);
		$currentValue = ($explode[0] <> ''? $explode[0] : '');
		$currentCurrency = ($explode[1] ? $explode[1] : '');

		$html = '<input type="text" style="width: auto;" value="'.htmlspecialcharsbx($currentValue).
			'" id="input-'.$randString.'">';
		$html .= '<input type="hidden" id="hidden-'.$randString.'" name="'.
			htmlspecialcharsbx($controlSettings['VALUE']).'" value="'.htmlspecialcharsbx($value["VALUE"]).'">';
		$listCurrency = self::getListCurrency();
		if($listCurrency)
		{
			if($property['MULTIPLE'] == 'Y')
				$html .= '<input type="hidden" data-id="'.$randString.'">';
			$html .= '<select id="selector-'.$randString.'" style="width: auto; margin: 0 5px;">';
			foreach($listCurrency as $currency)
			{
				$selected = ($currentCurrency == $currency['CURRENCY'] ||
					(!$currentCurrency && $currency['BASE'] == 'Y')) ? 'selected' : '';
				$html .= '<option '.$selected.' value="'.$currency['CURRENCY'].'">'.
					htmlspecialcharsbx($currency['NAME']).'</option>';
			}
			$html .= '</select>';
			$html .= '<span id="error-'.$randString.'" style="color: red;"></span>';
			$html .= self::getJsHandlerSelector($randString, $listCurrency);
		}

		return  $html;
	}

	/**
	 * The method must return safe HTML display the value of the properties on the list of items the administrative part.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @param array $controlSettings Form data.
	 * @return mixed|string
	 */
	public static function getAdminListViewHTML($property, $value, $controlSettings)
	{
		$explode = is_string($value['VALUE']) ? explode(self::SEPARATOR, $value['VALUE']) : array();
		$currentValue = ($explode[0] <> ''? $explode[0] : '');
		$currentCurrency = $explode[1] ? $explode[1] : '';

		if (!$currentCurrency)
			return intval($currentValue) ? $currentValue : '';

		if (CurrencyManager::isCurrencyExist($currentCurrency))
		{
			if(!empty($controlSettings['MODE']))
			{
				switch($controlSettings['MODE'])
				{
					case 'CSV_EXPORT':
						return $value['VALUE'];
					case 'SIMPLE_TEXT':
						return $currentValue;
					case 'ELEMENT_TEMPLATE':
						return $currentValue;
				}
			}

			list($currentValue, $currentCurrency, $decimalsValue) = array_values(self::getSeparatedValues($value['VALUE']));
			$currentValue = $currentValue.'.'.$decimalsValue;

			return \CCurrencyLang::CurrencyFormat($currentValue, $currentCurrency, true);
		}

		return  '';
	}

	/**
	 * Check fields before inserting into the database.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @return array An empty array, if no errors.
	 */
	public static function checkFields($property, $value)
	{
		$result = [];
		if(empty($value['VALUE'])) return $result;
		$explode = (is_string($value['VALUE']) ? explode(self::SEPARATOR, $value['VALUE']) : []);
		$currentValue = ($explode[0] <> ''? $explode[0] : '');
		$currentCurrency = ($explode[1] ? $explode[1] : '');

		if(!$currentCurrency)
			return intval($currentValue) ? $result : array(Loc::getMessage('CIMP_FORMAT_ERROR'));

		if(CurrencyManager::isCurrencyExist($currentCurrency))
		{
			$format = \CCurrencyLang::GetFormatDescription($currentCurrency);
			$decPoint = $format['DEC_POINT'];
			$thousandsSep = $format['THOUSANDS_SEP'];
			$decimals = $format['DECIMALS'];
			$regExp = '/^\d{1,3}(('.$thousandsSep.'){0,1}\d{3})*(\\'.$decPoint.'\d{1,'.$decimals.'})?$/';
			if($thousandsSep && $decPoint)
			{
				if ($decimals > 0)
				{
					$regExp = '/^\d{1,3}(('.$thousandsSep.'){0,1}\d{3})*(\\'.$decPoint.'\d{1,'.$decimals.'})?$/';
				}
				else
				{
					$regExp = '/^\d{1,3}(('.$thousandsSep.'){0,1}\d{3})*$/';
				}
			}
			elseif($thousandsSep && !$decPoint)
			{
				$regExp = '/^\d{1,3}(('.$thousandsSep.'){0,1}\d{3})*$/';
			}
			elseif(!$thousandsSep && $decPoint)
			{
				if ($decimals > 0)
				{
					$regExp = '/^[0-9]*(\\'.$decPoint.'\d{1,'.$decimals.'})?$/';
				}
				else
				{
					$regExp = '/^[0-9]?$/';
				}
			}
			elseif(!$thousandsSep && !$decPoint)
			{
				$regExp = '/^[0-9]*$/';
			}
			if(!preg_match($regExp, $currentValue))
			{
				$result[] = Loc::getMessage('CIMP_FORMAT_ERROR');
			}
		}
		else
		{
			$result[] = Loc::getMessage('CIMP_FORMAT_ERROR');
		}

		return $result;
	}

	/**
	 * Get the length of the value. Checks completion of mandatory.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @return int
	 */
	public static function getLength($property, $value)
	{
		return mb_strlen(trim($value['VALUE'], "\n\r\t"));
	}

	/**
	 * The method is to convert the value of a format suitable for storage in a database.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @return mixed
	 */
	public static function convertToDB($property, $value)
	{
		return $value;
	}

	/**
	 * The method is to convert the property value in the processing format.
	 *
	 * @param array $property Property data.
	 * @param array $value Current value.
	 * @return mixed
	 */
	public static function convertFromDB($property, $value)
	{
		return $value;
	}

	public static function getSeparatedValues($value)
	{
		$explode = is_string($value) ? explode(self::SEPARATOR, $value) : array();
		$currentValue = ($explode[0] <> ''? $explode[0] : '');
		$currentCurrency = $explode[1] ? $explode[1] : '';
		$format = \CCurrencyLang::GetFormatDescription($currentCurrency);
		$explode = explode($format['DEC_POINT'], $currentValue);
		$currentValue = ($explode[0] <> ''? $explode[0] : '');
		$decimalsValue = $explode[1] ? $explode[1] : '';
		return array(
			'AMOUNT' => $currentValue,
			'CURRENCY' => $currentCurrency,
			'DECIMALS' => $decimalsValue
		);
	}

	/**
	 * Add values in filter.
	 *
	 * @param array $property Property data.
	 * @param array $controlSettings Form data.
	 * @param array &$filter Filter data.
	 * @param bool &$filtered Marker filter.
	 * @return void
	 */
	public static function addFilterFields($property, $controlSettings, &$filter, &$filtered)
	{
		$filtered = false;

		if(isset($_REQUEST[$controlSettings['VALUE']]))
		{
			$value = $_REQUEST[$controlSettings['VALUE']];
		}
		elseif(isset($controlSettings["FILTER_ID"]))
		{
			$filterOption = new \Bitrix\Main\UI\Filter\Options($controlSettings["FILTER_ID"]);
			$filterData = $filterOption->getFilter();
			if(!empty($filterData[$controlSettings['VALUE']]))
				$value = $filterData[$controlSettings['VALUE']];
		}

		if(!empty($value))
		{
			$explode = explode(self::SEPARATOR, $value);
			if(empty($explode[1]))
			{
				$listCurrency = self::getListCurrency();
				if($listCurrency)
				{
					$filter[$controlSettings['VALUE']] = array();
					foreach($listCurrency as $currencyType => $currency)
					{
						$filter[$controlSettings['VALUE']][] = $value.self::SEPARATOR.$currencyType;
					}
				}
			}
			$filtered = true;
		}
	}

	protected static function getListCurrency()
	{
		$result = Editor::getListCurrency();
		return (empty($result) ? array() : $result);
	}

	protected static function getJsHandlerSelector($randString, array $listCurrency)
	{
		ob_start();
		?>
		<script>
			if (!window.BX && top.BX)
				window.BX = top.BX;
			BX.ready(function() {
			'use strict';
			if(!BX.HandlerMoneyField) {
				BX.HandlerMoneyField = function(params) {
					this.randomString = params.randomString;
					this.listCurrency = params.listCurrency;
					this.defaultSeparator = params.defaultSeparator;
					setTimeout(BX.proxy(this.setDefaultParams, this), 300);
				};
				BX.HandlerMoneyField.prototype.setDefaultParams = function() {
					this.input = BX('input-' + this.randomString);
					this.hidden = BX('hidden-' + this.randomString);
					this.selector = BX('selector-' + this.randomString);
					this.error = BX('error-' + this.randomString);
					this.init();
				};
				BX.HandlerMoneyField.prototype.init = function() {
					if (!this.input || !this.selector || !this.hidden) return;
					this.currentCurrency = this.selector.value;
					this.currentFormat = null;
					this.oldFormat = {
						'currentCurrency': this.currentCurrency,
						'decPoint': this.listCurrency[this.currentCurrency].DEC_POINT,
						'thousandsSep': this.listCurrency[this.currentCurrency].SEPARATOR
					};
					this.availableKey = [8,9,13,36,37,39,38,40,46,18,17,16,188,190,86,65,112,113,114,115,116,117,118,
						119,120,121,122,123,67,45,34,33,35];
					this.availableSymbol = [];
					this.changeCurrency();
					BX.bind(this.selector, 'change', BX.proxy(this.changeCurrency, this));
					BX.bind(this.input, 'keydown', BX.proxy(function(event) {this.onkeydown(event)}, this));
					BX.bind(this.input, 'keyup', BX.proxy(this.onkeyup, this));
					BX.bind(this.input, 'blur', BX.proxy(this.onblur, this));
					BX.bind(this.input, 'focus', BX.proxy(this.onfocus, this));
					BX.addCustomEvent(window, 'onAddNewRowBeforeInner', BX.proxy(function(htmlObject) {
						var regExp = new RegExp('data-id=".+?"', 'g'), oldId;
						var match = htmlObject.html.match(regExp);
						if(match) match = match[0].match(/"([^"]*)"/i);
						if(match) oldId = match[1];
						if(this.randomString == oldId)
						{
							var newId = BX.util.getRandomString(6).toLowerCase();
							htmlObject.html = htmlObject.html.replace(new RegExp(oldId, 'g'), newId);
						}
					}, this));
				};
				BX.HandlerMoneyField.prototype.changeCurrency = function() {
					this.currentCurrency = (BX.proxy_context && BX.proxy_context.value) ?
						BX.proxy_context.value : this.selector.value;
					this.currentFormat = this.listCurrency[this.currentCurrency].FORMAT;
					this.decPoint = this.listCurrency[this.currentCurrency].DEC_POINT;
					this.thousandsSep = this.listCurrency[this.currentCurrency].SEPARATOR;
					this.decimals = this.listCurrency[this.currentCurrency].DECIMALS;
					if(this.realValue)
					{
						var regExp = this.oldFormat.thousandsSep ? '\\'+this.oldFormat.thousandsSep : '';
						this.realValue = this.realValue.replace(new RegExp(regExp, 'g'),'');
						this.input.value = this.oldFormat.decPoint ?
							this.realValue.replace(this.oldFormat.decPoint,this.decPoint) : this.realValue;
					}
					this.oldFormat = {
						'currentCurrency': this.currentCurrency,
						'decPoint': this.decPoint,
						'thousandsSep': this.thousandsSep
					};
					this.setRealValue();
					this.setVisibleValue(true);
					this.setHiddenValue();
					var decimals = '';
					for(var i = 1; i <= this.decimals; i++)
						decimals += i;
					this.availableSymbol.push(this.thousandsSep);
					this.availableSymbol.push(this.decPoint);
					this.regExp = '';
					this.isDecimalsNull = (!parseInt(this.decimals));
					if(this.thousandsSep && this.decPoint)
					{
						if (this.isDecimalsNull)
						{
							this.regExp = '^\\d{1,3}('+this.thousandsSep+'?\\d{3})*$';
							this.exampleValue = '6'+this.thousandsSep+'456';
						}
						else
						{
							this.regExp = '^\\d{1,3}('+this.thousandsSep+
								'?\\d{3})*(\\'+this.decPoint+'\\d{1,'+this.decimals+'})?$';
							this.exampleValue = '6'+this.thousandsSep+'456'+this.decPoint+decimals;
						}
					}
					else if(this.thousandsSep && !this.decPoint)
					{
						this.regExp = '^\\d{1,3}('+this.thousandsSep+'?\\d{3})*$';
						this.exampleValue = '6'+this.thousandsSep+'456';
					}
					else if(!this.thousandsSep && this.decPoint)
					{
						if (this.isDecimalsNull)
						{
							this.regExp = '^[0-9]?$';
							this.exampleValue = '6456';
						}
						else
						{
							this.regExp = '^[0-9]*(\\'+this.decPoint+'\\d{1,'+this.decimals+'})?$';
							this.exampleValue = '6456'+this.decPoint+decimals;
						}
					}
					else if(!this.thousandsSep && !this.decPoint)
					{
						this.regExp = '^[0-9]*$';
						this.exampleValue = '6456';
					}
					this.checkFormatValue();
				};
				BX.HandlerMoneyField.prototype.onkeydown = function(event) {
					this.setTextError();
					if (!BX.util.in_array(event.key, this.availableSymbol)
						&& !BX.util.in_array(event.keyCode,this.availableKey)) {
						if (isNaN(parseInt(event.key))) {
							this.setTextError(BX.message('CIMP_INPUT_FORMAT_ERROR')
								.replace('#example#', this.exampleValue));
							return BX.PreventDefault(event);
						}
					}
				};
				BX.HandlerMoneyField.prototype.onkeyup = function() {
					this.setRealValue();
					this.setHiddenValue();
					this.setVisibleValue(true);
				};
				BX.HandlerMoneyField.prototype.onblur = function() {
					this.setRealValue();
					this.setHiddenValue();
					this.setVisibleValue(true);
					this.setTextError();
					this.checkFormatValue();
				};
				BX.HandlerMoneyField.prototype.onfocus = function() {
					this.input.value = this.realValue;
				};
				BX.HandlerMoneyField.prototype.getTemplateValue = function() {
					return this.currentFormat.replace(new RegExp('(^|[^&])#'), '$1' + this.realValue);
				};
				BX.HandlerMoneyField.prototype.checkFormatValue = function() {
					if(!this.realValue) return;
					var regExp = new RegExp(this.regExp);
					if(!this.realValue.match(regExp))
					{
						this.setTextError(BX.message('CIMP_INPUT_FORMAT_ERROR')
							.replace('#example#', this.exampleValue));
					}
					else
					{
						this.setTextError();
					}
				};
				BX.HandlerMoneyField.prototype.setTextError = function(text) {
					this.error.innerHTML = text ? text : '';
				};
				BX.HandlerMoneyField.prototype.setRealValue = function() {
					this.realValue = this.getFormatValue();
				};
				BX.HandlerMoneyField.prototype.setVisibleValue = function(useTemplate) {
					this.input.value = this.input.value ? !useTemplate ?
							this.getTemplateValue() : this.getFormatValue(): '';
				};
				BX.HandlerMoneyField.prototype.setHiddenValue = function() {
					if (this.input.value)
					{
						var regExp = this.thousandsSep ? '\\'+this.thousandsSep : '';
						this.hidden.value = this.realValue.replace(new RegExp(regExp, 'g'), '') +
							this.defaultSeparator + this.currentCurrency;
					}
					else
					{
						this.hidden.value = '';
					}
				};
				BX.HandlerMoneyField.prototype.getFormatValue = function() {
					var baseValue = this.input.value;
					var valueLength = baseValue.length;
					var formatValue = "";
					var regExp;
					if(this.thousandsSep == ',' || this.thousandsSep == '.')
						regExp = new RegExp('['+this.decPoint+']');
					else
						regExp = new RegExp('['+this.decPoint+',.]');
					var decPointPosition = baseValue.match(regExp);
					decPointPosition = decPointPosition == null ? baseValue.length : decPointPosition.index;
					var countDigit = 0;
					for(var i = 0; i < baseValue.length; i++)
					{
						var symbolPosition = baseValue.length-1-i;
						var symbol = baseValue.charAt(symbolPosition);
						var isDigit = ('0123456789'.indexOf(symbol) >= 0);
						if(isDigit) countDigit++;
						if(symbolPosition == decPointPosition) countDigit = 0;
						if(symbolPosition >= decPointPosition)
						{
							if(this.decPoint == '.' && symbol == ',')
								symbol = this.decPoint;
							if(this.decPoint == ',' && symbol == '.')
								symbol = this.decPoint;
							if(isDigit || (symbolPosition == decPointPosition && symbol == this.decPoint))
								formatValue = symbol + formatValue;
							else
								if(valueLength > symbolPosition) valueLength--;
						}
						if(symbolPosition < decPointPosition)
						{
							if(isDigit)
								formatValue = symbol+formatValue;
							else
							if(valueLength > symbolPosition) valueLength--;
							if(isDigit && countDigit % 3 == 0 && countDigit !== 0 && symbolPosition !== 0)
							{
								formatValue = this.thousandsSep + formatValue;
								if(valueLength >= symbolPosition) valueLength++;
							}
						}
					}
					if(this.decimals > 0)
					{
						decPointPosition = formatValue.match(new RegExp('['+this.decPoint+']'));
						decPointPosition = decPointPosition == null ? formatValue.length : decPointPosition.index;
						while(formatValue.length-1-decPointPosition > this.decimals)
						{
							if(valueLength >= formatValue.length-1) valueLength--;
							formatValue = formatValue.substr(0, formatValue.length - 1);
						}
					}

					return formatValue;
				};
			}
			var handlerMoneyField = new BX.HandlerMoneyField({
				randomString: '<?=$randString?>',
				defaultSeparator: '<?=self::SEPARATOR?>',
				listCurrency: <?=Json::encode($listCurrency)?>
			});
			BX.message({
				CIMP_INPUT_FORMAT_ERROR: '<?=GetMessageJS('CIMP_INPUT_FORMAT_ERROR')?>'
			});
			});
		</script>
		<?
		$script = ob_get_contents();
		ob_end_clean();
		return  $script;
	}

	public static function GetUIEntityEditorProperty($settings, $value)
	{
		return [
			'type' => 'money',
		];
	}
}