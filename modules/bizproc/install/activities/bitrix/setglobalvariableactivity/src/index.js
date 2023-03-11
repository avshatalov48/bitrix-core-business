import {Reflection, Tag, Type, Loc, Event, Dom, Text, Runtime} from 'main.core';
import {Globals} from 'bizproc.globals';

import 'bp_field_type';
import './css/style.css';

import {Menu} from "./menu/menu";
import {BaseEvent} from "main.core.events";
import {Selector} from "./selector/selector";

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class SetGlobalVariableActivity {
	static #INDEX_ATTRIBUTE_NAME = 'bp_sgva_index';
	static #INPUT_INDEX_ATTRIBUTE_NAME = 'bp_sgva_input_index';

	static #G_VAR_OBJECT_NAME = 'GlobalVar';
	static #G_CONST_OBJECT_NAME = 'GlobalConst';
	static #DOCUMENT_OBJECT_NAME = 'Document';
	static #HELPER_OBJECT_NAME = 'Default';

	static #ROW_TABLE_ID = 'bp_sgva_addrow_table';
	static #ADD_BUTTON_ID = 'bp_sgva_add_button';
	static #RESULTS_ID = 'bp_sgva_results_';
	static #VARIABLE_ROLE = 'bp_sgva_variable_';
	static #PARAMETER_ROLE = 'bp_sgva_value_';

	isRobot: boolean;
	documentType;
	signedDocumentType: string;

	variables: object;
	constants: object;
	documentFields: object;

	currentValues: object;
	visibilityMessages: object;
	formName: string;

	addRowTable: HTMLDivElement | HTMLTableElement;

	availableOptions: Map;
	availableOptionsByGroupId: Map;

	rowIndex: number = -1;
	numberOfTypes: number = 9;

	constructor(options)
	{
		if (Type.isPlainObject(options)) {
			this.isRobot = options.isRobot;
			this.documentType = options.documentType;
			this.signedDocumentType = options.signedDocumentType;

			this.variables = options.variables;
			this.constants = options.constants ?? {};
			this.documentFields = options.documentFields ?? {};

			this.currentValues = options.currentValues;
			this.visibilityMessages = options.visibilityMessages;
			this.formName = options.formName;

			this.addRowTable = options.addRowTable;
		}
	}

	init()
	{
		this.initAvailableOptions();

		const addAssignmentExpression =
			this.isRobot
				? 'addAssignmentExpressionRobot'
				: 'addAssignmentExpressionDesigner'
		;

		if (Object.keys(this.currentValues).length <= 0)
		{
			this[addAssignmentExpression]();
		}
		for (const variableExpression in this.currentValues)
		{
			this[addAssignmentExpression](variableExpression, this.currentValues[variableExpression]);
		}

		if (this.isRobot)
		{
			//this.addExpressionButtonRobot();
		}
		else
		{
			this.addExpressionButtonDesigner()
		}
	}

	// region check visibility
	isGVariableVisibility(visibility): boolean
	{
		return visibility.startsWith(this.constructor.#G_VAR_OBJECT_NAME);
	}

	isGConstantVisibility(visibility): boolean
	{
		return visibility.startsWith(this.constructor.#G_CONST_OBJECT_NAME);
	}

	isDocumentVisibility(visibility): boolean
	{
		return visibility.startsWith(this.constructor.#DOCUMENT_OBJECT_NAME);
	}

	isHelperVisibility(visibility): boolean
	{
		return visibility.startsWith(this.constructor.#HELPER_OBJECT_NAME);
	}
	// endregion

	// region options
	initAvailableOptions()
	{
		this.availableOptions = this.getAvailableOptions();
		this.availableOptionsByGroupId = this.getAvailableOptionsByGroup();
	}

	getAvailableOptions(): Map
	{
		const options = new Map();
		this.fillOptions(this.variables, options);
		this.fillOptions(this.constants, options);
		this.fillOptions(this.documentFields, options);

		options.set('variable', {
			id: '',
			title: Loc.getMessage('BPSGVA_VARIABLE'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.constructor.#HELPER_OBJECT_NAME,
				title: Loc.getMessage('BPSGVA_VARIABLE')
			}
		});

		options.set('parameter', {
			id: '',
			title: Loc.getMessage('BPSGVA_PARAMETER'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.constructor.#HELPER_OBJECT_NAME,
				title: Loc.getMessage('BPSGVA_PARAMETER')
			}
		});

		options.set('clear', {
			id: '',
			title: Loc.getMessage('BPSGVA_CLEAR'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.constructor.#HELPER_OBJECT_NAME,
				title: Loc.getMessage('BPSGVA_CLEAR')
			}
		});

		return options;
	}

	fillOptions(source, options)
	{
		let optionId, optionProperty, optionsSource;

		for (const groupName in source)
		{
			optionsSource = source[groupName];

			if (optionsSource['children'])
			{
				optionsSource = optionsSource['children'];
			}

			for (const i in optionsSource)
			{
				optionId = optionsSource[i]['id'];
				optionProperty = optionsSource[i];
				options.set(optionId, optionProperty);
			}
		}
	}

	getAvailableOptionsByGroup(): Map
	{
		const options = new Map();
		this.fillOptionsByGroupWithGlobals(this.variables, options, this.constructor.#G_VAR_OBJECT_NAME);
		this.fillOptionsByGroupWithGlobals(this.constants, options, this.constructor.#G_CONST_OBJECT_NAME);

		const items = [];
		for (const i in this.documentFields)
		{
			items.push(this.documentFields[i]);
		}
		options.set(this.constructor.#DOCUMENT_OBJECT_NAME + ':' + this.constructor.#DOCUMENT_OBJECT_NAME, items);

		return options;
	}

	fillOptionsByGroupWithGlobals(source, options, topGroupName)
	{
		for (const subGroupName in source)
		{
			const key = topGroupName + ':' + subGroupName;
			options.set(key, source[subGroupName]);
		}
	}
	// endregion

	addAssignmentExpressionRobot(variableId, values)
	{
		if (Type.isString(values))
		{
			values = {0: values};
		}

		const incomingData = {variable: variableId, values};
		this.modifyIncomingDataRobot(incomingData);

		const addRowTable = this.addRowTable;
		this.rowIndex++;

		const rowInputs = Tag.render`<div id="${this.constructor.#RESULTS_ID + this.rowIndex}"></div>`;

		const parameterRowWrapper = Tag.render`
			<div
				class="bizproc-automation-popup-settings-title"
				data-role="${Text.encode(this.constructor.#PARAMETER_ROLE + this.rowIndex)}"
			></div>
		`;

		if (incomingData.values.length <= 0)
		{
			const option = Runtime.clone(this.getOptionPropertiesRobot('clear'));
			option['multiple'] = incomingData.variable.property.Multiple;
			option['type'] = incomingData.variable.property.Type;
			option['inputIndex'] = 0;
			Dom.append(this.createParameterRowRobot(this.rowIndex, option, rowInputs), parameterRowWrapper);
		}
		for (const i in incomingData.values)
		{
			const option = Runtime.clone(incomingData.values[i]);
			option['multiple'] = incomingData.variable.property.Multiple;
			option['type'] = incomingData.variable.property.Type;
			option['inputIndex'] = i;
			Dom.append(this.createParameterRowRobot(this.rowIndex, option, rowInputs), parameterRowWrapper);
		}

		if (incomingData.variable.property.Multiple && incomingData.variable.property.Type !== 'user')
		{
			const inputIndex = incomingData.values.length <= 0 ? 1 : incomingData.values.length;
			Dom.append(this.createAddParameterRowRobot(this.rowIndex, inputIndex), parameterRowWrapper);
		}

		const newRow = Tag.render`
			<div class="bizproc-automation-popup-settings">
				<div
					class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text"
					style="display: flex; align-items: flex-start"
				>
					${this.createVariableRowRobot(incomingData.variable, rowInputs)}
					${parameterRowWrapper}
				</div>
				${rowInputs}
			</div>
		`;

		Dom.append(newRow, addRowTable);
	}

	modifyIncomingDataRobot(incomingData)
	{
		const option = this.getOptionPropertiesRobot(incomingData.variable);
		if (incomingData.variable === undefined || option.groupId === this.constructor.#HELPER_OBJECT_NAME + ':text')
		{
			incomingData.variable = Runtime.clone(this.getOptionPropertiesRobot('variable'));
			const valueOption = Runtime.clone(this.getOptionPropertiesRobot('parameter'));
			incomingData.values = [{
				id: valueOption.id,
				title: valueOption.title
			}];

			return;
		}

		let valuesOptions = [];

		switch (option.property.Type)
		{
			case 'select':
				valuesOptions = this.getIncomingValuesSelect(incomingData);
				break;
			case 'bool':
				valuesOptions = this.getIncomingValuesBool(incomingData);
				break;
			default:
				for (const i in incomingData.values)
				{
					let valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
					if (incomingData.values[i] === '')
					{
						valueOption = this.getOptionPropertiesRobot('clear');
					}
					valuesOptions.push({
						id: valueOption.id,
						title: valueOption.title
					});
				}
		}

		incomingData.variable = Runtime.clone(option);
		incomingData.values = valuesOptions;
	}

	getOptionPropertiesRobot(optionId): {}
	{
		const option = this.availableOptions.get(optionId);
		if (Type.isUndefined(option))
		{
			return this.getDefaultOptionProperties(optionId);
		}

		return this.getShortOptionProperties(option);
	}

	getDefaultOptionProperties(optionId): {}
	{
		return {
			id: optionId,
			property: {Type: 'string', Multiple: false},
			groupId: this.constructor.#HELPER_OBJECT_NAME + ':text',
			title: optionId
		};
	}

	getShortOptionProperties(option): {}
	{
		return {
			id: option.id,
			property: option.customData.property,
			groupId: option.customData.groupId,
			title: option.customData.title
		};
	}

	getIncomingValuesSelect(incomingData): []
	{
		const option = this.getOptionPropertiesRobot(incomingData.variable);
		const valuesOptions = []
		let title;
		let valueOption;
		let isExpressionOption;

		for (const i in incomingData.values)
		{
			title = Loc.getMessage('BPSGVA_CLEAR');
			if (incomingData.values[i] !== '')
			{
				valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
				isExpressionOption = true;
				title = valueOption.title;
			}
			if (option.property.Options[incomingData.values[i]] !== undefined)
			{
				isExpressionOption = false;
				title = option.property.Options[incomingData.values[i]];
			}

			valuesOptions.push({
				id: incomingData.values[i],
				title,
				isExpressionOption
			});
		}

		return valuesOptions;
	}

	getIncomingValuesBool(incomingData): []
	{
		const valuesOptions = [];
		let title;
		let valueOption;

		for (const i in incomingData.values)
		{
			let isExpressionOption = false;
			switch (incomingData.values[i])
			{
				case 'Y':
					title = Loc.getMessage('BPSGVA_BOOL_YES');
					break;
				case 'N':
					title = Loc.getMessage('BPSGVA_BOOL_NO');
					break;
				case '':
					title = Loc.getMessage('BPSGVA_CLEAR');
					break;
				default:
					valueOption = this.getOptionPropertiesRobot(incomingData.values[i]);
					title = valueOption.title;
					isExpressionOption = true;
			}
			valuesOptions.push({
				id: incomingData.values[i],
				title,
				isExpressionOption
			});
		}

		return valuesOptions;
	}

	createVariableRowRobot(variableData, rowInputs: HTMLDivElement): HTMLDivElement
	{
		const variableNode = Tag.render`
			<span 
				class="bizproc-automation-popup-settings-link setglobalvariableactivity-underline"
				data-role="${Text.encode(this.constructor.#VARIABLE_ROLE+ this.rowIndex)}"
				bp_sgva_index="${Text.encode(String(this.rowIndex))}"
			>
				${Loc.getMessage('BPSGVA_VARIABLE')}
			</span>
		`;

		const systemExpression = this.#parseSystemExpression(variableData.id);
		const isDeleted = (systemExpression.groupId === this.constructor.#HELPER_OBJECT_NAME + ':text');
		if (isDeleted)
		{
			systemExpression.title = Loc.getMessage('BPSGVA_VARIABLE');
		}

		this.#replaceTitle(variableNode, systemExpression.title);
		this.#setHiddenValue(
			variableNode,
			systemExpression.id,
			{
				isMultiple: false,
				inputIndex: 0,
				isExpressionOption: false,
			},
			rowInputs
		);

		Event.bind(variableNode, 'click', this.onVariableSelectClickRobot.bind(this))

		return Tag.render`<div>${variableNode}</div>`;
	}

	createParameterRowRobot(index, valueData, rowInputs): HTMLDivElement
	{
		const parameterNode = Tag.render`
			<span 
				class="bizproc-automation-popup-settings-link setglobalvariableactivity-underline"
				data-role="${Text.encode(this.constructor.#PARAMETER_ROLE + index)}"
				bp_sgva_index="${Text.encode(String(index))}"
			>
			</span>
		`;
		parameterNode.setAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME, Text.toInteger(String(valueData.inputIndex)));

		const systemExpression = this.#parseSystemExpression(valueData.id);

		systemExpression.title = this.#formatTitle(valueData.type, valueData.title, valueData.id) ?? valueData.title;
		if (!Type.isStringFilled(systemExpression.title))
		{
			systemExpression.title = Loc.getMessage('BPSGVA_CLEAR');
		}

		this.#replaceTitle(parameterNode, systemExpression.title);
		this.#setHiddenValue(
			parameterNode,
			systemExpression.id,
			{
				isMultiple: valueData.multiple,
				inputIndex: Text.toInteger(String(valueData.inputIndex)),
				isExpressionOption: valueData.isExpressionOption
			},
			rowInputs
		);

		Event.bind(parameterNode, 'click', this.onParameterSelectClickRobot.bind(this, valueData.inputIndex));

		return Tag.render`
			<div class="bizproc-automation-popup-settings-title setglobalvariableactivity-parameter-wrapper">
				<div class="bizproc-automation-popup-settings-title setglobalvariableactivity-symbol-equal"> = </div>
				<div>
					${parameterNode}
				</div>
			</div>
		`;
	}

	#formatTitle(type: string, title: string, inputValue: string, options: {} = null): ?string
	{
		const property = {
			Type: type,
			Options: Type.isPlainObject(options) ? options : null,
		};
		const value = (type === 'bool') ? inputValue : title;

		if (type === 'bool' && !['Y', 'N'].includes(value))
		{
			return null;
		}

		return BX.Bizproc.FieldType.formatValuePrintable(property, value) ?? null;
	}

	replaceHiddenInputRobot(data, rowInputs)
	{
		const inputValue = data.inputValue;
		const role =  data.role + '_input';
		let input = document.querySelectorAll('[data-role="' + role + '"]');

		// single input
		if (input.length >= 1 && !data.multiple)
		{
			input[0].name = data.isExpressionOption ? data.role + '_text' : data.role;
			input[0].value = data.inputValue;

			return;
		}

		// multiple input
		if (input.length >= 1 && data.multiple)
		{
			const inputKeys = Object.keys(input);
			for (const i in inputKeys)
			{
				const inputIndex = input[inputKeys[i]].getAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME);
				if (data.inputIndex === inputIndex)
				{
					input[i].name = data.isExpressionOption ? data.role + '_text' : data.role + '[]';
					input[i].value = data.inputValue;

					return;
				}
			}
		}

		// create input
		input = Tag.render`<input type="hidden">`;
		if (data.isExpressionOption)
		{
			input.name = data.role + '_text';
		}
		else
		{
			input.name = data.multiple ? data.role + '[]' : data.role;
		}

		input.value = inputValue;
		input.setAttribute('data-role', role);
		input.setAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME, String(data.inputIndex));
		Dom.append(input, rowInputs);
	}

	onVariableSelectClickRobot(event)
	{
		const target = event.target;
		const visibilityNames = this.getVisibilityNamesForSelect('variable');

		const menu = new Menu({
			popupOptions: {
				id: target.dataset.role + '_popup',
				target,
				offsetTop: 5,
				overlay: {backgroundColor: 'transparent'},
				cacheable: false,
				events: {
					onClose: () => (menu.destroy()),
				}
			},
			contentData: {
				rows: [
					{
						label: Loc.getMessage('BPSGVA_TYPE_OF_PARAMETER'),
						values: visibilityNames,
					},
					{
						label: Loc.getMessage('BPSGVA_LIST_OF_VALUES'),
						values: [
							{
								id: 'empty',
								text: Loc.getMessage('BPSGVA_EMPTY'),
							}
						],
						onClick: this.#onMenuRowVariableValuesClick.bind(this),
					}
				],
			},
			events: {
				'onSetRowValue': this.#onMenuVariableSetRowValue,
				'onApplyChangesClick': this.#onMenuVariableApplyChangesClick.bind(this),
			},
		});
		menu.create();

		const selectedVariable = this.getVariableInputValue(target.getAttribute('data-role'));
		const systemExpression = this.#parseSystemExpression(selectedVariable);
		const isDeleted = (systemExpression.groupId === this.constructor.#HELPER_OBJECT_NAME + ':text');
		if (isDeleted)
		{
			systemExpression.groupId = visibilityNames[0].id;
			systemExpression.title = Loc.getMessage('BPSGVA_EMPTY');
		}

		menu.setRowValue(0, systemExpression.groupId);
		menu.setRowValue(1, selectedVariable, systemExpression.title);

		menu.show();
	}

	#parseSystemExpression(systemExpression: string): {id: string, groupId: string, title: string}
	{
		const option = this.availableOptions.get(systemExpression);
		if (Type.isUndefined(option))
		{
			return {
				id: systemExpression,
				groupId: this.constructor.#HELPER_OBJECT_NAME + ':text',
				title: systemExpression,
			};
		}

		return {
			id: option.id,
			groupId: option.customData.groupId,
			title: option.customData.title,
		};
	}

	#onMenuRowVariableValuesClick(event: BaseEvent)
	{
		const menu: Menu = event.getData().menu;
		const selectedVariableType = menu.getRowValue(0);

		const items = this.availableOptionsByGroupId.get(selectedVariableType) ?? [];
		const filteredItems = this.filterItemsInStandardMenuRobot('string', items);

		const selector = new Selector(
			filteredItems,
			{
				target: event.getTarget(),
				showStubs: true, //this.isGVariableVisibility(selectedVariableType) || this.isGConstantVisibility(selectedVariableType),
				objectName: this.constructor.#G_VAR_OBJECT_NAME, //this.#getObjectName(selectedVariableType),
				events: {
					'onBeforeSelect': this.#onBeforeSelectItemInSelector.bind(this, menu),
					'onAfterCreate': this.#onAfterCreateItemInSelector.bind(this),
				},
				itemCreateContext: {
					index: 0,
					visibility: String(selectedVariableType),
					type: 'string',
					mode: Globals.Manager.Instance.mode.variable,
					objectName: this.constructor.#G_VAR_OBJECT_NAME,
					signedDocumentType: this.signedDocumentType,
				},
			}
		);

		selector.show();
	}

	#getObjectName(visibility: string): string
	{
		if (this.isGVariableVisibility(visibility))
		{
			return this.constructor.#G_VAR_OBJECT_NAME;
		}

		if (this.isGConstantVisibility(visibility))
		{
			return this.constructor.#G_CONST_OBJECT_NAME;
		}

		if (this.isDocumentVisibility(visibility))
		{
			return this.constructor.#DOCUMENT_OBJECT_NAME;
		}

		return '';
	}

	#onMenuVariableSetRowValue(event: BaseEvent)
	{
		const eventData = event.getData();
		const rowIndex = eventData.rowIndex;
		const menu: Menu = eventData.menu;

		if (rowIndex === 0)
		{
			menu.setRowValue(1, '', Loc.getMessage('BPSGVA_EMPTY'));
		}
	}

	#onMenuVariableApplyChangesClick(event: BaseEvent)
	{
		const eventData = event.getData();
		const values = eventData.values;
		const target = eventData.target;

		const newSelectedVariable = values[1];
		const systemExpression = this.#parseSystemExpression(newSelectedVariable);
		const isExist = (systemExpression.groupId !== this.constructor.#HELPER_OBJECT_NAME + ':text');
		if (!isExist)
		{
			systemExpression.title = Loc.getMessage('BPSGVA_VARIABLE');
		}

		this.#replaceTitle(target, systemExpression.title);
		this.#setHiddenValue(
			target,
			systemExpression.id,
			{
				isMultiple: false,
				inputIndex: 0,
				isExpressionOption: false
			}
		);
		this.#clearRelatedParameter(target);
		this.#addEmptyRelatedParameter(target, newSelectedVariable);
	}

	#replaceTitle(target: HTMLElement, title: string)
	{
		target.innerText = title;
	}

	#setHiddenValue(
		target: HTMLElement,
		value: string,
		context: {
			isMultiple: boolean,
			inputIndex: number,
			isExpressionOption: boolean,
		},
		rowInputs?: HTMLElement
	)
	{
		const index = target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const targetRole = target.getAttribute('data-role');
		const role = targetRole + '_input';
		if (Type.isNil(rowInputs))
		{
			rowInputs = document.getElementById(this.constructor.#RESULTS_ID + index);
		}

		const inputs = document.querySelectorAll('[data-role="' + role + '"]');
		// single input
		if (inputs.length >= 1 && !context.isMultiple)
		{
			inputs[0].name = Text.encode(targetRole + (context.isExpressionOption ? '_text' : ''));
			inputs[0].value = value;

			return;
		}

		// multiple input
		if (inputs.length >= 1 && context.isMultiple)
		{
			for (const input of inputs)
			{
				if (context.inputIndex === input.getAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME))
				{
					input.name =
						context.isExpressionOption
							? Text.encode(targetRole + '_text')
							: Text.encode(targetRole + '[]')
					;
					input.value = value;

					return;
				}
			}
		}

		let inputName;
		if (context.isExpressionOption)
		{
			inputName = targetRole + '_text';
		}
		else
		{
			inputName = targetRole + (context.isMultiple ? '[]' : '');
		}

		const input = Tag.render`
			<input 
				type="hidden"
				name="${Text.encode(inputName)}" value="${Text.encode(value)}"
				data-role="${Text.encode(role)}"
			>
		`;
		input.setAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME, Text.toInteger(context.inputIndex));

		Dom.append(input, rowInputs);
	}

	#clearRelatedParameter(target: HTMLElement)
	{
		const index = target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const parameterNode = document.querySelector('[data-role="' + this.constructor.#PARAMETER_ROLE + index + '"]');
		this.deleteOldValueRowsRobot(parameterNode);
	}

	#addEmptyRelatedParameter(target: HTMLElement, selectedVariable: string)
	{
		const index = target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const variableOption = this.getOptionPropertiesRobot(selectedVariable);
		variableOption.inputIndex = '0';

		this.changeParameterExpressionRobot(index, variableOption);
	}

	getVariableInputValue(role): string
	{
		const inputRole = role + '_input';
		const inputs = document.querySelectorAll('[data-role="' + inputRole + '"]');

		return (inputs.length >= 1) ? inputs['0'].value : '';
	}

	createInputForMenuFormRobot(type, index, inputValue): HTMLElement
	{
		if (type === 'variable')
		{
			const wrapper = Tag.render`<div class="bizproc-automation-popup-select"></div>`;
			const input = Tag.render`<input class="bizproc-automation-popup-input" type="hidden" style="width: 280px">`;
			Dom.append(input, wrapper);

			return wrapper;
		}

		const variableOption = this.getVariableOptionFromVariableInput(index);

		let wrapper;
		switch (variableOption.property.Type)
		{
			case 'user':
				wrapper = BX.Bizproc.FieldType.renderControl(
					this.documentType,
					variableOption.property,
					'bp_sgva_field_input',
					inputValue
				);
				break;
			case 'select':
			case 'bool':
				wrapper = BX.Bizproc.FieldType.renderControl(
					this.documentType,
					{
						Type: variableOption.property.Type,
						Options: variableOption.property.Options
					},
					'bp_sgva_field_input',
					inputValue
				);
				break;
			default:
				wrapper = BX.Bizproc.FieldType.renderControl(
					this.documentType,
					{Type: variableOption.property.Type},
					'bp_sgva_field_input',
					variableOption.id
				);
		}

		Dom.style(wrapper, 'width', '280px');
		const input = this.findInputInFormRobot(wrapper);
		if (['bool', 'select'].includes(variableOption.property.Type))
		{
			if (input.value !== inputValue)
			{
				const option = this.getOptionPropertiesRobot(inputValue);
				this.resolveAdditionOptionInSelectRobot(input, option);
			}
		}

		if (input)
		{
			Dom.style(input, 'width', '100%');
		}

		return wrapper;
	}

	getVariableOptionFromVariableInput(index): {}
	{
		const variableInput = document.querySelector('[data-role="' + this.constructor.#VARIABLE_ROLE + index + '_input"]');
		const variableId = variableInput ? variableInput.value: '';

		return this.getOptionPropertiesRobot(variableId);
	}

	findInputInFormRobot(form): HTMLElement
	{
		let inputs = form.getElementsByTagName('input');
		if (inputs.length >= 1)
		{
			return inputs[inputs.length - 1];
		}

		inputs = form.getElementsByTagName('textarea');
		if (inputs.length >= 1)
		{
			return inputs[inputs.length - 1];
		}

		inputs = form.getElementsByTagName('select');
		if (inputs.length >= 1)
		{
			return inputs[inputs.length - 1];
		}
	}

	resolveAdditionOptionInSelectRobot(input, option)
	{
		const selectOptions = input.options;
		let opt = selectOptions[selectOptions.length - 1];
		if (opt.getAttribute('data-role') !== 'expression')
		{
			opt = Tag.render`<option></option>`;
			opt.setAttribute('data-role', 'expression');
			Dom.append(opt, input);
		}
		opt.value = option.id;
		if (!option.customData)
		{
			opt.text = option.title;
		}
		else
		{
			opt.text = option.customData.get('title');
		}

		opt.setAttribute('selected', 'selected');
		if (!opt.selected)
		{
			opt.selected = true;
		}
	}

	filterItemsInStandardMenuRobot(variableType, items): []
	{
		const filter = this.getFilterByVariableType(variableType);
		if (filter.length === this.numberOfTypes)
		{
			return items;
		}

		const filterItems = [];
		for (const i in items)
		{
			if (items[i].children)
			{
				const filterChildrenItems = this.filterItemsInStandardMenuRobot(variableType, items[i].children);
				if (filterChildrenItems.length >= 1)
				{
					const menuItem = items[i];
					menuItem.children = filterChildrenItems;
					filterItems.push(menuItem);
				}
			}
			else
			{
				if (filter.includes(items[i].customData.property.Type))
				{
					filterItems.push(items[i]);
				}
			}
		}

		return filterItems;
	}

	getFilterByVariableType(type): []
	{
		switch (type)
		{
			case 'double':
				return ['int', 'double'];
			case 'datetime':
				return ['date', 'datetime'];
			case 'date':
			case 'int':
			case 'user':
				return [type];
			default:
				// this.numberOfTypes = 9
				return ['string', 'text', 'select', 'bool', 'int', 'double', 'date', 'datetime', 'user'];
		}
	}

	getVisibilityNamesForSelect(type): {}
	{
		const list = [];
		const parameterTypes = this.visibilityMessages;
		parameterTypes[this.constructor.#HELPER_OBJECT_NAME] = {
			'text': Loc.getMessage('BPSGVA_TEXT'),
		};
		for (const topGroupName in parameterTypes)
		{
			if (type === 'variable' && topGroupName !== this.constructor.#G_VAR_OBJECT_NAME)
			{
				continue;
			}
			for (const subGroupName in parameterTypes[topGroupName])
			{
				list.push({
					id: topGroupName + ':' + subGroupName,
					text: parameterTypes[topGroupName][subGroupName]
				});
			}
		}

		return list;
	}

	changeParameterExpressionRobot(index, variable)
	{
		const parameterNode = document.querySelector('[data-role="' + this.constructor.#PARAMETER_ROLE + index + '"]');
		this.deleteOldValueRowsRobot(parameterNode);
		const rowInputs = document.getElementById(this.constructor.#RESULTS_ID + index);

		const option = Runtime.clone(this.getOptionPropertiesRobot('parameter'));

		option['multiple'] = variable.property.Multiple;
		option['inputIndex'] = '0';

		Dom.append(this.createParameterRowRobot(index, option, rowInputs), parameterNode);

		if (variable.property.Multiple && variable.property.Type !== 'user')
		{
			const inputIndex = (variable.inputIndex !== '0') ? variable.inputIndex : '1';
			Dom.append(this.createAddParameterRowRobot(index, inputIndex), parameterNode);
		}
	}

	deleteOldValueRowsRobot(node)
	{
		const role = node.getAttribute('data-role');
		node.innerHTML = '';
		const oldInputs = document.querySelectorAll('[data-role="' + role + '_input"]');
		for (const i in Object.keys(oldInputs))
		{
			oldInputs[i].remove();
		}
	}

	createAddParameterRowRobot(index, inputIndex): HTMLDivElement
	{
		const addWrapper = Tag.render`<div class="bizproc-automation-popup-settings-title" style="display:flex;"></div>`
		const addExpression = Tag.render`
			<div class="bizproc-type-control-clone-btn setglobalvariableactivity-dashed-grey setglobalvariableactivity-add-parameter">
				${Text.encode(Loc.getMessage('BPSGVA_ADD_PARAMETER'))}
			</div>
		`;
		addExpression.setAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME, String(index));
		addExpression.setAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME, String(inputIndex));
		Event.bind(addExpression, 'click', this.onAddParameterButtonClickRobot.bind(this));

		Dom.append(addExpression, addWrapper);

		return addWrapper;
	}

	onAddParameterButtonClickRobot(event)
	{
		const index = event.target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const rowInputs = document.getElementById(this.constructor.#RESULTS_ID + index);
		const inputIndex = event.target.getAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME);

		const option = Runtime.clone(this.getOptionPropertiesRobot('parameter'));
		option['multiple'] = true;
		option['inputIndex'] = inputIndex;

		event.target.parentNode.before(this.createParameterRowRobot(index, option, rowInputs));
		event.target.setAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME, Number(inputIndex) + 1);
	}

	onParameterSelectClickRobot(inputIndex, event)
	{
		const target = event.target;
		const visibilityNames = this.getVisibilityNamesForSelect('all');

		const menu = new Menu({
			popupOptions: {
				id: target.dataset.role + '_popup',
				target,
				offsetTop: 5,
				overlay: {backgroundColor: 'transparent'},
				cacheable: false,
				events: {
					onClose: () => (menu.destroy()),
				},
			},
			contentData: {
				rows: [
					{
						label: Loc.getMessage('BPSGVA_TYPE_OF_PARAMETER'),
						values: visibilityNames,
					},
					{
						label: Loc.getMessage('BPSGVA_LIST_OF_VALUES'),
						values: [
							{
								id: 'empty',
								text: Loc.getMessage('BPSGVA_EMPTY'),
							}
						],
						onClick: this.#onMenuRowParameterValuesClick.bind(this),
					},
				],
			},
			events: {
				'onSetRowValue': this.#onMenuParameterSetRowValue.bind(this),
				'onApplyChangesClick': this.#onMenuParameterApplyChangesClick.bind(this),
			},
		});
		menu.create();

		const selectedParameter = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
		const systemExpression = this.#parseSystemExpression(selectedParameter);

		menu.setRowValue(0, systemExpression.groupId);
		const isOwnValue = systemExpression.groupId === this.constructor.#HELPER_OBJECT_NAME + ':text';
		const inputValue = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
		if (isOwnValue)
		{
			const index = target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
			const secondRowTarget = this.createInputForMenuFormRobot('all', index, inputValue);
			const input = this.findInputInFormRobot(secondRowTarget);
			menu.replaceRowTarget(1, secondRowTarget, input);
		}
		menu.setRowValue(1, inputValue, isOwnValue ? '' : systemExpression.title);

		menu.show();
	}

	#onMenuRowParameterValuesClick(event: BaseEvent)
	{
		const menu: Menu = event.getData().menu;
		const selectedParameterType = menu.getRowValue(0);

		const selectedVariableIndex = menu.target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const selectedVariableOption = this.getVariableOptionFromVariableInput(selectedVariableIndex);
		const selectedVariableType = selectedVariableOption.property.Type;

		const items = this.availableOptionsByGroupId.get(selectedParameterType) ?? [];
		const filteredItems = this.filterItemsInStandardMenuRobot(selectedVariableType, items);

		const showStubs = (
			this.isGVariableVisibility(selectedParameterType)
			|| this.isGConstantVisibility(selectedParameterType)
		);
		const objectName = this.#getObjectName(selectedParameterType);
		let mode = '';
		if (showStubs)
		{
			mode =
				this.isGVariableVisibility(selectedParameterType)
					? Globals.Manager.Instance.mode.variable
					: Globals.Manager.Instance.mode.constant
			;
		}

		const selector = new Selector(
			filteredItems,
			{
				showStubs,
				objectName,
				target: event.getTarget(),
				events: {
					'onBeforeSelect': this.#onBeforeSelectItemInSelector.bind(this, menu),
					'onAfterCreate': this.#onAfterCreateItemInSelector.bind(this),
				},
				itemCreateContext: {
					mode,
					objectName,
					index: 0,
					visibility: String(selectedParameterType),
					type: String(selectedVariableType),
					signedDocumentType: this.signedDocumentType,
				},
			}
		);

		selector.show();
	}

	#onMenuParameterSetRowValue(event: BaseEvent)
	{
		const eventData = event.getData();
		const rowIndex = eventData.rowIndex;
		const menu: Menu = eventData.menu;

		if (rowIndex === 0)
		{
			if (eventData.value !== this.constructor.#HELPER_OBJECT_NAME + ':text')
			{
				menu.setRowLabel(1, Loc.getMessage('BPSGVA_LIST_OF_VALUES'));

				const row = menu.createEmptyRow(1);
				menu.replaceRowTarget(1, row, row);
				menu.setRowValue(1, '', Loc.getMessage('BPSGVA_EMPTY'));

				return;
			}

			menu.setRowLabel(1, Loc.getMessage('BPSGVA_INPUT_TEXT'));

			const index = menu.target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
			const secondRowTarget = this.createInputForMenuFormRobot('all', index, '');
			const input = this.findInputInFormRobot(secondRowTarget);
			menu.replaceRowTarget(1, secondRowTarget, input);
			menu.setRowValue(1, '', '');
		}
	}

	#onMenuParameterApplyChangesClick(event: BaseEvent)
	{
		const eventData = event.getData();
		const menu: Menu = eventData.menu;
		const values = eventData.values;
		const target = eventData.target;

		const parameterType = values[0];
		let newSelectedParameter = values[1];
		if (parameterType === this.constructor.#HELPER_OBJECT_NAME + ':text')
		{
			let input = menu.getRowInput(1);
			if (!input)
			{
				input = this.findInputInFormRobot(menu.getRowTarget(1));
			}

			newSelectedParameter = input?.value ?? '';
		}

		const systemExpression = this.#parseSystemExpression(newSelectedParameter);
		if (!Type.isStringFilled(systemExpression.title))
		{
			systemExpression.title = Loc.getMessage('BPSGVA_CLEAR');
		}
		const selectedVariableIndex = menu.target.getAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME);
		const selectedVariableOption = this.getVariableOptionFromVariableInput(selectedVariableIndex);
		systemExpression.title =
			this.#formatTitle(
				selectedVariableOption.property.Type,
				systemExpression.title,
				newSelectedParameter,
				selectedVariableOption.property.Options
			)
			?? systemExpression.title
		;
		this.#replaceTitle(target, systemExpression.title);

		const isExpressionOption = (
			['select', 'bool'].includes(selectedVariableOption.property.Type)
			&& parameterType !== this.constructor.#HELPER_OBJECT_NAME + ':text'
		);
		this.#setHiddenValue(
			target,
			systemExpression.id,
			{
				isMultiple: selectedVariableOption.property.Multiple,
				inputIndex: target.getAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME),
				isExpressionOption,
			}
		);
	}

	#onBeforeSelectItemInSelector(menu, event: BaseEvent)
	{
		const dialogItem = event.getData().item;
		menu.setRowValue(1, dialogItem.id, dialogItem.customData.get('title'));
	}

	#onAfterCreateItemInSelector(event: BaseEvent)
	{
		const item = event.getData().item;
		this.availableOptions.set(item.id, item);

		const groupItems = this.availableOptionsByGroupId.get(item.customData.groupId) ?? [];
		groupItems.push(item);
		this.availableOptionsByGroupId.set(item.customData.groupId, groupItems);
	}

	getParameterInputValue(role, index): string
	{
		const inputs = document.querySelectorAll('[data-role="' + role + '"]', index);
		const keys = Object.keys(inputs);
		for (const i in keys)
		{
			if (String(inputs[keys[i]].getAttribute(this.constructor.#INPUT_INDEX_ATTRIBUTE_NAME)) === String(index))
			{
				return inputs[keys[i]].value;
			}
		}

		return '';

	}

	addExpressionButtonRobot()
	{
		const buttonAdd = document.getElementById(this.constructor.#ADD_BUTTON_ID);
		buttonAdd.innerText = Loc.getMessage('BPSGVA_ADD_VARIABLE');
		Event.bind(buttonAdd, 'click', this.addAssignmentExpressionRobot.bind(this));
	}

	addExpressionButtonDesigner()
	{
		const button = Tag.render`<a href='#'>${Loc.getMessage('BPSGVA_PD_ADD')}</a>`;
		Event.bind(button, 'click', (event) => {
			this.addAssignmentExpressionDesigner();
			event.preventDefault();
		});

		Dom.insertAfter(button, this.addRowTable);
	}

	convertFieldExpression(option): string
	{
		if (this.isDocumentVisibility(option.groupId))
		{
			return '{{' + option.property.Name + '}}';
		}

		if (this.isGVariableVisibility(option.groupId))
		{
			const messages = this.visibilityMessages[this.constructor.#G_VAR_OBJECT_NAME];
			const visibility = option.property.Visibility;
			const name = option.property.Name;

			return '{{' + messages[visibility] + ': ' + name + '}}';
		}

		if (this.isGConstantVisibility(option.groupId))
		{
			const messages = this.visibilityMessages[this.constructor.#G_CONST_OBJECT_NAME];
			const visibility = option.property.Visibility;
			const name = option.property.Name;

			return '{{' + messages[visibility] + ': ' + name + '}}';
		}

		return option.id;
	}

	addAssignmentExpressionDesigner(variable, value)
	{
		const addRowTable = this.addRowTable;
		this.rowIndex++;

		const newRow = addRowTable.insertRow(-1);
		newRow.id = 'delete_row_' + this.rowIndex;

		const cellSelect = newRow.insertCell(-1);

		const newSelect = Tag.render`<select name="${this.constructor.#VARIABLE_ROLE + this.rowIndex}"></select>`;
		newSelect.setAttribute(this.constructor.#INDEX_ATTRIBUTE_NAME, this.rowIndex);
		const me = this;
		newSelect.onchange = function() {
			me.changeFieldTypeDesigner(
				this.getAttribute(me.constructor.#INDEX_ATTRIBUTE_NAME),
				this.options[this.selectedIndex].value,
				null
			);
		};

		const objectVisibilityMessages = this.visibilityMessages[this.constructor.#G_VAR_OBJECT_NAME];
		for (const visibility in objectVisibilityMessages)
		{
			const optgroupLabel = objectVisibilityMessages[visibility];
			const optgroup = Tag.render`<optgroup label="${Text.encode(optgroupLabel)}"></optgroup>`;

			const groupOptions = this.availableOptionsByGroupId.get(this.constructor.#G_VAR_OBJECT_NAME + ':' + visibility);
			if (!groupOptions){
				continue;
			}

			let optionNode;
			for (const i in groupOptions)
			{
				optionNode = Tag.render`
					<option value="${Text.encode(groupOptions[i]['id'])}">
						${Text.encode(groupOptions[i]['customData']['title'])}
					</option>
				`;
				Dom.append(optionNode, optgroup);
			}

			Dom.append(optgroup, newSelect);
		}

		newSelect.value = variable;
		if (newSelect.selectedIndex === -1)
		{
			newSelect.selectedIndex = 0;
		}
		Dom.append(newSelect, cellSelect);

		const cellSymbolEquals = newRow.insertCell(-1);
		cellSymbolEquals.innerHTML = '=';

		const cellValue = newRow.insertCell(-1);
		cellValue.id = 'id_td_variable_value_' + this.rowIndex;
		cellValue.innerHTML = '';

		const cellDeleteRow = newRow.insertCell(-1);
		cellDeleteRow.aligh = 'right';
		const deleteLink = Tag.render`<a href="#">${Text.encode(Loc.getMessage('BPSGVA_PD_DELETE'))}</a>`;
		const index = this.rowIndex;
		Event.bind(deleteLink, 'click', (event) =>
		{
			me.deleteConditionDesigner(index);
			event.preventDefault();
		});
		Dom.append(deleteLink, cellDeleteRow);

		if (Type.isArray(value))
		{
			for (const i in value)
			{
				const item = this.getOptionPropertiesRobot(value[i]);
				if (item.groupId === this.constructor.#HELPER_OBJECT_NAME + ':text')
				{
					continue;
				}
				value[i] = this.convertFieldExpression(item);
			}
		}
		else
		{
			const item = this.getOptionPropertiesRobot(value);
			if (item.groupId !== this.constructor.#HELPER_OBJECT_NAME + ':text')
			{
				value = this.convertFieldExpression(item);
			}
		}

		if (value === undefined)
		{
			value = null;
		}

		this.changeFieldTypeDesigner(this.rowIndex, newSelect.value, value);
	}

	changeFieldTypeDesigner(index, field, value)
	{
		BX.showWait();
		const valueTd = document.getElementById('id_td_variable_value_' + index);

		const separatingSymbol = field.indexOf(':');
		let fieldId = field;
		if (separatingSymbol !== -1){
			fieldId = field.slice(separatingSymbol + 1, field.length - 1);
		}

		objFieldsGlobalVar.GetFieldInputControl(
			objFieldsGlobalVar.arDocumentFields[fieldId],
			value,
			{'Field': fieldId, 'Form': this.formName},
			function(v) {
				if (v === undefined)
				{
					valueTd.innerHTML = '';
				}
				else
				{
					valueTd.innerHTML = v;
					if (!Type.isUndefined(BX.Bizproc.Selector))
					{
						BX.Bizproc.Selector.initSelectors(valueTd);
					}
				}
				BX.closeWait();
			},
			true
		);
	}

	deleteConditionDesigner(index)
	{
		const addrowTable = document.getElementById(this.constructor.#ROW_TABLE_ID);
		const count = addrowTable.rows.length;
		for (let i = 0; i < count; i++)
		{
			if (addrowTable.rows[i].id !== 'delete_row_' + index)
			{
				continue;
			}

			addrowTable.deleteRow(i);
			break;
		}
	}
}

namespace.SetGlobalVariableActivity = SetGlobalVariableActivity;