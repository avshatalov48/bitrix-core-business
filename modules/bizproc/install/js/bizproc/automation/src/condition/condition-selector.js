import { Condition, ConditionGroup, Designer } from 'bizproc.automation';
import { Dom, Type, Event, Loc } from 'main.core';

export class ConditionSelector
{
	#condition: ?Condition;
	#fields: Array<Object>;
	#joiner: string;
	#fieldPrefix: string;

	node: ?HTMLElement;
	objectNode: ?HTMLElement;
	fieldNode: ?HTMLElement;
	joinerNode: ?HTMLElement;
	labelNode: ?HTMLElement;

	popup: BX.PopupWindow;
	fieldDialog;
	#inlineSelector;

	constructor(condition, options)
	{
		this.#condition = condition;
		this.#fields = [];
		this.#joiner = ConditionGroup.JOINER.And;
		this.#fieldPrefix = 'condition_';

		if (Type.isPlainObject(options))
		{
			if (Type.isArray(options.fields))
			{
				this.#fields = options.fields.map(field => {
					field.ObjectId = 'Document';

					return field;
				})
			}

			if (options.joiner && options.joiner === ConditionGroup.JOINER.Or)
			{
				this.#joiner = ConditionGroup.JOINER.Or;
			}
			if (options.fieldPrefix)
			{
				this.#fieldPrefix = options.fieldPrefix;
			}
		}
	}

	createNode()
	{
		const conditionObjectNode = this.objectNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: this.#fieldPrefix + "object[]",
				value: this.#condition.object
			}
		});
		const conditionFieldNode = this.fieldNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: this.#fieldPrefix + "field[]",
				value: this.#condition.field
			}
		});
		const conditionOperatorNode = this.operatorNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: this.#fieldPrefix + "operator[]",
				value: this.#condition.operator
			}
		});
		const conditionValueNode = this.valueNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: this.#fieldPrefix + "value[]",
				value: this.#condition.value
			}
		});

		const conditionJoinerNode = this.joinerNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: this.#fieldPrefix + "joiner[]",
				value: this.#joiner
			}
		});

		const labelNode = this.labelNode = Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-link-wrapper"
			}
		});

		this.setLabelText();
		this.bindLabelNode();

		const removeButtonNode = Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-link-remove"
			},
			events: {
				click: this.removeCondition.bind(this)
			}
		});

		const joinerButtonNode = Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-link bizproc-automation-condition-joiner"
			},
			text: ConditionGroup.JOINER.message(this.#joiner),
		});

		Event.bind(joinerButtonNode, 'click', this.changeJoiner.bind(this, joinerButtonNode));

		this.node = Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-link-wrapper bizproc-automation-condition-wrapper" },
			children: [
				conditionObjectNode,
				conditionFieldNode,
				conditionOperatorNode,
				conditionValueNode,
				conditionJoinerNode,
				labelNode,
				removeButtonNode,
				joinerButtonNode
			]
		});

		return this.node;
	}

	init(condition: Condition)
	{
		this.#condition = condition;
		this.setLabelText();
		this.bindLabelNode();
	}

	setLabelText()
	{
		if (!this.labelNode || !this.#condition)
		{
			return;
		}

		Dom.clean(this.labelNode);

		if (this.#condition.field !== '')
		{
			const field = this.getField(this.#condition.object, this.#condition.field) || '?';
			const valueLabel =
				(this.#condition.operator.indexOf('empty') < 0)
					? BX.Bizproc.FieldType.formatValuePrintable(field, this.#condition.value)
					: null
			;

			this.labelNode.appendChild(Dom.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link"
				},
				text: field.Name
			}));
			this.labelNode.appendChild(Dom.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link"
				},
				text: this.getOperatorLabel(this.#condition.operator)
			}));
			if (valueLabel)
			{
				this.labelNode.appendChild(Dom.create("span", {
					attrs: {
						className: "bizproc-automation-popup-settings-link"
					},
					text: valueLabel
				}));
			}
		}
		else
		{
			this.labelNode.appendChild(Dom.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link"
				},
				text: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
			}));
		}
	}

	bindLabelNode()
	{
		if (this.labelNode)
		{
			Event.bind(this.labelNode, 'click', this.onLabelClick.bind(this));
		}
	}

	onLabelClick()
	{
		this.showPopup();
	}

	showPopup()
	{
		if (this.popup)
		{
			this.popup.show();
			return;
		}

		const fields = this.filterFields();

		const objectSelect = Dom.create('input', {
			attrs: {
				type: 'hidden',
				className: 'bizproc-automation-popup-settings-dropdown'
			}
		});
		const fieldSelect = Dom.create('input', {
			attrs: {
				type: 'hidden',
				className: 'bizproc-automation-popup-settings-dropdown'
			}
		});
		const fieldSelectLabel = Dom.create('div', {
			attrs: {
				className: 'bizproc-automation-popup-settings-dropdown',
				readonly: 'readonly'
			},
			children: [fieldSelect]
		});

		Event.bind(
			fieldSelectLabel,
			'click',
			this.onFieldSelectorClick.bind(this, fieldSelectLabel, fieldSelect, fields, objectSelect)
		);

		let selectedField = this.getField(this.#condition.object, this.#condition.field);
		if (!this.#condition.field)
		{
			selectedField = fields[0];
		}
		fieldSelect.value = selectedField.Id;
		objectSelect.value = selectedField.ObjectId;
		fieldSelectLabel.textContent = selectedField.Name;

		const valueInput = (this.#condition.operator.indexOf('empty') < 0)
			? this.createValueNode(selectedField, this.#condition.value) : null;

		const valueWrapper = Dom.create('div', {
			attrs: {
				className: 'bizproc-automation-popup-settings'
			},
			children: [valueInput]
		});

		const operatorSelect = this.createOperatorNode(selectedField, valueWrapper);
		const operatorWrapper = Dom.create('div', {
			attrs: {
				className: 'bizproc-automation-popup-settings'
			},
			children: [operatorSelect]
		});

		if (this.#condition.field !== '')
		{
			operatorSelect.value = this.#condition.operator;
		}

		const form = Dom.create("form", {
			attrs: { className: "bizproc-automation-popup-select-block" },
			children: [
				Dom.create('div', {
					attrs: {
						className: 'bizproc-automation-popup-settings'
					},
					children: [fieldSelectLabel]
				}),
				operatorWrapper,
				valueWrapper
			]
		});

		Event.bind(fieldSelect, 'change', this.onFieldChange.bind(
			this,
			fieldSelect,
			operatorWrapper,
			valueWrapper,
			objectSelect
		));

		const self = this;
		this.popup = new BX.PopupWindow('bizproc-automation-popup-set', this.labelNode, {
			className: 'bizproc-automation-popup-set',
			autoHide: false,
			closeByEsc: true,
			closeIcon: false,
			titleBar: false,
			angle: true,
			offsetLeft: 45,
			overlay: { backgroundColor: 'transparent' },
			content: form,
			buttons: [
				new BX.PopupWindowButton({
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHOOSE'),
					className: "webform-button webform-button-create" ,
					events: {
						click()
						{
							self.#condition.setObject(objectSelect.value);
							self.#condition.setField(fieldSelect.value);
							self.#condition.setOperator(operatorWrapper.firstChild.value);

							const valueInput = valueWrapper.querySelector('[name^="' + self.#fieldPrefix + 'value"]');

							if (valueInput)
							{
								self.#condition.setValue(valueInput.value);
							}
							else
							{
								self.#condition.setValue('');
							}

							self.setLabelText();
							self.updateValueNode();
							this.popupWindow.close();
						}
					}
				}),
				new BX.PopupWindowButtonLink({
					text : Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
					className : "popup-window-button-link-cancel",
					events : {
						click()
						{
							this.popupWindow.close()
						}
					}
				})
			],
			events: {
				onPopupClose()
				{
					this.destroy();
					if (self.fieldDialog)
					{
						self.fieldDialog.destroy();
						delete(self.fieldDialog);
					}
					delete(self.popup);
				}
			}
		});

		this.popup.show();
	}

	onFieldSelectorClick(fieldSelectLabel, fieldSelect, fields, objectSelect, event)
	{
		if (!this.fieldDialog)
		{
			this.fieldDialog = new BX.Bizproc.Automation.Selector.InlineSelectorCondition(fieldSelectLabel, fields, function(property)
			{
				fieldSelectLabel.textContent = property.Name
				fieldSelect.value = property.Id;
				objectSelect.value = property.ObjectId;
				BX.fireEvent(fieldSelect, 'change');
			}, this.#condition);
		}

		this.fieldDialog.openMenu(event);
	}

	updateValueNode()
	{
		if (this.#condition)
		{
			if (this.objectNode)
			{
				this.objectNode.value = this.#condition.object;
			}
			if (this.fieldNode)
			{
				this.fieldNode.value = this.#condition.field;
			}
			if (this.operatorNode)
			{
				this.operatorNode.value = this.#condition.operator;
			}
			if (this.valueNode)
			{
				this.valueNode.value = this.#condition.value;
			}
		}
	}

	onFieldChange(selectNode: Node, conditionWrapper: Node, valueWrapper: Node, objectSelect)
	{
		const field = this.getField(objectSelect.value, selectNode.value);
		const operatorNode = this.createOperatorNode(field, valueWrapper);
		conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
		this.onOperatorChange(operatorNode, field, valueWrapper);
	}

	onOperatorChange(selectNode: Node, field: Object, valueWrapper: Node)
	{
		Dom.clean(valueWrapper);

		if (selectNode.value.indexOf('empty') < 0)
		{
			const valueNode = this.createValueNode(field);
			valueWrapper.appendChild(valueNode);
		}
	}

	// TODO - fix this method
	getField(object, id)
	{
		let field;
		const robot = Designer.getInstance().robot;
		const component = Designer.getInstance().component;
		const tpl = robot? robot.getTemplate() : null;

		switch (object)
		{
			case 'Document':
				for (let i = 0; i < this.#fields.length; ++i)
				{
					if (id === this.#fields[i].Id)
					{
						field = this.#fields[i];
					}
				}
				break;
			case 'Template':
				if (tpl && component && component.triggerManager)
				{
					field = component.triggerManager.getReturnProperty(tpl.getStatusId(), id);
				}
				break;
			case 'Constant':
				if (tpl)
				{
					field = tpl.getConstant(id);
				}
				break;
			case 'GlobalConst':
				if (component)
				{
					field = component.getConstant(id);
				}
				break;
			case 'GlobalVar':
				if (component)
				{
					field = component.getGVariable(id);
				}
				break;
			default:
				var foundRobot = tpl? tpl.getRobotById(object) : null;
				if (foundRobot)
				{
					field = foundRobot.getReturnProperty(id);
				}
				break;
		}

		return field || {
			Id: id,
			ObjectId: object,
			Name: id,
			Type: 'string',
			Expression: id,
			SystemExpression: '{='+object+':'+id+'}'
		};
	}

	getOperators(fieldType, multiple)
	{
		let list = {
			'!empty': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
			'empty': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY'),
			'=': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EQ'),
			'!=': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NE')
		};
		switch (fieldType)
		{
			case 'file':
			case 'UF:crm':
			case 'UF:resourcebooking':
				list = {
					'!empty': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
					'empty': Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
				};
				break;
			case 'bool':
			case 'select':
				if (multiple)
				{
					list['contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
					list['!contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
				}
				else
				{
					//TODO: render multiple select in value selector
					//list['in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
				}
				break;
			case 'user':
				list['in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
				list['!in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
				list['contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
				list['!contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
				break;
			default:
				list['in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
				list['!in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
				list['contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
				list['!contain'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
				list['>'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_GT');
				list['>='] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_GTE');
				list['<'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_LT');
				list['<='] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_LTE');
		}

		return list;
	}

	getOperatorLabel(id)
	{
		return this.getOperators()[id];
	}

	filterFields()
	{
		const filtered = [];
		for (let i = 0; i < this.#fields.length; ++i)
		{
			const type = this.#fields[i]['Type'];

			if (
				type === 'bool'
				|| type === 'date'
				|| type === 'datetime'
				|| type === 'double'
				|| type === 'file'
				|| type === 'int'
				|| type === 'select'
				|| type === 'string'
				|| type === 'text'
				|| type === 'user'
				|| type === 'UF:money'
				|| type === 'UF:crm'
				|| type === 'UF:resourcebooking'
				|| type === 'UF:url'
			)
			{
				filtered.push(this.#fields[i]);
			}
			else
			{
				//TODO add support of custom types
			}
		}

		return filtered;
	}

	createValueNode(docField, value)
	{
		const docType = (
			Designer.getInstance().component
				? Designer.getInstance().component.document.getRawType()
				: BX.Bizproc.Automation.API.documentType
		);
		const field = BX.clone(docField);
		field.Multiple = false;

		return BX.Bizproc.FieldType.renderControl(
			docType,
			field,
			this.#fieldPrefix + 'value',
			value
		);
	}

	createOperatorNode(field, valueWrapper)
	{
		const select = Dom.create('select', {
			attrs: {className: 'bizproc-automation-popup-settings-dropdown'}
		});

		const operatorList = this.getOperators(field['Type'], field['Multiple']);
		for (var operatorId in operatorList)
		{
			if (!operatorList.hasOwnProperty(operatorId))
			{
				continue;
			}

			select.appendChild(Dom.create('option', {
				props: {value: operatorId},
				text: operatorList[operatorId]
			}));
		}

		Event.bind(select, 'change', this.onOperatorChange.bind(
			this,
			select,
			field,
			valueWrapper
		));

		return select;
	}

	removeCondition(event: Event)
	{
		this.#condition = null;
		Dom.remove(this.node);
		this.labelNode = this.fieldNode = this.operatorNode = this.valueNode = this.node = null;

		event.stopPropagation();
	}

	changeJoiner(btn: Element, event: Event)
	{
		this.#joiner = (this.#joiner === ConditionGroup.JOINER.Or ? ConditionGroup.JOINER.And : ConditionGroup.JOINER.Or);
		btn.textContent = ConditionGroup.JOINER.message(this.#joiner);

		if (this.joinerNode)
		{
			this.joinerNode.value = this.#joiner;
		}

		event.preventDefault();
	}

	destroy()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}
}