import {BaseEvent, EventEmitter} from 'main.core.events';
import {Cache, Dom, Tag, Text} from 'main.core';
import {Draggable} from 'ui.draganddrop.draggable';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';
import {Loc} from 'landing.loc';
import {PageObject} from 'landing.pageobject';

import './css/opacity.css';

export default class Opacity extends EventEmitter
{
	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Opacity');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.options = {...options};
		this.cache = new Cache.MemoryCache();
		this.onPickerDragStart = this.onPickerDragStart.bind(this);
		this.onPickerDragMove = this.onPickerDragMove.bind(this);
		this.onPickerDragEnd = this.onPickerDragEnd.bind(this);

		// @fixme: Add 'context' parameter for Draggable
		this.draggable = new window.top.BX.UI.DragAndDrop.Draggable({
			container: this.getLayout(),
			draggable: '.landing-ui-field-color-opacity-picker',
			type: Draggable.HEADLESS,
		});

		this.draggable.subscribe('start', this.onPickerDragStart);
		this.draggable.subscribe('move', this.onPickerDragMove);
		this.draggable.subscribe('end', this.onPickerDragEnd);
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-opacity">
					${this.getPicker()}
					${this.getColorLayout()}
				</div>
			`;
		});
	}

	getColorLayout(): HTMLDivElement
	{
		return this.cache.remember('colorLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-opacity-color"></div>
			`;
		});
	}

	getColorLayoutWidth(): number
	{
		return this.cache.remember('colorLayoutWidth', () => {
			return this.getColorLayout().getBoundingClientRect().width - 6;
		});
	}

	getPicker(): HTMLDivElement
	{
		return this.cache.remember('picker', () => {
			return Tag.render`
				<div 
					class="landing-ui-field-color-opacity-picker"
					title="${Loc.getMessage('LANDING_COLORPICKER_FIELD_CHANGE_COLOR_OPACITY')}"
				></div>
			`;
		});
	}

	getValue(): number
	{
		const pickerLeft = Text.toNumber(Dom.style(this.getPicker(), 'left'));
		const layoutWidth = Text.toNumber(this.getLayout().getBoundingClientRect().width);

		return (1 - (pickerLeft / layoutWidth).toFixed(1));
	}

	setValue({parsedColor, skipOpacity = false})
	{
		const from = `rgba(${[parsedColor.slice(0, 3), 100].join(', ')})`;
		const to = `rgba(${[parsedColor.slice(0, 3), 0].join(', ')})`;

		Dom.style(this.getColorLayout(), {
			background: `linear-gradient(to right, ${from} 0%, ${to} 100%)`,
		});

		if (!skipOpacity)
		{
			const opacity = parsedColor[3] || 0;
			const leftPercent = (100 - (opacity * 100));

			Dom.style(this.getPicker(), {
				left: `calc(${leftPercent}% - ${leftPercent === 100 ? '6px' : '0px'})`,
			});
		}
	}

	onPickerDragStart()
	{
		this.cache.set('pickerStartPos', {
			left: Text.toNumber(Dom.style(this.getPicker(), 'left')),
		});

		const wrapper = PageObject
			.getRootWindow()
			.document
			.querySelector('.landing-ui-view-wrapper');

		Dom.style(wrapper, 'pointer-events', 'none');
	}

	onPickerDragMove(event: BaseEvent)
	{
		const {offsetX} = event.getData();
		const {left} = this.cache.get('pickerStartPos');

		const leftPos = Math.min(Math.max(left + offsetX, 0), this.getColorLayoutWidth());

		Dom.style(this.getPicker(), {
			left: `${leftPos}px`,
		});

		this.emit('onChange');
	}

	onPickerDragEnd()
	{
		const wrapper = PageObject
			.getRootWindow()
			.document
			.querySelector('.landing-ui-view-wrapper');

		Dom.style(wrapper, 'pointer-events', null);
	}
}