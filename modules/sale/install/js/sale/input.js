

BX.namespace('BX.Sale.Input');

BX.Sale.Input = (function () {
	'use strict';

	var Module = {};

	// Manager /////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.Manager = (function () {

		var types = {};

		return {

			/** Create input editor.
			 * @param {string}          name     - input name
			 * @param {Object}          settings - input attributes
			 * @param {?(string|array)} value    - input value
			 * @constructor
			 */
			Editor: function (name, settings, value)
			{
				var type = settings.TYPE;

				if (! types.hasOwnProperty(type))
					throw 'invalid input type: '+type;

				var privateObject = new types[type](name, settings, value, this);

				this.getType = function () {return type;};
			},

			/** Register input type.
			 * @param {string} name
			 * @param {Object} InputConstructor
			 */
			register: function (name, InputConstructor)
			{
				if (types.hasOwnProperty(name))
					throw 'duplicate input type: '+name;

				types[name] = InputConstructor;
			}

		};

	})();

	// Utils ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	var Utils = {};

	Module.Utils = Utils;

	// Values

	Utils.asSingle = function (value)
	{
		if (value === undefined || value === null || value === '')
		{
			return null;
		}
		else if (value.constructor === Array)
		{
			var i = 0, length = value.length, val;

			for (; i < length; i++)
			{
				val = value[i];

				if (val !== undefined && val !== null && val !== '')
					return val;
			}

			return null;
		}
		else
		{
			return value;
		}
	};

	Utils.asMultiple = function (value)
	{
		if (value === undefined || value === null || value === '')
		{
			return [];
		}
		else if (value.constructor === Array)
		{
			var i = 0, length = value.length, val;

			for (; i < length;)
			{
				val = value[i];

				if (val === undefined || val === null || val === '')
				{
					value.splice(i, 1);
					--length;
				}
				else
				{
					++i;
				}
			}

			return value;
		}
		else
		{
			return [value];
		}
	};

	// DOM Manipulation

	Utils.applyTo = function (node, method, item, arg)
	{
		if (item.constructor === Array)
		{
			var i = 0, len = item.length;

			for (; i < len; i++)
				node[method](item[i], arg);
		}
		else
		{
			node[method](item, arg);
		}
	};

	// Events

	if (window.addEventListener)
	{
		Utils.addEventTo      = function (element, name, action) {return element.addEventListener   (name, action, false);};
		Utils.removeEventFrom = function (element, name, action) {return element.removeEventListener(name, action);};
	}
	else
	{
		Utils.addEventTo      = function (element, name, action) {return element.attachEvent('on'+name, action);};
		Utils.removeEventFrom = function (element, name, action) {return element.detachEvent('on'+name, action);};
	}

	Utils.stopElementEvents = function (event) // TODO remove
	{
		if (event && event.preventDefault)
		{
			event.preventDefault();
			event.stopPropagation();
		}
		else if (event = window.event)
		{
			event.returnValue = false;
			event.cancelBubble = true;
		}

		return false;
	};

	// Attributes

	Utils.applyAttributesTo = function (args, callback)
	{
		var	element = args[0],
			settings = args[1],
			i = 2, length = args.length, whitelist, name;

		for (; i < length; i++)
		{
			whitelist = args[i];

			delete whitelist.REQUIRED; // WARNING will modify args!!! TODO remove with HTML5

			for (name in settings)
				if (settings.hasOwnProperty(name) && whitelist.hasOwnProperty(name))
					callback(element, name.toLowerCase(), settings[name], whitelist[name]);
		}
	};

	Utils.globalBooleanAttributes = {CONTENTEDITABLE:'', DRAGGABLE:'true', SPELLCHECK:'', TRANSLATE:'yes'};

	Utils.applyBooleanAttributesTo = (function () {

		var callback = function (element, name, value, whiteValue)
		{
			if (value == 'Y' || value === true)
				element.setAttribute(name, whiteValue);
		};

		return function () {Utils.applyAttributesTo(arguments, callback);};

	})();

	Utils.globalValueAttributes = {
		ACCESSKEY:1, CLASS:1, CONTEXTMENU:1, DIR:1, DROPZONE:1, LANG:1, STYLE:1, TABINDEX:1, TITLE:1, DATA:1,
		'XML:LANG':1, 'XML:SPACE':1, 'XML:BASE':1
	};

	Utils.applyValueAttributesTo = (function () {

		var callback = function (element, name, value, whiteValue)
		{
			if (value)
			{
				if (name == 'DATA')
				{
					if (value !== null && typeof value === 'object')
					{
						var n;
						for (n in value)
							if (value.hasOwnProperty(n))
								element.setAttribute('data-' + n, value[n]); // TODO dataset
					}
				}
				else
				{
					element.setAttribute(name, value);
				}
			}
		};

		return function () {Utils.applyAttributesTo(arguments, callback);};

	})();

	Utils.globalEventAttributes = {
		ONABORT:1, ONBLUR:1, ONCANPLAY:1, ONCANPLAYTHROUGH:1, ONCHANGE:1, ONCLICK:1, ONCONTEXTMENU:1, ONDBLCLICK:1,
		ONDRAG:1, ONDRAGEND:1, ONDRAGENTER:1, ONDRAGLEAVE:1, ONDRAGOVER:1, ONDRAGSTART:1, ONDROP:1,
		ONDURATIONCHANGE:1, ONEMPTIED:1, ONENDED:1, ONERROR:1, ONFOCUS:1, ONINPUT:1, ONINVALID:1, ONKEYDOWN:1,
		ONKEYPRESS:1, ONKEYUP:1, ONLOAD:1, ONLOADEDDATA:1, ONLOADEDMETADATA:1, ONLOADSTART:1, ONMOUSEDOWN:1,
		ONMOUSEMOVE:1, ONMOUSEOUT:1, ONMOUSEOVER:1, ONMOUSEUP:1, ONMOUSEWHEEL:1, ONPAUSE:1, ONPLAY:1, ONPLAYING:1,
		ONPROGRESS:1, ONRATECHANGE:1, ONREADYSTATECHANGE:1, ONRESET:1, ONSCROLL:1, ONSEEKED:1, ONSEEKING:1,
		ONSELECT:1, ONSHOW:1, ONSTALLED:1, ONSUBMIT:1, ONSUSPEND:1, ONTIMEUPDATE:1, ONVOLUMECHANGE:1, ONWAITING:1
	};

	// Extend

	Utils.extend = function (child, parent)
	{
		for (var key in parent) {
			if (parent.hasOwnProperty(key))
				child[key] = parent[key];
		}
		function ctor() {this.constructor = child;}
		ctor.prototype = parent.prototype;
		child.prototype = new ctor();
		child.__super__ = parent.prototype;
		return child;
	};

	// Base ////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.BaseInput = BaseInput;

	function BaseInput(name, settings, value, publicO)
	{
		this.name     = name;
		this.settings = settings;
		this.publicO  = publicO;
		this.multiple = settings.MULTIPLE === 'Y';
		this.disabled = settings.DISABLED === 'Y';
		this.required = settings.REQUIRED === 'Y';
		this.form     = settings.FORM;

		if (settings.MULTITAG)
			this.multitag = settings.MULTITAG;

		this.createEditor(value === undefined ? settings.VALUE : value); // TODO or Viewer

		// Create Public Interface

		var privateO = this;

		publicO.getName = function () {return name;};

		publicO.appendTo = function (node) {privateO.parentNode = node; privateO.applyTo(node, 'appendChild');};
		publicO.insertTo = function (node, beforeNode) {privateO.parentNode = node; privateO.applyTo(node, 'insertBefore', beforeNode);};
		publicO.remove   = function ()
		{
			if (privateO.parentNode) {
				privateO.applyTo(privateO.parentNode, 'removeChild');
				privateO.parentNode = null;
			}
		};
		publicO.getParentNode = function () {return privateO.parentNode;};

		publicO.isMultiple = function () {return privateO.multiple;};
		publicO.isRequired = function () {return privateO.required;};

		publicO.getValue = function () {return privateO.getValue();};
		publicO.setValue = function (value) {privateO.setValue(value);};

		publicO.isDisabled = function () {return privateO.disabled;};
		publicO.setDisabled = function (disabled)
		{
			if (privateO.disabled !== disabled)
			{
				privateO.disabled = disabled;
				privateO.setDisabled(disabled);
			}
		};

		publicO.addEvent = function (name, action) {privateO.addEvent(name, action);};
	}

	BaseInput.prototype.multitag = 'div';

	// Create Editor

	BaseInput.prototype.createEditor = function (value)
	{
		// TODO hidden as new Type

		if (this.multiple)
		{
			value = Utils.asMultiple(value);

			var	items = [],
				name = this.name,
				i = 0, length = value.length;

			for (; i < length; i++)
				items.push(this.createEditorSingleItem(name+'['+i+']', value[i]));

			this.items = items;

			this.insertor = this.createEditorInsertor();
		}
		else
		{
			this.items = this.createEditorSingle(this.name, Utils.asSingle(value));
		}
	};

	BaseInput.prototype.createEditorSingle = function (name, value)
	{
		throw 'you must override ' + this.settings.TYPE + '.appendEditorSingle';
	};

	BaseInput.prototype.createEditorSingleDeletor = function (item)
	{
		var privateO = this;

		// Checkbox

		var checkbox = document.createElement('input');

		checkbox.type = 'checkbox';
		checkbox.disabled = this.disabled;

		if (this.form)
			checkbox.setAttribute('form', this.form);

		Utils.addEventTo(checkbox, 'click', function ()
		{
			privateO.setDisabledSingle(item, checkbox.checked);
		});

		// Label

		var label = document.createElement('label');

		label.appendChild(document.createTextNode(' '+BX.message('INPUT_DELETE')+' '));
		label.appendChild(checkbox);
		label.appendChild(document.createTextNode(' '));

		label.getValue = function ()
		{
			return checkbox.checked;
		};

		label.setDisabled = function (disabled)
		{
			checkbox.disabled = disabled;
		};

		label.setName = function (name)
		{
			checkbox.name = name;
		};

		return label;
	};

	BaseInput.prototype.createEditorSingleItem = function (name, value)
	{
		var item = this.createEditorSingle(name, value),
			deletor = item.deletor,
			multitag = this.multitag,
			events = this.events;

		if (events)
		{
			for (var eventName in events)
			{
				if (events.hasOwnProperty(eventName))
				{
					var actions = events[eventName], i = 0, length = actions.length;
					for (; i < length; i++)
						this.addEventSingle(item, eventName, actions[i]);
				}
			}
		}

		if (! deletor && (deletor = this.createEditorSingleDeletor(item)))
		{
			item.deletor = deletor;
			item.push(deletor);
		}

		if (multitag)
		{
			var wrapper = document.createElement(multitag);

			Utils.applyTo(wrapper, 'appendChild', item);

			wrapper.deletor = deletor;

			return wrapper;
		}
		else
		{
			return item;
		}
	};

	BaseInput.prototype.createEditorInsertor = function ()
	{
		var privateO = this;

		var button = document.createElement('input');

		button.type = 'button';
		button.value = BX.message('INPUT_ADD');
		button.disabled = this.disabled;

		if (this.form)
			button.setAttribute('form', this.form);

		Utils.addEventTo(button, 'click', function ()
		{
			var	items = privateO.items,
				parentNode = privateO.parentNode,
				item = privateO.createEditorSingleItem(privateO.name+'['+items.length+']', null);

			items.push(item);

			if (parentNode)
				Utils.applyTo(parentNode, 'insertBefore', item, button);

			privateO.afterEditorSingleInsert(privateO.multitag ? item.childNodes : item);
		});

		return button;
	};

	BaseInput.prototype.afterEditorSingleInsert = function (item) {};

	// Set Value

	BaseInput.prototype.setValue = function (value)
	{
		if (this.multiple)
		{
			value = Utils.asMultiple(value);

			var	name = this.name,
				multitag = this.multitag,
				parentNode = this.parentNode,
				i = 0,
				items = this.items,
				valueLength = value.length,
				itemsLength = items.length;

			if (valueLength == itemsLength)
			{
				for (; i < itemsLength; i++)
					this.setValueSingle(multitag ? items[i].childNodes : items[i], Utils.asSingle(value[i]));
			}
			else if (valueLength > itemsLength)
			{
				for (; i < itemsLength; i++)
					this.setValueSingle(multitag ? items[i].childNodes : items[i], Utils.asSingle(value[i]));

				// add more items

				var item, insertor = this.insertor;

				for (; i < valueLength; i++)
				{
					item = this.createEditorSingleItem(name+'['+i+']', value[i]);

					items.push(item);

					if (parentNode)
						Utils.applyTo(parentNode, 'insertBefore', item, insertor);
				}
			}
			else // valueLength < itemsLength
			{
				for (; i < valueLength; i++)
					this.setValueSingle(multitag ? items[i].childNodes : items[i], Utils.asSingle(value[i]));

				// remove excess items

				var start = i;

				if (parentNode)
					for (; i < itemsLength; i++)
						Utils.applyTo(parentNode, 'removeChild', items[i]);

				items.splice(start, itemsLength - start);
			}
		}
		else
		{
			this.setValueSingle(this.items, Utils.asSingle(value));
		}
	};

	BaseInput.prototype.setValueSingle = function (item, value)
	{
		throw 'you must override ' + this.settings.TYPE + '.setValueSingle';
	};

	// Get Value

	BaseInput.prototype.getValue = function ()
	{
		if (this.multiple)
		{
			var	values = [],
				items = this.items,
				multitag = this.multitag,
				i = 0, length = items.length, val;

			for (; i < length; i++)
			{
				val = this.getValueSingle(multitag ? items[i].childNodes : items[i]);

				if (val !== undefined && val !== null && val !== '')
					values.push(val);
			}

			return values;
		}
		else
		{
			return this.getValueSingle(this.items);
		}
	};

	BaseInput.prototype.getValueSingle = function (item)
	{
		throw 'you must override ' + this.settings.TYPE + '.getValueSingle';
	};

	// Disabled

	BaseInput.prototype.setDisabled = function (disabled)
	{
		if (this.multiple)
		{
			var	items = this.items,
				multitag = this.multitag,
				i = 0, length = items.length,
				insertor = this.insertor,
				item, deletor;

			for (; i < length; i++)
			{
				item = items[i];
				deletor = item.deletor;

				if (multitag)
					item = item.childNodes;

				if (deletor)
				{
					deletor.setDisabled(disabled);

					if (! deletor.getValue())
						this.setDisabledSingle(item, disabled);
				}
				else
				{
					this.setDisabledSingle(item, disabled);
				}
			}

			if (insertor)
				insertor.disabled = disabled;
		}
		else
		{
			this.setDisabledSingle(this.items, disabled);
		}
	};

	BaseInput.prototype.setDisabledSingle = function (item, disabled)
	{
		throw 'you must override ' + this.settings.TYPE + '.setDisabledSingle';
	};

	// Event

	BaseInput.prototype.createEventHandler = function (action)
	{
		var publicO = this.publicO;

		return function (event)
		{
			action.call(this, event, publicO);
		};
	};

	BaseInput.prototype.events = false;

	BaseInput.prototype.addEvent = function (name, action)
	{
		action = this.createEventHandler(action);

		var events = this.events;

		if (! events)
			events = this.events = {};

		if (events.hasOwnProperty(name))
			events[name].push(action);
		else
			events[name] = [action];

		if (this.multiple)
		{
			var	items = this.items,
				multitag = this.multitag,
				i = 0, length = items.length;

			for (; i < length; i++)
				this.addEventSingle(multitag ? items[i].childNodes : items[i], name, action);
		}
		else
		{
			this.addEventSingle(this.items, name, action);
		}
	};

	BaseInput.prototype.addEventSingle = function (item, name, action)
	{
		throw 'you must override ' + this.settings.TYPE + '.addEventSingle';
	};

	BaseInput.prototype.applyEventAttributesTo = function ()
	{
		var privateO = this;

		Utils.applyAttributesTo(arguments, function (element, name, value, whiteValue)
		{
			if (value)
			{
				//Utils.addEventTo(element, name.substring(2), privateO.publicBind(eval('(function(){'+value+'})')));

				var eventHandler = privateO.createEventHandler(eval('(function(event, input){'+value+'})'));

				if (window.addEventListener) // TODO compile
				{
					Utils.addEventTo(element, name.substring(2), eventHandler);
				}
				else
				{
					Utils.addEventTo(element, name.substring(2), function ()
					{
						return eventHandler.call(element);
					});
				}
			}
		});
	};

	// DOM Manipulation

	BaseInput.prototype.applyTo = function (node, method, arg)
	{
		if (this.multiple)
		{
			var items = this.items, i = 0, len = items.length, insertor = this.insertor;

			for (; i < len; i++)
				Utils.applyTo(node, method, items[i], arg);

			if (insertor)
				node[method](insertor, arg);
		}
		else
		{
			Utils.applyTo(node, method, this.items, arg);
		}
	};

	// String //////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.StringInput = StringInput;
	Utils.extend(StringInput, BaseInput);
	Module.Manager.register('STRING', StringInput);

	function StringInput(name, settings, value, publicO)
	{
		StringInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	StringInput.prototype.createEditorSingle = function (name, value)
	{
		var element, settings = this.settings;

		if (settings.MULTILINE == 'Y')
		{
			element = document.createElement('textarea');

			if (! settings.ROWS && ! settings.COLS)
			{
				settings.ROWS = 4;
				settings.COLS = 40;
			}

			Utils.applyBooleanAttributesTo(element, settings, Utils.globalBooleanAttributes, {DISABLED:'', READONLY:'', AUTOFOCUS:'', REQUIRED:''});
			Utils.applyValueAttributesTo(element, settings, Utils.globalValueAttributes, {FORM:1, MAXLENGTH:1, PLACEHOLDER:1, DIRNAME:1, ROWS:1, COLS:1, WRAP:1});
			this.applyEventAttributesTo(element, settings, Utils.globalEventAttributes);
		}
		else
		{
			element = document.createElement('input');
			element.type = 'text';

			if (! settings.SIZE)
			{
				settings.SIZE = 30;
			}

			if (settings.PATTERN
				&& settings.PATTERN.length > 0
				&& settings.PATTERN[0] === settings.PATTERN[settings.PATTERN.length-1]
			)
			{
				var clearPattern = settings.PATTERN.substr(1, settings.PATTERN.length - 2);
				if (clearPattern && clearPattern.length)
				{
					settings.PATTERN = clearPattern;
				}
			}

			Utils.applyBooleanAttributesTo(element, settings, Utils.globalBooleanAttributes, {DISABLED:'', READONLY:'', AUTOFOCUS:'', REQUIRED:'', AUTOCOMPLETE:'on'});
			Utils.applyValueAttributesTo(element, settings, Utils.globalValueAttributes, {FORM:1, MAXLENGTH:1, PLACEHOLDER:1, DIRNAME:1, SIZE:1, LIST:1, PATTERN:1});
			this.applyEventAttributesTo(element, settings, Utils.globalEventAttributes);
		}

		element.name  = name;
		element.value = value || '';

		// Deletor
		var item = [element];

		if (settings.MULTIPLE == 'Y')
		{
			var deletor = this.createEditorSingleDeletor(item);
			deletor.setName(name+'[DELETE]');
			item.deletor = deletor;
			item.push(deletor);
		}
		
		return item;
	};

	StringInput.prototype.afterEditorSingleInsert = function (item)
	{
		item[0].focus();
	};

	StringInput.prototype.setValueSingle = function (item, value)
	{
		item[0].value = value;
	};

	StringInput.prototype.getValueSingle = function (item)
	{
		var element = item[0];
		return element.disabled ? null : element.value;
	};

	StringInput.prototype.setDisabledSingle = function (item, disabled)
	{
		item[0].disabled = disabled;
	};

	StringInput.prototype.addEventSingle = function (item, name, action)
	{
		Utils.addEventTo(item[0], name, action);
	};

	// Number //////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.NumberInput = NumberInput;
	Utils.extend(NumberInput, BaseInput);
	Module.Manager.register('NUMBER', NumberInput);

	function NumberInput(name, settings, value, publicO)
	{
		NumberInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	NumberInput.prototype.createEditorSingle = function (name, value)
	{
		// TODO HTML5 from IE10: remove SIZE; Add MIN, MAX, STEP; Change type="number"

		var s, size = 5, settings = this.settings;

		if ((s = settings.MIN) && s.toString().length > size)
			size = s.toString().length;

		if ((s = settings.MAX) && s.toString().length > size)
			size = s.toString().length;

		if ((s = settings.STEP) && s.toString().length > size)
			size = s.toString().length;

		if (size > 30)
			size = 30;

		var element = document.createElement('input');
		element.type  = 'text';
		element.name  = name;
		element.value = value;
		element.size  = size;

		Utils.applyBooleanAttributesTo(element, settings, Utils.globalBooleanAttributes, {DISABLED:'', READONLY:'', AUTOFOCUS:'', REQUIRED:'', AUTOCOMPLETE:'on'});
		Utils.applyValueAttributesTo(element, settings, Utils.globalValueAttributes, {FORM:1, LIST:1, PLACEHOLDER:1});
		this.applyEventAttributesTo(element, settings, Utils.globalEventAttributes);

		return [element];
	};

	NumberInput.prototype.afterEditorSingleInsert = function (item)
	{
		item[0].focus();
	};

	NumberInput.prototype.setValueSingle = function (item, value)
	{
		item[0].value = value;
	};

	NumberInput.prototype.getValueSingle = function (item)
	{
		var element = item[0];
		return element.disabled ? null : element.value;
	};

	NumberInput.prototype.setDisabledSingle = function (item, disabled)
	{
		item[0].disabled = disabled;
	};

	NumberInput.prototype.addEventSingle = function (item, name, action)
	{
		Utils.addEventTo(item[0], name, action);
	};

	// Either Y or N ///////////////////////////////////////////////////////////////////////////////////////////////////

	Module.EitherYNInput = EitherYNInput;
	Utils.extend(EitherYNInput, BaseInput);
	Module.Manager.register('Y/N', EitherYNInput);

	function EitherYNInput(name, settings, value, publicO)
	{
		EitherYNInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	EitherYNInput.prototype.createEditorSingle = function (name, value)
	{
		var settings = this.settings;

		// Hidden

		var hidden = document.createElement('input');

		hidden.type     = 'hidden';
		hidden.name     = name;
		hidden.value    = 'N';
		hidden.disabled = this.disabled;

		if (this.form)
			hidden.setAttribute('form', this.form);

		// Checkbox

		var checkbox = document.createElement('input');

		checkbox.type    = 'checkbox';
		checkbox.name    = name;
		checkbox.value   = 'Y';
		checkbox.checked = value === 'Y';

		Utils.applyBooleanAttributesTo(checkbox, settings, Utils.globalBooleanAttributes, {DISABLED:'', AUTOFOCUS:'', REQUIRED:''});
		Utils.applyValueAttributesTo(checkbox, settings, Utils.globalValueAttributes, {FORM:1});
		this.applyEventAttributesTo(checkbox, settings, Utils.globalEventAttributes);

		return [hidden, checkbox];
	};

	EitherYNInput.prototype.afterEditorSingleInsert = function (item)
	{
		item[1].focus();
	};

	EitherYNInput.prototype.setValueSingle = function (item, value)
	{
		item[1].checked = value === 'Y';
	};

	EitherYNInput.prototype.getValueSingle = function (item)
	{
		var element = item[1];
		return element.disabled ? null : (element.checked ? 'Y' : 'N');
	};

	EitherYNInput.prototype.setDisabledSingle = function (item, disabled)
	{
		item[0].disabled = disabled;
		item[1].disabled = disabled;
	};

	EitherYNInput.prototype.addEventSingle = function (item, name, action)
	{
		Utils.addEventTo(item[1], name, action);
	};

	// Enumeration /////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.EnumInput = EnumInput;
	Utils.extend(EnumInput, BaseInput);
	Module.Manager.register('ENUM', EnumInput);

	function EnumInput(name, settings, value, publicO)
	{
		this.multielement = settings.MULTIELEMENT === 'Y';
		EnumInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	EnumInput.prototype.getValueObject = function (value)
	{
		value = Utils.asMultiple(value);

		var	object = {},
			i = 0, length = value.length, v;

		for (; i < length; i++)
		{
			v = value[i];

			if (v !== undefined)
				object[v] = true;
		}

		return object;
	};

	EnumInput.prototype.createEditor = function (value)
	{
		var	variants = [],
			name = this.name,
			settings = this.settings,
			options = [];

		if (BX.type.isPlainObject(settings.OPTIONS))
		{
			for (var sort in settings.OPTIONS_SORT)
			{
				var code = settings.OPTIONS_SORT[sort];
				if (BX.type.isNotEmptyString(settings.OPTIONS[code]))
					options.push(settings.OPTIONS[code]);
			}
		}
		else if (BX.type.isArray(settings.OPTIONS))
		{
			options = settings.OPTIONS
		}

		if (options === undefined || options === null || (options.constructor !== Object && options.constructor !== Array) || options.length === 0)
		{
			this.variants = [];
			this.items = [document.createTextNode(BX.message('INPUT_ENUM_OPTIONS_ERROR'))];
			return;
		}

		// TODO hidden as new Type

		value = this.getValueObject(value);

		if (this.multielement)
		{
			var	type = 'radio',
				currentObject = this,
				multitag = this.multitag,
				items = [];

			if (this.multiple)
			{
				type = 'checkbox';
				name += '[]';
			}

			this.createEditorOptions(null, options, value,
				function (group)
				{
					var	legend = document.createElement('legend');
					legend.appendChild(document.createTextNode(group));

					var container = document.createElement('fieldset');
					container.appendChild(legend);

					items.push(container);
					return container;
				},
				function (container, value, checked, text)
				{
					// Element

					var element = document.createElement('input');

					element.type     = type;
					element.name     = name;
					element.value    = value;
					element.checked  = checked;
					element.disabled = currentObject.disabled;

					if (currentObject.form)
						element.setAttribute('form', currentObject.form);

					currentObject.applyEventAttributesTo(element, settings, Utils.globalEventAttributes);

					// Label

					var label = document.createElement('label');

					label.appendChild(element);
					label.appendChild(document.createTextNode(' '+text+' '));

					// Wrapper

					var wrapper;

					if (multitag)
					{
						wrapper = document.createElement(multitag);
						wrapper.appendChild(label);
					}
					else
					{
						wrapper = label;
					}

					if (container)
						container.appendChild(wrapper);
					else
						items.push(wrapper);

					variants.push(element);
				}
			);

			this.items = items;
		}
		else
		{
			var select = document.createElement('select');

			if (this.multiple)
			{
				select.name = name+'[]';
				select.multiple = true;
			}
			else
			{
				select.name = name;
			}

			Utils.applyBooleanAttributesTo(select, settings, Utils.globalBooleanAttributes, {DISABLED:'', AUTOFOCUS:'', REQUIRED:''});
			Utils.applyValueAttributesTo(select, settings, Utils.globalValueAttributes, {FORM:1, SIZE:1});
			this.applyEventAttributesTo(select, settings, Utils.globalEventAttributes);

			this.createEditorOptions(select, options, value,
				function (group)
				{
					var container = document.createElement('optgroup');

					container.label = group;

					select.appendChild(container);
					return container;
				},
				function (container, value, selected, text)
				{
					var option = document.createElement('option');

					option.text     = text;
					option.value    = value;
					option.selected = selected;

					container.appendChild(option);
					variants.push(option);
				}
			);

			if (settings.REQUIRED == "N")
			{
				var option = document.createElement('option');
				option.text     = BX.message('INPUT_ENUM_EMPTY_OPTION');
				option.value    = "";

				if (Object.keys(value).length === 0)
					option.selected = "selected";

				select.insertBefore(option, select.firstChild);
				variants.push(option);
			}

			this.items = [select];
		}

		this.variants = variants;
	};

	EnumInput.prototype.createEditorOptions = function (container, options, selected, group, option)
	{
		var key, value, code;

		for (key in options)
		{
			if (options.hasOwnProperty(key))
			{
				value = options[key];
				code = this.settings.OPTIONS_SORT[key];

				if (value.constructor === Object)
					this.createEditorOptions(group(code), value, selected, group, option);
				else
					option(container, code, selected.hasOwnProperty(code), value || code);
			}
		}
	};

	EnumInput.prototype.setValue = function (value)
	{
		value = this.getValueObject(value);

		var	variants = this.variants,
			multielement = this.multielement,
			i = 0, length = variants.length, variant;

		for (; i < length; i++)
		{
			variant = variants[i];
			variant[multielement ? 'checked' : 'selected'] = value.hasOwnProperty(variant.value);
		}
	};

	EnumInput.prototype.getValue = function ()
	{
		var	value = [],
			variants = this.variants,
			multielement = this.multielement,
			i = 0, length = variants.length, variant;

		for (; i < length; i++)
		{
			variant = variants[i];
			if (variant[multielement ? 'checked' : 'selected'])
				value.push(variant.value);
		}

		return this.multiple ? value : Utils.asSingle(value);
	};

	EnumInput.prototype.setDisabled = function (disabled)
	{
		if (this.multielement)
		{
			var	variants = this.variants,
				i = 0, length = variants.length;

			for (; i < length; i++)
				variants[i].disabled = disabled;
		}
		else
		{
			this.items[0].disabled = disabled;
		}
	};

	EnumInput.prototype.addEvent = function (name, action)
	{
		action = this.createEventHandler(action);

		if (this.multielement)
		{
			var	variants = this.variants,
				i = 0, length = variants.length;

			for (; i < length; i++)
				Utils.addEventTo(variants[i], name, action);
		}
		else
		{
			Utils.addEventTo(this.items[0], name, action);
		}
	};

	// File ////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.FileInput = FileInput;
	Utils.extend(FileInput, BaseInput);
	Module.Manager.register('FILE', FileInput);

	function FileInput(name, settings, value, publicO)
	{
		FileInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	FileInput.prototype.createEditorSingle = function (name, value)
	{
		var privateO = this,
			settings = this.settings;

		var anchor = document.createElement('a');

		// Hidden

		var hidden = document.createElement('input');

		hidden.type     = 'hidden';
		hidden.name     = name+'[ID]';
		hidden.disabled = this.disabled;

		if (this.form)
			hidden.setAttribute('form', this.form);

		// File

		var file = document.createElement('input');

		file.type = 'file';
		file.name = name;
		file.style.position   = 'absolute';
		file.style.visibility = 'hidden';

		Utils.applyBooleanAttributesTo(file, settings, Utils.globalBooleanAttributes, {DISABLED:'', AUTOFOCUS:'', REQUIRED:''});
		Utils.applyValueAttributesTo(file, settings, Utils.globalValueAttributes, {FORM:1, ACCEPT:1});
		this.applyEventAttributesTo(file, settings, Utils.globalEventAttributes);

		var resetSingle = function (event)
		{
			file.value = ''; // TODO IE8
			privateO.setAnchor(anchor, anchor.value);
			Utils.removeEventFrom(anchor, 'click', resetSingle);
			return Utils.stopElementEvents(event);
		};

		Utils.addEventTo(file, 'change', function ()
		{
			var filePath = file.value;
			if (filePath)
			{
				var child = anchor.firstChild;
				if (child)
					anchor.removeChild(child);

				anchor.removeAttribute('href');
				anchor.title = BX.message('INPUT_FILE_RESET');
				anchor.appendChild(document.createTextNode(filePath.split(/(\\|\/)/g).pop()));

				Utils.addEventTo(anchor, 'click', resetSingle);
			}
			else // reverse to previous state in chrome
			{
				resetSingle();
			}
		});

		// Button

		var button = document.createElement('input');

		button.type  = 'button';
		button.value = BX.message('INPUT_FILE_BROWSE');
		button.style.margin = '5px 10px';

		Utils.addEventTo(button, 'click', function ()
		{
			file.click();
		});

		// Item

		var item = [anchor, hidden, file, button];

		this.setValueSingle(item, value);

		// Deletor

		var deletor = this.createEditorSingleDeletor(item);

		deletor.setName(name+'[DELETE]');

		item.deletor = deletor;

		item.push(deletor);

		return item;
	};

	FileInput.prototype.setAnchor = function (anchor, value)
	{
		var child = anchor.firstChild,
			src = value.SRC,
			id = value.ID;

		if (child)
		{
			anchor.removeChild(child);
			child = null;
		}

		if (src)
		{
			anchor.href = src;
			anchor.target = '_blank';
			anchor.title = BX.message('INPUT_FILE_DOWNLOAD');
			switch (src.split('.').pop())
			{
				case 'jpg':
				case 'bmp':
				case 'jpeg':
				case 'jpe':
				case 'gif':
				case 'png':
						child = document.createElement('img');
						child.src = src;
						child.style.maxWidth  = '100px';
						child.style.maxHeight = '100px';
						anchor.appendChild(child);
			}
		}
		else
		{
			anchor.removeAttribute('href');
			anchor.removeAttribute('target');
			anchor.removeAttribute('title');
		}

		if (! child && id)
		{
			anchor.appendChild(document.createTextNode(value.ORIGINAL_NAME || value.FILE_NAME || id));
		}
	};

	FileInput.prototype.setValueSingle = function (item, value)
	{
		if (value % 1 === 0)
		{
			value = {ID: value};
		}
		else if (! value.hasOwnProperty('ID'))
		{
			value = {};
		}

		var anchor = item[0];
		anchor.value = value;
		this.setAnchor(anchor, value);

		var hidden = item[1];
		hidden.value = value.ID;
	};

	FileInput.prototype.getValueSingle = function (item)
	{
		var hidden = item[1];
		return hidden.disabled ? null : hidden.value;
	};

	FileInput.prototype.setDisabledSingle = function (item, disabled)
	{
		var	file = item[2],
			button = item[3];

		file.disabled = disabled;
		button.disabled = disabled;
	};

	FileInput.prototype.addEventSingle = function (item, name, action)
	{
		var file = item[2];

		Utils.addEventTo(file, name, action);
	};

	FileInput.prototype.afterEditorSingleInsert = function (item)
	{
		var button = item[3];
		button.focus();
	};

	// Date ////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.DateInput = DateInput;
	Utils.extend(DateInput, BaseInput);
	Module.Manager.register('DATE', DateInput);

	function DateInput(name, settings, value, publicO)
	{
		DateInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	DateInput.prototype.createEditorSingle = function (name, value)
	{
		var settings = this.settings,
			showTime = settings.TIME == 'Y';

		// TODO HTML5 input="date|datetime|datetime-local" & min & max & step(date:integer|datetime..:float)

		// Text

		var text = document.createElement('input');
		text.type  = 'text';
		text.name  = name;
		text.value = value;

		Utils.applyBooleanAttributesTo(text, settings, Utils.globalBooleanAttributes, {DISABLED:'', READONLY:'', AUTOFOCUS:'', REQUIRED:'', AUTOCOMPLETE:'on'});
		Utils.applyValueAttributesTo(text, settings, Utils.globalValueAttributes, {FORM:1, LIST:1});
		this.applyEventAttributesTo(text, settings, Utils.globalEventAttributes);

		text.setAttribute('size', showTime ? '20' : '10');

		// Button

		var button = document.createElement('input');

		button.type     = 'button';
		button.value    = BX.message('INPUT_DATE_SELECT');
		button.disabled = this.disabled;
		button.style.margin = '0 10px';

		Utils.addEventTo(button, 'click', function ()
		{
			BX.calendar({node:button, field:name, form:'', bTime:showTime, bHideTime:false}); // TODO form
		});

		return [text, button];
	};

	DateInput.prototype.afterEditorSingleInsert = function (item)
	{
		item[0].focus();
	};

	DateInput.prototype.setValueSingle = function (item, value)
	{
		item[0].value = value;
	};

	DateInput.prototype.getValueSingle = function (item)
	{
		var element = item[0];
		return element.disabled ? null : element.value;
	};

	DateInput.prototype.setDisabledSingle = function (item, disabled)
	{
		item[0].disabled = disabled;
		item[1].disabled = disabled;
	};

	DateInput.prototype.addEventSingle = function (item, name, action)
	{
		Utils.addEventTo(item[0], name, action);
	};

	// Location ////////////////////////////////////////////////////////////////////////////////////////////////////////

	Module.LocationInput = LocationInput;
	Utils.extend(LocationInput, BaseInput);
	Module.Manager.register('LOCATION', LocationInput);

	function LocationInput(name, settings, value, publicO)
	{
		LocationInput.__super__.constructor.call(this, name, settings, value, publicO);
		var privateO = this;
		publicO.getValuePath = function () {return privateO.getValuePath();};
	}

	LocationInput.prototype.getValuePath = function ()
	{
		if (this.multiple)
		{
			var	values = [],
				items = this.items,
				multitag = this.multitag,
				i = 0, length = items.length, val;

			for (; i < length; i++)
			{
				val = this.getValuePathSingle(multitag ? items[i].childNodes : items[i]);

				if (val !== undefined && val !== null && val !== '')
					values.push(val);
			}

			return values;
		}
		else
		{
			return this.getValuePathSingle(this.items);
		}
	};

	LocationInput.prototype.createEditorSingle = function (name, value)
	{
		return BX.Sale.InputManager.locationLoader.display({
			properties: {
				INPUT_NAME: name,
				CODE: value
			},
			parent: this
		});
	};

	LocationInput.prototype.setValueSingle = function (item, value)
	{
		var loc = BX.data(item[0], 'sale-location-property-link');
		loc.setValueByLocationCode(value);
	};

	LocationInput.prototype.getValueSingle = function (item)
	{
		var loc = BX.data(item[0], 'sale-location-property-link');

		if(!loc)
		{
			BX.debug('loc undefined!');
			return null;
		}

		//return loc.checkDisabled() ? null : loc.getValue();
		return loc.getValue();
	};

	LocationInput.prototype.getValuePathSingle = function (item)
	{
		var loc = BX.data(item[0], 'sale-location-property-link');

		if(!loc)
		{
			BX.debug('loc undefined!');
			return null;
		}

		//return loc.checkDisabled() ? null : loc.getValue();
		return loc.getSelectedPath();
	};

	LocationInput.prototype.setDisabledSingle = function (item, disabled)
	{
		var loc = BX.data(item[0], 'sale-location-property-link');

		loc[disabled ? 'disable' : 'enable']();
	};

	// supports only "change", TODO "cityset" maybe??
	LocationInput.prototype.addEventSingle = function (item, name, action)
	{
		if(name != 'change')
			throw new Error('action is not supported');

		// here are two options:
		// 1) bind to the low-level native input that lies behind all logic
		// 2) bind to the artificial high-level events like "after-select-item"
		// i prefer the second

		var loc = BX.data(item[0], 'sale-location-property-link');

		if(!loc)
		{
			BX.debug('loc undefined!');
			return false;
		}

		loc.bindEvent('after-select-item', action);
		loc.bindEvent('after-deselect-item', action);

		return true;
	};

	// Address ////////////////////////////////////////////////////////////////////////////////////////////////////////
	Module.AddressInput = AddressInput;
	Utils.extend(AddressInput, BaseInput);
	Module.Manager.register('ADDRESS', AddressInput);

	function AddressInput(name, settings, value, publicO)
	{
		AddressInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	AddressInput.prototype.getValuePath = function ()
	{
	};

	AddressInput.prototype.createEditorSingle = function (name, value)
	{
		var addressControl = new BX.Sale.AddressControlConstructor({
			propsData: {
				name: name,
				initValue: value ? JSON.stringify(value) : null,
				isLocked: false,
			}
		});

		addressControl.$mount();

		return [addressControl.$el];
	};

	AddressInput.prototype.setValueSingle = function (item, value)
	{
	};

	AddressInput.prototype.getValueSingle = function (item)
	{
	};

	AddressInput.prototype.setDisabledSingle = function (item, disabled)
	{
	};

	AddressInput.prototype.addEventSingle = function (item, name, action)
	{
	};

	Module.UserFieldInput = UserFieldInput;
	Utils.extend(UserFieldInput, BaseInput);
	Module.Manager.register('UF', UserFieldInput);

	function UserFieldInput(name, settings, value, publicO)
	{
		UserFieldInput.__super__.constructor.call(this, name, settings, value, publicO);
	}

	UserFieldInput.prototype.createEditor = function (value)
	{
		var element = document.createElement('div');
		if (BX.type.isNotEmptyString(this.settings.EDIT_HTML))
			element.innerHTML = this.settings.EDIT_HTML;
		this.items = [element];
	};

	UserFieldInput.prototype.afterEditorSingleInsert = function (item)
	{
		item[0].focus();
	};

	UserFieldInput.prototype.setValueSingle = function (item, value)
	{
		item[0].value = value;
	};

	UserFieldInput.prototype.getValueSingle = function (item)
	{
		var element = item[0];
		return element.disabled ? null : element.value;
	};

	UserFieldInput.prototype.setDisabledSingle = function (item, disabled)
	{
		item[0].disabled = disabled;
	};

	UserFieldInput.prototype.addEventSingle = function (item, name, action)
	{
		Utils.addEventTo(item[0], name, action);
	};

	return Module;

})();

// TODO remove or move into LocationInput!!
BX.Sale.InputManager = {};
BX.Sale.InputManager.locationLoader = function(){};
BX.Sale.InputManager.locationLoader.etalon = null;
BX.Sale.InputManager.locationLoader.spawn = function(parameters)
{
	var self = this;
	var loc = self.etalon.spawn(parameters.scope, {
		useSpawn: false,
		initializeByGlobalEvent: false
	});
	loc.clearSelected();
	loc.setTargetInputName(parameters.properties.INPUT_NAME);

	if(typeof parameters.properties.CODE != 'undefined' && parameters.properties.CODE != null)
	{
		loc.setValueByLocationCode(parameters.properties.CODE);
	}

	BX.data(parameters.scope, 'sale-location-property-link', loc);

	return loc;
};
BX.Sale.InputManager.locationLoader.preload = function(properties, callbacks)
{
	var self = this;

	if(this.etalon != null)
	{
		if(BX.type.isFunction(callbacks.success))
			callbacks.success();

		return;
	}

	BX.ajax({

		url: '/bitrix/tools/sale_location_loader.php',
		method: 'post',
		dataType: 'html',
		async: true,
		processData: false,
		emulateOnload: true,
		start: true,
		data: properties,
		onsuccess: function(result){

			// appending etalon
			var scope = BX.create('div', {style: {display: 'none'}});
			BX.append(scope, document.getElementsByTagName('body')[0]);

			BX.html(scope, result, {
				htmlFirst: true,
				callback: function(){

					self.etalon = BX.locationSelectors['SALE_LOCATION_SELECTOR_RESOURCES'];
					BX.onCustomEvent(self, 'bx-sale-location-preloaded', [self.etalon, null]);

					if(BX.type.isFunction(callbacks.success))
						callbacks.success();
				}
			});
		},
		onfailure: function(type, e){

			if(BX.type.isFunction(callbacks.fail))
				callbacks.fail.apply(self, arguments);

			BX.onCustomEvent(self, 'bx-sale-location-preloaded', [null, arguments]);
		}

	});
};
BX.Sale.InputManager.locationLoader.display = function(parameters)
{
	var self = this;

	if(typeof BX.locationSelectors != 'undefined' && typeof BX.locationSelectors['SALE_LOCATION_SELECTOR_RESOURCES'] != 'undefined')
		self.etalon = BX.locationSelectors['SALE_LOCATION_SELECTOR_RESOURCES'];

	parameters.scope = BX.create('div');

	if(this.etalon != null)
	{
		parameters.parent.loc = self.spawn(parameters);
	}
	else
	{
		self.preload(parameters.properties, {
			success: function(){
				parameters.parent.loc = self.spawn(parameters);
			},
			fail: function(){
				parameters.scope.innerHTML = 'Network failure';
			}
		});
	}

	return [parameters.scope];
};
