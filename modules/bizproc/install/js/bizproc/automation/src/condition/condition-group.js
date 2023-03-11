import { Type, Loc } from 'main.core';
import { Condition } from './condition';
import { ConditionNames } from './types';
import { Operator } from 'bizproc.condition';

export class ConditionGroup
{
	static CONDITION_TYPE = {
		Field: 'field',
		Mixed: 'mixed',
	}
	static JOINER = {
		And: 'AND',
		Or: 'OR',

		message(type)
		{
			if (type === this.Or)
			{
				return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_OR');
			}

			return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION_AND');
		}
	}

	#type: string;
	#items: Array<[Condition, string]>;
	#activityNames: ?ConditionNames;

	constructor(params: ?Object)
	{
		this.#type = ConditionGroup.CONDITION_TYPE.Field;
		this.#items = [];

		if (Type.isPlainObject(params))
		{
			if (params['type'])
			{
				this.#type = params['type'];
			}
			if (Type.isArray(params['items']))
			{
				params['items'].forEach(item => {
					const condition = new Condition(item[0], this);
					this.addItem(condition, item[1]);
				});
			}
			if (Type.isPlainObject(params['activityNames']))
			{
				this.#activityNames = params['activityNames'];
			}
		}
	}

	clone()
	{
		const clonedGroup = new ConditionGroup({type: this.#type});
		this.#items.forEach(([condition, joiner]) => {
			const clonedCondition = condition.clone();
			clonedCondition.parentGroup = clonedGroup;

			clonedGroup.addItem(clonedCondition, joiner);
		});

		return clonedGroup;
	}

	get conditionNamesList(): Array<string>
	{
		if (Type.isPlainObject(this.#activityNames))
		{
			return [
				this.#activityNames.Activity,
				this.#activityNames.Branch1,
				this.#activityNames.Branch2,
			];
		}

		return [];
	}

	get type()
	{
		return this.#type;
	}

	set type(type: string)
	{
		if (Object.values(ConditionGroup.CONDITION_TYPE).includes(type))
		{
			this.#type = type;
		}

		return this;
	}

	get items()
	{
		return this.#items;
	}

	static createFromForm(formFields, prefix)
	{
		const conditionGroup = new ConditionGroup();
		if (!prefix)
		{
			prefix = 'condition_';
		}

		if (Type.isArray(formFields[prefix + 'field']))
		{
			for (let i = 0, valueIndex = 0; i < formFields[prefix + 'field'].length; ++i, ++valueIndex)
			{
				if (formFields[prefix + 'field'][i] === '')
				{
					continue;
				}

				const condition = new Condition({}, conditionGroup);
				condition.setObject(formFields[prefix + 'object'][i]);
				condition.setField(formFields[prefix + 'field'][i]);
				condition.setOperator(formFields[prefix + 'operator'][i]);

				const value =
					condition.operator === Operator.BETWEEN
						? [formFields[prefix + 'value'][valueIndex], formFields[prefix + 'value'][valueIndex + 1]]
						: formFields[prefix + 'value'][valueIndex]
				;
				condition.setValue(value);

				let joiner = ConditionGroup.JOINER.And;
				if (formFields[prefix + 'joiner'] && formFields[prefix + 'joiner'][i] === ConditionGroup.JOINER.Or)
				{
					joiner = ConditionGroup.JOINER.Or;
				}

				if (condition.operator === Operator.BETWEEN)
				{
					valueIndex++;
				}

				conditionGroup.addItem(condition, joiner);
			}
		}

		return conditionGroup;
	}

	addItem(condition, joiner)
	{
		this.#items.push([condition, joiner]);
	}

	getItems(): Array<Array<Condition, string>>
	{
		return this.#items;
	}

	serialize()
	{
		const itemsArray = [];

		this.#items.forEach(item => {
			if (item.field !== '')
			{
				itemsArray.push([item[0].serialize(), item[1]]);
			}
		});

		return {
			type: this.#type,
			items: itemsArray,
			activityNames: this.#activityNames,
		};
	}
}