;(function()
{
	if (BX.MaskedInput)
		return;

	var defaultDefinitions = {
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

	/**
	 * @param {object} params
	 * @constructor
	 */
	BX.MaskedInput = function (params)
	{
		this.mask = params.mask || '';
		this.placeholder = (params.placeholder || '_').substring(0, 1);
		this.definitions = this.prepareDefinitions(params.definitions || ['cypher', 'lang_en']);
		this.stopChangeEvent = false;

		this.initInput(params);
	};

	BX.MaskedInput.prototype.initInput = function (params)
	{
		if (params.input.tagName == 'INPUT')
		{
			this.input = new BX.MaskedInputElement({node: params.input});
		}
		else
		{
			this.input = new BX.MaskedTextElement({node: params.input});
		}

		this.dataInput = params.dataInput || null;
		this.isDataInputClean = params.isDataInputClean || false;
		this.isHoldOverInputValueInit = params.isHoldOverInputValueInit || false;
		this.enableCheckingValue = params.enableCheckingValue || false;
		this.onDataInputChange = params.onDataInputChange || null;
		this.onDataInputInitValue = params.onDataInputInitValue || null;

		BX.addCustomEvent(this.input, 'change', BX.proxy(this.onInputChange, this));
		BX.addCustomEvent(this.input, 'paste', BX.proxy(this.onInputPaste, this));
		BX.addCustomEvent(this.input, 'delete', BX.proxy(this.onInputDelete, this));

		BX.bind(this.input.node, 'focus', BX.proxy(function ()
		{
			if (this.getFirstEmptyPosition() === null)
			{
				return;
			}

			setTimeout(BX.proxy(function ()
			{
				this.moveCaret(this.getFirstEmptyPosition());
			}, this), 50);
		}, this));

		if (!this.isHoldOverInputValueInit && !this.test(this.input.val()))
		{
			this.input.val(this.getMaskedPlaceholder());
		}

		if (this.dataInput)
		{
			var dataInputVal = this.dataInput.value;
			if (BX.type.isFunction(this.onDataInputInitValue))
			{
				dataInputVal = this.onDataInputInitValue.apply(this, [dataInputVal]);
			}
			if (BX.type.isString(dataInputVal) && dataInputVal.length > 0)
			{
				this.setValue(dataInputVal);
			}

			BX.addCustomEvent(this, 'change', BX.proxy(function ()
			{
				var inputVal = '';
				if (BX.type.isFunction(this.onDataInputChange))
				{
					inputVal = this.onDataInputChange.apply(this, [this.getValueClean(), this.getValue()]);
				}
				else if (this.isDataInputClean)
				{
					inputVal = this.getValueClean();
				}
				else
				{
					inputVal = this.getValue();
				}

				if (!BX.type.isString(inputVal))
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
	};

	BX.MaskedInput.prototype.setMask = function (mask)
	{
		if (this.mask == mask)
		{
			return;
		}

		var val = this.getValueClean();
		this.mask = mask;

		this.setChangeEventFiring(false);
		this.setValue(val);
		this.setChangeEventFiring(true);
	};

	BX.MaskedInput.prototype.getMask = function ()
	{
		return this.mask;
	};

	BX.MaskedInput.prototype.setValue = function (val)
	{
		if (!this.mask)
		{
			this.input.val(val);
			this.fireChangeEvent();
			return;
		}

		var lastCaretPosition = this.input.getSelectionStart();
		this.moveCaret(0);

		this.input.val(this.mask);

		var j = 0;
		for (var i = 0; i < this.mask.length; i++)
		{
			if (!this.isMaskCharReplaceable(i))
			{
				this.replaceChar(i, this.mask.charAt(i));
				continue;
			}

			while (true)
			{
				var char = val.charAt(j);
				if (!char)
				{
					char = this.placeholder;
					break;
				}

				if (char == this.placeholder)
				{
					break;
				}

				if (this.testChar(i, char))
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
	};

	BX.MaskedInput.prototype.getValue = function ()
	{
		return this.input.val();
	};

	BX.MaskedInput.prototype.getFirstEmptyPosition = function ()
	{
		var val = this.getValue();
		for (var i = 0; i < val.length; i++)
		{
			if (!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			var char = val.charAt(i);
			if (char == this.placeholder)
			{
				return i;
			}
		}

		return null;
	};

	BX.MaskedInput.prototype.getValueClean = function ()
	{
		var returnValue = '';
		var val = this.getValue();
		if (!this.mask)
		{
			return val;
		}

		for (var i = 0; i < val.length; i++)
		{
			if (!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			var char = val.charAt(i);
			returnValue += char == this.placeholder ? '' : char;
		}

		return returnValue;
	};

	BX.MaskedInput.prototype.checkValue = function ()
	{
		var val = this.getValue();
		if (!this.mask)
		{
			return val;
		}

		for (var i = 0; i < val.length; i++)
		{
			if (!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			if (val.charAt(i) == this.placeholder)
			{
				return false;
			}
		}

		return true;
	};

	BX.MaskedInput.prototype.onInputDelete = function (directionLeft)
	{
		if (this.deleteSelection())
		{
			this.fireChangeEvent();
			return;
		}

		var pos = this.input.getSelectionStart();
		if (directionLeft)
		{
			if (pos === this.mask.length)
			{
				this.replaceChar(pos - 1, this.placeholder);
			}
			else
			{
				this.shift(pos, pos - 1);
			}
			this.moveCaret(pos - 1);
		}
		else
		{
			if (pos === this.mask.length - 1)
			{
				this.replaceChar(pos, this.placeholder);
			}
			else if (pos < this.mask.length - 1)
			{
				this.shift(pos + 1, pos);
			}

			this.moveCaret(pos);
		}
		this.fireChangeEvent();
	};

	BX.MaskedInput.prototype.onInputPaste = function (pastedData)
	{
		this.deleteSelection();
		for (var i = 0; i < pastedData.length; i++)
		{
			this.setCharOnCaret(pastedData.charAt(i));
		}
		this.fireChangeEvent();
	};

	BX.MaskedInput.prototype.onInputChange = function (char)
	{
		if (this.input.val() == '' && this.mask)
		{
			this.input.val(this.getMaskedPlaceholder());
			this.input.setCaretPosition(0);
		}

		this.deleteSelection();
		this.setCharOnCaret(char);
		this.fireChangeEvent();
	};

	BX.MaskedInput.prototype.moveCaret = function (pos)
	{
		if (pos > this.mask.length)
		{
			pos = this.mask.length;
		}
		else if (pos < 0)
		{
			pos = 0;
		}
		else if (pos === null)
		{
			return this.input.getSelectionStart();
		}

		this.input.setCaretPosition(pos);
		return pos;
	};

	BX.MaskedInput.prototype.findClosestAllowPosition = function (pos, char, directionLeft)
	{
		if (typeof pos === 'undefined')
		{
			pos = 0;
		}
		if (typeof char === 'undefined')
		{
			char = null;
		}
		if (typeof directionLeft === 'undefined')
		{
			directionLeft = false;
		}

		while (true)
		{
			if (!directionLeft && pos >= this.mask.length)
			{
				return null;
			}
			else if (directionLeft && pos <= 0)
			{
				return 0;
			}

			if (this.isMaskCharReplaceable(pos))
			{
				break;
			}

			if (!directionLeft)
			{
				pos++;
			}
			else
			{
				pos--;
			}
		}

		if (!this.isMaskCharReplaceable(pos))
		{
			return null;
		}
		if (char && !this.testChar(pos, char))
		{
			return null;
		}

		return pos;
	};

	BX.MaskedInput.prototype.setCharOnCaret = function (char)
	{
		var pos = this.input.getSelectionStart();
		pos = this.findClosestAllowPosition(pos, char);
		if (pos === null)
		{
			return;
		}

		this.shift(pos, pos + 1);
		pos = this.replaceChar(pos, char);

		if (pos === null)
		{
			return;
		}

		pos = this.findClosestAllowPosition(pos + 1);

		this.moveCaret(pos);

		if (BX.browser.IsAndroid() && BX.browser.DetectAndroidVersion() < 7)
		{
			var _this = this;
			setTimeout(function ()
			{
				_this.moveCaret(pos);
			}, 50);
		}

		/*
		 var pos = this.getFirstEmptyPosition();
		 if(pos)
		 {
		 this.moveCaret(pos);
		 }
		 */
	};

	BX.MaskedInput.prototype.shift = function (start, target)
	{
		var i, char = null;
		var buffer = [];

		for (i = start; i < this.mask.length; i++)
		{
			if (!this.isMaskCharReplaceable(i)) continue;
			var val = this.input.val();
			buffer.push(val.charAt(i));
			this.replaceChar(i, this.placeholder);
		}

		buffer.reverse();
		for (i = target; i < this.mask.length; i++)
		{
			if (!this.isMaskCharReplaceable(i)) continue;
			if (buffer.length > 0)
			{
				char = buffer.pop();
			}
			else
			{
				char = this.placeholder;
			}

			this.replaceChar(i, char);
		}
	};

	BX.MaskedInput.prototype.deleteSelection = function ()
	{
		var posStart = this.input.getSelectionStart();
		var posEnd = this.input.getSelectionEnd();
		if (posStart == posEnd)
		{
			return false;
		}

		// delete
		for (var i = posStart; i < posEnd; i++)
		{
			if (!this.isMaskCharReplaceable(i))
			{
				continue;
			}

			this.replaceChar(i, this.placeholder);
		}

		this.shift(posEnd, posStart);
		this.moveCaret(posStart);

		return true;
	};

	BX.MaskedInput.prototype.setChangeEventFiring = function (start)
	{
		this.stopChangeEvent = !start;
	};

	BX.MaskedInput.prototype.fireChangeEvent = function ()
	{
		if (!this.stopChangeEvent)
		{
			BX.onCustomEvent(this, 'change', [this.getValueClean(), this.getValue()]);
		}
	};

	BX.MaskedInput.prototype.replaceChar = function (pos, char)
	{
		if (isNaN(pos))
		{
			return null;
		}

		var val = this.input.val();
		var valTml = val.substring(0, pos) + char;
		valTml += (pos >= val.length) ? '' : val.substring((pos + 1));
		val = valTml;

		this.input.val(val);

		return pos;
	};
	BX.MaskedInput.prototype.isMaskCharReplaceable = function (pos)
	{
		var char = this.mask.charAt(pos);
		if (!char)
		{
			return false;
		}

		return !!this.definitions[char];
	};

	BX.MaskedInput.prototype.getMaskedPlaceholder = function ()
	{
		var val = '';
		for (var i = 0; i < this.mask.length; i++)
		{
			var char = this.mask[i];
			if (this.definitions[char])
			{
				char = this.placeholder;
			}

			val += char;
		}

		return val;
	};

	BX.MaskedInput.prototype.prepareDefinitions = function (definitions)
	{
		var result = {};

		definitions.forEach(function (definition)
		{
			if (BX.type.isString(definition) && defaultDefinitions[definition])
			{
				definition = defaultDefinitions[definition];
			}

			if (BX.type.isPlainObject(definition))
			{
				var def = {
					"rule": definition.rule,
					"isFunction": false
				};
				if (BX.type.isFunction(definition.rule))
				{
					def.isFunction = true;
				}
				else
				{
					def.regexp = new RegExp(definition.rule);
				}

				result[definition.char] = def;
			}
		}, this);
		return result;
	};

	BX.MaskedInput.prototype.test = function (string)
	{
		for (var i = 0; i < string.length; i++)
		{
			var r = this.testChar(i, string[i]);
			if (!r)
			{
				return false;
			}
		}

		return true;
	};

	BX.MaskedInput.prototype.testChar = function (pos, char)
	{
		var maskChar = this.mask[pos];

		if (!this.definitions[maskChar])
		{
			return char === maskChar;
		}

		var isSuccess = true;
		if (this.definitions[maskChar].isFunction)
		{
			isSuccess = !!this.definitions[maskChar].func.apply(this, [char]);
		}
		else
		{
			isSuccess = this.definitions[maskChar].regexp.test(char);
		}

		return isSuccess;
	};

	BX.MaskedInputElement = function (params)
	{
		this.node = params.node;
		this.skipTextInputEvent = false;

		BX.bind(this.node, 'paste', BX.proxy(this.onChange, this));
		BX.bind(this.node, 'keypress', BX.proxy(this.onChange, this));

		if (BX.browser.IsAndroid() && BX.browser.DetectAndroidVersion() < 7)
		{
			BX.bind(this.node, 'textInput', BX.proxy(this.onAndroidInput, this));
			BX.bind(this.node, 'keydown', BX.proxy(this.onAndroidInput, this));
		}
		else
		{
			BX.bind(this.node, 'keydown', BX.proxy(this.onChange, this));
		}
	};

	BX.MaskedInputElement.prototype.val = function (value)
	{
		if (typeof value != 'undefined')
		{
			this.node.value = value;
		}

		return this.node.value;
	};

	BX.MaskedInputElement.prototype.onAndroidInput = function (e)
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
	};

	BX.MaskedInputElement.prototype.onChange = function (e, eReal)
	{
		var isCatch = true;
		var kc = (typeof e.which == "number") ? e.which : e.keyCode;

		if (kc <= 0)
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

		if (!isCatch)
		{
			return;
		}

		BX.PreventDefault(eReal || e);
		if (e.type == 'paste')
		{
			var clipboardData = e.clipboardData || window.clipboardData;
			var pastedData = clipboardData.getData('Text');
			BX.onCustomEvent(this, 'paste', [pastedData]);
		}
		else if (kc === 8 || kc === 46 || (BX.browser.IsIOS() && kc === 127))
		{
			var directionLeft = kc === 8;
			BX.onCustomEvent(this, 'delete', [directionLeft]);
		}
		else
		{
			var char = String.fromCharCode(kc);
			BX.onCustomEvent(this, 'change', [char]);
		}
	};

	BX.MaskedInputElement.prototype.setCaretPosition = function (pos)
	{
		this.node.setSelectionRange(pos, pos);
	};

	BX.MaskedInputElement.prototype.getSelectionStart = function ()
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
	};

	BX.MaskedInputElement.prototype.getSelectionEnd = function ()
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
	};

	BX.MaskedTextElement = function (params)
	{
		this.node = params.node;
	};


	BX.MaskedTextElement.prototype.val = function (value)
	{
		if (typeof value != 'undefined')
		{
			this.node.innerText = value;
		}

		return this.node.innerText;
	};

	BX.MaskedTextElement.prototype.onChange = function (e)
	{
	};

	BX.MaskedTextElement.prototype.setCaretPosition = function (pos)
	{
	};

	BX.MaskedTextElement.prototype.getSelectionStart = function ()
	{
		return 0;
	};

	BX.MaskedTextElement.prototype.getSelectionEnd = function ()
	{
		return 0;
	};
})();