import {
	Condition,
	ConditionGroup,
	Designer,
	getGlobalContext,
	InlineSelectorCondition,
	SelectorContext,
	SelectorManager,
} from 'bizproc.automation';
import { Dom, Type, Event, Loc, Runtime, Tag, Text } from 'main.core';
import { Popup } from 'main.popup';
import { Button, CancelButton } from 'ui.buttons';

import type { ConditionSelectorOptions } from './types';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Operator } from 'bizproc.condition';

import 'ui.icon-set.main';
import 'ui.icon-set.actions';

export class ConditionSelector extends EventEmitter
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

	popup: Popup;
	fieldDialog: ?InlineSelectorCondition;
	#selectedField;

	constructor(condition, options: ?ConditionSelectorOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation.Condition');

		this.#condition = condition;
		this.#fields = [];
		this.#joiner = ConditionGroup.JOINER.And;
		this.#fieldPrefix = 'condition_';

		if (Type.isPlainObject(options))
		{
			if (Type.isArray(options.fields))
			{
				this.#fields = options.fields.map((field) => {
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
		const value = Type.isArrayFilled(this.#condition.value) ? this.#condition.value[0] : this.#condition.value;
		const conditionValueNode = this.#createValueNode(value);

		const conditionValueNode2 = (
			this.#condition.operator === Operator.BETWEEN
				? this.#createValueNode(
					Type.isArrayFilled(this.#condition.value) && this.#condition.value.length > 1
						? this.#condition.value[1]
						: '',
				)
				: ''
		);

		const {
			root,
			conditionObjectNode,
			conditionFieldNode,
			conditionOperatorNode,
			labelNode,
		} = Tag.render`
			<div class="bizproc-automation-popup-settings__condition-selector ui-draggable--item">
				<div class="bizproc-automation-popup-settings__condition-item">
					<input
						ref="conditionObjectNode"
						type="hidden"
						name="${Text.encode(`${this.#fieldPrefix}object[]`)}"
						value="${Text.encode(this.#condition.object)}"
					/>
					<input
						ref="conditionFieldNode"
						type="hidden"
						name="${Text.encode(`${this.#fieldPrefix}field[]`)}"
						value="${Text.encode(this.#condition.field)}"
					/>
					<input
						ref="conditionOperatorNode"
						type="hidden"
						name="${Text.encode(`${this.#fieldPrefix}operator[]`)}"
						value="${Text.encode(this.#condition.operator)}"
					/>
					${conditionValueNode}
					${conditionValueNode2}
					<div class="bizproc-automation-popup-settings__condition-item_draggable">
						<div class="ui-icon-set --more-points"></div>
					</div>
					<div
						ref="labelNode"
						class="bizproc-automation-popup-settings__condition-item_content"
					></div>
					${this.#createRemoveButton()}
				</div>
				${this.#createJoinerSwitcher()}
			</div>
		`;

		this.node = root;
		this.objectNode = conditionObjectNode;
		this.fieldNode = conditionFieldNode;
		this.operatorNode = conditionOperatorNode;
		this.valueNode = conditionValueNode;
		this.#valueNode2 = conditionValueNode2 === '' ? null : conditionValueNode2;
		this.labelNode = labelNode;

		this.setLabelText();
		this.bindLabelNode();

		return this.node;
	}

	#createValueNode(value: string): HTMLElement
	{
		return Tag.render`
			<input
				type="hidden"
				name="${Text.encode(`${this.#fieldPrefix}value[]`)}"
				value="${Text.encode(value)}"
			>
		`;
	}

	#createRemoveButton(): HTMLDivElement
	{
		const { root, removeButtonNode } = Tag.render`
			<div class="bizproc-automation-popup-settings__condition-item_close">
				<div ref="removeButtonNode" class="ui-icon-set --cross-20"></div>
			</div>
		`;
		Event.bind(removeButtonNode, 'click', this.removeCondition.bind(this));

		return root;
	}

	#createJoinerSwitcher(): HTMLDivElement
	{
		const { root, switcherBtnAnd, switcherBtnOr, inputNode } = Tag.render`
			<div class="bizproc-automation-popup-settings__condition-switcher">
				<div class="bizproc-automation-popup-settings__condition-switcher_wrapper">
					<span
						ref="switcherBtnAnd"
						class="bizproc-automation-popup-settings__condition-switcher_btn ${this.#joiner === 'AND' ? '--active' : ''}"
					>
						${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_AND')}
					</span>
					<span
						ref="switcherBtnOr"
						class="bizproc-automation-popup-settings__condition-switcher_btn ${this.#joiner === 'OR' ? '--active' : ''}"
					>
						${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_OR')}
					</span>
				</div>
				<input
					ref="inputNode"
					type="hidden"
					name="${Text.encode(`${this.#fieldPrefix}joiner[]`)}"
					value="${Text.encode(this.#joiner)}"
				/>
			</div>
		`;
		this.joinerNode = inputNode;

		Event.bind(root, 'click', () => {
			this.#joiner = (this.#joiner === ConditionGroup.JOINER.Or ? ConditionGroup.JOINER.And : ConditionGroup.JOINER.Or);
			if (this.joinerNode)
			{
				this.joinerNode.value = this.#joiner;
			}

			Dom.toggleClass(switcherBtnOr, '--active');
			Dom.toggleClass(switcherBtnAnd, '--active');
		});

		return root;
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

		if (this.#condition.field === '')
		{
			Dom.append(
				Tag.render`
					<span class="bizproc-automation-popup-settings__condition-text">
						${Text.encode(this.getOperatorLabel(Operator.EMPTY))}
					</span>
				`,
				this.labelNode,
			);
		}
		else
		{
			const field = this.getField(this.#condition.object, this.#condition.field) || '?';
			const valueLabel = this.#getValueLabel(field);

			Dom.append(
				Tag.render`<span class="bizproc-automation-popup-settings__condition-text">${Text.encode(field.Name)}</span>`,
				this.labelNode,
			);
			Dom.append(
				Tag.render`
					<span class="bizproc-automation-popup-settings__condition-text">
						${Text.encode(this.getOperatorLabel(this.#condition.operator))}
					</span>
				`,
				this.labelNode,
			);

			if (valueLabel)
			{
				Dom.append(
					Tag.render`<span class="bizproc-automation-popup-settings__condition-text">${Text.encode(valueLabel)}</span>`,
					this.labelNode,
				);
			}
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
					'BIZPROC_AUTOMATION_ROBOT_CONDITION_BETWEEN_VALUE_1',
					{
						'#VALUE_1#': BX.Bizproc.FieldType.formatValuePrintable(
							field,
							Type.isArrayFilled(value) ? value[0] : value,
						),
						'#VALUE_2#': BX.Bizproc.FieldType.formatValuePrintable(
							field,
							Type.isArrayFilled(value) ? value[1] : '',
						),
					},
				)
			);
		}

		if (!operator.includes('empty'))
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

		const objectSelect = Tag.render`<input type="hidden" class="bizproc-automation-popup-settings-dropdown"/>`;
		const { root: fieldSelectLabel, fieldSelect } = Tag.render`
			<div class="bizproc-automation-popup-settings-dropdown" readonly="readonly">
				<input ref="fieldSelect" type="hidden" class="bizproc-automation-popup-settings-dropdown"/>
			</div>
		`;

		Event.bind(
			fieldSelectLabel,
			'click',
			this.onFieldSelectorClick.bind(this, fieldSelectLabel, fieldSelect, fields, objectSelect),
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
		const valueWrapper = Tag.render`<div class="bizproc-automation-popup-settings">${valueInput}</div>`;

		const operatorSelect = this.createOperatorNode(selectedField, valueWrapper);
		if (this.#condition.field !== '')
		{
			operatorSelect.value = this.#condition.operator;
		}

		const { root: form, operatorWrapper } = Tag.render`
			<form class="bizproc-automation-popup-select-block">
				<div class="bizproc-automation-popup-settings">${fieldSelectLabel}</div>
				<div ref="operatorWrapper" class="bizproc-automation-popup-settings">${operatorSelect}</div>
				${valueWrapper}
			</form>
		`;
		Event.bind(fieldSelect, 'change', this.onFieldChange.bind(
			this,
			fieldSelect,
			operatorWrapper,
			valueWrapper,
			objectSelect,
		));

		this.popup = new Popup({
			id: 'bizproc-automation-popup-set',
			bindElement: this.labelNode,
			content: form,
			closeByEsc: true,
			buttons: [
				new Button({
					color: Button.Color.PRIMARY,
					text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CHOOSE_BUTTON_CAPS'),
					onclick: () => {
						this.#condition.setObject(objectSelect.value);
						this.#condition.setField(fieldSelect.value);
						this.#condition.setOperator(operatorWrapper.firstChild.value);

						const valueInputs = valueWrapper.querySelectorAll(`[name^="${this.#fieldPrefix}value"]`);

						if (valueInputs.length > 0)
						{
							let value = valueInputs[valueInputs.length - 1].value;

							if (this.#condition.operator === Operator.BETWEEN && valueInputs.length > 1)
							{
								value = [valueInputs[0].value, valueInputs[1].value];
							}

							this.#condition.setValue(value);
						}
						else
						{
							this.#condition.setValue('');
						}

						this.setLabelText();

						const field = this.getField(this.#condition.object, this.#condition.field);
						if (field && field.Type === 'UF:address')
						{
							const input = valueWrapper.querySelector(`[name="${this.#fieldPrefix}value"]`);
							this.#condition.setValue(input ? input.value : '');
						}
						this.updateValueNode();
						this.popup.close();
					},
				}),
				new CancelButton({
					text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CANCEL_BUTTON_CAPS'),
					onclick: () => {
						this.popup.close();
					},
				}),
			],
			className: 'bizproc-automation-popup-set',
			closeIcon: false,
			autoHide: false,
			events: {
				onClose: () => {
					this.popup.destroy();
					if (this.fieldDialog)
					{
						this.fieldDialog.destroy();
						delete this.fieldDialog;
					}

					delete this.popup;
				},
			},
			titleBar: false,
			angle: true,
			overlay: { backgroundColor: 'transparent' },
			offsetLeft: 45,
		});

		this.popup.show();
	}

	onFieldSelectorClick(fieldSelectLabel, fieldSelect, fields, objectSelect, event)
	{
		if (!this.fieldDialog)
		{
			const globalContext = getGlobalContext();
			const fields = Runtime.clone(
				Type.isArrayFilled(this.#fields) ? this.#fields : globalContext.document.getFields(),
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
				fieldSelectLabel.textContent = property.Name;
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
				this.valueNode.value = (
					Type.isArrayFilled(this.#condition.value)
						? this.#condition.value[0]
						: this.#condition.value
				);
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

		// clean value if field types are different
		if (field.Type !== this.#selectedField?.Type)
		{
			Dom.clean(valueWrapper);
		}
		this.#selectedField = field;

		// keep selected operator if possible
		if (this.getOperators(field.Type, field.Multiple)[conditionWrapper.firstChild.value])
		{
			operatorNode.value = conditionWrapper.firstChild.value;
		}

		conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
		this.onOperatorChange(operatorNode, field, valueWrapper);
	}

	onOperatorChange(selectNode: Node, field: Object, valueWrapper: HTMLElement)
	{
		const valueInput = valueWrapper.querySelector(`[name^="${this.#fieldPrefix}value"]`);
		Dom.clean(valueWrapper);

		Dom.append(
			this.#getValueNode(field, valueInput?.value || this.#condition.value, selectNode.value),
			valueWrapper,
		);
	}

	#getValueNode(field: {}, value, operator: string): any
	{
		if (operator === Operator.BETWEEN)
		{
			return Tag.render`
				<div>
					${this.createValueNode(field, Type.isArrayFilled(value) ? value[0] : value)}
					<div style="height: 8px;"></div>
					${this.createValueNode(field, Type.isArrayFilled(value) ? value[1] : '')}
				</div>
			`;
		}

		if (!operator.includes('empty'))
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
		const tpl = robot ? robot.getTemplate() : null;

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
			SystemExpression: `{=${object}:${id}}`,
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
			case 'email':
			case 'phone':
			case 'web':
			case 'im':
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

		if (['time', 'date', 'datetime', 'int', 'double'].includes(fieldType) || Type.isUndefined(fieldType))
		{
			list[Operator.BETWEEN] = allLabels[Operator.BETWEEN];
		}

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
			const type = this.#fields[i].Type;

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
				// TODO add support of custom types
			}
		}

		return filtered;
	}

	createValueNode(docField, value)
	{
		const currentDocument = (
			Designer.getInstance().component
				? Designer.getInstance().component.document
				: getGlobalContext().document
		);

		const docType = [...currentDocument.getRawType(), currentDocument.getCategoryId()];

		const field = Runtime.clone(docField);
		field.Multiple = false;

		const valueNodes = BX.Bizproc.FieldType.renderControlPublic(
			docType,
			field,
			`${this.#fieldPrefix}value`,
			value,
			false,
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
			attrs: { className: 'bizproc-automation-popup-settings-dropdown' },
		});

		const operatorList = this.getOperators(field.Type, field.Multiple);
		for (const operatorId in operatorList)
		{
			if (!operatorList.hasOwnProperty(operatorId))
			{
				continue;
			}

			Dom.append(
				Tag.render`
					<option value="${Text.encode(operatorId)}">${Text.encode(operatorList[operatorId])}</option>
				`,
				select,
			);
		}

		Event.bind(select, 'change', this.onOperatorChange.bind(
			this,
			select,
			field,
			valueWrapper,
		));

		return select;
	}

	removeCondition(event: Event)
	{
		this.emit('onRemoveConditionClick', new BaseEvent({ data: { conditionSelector: this } }));

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
	{}

	destroy()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}
}
