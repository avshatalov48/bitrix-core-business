import { Reflection, Type, Tag, Event, Dom, Loc, Text } from 'main.core';
import { BpMixedSelector } from 'bizproc.mixed-selector';
import { BpCondition, Operator } from "bizproc.condition";

import 'bp_selector';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class MixedCondition
{
	conditions: Array;
	table: HTMLTableElement;
	objectTabs;
	template: Array;
	#documentType: any;

	index: number = 0;
	selector: BpMixedSelector;
	addConditionNode: HTMLElement;

	constructor(options) {
		if (Type.isPlainObject(options))
		{
			this.conditions = options.conditions;
			this.table = options.table;
			this.objectTabs = options.objectTabs;
			this.template = options.template;
			this.#documentType = options.documentType;
		}
	}

	init()
	{
		this.addConditionNode = this.#createAddConditionNode();
		Dom.append(this.addConditionNode, this.table);

		for (const i in this.conditions)
		{
			this.addCondition(this.conditions[i]);
		}
	}

	#createAddConditionNode(): HTMLElement
	{
		const me = this;

		const addButton = Tag.render`<a href="#">${Loc.getMessage('BPMC_PD_ADD')}</a>`;
		Event.bind(addButton, 'click', (event) => {
			event.preventDefault();
			me.addCondition();
		});

		return Tag.render`
			<tbody>
				<tr>
					<td class="adm-detail-content-cell-l"></td>
					<td class="adm-detail-content-cell-r">
						${addButton}
					</td>
				</tr>
			</tbody>
		`;
	}

	addCondition(condition = {
		object: null,
		field: null,
		value: null,
		joiner: '0',
		operator: Operator.NOT_EMPTY
	})
	{
		condition.object = condition.object === 'Template' ? 'Parameter' : condition.object;

		const bpCondition: BpCondition = new BpCondition({
			operator: condition.operator,
			value: condition.value,
			selectName: 'mixed_condition[' + Text.toInteger(this.index) + '][operator]',
			inputName: 'mixed_condition_value_' + Text.toInteger(this.index),
			useOperatorModified: false,
			documentType: this.#documentType,
		});
		const property = this.getProperty(condition.object, condition.field) ?? {Type: 'string'};

		const joiner = this.index > 0 ? this.#createJoiner(condition.joiner) : '';
		const tbody = Tag.render`
			<tbody 
				data-index="${Text.toInteger(this.index)}"
				data-object="${Text.encode(condition.object ?? '')}"
				data-field="${Text.encode(condition.field ?? '')}"
			>
				${joiner}
				${this.#createSource(condition.object, condition.field)}
				${bpCondition.renderOperator(property.Type)}
				${bpCondition.renderValue(property)}
			</tbody>
		`;

		if (this.selector)
		{
			this.selector.subscribe('onSelect', function (event) {
				const object = event.data.item.object;
				const field = event.data.item.field;
				const property = this.getProperty(object, field) ?? {Type: 'string'};

				tbody.setAttribute('data-object', object);
				tbody.setAttribute('data-field', field);
				bpCondition.rerenderOperator(property.Type);
				bpCondition.rerenderValue(property);
			}.bind(this));
		}

		Dom.insertBefore(tbody, this.addConditionNode);
		this.index++;
	}

	#createJoiner(joiner): HTMLElement
	{
		const deleteNode = Tag.render`<a href="#">${Loc.getMessage('BPMC_PD_DELETE')}</a>`;
		Event.bind(deleteNode, 'click', this.#deleteCondition.bind(this));

		return Tag.render`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					<select name="mixed_condition[${Text.toInteger(this.index)}][joiner]">
						<option value="0">${Loc.getMessage('BPMC_PD_AND')}</option>
						<option value="1"${Text.toInteger(joiner) === 1 ? ' selected' : ''}>
							${Loc.getMessage('BPMC_PD_OR')}
						</option>
					</select>
				</td>
				<td align="right" width="60%" class="adm-detail-content-cell-r">
					${deleteNode}
				</td>
			</tr>
		`;
	}

	#createSource(object, field): HTMLElement
	{
		const source = Tag.render`<td width="60%" class="adm-detail-content-cell-r"></td>`;

		this.selector = new BpMixedSelector({
			targetNode: source,
			template: this.template,
			objectTabs: this.objectTabs,
			inputNames: {
				object: 'mixed_condition[' + String(Text.toInteger(this.index)) + '][object]',
				field: 'mixed_condition[' + String(Text.toInteger(this.index)) + '][field]',
			}
		});
		this.selector.renderMixedSelector();
		if (object && field && this.objectTabs[object] && this.objectTabs[object][field])
		{
			this.selector.setSelectedObjectAndField(object, field, this.objectTabs[object][field]['Name']);
		}
		else
		{
			const sourceName = this.#findActivityTitle(object, field);
			if (sourceName)
			{
				this.selector.setSelectedObjectAndField(object, field, sourceName);
			}
		}

		return Tag.render`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${Loc.getMessage('BPMC_PD_FIELD') + ':'}
				</td>
				${source}
			</tr>
		`;
	}

	#findActivityTitle(object, field): string | null
	{
		const activityTabItems = this.selector.getMenuItemsByTabName('Activity');

		for (const i in activityTabItems)
		{
			const activityInfo = activityTabItems[i];
			if (activityInfo.object === object)
			{
				const activityItems = activityInfo.items;
				for (const j in activityItems)
				{
					const itemInfo = activityItems[j];
					if (itemInfo.field === field)
					{
						return itemInfo.text;
					}
				}
			}
		}

		return null;
	}

	#deleteCondition(event)
	{
		const target = event.target.closest('tbody');
		if (target)
		{
			Dom.remove(target);
		}
		event.preventDefault();
	}

	getProperty(object, field): Object | null
	{
		if (object && this.objectTabs[object])
		{
			return this.objectTabs[object][field];
		}

		const results = BX.Bizproc.Selector.getActivitiesItems();
		for (let i = 0; i < results.length; ++i)
		{
			if (results[i].propertyObject === object && results[i].propertyField === field)
			{
				return results[i].property;
			}
		}

		return null;
	}
}

namespace.MixedCondition = MixedCondition;