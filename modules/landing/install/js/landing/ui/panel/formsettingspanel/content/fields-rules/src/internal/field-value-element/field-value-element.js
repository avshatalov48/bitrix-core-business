import {EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag, Text, Type} from 'main.core';
import {IconButton} from 'landing.ui.component.iconbutton';
import {Popup, Menu, MenuItem} from 'main.popup';
import {PageObject} from 'landing.pageobject';
import {Loc} from 'landing.loc';
import {TextField} from 'landing.ui.field.textfield';
import type {FormDictionary} from 'crm.form.type';
import {CrmField} from '../../types';

import './css/style.css';

type FieldValueState = {
	operation: string,
	target: string,
	value: any,
}

type FieldValueElementOptions = {
	removable: boolean,
	dictionary: FormDictionary,
	fields: Array<any>,
	data: FieldValueState,
};

export default class FieldValueElement extends EventEmitter
{
	options: FieldValueElementOptions;
	cache = new Cache.MemoryCache();
	state: FieldValueState;

	constructor(options: FieldValueElementOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ValueElement');
		this.options = {...options};
		this.state = {...this.options.data};
	}

	getOperatorLabelLayout(): HTMLDivElement
	{
		return this.cache.remember('operatorLabelLayout', () => {
			const text = this.getOperatorLabelText(this.options.data.operation);
			return Tag.render`
				<div
					class="landing-ui-rule-value-operator-label"
					onclick="${this.onOperatorLabelClick.bind(this)}"
				>${text}</div>
			`;
		});
	}

	onOperatorLabelClick(event: MouseEvent)
	{
		event.preventDefault();
		this.getOperatorSettingsPopup().show();
	}

	getTargetContainer()
	{
		return this.cache.remember('targetContainer', () => {
			return this.getLayout().closest('.landing-ui-panel-content-body-content') || this.getLayout();
		});
	}

	getOperatorSettingsPopup()
	{
		return this.cache.remember('operatorSettingsPopup', () => {
			const rootWindow = PageObject.getRootWindow();
			return new rootWindow.BX.Main.Popup({
				bindElement: this.getLayout(),
				targetContainer: this.getTargetContainer(),
				content: this.getOperatorField().getLayout(),
				autoHide: true,
				minWidth: 160,
				offsetLeft: 20,
				offsetTop: 3,
				bindOptions: {
					position: 'bottom',
				},
			});
		});
	}

	getValueLabelLayout(): HTMLDivElement
	{
		return this.cache.remember('valueLabelLayout', () => {
			const text = this.getValueLabelText(this.options.data.value);
			const layout = Tag.render`
				<div
					class="landing-ui-rule-value-value-label"
					onclick="${this.onValueLabelClick.bind(this)}"
				>
					<span class="landing-ui-rule-value-value-label-inner">${Text.encode(text)}</span>
				</div>
			`;

			if (
				this.options.data.operation === 'any'
				|| this.options.data.operation === 'empty'
			)
			{
				Dom.hide(layout);
			}

			return layout;
		});
	}

	setValueLabelText(text: string)
	{
		this.getValueLabelLayout().firstElementChild.textContent = text;
	}

	onValueLabelClick(event: MouseEvent)
	{
		event.preventDefault();
		this.getValueSettingsPopup().show();
	}

