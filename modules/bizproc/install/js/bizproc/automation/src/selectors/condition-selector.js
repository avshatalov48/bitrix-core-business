import {
	Condition,
	ConditionGroup,
	Designer,
	getGlobalContext,
	InlineSelectorCondition,
	SelectorContext,
	SelectorManager,
} from 'bizproc.automation';
import { Dom, Type, Event, Loc, Runtime, Tag, Text} from 'main.core';

import type { ConditionSelectorOptions } from './types';
import { BaseEvent } from 'main.core.events';
import { Operator } from "bizproc.condition";

export class ConditionSelector
{
	#condition: ?Condition;
	#fields: Array<Object>;
	#joiner: string;
	#fieldPrefix: string;
	#rootGroupTitle: ?string;
	#onOpenFieldMenu: ?(BaseEvent) => void;
	#onOpenMenu: ?(BaseEvent) => void;
	#showValuesSelector: boolean;

	node: ?HTMLElement;
	objectNode: ?HTMLElement;
	fieldNode: ?HTMLElement;
	joinerNode: ?HTMLElement;
	labelNode: ?HTMLElement;
	valueNode: ?HTMLElement;
	#valueNode2: ?HTMLElement = null;

	popup: BX.PopupWindow;
	fieldDialog: ?InlineSelectorCondition;
	#selectedField;

