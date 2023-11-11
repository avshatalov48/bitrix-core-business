import { Type, Loc, Text, Dom, Tag, Runtime, Event } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { Menu, MenuItem } from 'main.popup';
import { InlineSelector } from './inline-selector';
import { SelectorContext } from 'bizproc.automation';

export class InlineTimeSelector extends InlineSelector
{
	#labelNode: HTMLElement = null;
	#inputNode: HTMLInputElement = null;
	#showDottedSelector: boolean;

	#timeValues: [] = [];
	#timeFormat: string;

	#selector: Menu;
	#chevron: HTMLSpanElement;

	constructor(props: { context: SelectorContext, showValuesSelector: boolean })
	{
		super(props);

		this.#fillTimeFormat();
		this.#fillTimeValues();

		this.#showDottedSelector = Type.isNil(props.showValuesSelector) ? true : Text.toBoolean(props.showValuesSelector);
	}

	#fillTimeFormat()
	{
		const getFormat = (formatId) => (
			BX.Main.Date.convertBitrixFormat(Loc.getMessage(formatId)).replace(/:?\s*s/, '')
		);

		const dateFormat = getFormat('FORMAT_DATE');
		const dateTimeFormat = getFormat('FORMAT_DATETIME');
		this.#timeFormat = dateTimeFormat.replace(dateFormat, '').trim();
	}

	#fillTimeValues()
	{
		const onclick = (event, item: MenuItem) => {
			event.preventDefault();
			this.#inputNode.value = Text.encode(item.text);
			item.getMenuWindow().close();
		};

		for (let hour = 0; hour < 24; hour++)
		{
			this.#timeValues.push({
				id: hour * 60,
				text: this.#formatTime(hour, 0),
				onclick,
			}, {
				id: hour * 60 + 30,
				text: this.#formatTime(hour, 30),
				onclick,
			});
		}
	}

	#formatTime(hour, minute): string
	{
		const date = new Date();
		date.setHours(hour, minute);

		return DateTimeFormat.format(this.#timeFormat, date.getTime() / 1000);
	}

	renderWith(targetInput: Element): HTMLDivElement
	{
		this.targetInput = Runtime.clone(targetInput);
		this.targetInput.setAttribute('autocomplete', 'off');

		this.parseTargetProperties();
		this.replaceOnWrite = true;

		if (this.#showDottedSelector === false)
		{
			return this.#labelNode;
		}

		const { root, menuButton } = Tag.render`
			<div class="bizproc-automation-popup-select">
				${this.#labelNode}
				<span
					ref="menuButton"
					onclick="${this.openMenu.bind(this)}"
					class="bizproc-automation-popup-select-dotted"
				></span>
			</div>
		`;
		this.menuButton = menuButton;

		return root;
	}

	parseTargetProperties()
	{
		super.parseTargetProperties();
		this.#init();
	}

	#init()
	{
		const targetInput = this.targetInput;
		const hasParentNode = Type.isDomNode(this.targetInput.parentNode);
		if (hasParentNode)
		{
			this.targetInput = Runtime.clone(targetInput);
		}

		const { root, chevron } = Tag.render`
			<span onclick="${this.#onLabelClick.bind(this)}" style="width: 100%; position: relative">
				${this.targetInput}
				<span 
					ref="chevron"
					class="ui-icon-set --chevron-down bizproc-automation-inline-time-selector-chevron"
				></span>
			</span>
		`;

		this.#labelNode = root;
		this.#inputNode = this.targetInput;
		this.#chevron = chevron;

		if (hasParentNode)
		{
			Dom.replace(targetInput, this.#labelNode);
		}
	}

	#onLabelClick(event)
	{
		this.#showTimeSelector();
		event.preventDefault();
	}

	#showTimeSelector()
	{
		if (Type.isNil(this.#selector))
		{
			this.#selector = new Menu({
				autoHide: true,
				bindElement: this.#labelNode,
				items: this.#timeValues,
				maxHeight: 230,
				width: this.#labelNode.offsetWidth || this.#labelNode.clientWidth || 100,
				events: {
					onPopupClose: () => {
						if (Dom.hasClass(this.#chevron, '--chevron-up'))
						{
							Dom.toggleClass(this.#chevron, ['--chevron-down', '--chevron-up']);
						}
					},
				},
			});
		}

		this.#selector.show();
		if (Dom.hasClass(this.#chevron, '--chevron-down'))
		{
			Dom.toggleClass(this.#chevron, ['--chevron-down', '--chevron-up']);
		}
	}
}
