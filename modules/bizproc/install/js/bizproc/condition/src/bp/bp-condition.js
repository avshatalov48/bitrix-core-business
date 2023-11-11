import { Type, Tag, Text, Event, Dom, Loc, Runtime } from 'main.core';
import type { ConditionType } from '../types';
import { Operator } from '../condition';

import 'bp_field_type';

export class BpCondition
{
	#operator: string = Operator.EQUAL;
	#operatorName: string = '';
	#valueName: string = '';
	#value: any;
	#documentType;
	#useModified: boolean = false;

	#operatorElement: HTMLSelectElement;
	#valueElement: HTMLElement;
	#lastFieldProperty: string;

	constructor(parameters: ConditionType)
	{
		if (!Type.isPlainObject(parameters))
		{
			return;
		}

		if (Operator.getAll().includes(parameters.operator))
		{
			this.#operator = parameters.operator;
		}

		if (Type.isStringFilled(parameters.selectName))
		{
			this.#operatorName = parameters.selectName;
		}

		if (Type.isStringFilled(parameters.inputName))
		{
			this.#valueName = parameters.inputName;
		}

		if (Type.isBoolean(parameters.useOperatorModified))
		{
			this.#useModified = parameters.useOperatorModified;
		}

		this.#value = parameters.value;
		this.#documentType = parameters.documentType;
	}

	renderOperator(fieldType: string): HTMLElement
	{
		const select: HTMLElement = Tag.render`<select name="${Text.encode(this.#operatorName)}"></select>`;
		Event.bind(select, 'change', this.#onChangeOperator.bind(this));

		this.#getFilteredOperatorsByFieldType(fieldType).forEach((operator) => {
			Dom.append(
				Tag.render`
					<option value="${Text.encode(operator)}"${this.#operator === operator ? ' selected' : ''}>
						${Text.encode(Operator.getOperatorLabel(operator))}
					</option>
				`,
				select,
			);
		});

		this.#operatorElement = select;

		return Tag.render`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${Loc.getMessage('BIZPROC_JS_CONDITION')}
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					${select}
				</td>
			</tr>
		`;
	}

	#onChangeOperator(event)
	{
		const select: HTMLSelectElement = event.target;
		const previousOperator = String(this.#operator);
		this.#operator = select.selectedOptions[0].value;

		const valueRow = this.#valueElement.closest('tr');
		if (Operator.getOperatorsWithoutRenderValue().includes(this.#operator))
		{
			if (Dom.isShown(valueRow))
			{
				Dom.hide(valueRow);
			}

			return;
		}

		if (!Dom.isShown(valueRow))
		{
			Dom.show(valueRow);
		}

		const needRerender = (
			previousOperator === Operator.BETWEEN
			|| this.#operator === Operator.BETWEEN
			|| Operator.getOperatorsWithoutRenderValue().includes(previousOperator)
		);
		if (needRerender)
		{
			this.rerenderValue(this.#lastFieldProperty);
		}
	}

	rerenderOperator(fieldType: string)
	{
		const filterOperators = this.#getFilteredOperatorsByFieldType(fieldType);
		if (this.#operatorElement.options.length === filterOperators.length)
		{
			return;
		}

		Dom.clean(this.#operatorElement);
		filterOperators.forEach((operator) => {
			Dom.append(
				Tag.render`
					<option value="${Text.encode(operator)}"${this.#operator === operator ? ' selected' : ''}>
						${Text.encode(Operator.getOperatorLabel(operator))}
					</option>
				`,
				this.#operatorElement,
			);
		});
		this.#operator = this.#operatorElement.selectedOptions[0].value;
	}

	#getFilteredOperatorsByFieldType(fieldType: string): []
	{
		return Operator.getAllSortedForBp().filter((operator) => {
			if (!this.#useModified && operator === Operator.MODIFIED)
			{
				return false;
			}

			const filterFields = Operator.getOperatorFieldTypeFilter(operator);

			// todo: white list
			return filterFields.length === 0 || filterFields.includes(fieldType);
		});
	}

	renderOperatorTo(fieldType: string, to: HTMLElement)
	{
		Dom.append(this.renderOperator(fieldType), to);
	}

	renderValue(fieldProperty: {}): HTMLElement
	{
		this.#lastFieldProperty = fieldProperty;

		this.#valueElement = (
			this.#operator === Operator.BETWEEN
				? this.#renderBetweenValue(fieldProperty, this.#value)
				: this.#getFieldControl(fieldProperty, this.#value)
		);

		return Tag.render`
			<tr${Operator.getOperatorsWithoutRenderValue().includes(this.#operator) ? ' hidden' : ''}>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${Loc.getMessage('BIZPROC_JS_CONDITION_VALUE')}
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					${this.#valueElement}
				</td>
			</tr>
		`;
	}

	#renderBetweenValue(fieldProperty: {}, value): HTMLElement
	{
		const property = Object.assign(Runtime.clone(fieldProperty), { Multiple: false });

		const valueElement1 = this.#getFieldControl(
			property,
			value[0] || '',
			`${this.#valueName}_greater_then`,
		);
		const valueElement2 = this.#getFieldControl(
			property,
			value[1] || '',
			`${this.#valueName}_less_then`,
		);

		return Tag.render`
			<table>
				<tbody>
					<tr><td>${valueElement1}</td></tr>
					<tr><td>${valueElement2}</td></tr>
				</tbody>
			</table>
		`;
	}

	rerenderValue(fieldProperty: {})
	{
		this.#lastFieldProperty = fieldProperty;

		if (this.#operator === Operator.BETWEEN)
		{
			this.#rerenderBetweenValue(fieldProperty);

			return;
		}

		const valueElement = this.#getFieldControl(fieldProperty, '');

		Dom.replace(this.#valueElement, valueElement);
		this.#valueElement = valueElement;
	}

	#rerenderBetweenValue(fieldProperty: {})
	{
		const valueElement = this.#renderBetweenValue(fieldProperty, ['', '']);
		Dom.replace(this.#valueElement, valueElement);
		this.#valueElement = valueElement;
	}

	#getFieldControl(fieldProperty: {}, value: any, valueName?: string): any
	{
		const name = Type.isNil(valueName) ? this.#valueName : valueName;

		return BX.Bizproc.FieldType.renderControl(this.#documentType, fieldProperty, name, value, 'designer');
	}

	renderValueTo(fieldType: string, to: HTMLElement)
	{
		Dom.append(this.renderValue(fieldType), to);
	}

	destroy()
	{
		this.#operator = null;
		this.#value = null;
		this.#documentType = null;

		this.#operatorName = null;
		this.#valueName = null;

		Dom.remove(this.#operatorElement.parentElement.parentElement);
		this.#operatorElement = null;

		Dom.remove(this.#valueElement.parentElement.parentElement);
		this.#valueElement = null;

		this.#lastFieldProperty = null;
	}
}
