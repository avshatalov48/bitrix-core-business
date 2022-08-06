/* eslint-disable no-underscore-dangle */
/* eslint-disable class-methods-use-this */
import {Dom, Event, Runtime, Type, Text, Loc, Tag} from 'main.core';
import createDateInputDecl from './fields/create-date-input-decl';
import createNumberInputDecl from './fields/create-number-input-decl';
import createLineDecl from './fields/create-line-decl';
import createSelectDecl from './fields/create-select-decl';
import {Field} from './field/field';
import {AdditionalFilter} from './additional-filter';

const errorMessages = new WeakMap();
const errorMessagesTypes = new WeakMap();
const values = new WeakMap();

export class Fields
{
	constructor(parent)
	{
		this.parent = null;
		this.init(parent);
	}

	init(parent)
	{
		this.parent = parent;
		BX.addCustomEvent(window, 'UI::Select::change', this._onDateTypeChange.bind(this));
	}

	deleteField(node)
	{
		Dom.remove(node);
	}

	isFieldDelete(node)
	{
		return Dom.hasClass(node, this.parent.settings.classFieldDelete);
	}

	isFieldValueDelete(node)
	{
		return (
			Dom.hasClass(node, this.parent.settings.classValueDelete)
			|| Dom.hasClass(node.parentNode, this.parent.settings.classValueDelete)
		);
	}

	isDragButton(node)
	{
		return node && Dom.hasClass(node, this.parent.settings.classPresetDragButton);
	}

	/**
	 * Clears values of filter field node
	 * @param {HTMLElement} field
	 */
	clearFieldValue(field)
	{
		if (field)
		{
			const controls = [...field.querySelectorAll('.main-ui-control')];
			const squares = [...field.querySelectorAll('.main-ui-square')];

			squares.forEach((square) => Dom.remove(square));
			controls.forEach((control) => {
				if (Reflect.has(control, 'value'))
				{
					control.value = '';
				}
			});
		}
	}

	getField(node)
	{
		if (Type.isDomNode(node))
		{
			return node.closest('.main-ui-control-field, .main-ui-control-field-group');
		}

		return null;
	}

	render(template, data)
	{
		if (Type.isString(template) && Type.isPlainObject(data))
		{
			const html = Object.entries(data).reduce((acc, [key, value]) => {
				return acc.replace(new RegExp(`{{${key}}}`, 'g'), value);
			}, template);

			const wrapped = Dom.create('div', {html});

			const fieldGroup = wrapped.querySelector('.main-ui-control-field-group');
			if (fieldGroup)
			{
				return fieldGroup;
			}

			const field = wrapped.querySelector('.main-ui-control-field');
			if (field)
			{
				return field;
			}

			const fieldLine = wrapped.querySelector('.main-ui-filter-field-line');
			if (fieldLine)
			{
				return fieldLine;
			}
		}

		return null;
	}

