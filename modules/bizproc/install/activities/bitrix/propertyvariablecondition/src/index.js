import {Reflection, Text, Type, Tag, Dom, Loc, Event} from 'main.core';
import {BpCondition, Operator} from "bizproc.condition";

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class PropertyVariableCondition
{
	#table: HTMLTableElement;
	#fields: {
		variables: Object<string, {Name: string, Type: string}>,
		properties: Object<string, {Name: string, Type: string}>,
	} = {
		variables: {},
		properties: {},
	};
	#documentType: any;

	#index: number = 0;
	#conditions: Array<{
		operator: string,
		fieldId: string,
		joiner: number,
		value: any,
	}> = [];
	#conditionCountNode: HTMLInputElement;

	constructor(options: {
		table: HTMLTableElement,
		conditions: {
			variable_condition_count: string,
		},
		variables?: {},
		properties?: {},
		documentType: any,
	})
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		if (Type.isElementNode(options.table))
		{
			this.#table = options.table;
		}

		if (Type.isPlainObject(options.conditions))
		{
			const indexes = String(options.conditions.variable_condition_count).split(',').map(Text.toInteger);
			indexes.forEach((index) => {
				this.#conditions.push({
					operator: options.conditions['variable_condition_condition_' + index] || Operator.EQUAL,
					fieldId: options.conditions['variable_condition_field_' + index] || '',
					joiner: options.conditions['variable_condition_joiner_' + index] || 0,
					value: options.conditions['variable_condition_value_' + index] || '',
				});
			});
		}

		if (Type.isPlainObject(options.variables))
		{
			this.#fields.variables = options.variables;
		}
		if (Type.isPlainObject(options.properties))
		{
			this.#fields.properties = options.properties;
		}

		this.#documentType = options.documentType;
	}

	init()
	{
		const wrapper = Tag.render`<tbody></tbody>`;
		this.#conditions.forEach((condition) => {this.#renderConditionTo(condition, wrapper)});
		Dom.append(this.#renderAddButton(), wrapper);
		Dom.append(wrapper, this.#table);
	}

	#renderConditionTo(condition: {operator?: string, fieldId?: string, joiner?: number, value?: any}, wrapper)
	{
		const bpCondition = new BpCondition({
			operator: condition.operator || Operator.EQUAL,
			value: condition.value || '',
			selectName: 'variable_condition_condition_' + String(this.#index),
			inputName: 'variable_condition_value_' + String(this.#index),
			documentType: this.#documentType,
			useOperatorModified: false,
		});

		if (this.#index !== 0)
		{
			Dom.append(this.#renderJoinerAndDeleteButton(Text.toInteger(condition.joiner || 0), bpCondition), wrapper);
		}

		const fieldNode = this.#renderField(condition.fieldId || '', this.#index, bpCondition);
		Dom.append(fieldNode, wrapper);

		let fieldId = condition.fieldId || '';

		let field = this.#fields.variables[fieldId] ?? this.#fields.properties[fieldId];
		if (Type.isNil(field))
		{
			fieldId = fieldNode.getElementsByTagName('SELECT')[0].options[0].value;
			field = this.#fields.variables[fieldId] ?? this.#fields.properties[fieldId];
		}

		bpCondition.renderOperatorTo(field.Type, wrapper);
		bpCondition.renderValueTo(field, wrapper);

		this.#index++;
	}

	#renderField(fieldId: string, index: string, condition: BpCondition): HTMLElement
	{
		const select: HTMLSelectElement = Tag.render`
			<select name="variable_condition_field_${String(index)}"></select>
		`;

		const propertyKeys = Object.keys(this.#fields.properties);
		if (propertyKeys.length > 0)
		{
			const optgroup = Tag.render`<optgroup label="${Loc.getMessage('BPFC_PD_PARAMS')}"></optgroup>`;
			propertyKeys.forEach((key) => {
				Dom.append(
					Tag.render`
						<option value="${Text.encode(key)}"${fieldId === key ? ' selected' : ''}>
							${Text.encode(this.#fields.properties[key].Name)}
						</option>
					`,
					optgroup
				)
			});

			Dom.append(optgroup, select);
		}

		const variableKeys = Object.keys(this.#fields.variables);
		if (variableKeys.length > 0)
		{
			const optgroup = Tag.render`<optgroup label="${Loc.getMessage('BPFC_PD_VARS')}"></optgroup>`;
			variableKeys.forEach((key) => {
				Dom.append(
					Tag.render`
						<option value="${Text.encode(key)}"${fieldId === key ? ' selected' : ''}>
							${Text.encode(this.#fields.variables[key].Name)}
						</option>
					`,
					optgroup
				)
			});

			Dom.append(optgroup, select);
		}

		Event.bind(select, 'change', this.#onFieldChange.bind(this, condition));

		return Tag.render`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${Loc.getMessage('BPFC_PD_FIELD') + ':'}
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					${select}
				</td>
			</tr>
		`;
	}

	#onFieldChange(condition: BpCondition, event)
	{
		const select: HTMLSelectElement = event.target;
		const fieldId = select.selectedOptions[0].value;

		let field = this.#fields.properties[fieldId] ?? this.#fields.variables[fieldId];
		if (!field)
		{
			select.selectedIndex = 0;
			field = this.#fields[select.selectedOptions[0]];
		}

		condition.rerenderOperator(field.Type ?? 'string');
		condition.rerenderValue(field ?? {Type: 'string'});
	}

	#renderJoinerAndDeleteButton(joiner: number, condition: BpCondition): HTMLElement
	{
		const index = this.#index;
		const deleteButton = Tag.render`<a href="#">${Loc.getMessage('BPFC_PD_DELETE')}</a>`;
		Event.bindOnce(deleteButton, 'click', this.#onDeleteClick.bind(this, condition, index));

		return Tag.render`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					<select name="${'variable_condition_joiner_' + String(this.#index)}">
						<option value="0">${Loc.getMessage('BPFC_PD_AND')}</option>
						<option value="1"${joiner === 1 ? ' selected' : ''}>${Loc.getMessage('BPFC_PD_OR')}</option>
					</select>
				</td>
				<td align="right" width="60%" class="adm-detail-content-cell-r">
					${deleteButton}
				</td>
			</tr>
		`;
	}

	#onDeleteClick(condition: BpCondition, index: number, event)
	{
		event.preventDefault();

		const eventTarget: HTMLElement = event.target;
		const wrapper = eventTarget.closest('tbody');

		const td = eventTarget.parentElement;
		const tr: HTMLTableRowElement = td.parentElement;

		const rowIndex = tr.rowIndex;
		wrapper.deleteRow(rowIndex + 1); // field
		wrapper.deleteRow(rowIndex); // joiner
		condition.destroy();

		const currentIndexes = this.#conditionCountNode.value.split(',');
		const deletedIndex = currentIndexes.indexOf(String(index));
		if (deletedIndex !== -1)
		{
			currentIndexes.splice(deletedIndex, 1);
		}
		this.#conditionCountNode.value = currentIndexes.join(',');
	}

	#renderAddButton(): HTMLElement
	{
		this.#conditionCountNode = Tag.render`
			<input 
				type="hidden"
				name="variable_condition_count"
				value="${[...Array(this.#index)].map((value, index) => index).join(',')}"
			>
		`;

		const addButton = Tag.render`<a href="#">${Loc.getMessage('BPFC_PD_ADD')}</a>`;
		Event.bind(addButton, 'click', this.#onAddClick.bind(this));

		return Tag.render`
			<tr>
				<td class="adm-detail-content-cell-l"></td>
				<td class="adm-detail-content-cell-r">
					${this.#conditionCountNode}
					${addButton}
				</td>
			</tr>
		`;
	}

	#onAddClick(event)
	{
		event.preventDefault();

		const eventTarget: HTMLElement = event.target;
		const wrapper = eventTarget.closest('tbody');

		const addRow = eventTarget.closest('tr');
		Dom.remove(addRow);

		this.#conditionCountNode.value += ',' + String(this.#index);
		this.#renderConditionTo({}, wrapper);

		Dom.append(addRow, wrapper);
	}
}

namespace.PropertyVariableCondition = PropertyVariableCondition;