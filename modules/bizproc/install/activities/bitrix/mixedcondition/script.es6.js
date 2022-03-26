import { Reflection, Type, Tag, Event, Dom, Loc } from 'main.core';
import { BpMixedSelector } from 'bizproc.mixed-selector';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class MixedCondition {

	operatorList;
	conditions: Array;
	table: HTMLTableElement;
	objectTabs;
	template: Array;
	formName: string;

	index: number = 0;
	selector: BpMixedSelector;
	addConditionNode: HTMLElement;

	constructor(options) {
		if (Type.isPlainObject(options))
		{
			this.operatorList = options.operatorList;
			this.conditions = options.conditions;
			this.table = options.table;
			this.objectTabs = options.objectTabs;
			this.template = options.template;
			this.formName = options.formName;
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

		return Dom.create('tbody', {
			children: [
				Dom.create('tr', {
					children: [
						Dom.create('td', {
							attrs: {
								className: 'adm-detail-content-cell-l'
							}
						}),
						Dom.create('td', {
							attrs: {
								className: 'adm-detail-content-cell-r'
							},
							children: [
								Dom.create('a', {
									attrs: {
										href: '#'
									},
									text: Loc.getMessage('BPMC_PD_ADD'),
									events: {
										click: function (event){
											me.addCondition();

											event.preventDefault();
										}
									}
								})
							]
						})
					]
				})
			]
		});
	}

	addCondition(condition = {
		object: null,
		field: null,
		value: null,
		joiner: '0',
		operator: '!empty'
	})
	{
		const me = this;

		if (condition.object === 'Template')
		{
			condition.object = 'Parameter';
		}

		// Tag.render can't render <tbody>, <td>, <tr>;
		const tbody = Dom.create('tbody', {
			attrs: {
				'data-index': String(this.index),
				'data-object': BX.util.htmlspecialchars(condition.object) ?? '',
				'data-field': BX.util.htmlspecialchars(condition.field) ?? ''
			}
		});

		const joinerNode = this.#createJoiner(condition.joiner);
		if (this.index > 0)
		{
			Dom.append(joinerNode, tbody);
		}

		const sourceNode = this.#createSource(condition.object, condition.field);
		Dom.append(sourceNode, tbody);
		if (this.selector)
		{
			this.selector.subscribe('onSelect', function (event) {
				tbody.setAttribute('data-object', event.data.item.object);
				tbody.setAttribute('data-field', event.data.item.field);
				me.#renderValue(tbody);
			});
		}

		const conditionNode = this.#createCondition(condition.operator);
		Dom.append(conditionNode, tbody);

		const fieldNode = this.#createField(condition.operator);
		Dom.append(fieldNode, tbody);

		this.#renderValue(tbody, condition.operator, condition.value);

		Dom.insertBefore(tbody, this.addConditionNode);
		this.index++;
	}

	#createJoiner(joiner): HTMLElement
	{
		const wrapJoiner = Dom.create('td', {
			attrs: {
				className: 'adm-detail-content-cell-l',
				align: 'right',
				width: '40%'
			},
		});

		const joinerNode = Tag.render`
			<select name="mixed_condition[${this.index}][joiner]">
				<option value="0">${Loc.getMessage('BPMC_PD_AND')}</option>
				<option value="1">${Loc.getMessage('BPMC_PD_OR')}</option>
			</select>
		`;
		if (String(joiner) === '1'){
			joinerNode.value = '1';
		}
		Dom.append(joinerNode, wrapJoiner);

		const wrapDelete = Dom.create('td', {
			attrs: {
				className: "adm-detail-content-cell-r",
				align: 'right',
				width: '60%'
			},
		});

		const deleteNode = Tag.render`<a href="#">${Loc.getMessage('BPMC_PD_DELETE')}</a>`;
		Event.bind(deleteNode, 'click', this.#deleteCondition.bind(this));
		Dom.append(deleteNode, wrapDelete);

		return Dom.create('tr', {
			children: [
				wrapJoiner,
				wrapDelete
			]
		});
	}

	#createSource(object, field): HTMLElement
	{
		const label = Dom.create('td', {
			attrs: {
				className: 'adm-detail-content-cell-l',
				align: 'right',
				width: '40%'
			},
			text: Loc.getMessage('BPMC_PD_FIELD') + ':'
		});

		const source = Dom.create('td', {
			attrs: {
				className: 'adm-detail-content-cell-r',
				width: "60%"
			}
		});

		this.selector = new BpMixedSelector({
			targetNode: source,
			template: this.template,
			objectTabs: this.objectTabs,
			inputNames: {
				object: 'mixed_condition[' + String(this.index) + '][object]',
				field: 'mixed_condition[' + String(this.index) + '][field]',
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

		return Dom.create('tr', {children: [label, source]});
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

	#createCondition(operator): HTMLElement
	{
		const label = Dom.create('td', {
			attrs: {
				className: 'adm-detail-content-cell-l',
				align: 'right',
				width: "40%"
			},
			text: Loc.getMessage('BPMC_PD_CONDITION') + ':'
		});

		const select = Tag.render`
			<select name="mixed_condition[${this.index}][operator]" data-role="operator-selector"></select>
		`;
		Event.bind(select, 'change', this.#changeCondition.bind(this));

		for (const operation in this.operatorList)
		{
			const option = Tag.render`
				<option value="${operation}">${BX.util.htmlspecialchars(this.operatorList[operation])}</option>
			`;
			Dom.append(option, select);
		}
		select.value = operator;
		if (select.selectedIndex === -1)
		{
			select.value = '!empty';
		}

		return Dom.create('tr', {
			children: [
				label,
				Dom.create('td', {
					attrs: {
						className: 'adm-detail-content-cell-r',
						width: "60%"
					},
					children: [select]
				}),
			]
		});
	}

	#createField(operator): HTMLElement
	{
		const wrapper = Dom.create('tr', {
			attrs: {
				'data-role': 'value-row'
			},
			children: [
				Dom.create('td', {
					attrs: {
						className: 'adm-detail-content-cell-l',
						align: 'right',
						width: '40%'
					},
					text: Loc.getMessage('BPMC_PD_VALUE') + ':'
				}),
				Dom.create('td', {
					attrs: {
						className: 'adm-detail-content-cell-r',
						'data-role': 'value-cell',
						width: '60%'
					},
					text: '...'
				})
			]
		});

		if (['empty', '!empty'].includes(operator))
		{
			Dom.style(wrapper, 'display',  'none');
		}

		return wrapper;
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

	#changeCondition(event)
	{
		const target = event.target;
		this.#renderValue(target.closest('tbody'), target.value);
	}

	#renderValue(conditionNode, operator, value=null)
	{
		operator = operator || conditionNode.querySelector('[data-role="operator-selector"]').value;
		const valueRow = conditionNode.querySelector('[data-role="value-row"]');
		if (['empty', '!empty'].includes(operator))
		{
			Dom.style(valueRow, 'display', 'none');
		}
		else
		{
			Dom.style(valueRow, 'display', '');
			this.#renderField(conditionNode, value);
		}
	}

	#renderField(conditionNode, value)
	{
		const cell = conditionNode.querySelector('[data-role="value-cell"]');
		const index = conditionNode.getAttribute('data-index');
		const property = this.getProperty(
			conditionNode.getAttribute('data-object'),
			conditionNode.getAttribute('data-field')
		);

		if (!property)
		{
			return;
		}

		objFieldsPVC.GetFieldInputControl(
			property,
			value ?? '',
			{
				Field: 'mixed_condition_value_' + index,
				Form: this.formName
			},
			function (value) {
				if (value)
				{
					cell.innerHTML = value;
				}
				if (!Type.isUndefined(BX.Bizproc.Selector))
				{
					BX.Bizproc.Selector.initSelectors(cell);
				}
			},
			true
		);

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