	createInputText(fieldData)
	{
		const field = {
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			deleteButton: true,
			valueDelete: true,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			content: [
				{
					block: 'main-ui-control-string',
					name: fieldData.NAME,
					placeholder: fieldData.PLACEHOLDER || '',
					value: (Type.isString(fieldData.VALUE)
							|| Type.isNumber(fieldData.VALUE) ? fieldData.VALUE : ''),
					tabindex: fieldData.TABINDEX,
				},
			],
		};

		const renderedField = BX.decl(field);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: renderedField,
				}),
			},
		);

		return renderedField;
	}

	createTextarea(fieldData)
	{
		const field = BX.decl({
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			deleteButton: true,
			valueDelete: true,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			content: [
				{
					block: 'main-ui-control-textarea',
					name: fieldData.NAME,
					placeholder: fieldData.PLACEHOLDER || '',
					value: (Type.isString(fieldData.VALUE)
					|| Type.isNumber(fieldData.VALUE) ? fieldData.VALUE : ''),
					tabindex: fieldData.TABINDEX,
				},
			],
		});

		const textarea = field.querySelector('textarea');
		const onChange = () => {
			Dom.style(textarea, 'height', '1px');
			Dom.style(textarea, 'height', `${textarea.scrollHeight}px`);
		};

		Event.bind(textarea, 'input', onChange);
		Event.bind(textarea, 'change', onChange);
		Event.bind(textarea, 'keyup', onChange);
		Event.bind(textarea, 'cut', onChange);
		Event.bind(textarea, 'paste', onChange);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	createCustomEntityFieldLayout(fieldData)
	{
		let field = {
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			deleteButton: true,
			valueDelete: true,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			content: {
				block: 'main-ui-control-entity',
				mix: 'main-ui-control',
				attrs: {
					'data-multiple': JSON.stringify(fieldData.MULTIPLE),
				},
				content: [],
			},
		};

		if ('_label' in fieldData.VALUES && !!fieldData.VALUES._label)
		{
			if (fieldData.MULTIPLE)
			{
				let label = fieldData.VALUES._label ? fieldData.VALUES._label : [];

				if (Type.isPlainObject(label))
				{
					label = Object.keys(label).map((key) => {
						return label[key];
					});
				}

				if (!Type.isArray(label))
				{
					label = [label];
				}

				let value = fieldData.VALUES._value ? fieldData.VALUES._value : [];
				if (Type.isPlainObject(value))
				{
					value = Object.keys(value).map((key) => {
						return value[key];
					});
				}

				if (!Type.isArray(value))
				{
					value = [value];
				}

				label.forEach((currentLabel, index) => {
					field.content.content.push({
						block: 'main-ui-square',
						tag: 'span',
						name: currentLabel,
						item: {_label: currentLabel, _value: value[index]},
					});
				});
			}
			else
			{
				field.content.content.push({
					block: 'main-ui-square',
					tag: 'span',
					name: '_label' in fieldData.VALUES ? fieldData.VALUES._label : '',
					item: fieldData.VALUES,
				});
			}
		}

		field.content.content.push(
			{
				block: 'main-ui-square-search',
				tag: 'span',
				content: {
					block: 'main-ui-control-string',
					name: `${fieldData.NAME}_label`,
					tabindex: fieldData.TABINDEX,
					type: 'text',
					placeholder: fieldData.PLACEHOLDER || '',
				},
			},
			{
				block: 'main-ui-control-string',
				name: fieldData.NAME,
				type: 'hidden',
				placeholder: fieldData.PLACEHOLDER || '',
				value: '_value' in fieldData.VALUES ? fieldData.VALUES._value : '',
				tabindex: fieldData.TABINDEX,
			},
		);

		field = BX.decl(field);

		const input = BX.Filter.Utils.getBySelector(field, '.main-ui-control-string[type="text"]');
		BX.addClass(input, 'main-ui-square-search-item');
		input.autocomplete = 'off';

		Event.bind(input, 'focus', BX.proxy(this._onCustomEntityInputFocus, this));
		Event.bind(input, 'click', BX.proxy(this._onCustomEntityInputClick, this));

		if (!this.bindDocument)
		{
			Event.bind(document, 'click', BX.proxy(this._onCustomEntityBlur, this));
			document.addEventListener('focus', BX.proxy(this._onDocumentFocus, this), true);
			this.bindDocument = true;
		}

		Event.bind(input, 'keydown', BX.proxy(this._onCustomEntityKeydown, this));
		Event.bind(field, 'click', BX.proxy(this._onCustomEntityFieldClick, this));

		return field;
	}

	createDestSelector(fieldData)
	{
		const field = this.createCustomEntityFieldLayout(fieldData);

		BX.ready(BX.proxy(function() {
			BX.Filter.DestinationSelector.create(
				fieldData.NAME,
				{
					filterId: this.parent.getParam('FILTER_ID'),
					fieldId: fieldData.NAME,
				},
			);
		}, this));

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	createEntitySelector(fieldData)
	{
		const field = this.createCustomEntityFieldLayout(fieldData);

		BX.Filter.EntitySelector.create(
			fieldData.NAME,
			{
				filter: this.parent,
				isMultiple: fieldData.MULTIPLE,
				addEntityIdToResult: fieldData.ADD_ENTITY_ID_TO_RESULT,
				showDialogOnEmptyInput: fieldData.SHOW_DIALOG_ON_EMPTY_INPUT,
				dialogOptions: fieldData.DIALOG_OPTIONS
			},
		);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	createCustomEntity(fieldData)
	{
		const field = this.createCustomEntityFieldLayout(fieldData);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	_onCustomEntityInputFocus(event)
	{
		BX.fireEvent(event.currentTarget, 'click');
	}

	_onCustomEntityInputClick(event)
	{
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

		const trustDate = new Date(this.trustTimestamp);
		const notTrustDate = new Date(this.notTrustTimestamp);
		const trustTime = `${trustDate.getMinutes()}:${trustDate.getSeconds()}`;
		const notTrustTime = `${notTrustDate.getMinutes()}:${notTrustDate.getSeconds()}`;

		if (trustTime !== notTrustTime)
		{
			this._onCustomEntityFocus(event);
		}
	}

	_onDocumentFocus(event)
	{
		const CustomEntity = this.getCustomEntityInstance();
		const popupContainer = CustomEntity.getPopupContainer();
		const isOnInputField = CustomEntity.getLabelNode() === event.target;
		const isInsidePopup = !!popupContainer && popupContainer.contains(event.target);

		if (!isOnInputField && !isInsidePopup)
		{
			this._onCustomEntityBlur(event);
		}
	}

	_onCustomEntityKeydown(event)
	{
		const {target, currentTarget} = event;
		const {parentNode} = target.parentNode;

		const squares = parentNode.querySelectorAll('.main-ui-square');
		const square = squares[squares.length - 1];

		if (!Type.isDomNode(square))
		{
			return;
		}

		if (
			BX.Filter.Utils.isKey(event, 'backspace')
			&& currentTarget.selectionStart === 0
		)
		{
			if (Dom.hasClass(square, 'main-ui-square-selected'))
			{
				const input = parentNode.querySelector('input[type="hidden"]');

				if (Type.isDomNode(input))
				{
					input.value = '';
					BX.fireEvent(input, 'input');
				}

				Dom.remove(square);
				return;
			}

			Dom.addClass(square, 'main-ui-square-selected');
			return;
		}

		Dom.removeClass(square, 'main-ui-square-selected');
	}

	_onCustomEntityFieldClick({target})
	{
		if (Dom.hasClass(target, 'main-ui-square-delete'))
		{
			const square = target.closest('.main-ui-square');

			if (Type.isDomNode(square))
			{
				const CustomEntity = this.getCustomEntityInstance();
				BX.onCustomEvent(window, 'BX.Main.Filter:customEntityRemove', [CustomEntity]);
				Dom.remove(square);
			}

			return;
		}

		const input = target.querySelector('input[type="text"]');

		if (Type.isDomNode(input))
		{
			BX.fireEvent(input, 'focus');
		}
	}

	_onCustomEntityBlur(event)
	{
		const eventData = {
			stopBlur: false,
		};

		BX.onCustomEvent(window, 'BX.Main.Filter:onGetStopBlur', [event, eventData]);

		if (
			typeof eventData.stopBlur === 'undefined'
			|| !eventData.stopBlur
		)
		{
			const CustomEntity = this.getCustomEntityInstance();
			BX.onCustomEvent(window, 'BX.Main.Filter:customEntityBlur', [CustomEntity]);

			Event.unbind(CustomEntity.getPopupContainer(), 'click', this._stopPropagation);
			Dom.removeClass(CustomEntity.getField(), 'main-ui-focus');
		}
	}

	_stopPropagation(event)
	{
		event.stopPropagation();
	}

	getCustomEntityInstance()
	{
		if (!(this.customEntityInstance instanceof BX.Main.ui.CustomEntity))
		{
			this.customEntityInstance = new BX.Main.ui.CustomEntity();
		}

		return this.customEntityInstance;
	}

	_onCustomEntityFocus(event)
	{
		event.stopPropagation();

		const {currentTarget} = event;
		const field = currentTarget.closest('.main-ui-control-entity');

		const CustomEntity = this.getCustomEntityInstance();
		CustomEntity.setField(field);
		BX.onCustomEvent('BX.Main.Filter:customEntityFocus', [CustomEntity]);

		const popupContainer = CustomEntity.getPopupContainer();
		if (Type.isElementNode(popupContainer))
		{
			Event.bind(popupContainer, 'click', this._stopPropagation);
		}

		Dom.addClass(field, 'main-ui-focus');
	}

	createCustom(fieldData)
	{
		const field = BX.decl({
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			deleteButton: true,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			content: {
				block: 'main-ui-custom',
				mix: [
					'main-ui-control',
					'main-ui-custom-style',
				],
				attrs: {
					'data-name': fieldData.NAME,
				},
				content: '',
			},
		});

		if (Type.isString(fieldData.VALUE))
		{
			const fieldValue = (() => {
				if (Reflect.has(fieldData, '_VALUE'))
				{
					return fieldData._VALUE;
				}

				return '';
			})();

			const html = Text
				.decode(fieldData.VALUE)
				.replace(
					`name="${fieldData.NAME}"`,
					`name="${fieldData.NAME}" value="${fieldValue}"`,
				);

			const control = field.querySelector('.main-ui-custom');
			Runtime.html(control, html);
		}

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	createSelect(fieldData)
	{
		const field = BX.decl({
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			deleteButton: true,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			content: {
				block: this.parent.settings.classSelect,
				name: fieldData.NAME,
				items: fieldData.ITEMS,
				value: 'VALUE' in fieldData ? fieldData.VALUE : fieldData.ITEMS[0],
				params: fieldData.PARAMS,
				tabindex: fieldData.TABINDEX,
				valueDelete: false,
			},
		});

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}

	createMultiSelect(fieldData)
	{
		const field = BX.decl({
			block: 'main-ui-control-field',
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel] : null,
			name: fieldData.NAME,
			type: fieldData.TYPE,
			deleteButton: true,
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
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
				valueDelete: true,
			},
		});

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}


	createCustomDate(fieldData)
	{
		const group = {
			block: 'main-ui-control-field-group',
			type: fieldData.TYPE,
			mix: this.parent.getParam('ENABLE_LABEL') ? [this.parent.settings.classFieldWithLabel, 'main-ui-filter-date-group'] : ['main-ui-filter-date-group'],
			label: this.parent.getParam('ENABLE_LABEL') ? fieldData.LABEL : '',
			icon: (this.parent.getParam('ENABLE_LABEL') && fieldData.ICON) ? fieldData.ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
			name: 'NAME' in fieldData ? fieldData.NAME : '',
			deleteButton: true,
			content: [],
		};

		if (Type.isPlainObject(fieldData.VALUE.days))
		{
			fieldData.VALUE.days = Object.keys(fieldData.VALUE.days).map((index) => {
				return fieldData.VALUE.days[index];
			});
		}

		const daysValue = fieldData.DAYS.filter((item) => {
			return fieldData.VALUE.days.some((value) => {
				return value === item.VALUE;
			});
		});

		const days = {
			block: 'main-ui-control-field',
			mix: ['main-ui-control-custom-date'],
			placeholder: fieldData.DAYS_PLACEHOLDER,
			dragButton: false,
			content: {
				block: 'main-ui-multi-select',
				name: `${fieldData.NAME}_days`,
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				items: fieldData.DAYS,
				value: daysValue,
				params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
				valueDelete: true,
				attrs: {'data-placeholder': fieldData.DAYS_PLACEHOLDER},
			},
		};


		if (Type.isPlainObject(fieldData.VALUE.months))
		{
			fieldData.VALUE.months = Object.keys(fieldData.VALUE.months).map((index) => {
				return fieldData.VALUE.months[index];
			});
		}

		const monthsValue = fieldData.MONTHS.filter((item) => {
			return fieldData.VALUE.months.some((value) => {
				return value === item.VALUE;
			});
		});

		const months = {
			block: 'main-ui-control-field',
			mix: ['main-ui-control-custom-date'],
			dragButton: false,
			content: {
				block: 'main-ui-multi-select',
				name: `${fieldData.NAME}_months`,
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				items: fieldData.MONTHS,
				value: monthsValue,
				params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
				valueDelete: true,
				attrs: {'data-placeholder': fieldData.MONTHS_PLACEHOLDER},
			},
		};


		if (Type.isPlainObject(fieldData.VALUE.years))
		{
			fieldData.VALUE.years = Object.keys(fieldData.VALUE.years).map((index) => {
				return fieldData.VALUE.years[index];
			});
		}

		const yearsValue = fieldData.YEARS.filter((item) => {
			return fieldData.VALUE.years.some((value) => {
				return value === item.VALUE;
			});
		});

		const years = {
			block: 'main-ui-control-field',
			mix: ['main-ui-control-custom-date'],
			dragButton: false,
			content: {
				block: 'main-ui-multi-select',
				name: `${fieldData.NAME}_years`,
				tabindex: 'TABINDEX' in fieldData ? fieldData.TABINDEX : '',
				items: fieldData.YEARS,
				value: yearsValue,
				params: 'PARAMS' in fieldData ? fieldData.PARAMS : {isMulti: true},
				valueDelete: true,
				attrs: {'data-placeholder': fieldData.YEARS_PLACEHOLDER},
			},
		};

		group.content.push(days);
		group.content.push(months);
		group.content.push(years);

		const field = BX.decl(group);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...fieldData},
					node: field,
				}),
			},
		);

		return field;
	}


	_onDateTypeChange(instance, data)
	{
		if (this.parent.getPopup().contentContainer.contains(instance.node))
		{
			const fieldData = {};
			let dateGroup = null;
			let label;
			let controls;
			let index;

			if (Type.isPlainObject(data) && Reflect.has(data, 'VALUE'))
			{
				const fieldNode = instance.getNode();
				const params = instance.getParams();
				const {name} = fieldNode.dataset;

				if (
					!Type.isPlainObject(params)
					&& (name.endsWith('_datesel') || name.endsWith('_numsel')))
				{
					const group = fieldNode.parentNode.parentNode;
					fieldData.TABINDEX = instance.getInput().getAttribute('tabindex');
					fieldData.SUB_TYPES = instance.getItems();
					fieldData.SUB_TYPE = data;
					fieldData.NAME = group.dataset.name;
					fieldData.TYPE = group.dataset.type;
					fieldData.VALUE_REQUIRED = group.dataset.valueRequired === 'true';

					const presetData = this.parent.getPreset().getCurrentPresetData();

					if (Type.isArray(presetData.FIELDS))
					{
						let presetField = presetData.FIELDS.find((current) => {
							return current.NAME === fieldData.NAME;
						});

						if (Type.isNil(presetField))
						{
							presetField = this.parent.params.FIELDS_STUBS.find((current) => {
								return current.TYPE === fieldData.TYPE;
							});
						}

						if (!Type.isNil(presetField))
						{
							if (name.endsWith('_datesel'))
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
							fieldData.REQUIRED = presetField.REQUIRED;
						}
					}

					if (this.parent.getParam('ENABLE_LABEL'))
					{
						label = group.querySelector('.main-ui-control-field-label');
						fieldData.LABEL = label.innerText;
					}

					if (name.endsWith('_datesel'))
					{
						dateGroup = this.createDate(fieldData);
					}
					else
					{
						dateGroup = this.createNumber(fieldData);
					}

					if (Type.isArray(this.parent.fieldsList))
					{
						index = this.parent.fieldsList.indexOf(group);

						if (index !== -1)
						{
							this.parent.fieldsList[index] = dateGroup;
							this.parent.registerDragItem(dateGroup);
						}
					}

					this.parent.unregisterDragItem(group);

					controls = [...dateGroup.querySelectorAll('.main-ui-control-field')];

					if (Type.isArray(controls) && controls.length)
					{
						controls.forEach((control) => {
							control.FieldController = new BX.Filter.FieldController(control, this.parent);
						});
					}

					if (this.parent.getParam('ENABLE_ADDITIONAL_FILTERS'))
					{
						const button = AdditionalFilter.getInstance().getAdditionalFilterButton({
							fieldId: fieldData.NAME,
							enabled: fieldData.ADDITIONAL_FILTER_ALLOWED,
						});
						Dom.append(button, dateGroup);
					}

					Dom.insertAfter(dateGroup, group);
					Dom.remove(group);
				}
			}
		}
	}

	createNumber(options)
	{
		const {
			numberTypes,
			additionalNumberTypes,
		} = this.parent;
		const {ENABLE_LABEL} = this.parent.params;
		const {
			SUB_TYPE = {},
			SUB_TYPES = [],
			TABINDEX = '',
			VALUES = {_from: '', _to: ''},
			LABEL = '',
			ICON = null,
			TYPE,
		} = options;

		const subType = SUB_TYPE.VALUE || numberTypes.SINGLE;
		const placeholder = SUB_TYPE.PLACEHOLDER || '';
		const fieldName = options.NAME.replace('_numsel', '');
		const classes = (() => {
			if (ENABLE_LABEL)
			{
				return [
					'main-ui-filter-wield-with-label',
					'main-ui-filter-number-group',
				];
			}

			return ['main-ui-filter-number-group'];
		})();

		const fieldGroup = {
			block: 'number-group',
			type: TYPE,
			mix: classes,
			label: ENABLE_LABEL ? LABEL : '',
			icon: ENABLE_LABEL ? ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			tabindex: TABINDEX,
			value: SUB_TYPE,
			items: SUB_TYPES,
			name: fieldName,
			deleteButton: true,
			content: [],
		};

		if (
			subType !== numberTypes.LESS
			&& subType !== additionalNumberTypes.BEFORE_N
		)
		{
			const from = {
				block: 'main-ui-control-field',
				type: TYPE,
				dragButton: false,
				content: {
					block: 'main-ui-number',
					mix: ['filter-type-single'],
					calendarButton: true,
					valueDelete: true,
					placeholder,
					name: `${fieldName}_from`,
					tabindex: TABINDEX,
					value: VALUES._from || '',
				},
			};

			fieldGroup.content.push(from);
		}

		if (subType === numberTypes.RANGE)
		{
			const line = {
				block: 'main-ui-filter-field-line',
				content: {
					block: 'main-ui-filter-field-line-item',
					tag: 'span',
				},
			};

			fieldGroup.content.push(line);
		}

		if (
			subType === numberTypes.RANGE
			|| subType === numberTypes.LESS
			|| subType === additionalNumberTypes.BEFORE_N
		)
		{
			const to = {
				block: 'main-ui-control-field',
				type: TYPE,
				dragButton: false,
				content: {
					block: 'main-ui-number',
					calendarButton: true,
					valueDelete: true,
					name: `${fieldName}_to`,
					tabindex: TABINDEX,
					value: VALUES._to || '',
				},
			};

			fieldGroup.content.push(to);
		}

		const field = BX.decl(fieldGroup);

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...options},
					node: field,
				}),
			},
		);

		return field;
	}

	createDate(options)
	{
		const {
			dateTypes,
			additionalDateTypes,
		} = this.parent;
		const {
			SUB_TYPE = {},
			SUB_TYPES = [],
			PLACEHOLDER = '',
			VALUES = {
				_from: '',
				_to: '',
				_quarter: '',
				_days: '',
				_month: '',
				_year: '',
				_allow_year: '',
			},
			TABINDEX = '',
			ENABLE_TIME = false,
			LABEL = '',
			ICON = null,
			TYPE,
			VALUE_REQUIRED = false,
			REQUIRED = false,
		} = options;
		const {ENABLE_LABEL} = this.parent.params;

		const subType = SUB_TYPE.VALUE || dateTypes.NONE;
		const fieldName = options.NAME.replace('_datesel', '');
		const classes = (() => {
			if (ENABLE_LABEL)
			{
				return [
					'main-ui-filter-wield-with-label',
					'main-ui-filter-date-group',
				];
			}

			return ['main-ui-filter-date-group'];
		})();

		const fieldGroup = {
			block: 'date-group',
			type: TYPE,
			mix: classes,
			label: ENABLE_LABEL ? LABEL : '',
			icon: ENABLE_LABEL ? ICON : null,
			dragTitle: this.parent.getParam('MAIN_UI_FILTER__DRAG_FIELD_TITLE'),
			deleteTitle: this.parent.getParam('MAIN_UI_FILTER__REMOVE_FIELD'),
			tabindex: TABINDEX,
			value: SUB_TYPE,
			items: SUB_TYPES,
			name: fieldName,
			enableTime: ENABLE_TIME,
			deleteButton: true,
			content: [],
		};

		if (subType === dateTypes.EXACT)
		{
			const fieldDecl = createDateInputDecl({
				type: TYPE,
				name: `${fieldName.NAME}_from`,
				placeholder: PLACEHOLDER,
				tabindex: TABINDEX,
				value: VALUES._from || '',
				enableTime: ENABLE_TIME,
			});

			fieldGroup.content.push(fieldDecl);
		}

		if (
			subType === dateTypes.NEXT_DAYS
			|| subType === dateTypes.PREV_DAYS
			|| subType === additionalDateTypes.PREV_DAY
			|| subType === additionalDateTypes.NEXT_DAY
			|| subType === additionalDateTypes.MORE_THAN_DAYS_AGO
			|| subType === additionalDateTypes.AFTER_DAYS
		)
		{
			const fieldDecl = createNumberInputDecl({
				type: TYPE,
				name: `${fieldName}_days`,
				tabindex: TABINDEX,
				value: VALUES._days || '',
				placeholder: PLACEHOLDER,
			});

			fieldGroup.content.push(fieldDecl);
		}

		if (subType === dateTypes.RANGE)
		{
			const rangeGroup = {
				block: 'main-ui-filter-range-group',
				content: [
					createDateInputDecl({
						type: TYPE,
						name: `${fieldName}_from`,
						placeholder: PLACEHOLDER,
						tabindex: TABINDEX,
						value: VALUES._from || '',
						enableTime: ENABLE_TIME,
					}),
					createLineDecl(),
					createDateInputDecl({
						type: TYPE,
						name: `${fieldName}_to`,
						placeholder: PLACEHOLDER,
						tabindex: TABINDEX,
						value: VALUES._to || '',
						enableTime: ENABLE_TIME,
					}),
				],
			};

			fieldGroup.content.push(rangeGroup);
		}

		if (subType === dateTypes.MONTH)
		{
			const {MONTHS, MONTH, YEARS, YEAR} = options;

			const monthValue = (
				MONTHS.find((item) => {
					return item.VALUE === VALUES._month;
				})
				|| MONTH
				|| MONTHS[0]
			);

			const yearValue = (
				YEARS.find((item) => {
					return item.VALUE === VALUES._year;
				})
				|| YEAR
				|| YEARS[0]
			);

			fieldGroup.content.push(
				createSelectDecl({
					name: `${fieldName}_month`,
					value: monthValue,
					items: MONTHS,
					tabindex: TABINDEX,
				}),
				createSelectDecl({
					name: `${fieldName}_year`,
					value: yearValue,
					items: YEARS,
					tabindex: TABINDEX,
				}),
			);
		}

		if (subType === dateTypes.QUARTER)
		{
			const {YEARS, YEAR, QUARTERS, QUARTER, PARAMS} = options;

			const yearValue = (
				YEARS.find((item) => {
					return item.VALUE === VALUES._year;
				})
				|| YEAR
				|| YEARS[0]
			);

			const quarterValue = (
				QUARTERS.find((item) => {
					return item.VALUE === VALUES._quarter;
				})
				|| QUARTER
				|| QUARTERS[0]
			);

			fieldGroup.content.push(
				createSelectDecl({
					name: `${fieldName}_year`,
					value: yearValue,
					items: YEARS,
					tabindex: TABINDEX,
				}),
				createSelectDecl({
					name: `${fieldName}_quarter`,
					value: quarterValue,
					items: QUARTERS,
					tabindex: TABINDEX,
					params: PARAMS,
				}),
			);
		}

		if (subType === dateTypes.YEAR)
		{
			const {YEARS, YEAR} = options;

			const yearValue = (
				YEARS.find((item) => {
					return item.VALUE === VALUES._year;
				})
				|| YEAR
				|| YEARS[0]
			);

			fieldGroup.content.push(
				createSelectDecl({
					name: `${fieldName}_year`,
					value: yearValue,
					items: YEARS,
					tabindex: TABINDEX,
				}),
			);
		}

		if (subType === 'CUSTOM_DATE')
		{
			const customDateSubType = SUB_TYPES.find((item) => {
				return item.VALUE === 'CUSTOM_DATE';
			});

			if (customDateSubType)
			{
				const customDateDecl = Runtime.clone(customDateSubType.DECL);

				if (Type.isArray(VALUES._days))
				{
					customDateDecl.VALUE.days = VALUES._days;
				}

				if (Type.isArray(VALUES._month))
				{
					customDateDecl.VALUE.months = VALUES._month;
				}

				if (Type.isArray(VALUES._year))
				{
					customDateDecl.VALUE.years = VALUES._year;
				}

				const renderedField = this.createCustomDate(customDateDecl);
				Dom.removeClass(renderedField, 'main-ui-filter-wield-with-label');

				const buttons = [
					...renderedField
						.querySelectorAll('.main-ui-item-icon-container, .main-ui-filter-icon-grab'),
				];

				buttons.forEach((button) => Dom.remove(button));

				fieldGroup.content.push(renderedField);
				fieldGroup.mix.push('main-ui-filter-custom-date-group');
			}
		}

		if (
			subType !== dateTypes.NONE
			&& subType !== additionalDateTypes.CUSTOM_DATE
			&& options.YEARS_SWITCHER
		)
		{
			const YEARS_SWITCHER = Runtime.clone(options.YEARS_SWITCHER);
			const {ITEMS} = YEARS_SWITCHER;

			YEARS_SWITCHER.VALUE = ITEMS.reduce((acc, item) => {
				return item.VALUE === VALUES._allow_year ? item : acc;
			});

			const renderedField = this.createSelect(YEARS_SWITCHER);

			Dom.addClass(renderedField, ['main-ui-filter-year-switcher', 'main-ui-filter-with-padding']);
			Dom.removeClass(renderedField, 'main-ui-filter-wield-with-label');

			const buttons = [
				...renderedField
					.querySelectorAll('.main-ui-item-icon-container, .main-ui-filter-icon-grab'),
			];

			buttons.forEach((button) => Dom.remove(button));

			const lastIndex = fieldGroup.content.length - 1;
			const lastContentItem = fieldGroup.content[lastIndex];

			if (Type.isPlainObject(lastContentItem))
			{
				if (!Type.isArray(lastContentItem.mix))
				{
					lastContentItem.mix = [];
				}

				lastContentItem.mix.push('main-ui-filter-remove-margin-right');
			}

			if (Type.isDomNode(lastContentItem))
			{
				Dom.addClass(lastContentItem, 'main-ui-filter-remove-margin-right');
			}

			requestAnimationFrame(() => {
				Dom.addClass(renderedField.previousElementSibling, 'main-ui-filter-remove-margin-right');
			});

			fieldGroup.content.push(renderedField);
			fieldGroup.mix.push('main-ui-filter-date-with-years-switcher');
		}

		const renderedFieldGroup = BX.decl(fieldGroup);
		const onDateChange = Runtime.debounce(this.onDateChange, 500, this);

		const inputs = [
			...renderedFieldGroup
				.querySelectorAll('.main-ui-date-input'),
		];

		inputs
			.forEach((input) => {
				input.addEventListener('change', onDateChange);
				input.addEventListener('input', onDateChange);

				const {parentNode} = input;
				const clearButton = parentNode.querySelector('.main-ui-control-value-delete');

				if (clearButton)
				{
					clearButton.addEventListener('click', () => {
						setTimeout(() => {
							this.onDateChange({target: input});
						});
					});
				}
			});

		if (VALUE_REQUIRED)
		{
			renderedFieldGroup.dataset.valueRequired = true;

			const allInputs = [
				...inputs,
				...renderedFieldGroup
					.querySelectorAll('.main-ui-number-input'),
			];

			allInputs
				.forEach((input) => {
					input.addEventListener('change', this.checkRequiredDateValue.bind(this));
					input.addEventListener('input', this.checkRequiredDateValue.bind(this));

					const {parentNode} = input;
					const clearButton = parentNode.querySelector('.main-ui-control-value-delete');

					if (clearButton)
					{
						clearButton.addEventListener('click', () => {
							setTimeout(() => {
								this.checkRequiredDateValue({target: input});
							});
						});
					}

					Event.bindOnce(input, 'mouseout', () => {
						this.checkRequiredDateValue({target: input});
					});
				});
		}

		if (REQUIRED)
		{
			const removeButton = renderedFieldGroup
				.querySelector('.main-ui-filter-field-delete');

			if (removeButton)
			{
				BX.remove(removeButton);
			}
		}

		const currentValues = {};
		this.parent.prepareControlDateValue(currentValues, fieldName, renderedFieldGroup);

		Object.entries(currentValues).forEach(([key, value]) => {
			currentValues[key.replace(fieldName, '')] = value;
			delete currentValues[key];
		});

		this.parent.getEmitter().emit(
			'init',
			{
				field: new Field({
					parent: this.parent,
					options: {...options, VALUES: currentValues},
					node: renderedFieldGroup,
				}),
			},
		);

		return renderedFieldGroup;
	}

	checkRequiredDateValue(event)
	{
		if (event.target.value === '')
		{
			this.showError({
				id: 'valueError',
				target: event.target,
				text: this.parent.params.MAIN_UI_FILTER__VALUE_REQUIRED,
			});
			return;
		}

		this.hideError({
			id: 'valueError',
			target: event.target,
		});
	}

	onDateChange(event)
	{
		if (values.get(event.target) === event.target.value)
		{
			return;
		}

		values.set(event.target, event.target.value);

		if (event.target.value === '')
		{
			this.hideError({
				id: 'formatError',
				target: event.target,
			});
			return;
		}

		BX.ajax
			.runComponentAction(
				'bitrix:main.ui.filter',
				'checkDateFormat',
				{
					mode: 'ajax',
					data: {
						value: event.target.value,
						format: BX.message('FORMAT_DATETIME'),
					},
				},
			)
			.then((result) => {
				if (!result.data.result)
				{
					this.showError({
						id: 'formatError',
						target: event.target,
					});
					return;
				}

				this.hideError({
					id: 'formatError',
					target: event.target,
				});
			});
	}

	showError({id, target, text = null})
	{
		Dom.style(target, 'border-color', '#FF5752');

		if (
			errorMessages.has(target)
			&& errorMessagesTypes.get(target) === id
		)
		{
			Dom.remove(errorMessages.get(target));
		}

		const {
			MAIN_UI_FILTER__DATE_ERROR_TITLE,
			MAIN_UI_FILTER__DATE_ERROR_LABEL,
		} = this.parent.params;

		const errorText = text || `${MAIN_UI_FILTER__DATE_ERROR_LABEL} ${Loc.getMessage('FORMAT_DATE')}`;

		const dateErrorMessage = Tag.render`
			<div 
				class="main-ui-filter-error-message" 
				title="${MAIN_UI_FILTER__DATE_ERROR_TITLE}">
				${errorText}
			</div>
		`;

		errorMessages.set(target, dateErrorMessage);
		errorMessagesTypes.set(target, id);

		Dom.insertAfter(dateErrorMessage, target);
		Dom.attr(target, 'is-valid', 'false');
	}

	hideError({id, target})
	{
		Dom.style(target, 'border-color', null);

		if (
			errorMessages.has(target)
			&& errorMessagesTypes.get(target) === id
		)
		{
			Dom.remove(errorMessages.get(target));
		}

		Dom.attr(target, 'is-valid', 'true');
	}
}