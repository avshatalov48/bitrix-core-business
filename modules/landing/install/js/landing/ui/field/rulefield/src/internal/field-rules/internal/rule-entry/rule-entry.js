/**
* @bxjs_lang_path
*/
import {Cache, Dom, Tag, Text, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';
import {IconButton} from 'landing.ui.component.iconbutton';
import {Loc} from 'landing.loc';
import {Popup} from 'main.popup';
import {Draggable} from 'ui.draganddrop.draggable';
import {PageObject} from 'landing.pageobject';
import {TextField} from 'landing.ui.field.textfield';
import {FieldElement} from '../../../field-element/field-element';
import type {RuleEntryOptions} from '../../../../types/rule-field-options';

import './css/style.css';

type ExpressionEntry = {
	field: string,
	action: 'show' | 'hide',
};

type RuleEntryState = {
	condition: {
		field: string,
		value: any,
		operator: '=' | '!=',
	},
	expression: Array<ExpressionEntry>,
};

export class RuleEntry extends EventEmitter
{
	state: RuleEntryState;

	constructor(options: RuleEntryOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.RuleField.RuleEntry');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.options = {...options};
		this.cache = new Cache.MemoryCache();

		this.draggable = new Draggable({
			container: this.getExpressionContainer(),
			dragElement: '.landing-ui-button-icon-drag',
			draggable: '.landing-ui-field-element-green',
			type: Draggable.HEADLESS,
			context: window.parent,
			offset: {
				y: -62,
			},
		});

		this.draggable.subscribe('end', this.onDragEnd.bind(this));

		this.state = {
			condition: {
				field: this.options.condition.field.id,
				value: this.options.condition.value,
				operator: this.options.condition.operator,
			},
			expression: this.options.expression.map((item) => {
				return {
					field: item.field.id,
					action: item.action,
				};
			}),
		};

		this.options.expression.forEach((item) => {
			this.addExpressionItem({
				id: item.field.id,
				label: item.field.label,
				action: item.action,
				preventEvent: true,
			});
		});
	}

	getOperatorField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('operatorField', () => {
			const {condition} = this.options.dictionary.deps;
			return new BX.Landing.UI.Field.Radio({
				selector: 'operator',
				value: [this.state.condition.operator],
				items: condition.operations
					.filter((item) => {
						return (
							!Type.isArrayFilled(item.fieldTypes)
							|| item.fieldTypes.includes(this.options.condition.field.type)
						);
					})
					.map((item) => {
						return {name: item.name, value: item.id};
					}),
				onChange: this.onOperatorChange.bind(this),
			});
		});
	}

	getOperatorLabel(operator: string): string
	{
		const operatorField = this.getOperatorField();
		return operatorField.items.reduce((acc, item) => {
			return String(item.value) === String(operator) ? item.name : acc;
		}, '');
	}

	onOperatorChange()
	{
		const operatorField = this.getOperatorField();
		const [value] = operatorField.getValue();

		this.getOperatorLabelLayout().textContent = this.getOperatorLabel(value);
		this.state.condition.operator = value;
		this.emit('onChange');
	}

	getSeparator(): HTMLDivElement
	{
		return this.cache.remember('separator', () => {
			return Tag.render`
				<div class="value-settings-item-separator"></div>
			`;
		});
	}

	renderValueRadioButton({label, value, id, checked}): HTMLDivElement
	{
		const onChange = () => {
			this.setValueLabelText(label);
			this.state.condition.value = value;

			this.emit('onChange');
		};

		return Tag.render`
			<div class="value-settings-item value-settings-item-value">
				<input 
					type="radio" 
					id="value_${id}_${value}" 
					name="value_${id}_${this.options.condition.field.id}"
					onchange="${onChange}"
					${checked ? 'checked' : ''}
				>
				<label for="value_${id}_${value}">${label}</label>
			</div>
		`;
	}

	getValueSettingsPopup(): Popup
	{
		return this.cache.remember('valueSettingsPopup', () => {
			const rootWindow = PageObject.getRootWindow();
			const popupContent = Tag.render`<div class="value-settings-popup"></div>`;
			const random = Text.getRandom();

			if (
				this.options.condition.field.type === 'list'
				|| this.options.condition.field.type === 'checkbox'
				|| this.options.condition.field.type === 'radio'
				|| this.options.condition.field.type === 'bool'
			)
			{
				const operatorField = this.getOperatorField();
				operatorField.setValue(this.options.condition.operator);
				Dom.append(operatorField.getLayout(), popupContent);

				Dom.append(this.getSeparator(), popupContent);

				const valueItems = (() => {
					if (this.options.condition.field.type === 'bool')
					{
						return [
							{label: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES'), value: 'Y'},
							{label: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO'), value: 'N'},
						];
					}

					return this.options.condition.field.items;
				})();

				valueItems.forEach((item) => {
					const checked = String(this.options.condition.value) === String(item.value);
					Dom.append(
						Dom.append(
							this.renderValueRadioButton({...item, id: random, checked}),
							popupContent,
						),
						popupContent,
					);
				});
			}
			else
			{
				const operatorField = this.getOperatorField();
				operatorField.setValue(this.options.condition.operator);
				Dom.append(operatorField.getLayout(), popupContent);

				Dom.append(this.getSeparator(), popupContent);

				const inputField = new TextField({
					textOnly: true,
					onValueChange: () => {
						const conditionValue = (
							inputField.getValue()
							|| Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_EMPTY')
						);
						this.setValueLabelText(conditionValue);
						this.state.condition.value = inputField.getValue();
						this.emit('onChange');
					},
					content: this.options.condition.value,
				});

				Dom.append(inputField.getLayout(), popupContent);
			}

			return new rootWindow.BX.Main.Popup({
				bindElement: this.getConditionValueLayout(),
				content: popupContent,
				width: 228,
				autoHide: true,
				offsetLeft: 8,
				offsetTop: 1,
				maxHeight: 200,
				events: {
					onShow: () => {
						Dom.addClass(
							this.getConditionValueLayout(),
							'landing-ui-rule-entry-condition-value-active',
						);

						this.getValueSettingsPopup().adjustPosition({forceBindPosition: true});
					},
					onClose: () => {
						Dom.removeClass(
							this.getConditionValueLayout(),
							'landing-ui-rule-entry-condition-value-active',
						);
					},
				},
			});
		});
	}

	getOperatorLabelLayout(): HTMLDivElement
	{
		return this.cache.remember('operatorLayout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-condition-value-operator">
					${this.getOperatorLabel(this.options.condition.operator)}
				</div>
			`;
		});
	}

	getValueLabel(): string
	{
		if (Type.isArray(this.options.condition.field.items))
		{
			const valueItem = this.options.condition.field.items.find((item) => {
				return String(item.value) === String(this.options.condition.value);
			});

			if (valueItem && Type.isString(valueItem.label))
			{
				return valueItem.label;
			}
		}

		if (Type.isStringFilled(this.options.condition.value))
		{
			if (this.options.condition.value === 'Y')
			{
				return Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES');
			}

			if (this.options.condition.value === 'N')
			{
				return Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO');
			}

			return this.options.condition.value;
		}

		return Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_EMPTY');
	}

	getValueLabelLayout(): HTMLDivElement
	{
		return this.cache.remember('valueLabelLayout', () => {
			return Tag.render`
				<div 
					class="landing-ui-rule-entry-condition-value-value-label"
					onclick="${this.onValueLabelClick.bind(this)}"
				>
					${this.getValueLabel()}
				</div>			
			`;
		});
	}

	setValueLabelText(text: string)
	{
		this.getValueLabelLayout().textContent = text;
	}

	onValueLabelClick(event: MouseEvent)
	{
		event.preventDefault();

		const valueSettingsPopup = this.getValueSettingsPopup();
		if (!valueSettingsPopup.isShown())
		{
			valueSettingsPopup.show();
		}
		else
		{
			valueSettingsPopup.close();
		}
	}

	getConditionValueLayout(): HTMLDivElement
	{
		return this.cache.remember('conditionValueLayout', () => {
			// const removeButton = new IconButton({
			// 	type: IconButton.Types.remove,
			// 	iconSize: '9px',
			// 	style: {
			// 		width: '19px',
			// 		marginLeft: 'auto',
			// 	},
			// 	onClick: () => {
			// 		Dom.remove(this.getLayout());
			// 		this.emit('onRemove');
			// 	},
			// });

			return Tag.render`
				<div class="landing-ui-rule-entry-condition-value">
					<div class="landing-ui-rule-entry-condition-value-text">
						${this.getOperatorLabelLayout()}
						${this.getValueLabelLayout()}
					</div>
				</div>
			`;
		});
	}

	getConditionContainer(): HTMLDivElement
	{
		return this.cache.remember('conditionContainer', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-condition">
					${this.getConditionValueLayout()}
				</div>
			`;
		});
	}

	getFieldsListMenu(): Menu
	{
		return this.cache.remember('fieldsListMenu', () => {
			return new window.top.BX.Main.Menu({
				bindElement: this.getAddFieldLink(),
				maxHeight: 205,
				items: this.options.fieldsList
					.filter((field) => {
						return (
							field.type !== 'page'
							&& field.type !== 'layout'
							&& field.id !== this.options.condition.field.id
						);
					})
					.map((item) => {
						return {
							id: item.id,
							text: item.label,
							onclick: this.onAddExpressionField.bind(this, item),
						};
					}),
			});
		});
	}

	onExpressionItemRemove(event: BaseEvent)
	{
		const target = event.getTarget();

		Dom.remove(target.getLayout());

		if (this.getExpressionContainer().children.length === 1)
		{
			Dom.removeClass(this.getLayout(), 'landing-ui-rule-entry-with-expression');
		}

		this.state.expression = this.state.expression.filter((entry) => {
			return entry.field !== target.options.id;
		});

		this.emit('onChange');
	}

	onExpressionFieldChange(event: BaseEvent)
	{
		const target = event.getTarget();

		const expressionEntry = this.state.expression.find((currentEntry) => {
			return currentEntry.field === target.options.id;
		});

		if (expressionEntry)
		{
			expressionEntry.action = target.getActionsDropdown().getValue();
		}

		this.emit('onChange');
	}

	addExpressionItem(
		options: {
			id: string,
			label: string,
			action: string,
			preventEvent: boolean,
		},
	)
	{
		const preparedOptions = {
			preventEvent: false,
			action: 'show',
			...options,
		};

		const fieldElement = new FieldElement({
			id: preparedOptions.id,
			title: preparedOptions.label,
			removable: true,
			color: FieldElement.Colors.green,
			actionsLabel: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_LABEL'),
			actionsList: [
				{name: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_SHOW_LABEL'), value: 'show'},
				{name: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_HIDE_LABEL'), value: 'hide'},
			],
			actionsValue: preparedOptions.action,
			onRemove: this.onExpressionItemRemove.bind(this),
			onChange: this.onExpressionFieldChange.bind(this),
		});

		Dom.insertBefore(fieldElement.getLayout(), this.getAddFieldLink());

		this.state.expression.push({field: preparedOptions.id, action: 'show'});
		this.state.expression = this.state.expression.reduce((acc, entry) => {
			if (!acc.find((accEntry) => accEntry.field === entry.field))
			{
				acc.push(entry);
			}

			return acc;
		}, []);

		this.adjustExpressionItems();

		Dom.addClass(this.getLayout(), 'landing-ui-rule-entry-with-expression');

		if (!preparedOptions.preventEvent)
		{
			this.emit('onChange');
		}
	}

	onAddExpressionField(item: {id: string, label: string})
	{
		this.addExpressionItem(item);
		this.getFieldsListMenu().close();
	}

	adjustExpressionItems()
	{
		[...this.getExpressionContainer().children]
			.reverse()
			.forEach((element, index) => {
				if (!Dom.hasClass(element, 'landing-ui-rule-entry-expression-link'))
				{
					Dom.style(element, 'z-index', index + 2);
				}
			});
	}

	onAddFieldLinkClick(event: MouseEvent)
	{
		event.preventDefault();

		const menu = this.getFieldsListMenu();
		const expressionItems = this.state.expression;
		menu.getMenuItems().forEach((item) => {
			const isUsed = expressionItems.some((expressionItem) => {
				return expressionItem.field === item.getId();
			});

			if (isUsed)
			{
				Dom.addClass(item.getLayout().item, 'landing-ui-disabled');
			}
			else
			{
				Dom.removeClass(item.getLayout().item, 'landing-ui-disabled');
			}
		});

		this.getFieldsListMenu().show();
	}

	getAddFieldLink(): HTMLDivElement
	{
		return this.cache.remember('addFieldLink', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-expression-link">
					<div 
						class="landing-ui-rule-entry-expression-link-text"
						onclick="${this.onAddFieldLinkClick.bind(this)}"
					>
						${Loc.getMessage('LANDING_RULE_FIELD_EXPRESSION_ADD_FIELD_LINK_LABEL')}
					</div>
					<div class="landing-ui-rule-entry-expression-link-sep"></div>
				</div>
			`;
		});
	}

	getExpressionContainer(): HTMLDivElement
	{
		return this.cache.remember('expressionContainer', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry-expression">
					${this.getAddFieldLink()}
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-rule-entry">
					${this.getConditionContainer()}
					${this.getExpressionContainer()}
				</div>
			`;
		});
	}

	getValue()
	{
		return this.state;
	}

	onDragEnd()
	{
		this.adjustExpressionItems();
		this.emit('onChange');
	}
}