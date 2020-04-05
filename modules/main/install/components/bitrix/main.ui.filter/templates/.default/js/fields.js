;(function() {
	'use strict';

	BX.namespace('BX.Filter');

	/**
	 * Filter fields class
	 * @param parent
	 * @constructor
	 */
	BX.Filter.Fields = function(parent)
	{
		this.parent = null;
		this.init(parent);
	};

	var errorMessages = new WeakMap();
	var values = new WeakMap();


	BX.Filter.Fields.prototype = {
		init: function(parent)
		{
			this.parent = parent;
			BX.addCustomEvent(window, 'UI::Select::change', BX.delegate(this._onDateTypeChange, this));
		},

		deleteField: function(node)
		{
			BX.remove(node);
		},

		isFieldDelete: function(node)
		{
			return BX.hasClass(node, this.parent.settings.classFieldDelete);
		},

		isFieldValueDelete: function(node)
		{
			return (
				BX.hasClass(node, this.parent.settings.classValueDelete) ||
				BX.hasClass(node.parentNode, this.parent.settings.classValueDelete)
			);
		},

		isDragButton: function(node)
		{
			return node && BX.hasClass(node, this.parent.settings.classPresetDragButton);
		},

		/**
		 * Clears values of filter field node
		 * @param {HTMLElement} field
		 */
		clearFieldValue: function(field)
		{
			if (field)
			{
				var controls = BX.Filter.Utils.getByClass(field, this.parent.settings.classControl, true);
				var squares = BX.Filter.Utils.getByClass(field, this.parent.settings.classSquare, true);

				(squares || []).forEach(BX.remove);
				(controls || []).forEach(function(control) {
					control && 'value' in control && (control.value = '');
				}, this);
			}
		},

		getField: function(node)
		{
			var field;

			if (BX.type.isDomNode(node))
			{
				field = BX.findParent(node, {class: this.parent.settings.classField}, true, false);

				if (!BX.type.isDomNode(field))
				{
					field = BX.findParent(node, {class: this.parent.settings.classFieldGroup}, true, false);
				}
			}

			return field;
		},

		render: function(template, data)
		{
			var dataKeys, result, tmp, placeholder;

			if (BX.type.isPlainObject(data) && BX.type.isNotEmptyString(template))
			{
				dataKeys = Object.keys(data);

				dataKeys.forEach(function(key) {
					placeholder = '{{'+key+'}}';
					template = template.replace(new RegExp(placeholder, 'g'), data[key]);
				});

				tmp = BX.create('div', {html: template});
				result = BX.Filter.Utils.getByClass(tmp, this.parent.settings.classFieldGroup);

				if (!BX.type.isDomNode(result))
				{
					result = BX.Filter.Utils.getByClass(tmp, this.parent.settings.classField);
				}

				if (!BX.type.isDomNode(result))
				{
					result = BX.Filter.Utils.getByClass(tmp, this.parent.settings.classFieldLine);
				}
			}

			return result;
		},

		createInputText: function(fieldData)
		{
			var field = {
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				deleteButton: true,
				valueDelete: true,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: [
					{
						block: 'main-ui-control-string',
						name: fieldData.NAME,
						placeholder: fieldData.PLACEHOLDER || '',
						value: (BX.type.isNotEmptyString(fieldData.VALUE) ||
								BX.type.isNumber(fieldData.VALUE) ? fieldData.VALUE : ''),
						tabindex: fieldData.TABINDEX
					}
				]
			};

			return BX.decl(field);
		},

		createDestSelector: function(fieldData)
		{
			var input, square;
			var field = {
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				deleteButton: true,
				valueDelete: true,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: {
					block: 'main-ui-control-entity',
					mix: 'main-ui-control',
					attrs: {
						'data-multiple': JSON.stringify(fieldData.MULTIPLE)
					},
					content: []
				}
			};

			if ('_label' in fieldData.VALUES && !!fieldData.VALUES._label)
			{
				if (fieldData.MULTIPLE)
				{
					var label = !!fieldData.VALUES._label ? fieldData.VALUES._label : [];

					if (BX.type.isPlainObject(label))
					{
						label = Object.keys(label).map(function(key) {
							return label[key];
						});
					}

					if (!BX.type.isArray(label))
					{
						label = [ label ];
					}

					var value = !!fieldData.VALUES._value ? fieldData.VALUES._value : [];
					if (BX.type.isPlainObject(value))
					{
						value = Object.keys(value).map(function(key) {
							return value[key];
						});
					}

					if (!BX.type.isArray(value))
					{
						value = [ value ];
					}

					label.forEach(function(currentLabel, index) {
						field.content.content.push({
							block: 'main-ui-square',
							tag: 'span',
							name: currentLabel,
							item: {_label: currentLabel, _value: value[index]}
						});
					});
				}
				else
				{
					field.content.content.push({
						block: 'main-ui-square',
						tag: 'span',
						name: '_label' in fieldData.VALUES ? fieldData.VALUES['_label'] : '',
						item: fieldData.VALUES
					});
				}
			}

			field.content.content.push(
				{
					block: 'main-ui-square-search',
					tag: 'span',
					content: {
						block: 'main-ui-control-string',
						name: fieldData.NAME + '_label',
						tabindex: fieldData.TABINDEX,
						type: 'text',
						placeholder: fieldData.PLACEHOLDER || ''
					}
				},
				{
					block: 'main-ui-control-string',
					name: fieldData.NAME,
					type: 'hidden',
					placeholder: fieldData.PLACEHOLDER || '',
					value: '_value' in fieldData.VALUES ? fieldData.VALUES['_value'] : '',
					tabindex: fieldData.TABINDEX
				}
			);

			field = BX.decl(field);

			input = BX.Filter.Utils.getBySelector(field, '.main-ui-control-string[type="text"]');
			BX.addClass(input, 'main-ui-square-search-item');

			BX.bind(input, 'focus', function(event)
			{
				BX.fireEvent(event.currentTarget, 'click');
			});


			BX.bind(input, 'click', BX.proxy(this._onCustomEntityInputClick, this));

			if (!this.bindDocument)
			{
				BX.bind(document, 'click', BX.proxy(this._onCustomEntityBlur, this));
				document.addEventListener('focus', BX.proxy(this._onDocumentFocus, this), true);
				this.bindDocument = true;
			}

			BX.bind(input, 'keydown', BX.proxy(this._onCustomEntityKeydown, this));
			BX.bind(field, 'click', BX.proxy(this._onCustomEntityFieldClick, this));


			BX.ready(BX.proxy(function() {
				BX.Filter.DestinationSelector.create(
					fieldData.NAME,
					{
						filterId: this.parent.getParam('FILTER_ID'),
						fieldId: fieldData.NAME
					}
				);
			}, this));

			return field;

		},

		createCustomEntity: function(fieldData)
		{
			var input, square;
			var field = {
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				deleteButton: true,
				valueDelete: true,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: {
					block: 'main-ui-control-entity',
					mix: 'main-ui-control',
					attrs: {
						'data-multiple': JSON.stringify(fieldData.MULTIPLE)
					},
					content: []
				}
			};

			if ('_label' in fieldData.VALUES && !!fieldData.VALUES._label)
			{
				if (fieldData.MULTIPLE)
				{
					var label = !!fieldData.VALUES._label ? fieldData.VALUES._label : [];

					if (BX.type.isPlainObject(label))
					{
						label = Object.keys(label).map(function(key) {
							return label[key];
						});
					}

					if (!BX.type.isArray(label))
					{
						label = [ label ];
					}

					var value = !!fieldData.VALUES._value ? fieldData.VALUES._value : [];
					if (BX.type.isPlainObject(value))
					{
						value = Object.keys(value).map(function(key) {
							return value[key];
						});
					}

					if (!BX.type.isArray(value))
					{
						value = [ value ];
					}

					label.forEach(function(currentLabel, index) {
						field.content.content.push({
							block: 'main-ui-square',
							tag: 'span',
							name: currentLabel,
							item: {_label: currentLabel, _value: value[index]}
						});
					});
				}
				else
				{
					field.content.content.push({
						block: 'main-ui-square',
						tag: 'span',
						name: '_label' in fieldData.VALUES ? fieldData.VALUES['_label'] : '',
						item: fieldData.VALUES
					});
				}
			}

			field.content.content.push(
				{
					block: 'main-ui-square-search',
					tag: 'span',
					content: {
						block: 'main-ui-control-string',
						name: fieldData.NAME + '_label',
						tabindex: fieldData.TABINDEX,
						type: 'text',
						placeholder: fieldData.PLACEHOLDER || ''
					}
				},
				{
					block: 'main-ui-control-string',
					name: fieldData.NAME,
					type: 'hidden',
					placeholder: fieldData.PLACEHOLDER || '',
					value: '_value' in fieldData.VALUES ? fieldData.VALUES['_value'] : '',
					tabindex: fieldData.TABINDEX
				}
			);


			field = BX.decl(field);
			input = BX.Filter.Utils.getBySelector(field, '.main-ui-control-string[type="text"]');
			BX.addClass(input, 'main-ui-square-search-item');

			BX.bind(input, 'focus', BX.proxy(this._onCustomEntityInputFocus, this));
			BX.bind(input, 'click', BX.proxy(this._onCustomEntityInputClick, this));

			if (!this.bindDocument)
			{
				BX.bind(document, 'click', BX.proxy(this._onCustomEntityBlur, this));
				document.addEventListener('focus', BX.proxy(this._onDocumentFocus, this), true);
				this.bindDocument = true;
			}

			BX.bind(input, 'keydown', BX.proxy(this._onCustomEntityKeydown, this));
			BX.bind(field, 'click', BX.proxy(this._onCustomEntityFieldClick, this));

			return field;
		},

		_onCustomEntityInputFocus: function(event)
		{
			BX.fireEvent(event.currentTarget, 'click');
		},

		_onCustomEntityInputClick: function(event)
		{
			var trustDate, notTrustDate, trustTime, notTrustTime;

			event.preventDefault();
			event.stopPropagation();

			if (event.isTrusted)
			{
				this.trustTimestamp = event.timeStamp;
				this.notTrustTimestamp = this.notTrustTimestamp || event.timeStamp;
			}
			else
			{
				this.notTrustTimestamp = event.timeStamp;
			}

			trustDate = new Date(this.trustTimestamp);
			notTrustDate = new Date(this.notTrustTimestamp);
			trustTime = trustDate.getMinutes() + ':' + trustDate.getSeconds();
			notTrustTime = notTrustDate.getMinutes() + ':' + notTrustDate.getSeconds();

			if (trustTime !== notTrustTime)
			{
				this._onCustomEntityFocus(event);
			}
		},

		_onDocumentFocus: function(event)
		{
			var CustomEntity = this.getCustomEntityInstance();
			var popupContainer = CustomEntity.getPopupContainer();
			var isOnInputField = CustomEntity.getLabelNode() === event.target;
			var isInsidePopup = !!popupContainer && popupContainer.contains(event.target);

			if (!isOnInputField && !isInsidePopup)
			{
				this._onCustomEntityBlur(event);
			}

		},

		_onCustomEntityKeydown: function(event)
		{
			var squares = BX.Filter.Utils.getByClass(event.target.parentNode.parentNode, this.parent.settings.classSquare, true);
			var square = null;

			if (squares.length)
			{
				square = squares[squares.length-1];
			}

			if (BX.Filter.Utils.isKey(event, 'backspace') && event.currentTarget.selectionStart === 0)
			{
				if (BX.type.isDomNode(square))
				{
					if (BX.hasClass(square, this.parent.settings.classSquareSelected))
					{
						BX.remove(square);
						var input = BX.Filter.Utils.getBySelector(event.target.parentNode.parentNode, 'input[type="hidden"]');
						input.value = '';
						BX.fireEvent(input, 'input');
					}
					else
					{
						BX.addClass(square, this.parent.settings.classSquareSelected)
					}
				}
			} else if (BX.type.isDomNode(square) && BX.hasClass(square, this.parent.settings.classSquareSelected)) {
				BX.removeClass(square, this.parent.settings.classSquareSelected)
			}
		},

		_onCustomEntityFieldClick: function(event)
		{
			var square;

			if (BX.hasClass(event.target, this.parent.settings.classSquareDelete))
			{
				square = BX.findParent(event.target, {className: this.parent.settings.classSquare}, true, false);

				if (BX.type.isDomNode(square))
				{
					var CustomEntity = this.getCustomEntityInstance();
					BX.onCustomEvent(window, 'BX.Main.Filter:customEntityRemove', [CustomEntity]);
					BX.remove(square);
				}
			}
			else
			{
				var input = BX.Filter.Utils.getBySelector(event.target, 'input[type="text"]');
				BX.fireEvent(input, 'focus');
			}
		},

		_onCustomEntityBlur: function(event)
		{
			var obResult = {
				stopBlur: false
			};
			BX.onCustomEvent(window, 'BX.Main.Filter:onGetStopBlur', [event, obResult]);

			if (
				typeof obResult.stopBlur == 'undefined'
				|| !obResult.stopBlur
			)
			{
				var CustomEntity = this.getCustomEntityInstance();

				BX.onCustomEvent(window, 'BX.Main.Filter:customEntityBlur', [CustomEntity]);
				BX.unbind(CustomEntity.getPopupContainer(), 'click', this._stopPropagation);
				BX.removeClass(CustomEntity.getField(), this.parent.settings.classFocus);
			}
		},

		_stopPropagation: function(event)
		{
			event.stopPropagation();
		},

		getCustomEntityInstance: function()
		{
			if (!(this.customEntityInstance instanceof BX.Main.ui.CustomEntity))
			{
				this.customEntityInstance = new BX.Main.ui.CustomEntity();
			}

			return this.customEntityInstance;
		},


		_onCustomEntityFocus: function(event)
		{
			var field = BX.findParent(event.currentTarget, {className: 'main-ui-control-entity' }, true, false);
			var CustomEntity = this.getCustomEntityInstance();

			event.stopPropagation();

			CustomEntity.setField(field);

			BX.onCustomEvent(window, 'BX.Main.Filter:customEntityFocus', [CustomEntity]);

			var popupContainer = CustomEntity.getPopupContainer();
			if(BX.type.isElementNode(popupContainer))
			{
				BX.bind(popupContainer, 'click', this._stopPropagation);
			}

			BX.addClass(field, this.parent.settings.classFocus);
		},
		createCustom: function(fieldData)
		{
			var control, strReplace, field;
			var cls = [];
			fieldData.VALUE = BX.util.htmlspecialcharsback(fieldData.VALUE);

			cls.push('main-ui-control');
			cls.push('main-ui-custom-style');

			field = BX.decl({
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				deleteButton: true,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: {
					block: 'main-ui-custom',
					mix: cls,
					attrs: {
						'data-name': fieldData.NAME
					},
					content: ''
				}
			});

			strReplace = 'name="'+fieldData.NAME+'" value="' + ('_VALUE' in fieldData ? fieldData._VALUE : '') + '"';
			control = BX.Filter.Utils.getByClass(field, 'main-ui-custom');

			try {
				fieldData.VALUE = fieldData.VALUE.replace('name="'+fieldData.NAME+'"', strReplace);
			} catch (err) {}

			BX.html(control, fieldData.VALUE);

			return field;
		},

		createSelect: function(fieldData)
		{
			return BX.decl({
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				deleteButton: true,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: {
					block: this.parent.settings.classSelect,
					name: fieldData.NAME,
					items: fieldData.ITEMS,
					value: 'VALUE' in fieldData ? fieldData.VALUE : fieldData.ITEMS[0],
					params: fieldData.PARAMS,
					tabindex: fieldData.TABINDEX,
					valueDelete: false
				}
			});
		},

		createMultiSelect: function(fieldData)
		{
			return BX.decl({
				block: 'main-ui-control-field',
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
				name: fieldData.NAME,
				type: fieldData.TYPE,
				deleteButton: true,
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				content: {
					block: 'main-ui-multi-select',
					name: fieldData.NAME,
					tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
					placeholder: !this.parent.getParam('ENABLE_LABEL') && 'PLACEHOLDER' in fieldData ? fieldData.PLACEHOLDER : '',
					items: 'ITEMS' in fieldData ? fieldData.ITEMS : [],
					value: 'VALUE' in fieldData ? fieldData.VALUE : [],
					params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
					valueDelete: true
				}
			});
		},


		createCustomDate: function(fieldData)
		{
			var group = {
				block: 'main-ui-control-field-group',
				type: fieldData.TYPE,
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel, 'main-ui-filter-date-group'] : ['main-ui-filter-date-group'],
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				name: 'NAME' in fieldData ? fieldData.NAME : '',
				deleteButton: true,
				content: []
			};

			if (BX.type.isPlainObject(fieldData.VALUE.days))
			{
				fieldData.VALUE.days = Object.keys(fieldData.VALUE.days).map(function(index) {
					return fieldData.VALUE.days[index];
				});
			}

			var daysValue = fieldData.DAYS.filter(function(item) {
				return fieldData.VALUE.days.some(function(value) {
					return value === item.VALUE;
				});
			});

			var days = {
				block: 'main-ui-control-field',
				mix: ['main-ui-control-custom-date'],
				placeholder: fieldData.DAYS_PLACEHOLDER,
				dragButton: false,
				content: {
					block: 'main-ui-multi-select',
					name: fieldData.NAME + "_days",
					tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
					items: fieldData.DAYS,
					value: daysValue,
					params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
					valueDelete: true,
					attrs: {"data-placeholder": fieldData.DAYS_PLACEHOLDER}
				}
			};


			if (BX.type.isPlainObject(fieldData.VALUE.months))
			{
				fieldData.VALUE.months = Object.keys(fieldData.VALUE.months).map(function(index) {
					return fieldData.VALUE.months[index];
				});
			}

			var monthsValue = fieldData.MONTHS.filter(function(item) {
				return fieldData.VALUE.months.some(function(value) {
					return value === item.VALUE;
				});
			});

			var months = {
				block: 'main-ui-control-field',
				mix: ['main-ui-control-custom-date'],
				dragButton: false,
				content: {
					block: 'main-ui-multi-select',
					name: fieldData.NAME + "_months",
					tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
					items: fieldData.MONTHS,
					value: monthsValue,
					params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
					valueDelete: true,
					attrs: {"data-placeholder": fieldData.MONTHS_PLACEHOLDER}
				}
			};


			if (BX.type.isPlainObject(fieldData.VALUE.years))
			{
				fieldData.VALUE.years = Object.keys(fieldData.VALUE.years).map(function(index) {
					return fieldData.VALUE.years[index];
				});
			}

			var yearsValue = fieldData.YEARS.filter(function(item) {
				return fieldData.VALUE.years.some(function(value) {
					return value === item.VALUE;
				});
			});

			var years = {
				block: 'main-ui-control-field',
				mix: ['main-ui-control-custom-date'],
				dragButton: false,
				content: {
					block: 'main-ui-multi-select',
					name: fieldData.NAME + "_years",
					tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
					items: fieldData.YEARS,
					value: yearsValue,
					params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
					valueDelete: true,
					attrs: {"data-placeholder": fieldData.YEARS_PLACEHOLDER}
				}
			};

			group.content.push(days);
			group.content.push(months);
			group.content.push(years);

			return BX.decl(group);
		},


		_onDateTypeChange: function(instance, data)
		{
			if (this.parent.getPopup().contentContainer.contains(instance.node))
			{
				var fieldData = {};
				var dateGroup = null;
				var group, params, label, fullName, controls, presetData, presetField, index;

				if (BX.type.isPlainObject(data) && 'VALUE' in data)
				{
					fullName = BX.data(instance.getNode(), 'name');
					params = instance.getParams();

					if (!BX.type.isPlainObject(params) && (fullName.indexOf('_datesel') !== -1 || fullName.indexOf('_numsel') !== -1))
					{
						group = instance.getNode().parentNode.parentNode;
						fieldData.TABINDEX = instance.getInput().getAttribute('tabindex');
						fieldData.SUB_TYPES = instance.getItems();
						fieldData.SUB_TYPE = data;
						fieldData.NAME = BX.data(group, 'name');
						fieldData.TYPE = BX.data(group, 'type');

						presetData = this.parent.getPreset().getCurrentPresetData();

						if ('FIELDS' in presetData && presetData.FIELDS.length)
						{
							presetField = presetData.FIELDS.filter(function(current) {
								return current.NAME === fieldData.NAME;
							}, this);

							if (presetField.length)
							{
								presetField = presetField[0];

								if (fullName.indexOf('_datesel') !== -1)
								{
									fieldData.MONTHS = presetField.MONTHS;
									fieldData.MONTH = presetField.MONTH;
									fieldData.YEARS = presetField.YEARS;
									fieldData.YEAR = presetField.YEAR;
									fieldData.QUARTERS = presetField.QUARTERS;
									fieldData.QUARTER = presetField.QUARTER;
									fieldData.ENABLE_TIME = presetField.ENABLE_TIME;
									fieldData.YEARS_SWITCHER = presetField.YEARS_SWITCHER;
								}

								fieldData.VALUES = presetField.VALUES;
							}
						}

						if (this.parent.getParam('ENABLE_LABEL'))
						{
							label = BX.Filter.Utils.getByClass(group, this.parent.settings.classFieldLabel);
							fieldData.LABEL = label.innerText;
						}

						if (fullName.indexOf('_datesel') !== -1)
						{
							dateGroup = this.createDate(fieldData);
						}
						else
						{
							dateGroup = this.createNumber(fieldData);
						}


						if (BX.type.isArray(this.parent.fieldsList))
						{
							index = this.parent.fieldsList.indexOf(group);

							if (index !== -1)
							{
								this.parent.fieldsList[index] = dateGroup;
								this.parent.registerDragItem(dateGroup);
							}
						}

						this.parent.unregisterDragItem(group);

						controls = BX.Filter.Utils.getByClass(dateGroup, this.parent.settings.classField, true);

						if (BX.type.isArray(controls) && controls.length)
						{
							controls.forEach(function(control) {
								control.FieldController = new BX.Filter.FieldController(control, this.parent);
							}, this);
						}

						BX.insertAfter(dateGroup, group);
						BX.remove(group);
					}
				}
			}
		},

		createNumber: function(fieldData)
		{
			var group, single, placeholder, from, line, to;
			var subTypes = this.parent.numberTypes;
			var subType = subTypes.SINGLE;

			if ('SUB_TYPE' in fieldData && BX.type.isPlainObject(fieldData.SUB_TYPE))
			{
				subType = fieldData.SUB_TYPE.VALUE;
				placeholder = 'PLACEHOLDER' in fieldData.SUB_TYPE ? fieldData.SUB_TYPE.PLACEHOLDER : '';
			}

			fieldData.NAME = fieldData.NAME.replace('_numsel', '');

			group = {
				block: 'number-group',
				type: fieldData.TYPE,
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel, 'main-ui-filter-number-group'] : ['main-ui-filter-number-group'],
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				value: 'SUB_TYPE' in fieldData ? fieldData.SUB_TYPE : {},
				items: 'SUB_TYPES' in fieldData ? fieldData.SUB_TYPES : [],
				name: 'NAME' in fieldData ? fieldData.NAME : '',
				deleteButton: true,
				content: []
			};

			if (subType !== subTypes.LESS)
			{
				from = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-number',
						mix: ['filter-type-single'],
						calendarButton: true,
						valueDelete: true,
						placeholder: placeholder,
						name: 'NAME' in fieldData ? fieldData.NAME + '_from' : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._from : ''
					}
				};

				group.content.push(from);
			}

			if (subType === subTypes.RANGE)
			{
				line = {
					block: 'main-ui-filter-field-line',
					content: {
						block: 'main-ui-filter-field-line-item',
						tag: 'span'
					}
				};

				group.content.push(line);
			}

			if (subType === subTypes.RANGE || subType === subTypes.LESS)
			{
				to = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-number',
						calendarButton: true,
						valueDelete: true,
						name: 'NAME' in fieldData ? (fieldData.NAME + '_to') : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._to : ''
					}
				};
			}

			group.content.push(to);

			return BX.decl(group);
		},

		createDate: function(fieldData)
		{
			var group, dateFrom, dateTo, singleDate, line, placeholder, select, quarter, month;
			var subTypes = this.parent.dateTypes;
			var additionalTypes = this.parent.additionalDateTypes;
			var subType = subTypes.NONE;

			if ('SUB_TYPE' in fieldData && BX.type.isPlainObject(fieldData.SUB_TYPE))
			{
				subType = fieldData.SUB_TYPE.VALUE;
				placeholder = 'PLACEHOLDER' in fieldData.SUB_TYPE ? fieldData.SUB_TYPE.PLACEHOLDER : '';
			}

			fieldData.NAME = fieldData.NAME.replace('_datesel', '');

			if ('VALUES' in fieldData && BX.type.isPlainObject(fieldData.VALUES))
			{
				var fieldValuesKeys = Object.keys(fieldData.VALUES);

				fieldValuesKeys.forEach(function(curr) {
					if (!fieldData.VALUES[curr])
					{
						fieldData.VALUES[curr] = "";
					}
				});
			}

			group = {
				block: 'date-group',
				type: fieldData.TYPE,
				mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel, 'main-ui-filter-date-group'] : ['main-ui-filter-date-group'],
				label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
				dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
				deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				value: 'SUB_TYPE' in fieldData ? fieldData.SUB_TYPE : {},
				items: 'SUB_TYPES' in fieldData ? fieldData.SUB_TYPES : [],
				name: 'NAME' in fieldData ? fieldData.NAME : '',
				enableTime: "ENABLE_TIME" in fieldData ? fieldData.ENABLE_TIME : false,
				deleteButton: true,
				content: []
			};

			fieldData.NAME = fieldData.NAME.replace('_datesel', '');

			if (subType === subTypes.EXACT)
			{
				singleDate = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-date',
						mix: ['filter-type-single'],
						calendarButton: true,
						valueDelete: true,
						placeholder: placeholder,
						name: 'NAME' in fieldData ? fieldData.NAME + '_from' : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._from : '',
						enableTime: fieldData.ENABLE_TIME
					}
				};

				group.content.push(singleDate);
			}

			if (subType === subTypes.NEXT_DAYS ||
				subType === subTypes.PREV_DAYS ||
				subType === additionalTypes.PREV_DAY ||
				subType === additionalTypes.NEXT_DAY ||
				subType === additionalTypes.MORE_THAN_DAYS_AGO ||
				subType === additionalTypes.AFTER_DAYS)
			{
				singleDate = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-number',
						mix: ['filter-type-single'],
						calendarButton: true,
						valueDelete: true,
						placeholder: placeholder,
						name: 'NAME' in fieldData ? fieldData.NAME + '_days' : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._days : ''
					}
				};

				group.content.push(singleDate);
			}


			if (subType === subTypes.RANGE)
			{
				var rangeGroup = {
					block: "main-ui-filter-range-group",
					content: []
				};

				dateFrom = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-date',
						calendarButton: true,
						valueDelete: true,
						name: 'NAME' in fieldData ? (fieldData.NAME + '_from') : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._from : '',
						placeholder: placeholder,
						enableTime: fieldData.ENABLE_TIME
					}
				};

				line = {
					block: 'main-ui-filter-field-line',
					content: {
						block: 'main-ui-filter-field-line-item',
						tag: 'span'
					}
				};

				dateTo = {
					block: 'main-ui-control-field',
					type: fieldData.TYPE,
					dragButton: false,
					content: {
						block: 'main-ui-date',
						calendarButton: true,
						valueDelete: true,
						name: 'NAME' in fieldData ? (fieldData.NAME + '_to') : '',
						tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
						value: 'VALUES' in fieldData ? fieldData.VALUES._to : '',
						placeholder: placeholder,
						enableTime: fieldData.ENABLE_TIME
					}
				};

				rangeGroup.content.push(dateFrom);
				rangeGroup.content.push(line);
				rangeGroup.content.push(dateTo);
				group.content.push(rangeGroup);
			}

			if (subType === subTypes.MONTH)
			{
				if ('_month' in fieldData.VALUES && fieldData.VALUES._month)
				{
					fieldData.MONTH = fieldData.MONTHS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._month;
					});

					fieldData.MONTH = fieldData.MONTH.length ? fieldData.MONTH[0] : null;
				}

				if (!fieldData.MONTH)
				{
					fieldData.MONTH = fieldData.MONTHS[0];
				}

				month = {
					block: 'main-ui-control-field',
					dragButton: false,
					content: {
						block: 'main-ui-select',
						tabindex: 'tabindex' in fieldData ? fieldData.tabindex : '',
						value: fieldData.MONTH,
						items: fieldData.MONTHS,
						name: fieldData.NAME + '_month',
						valueDelete: false
					}
				};


				if ('_year' in fieldData.VALUES && fieldData.VALUES._year)
				{
					fieldData.YEAR = fieldData.YEARS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._year;
					});

					fieldData.YEAR = fieldData.YEAR.length ? fieldData.YEAR[0] : null;
				}

				if (!fieldData.YEAR)
				{
					fieldData.YEAR = fieldData.YEARS[0];
				}

				select = {
					block: 'main-ui-control-field',
					dragButton: false,
					content: {
						block: 'main-ui-select',
						tabindex: 'tabindex' in fieldData ? fieldData.tabindex : '',
						value: fieldData.YEAR,
						items: fieldData.YEARS,
						name: fieldData.NAME + '_year',
						valueDelete: false
					}
				};

				group.content.push(month);
				group.content.push(select);
			}


			if (subType === subTypes.QUARTER)
			{
				if ('_year' in fieldData.VALUES && fieldData.VALUES._year)
				{
					fieldData.YEAR = fieldData.YEARS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._year;
					});

					fieldData.YEAR = fieldData.YEAR.length ? fieldData.YEAR[0] : null;
				}

				if (!fieldData.YEAR)
				{
					fieldData.YEAR = fieldData.YEARS[0];
				}

				select = {
					block: 'main-ui-control-field',
					dragButton: false,
					content: {
						block: 'main-ui-select',
						tabindex: 'tabindex' in fieldData ? fieldData.tabindex : '',
						value: fieldData.YEAR,
						items: fieldData.YEARS,
						name: fieldData.NAME + '_year',
						valueDelete: false
					}
				};


				if ('_quarter' in fieldData.VALUES && fieldData.VALUES._quarter)
				{
					fieldData.QUARTER = fieldData.QUARTERS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._quarter;
					});

					fieldData.QUARTER = fieldData.QUARTER.length ? fieldData.QUARTER[0] : null;
				}

				if (!fieldData.QUARTER)
				{
					fieldData.QUARTER = fieldData.QUARTERS[0];
				}

				quarter = {
					block: 'main-ui-control-field',
					dragButton: false,
					content: {
						block: 'main-ui-select',
						tabindex: 'tabindex' in fieldData ? fieldData.tabindex : '',
						value: fieldData.QUARTER,
						items: fieldData.QUARTERS,
						name: fieldData.NAME + '_quarter',
						params: fieldData.PARAMS,
						valueDelete: false
					}
				};

				group.content.push(quarter);
				group.content.push(select);
			}


			if (subType === subTypes.YEAR)
			{
				if ('_year' in fieldData.VALUES && fieldData.VALUES._year)
				{
					fieldData.YEAR = fieldData.YEARS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._year;
					});

					fieldData.YEAR = fieldData.YEAR.length ? fieldData.YEAR[0] : null;
				}

				if (!fieldData.YEAR)
				{
					fieldData.YEAR = fieldData.YEARS[0];
				}

				select = {
					block: 'main-ui-control-field',
					dragButton: false,
					content: {
						block: 'main-ui-select',
						tabindex: 'tabindex' in fieldData ? fieldData.tabindex : '',
						value: fieldData.YEAR,
						items: fieldData.YEARS,
						name: fieldData.NAME + '_year',
						valueDelete: false
					}
				};

				group.content.push(select);
			}

			if (subType === "CUSTOM_DATE")
			{
				var customDateDecl = fieldData.SUB_TYPES.filter(function(item) {
					return item.VALUE === "CUSTOM_DATE";
				});

				if (customDateDecl[0])
				{
					customDateDecl = BX.clone(customDateDecl[0].DECL);

					if (BX.type.isArray(fieldData.VALUES._days))
					{
						customDateDecl.VALUE.days = fieldData.VALUES._days;
					}

					if (BX.type.isArray(fieldData.VALUES._month))
					{
						customDateDecl.VALUE.months = fieldData.VALUES._month;
					}

					if (BX.type.isArray(fieldData.VALUES._year))
					{
						customDateDecl.VALUE.years = fieldData.VALUES._year;
					}

					var customDateField = this.createCustomDate(customDateDecl);

					customDateField.classList.remove("main-ui-filter-wield-with-label");
					var removeButton = customDateField.querySelector(".main-ui-item-icon-container");

					if (removeButton)
					{
						BX.remove(removeButton);
					}

					var dragButton = customDateField.querySelector(".main-ui-filter-icon-grab");

					if (dragButton)
					{
						BX.remove(dragButton);
					}

					group.content.push(customDateField);
					group.mix.push("main-ui-filter-custom-date-group");
				}
			}

			if (subType !== subTypes.NONE && subType !== additionalTypes.CUSTOM_DATE && fieldData["YEARS_SWITCHER"])
			{
				if ('_allow_year' in fieldData.VALUES && fieldData.VALUES._allow_year)
				{
					var allowYear = fieldData.YEARS_SWITCHER.ITEMS.filter(function(curr) {
						return curr.VALUE === fieldData.VALUES._allow_year;
					});
					allowYear = allowYear.length ? allowYear[0] : null;
					if (allowYear)
					{
						fieldData.YEARS_SWITCHER.VALUE = allowYear;
					}
				}
				var yearsSwitcher = this.createSelect(fieldData["YEARS_SWITCHER"]);

				yearsSwitcher.classList.add("main-ui-filter-year-switcher");
				yearsSwitcher.classList.add("main-ui-filter-with-padding");
				yearsSwitcher.classList.remove("main-ui-filter-wield-with-label");
				removeButton = yearsSwitcher.querySelector(".main-ui-item-icon-container");

				if (removeButton)
				{
					BX.remove(removeButton);
				}

				dragButton = yearsSwitcher.querySelector(".main-ui-filter-icon-grab");

				if (dragButton)
				{
					BX.remove(dragButton);
				}

				if (group.content[group.content.length-1])
				{
					if (BX.type.isPlainObject(group.content[group.content.length-1]))
					{
						if (!BX.type.isArray(group.content[group.content.length-1].mix))
						{
							group.content[group.content.length-1].mix = [];
						}

						group.content[group.content.length-1].mix.push("main-ui-filter-remove-margin-right")
					}

					if (BX.type.isDomNode(group.content[group.content.length-1]))
					{
						group.content[group.content.length-1].classList.add("main-ui-filter-remove-margin-right");
					}
				}

				requestAnimationFrame(function() {
					yearsSwitcher.previousElementSibling.classList.add("main-ui-filter-remove-margin-right");
				});

				group.content.push(yearsSwitcher);
				group.mix.push("main-ui-filter-date-with-years-switcher");
			}

			var renderedField = BX.decl(group);
			var inputFields = [].slice.call(renderedField.querySelectorAll(".main-ui-date-input"));

			if (inputFields.length > 0)
			{
				inputFields.forEach(function(input) {
					var handler = BX.debounce(this.onDateChange, 500, this);
					input.addEventListener('change', handler);
					input.addEventListener('input', handler);
					var clearButton = input.parentNode.querySelector(".main-ui-control-value-delete");

					if (clearButton)
					{
						clearButton.addEventListener('click', function() {
							setTimeout(function() {
								this.onDateChange({target: input});
							}.bind(this));
						}.bind(this));
					}
				}, this);
			}

			return renderedField;
		},

		onDateChange: function(event)
		{
			if (values.get(event.target) === event.target.value)
			{
				return;
			}

			values.set(event.target, event.target.value);

			if (event.target.value === "")
			{
				this.hideDateError(event.target);
				return;
			}

			BX.ajax
				.runComponentAction("bitrix:main.ui.filter", "checkDateFormat", {
					mode: 'ajax',
					data: {
						value: event.target.value
					}
				})
				.then(function(result) {
					if (!result.data.result)
					{
						this.showDateError(event.target);
						return;
					}

					this.hideDateError(event.target);
				}.bind(this));
		},

		showDateError: function(inputField)
		{
			inputField.style.borderColor = "#FF5752";

			if (errorMessages.has(inputField))
			{
				BX.remove(errorMessages.get(inputField));
			}

			var dateErrorMessage = BX.create("div", {
				attrs: {
					"class": "main-ui-filter-error-message",
					title: this.parent.params["MAIN_UI_FILTER__DATE_ERROR_TITLE"],
				},
				text: this.parent.params["MAIN_UI_FILTER__DATE_ERROR_LABEL"] + " " + BX.message("FORMAT_DATE"),
			});

			errorMessages.set(inputField, dateErrorMessage);

			BX.insertAfter(dateErrorMessage, inputField);

			inputField.dataset.isValid = false;
		},

		hideDateError: function(inputField)
		{
			inputField.style.borderColor = null;

			if (errorMessages.has(inputField))
			{
				BX.remove(errorMessages.get(inputField));
			}

			inputField.dataset.isValid = true;
		},
	};
})();