import { Type, Dom } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { ConditionGroup } from 'bizproc.automation';
import { ConditionSelector } from './condition-selector';
import { Condition } from 'bizproc.automation';

export class ConditionGroupSelector
{
	#conditionGroup: ConditionGroup;
	#fields: Array<Object>;
	#fieldPrefix: string;
	#itemSelectors: Array<ConditionSelector>;
	#onOpenFieldMenu: ?(BaseEvent) => void;
	#onOpenMenu: ?(BaseEvent) => void;
	#showValuesSelector: boolean;
	#rootGroupTitle: ?string;

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

			this.#rootGroupTitle = options.rootGroupTitle
			this.#onOpenFieldMenu = options.onOpenFieldMenu;
			this.#onOpenMenu = options.onOpenMenu;
			this.#showValuesSelector = options.showValuesSelector ?? true;
		}
	}

	createNode()
	{
		const conditionNodes = [];

		this.#conditionGroup.getItems().forEach((item) => {
			const conditionSelector = new ConditionSelector(item[0], {
				fields: this.#fields,
				joiner: item[1],
				fieldPrefix: this.#fieldPrefix,
				rootGroupTitle: this.#rootGroupTitle,
				onOpenFieldMenu: this.#onOpenFieldMenu,
				onOpenMenu: this.#onOpenMenu,
				showValuesSelector: this.#showValuesSelector,
			});

			this.#itemSelectors.push(conditionSelector);
			conditionNodes.push(conditionSelector.createNode());
		});

		const self = this;
		conditionNodes.push(Dom.create("a", {
			attrs: { className: "bizproc-automation-popup-settings-link" },
			text: '[+]',
			events: {
				click()
				{
					self.addItem(this);
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
			rootGroupTitle: this.#rootGroupTitle,
			onOpenFieldMenu: this.#onOpenFieldMenu,
			onOpenMenu: this.#onOpenMenu,
			showValuesSelector: this.#showValuesSelector,
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