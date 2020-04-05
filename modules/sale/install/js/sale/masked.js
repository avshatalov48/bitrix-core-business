function BXMaskedInput(params)
{
	this.defaultDefinitions = {
		"cypher": {
			"char": "9",
			"rule": "[0-9]"
		},
		"hexrgb": {
			"char": "h",
			"rule": "[A-Fa-f0-9]"
		},
		"lang_en": {
			"char": "a",
			"rule": "[a-zA-Z]"
		}
	};

	this.init(params);
}
BXMaskedInput.prototype = {
	init: function(params)
	{
		params.definitions = params.definitions || ['cypher', 'lang_en'];
		this.initDefinitions(params.definitions);

		this.placeholder = (params.placeholder || '_').substring(0, 1);
		this.mask = params.mask || '';

		this.initInput(params);
		this.setChangeEventFiring(true);
	},
	setMask: function(mask)
	{
		if(this.mask == mask)
		{
			return;
		}

		var val = this.getValueClean();
		this.mask = mask;

		this.setChangeEventFiring(false);
		this.setValue(val);
		this.setChangeEventFiring(true);
	},
	getMask: function()
	{
		return this.mask;
	},
	setValue: function(val)
	{
		if(!this.mask)
		{
			this.input.val(val);
			this.fireChangeEvent();
			return;
		}

		var lastCaretPosition = this.input.getSelectionStart();
		this.moveCaret(0);

		this.input.val(this.mask);

		var j = 0;
		for(var i = 0; i < this.mask.length; i++)
		{
			if(!this.isMaskCharReplaceable(i))
			{
				this.replaceChar(i, this.mask.charAt(i));
				continue;
			}

			while(true)
			{
				var char = val.charAt(j);
				if(!char)
				{
					char = this.placeholder;
					break;
				}

				if(char == this.placeholder)
				{
					break;
				}

				if(this.testChar(i, char))
				{
					break;
				}

				j++;
			}

			this.replaceChar(i, char);
			j++;
		}

		this.moveCaret(lastCaretPosition);
		//this.moveCaret(this.getFirstEmptyPosition());
		this.fireChangeEvent();
	},
	getValue: function()
	{
		return this.input.val();
	},
	getFirstEmptyPosition: function()
	{
		var val = this.getValue();
		for(var i = 0; i < val.length; i++)
		{
			if(!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			var char = val.charAt(i);
			if(char == this.placeholder)
			{
				return i;
			}
		}

		return null;
	},
	getValueClean: function()
	{
		var returnValue = '';
		var val = this.getValue();
		if(!this.mask)
		{
			return val;
		}

		for(var i = 0; i < val.length; i++)
		{
			if(!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			var char = val.charAt(i);
			returnValue += char == this.placeholder ? '' : char;
		}

		return returnValue;
	},
	checkValue: function()
	{
		var val = this.getValue();
		if(!this.mask)
		{
			return val;
		}

		for(var i = 0; i < val.length; i++)
		{
			if(!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			if (val.charAt(i) == this.placeholder)
			{
				return false;
			}
		}

		return true;
	},
	onInputDelete: function(directionLeft)
	{
		if(this.deleteSelection())
		{
			this.fireChangeEvent();
			return;
		}

		var pos = this.input.getSelectionStart();
		if(directionLeft)
		{
			if(pos === this.mask.length)
			{
				this.replaceChar(pos-1, this.placeholder);
			}
			else
			{
				this.shift(pos, pos-1);
			}
			this.moveCaret(pos-1);
		}
		else
		{
			if(pos === this.mask.length - 1)
			{
				this.replaceChar(pos, this.placeholder);
			}
			else if(pos < this.mask.length - 1)
			{
				this.shift(pos+1, pos);
			}

			this.moveCaret(pos);
		}
		this.fireChangeEvent();
	},
	onInputPaste: function(pastedData)
	{
		this.deleteSelection();
		for(var i = 0; i < pastedData.length; i++)
		{
			this.setCharOnCaret(pastedData.charAt(i));
		}
		this.fireChangeEvent();
	},
	onInputChange: function(char)
	{
		if(this.input.val() == '' && this.mask)
		{
			this.input.val(this.getMaskedPlaceholder());
			this.input.setCaretPosition(0);
		}

		this.deleteSelection();
		this.setCharOnCaret(char);
		this.fireChangeEvent();
	},
	moveCaret: function(pos)
	{
		if(pos > this.mask.length)
		{
			pos = this.mask.length;
		}
		else if(pos < 0)
		{
			pos = 0;
		}
		else if(pos === null)
		{
			return this.input.getSelectionStart();
		}

		this.input.setCaretPosition(pos);
		return pos;
	},
	findClosestAllowPosition: function(pos, char, directionLeft)
	{
		if(typeof pos === 'undefined')
		{
			pos = 0;
		}
		if(typeof char === 'undefined')
		{
			char = null;
		}
		if(typeof directionLeft === 'undefined')
		{
			directionLeft = false;
		}

		while (true)
		{
			if(!directionLeft && pos >= this.mask.length)
			{
				return null;
			}
			else if(directionLeft && pos <= 0)
			{
				return 0;
			}

			if(this.isMaskCharReplaceable(pos))
			{
				break;
			}

			if(!directionLeft)
			{
				pos++;
			}
			else
			{
				pos--;
			}
		}

		if(!this.isMaskCharReplaceable(pos))
		{
			return null;
		}
		if(char && !this.testChar(pos, char))
		{
			return null;
		}

		return pos;
	},
	setCharOnCaret: function(char)
	{
		var pos = this.input.getSelectionStart();
		pos = this.findClosestAllowPosition(pos, char);
		if(pos === null)
		{
			return;
		}

		this.shift(pos, pos+1);
		pos = this.replaceChar(pos, char);

		if(pos === null)
		{
			return;
		}

		pos = this.findClosestAllowPosition(pos+1);

		this.moveCaret(pos);

		if (BX.browser.IsAndroid())
		{
			if (this.input.detectAndroidVersion && this.input.detectAndroidVersion() < 7)
			{
				var _this = this;
				setTimeout(function () {
					_this.moveCaret(pos);
				}, 50);
			}
		}

		/*
		 var pos = this.getFirstEmptyPosition();
		 if(pos)
		 {
		 this.moveCaret(pos);
		 }
		*/
	},
	shift: function(start, target)
	{
		var i, char = null;
		var buffer = [];

		for(i = start; i < this.mask.length; i++)
		{
			if (!this.isMaskCharReplaceable(i)) continue;
			var val = this.input.val();
			buffer.push(val.charAt(i));
			this.replaceChar(i, this.placeholder);
		}

		buffer.reverse();
		for(i = target; i < this.mask.length; i++)
		{
			if(!this.isMaskCharReplaceable(i)) continue;
			if(buffer.length > 0)
			{
				char = buffer.pop();
			}
			else
			{
				char = this.placeholder;
			}

			this.replaceChar(i, char);
		}
	},
	deleteSelection: function()
	{
		var posStart = this.input.getSelectionStart();
		var posEnd = this.input.getSelectionEnd();
		if(posStart == posEnd)
		{
			return false;
		}

		// delete
		for(var i = posStart; i < posEnd; i++)
		{
			if(!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			this.replaceChar(i, this.placeholder);
		}

		this.shift(posEnd, posStart);
		this.moveCaret(posStart);

		return true;
	},
	setChangeEventFiring: function(start)
	{
		this.stopChangeEvent = !start;
	},
	fireChangeEvent: function()
	{
		if(!this.stopChangeEvent)
		{
			BX.onCustomEvent(this, 'change', [this.getValueClean(), this.getValue()]);
		}
	},
	replaceChar: function(pos, char)
	{
		if(isNaN(pos))
		{
			return null;
		}

		var val = this.input.val();
		var valTml = val.substring(0, pos) + char;
		valTml += (pos >= val.length) ? '' : val.substring((pos+1));
		val = valTml;

		this.input.val(val);

		return pos;
	},
	isMaskCharReplaceable: function(pos)
	{
		var char = this.mask.charAt(pos);
		if(!char)
		{
			return false;
		}

		return !!this.definitions[char];
	},
	getMaskedPlaceholder: function()
	{
		var val = '';
		for(var i = 0; i < this.mask.length; i++)
		{
			var char = this.mask[i];
			if(this.definitions[char])
			{
				char = this.placeholder;
			}

			val += char;
		}

		return val;
	},
	initInput: function(params)
	{
		if(params.input.tagName == 'INPUT')
		{
			this.input = new BXMaskedInputElement({node: params.input});
		}
		else
		{
			this.input = new BXMaskedTextElement({node: params.input});
		}

		this.dataInput = params.dataInput || null;
		this.isDataInputClean = params.isDataInputClean || false;
		this.isHoldOverInputValueInit = params.isHoldOverInputValueInit || false;
		this.onDataInputChange = params.onDataInputChange || null;
		this.onDataInputInitValue = params.onDataInputInitValue || null;
		this.enableCheckingValue = params.enableCheckingValue || false;

		BX.addCustomEvent(this.input, 'change', BX.proxy(this.onInputChange, this));
		BX.addCustomEvent(this.input, 'paste', BX.proxy(this.onInputPaste, this));
		BX.addCustomEvent(this.input, 'delete', BX.proxy(this.onInputDelete, this));

		BX.bind(this.input.node, 'focus', BX.proxy(function(){
			if(this.getFirstEmptyPosition() === null)
			{
				return;
			}

			setTimeout(BX.proxy(function(){
				this.moveCaret(this.getFirstEmptyPosition());
			}, this), 50);
		}, this));

		if(!this.isHoldOverInputValueInit && !this.test(this.input.val()))
		{
			this.input.val(this.getMaskedPlaceholder());
		}

		if(this.dataInput)
		{
			var dataInputVal = this.dataInput.value;
			if(BX.type.isFunction(this.onDataInputInitValue))
			{
				dataInputVal = this.onDataInputInitValue.apply(this, [dataInputVal]);
			}
			if(BX.type.isString(dataInputVal) && dataInputVal.length > 0)
			{
				this.setValue(dataInputVal);
			}

			BX.addCustomEvent(this, 'change', BX.proxy(function()
			{
				var inputVal = '';
				if(BX.type.isFunction(this.onDataInputChange))
				{
					inputVal = this.onDataInputChange.apply(this, [this.getValueClean(), this.getValue()]);
				}
				else if(this.isDataInputClean)
				{
					inputVal = this.getValueClean();
				}
				else
				{
					inputVal = this.getValue();
				}

				if(!BX.type.isString(inputVal))
				{
					inputVal = '';
				}

				if (this.enableCheckingValue && !this.checkValue())
				{
					inputVal = '';
				}

				this.dataInput.value = inputVal;

			}, this));

		}
	},
	initDefinitions: function(definitions)
	{
		this.definitions = {};
		this.charTests = [];
		definitions.forEach(function(definition)
		{
			if(BX.type.isString(definition) && this.defaultDefinitions[definition])
			{
				definition = this.defaultDefinitions[definition];
			}

			if(BX.type.isPlainObject(definition))
			{
				var def = {
					"rule": definition.rule,
					"isFunction": false
				};
				if(BX.type.isFunction(definition.rule))
				{
					def.isFunction = true;
				}
				else
				{
					def.regexp = new RegExp(definition.rule);
				}

				this.definitions[definition.char] = def;
			}
		}, this);
	},

	test: function(string)
	{
		for(var i = 0; i < string.length; i++)
		{
			var r = this.testChar(i, string[i]);
			if(!r)
			{
				return false;
			}
		}

		return true;
	},

	testChar: function(pos, char)
	{
		var maskChar = this.mask[pos];

		if(!this.definitions[maskChar])
		{
			return char === maskChar;
		}

		var isSuccess = true;
		if(this.definitions[maskChar].isFunction)
		{
			isSuccess = !!this.definitions[maskChar].func.apply(this, [char]);
		}
		else
		{
			isSuccess = this.definitions[maskChar].regexp.test(char);
		}

		return isSuccess;
	}
};

function BXMaskedInputElement(params)
{
	this.node = params.node;

	BX.bind(this.node, 'paste', BX.proxy(this.onChange, this));
	BX.bind(this.node, 'keypress', BX.proxy(this.onChange, this));

	if (BX.browser.IsAndroid() && this.detectAndroidVersion() < 7)
	{
		BX.bind(this.node, 'textInput', BX.proxy(this.onAndroidInput, this));
		BX.bind(this.node, 'keydown', BX.proxy(this.onAndroidInput, this));
	}
	else
	{
		BX.bind(this.node, 'keydown', BX.proxy(this.onChange, this));
	}

}
BXMaskedInputElement.prototype = {

	skipTextInputEvent: false,
	val: function (value)
	{
		if(typeof value != 'undefined')
		{
			this.node.value = value;
		}

		return this.node.value;
	},

	detectAndroidVersion: function ()
	{
		var re = new RegExp("Android ([0-9]+[\.0-9]*)");
		if (re.exec(navigator.userAgent) != null)
			return parseFloat( RegExp.$1 );
		else
			return 0;
	},

	onAndroidInput: function (e)
	{
		var kc = 0;
		if (e.type == 'keydown')
		{
			kc = (typeof e.which == "number") ? e.which : e.keyCode;
			if (kc != 8)
			{
				if (e.key == "Unidentified") // if keydown has wrong key, use textInput with right key
				{
					this.skipTextInputEvent = false;
					BX.PreventDefault(e);
					return false;
				}
				else
				{
					this.skipTextInputEvent = true;
				}
			}
		}
		else
		{
			if (this.skipTextInputEvent)
			{
				BX.PreventDefault(e);
				return false;
			}

			kc = e.data.toUpperCase().charCodeAt(0);
		}

		var eventObject = {
			keyCode: kc,
			which: kc,
			type: 'keydown'
		};

		return this.onChange(eventObject, e);
	},

	onChange: function (e, eReal)
	{
		var isCatch = true;
		var kc = (typeof e.which == "number") ? e.which : e.keyCode;

		if(kc <= 0)
		{
			return;
		}

		switch (e.type)
		{
			case 'keydown':

				isCatch = (
					(!e.ctrlKey && !e.altKey && !e.metaKey)
					&&
					(
						kc
						&&
						(
							(kc > 46 && kc <= 90) // chars
							||
							kc > 145
							||
							kc === 13 // Carriage return
							||
							kc === 8 // [backspace] key
							||
							kc === 46 // [Del] key
							||
							(BX.browser.IsIOS() && kc === 127) // iOS [delete] key
						)
					)
				);
				break;
			case 'keypress':
				break;
		}

		if(!isCatch)
		{
			return;
		}

		BX.PreventDefault(eReal || e);
		if(e.type == 'paste')
		{
			var clipboardData = e.clipboardData || window.clipboardData;
			var pastedData = clipboardData.getData('Text');
			BX.onCustomEvent(this, 'paste', [pastedData]);
		}
		else if(kc === 8 || kc === 46 || (BX.browser.IsIOS() && kc === 127))
		{
			var directionLeft = kc === 8;
			BX.onCustomEvent(this, 'delete', [directionLeft]);
		}
		else
		{
			var char = String.fromCharCode(kc);
			BX.onCustomEvent(this, 'change', [char]);
		}
	},

	setCaretPosition: function (pos)
	{
		this.node.setSelectionRange(pos, pos);
	},

	getSelectionStart: function ()
	{
		if (this.node.selectionStart)
		{
			return this.node.selectionStart;
		}
		else if (this.node.createTextRange)
		{
			var range = this.node.createTextRange().duplicate();
			range.moveEnd('character', this.node.value.length);
			if (range.text == '')
			{
				return this.node.value.length;
			}
			else
			{
				return this.node.value.lastIndexOf(range.text);
			}
		}
		else
		{
			return 0;
		}
	},

	getSelectionEnd: function ()
	{
		if (this.node.selectionEnd)
		{
			return this.node.selectionEnd;
		}
		else if (this.node.createTextRange)
		{
			var range = this.node.createTextRange().duplicate();
			range.moveStart('character', -this.node.value.length);
			return range.text.length;
		}
		else
		{
			return 0;
		}
	}
};

function BXMaskedTextElement(params)
{
	this.node = params.node;
}
BXMaskedTextElement.prototype = {

	val: function (value)
	{
		if(typeof value != 'undefined')
		{
			this.node.innerText = value;
		}

		return this.node.innerText;
	},

	onChange: function (e)
	{
	},

	setCaretPosition: function (pos)
	{
	},

	getSelectionStart: function ()
	{
		return 0;
	},

	getSelectionEnd: function ()
	{
		return 0;
	}
};


function BXMaskedPhone(params)
{
	this.country = params.country || this.getBrowserCountry();
	this.onChangeMask = params.onChangeMask || null;
	this.onChangeFlag = params.onChangeFlag || null;
	this.url = params.url || '/upload/callback/base';

	// init flag changer
	this.flagSize = (params.flagSize && BX.util.in_array(parseInt(params.flagSize), [16, 24, 32])) ? params.flagSize : 16;
	this.flagNode = (params.flagNode && BX.type.isDomNode(params.flagNode)) ? params.flagNode : null;

	// init masked input
	if(params.maskedInput instanceof BXMaskedInput)
	{
		this.maskedInput = params.maskedInput;
	}
	else if(BX.type.isPlainObject(params.maskedInput))
	{
		params.maskedInput.onDataInputChange = params.maskedInput.onDataInputChange || this.onDataInputChange;
		params.maskedInput.isHoldOverInputValueInit = true;
		params.maskedInput.enableCheckingValue = true;
		this.maskedInput = new BXMaskedInput(params.maskedInput);
	}
	else
	{
		throw Error('Parameter "maskedInput" not found.');
	}

	this.current = null;
	this.wasInited = false;

	// listen change input event
	BX.addCustomEvent(this.maskedInput, 'change', BX.proxy(this.onChange, this));

	// init loading of country codes
	BX.addCustomEvent(window, 'bx-masked-phone-codes-loaded', BX.proxy(this.onCodesLoaded, this));
	if(this.codes === null)
	{
		BXMaskedPhone.prototype.codes = [];
		this.loadJson();
	}
	else if(this.codes.length > 0)
	{
		this.onCodesLoaded();
	}
}
BXMaskedPhone.prototype = {

	codes: null,

	loadJson: function (country)
	{
		var page = (country ? 'country_' + country : 'countries') + '.json';
		var _this = this;
		BX.ajax.load({
			'url': this.url + '/' + page,
			'type': 'json',
			'callback': function(data)
			{
				_this.prepareDataCodes(data, country);
			}
		});
	},

	findCurrent: function (val)
	{
		val = this.getCyphers(val || '');

		if(this.codes.length <= 0)
		{
			return;
		}

		var country = this.country;
		if(val.length == 0 && (!country || country.length ==0))
		{
			return;
		}

		var filtered = null;
		var filteredCountry = null;
		for (var codeIterator = 0; codeIterator < this.codes.length; codeIterator++)
		{
			filtered = null;
			filteredCountry = null;

			var item = this.codes[codeIterator];
			if (!item || !item.code || !item.data)
			{
				continue;
			}

			if (val)
			{
				if(val.substring(0, item.code.length) != item.code)
				{
					continue;
				}
			}
			else if (country)
			{
				for (var countryIterator = 0; countryIterator < item.data.length; countryIterator++)
				{
					if (country == item.data[countryIterator].id)
					{
						filteredCountry = item.data[countryIterator];
					}
				}

				if(!filteredCountry)
				{
					continue;
				}
			}

			filtered = item;
			if (!filteredCountry)
			{
				filteredCountry = item.data[0];
			}
			break;
		}


		this.current = null;
		if(!filtered && !filteredCountry)
		{
			return;
		}

		this.current = filteredCountry;
		this.current.code = filtered.code;

		if(this.current.codes === true)
		{
			this.current.codes = [];
			this.loadJson(this.current.id);
		}
	},

	findMaskByValue: function (val)
	{
		val = this.getCyphers(val || '');

		var mask = this.current.mask;
		if(BX.type.isArray(this.current.codes))
		{
			filtered = null;
			for(var i = 0; i < this.current.codes.length; i++)
			{
				var item = this.current.codes[i];
				if(!item.code)
				{
					continue;
				}

				if(val.substring(0, item.code.length) != item.code)
				{
					continue;
				}

				if(!filtered || (item.code.length > filtered.code.length))
				{
					filtered = item;
				}
			}

			if(filtered)
			{
				mask = filtered.mask;
			}
		}

		return mask;
	},

	getCyphers: function (val)
	{
		var result = '';
		for(var i = 0; i < val.length; i++)
		{
			var char = val.charAt(i);
			if(!char)
			{
				break;
			}

			if(isNaN(char))
			{
				continue;
			}

			result += char;
		}

		return result;
	},

	changeMask: function ()
	{
		var val = this.maskedInput.getValueClean();
		this.findCurrent(val);

		if(!this.current)
		{
			if(!this.wasInited)
			{
				this.maskedInput.setValue('');
			}
			else
			{
				this.changeFlag();
			}
			return;
		}

		var mask = this.findMaskByValue(val);
		mask = mask.replace(new RegExp('_','g'), '9');
		this.maskedInput.setMask(mask);

		if(!this.wasInited)
		{
			if(!val)
			{
				this.maskedInput.setChangeEventFiring(false);
				this.maskedInput.setValue(this.current.code);
				this.maskedInput.setChangeEventFiring(true);
			}

			this.maskedInput.moveCaret(this.maskedInput.getFirstEmptyPosition());
			this.wasInited = true;
		}

		if(this.onChangeMask)
		{
			this.onChangeMask.apply(this, [mask]);
		}

		this.changeFlag();
	},

	onCodesLoaded: function ()
	{
		this.changeMask();
	},

	prepareDataCodes: function (data, country)
	{
		if(!data)
		{
			return;
		}

		if(!country && this.codes.length == 0)
		{
			BXMaskedPhone.prototype.codes = data;
			BX.onCustomEvent(window, 'bx-masked-phone-codes-loaded');
		}
		else
		{
			if(this.current.id === country)
			{
				this.current.codes = data;
			}

			this.onCodesLoaded();
		}
	},

	onDataInputChange: function (valueClean, value)
	{
		return '+' + valueClean;
	},

	onChange: function (valueClean, value)
	{
		this.changeMask();
	},

	changeFlag: function ()
	{
		var countryId = this.current ? this.current.id : '';
		if(this.onChangeFlag)
		{
			this.onChangeFlag.apply(this, [countryId, this.flagSize]);
		}

		if(!this.flagNode)
		{
			return;
		}

		var classList = this.flagNode.className.split(' ');
		if(classList)
		{
			classList.forEach(function(classNameItem){
				if(classNameItem.length == 2)
				{
					BX.removeClass(this.flagNode, classNameItem);
				}
			}, this);
		}

		BX.addClass(this.flagNode, 'bx-flag-' + this.flagSize);
		BX.addClass(this.flagNode, countryId);
	},

	getBrowserCountry: function ()
	{
		var lang = window.navigator.languages ? window.navigator.languages[0] : null;
		lang = lang || window.navigator.language || window.navigator.browserLanguage || window.navigator.userLanguage;
		if (lang.indexOf('-') !== -1)
		{
			lang = lang.split('-')[1];
		}

		if (!lang && lang.indexOf('_') !== -1)
		{
			lang = lang.split('_')[0];
		}

		return (lang ? lang.toLowerCase() : 'us');
	}
};