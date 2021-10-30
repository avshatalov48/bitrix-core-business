import {BaseEvent} from 'main.core.events';
import {Dom, Event, Tag, Text} from 'main.core';

import {IColorValue} from '../../types/i_color_value';
import ColorValue from "../../color_value";
import './css/opacity.css';
import BaseControl from "../base_control/base_control";
import {PageObject} from 'landing.pageobject';

export default class Opacity extends BaseControl
{
	static +DEFAULT_COLOR: string = '#cccccc';
	static +PICKER_WIDTH: string = '#cccccc';

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Opacity');

		this.document = PageObject.getRootWindow().document;

		this.onPickerDragStart = this.onPickerDragStart.bind(this);
		this.onPickerDragMove = this.onPickerDragMove.bind(this);
		this.onPickerDragEnd = this.onPickerDragEnd.bind(this);
		Event.bind(this.getLayout(), 'mousedown', this.onPickerDragStart);
	}

	buildLayout(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-opacity">
				${this.getPicker()}
				${this.getColorLayout()}
			</div>
		`;
	}

	onPickerDragStart(event: MouseEvent)
	{
		if (event.ctrlKey || event.metaKey || event.button)
		{
			return;
		}

		Event.bind(this.document, 'mousemove', this.onPickerDragMove);
		Event.bind(this.document, 'mouseup', this.onPickerDragEnd);

		Dom.addClass(this.document.body, 'landing-ui-field-color-draggable');

		this.onPickerDragMove(event);
	}

	onPickerDragMove(event: MouseEvent)
	{
		if (event.target === this.getPicker())
		{
			return;
		}
		this.setPickerPos(event.pageX);
		this.onChange();
	}

	onPickerDragEnd()
	{
		Event.unbind(this.document, 'mousemove', this.onPickerDragMove);
		Event.unbind(this.document, 'mouseup', this.onPickerDragEnd);

		Dom.removeClass(this.document.body, 'landing-ui-field-color-draggable');
	}

	/**
	 * Set picker by absolute page coords
	 * @param x
	 */
	setPickerPos(x: number)
	{
		const leftPos = Math.max(Math.min((x - this.getLayoutRect().left), this.getLayoutRect().width), 0);
		Dom.style(this.getPicker(), {
			left: `${leftPos}px`,
		});
	}

	getLayoutRect(): {}
	{
		return this.cache.remember('layoutSize', () => {
			const layoutRect = this.getLayout().getBoundingClientRect();
			return {
				width: layoutRect.width,
				left: layoutRect.left,
			};
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

	getPicker(): HTMLDivElement
	{
		return this.cache.remember('picker', () => {
			return Tag.render`
				<div class="landing-ui-field-color-opacity-picker">
					<div class="landing-ui-field-color-opacity-picker-item">
						<div class="landing-ui-field-color-opacity-picker-item-circle"></div>
					</div>
				</div>`;
		});
	}

	getDefaultValue(): ColorValue
	{
		return this.cache.remember('default', () => {
			return new ColorValue(Opacity.DEFAULT_COLOR);
		});
	}

	getValue(): ColorValue
	{
		return this.cache.remember('value', () => {
			const pickerLeft = Text.toNumber(Dom.style(this.getPicker(), 'left'));
			const layoutWidth = Text.toNumber(this.getLayout().getBoundingClientRect().width);

			return this.getDefaultValue().setOpacity((1 - pickerLeft / layoutWidth));
		});
	}

	setValue(value: ?IColorValue)
	{
		const valueToSet = (value !== null) ? value : this.getDefaultValue();
		super.setValue(valueToSet);

		Dom.style(this.getColorLayout(), {background: valueToSet.getStyleStringForOpacity()});
		Dom.style(this.getPicker(), {
			left: `${100 - (valueToSet.getOpacity() * 100)}%`,
		});
	}

	onChange(event: ?BaseEvent)
	{
		this.cache.delete('value');
		this.emit('onChange', {color: this.getValue()});
	}
}
