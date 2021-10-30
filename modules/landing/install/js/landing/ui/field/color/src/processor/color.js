import {Tag, Loc} from 'main.core';
import {BaseEvent} from 'main.core.events';

import BaseProcessor from './base_processor';
import ColorSet from "../control/color_set/color_set";
import Opacity from "../control/opacity/opacity";
import Tabs from '../layout/tabs/tabs';
import Primary from '../layout/primary/primary';

import ColorValue from "../color_value";
import {IColorValue} from '../types/i_color_value';

export default class Color extends BaseProcessor
{
	static PRIMARY_VAR: string = 'var(--primary)';

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.Color');
		this.property = 'color';
		this.variableName = '--color';
		this.className = 'g-color';

		this.colorSet = new ColorSet(options);
		this.colorSet.subscribe('onChange', this.onColorSetChange.bind(this));
		this.colorSet.subscribe('onReset', this.onReset.bind(this));

		this.opacity = new Opacity();
		this.opacity.subscribe('onChange', this.onOpacityChange.bind(this));

		this.primary = new Primary();
		this.primary.subscribe('onChange', this.onPrimaryChange.bind(this));

		this.tabs = new Tabs().appendTab('Opacity', Loc.getMessage('LANDING_FIELD_COLOR-TAB_OPACITY'), this.opacity);
	}

	isNullValue(value: ?string): boolean
	{
		// todo: check different browsers
		return (
			value === null
			|| value === 'none'
			|| value === 'rgba(0, 0, 0, 0)'
		);
	}

	buildLayout(): HTMLElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-color">
				${this.colorSet.getLayout()}
				${this.primary.getLayout()}
				${this.tabs.getLayout()}
			</div>
		`;
	}

	onColorSetChange(event: BaseEvent)
	{
		this.primary.unsetActive();

		const color = event.getData().color;
		if (color !== null)
		{
			color.setOpacity(this.opacity.getValue().getOpacity());
			this.opacity.setValue(color);
		}

		this.onChange();
	}

	onOpacityChange()
	{
		this.onChange();
	}

	onPrimaryChange(event: BaseEvent)
	{
		this.colorSet.setValue(event.getData().color);
		this.onColorSetChange(event);

		this.colorSet.unsetActive();
		this.primary.setActive();
	}

	unsetActive()
	{
		this.colorSet.unsetActive();
		this.primary.unsetActive();
	}

	setValue(value: ?string): void
	{
		const valueObj = (value !== null) ? new ColorValue(value) : null;

		this.colorSet.setValue(valueObj);
		this.opacity.setValue(valueObj);

		// todo: what about opacity in primary?
		if (this.primary.isPrimaryValue(valueObj))
		{
			this.primary.setActive();
			this.colorSet.unsetActive();
		}

		if (value !== null && valueObj.getOpacity() < 1)
		{
			this.tabs.showTab('Opacity');
		}
	}

	getValue(): ?IColorValue
	{
		return this.cache.remember('value', () => {
			const value = this.primary.isActive() ? this.primary.getValue() : this.colorSet.getValue();

			return (value === null)
				? null
				: value.setOpacity(this.opacity.getValue().getOpacity());
		});
	}
}
