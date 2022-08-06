import {Reflection, Tag, Type, Loc, Event, Dom, Text} from 'main.core';
import {Globals} from 'bizproc.globals';
import {Dialog} from 'ui.entity-selector';
import 'bp_field_type';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class SetGlobalVariableActivity {
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

	gVarObjectName: string;
	gConstObjectName: string;
	documentObjectName: string;
	helperObjectName: string;

	addRowTableNodeId: string;
	addButtonNodeId: string;
	hiddenInputsNodeId: string;
	variableRole: string;
	parameterRole: string;

	indexAttributeName: string;
	inputIndexAttributeName: string;

	availableOptions: Map;
	availableOptionsByGroupId: Map;

	rowIndex: number;
	numberOfTypes: number;

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
		this.initObjectNames();
		this.initNodeIdNames();
		this.initNodeAttributeNames();
		this.initAvailableOptions();

		this.rowIndex = -1;
		this.numberOfTypes = 9;

		const addAssignmentExpression = this.isRobot ? 'addAssignmentExpressionRobot' : 'addAssignmentExpressionDesigner';
		if (Object.keys(this.currentValues).length <= 0) {
			this[addAssignmentExpression]();
		}
		for (const variableExpression in this.currentValues) {
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

	initObjectNames()
	{
		this.gVarObjectName = 'GlobalVar';
		this.gConstObjectName = 'GlobalConst';
		this.documentObjectName = 'Document';
		this.helperObjectName = 'Default';
	}

	isGVariableVisibility(visibility)
	{
		return visibility.startsWith(this.gVarObjectName);
	}

	isGConstantVisibility(visibility)
	{
		return visibility.startsWith(this.gConstObjectName);
	}

	isDocumentVisibility(visibility)
	{
		return visibility.startsWith(this.documentObjectName);
	}

	isHelperVisibility(visibility)
	{
		return visibility.startsWith(this.helperObjectName);
	}

	initNodeIdNames()
	{
		this.addRowTableNodeId = 'bp_sgva_addrow_table';
		this.addButtonNodeId = 'bp_sgva_add_button';
		this.hiddenInputsNodeId = 'bp_sgva_results_';
		this.variableRole = 'bp_sgva_variable_';
		this.parameterRole = 'bp_sgva_value_';
	}

	initNodeAttributeNames()
	{
		this.indexAttributeName = 'bp_sgva_index';
		this.inputIndexAttributeName = 'bp_sgva_input_index';
	}

	initAvailableOptions()
	{
		this.availableOptions = this.getAvailableOptions();
		this.availableOptionsByGroupId = this.getAvailableOptionsByGroup();
	}

	getAvailableOptions()
	{
		const options = new Map();
		this.fillOptions(this.variables, options);
		this.fillOptions(this.constants, options);
		this.fillOptions(this.documentFields, options);

		options.set('variable', {
			id: '',
			title: BX.message('BPSGVA_VARIABLE'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.helperObjectName,
				title: BX.message('BPSGVA_VARIABLE')
			}
		});

		options.set('parameter', {
			id: '',
			title: BX.message('BPSGVA_PARAMETER'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.helperObjectName,
				title: BX.message('BPSGVA_PARAMETER')
			}
		});

		options.set('clear', {
			id: '',
			title: BX.message('BPSGVA_CLEAR'),
			customData: {
				property: {Type: 'string', Multiple: false},
				groupId: this.helperObjectName,
				title: BX.message('BPSGVA_CLEAR')
			}
		});

		return options;
	}

	fillOptions(source, options)
	{
		let optionId, optionProperty, optionsSource;

		for (const groupName in source) {
			optionsSource = source[groupName];

			if (optionsSource['children']) {
				optionsSource = optionsSource['children'];
			}

			for (const i in optionsSource) {
				optionId = optionsSource[i]['id'];
				optionProperty = optionsSource[i];
				options.set(optionId, optionProperty);
			}
		}
	}

	getAvailableOptionsByGroup()
	{
		const options = new Map();
		this.fillOptionsByGroupWithGlobals(this.variables, options, this.gVarObjectName);
		this.fillOptionsByGroupWithGlobals(this.constants, options, this.gConstObjectName);

		const items = [];
		for (const i in this.documentFields) {
			items.push(this.documentFields[i]);
		}
		options.set(this.documentObjectName + ':' + this.documentObjectName, items);

		return options;
	}

	fillOptionsByGroupWithGlobals(source, options, topGroupName)
	{
		for (const subGroupName in source) {
			const key = topGroupName + ':' + subGroupName;
			options.set(key, source[subGroupName]);
		}
	}

	addAssignmentExpressionRobot(variableId, values) {
		if (Type.isString(values)) {
			values = {0: values};
		}

		const incomingData = {variable: variableId, values};
		this.modifyIncomingDataRobot(incomingData);

		const addRowTable = this.addRowTable;
		this.rowIndex++;
		const newRow = Tag.render`<div class="bizproc-automation-popup-settings"></div>`;

		const rowInputs = Tag.render`<div id="${this.hiddenInputsNodeId + this.rowIndex}"></div>`;

		const dataRow = Tag.render`
			<div 
				class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text" 
				style="display: flex; align-items: flex-start"
			></div>
		`;
		dataRow.appendChild(this.createVariableRowRobot(incomingData.variable, rowInputs));

		const parameterRowWrapper = Tag.render`<div class="bizproc-automation-popup-settings-title"></div>`;
		parameterRowWrapper.setAttribute('data-role', this.parameterRole + this.rowIndex);

		if (incomingData.values.length <= 0)
		{
			const option = BX.clone(this.getOptionPropertiesRobot('clear'));
			option['multiple'] = incomingData.variable.property.Multiple;
			option['type'] = incomingData.variable.property.Type;
			option['inputIndex'] = 0;
			parameterRowWrapper.appendChild(this.createParameterRowRobot(this.rowIndex, option, rowInputs));
		}
		for (const i in incomingData.values)
		{
			const option = BX.clone(incomingData.values[i]);
			option['multiple'] = incomingData.variable.property.Multiple;
			option['type'] = incomingData.variable.property.Type;
			option['inputIndex'] = i;
			parameterRowWrapper.appendChild(this.createParameterRowRobot(this.rowIndex, option, rowInputs));
		}

		if (incomingData.variable.property.Multiple && incomingData.variable.property.Type !== 'user')
		{
			parameterRowWrapper.appendChild(this.createAddParameterRowRobot(this.rowIndex, incomingData.values.length));
		}

		dataRow.appendChild(parameterRowWrapper);

		newRow.appendChild(dataRow);
		newRow.appendChild(rowInputs);

		addRowTable.appendChild(newRow);
	}

	modifyIncomingDataRobot(incomingData)
	{
		const option = this.getOptionPropertiesRobot(incomingData.variable);
		if (incomingData.variable === undefined || option.groupId === this.helperObjectName + ':text')
		{
			incomingData.variable = BX.clone(this.getOptionPropertiesRobot('variable'));
			const valueOption = BX.clone(this.getOptionPropertiesRobot('parameter'));
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

		incomingData.variable = BX.clone(option);
		incomingData.values = valuesOptions;
	}

	getOptionPropertiesRobot(optionId)
	{
		const option = this.availableOptions.get(optionId);
		if (option === undefined) {
			return this.getDefaultOptionProperties(optionId);
		}

		return this.getShortOptionProperties(option);
	}

	getDefaultOptionProperties(optionId)
	{
		return {
			id: optionId,
			property: {Type: 'string', Multiple: false},
			groupId: this.helperObjectName + ':text',
			title: optionId
		};
	}

	getShortOptionProperties(option)
	{
		return {
			id: option.id,
			property: option.customData.property,
			groupId: option.customData.groupId,
			title: option.customData.title
		};
	}

	getIncomingValuesSelect(incomingData)
	{
		const option = this.getOptionPropertiesRobot(incomingData.variable);
		let title, valueOption, valuesOptions = [], isExpressionOption;

		for (const i in incomingData.values)
		{
			title = BX.message('BPSGVA_CLEAR');
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

	getIncomingValuesBool(incomingData)
	{
		let title, valueOption, valuesOptions = [];

		for (const i in incomingData.values)
		{
			let isExpressionOption = false;
			switch (incomingData.values[i])
			{
				case 'Y':
					title = BX.message('BPSGVA_BOOL_YES');
					break;
				case 'N':
					title = BX.message('BPSGVA_BOOL_NO');
					break;
				case '':
					title = BX.message('BPSGVA_CLEAR');
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

	createVariableRowRobot(variableData, rowInputs)
	{
		const div = Tag.render`<div></div>`;
		const variableNode = Tag.render`
			<span class="bizproc-automation-popup-settings-link setglobalvariableactivity-underline"></span>
		`;
		variableNode.setAttribute('data-role', this.variableRole + this.rowIndex);
		variableNode.setAttribute(this.indexAttributeName, String(this.rowIndex));

		const data = this.getDataForTitleReplacement(variableData, variableNode.getAttribute('data-role'));
		data.multiple = false;
		data.type = 'string';
		data.inputIndex = 0;
		if (data.title === '')
		{
			data.title = BX.message('BPSGVA_VARIABLE');
		}
		this.replaceTitleAndHiddenInputRobot(variableNode, data, rowInputs);

		BX.bind(variableNode, 'click', BX.proxy(this.onVariableSelectClickRobot, this));

		div.appendChild(variableNode);

		return div;
	}

	createParameterRowRobot(index, valueData, rowInputs)
	{
		const wrapper = Tag.render`
			<div class="bizproc-automation-popup-settings-title setglobalvariableactivity-parameter-wrapper"></div>
		`;

		const equal = Tag.render`
			<div class="bizproc-automation-popup-settings-title setglobalvariableactivity-symbol-equal"> = </div>
		`;

		const div = Tag.render`<div></div>`;
		const parameter = Tag.render`
			<span class="bizproc-automation-popup-settings-link setglobalvariableactivity-underline"></span>
		`;
		parameter.setAttribute('data-role', this.parameterRole + index)
		parameter.setAttribute(this.indexAttributeName, String(index));
		parameter.setAttribute(this.inputIndexAttributeName, String(valueData.inputIndex));

		wrapper.appendChild(equal);
		div.appendChild(parameter);
		wrapper.appendChild(div);

		const data = BX.clone(this.getDataForTitleReplacement(valueData, parameter.getAttribute('data-role')));
		data.isExpressionOption = valueData.isExpressionOption;
		if (data.title === '')
		{
			data.title = BX.message('BPSGVA_CLEAR');
		}
		this.replaceTitleAndHiddenInputRobot(parameter, data, rowInputs);

		BX.bind(parameter, 'click', BX.proxy(
			(event) => {
				this.onParameterSelectClickRobot(event, valueData.inputIndex)
			},
			this)
		);

		return wrapper;
	}

	getDataForTitleReplacement(data, role)
	{
		return {
			inputValue: data.id,
			title: data.title,
			multiple: data.multiple,
			type: data.type,
			inputIndex: data.inputIndex,
			property: data.property,
			role
		};
	}

	replaceTitleAndHiddenInputRobot(target, data, rowInputs)
	{
		target.innerText = this.getTitleForReplacement(data) ?? data.title;
		this.replaceHiddenInputRobot(data, rowInputs);
	}

	getTitleForReplacement(data)
	{
		const type = data.type;
		const title = data.title;
		const value = data.inputValue;

		if (type === 'bool')
		{
			if (['Y', 'N'].includes(value))
			{
				return BX.Bizproc.FieldType.formatValuePrintable({Type: type}, value);
			}

			return null;
		}

		return BX.Bizproc.FieldType.formatValuePrintable({Type: type}, title);
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
				const inputIndex = input[inputKeys[i]].getAttribute(this.inputIndexAttributeName);
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
		input.setAttribute(this.inputIndexAttributeName, String(data.inputIndex));
		rowInputs.appendChild(input);
	}

	onVariableSelectClickRobot(event)
	{
		const target = event.target;
		const inputValue = this.getVariableInputValue(target.getAttribute('data-role'));
		const index = target.getAttribute(this.indexAttributeName);

		const form = this.createFormForMenuRobot('variable', inputValue, index);

		const me = this;
		const popup = new BX.PopupWindow(target.id + '_popup', target, {
			className: 'bizproc-automation-popup-set',
			autoHide: true,
			closeByEsc: true,
			offsetTop: 5,
			overlay: {backgroundColor: 'transparent'},
			content: form,
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
					className: 'webform-button webform-button-create',
					events: {
						click: function ()
						{
							const rowInputs = document.getElementById(me.hiddenInputsNodeId + index);
							const input = me.findInputInFormRobot(form);

							const data = me.getDataForTitleReplacement(
								me.getOptionPropertiesRobot(input.value),
								target.getAttribute('data-role')
							);
							data.multiple = false;
							data.type = 'string';
							data.inputIndex = target.getAttribute(me.inputIndexAttributeName) ?? '0';
							if (data.title === '')
							{
								data.title = BX.message('BPSGVA_VARIABLE');
							}

							me.replaceTitleAndHiddenInputRobot(target, data, rowInputs);
							me.changeParameterExpressionRobot(index, data);

							popup.close();
						}
					}
				}),
				new BX.PopupWindowButtonLink({
					text: BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
					className: 'popup-window-button-link',
					events: {
						click: function ()
						{
							popup.close();
						}
					}
				})
			],
			events: {
				onPopupClose: function ()
				{
					this.destroy();
				}
			}
		});

		popup.show();
	}

	getVariableInputValue(role)
	{
		const inputRole = role + '_input';
		const inputs = document.querySelectorAll('[data-role="' + inputRole + '"]');

		return (inputs.length >= 1) ? inputs['0'].value : '';
	}

	createFormForMenuRobot(typeMenu, inputValue, index)
	{
		const me = this;

		const form = Tag.render`<form class="bizproc-automation-popup-select-block"></form>`;

		const fieldsListWrapper = Tag.render`<div class="bizproc-automation-popup-settings"></div>`;
		const labelFieldsList = Tag.render`<div class="bizproc-automation-robot-settings-title"></div>`;
		labelFieldsList.innerText = BX.message('BPSGVA_LIST_OF_VALUES');

		const formInputWrapper = this.createInputForMenuFormRobot(typeMenu, index, inputValue);
		const formInput = this.findInputInFormRobot(formInputWrapper);

		const filterType = (typeMenu === 'variable')
			? 'string'
			: this.getVariableOptionFromVariableInput(index).property.Type
		;

		const fieldsSelectNode = Tag.render`<div class="bizproc-automation-popup-settings-dropdown" readonly="readonly"></div>`;
		BX.bind(fieldsSelectNode, 'click', () => {
			const items = me.availableOptionsByGroupId.get(visibilitySelect.value) ?? [];
			const filterItems = me.filterItemsInStandardMenuRobot(filterType, items);
			const visibilityInfo = me.getVisibilityInfoForDialog(visibilitySelect.value);

			const dialogOptions = me.getDialogOptions(filterItems, visibilityInfo);
			dialogOptions['targetNode'] = fieldsSelectNode;
			dialogOptions['events'] = {
				'Item:onBeforeSelect': (event) => {
					const dialogItem = event.data.item;
					fieldsSelectNode.innerText = dialogItem.customData.get('title');
					if (!formInput)
					{
						Dom.append(Tag.render`<input type="hidden" name="bp_sgva_field_input" value="${Text.encode(dialogItem.id)}">`, formInputWrapper);

						return;
					}
					if (formInput.tagName !== 'SELECT')
					{
						formInput.value = dialogItem.id;
					}
					else
					{
						me.resolveAdditionOptionInSelectRobot(formInput, dialogItem);
					}
				},
				onHide: (event) => {
					event.target.destroy();
				},
				'Search:onItemCreateAsync': (event) => {
					return new Promise((resolve) => {
						const query = event.getData().searchQuery.query;
						const dialog = event.getTarget();
						const context = {
							visibilityInfo,
							index
						};

						me.onCreateGlobalsClick(dialog, context, query, me, resolve);
					});
				},
			};

			const dialog = new Dialog(dialogOptions);

			if (filterItems.length <= 0) {
				dialog.setFooter(me.getFooter({visibilityInfo, index}, dialog));
			}

			dialog.show();
		});

		const visibilityWrapper = Tag.render`<div class="bizproc-automation-popup-settings"></div>`;

		const visibilitySelect = Tag.render`<select class="bizproc-automation-popup-settings-dropdown"></select>`;
		BX.bind(visibilitySelect, 'change', BX.proxy(() => {
			me.changeParameterSelectInFormRobot(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInputWrapper)
		}, this));

		const visibilityOptions = this.getVisibilityNamesForSelect(typeMenu);
		for (const groupId in visibilityOptions)
		{
			const optionNode = Tag.render`
				<option value="${BX.util.htmlspecialchars(groupId)}">
					${BX.util.htmlspecialchars(visibilityOptions[groupId])}
				</option>
			`;
			visibilitySelect.appendChild(optionNode);
		}

		const option = this.getOptionPropertiesRobot(inputValue);
		if (option.groupId === this.helperObjectName)
		{
			option.groupId = this.helperObjectName + ':text';
			option.id = inputValue;
		}

		visibilitySelect.value = this.getVisibilityRelativeToVariableType(option, filterType);
		if (visibilitySelect.selectedIndex === -1)
		{
			visibilitySelect.selectedIndex = 0;
		}
		this.changeParameterSelectInFormRobot(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInputWrapper);
		fieldsSelectNode.innerText = (option.title !== '') ? option.title : BX.message('BPSGVA_EMPTY');

		if (visibilitySelect.value === this.helperObjectName + ':text' && option.groupId !== this.helperObjectName + ':text')
		{
			if (formInput)
			{
				formInput.value = this.convertFieldExpression(option);
			}
		}
		else
		{
			if (formInput)
			{
				formInput.value = option.id;
			}
		}

		visibilityWrapper.appendChild(Tag.render`
			<div class="bizproc-automation-robot-settings-title">
				${BX.util.htmlspecialchars(BX.message('BPSGVA_TYPE_OF_PARAMETER'))}
			</div>
		`);
		visibilityWrapper.appendChild(visibilitySelect);

		fieldsListWrapper.appendChild(labelFieldsList);
		fieldsListWrapper.appendChild(fieldsSelectNode);
		fieldsListWrapper.append(formInputWrapper);

		form.appendChild(visibilityWrapper);
		form.appendChild(fieldsListWrapper);

		return form;
	}

	createInputForMenuFormRobot(type, index, inputValue)
	{
		if (type === 'variable')
		{
			const wrapper = Tag.render`<div class="bizproc-automation-popup-select"></div>`;
			const input = Tag.render`<input class="bizproc-automation-popup-input" type="hidden" style="width: 280px">`;
			wrapper.appendChild(input);

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

		wrapper.style.width = '280px';
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
			input.style.width = '100%';
		}

		return wrapper;
	}

	getVariableOptionFromVariableInput(index)
	{
		const variableInput = document.querySelector('[data-role="' + this.variableRole + index + '_input"]');
		const variableId = variableInput ? variableInput.value: '';

		return this.getOptionPropertiesRobot(variableId);
	}

	findInputInFormRobot(form)
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
			input.appendChild(opt);
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

	filterItemsInStandardMenuRobot(variableType, items)
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

	getFilterByVariableType(type)
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

	getVisibilityInfoForDialog(visibility)
	{
		let recentStubOptions = {};
		let searchStubOptions = {};
		let searchFooterOptions = {};
		let mode = '';
		let objectName = '';

		if (this.isGVariableVisibility(visibility))
		{
			recentStubOptions = {
				title: BX.message('BPSGVA_GVARIABLE_NO_EXIST'),
				subtitle: BX.message('BPSGVA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};

			searchStubOptions = {
				title: BX.message('BPSGVA_GVARIABLE_NOT_FOUND'),
				subtitle: BX.message('BPSGVA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};

			searchFooterOptions = {
				label: BX.message('BPSGVA_CREATE_GVARIABLE'),
			};

			mode = Globals.Manager.Instance.mode.variable;
			objectName = this.gVarObjectName;
		}
		else if (this.isGConstantVisibility(visibility))
		{
			recentStubOptions = {
				title: BX.message('BPSGVA_GCONSTANT_NO_EXIST'),
				subtitle: BX.message('BPSGVA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};

			searchStubOptions = {
				title: BX.message('BPSGVA_GCONSTANT_NOT_FOUND'),
				subtitle: BX.message('BPSGVA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};

			searchFooterOptions = {
				label: BX.message('BPSGVA_CREATE_GCONSTANT')
			};

			mode = Globals.Manager.Instance.mode.constant;
			objectName = this.gConstObjectName;
		}
		else if (this.isDocumentVisibility(visibility))
		{
			objectName = this.documentObjectName;
		}

		return {
			recentStubOptions,
			searchStubOptions,
			searchFooterOptions,
			mode,
			objectName,
			visibility
		};
	}

	getDialogOptions(items, visibilityInfo)
	{
		const options = {
			width: 480,
			height: 300,
			multiple: false,
			dropdownMode: true,
			enableSearch: true,
			showAvatars: false,
			compactView: true,
			items: items,
			tagSelectorOptions: {
				textBoxWidth: 400
			}
		};

		const extraOptions = {
			recentTabOptions: {
				stub: true,
				icon: '',
				stubOptions: visibilityInfo.recentStubOptions
			},
			searchTabOptions: {
				stub: true,
				stubOptions: visibilityInfo.searchStubOptions
			},
			searchOptions: {
				allowCreateItem: true,
				footerOptions: visibilityInfo.searchFooterOptions
			}
		};

		if (
			visibilityInfo.objectName === this.gVarObjectName
			|| visibilityInfo.objectName === this.gConstObjectName
		)
		{
			return Object.assign(options, extraOptions);
		}

		return options;
	}

	getFooter(context, dialog)
	{
		const me = this;
		const footer = Tag.render`
			<span class="ui-selector-footer-link ui-selector-footer-link-add" style="border: none">
				${BX.util.htmlspecialchars(context.visibilityInfo.searchFooterOptions.label)}
			</span>
		`;

		BX.bind(footer, 'click', () => {
			me.onCreateGlobalsClick(dialog, context, '', me);
		});

		return footer;
	}

	onCreateGlobalsClick(dialog, context, query,  me, resolve)
	{
		const variableType = me.getVariableOptionFromVariableInput(context.index).property.Type;
		const optionAvailableTypes = me.getFilterByVariableType(variableType);

		const visibility = context.visibilityInfo.visibility;
		const additionalContext = {
			visibility: visibility.slice(visibility.indexOf(':') + 1),
			availableTypes: optionAvailableTypes
		};
		Globals.Manager.Instance.createGlobals(context.visibilityInfo.mode, me.signedDocumentType, query, additionalContext)
			.then((slider) =>
			{
				const newContext = {
					'objectName': context.visibilityInfo.objectName,
					'visibility': context.visibilityInfo.visibility,
					'index': context.index
				};
				me.onAfterCreateGlobals(dialog, slider, newContext);
				if (resolve)
				{
					resolve();
				}
			})
		;
	}

	onAfterCreateGlobals(dialog, slider, context)
	{
		const info = slider.getData().entries();
		const keys = Object.keys(info);
		if (keys.length <= 0)
		{
			return;
		}

		const id = keys[0];
		const property = BX.clone(info[keys[0]]);

		property.Multiple = property.Multiple === 'Y';
		const variableType = this.getVariableOptionFromVariableInput(context.index).property.Type;
		const optionAvailableTypes = this.getFilterByVariableType(variableType);

		const item = {
			entityId: 'bp',
			tabs: 'recents',
			title: property['Name'],
			id: '{=' + context.objectName + ':' + id + '}',
			customData: {
				groupId: context.objectName + ':' + property['Visibility'],
				property: property,
				title: property['Name']
			}
		};

		if (
			item.customData.groupId === context.visibility
			&& optionAvailableTypes.includes(item.customData.property.Type)
		)
		{
			dialog.setFooter(null);
			dialog.addItem(item);
		}

		this.availableOptions.set(item.id, item);

		const groupItems = this.availableOptionsByGroupId.get(item.customData.groupId) ?? [];
		groupItems.push(item);
		this.availableOptionsByGroupId.set(item.customData.groupId, groupItems);
	}

	changeParameterSelectInFormRobot(visibility, target, label, inputWrapper)
	{
		if (visibility !== this.helperObjectName + ':text')
		{
			target.style.display = 'inline-block';
			target.innerText = BX.message('BPSGVA_EMPTY');
			label.innerText = BX.message('BPSGVA_LIST_OF_VALUES');
			inputWrapper.style.display = 'none';
		}
		else
		{
			target.style.display = 'none';
			label.innerText = BX.message('BPSGVA_INPUT_TEXT');
			inputWrapper.style.display = '';
		}

		const input = this.findInputInFormRobot(inputWrapper);
		if (input)
		{
			input.value = '';
		}
	}

	getVisibilityNamesForSelect(type)
	{
		const list = {};
		const textMessages = {};
		textMessages[this.helperObjectName] = {
			'text': BX.message('BPSGVA_TEXT')
		};

		const source = Object.assign({}, this.visibilityMessages, textMessages);

		for (const topGroupName in source) {
			if (type === 'variable' && topGroupName !== this.gVarObjectName) {
				continue;
			}
			for (const subGroupName in source[topGroupName]) {
				list[topGroupName + ':' + subGroupName] = source[topGroupName][subGroupName];
			}
		}

		return list;
	}

	getVisibilityRelativeToVariableType(option, variableType)
	{
		const optionAvailableTypes = this.getFilterByVariableType(variableType);
		if (
			option.groupId === this.helperObjectName + ':text'
			|| optionAvailableTypes.includes(option.property.Type)
		)
		{
			return option.groupId;
		}

		return this.helperObjectName + ':text';
	}

	changeParameterExpressionRobot(index, variable)
	{
		const parameterNode = document.querySelector('[data-role="' + this.parameterRole + index + '"]');
		this.deleteOldValueRowsRobot(parameterNode);
		const rowInputs = document.getElementById(this.hiddenInputsNodeId + index);

		const option = BX.clone(this.getOptionPropertiesRobot('parameter'));

		option['multiple'] = variable.property.Multiple;
		option['inputIndex'] = '0';

		parameterNode.appendChild(this.createParameterRowRobot(index, option, rowInputs));

		if (variable.property.Multiple && variable.property.Type !== 'user')
		{
			const inputIndex = (variable.inputIndex !== '0') ? variable.inputIndex : '1';
			parameterNode.appendChild(this.createAddParameterRowRobot(index, inputIndex));
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

	createAddParameterRowRobot(index, inputIndex)
	{
		const addWrapper = Tag.render`<div class="bizproc-automation-popup-settings-title" style="display:flex;"></div>`
		const addExpression = Tag.render`
			<div class="bizproc-type-control-clone-btn setglobalvariableactivity-dashed-grey setglobalvariableactivity-add-parameter">
				${BX.util.htmlspecialchars(BX.message('BPSGVA_ADD_PARAMETER'))}
			</div>
		`;
		addExpression.setAttribute(this.indexAttributeName, String(index));
		addExpression.setAttribute(this.inputIndexAttributeName, String(inputIndex));
		BX.bind(addExpression, 'click', BX.proxy(this.onAddParameterButtonClickRobot, this));

		addWrapper.appendChild(addExpression);

		return addWrapper;
	}

	onAddParameterButtonClickRobot(event)
	{
		const index = event.target.getAttribute(this.indexAttributeName);
		const rowInputs = document.getElementById(this.hiddenInputsNodeId + index);
		const inputIndex = event.target.getAttribute(this.inputIndexAttributeName);

		const option = BX.clone(this.getOptionPropertiesRobot('parameter'));
		option['multiple'] = true;
		option['inputIndex'] = inputIndex;

		event.target.parentNode.before(this.createParameterRowRobot(index, option, rowInputs));
		event.target.setAttribute(this.inputIndexAttributeName, Number(inputIndex) + 1);
	}

	onParameterSelectClickRobot(event, inputIndex)
	{
		const target = event.target;
		const inputValue = this.getParameterInputValue(target.getAttribute('data-role') + '_input', inputIndex);
		const index = target.getAttribute(this.indexAttributeName);

		const form = this.createFormForMenuRobot('all', inputValue, index);

		const me = this;
		const popup = new BX.PopupWindow(target.id + '_popup', target, {
			className: 'bizproc-automation-popup-set',
			closeByEsc: true,
			autoHide: true,
			offsetTop: 5,
			overlay: {backgroundColor: 'transparent'},
			content: form,
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
					className: 'webform-button webform-button-create',
					events: {
						click: function ()
						{
							const rowInputs = document.getElementById(me.hiddenInputsNodeId + index);
							const variableOption = me.getVariableOptionFromVariableInput(index);

							const input = me.findInputInFormRobot(form);
							let data;
							if (input.tagName === 'SELECT')
							{
								const id = input.selectedOptions[0].value;
								const title = (id !== '') ? input.selectedOptions[0].text : BX.message('BPSGVA_CLEAR');
								data = me.getDataForTitleReplacement(
									{id, title},
									target.getAttribute('data-role')
								);
								data.isExpressionOption = (input.selectedOptions[0].getAttribute('data-role') === 'expression');
							}
							else
							{
								const option = BX.clone(me.getOptionPropertiesRobot(input.value));
								if (option.groupId === me.helperObjectName)
								{
									option.id = input.value;
									option.title = input.value;
								}

								data = me.getDataForTitleReplacement(
									me.getOptionPropertiesRobot(input.value),
									target.getAttribute('data-role')
								);
							}

							data.inputIndex = target.getAttribute(me.inputIndexAttributeName) ?? '0';
							data.multiple = variableOption.property.Multiple;
							data.type = variableOption.property.Type;
							if (data.title === '')
							{
								data.title = BX.message('BPSGVA_CLEAR');
							}

							me.replaceTitleAndHiddenInputRobot(target, data, rowInputs);

							popup.close();
						}
					}
				}),
				new BX.PopupWindowButtonLink({
					text: BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
					className: 'popup-window-button-link',
					events: {
						click: function ()
						{
							popup.close();
						}
					}
				})
			],
			events: {
				onPopupClose: function ()
				{
					this.destroy();
				}
			}
		});

		popup.show();
	}

	getParameterInputValue(role, index)
	{
		const inputs = document.querySelectorAll('[data-role="' + role + '"]', index);
		const keys = Object.keys(inputs);
		for (const i in keys)
		{
			if (String(inputs[keys[i]].getAttribute(this.inputIndexAttributeName)) === String(index))
			{
				return inputs[keys[i]].value;
			}
		}

		return '';

	}

	addExpressionButtonRobot()
	{
		const buttonAdd = document.getElementById(this.addButtonNodeId);
		buttonAdd.innerText = BX.message('BPSGVA_ADD_VARIABLE');
		BX.bind(buttonAdd, 'click', BX.proxy(function () {
			this.addAssignmentExpressionRobot()
		}, this));
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

	convertFieldExpression(option)
	{
		if (this.isDocumentVisibility(option.groupId))
		{
			return '{{' + option.property.Name + '}}';
		}

		if (this.isGVariableVisibility(option.groupId))
		{
			const messages = this.visibilityMessages[this.gVarObjectName];
			const visibility = option.property.Visibility;
			const name = option.property.Name;

			return '{{' + messages[visibility] + ': ' + name + '}}';
		}

		if (this.isGConstantVisibility(option.groupId))
		{
			const messages = this.visibilityMessages[this.gConstObjectName];
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

		const newSelect = Tag.render`<select name="${this.variableRole + this.rowIndex}"></select>`;
		newSelect.setAttribute(this.indexAttributeName, this.rowIndex);
		const me = this;
		newSelect.onchange = function() {
			me.changeFieldTypeDesigner(
				this.getAttribute(me.indexAttributeName),
				this.options[this.selectedIndex].value,
				null
			);
		};

		const objectVisibilityMessages = this.visibilityMessages[this.gVarObjectName];
		for (const visibility in objectVisibilityMessages)
		{
			const optgroupLabel = objectVisibilityMessages[visibility];
			const optgroup = Tag.render`<optgroup label="${BX.util.htmlspecialchars(optgroupLabel)}"></optgroup>`;

			const groupOptions = this.availableOptionsByGroupId.get(this.gVarObjectName + ':' + visibility);
			if (!groupOptions){
				continue;
			}

			let optionNode;
			for (const i in groupOptions)
			{
				optionNode = Tag.render`
					<option value="${BX.util.htmlspecialchars(groupOptions[i]['id'])}">
						${BX.util.htmlspecialchars(groupOptions[i]['customData']['title'])}
					</option>
				`;
				optgroup.appendChild(optionNode);
			}

			newSelect.appendChild(optgroup);
		}

		newSelect.value = variable;
		if (newSelect.selectedIndex === -1)
		{
			newSelect.selectedIndex = 0;
		}
		cellSelect.appendChild(newSelect);

		const cellSymbolEquals = newRow.insertCell(-1);
		cellSymbolEquals.innerHTML = '=';

		const cellValue = newRow.insertCell(-1);
		cellValue.id = 'id_td_variable_value_' + this.rowIndex;
		cellValue.innerHTML = '';

		const cellDeleteRow = newRow.insertCell(-1);
		cellDeleteRow.aligh = 'right';
		const deleteLink = Tag.render`<a href="#">${BX.util.htmlspecialchars(BX.message('BPSGVA_PD_DELETE'))}</a>`;
		const index = this.rowIndex;
		Event.bind(deleteLink, 'click', (event) =>
		{
			me.deleteConditionDesigner(index);
			event.preventDefault();
		});
		cellDeleteRow.appendChild(deleteLink);

		if (Type.isArray(value))
		{
			for (const i in value)
			{
				const item = this.getOptionPropertiesRobot(value[i]);
				if (item.groupId === this.helperObjectName + ':text')
				{
					continue;
				}
				value[i] = this.convertFieldExpression(item);
			}
		}
		else
		{
			const item = this.getOptionPropertiesRobot(value);
			if (item.groupId !== this.helperObjectName + ':text')
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
					if (typeof BX.Bizproc.Selector !== 'undefined')
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
		const addrowTable = document.getElementById(this.addRowTableNodeId);
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