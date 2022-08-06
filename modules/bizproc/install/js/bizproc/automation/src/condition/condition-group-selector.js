import { Type, Dom } from 'main.core';
import { ConditionGroup } from './condition-group';
import { ConditionSelector } from './condition-selector';
import { Condition } from 'bizproc.automation';

export class ConditionGroupSelector
{
	#conditionGroup: ConditionGroup;
	#fields: Array<Object>;
	#fieldPrefix: string;
	#itemSelectors: Array<ConditionSelector>;

	constructor(conditionGroup: ConditionGroup, options: Object)
	{
		this.#conditionGroup = conditionGroup;
		this.#fields = [];
		this.#fieldPrefix = 'condition_';
		this.#itemSelectors = [];

		if (Type.isPlainObject(options))
		{
			if (Type.isArray(options.fields))
			{
				this.#fields = options.fields;
			}
			if (options.fieldPrefix)
			{
				this.#fieldPrefix = options.fieldPrefix;
			}
		}
	}

	createNode()
	{
		const me = this;
		const conditionNodes = [];
		const fields = this.#fields;

		this.#conditionGroup.getItems().forEach(function(item)
		{
			const conditionSelector = new ConditionSelector(item[0], {
				fields: fields,
				joiner: item[1],
				fieldPrefix: me.#fieldPrefix,
			});

			this.#itemSelectors.push(conditionSelector);
			conditionNodes.push(conditionSelector.createNode());
		}, this);

		conditionNodes.push(Dom.create("a", {
			attrs: { className: "bizproc-automation-popup-settings-link" },
			text: '[+]',
			events: {
				click()
				{
					me.addItem(this);
				}
			}
		}));

		return Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-link-wrapper" },
			children: conditionNodes
		});
	}

	addItem(buttonNode)
	{
		const conditionSelector = new ConditionSelector(new Condition({}, this.#conditionGroup), {
			fields: this.#fields,
			fieldPrefix: this.#fieldPrefix,
		});
		this.#itemSelectors.push(conditionSelector);

		buttonNode.parentNode.insertBefore(conditionSelector.createNode(), buttonNode);
	}

	destroy()
	{
		this.#itemSelectors.forEach(selector => selector.destroy());
		this.#itemSelectors = [];
	}
}