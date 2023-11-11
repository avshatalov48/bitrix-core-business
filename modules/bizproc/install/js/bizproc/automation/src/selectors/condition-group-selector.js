import { Type, Dom, Event, Tag, Loc, Text } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { Condition, ConditionGroup } from 'bizproc.automation';
import { ConditionSelector } from './condition-selector';
import { Draggable } from 'ui.draganddrop.draggable';

type ConditionGroupSelectorOptions = {
	fields: Array<Object>,
	fieldPrefix: string,
	rootGroupTitle: string,
	onOpenFieldMenu: ?(BaseEvent) => void,
	onOpenMenu: ?(BaseEvent) => void,
	showValuesSelector: boolean,
	caption: ?{
		head: string,
		add: string,
		collapsed: string,
	},
	isExpanded: boolean,
};

export class ConditionGroupSelector extends EventEmitter
{
	modern: boolean = true;// todo: remove 2024

	#conditionGroup: ConditionGroup;
	#fields: Array<Object>;
	#fieldPrefix: string;
	#itemSelectors: Array<ConditionSelector>;
	#onOpenFieldMenu: ?(BaseEvent) => void;
	#onOpenMenu: ?(BaseEvent) => void;
	#showValuesSelector: boolean;
	#rootGroupTitle: ?string;

	#options: ConditionGroupSelectorOptions = {};
	#toggleButtonNode: HTMLDivElement;
	#draggableNode: HTMLDivElement;

	constructor(conditionGroup: ConditionGroup, options: ConditionGroupSelectorOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation.Condition');

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

			this.#rootGroupTitle = options.rootGroupTitle;
			this.#onOpenFieldMenu = options.onOpenFieldMenu;
			this.#onOpenMenu = options.onOpenMenu;
			this.#showValuesSelector = options.showValuesSelector ?? true;

			this.#options = options;
		}
	}

	createNode()
	{
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
			conditionSelector.subscribe('onRemoveConditionClick', this.#onRemoveConditionClick.bind(this));

			this.#itemSelectors.push(conditionSelector);
		});

		const hasConditions = this.#conditionGroup.items.length > 0;
		const isCollapsed = this.#options.isExpanded !== true && hasConditions;

		const collapseButtonTitle = (
			isCollapsed
				? Loc.getMessage('BIZPROC_JS_AUTOMATION_EXPAND_CONDITION')
				: Loc.getMessage('BIZPROC_JS_AUTOMATION_COLLAPSE_CONDITION')
		);

		const { root, conditionContent, btnToggleList, btnTextNode, addButton, draggableNode } = Tag.render`
			<div class="bizproc-automation-popup-settings">
				<div
					ref="conditionContent"
					class="bizproc-automation-popup-settings__condition-content ${isCollapsed ? '' : '--active'}"
				>
					<div class="bizproc-automation-popup-settings__condition-header">
						<span class="bizproc-automation-popup-settings-title">
							${Text.encode(this.#options.caption?.head)}
						</span>
						<div
							ref="btnToggleList"
							class="bizproc-automation-popup-settings__btn-toggle ${hasConditions ? '' : '--disabled'}"
							data-role="condition-toggle"
						>
							<span ref="btnTextNode" class="bizproc-automation-popup-settings-title">
								${collapseButtonTitle}
							</span>
							<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 16px;"></div>
						</div>
					</div>
					<div class="bizproc-automation-popup-settings__transition-height-wrapper">
						<div class="bizproc-automation-popup-settings__transition-height-content">
							<div class="bizproc-automation-popup-settings__condition-body">
								<div ref="draggableNode" class="bizproc-automation-popup-settings__condition">
									${this.#itemSelectors.map((selector) => selector.createNode())}
								</div>
								<span class="bizproc-automation-popup-settings-link-wrapper">
									<a ref="addButton" class="bizproc-automation-popup-settings-link">
										${Text.encode(this.#options.caption?.add || Loc.getMessage('BIZPROC_JS_AUTOMATION_ADD_CONDITION'))}
									</a>
								</span>
							</div>
						</div>
					</div>
					<div class="bizproc-automation-popup-settings__transition-height-wrapper --revert">
						<div class="bizproc-automation-popup-settings__transition-height-content">
							<div class="bizproc-automation-popup-settings__condition-help">
								${Text.encode(this.#options.caption?.collapsed || Loc.getMessage('BIZPROC_JS_AUTOMATION_CONDITION_COLLAPSED_TITLE_1'))}
							</div>
						</div>
					</div>
				</div>
			</div>
		`;
		this.#toggleButtonNode = btnToggleList;
		this.#draggableNode = draggableNode;

		Event.bind(btnToggleList, 'click', this.#onToggleGroupViewClick.bind(this, conditionContent, btnTextNode));
		Event.bind(addButton, 'click', this.addItem.bind(this));
		this.#initDragNDrop();

		return root;
	}

	#onToggleGroupViewClick(content, toggleText)
	{
		Dom.toggleClass(content, '--active');
		const isExpanded = Dom.hasClass(content, '--active');

		Dom.adjust(
			toggleText,
			{
				text: (
					isExpanded
						? Loc.getMessage('BIZPROC_JS_AUTOMATION_COLLAPSE_CONDITION')
						: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXPAND_CONDITION')
				),
			},
		);

		this.emit(
			'onToggleGroupViewClick',
			new BaseEvent({ data: { isCollapsed: !isExpanded, isExpanded } }),
		);
	}

	#initDragNDrop()
	{
		new Draggable({
			container: this.#draggableNode,
			type: Draggable.CLONE,
			draggable: '.bizproc-automation-popup-settings__condition-selector',
			dragElement: '.bizproc-automation-popup-settings__condition-item_draggable',
		});
	}

	addItem()
	{
		const conditionSelector = new ConditionSelector(new Condition({}, this.#conditionGroup), {
			fields: this.#fields,
			fieldPrefix: this.#fieldPrefix,
			rootGroupTitle: this.#rootGroupTitle,
			onOpenFieldMenu: this.#onOpenFieldMenu,
			onOpenMenu: this.#onOpenMenu,
			showValuesSelector: this.#showValuesSelector,
		});
		conditionSelector.subscribe('onRemoveConditionClick', this.#onRemoveConditionClick.bind(this));
		this.#itemSelectors.push(conditionSelector);

		Dom.append(conditionSelector.createNode(), this.#draggableNode);
		if (Dom.hasClass(this.#toggleButtonNode, '--disabled'))
		{
			Dom.removeClass(this.#toggleButtonNode, '--disabled');
		}
	}

	#onRemoveConditionClick(event: BaseEvent)
	{
		const conditionSelector = event.getData().conditionSelector;
		if (conditionSelector)
		{
			const index = this.#itemSelectors.indexOf(conditionSelector);
			if (index > -1)
			{
				this.#itemSelectors.splice(index, 1);
			}
		}

		if (this.#itemSelectors.length <= 0 && !Dom.hasClass(this.#toggleButtonNode, '--disabled'))
		{
			Dom.addClass(this.#toggleButtonNode, '--disabled');
		}
	}

	destroy()
	{
		this.#itemSelectors.forEach((selector) => selector.destroy());
		this.#itemSelectors = [];
	}
}