	getValueSettingsPopup(): Popup
	{
		return this.cache.remember('valueSettingsPopup', () => {
			const rootWindow = PageObject.getRootWindow();
			const popupContent = Tag.render`<div class="value-settings-popup"></div>`;
			const random = Text.getRandom();
			const targetField = this.getTargetField();

			if (
				targetField.type === 'list'
				|| targetField.type === 'product'
				|| targetField.type === 'checkbox'
				|| targetField.type === 'radio'
				|| targetField.type === 'bool'
			)
			{
				const valueItems = (() => {
					if (targetField.type === 'bool')
					{
						return [
							{label: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES'), value: 'Y'},
							{label: Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO'), value: 'N'},
						];
					}

					return targetField.items;
				})();

				valueItems.forEach((item) => {
					const checked = String(targetField.value) === String(item.value);
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
				const value = (() => {
					if (Type.isStringFilled(this.options.data.value))
					{
						return this.getValueLabelText(this.options.data.value);
					}

					return '';
				})();
				const inputField = new TextField({
					textOnly: true,
					onValueChange: () => {
						const conditionValue = (
							inputField.getValue()
							|| Loc.getMessage('LANDING_RULE_CONDITION_VALUE_EMPTY')
						);
						this.setValueLabelText(conditionValue);
						this.state.value = inputField.getValue();
						this.emit('onChange');
					},
					content: value,
				});

				Dom.append(inputField.getLayout(), popupContent);
			}

			return new rootWindow.BX.Main.Popup({
				bindElement: this.getLayout(),
				targetContainer: this.getTargetContainer(),
				content: popupContent,
				width: 228,
				autoHide: true,
				maxHeight: 200,
				offsetLeft: 20,
				offsetTop: 3,
				events: {
					onShow: () => {
						Dom.addClass(
							this.getLayout(),
							'landing-ui-rule-value-active',
						);
					},
					onClose: () => {
						Dom.removeClass(
							this.getLayout(),
							'landing-ui-rule-value-active',
						);
					},
				},
			});
		});
	}

	renderValueRadioButton({label, value, id, checked}): HTMLDivElement
	{
		const onChange = () => {
			this.setValueLabelText(label);
			this.state.value = value;

			this.emit('onChange');
		};

		return Tag.render`
			<div class="value-settings-item value-settings-item-value">
				<input
					type="radio"
					id="value_${id}_${value}"
					name="value_${id}_${this.options.data.target}"
					onchange="${onChange}"
					${checked ? 'checked' : ''}
				>
				<label for="value_${id}_${value}">${Text.encode(label)}</label>
			</div>
		`;
	}

	getOperatorField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('operatorField', () => {
			const {condition} = this.options.dictionary.deps;
			const targetField = this.getTargetField();
			return new BX.Landing.UI.Field.Radio({
				selector: 'operation',
				value: [this.state.operation],
				items: condition.operations
					.filter((item) => {
						return (
							(
								!Type.isArrayFilled(item.fieldTypes)
								|| item.fieldTypes.includes(targetField.type)
							)
							&& (
								!Type.isArrayFilled(item.excludeFieldTypes)
								|| (
									Type.isArrayFilled(item.excludeFieldTypes)
									&& !item.excludeFieldTypes.includes(targetField.type)
								)
							)
						);
					})
					.map((item) => {
						return {name: item.name, value: item.id};
					}),
				onChange: this.onOperationChange.bind(this),
			});
		});
	}

	setOperationLabelText(text: string)
	{
		this.getOperatorLabelLayout().textContent = text;
	}

	onOperationChange()
	{
		const operatorField = this.getOperatorField();
		const [value] = operatorField.getValue();

		if (
			value === 'empty'
			|| value === 'any'
		)
		{
			Dom.hide(this.getValueLabelLayout());
		}
		else
		{
			Dom.show(this.getValueLabelLayout());
		}

		this.setOperationLabelText(this.getOperatorLabelText(value));
		this.state.operation = value;
		this.emit('onChange');
	}

	getRemoveButton()
	{
		return this.cache.remember('removeButton', () => {
			return new IconButton({
				type: IconButton.Types.remove,
				iconSize: '9px',
				style: {
					width: '19px',
					marginLeft: 'auto',
				},
				onClick: () => {
					this.emit('onRemove');
					this.emit('onChange');
				},
			});
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div
					class="landing-ui-rule-value"
					data-target="${Text.encode(this.options.data.target)}"
				>
					<div class="landing-ui-rule-value-text">
						${this.getOperatorLabelLayout()}
						${this.getValueLabelLayout()}
					</div>
					<div class="landing-ui-rule-value-actions">
						${this.options.removable ? this.getRemoveButton().getLayout() : ''}
					</div>
					<div class="landing-ui-rule-decoration">
						<div class="landing-ui-rule-decoration-v-line"></div>
						<div class="landing-ui-rule-decoration-h-line"></div>
						<div class="landing-ui-rule-decoration-arrow"></div>
					</div>
				</div>
			`;
		});
	}

	getOperatorLabelText(operatorValue: string): string
	{
		return this.options.dictionary.deps.condition.operations.reduce((acc, item) => {
			if (item.id === operatorValue)
			{
				return item.name;
			}

			return acc;
		}, this.options.dictionary.deps.condition.operations[0].name);
	}

	getTargetField(): CrmField
	{
		return this.cache.remember('targetField', () => {
			return this.options.fields.find((field) => {
				return String(field.id) === String(this.options.data.target);
			});
		});
	}

	getValueLabelText(value: string): string
	{
		const targetField = this.getTargetField();
		if (Type.isPlainObject(targetField))
		{
			if (Type.isArrayFilled(targetField.items))
			{
				const item = targetField.items.find((currentItem) => {
					return String(currentItem.value) === String(value);
				});

				if (Type.isPlainObject(item))
				{
					return item.label;
				}
			}

			if (Type.isStringFilled(value))
			{
				if (value === 'Y')
				{
					return Loc.getMessage('LANDING_RULE_CONDITION_VALUE_YES');
				}

				if (value === 'N')
				{
					return Loc.getMessage('LANDING_RULE_CONDITION_VALUE_NO');
				}

				return value;
			}
		}

		return Loc.getMessage('LANDING_RULE_CONDITION_VALUE_EMPTY');
	}

	getValue(): FieldValueState
	{
		return {...this.state};
	}
}