	constructor(condition, options: ?ConditionSelectorOptions)
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
				});
			}

			if (options.joiner && options.joiner === ConditionGroup.JOINER.Or)
			{
				this.#joiner = ConditionGroup.JOINER.Or;
			}
			if (options.fieldPrefix)
			{
				this.#fieldPrefix = options.fieldPrefix;
			}

			this.#rootGroupTitle = options.rootGroupTitle;
			this.#onOpenFieldMenu = options.onOpenFieldMenu;
			this.#onOpenMenu = options.onOpenMenu;
			this.#showValuesSelector = options.showValuesSelector ?? true;
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

		const value = Type.isArrayFilled(this.#condition.value) ? this.#condition.value[0] : this.#condition.value;
		this.valueNode = this.#createValueNode(value)
		const conditionValueNode = this.valueNode;

		let conditionValueNode2;
		if (this.#condition.operator === Operator.BETWEEN)
		{
			const value2 =
				(Type.isArrayFilled(this.#condition.value) && this.#condition.value.length > 1)
					? this.#condition.value[1]
					: ''
			;

			this.#valueNode2 = this.#createValueNode(value2);
			conditionValueNode2 = this.#valueNode2;
		}

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
				conditionValueNode2,
				conditionJoinerNode,
				labelNode,
				removeButtonNode,
				joinerButtonNode
			]
		});

		return this.node;
	}

	#createValueNode(value: string): HTMLElement
	{
		return Tag.render`
			<input
				type="hidden"
				name="${Text.encode(this.#fieldPrefix + 'value[]')}"
				value="${Text.encode(value)}"
			>
		`;
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
			const valueLabel = this.#getValueLabel(field);

			Dom.append(
				Tag.render`<span class="bizproc-automation-popup-settings-link">${Text.encode(field.Name)}</span>`,
				this.labelNode
			);
			Dom.append(
				Tag.render`
					<span class="bizproc-automation-popup-settings-link">
						${Text.encode(this.getOperatorLabel(this.#condition.operator))}
					</span>
				`,
				this.labelNode
			);

			if (valueLabel)
			{
				Dom.append(
					Tag.render`<span class="bizproc-automation-popup-settings-link">${Text.encode(valueLabel)}</span>`,
					this.labelNode
				);
			}
		}
		else
		{
			Dom.append(
				Tag.render`
					<span class="bizproc-automation-popup-settings-link">
						${Text.encode(this.getOperatorLabel(Operator.EMPTY))}
					</span>
				`,
				this.labelNode
			);
		}
	}

	#getValueLabel(field): ?string
	{
		const operator = this.#condition.operator;
		const value = this.#condition.value;

		if (operator === 'between')
		{
			return (
				Loc.getMessage(
				'BIZPROC_AUTOMATION_ROBOT_CONDITION_BETWEEN_VALUE',
					{
						'#VALUE_1#': BX.Bizproc.FieldType.formatValuePrintable(
							field,
							Type.isArrayFilled(value) ? value[0] : value
						),
						'#VALUE_2#': BX.Bizproc.FieldType.formatValuePrintable(
							field,
							Type.isArrayFilled(value) ? value[1] : ''
						)
					}
				)
				?? ''
			);
		}
		else if(operator.indexOf('empty') < 0)
		{
			return BX.Bizproc.FieldType.formatValuePrintable(field, value);
		}

		return null;
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

		this.#selectedField = selectedField;

		fieldSelect.value = selectedField.Id;
		objectSelect.value = selectedField.ObjectId;
		fieldSelectLabel.textContent = selectedField.Name;

		const valueInput = this.#getValueNode(selectedField, this.#condition.value, this.#condition.operator);

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

							const valueInputs = valueWrapper.querySelectorAll('[name^="' + self.#fieldPrefix + 'value"]');

							if (valueInputs.length > 0)
							{
								let value = valueInputs[0].value;

								if (self.#condition.operator === Operator.BETWEEN && valueInputs.length > 1)
								{
									value = [valueInputs[0].value, valueInputs[1].value];
								}

								self.#condition.setValue(value);
							}
							else
							{
								self.#condition.setValue('');
							}

							self.setLabelText();

							const field = self.getField(self.#condition.object, self.#condition.field);
							if (field && field.Type === 'UF:address')
							{
								const input = valueWrapper.querySelector('[name="' + self.#fieldPrefix + 'value"]');
								self.#condition.setValue(input ? input.value : '');
							}
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
			const globalContext = getGlobalContext();
			const fields = Runtime.clone(
				Type.isArrayFilled(this.#fields) ? this.#fields : globalContext.document.getFields()
			);

			this.fieldDialog = new InlineSelectorCondition({
				context: new SelectorContext({
					fields,
					rootGroupTitle: globalContext.document.title,
				}),
				condition: this.#condition,
			});

			if (Type.isFunction(this.#onOpenFieldMenu))
			{
				this.fieldDialog.subscribe('onOpenMenu', this.#onOpenFieldMenu);
			}

			this.fieldDialog.subscribe('change', (event) => {
				const property = event.getData().field;
				fieldSelectLabel.textContent = property.Name
				fieldSelect.value = property.Id;
				objectSelect.value = property.ObjectId;
				BX.fireEvent(fieldSelect, 'change');
			});

			this.fieldDialog.renderTo(fieldSelectLabel);
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
				this.valueNode.value = Type.isArrayFilled(this.#condition.value) ? this.#condition.value[0] : this.#condition.value;
			}

			if (this.#condition.operator === Operator.BETWEEN)
			{
				const value2 = this.#condition.value[1] || '';
				if (this.#valueNode2)
				{
					this.#valueNode2.value = value2;
				}
				else
				{
					this.#valueNode2 = this.#createValueNode(value2);
					Dom.append(this.#valueNode2, this.node);
				}
			}
			else if (Type.isDomNode(this.#valueNode2))
			{
				Dom.remove(this.#valueNode2);
				this.#valueNode2 = null;
			}
		}
	}

	onFieldChange(selectNode: Node, conditionWrapper: Node, valueWrapper: Node, objectSelect)
	{
		const field = this.getField(objectSelect.value, selectNode.value);
		const operatorNode = this.createOperatorNode(field, valueWrapper);

		//clean value if field types are different
		if (field.Type !== this.#selectedField?.Type)
		{
			Dom.clean(valueWrapper);
		}
		this.#selectedField = field;

		//keep selected operator if possible
		if (this.getOperators(field['Type'], field['Multiple'])[conditionWrapper.firstChild.value])
		{
			operatorNode.value = conditionWrapper.firstChild.value;
		}

		conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
		this.onOperatorChange(operatorNode, field, valueWrapper);
	}

	onOperatorChange(selectNode: Node, field: Object, valueWrapper: HTMLElement)
	{
		const valueInput = valueWrapper.querySelector('[name^="' + this.#fieldPrefix + 'value"]');
		Dom.clean(valueWrapper);

		Dom.append(
			this.#getValueNode(field, valueInput?.value || this.#condition.value, selectNode.value),
			valueWrapper
		);
	}

	#getValueNode(field: {}, value, operator: string): any
	{
		if (operator === Operator.BETWEEN)
		{
			const valueNode1 = this.createValueNode(field, Type.isArrayFilled(value) ? value[0] : value);
			const valueNode2 = this.createValueNode(field, Type.isArrayFilled(value) ? value[1] : '');

			return Tag.render`
				<div>
					${valueNode1}
					<div>${ConditionGroup.JOINER.message('AND')}</div>
					${valueNode2}
				</div>
			`;
		}
		else if (operator.indexOf('empty') < 0)
		{
			return this.createValueNode(field, value);
		}

		return '';
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

	getOperators(fieldType, multiple): {}
	{
		const allLabels = Operator.getAllLabels();

		let list = {
			'!empty': allLabels[Operator.NOT_EMPTY],
			'empty': allLabels[Operator.EMPTY],
			'=': allLabels[Operator.EQUAL],
			'!=': allLabels[Operator.NOT_EQUAL],
		};
		switch (fieldType)
		{
			case 'file':
			case 'UF:crm':
			case 'UF:resourcebooking':
				list = {
					'!empty': allLabels[Operator.NOT_EMPTY],
					'empty': allLabels[Operator.EMPTY],
				};
				break;
			case 'bool':
			case 'select':
				if (multiple)
				{
					list[Operator.CONTAIN] = allLabels[Operator.CONTAIN];
					list[Operator.NOT_CONTAIN] = allLabels[Operator.NOT_CONTAIN];
				}
				else
				{
					//TODO: render multiple select in value selector
					//list['in'] = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
				}
				break;
			case 'user':
				list[Operator.IN] = allLabels[Operator.IN];
				list[Operator.NOT_IN] = allLabels[Operator.NOT_IN];
				list[Operator.CONTAIN] = allLabels[Operator.CONTAIN];
				list[Operator.NOT_CONTAIN] = allLabels[Operator.NOT_CONTAIN];
				break;
			default:
				list[Operator.IN] = allLabels[Operator.IN];
				list[Operator.NOT_IN] = allLabels[Operator.NOT_IN];
				list[Operator.CONTAIN] = allLabels[Operator.CONTAIN];
				list[Operator.NOT_CONTAIN] = allLabels[Operator.NOT_CONTAIN];
				list[Operator.GREATER_THEN] = allLabels[Operator.GREATER_THEN];
				list[Operator.GREATER_THEN_OR_EQUAL] = allLabels[Operator.GREATER_THEN_OR_EQUAL];
				list[Operator.LESS_THEN] = allLabels[Operator.LESS_THEN];
				list[Operator.LESS_THEN_OR_EQUAL] = allLabels[Operator.LESS_THEN_OR_EQUAL];
		}

		// todo: interface
		// if (['time', 'date', 'datetime', 'int', 'double'].includes(fieldType) || Type.isUndefined(fieldType))
		// {
		// 	list[Operator.BETWEEN] = allLabels[Operator.BETWEEN];
		// }

		return list;
	}

	getOperatorLabel(id): string
	{
		return Operator.getOperatorLabel(id);
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
				: getGlobalContext().document.getRawType()
		);
		const field = BX.clone(docField);
		field.Multiple = false;

		const valueNodes = BX.Bizproc.FieldType.renderControlPublic(
			docType,
			field,
			this.#fieldPrefix + 'value',
			value,
			false
		);

		valueNodes.querySelectorAll('[data-role]').forEach((node) => {
			const selector = SelectorManager.createSelectorByRole(node.dataset.role, {
				context: new SelectorContext({
					fields: getGlobalContext().document.getFields(),
					useSwitcherMenu: false,
					rootGroupTitle: this.#rootGroupTitle ?? getGlobalContext().document.title,
				}),
			});

			if (selector)
			{
				if (this.#showValuesSelector === true)
				{
					if (Type.isFunction(this.#onOpenMenu))
					{
						selector.subscribe('onOpenMenu', this.#onOpenMenu);
					}
					selector.renderTo(node);
				}
				else
				{
					selector.targetInput = node;
					selector.parseTargetProperties();
				}
			}
		});

		return valueNodes;
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

		this.labelNode = null;
		this.fieldNode = null;
		this.operatorNode = null;
		this.valueNode = null;
		this.#valueNode2 = null;
		this.node = null;

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