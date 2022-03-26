import {Reflection, Tag, Type} from 'main.core';
import {Dialog} from 'ui.entity-selector';
import {Globals} from 'bizproc.globals';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class MathOperationActivity
{
	isRobot: boolean;
	signedDocumentType: string;

	variables: object;
	constants: object;
	documentFields: object;
	operations: Array<string>;

	currentValues: object;

	visibilityMessages: object;

	addRowTable: HTMLDivElement | HTMLTableElement;

	gVarObjectName: string;
	gConstObjectName: string;
	documentObjectName: string;
	helperObjectName: string;
	operationObjectName: string;

	indexAttributeName: string;

	variableIdName: string;
	parameter1IdName: string;
	operationIdName: string;
	parameter2IdName: string;
	resultIdName: string;
	operationMenuIdName: string;

	options: Map<string, object>;
	optionsByGroup: Map<string, Array<string>>;
	rowIndex: number;

	availableTypes: Array<string>;

	constructor(options)
	{
		if (Type.isPlainObject(options))
		{
			this.isRobot = options.isRobot;
			this.signedDocumentType = options.signedDocumentType;

			this.variables = options.variables;
			this.constants = options.constants;
			this.documentFields = options.documentFields;
			this.operations = options.operations;

			this.currentValues = options.currentValues;
			this.visibilityMessages = options.visibilityMessages;

			this.addRowTable = options.addRowTable;
		}
	}

	init()
	{
		this.initObjectNames();
		this.initNodeAttributeNames();
		this.initNodeIdNames();
		this.initAvailableOptions();

		this.availableTypes = ['int', 'integer', 'double'];
		this.rowIndex = -1;

		let addCondition = this.isRobot ? 'addConditionRobot' : 'addConditionDesigner';

		if (Object.keys(this.currentValues).length <= 0)
		{
			this[addCondition]('variable', ['parameter', '+', 'parameter']);
		}
		for (let variableId in this.currentValues)
		{
			this[addCondition](variableId, this.currentValues[variableId]);
		}
	}

	initObjectNames()
	{
		this.gVarObjectName = 'GlobalVar';
		this.gConstObjectName = 'GlobalConst';
		this.documentObjectName = 'Document';
		this.operationObjectName = 'Operation';

		this.helperObjectName = 'Default';
	}

	isGVariable(visibility)
	{
		return visibility.startsWith(this.gVarObjectName);
	}

	isGConstant(visibility)
	{
		return visibility.startsWith(this.gConstObjectName);
	}

	isDocument(visibility)
	{
		return visibility.startsWith(this.documentObjectName);
	}

	initNodeAttributeNames()
	{
		this.indexAttributeName = 'bp_moa_index';
	}

	initNodeIdNames()
	{
		this.variableIdName = 'bp_moa_variable_';
		this.parameter1IdName = 'bp_moa_common1_';
		this.operationIdName = 'bp_moa_operation_';
		this.parameter2IdName = 'bp_moa_common2_';

		this.resultIdName = 'bp_moa_results_';
		this.operationMenuIdName = 'bp_moa_operations_menu_';
	}

	initAvailableOptions()
	{
		this.options = this.getAvailableOptions();
		this.optionsByGroup = this.getAvailableOptionsByGroup();
	}

	getAvailableOptions()
	{
		let options = new Map();

		this.fillOptions(this.variables, options);
		this.fillOptions(this.constants, options);
		this.fillOptions(this.documentFields, options);

		let source = this.operations;
		for (let i in source)
		{
			options.set(source[i], {
				title: source[i],
				groupId: this.operationObjectName,
				value: source[i]
			});
		}

		options.set('variable', {
			title: BX.message('BPMOA_CHOOSE_VARIABLE'),
			groupId: this.helperObjectName,
			value: ''
		});
		options.set('parameter', {
			title: BX.message('BPMOA_CHOOSE_PARAMETER'),
			groupId: this.helperObjectName,
			value: ''
		});
		options.set('operation', {
			title: '+',
			groupId: this.helperObjectName,
			value: '+'
		});

		return options;
	}

	fillOptions(source, options)
	{
		let optionId, optionsSource;
		for (let groupName in source)
		{
			optionsSource = source[groupName];
			if (optionsSource['children']) {
				optionsSource = optionsSource['children'];
			}
			for (let i in optionsSource)
			{
				optionId = optionsSource[i]['id'];
				options.set(optionId, this.createShortOptionProperty(optionId, optionsSource[i]));
			}
		}
	}

	createShortOptionProperty(id, property)
	{
		return {
			title: property['customData']['title'],
			groupId: property['customData']['groupId'],
			value: id
		};
	}

	getAvailableOptionsByGroup()
	{
		let options = new Map();
		let items;

		this.fillOptionsByGroupWithGlobals(this.variables, options, this.gVarObjectName);
		this.fillOptionsByGroupWithGlobals(this.constants, options, this.gConstObjectName);

		items = [];
		for (let i in this.documentFields) {
			items.push(this.documentFields[i]);
		}
		options.set(this.documentObjectName + ':' + this.documentObjectName, items);

		options.set(this.operationObjectName, this.getOperationGroupOptions());

		return options;
	}

	fillOptionsByGroupWithGlobals(source, options, topGroupName)
	{
		let key;
		for (let subGroupName in source)
		{
			key = topGroupName + ':' + subGroupName;
			options.set(key, source[subGroupName]);
		}
	}

	getOperationGroupOptions()
	{
		let items = [];
		let source = this.operations;
		let me = this;

		for (let i in source)
		{
			items.push({
				text: source[i],
				onclick: function (event, item)
				{
					let target = this.bindElement;
					if (target)
					{
						target.innerText = item.text;
						me.resolveHiddenInput(target, item.text, document.getElementById(
							me.resultIdName + target.getAttribute(me.indexAttributeName)
						));
						this.popupWindow.close();
					}
				}
			});
		}

		return items;
	}

	addConditionRobot(variableId, mathCondition)
	{
		let properties = this.getPropertiesInfo(variableId, mathCondition);

		let me = this;
		let addRowTable = this.addRowTable;
		this.rowIndex++;

		let newRow = BX.Tag.render`<div class="bizproc-automation-popup-settings"></div>`;

		let rowProperties = BX.Tag.render`
			<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text"></div>
		`;
		let rowInputs = BX.Tag.render`<div id="${this.resultIdName + this.rowIndex}"></div>`;

		/* region Variable Wrapper */

		let variableSpan = BX.Tag.render`
			<span class="bizproc-automation-popup-settings-link" id="${this.variableIdName + this.rowIndex}"></span>
		`;
		variableSpan.setAttribute(this.indexAttributeName, this.rowIndex);

		this.replaceTitleSelector(variableSpan, properties['variable'].title, rowInputs);

		BX.bind(variableSpan, 'click', function (event) {
			me.onFieldSelectClick(event, 'variable', me);
		});
		rowProperties.appendChild(variableSpan);

		/* endregion */

		rowProperties.appendChild(BX.Tag.render`<span> = </span>`);

		let parameter1Span = this.getParameterSpan(
			this.parameter1IdName + this.rowIndex,
			properties['parameter1'].title,
			rowInputs
		);
		rowProperties.appendChild(parameter1Span);

		/* region Operation Wrapper*/

		let operationSpan = BX.Tag.render`
			<span 
				class="bizproc-automation-popup-settings-link bizproc-automation-condition-joiner" 
				id="${this.operationIdName + this.rowIndex}"
			></span>
		`;
		operationSpan.setAttribute(this.indexAttributeName, this.rowIndex);

		this.replaceTitleSelector(operationSpan, properties['operation'].title, rowInputs);

		BX.bind(operationSpan, 'click', function (event) {
			me.onOperationSelectClick(event, me)
		});
		rowProperties.appendChild(operationSpan);

		/* endregion */

		let parameter2Span = this.getParameterSpan(
			this.parameter2IdName + this.rowIndex,
			properties['parameter2'].title,
			rowInputs
		);
		rowProperties.appendChild(parameter2Span);

		newRow.appendChild(rowProperties);
		newRow.appendChild(rowInputs);

		addRowTable.appendChild(newRow);
	}

	getPropertiesInfo(variableId, mathCondition)
	{
		let properties = {
			'variable': {value: variableId, defaultValue: 'variable'},
			'parameter1': {value: mathCondition[0], defaultValue: 'parameter'},
			'operation': {value: mathCondition[1], defaultValue: '+'},
			'parameter2': {value: mathCondition[2], defaultValue: 'parameter'}
		}

		let infos = {};
		for (let i in properties)
		{
			infos[i] = this.getPropertyInfo(properties[i].value, properties[i].defaultValue);
		}

		return infos;
	}

	getPropertyInfo(item, defaultValue)
	{
		if (this.options.get(item) === undefined)
		{
			item = Number(item);
			if (isNaN(item))
			{
				return {title: defaultValue}
			}
		}

		return {title: item};
	}

	getParameterSpan(id, title, rowInputs)
	{
		let parameterSpan = BX.Tag.render`<span class="bizproc-automation-popup-settings-link" id="${id}"></span>`;
		parameterSpan.setAttribute(this.indexAttributeName, this.rowIndex);

		this.replaceTitleSelector(parameterSpan, title, rowInputs);

		let me = this;
		BX.bind(parameterSpan, 'click', function (event) {
			me.onFieldSelectClick(event, 'all', me);
		});

		return parameterSpan;
	}

	onFieldSelectClick(event, type, me)
	{
		let target = event.target;
		let targetId = target.id;

		let itemValue = document.getElementById(targetId + '_input').value;
		let form = me.createFormForMenu(type, itemValue);

		let popup = new BX.PopupWindow(
			targetId + '_popup',
			target,
			{
				className: 'bizproc-automation-popup-set',
				autoHide: true,
				closeByEsc: true,
				offsetTop: 5,
				overlay: {backgroundColor: 'transparent'},
				content: form,
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
						className: "webform-button webform-button-create" ,
						events: {
							click: function() {
								let formInput = form.getElementsByTagName('input')[0];
								let rowInput = document.getElementById(
									me.resultIdName + target.getAttribute(me.indexAttributeName)
								);

								me.replaceTitleSelector(target, formInput.value, rowInput);
								popup.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('BIZPROC_AUTOMATION_CMP_CANCEL'),
						className : "popup-window-button-link",
						events : {
							click: function(){
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
			}
		);
		popup.show();
	}

	onOperationSelectClick(event, me)
	{
		let target = event.target;
		BX.Main.MenuManager.show(
			me.operationMenuIdName + Math.random(),
			target,
			me.optionsByGroup.get(me.operationObjectName) ?? [],
			{
				autoHide: true,
				className: 'bizproc-automation-inline-selector-menu',
				overlay: {backgroundColor: 'transparent'},
				minHeight: 50,
				minWidth: 40,
				events:
					{
						onPopupClose: function()
						{
							this.destroy();
						}
					}
			}
		);
	}

	replaceTitleSelector(target, itemValue, rowInputs)
	{
		let item = this.options.get(itemValue);
		let inputValue;

		if (target && (item !== undefined)) {
			target.innerText = item['title'];
			inputValue = item['value'];
		}
		else if (target && !isNaN(Number(itemValue)))
		{
			inputValue = Number(itemValue);
			if (String(target.id).startsWith(this.variableIdName) && inputValue === 0)
			{
				return;
			}
			target.innerText = inputValue;
		}
		else {
			return;
		}

		if (rowInputs)
		{
			this.resolveHiddenInput(target, inputValue, rowInputs);
		}
	}

	resolveHiddenInput(source, value, target)
	{
		let input = document.getElementById(source.id + '_input');
		if (input)
		{
			input.name = source.id;
			input.value = value;

			return;
		}

		let name = source.id;
		let id = name + '_input';

		target.appendChild(BX.Tag.render`<input type="hidden" id="${id}" name="${name}" value="${value}">`);
	}

	createFormForMenu(type, itemValue)
	{
		let me = this;

		let form = Tag.render`<form class="bizproc-automation-popup-select-block"></form>`;

		let fieldsListWrapper = Tag.render`<div class="bizproc-automation-popup-settings"></div>`;
		let labelFieldsList = Tag.render`<div class="bizproc-automation-robot-settings-title"></div>`;
		labelFieldsList.innerText = BX.message('BPMOA_LIST_OF_VALUES');

		let formInput = Tag.render`<input class="bizproc-automation-popup-input" type="hidden" style="width: 280px;">`;

		let fieldsSelectNode = Tag.render`<div class="bizproc-automation-popup-settings-dropdown" readonly="readonly"></div>`;
		BX.bind(fieldsSelectNode, 'click', function () {
			let items = me.optionsByGroup.get(visibilitySelect.value) ?? [];
			let visibilityInfo = me.getVisibilityInfoForDialog(visibilitySelect.value);

			let dialogOptions = me.getDialogOptions(items, visibilityInfo);
			dialogOptions['targetNode'] = this;
			dialogOptions['events'] = {
				'Item:onBeforeSelect': (event) => {
					let item = event.data.item;
					fieldsSelectNode.innerText = item.customData.get('title');
					formInput.value = item.id;
				},
				onHide: function(event) {
					event.target.destroy();
				},
				'Search:onItemCreateAsync': (event) => {
					return new Promise((resolve) => {
						let query = event.getData().searchQuery.query;
						let dialog = event.getTarget();

						me.onCreateGlobalsClick(dialog, visibilityInfo, query, me, resolve);
					});
				},
			};

			let dialog = new Dialog(dialogOptions);

			if (items.length <= 0) {
				dialog.setFooter(me.getFooter(visibilityInfo, dialog));
			}

			dialog.show();
		});

		let visibilityWrapper = Tag.render`<div class="bizproc-automation-popup-settings"></div>`;
		let visibilitySelect = Tag.render`<select class="bizproc-automation-popup-settings-dropdown"></select>`;
		BX.bind(visibilitySelect, 'change', function () {
			me.changeSelectForField(this.value, fieldsSelectNode, labelFieldsList, formInput);
		});

		let options = this.getVisibilityNamesForSelect(type);
		for (let groupId in options)
		{
			let optionNode = Tag.render`<option value="${BX.util.htmlspecialchars(groupId)}"></option>`;
			optionNode.innerText = options[groupId];

			visibilitySelect.appendChild(optionNode);
		}

		let item = this.options.get(itemValue);

		visibilitySelect.value = item ? item['groupId'] : this.helperObjectName + ':number';
		if (visibilitySelect.selectedIndex === -1) {
			visibilitySelect.selectedIndex = 0;
		}
		this.changeSelectForField(visibilitySelect.value, fieldsSelectNode, labelFieldsList, formInput);
		if (item && item['groupId'] !== this.helperObjectName)
		{
			fieldsSelectNode.innerText = item['title'];
			formInput.value = itemValue;
		}
		else
		{
			fieldsSelectNode.innerText = BX.message('BPMOA_EMPTY');
			formInput.value = itemValue;
		}

		visibilityWrapper.appendChild(Tag.render`
			<div class="bizproc-automation-robot-settings-title">
				${BX.util.htmlspecialchars(BX.message('BPMOA_TYPE_OF_PARAMETER'))}
			</div>
		`);
		visibilityWrapper.appendChild(visibilitySelect);

		fieldsListWrapper.appendChild(labelFieldsList);
		fieldsListWrapper.appendChild(fieldsSelectNode);
		fieldsListWrapper.append(formInput);

		form.appendChild(visibilityWrapper);
		form.appendChild(fieldsListWrapper);

		return form;
	}

	getVisibilityInfoForDialog(visibility)
	{
		let recentStubOptions = {};
		let searchStubOptions = {};
		let searchFooterOptions = {};
		let mode = '';
		let objectName = '';

		if (this.isGVariable(visibility))
		{
			recentStubOptions = {
				title: BX.message('BPMOA_GVARIABLE_NO_EXIST'),
				subtitle: BX.message('BPMOA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};

			searchStubOptions = {
				title: BX.message('BPMOA_GVARIABLE_NOT_FOUND'),
				subtitle: BX.message('BPMOA_CREATE_GVARIABLE_QUESTION'),
				arrow: true
			};

			searchFooterOptions = {
				label: BX.message('BPMOA_CREATE_GVARIABLE'),
			};

			mode = Globals.Manager.Instance.mode.variable;
			objectName = this.gVarObjectName;
		}
		else if (this.isGConstant(visibility))
		{
			recentStubOptions = {
				title: BX.message('BPMOA_GCONSTANT_NO_EXIST'),
				subtitle: BX.message('BPMOA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};

			searchStubOptions = {
				title: BX.message('BPMOA_GCONSTANT_NOT_FOUND'),
				subtitle: BX.message('BPMOA_CREATE_GCONSTANT_QUESTION'),
				arrow: true
			};

			searchFooterOptions = {
				label: BX.message('BPMOA_CREATE_GCONSTANT')
			};

			mode = Globals.Manager.Instance.mode.constant;
			objectName = this.gConstObjectName;
		}
		else if (this.isDocument(visibility))
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
		let options = {
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

		let extraOptions = {
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

	getFooter(visibilityInfo, dialog)
	{
		let me = this;
		let footer = Tag.render`
			<span class="ui-selector-footer-link ui-selector-footer-link-add" style="border: none">
				${BX.util.htmlspecialchars(visibilityInfo.searchFooterOptions.label)}
			</span>
		`;

		BX.bind(footer, 'click', () => {
			me.onCreateGlobalsClick(dialog, visibilityInfo, '', me);
		});

		return footer;
	}

	onCreateGlobalsClick(dialog, visibilityInfo, query,  me, resolve)
	{
		let visibility = visibilityInfo.visibility;
		let additionalContext = {
			visibility: visibility.slice(visibility.indexOf(':') + 1),
			availableTypes: me.availableTypes
		};
		Globals.Manager.Instance.createGlobals(visibilityInfo.mode, me.signedDocumentType, query, additionalContext)
			.then((slider) =>
			{
				let context = {
					'objectName': visibilityInfo.objectName,
					'visibility': visibilityInfo.visibility
				};
				me.onAfterCreateGlobals(dialog, slider, context);
				if (resolve)
				{
					resolve();
				}
			});
	}

	onAfterCreateGlobals(dialog, slider, context)
	{
		let info = slider.getData().entries();
		let keys = Object.keys(info);
		if (keys.length <= 0)
		{
			return;
		}

		let id = keys[0];
		let property = info[keys[0]];

		if (!this.availableTypes.includes(property['Type']))
		{
			return;
		}

		let item = {
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

		if (item.customData.groupId === context.visibility)
		{
			dialog.setFooter(null);
			dialog.addItem(item);
		}

		this.options.set(item.id, this.createShortOptionProperty(item.id, item));

		let groupItems = this.optionsByGroup.get(item.customData.groupId) ?? [];
		groupItems.push(item);
		this.optionsByGroup.set(item.customData.groupId, groupItems);
	}

	changeSelectForField(value, target, label, input)
	{
		if (value !== this.helperObjectName + ':number')
		{
			target.style.display = '';
			label.innerText = BX.message('BPMOA_LIST_OF_VALUES');
			target.innerText = BX.message('BPMOA_EMPTY');
			input.type = 'hidden';
			input.value = '';

			return;
		}

		label.innerText = BX.message('BPMOA_INPUT_NUMBER');
		target.style.display = 'none';
		input.type = 'text';
		input.value = '0';
	}

	getVisibilityNamesForSelect(type)
	{
		let list = {};
		let numberMessages = {};
		numberMessages[this.helperObjectName] = {
			'number': BX.message('BPMOA_NUMBER')
		};
		let source = Object.assign({}, this.visibilityMessages, numberMessages);

		for (let topGroupName in source) {
			if (type === 'variable' && topGroupName !== this.gVarObjectName) {
				continue;
			}
			for (let subGroupName in source[topGroupName]) {
				list[topGroupName + ':' + subGroupName] = source[topGroupName][subGroupName];
			}
		}

		return list;
	}

	addConditionDesigner(variableId, mathCondition)
	{
		let addRowTable = this.addRowTable;
		this.rowIndex++;

		let newRow;
		let cell, select;

		if (!mathCondition) {
			mathCondition = [NaN, null, NaN];
		}

		newRow = addRowTable.insertRow(-1);

		/* region Variable Wrapper */

		cell = newRow.insertCell(-1);
		cell.style.minWidth = '50px';

		select = BX.Tag.render`<select name="${this.variableIdName + this.rowIndex}" style="width: 100%;"></select>`;
		this.appendChildToSelectDesigner(select, 'variable');

		select.value = variableId;
		if (select.selectedIndex === -1) {
			select.selectedIndex = 0;
		}
		cell.appendChild(select);

		/* endregion*/

		cell = newRow.insertCell(-1);
		cell.innerText = '=';

		this.appendParameterSelectDesigner(newRow, this.parameter1IdName + this.rowIndex, mathCondition[0]);

		/* region Operation Wrapper */

		cell = newRow.insertCell(-1);
		cell.style.minWidth = '45px';
		select = BX.Tag.render`<select name="${this.operationIdName + this.rowIndex}" style="width: 100%"></select>`;
		for (let i in this.operations) {
			select.appendChild(BX.Tag.render`
				<option value="${BX.util.htmlspecialchars(this.operations[i])}">
					${BX.util.htmlspecialchars(this.operations[i])}
				</option>
			`);
		}
		select.value = mathCondition[1];
		if (select.selectedIndex === -1) {
			select.selectedIndex = 0;
		}
		cell.appendChild(select);

		/* endregion */

		this.appendParameterSelectDesigner(newRow, this.parameter2IdName + this.rowIndex, mathCondition[2]);
	}

	appendChildToSelectDesigner(select, type)
	{
		for (let objectName in this.visibilityMessages)
		{
			if (type === 'variable' && objectName !== this.gVarObjectName) {
				continue;
			}
			let objectVisibilityMessages = this.visibilityMessages[objectName];
			for (let visibility in objectVisibilityMessages)
			{
				let optgroupLabel = objectVisibilityMessages[visibility];
				let optgroup = BX.Tag.render`<optgroup label="${BX.util.htmlspecialchars(optgroupLabel)}"></optgroup>`;

				let groupOptions = this.optionsByGroup.get(objectName + ':' + visibility);
				if (!groupOptions){
					continue;
				}

				let optionNode, id, title;
				for (let i in groupOptions)
				{
					let groupOption = groupOptions[i];
					if (groupOption['children'])
					{
						for (let j in groupOption['children'])
						{
							id = groupOption['children'][j].id;
							title = groupOption['children'][j].customData.title;
							optionNode = BX.Tag.render`
								<option value="${BX.util.htmlspecialchars(id)}">
									${BX.util.htmlspecialchars(title)}
								</option>
							`;
							optgroup.appendChild(optionNode);
						}
					}
					else
					{
						id = groupOption['id'];
						title = groupOption['customData']['title'];
						optionNode = BX.Tag.render`
							<option value="${BX.util.htmlspecialchars(id)}">
								${BX.util.htmlspecialchars(title)}
							</option>
						`;
						optgroup.appendChild(optionNode);
					}
				}

				select.appendChild(optgroup);
			}
		}
	}

	changeInputDesigner(target, value)
	{
		if (target.options[target.selectedIndex].value === '')
		{
			target.after(BX.Tag.render`
				<input 
					type="text"
					name="${target.name}"
					style="width: 100px; height: 27px;" 
					value="${isFinite(value) ? value : 0}"
				>
			`);
		}
		else
		{
			let input = document.getElementsByName(target.name)[1];
			if (input) {
				input.remove();
			}
		}
	}

	appendParameterSelectDesigner(newRow, id, value)
	{
		let me = this;
		let cell = newRow.insertCell(-1);
		let select = BX.Tag.render`<select name="${BX.util.htmlspecialchars(id)}" style="width: 100%"></select>`;
		BX.bind(select, 'change', function (){
			me.changeInputDesigner(this, value);
		});

		select.appendChild(BX.Tag.render`<option value="">${BX.util.htmlspecialchars(BX.message('BPMOA_NUMBER'))}</option>`);
		this.appendChildToSelectDesigner(select);

		select.value = value;
		if (select.selectedIndex === -1) {
			select.selectedIndex = 0;
		}
		cell.appendChild(select);
		this.changeInputDesigner(select, value);
	}
}

namespace.MathOperationActivity = MathOperationActivity;