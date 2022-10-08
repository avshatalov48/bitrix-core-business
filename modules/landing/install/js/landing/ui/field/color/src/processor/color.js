import {Tag, Loc, Dom, Type} from 'main.core';
import {BaseEvent} from 'main.core.events';

import BaseProcessor from './base_processor';
import ColorSet from "../control/color_set/color_set";
import Opacity from "../control/opacity/opacity";
import Tabs from '../layout/tabs/tabs';
import Primary from '../layout/primary/primary';

import ColorValue from "../color_value";
import {IColorValue} from '../types/i_color_value';
import Zeroing from '../layout/zeroing/zeroing';

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

		this.zeroing = new Zeroing();
		this.zeroing.subscribe('onChange', this.onZeroingChange.bind(this));

		this.primary = new Primary();
		this.primary.subscribe('onChange', this.onPrimaryChange.bind(this));

		this.tabs = new Tabs().appendTab('Opacity', Loc.getMessage('LANDING_FIELD_COLOR-TAB_OPACITY'), this.opacity);
	}

	isNullValue(value: ?string): boolean
	{
		return (
			value === null
			|| value === 'none'
			|| value === 'rgba(0, 0, 0, 0)'
		);
	}

	getNullValue()
	{
		return new ColorValue('rgba(0, 0, 0, 0)');
	}

	buildLayout(): HTMLElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-color">
				${this.colorSet.getLayout()}
				${this.primary.getLayout()}
				${this.zeroing.getLayout()}
				${this.tabs.getLayout()}
			</div>
		`;
	}

	onColorSetChange(event: BaseEvent)
	{
		this.primary.unsetActive();
		this.zeroing.unsetActive();

		const color = event.getData().color;
		if (color !== null)
		{
			color.setOpacity(this.opacity.getValue().getOpacity());
		}
		this.opacity.setValue(color);

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
		this.zeroing.unsetActive();
		this.primary.setActive();
	}

	onZeroingChange(event: BaseEvent)
	{
		this.colorSet.unsetActive();
		this.primary.unsetActive();
		this.zeroing.setActive();
		this.setValue(event.getData().color);
		// todo: need reload computed props and reinit
		this.onChange(event);
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

	setDefaultValue(value: {string: string} | null)
	{
		this.zeroing.setActive();
		if (!Type.isNull(value))
		{
			this.colorSet.colorpicker.hex.setActive();
		}
		super.setDefaultValue(value);
	}

	onReset()
	{
		this.zeroing.unsetActive();
		super.onReset();
	}

	setActiveControl(controlName)
	{
		if (controlName === 'primary')
		{
			this.primary.setActive();
		}
		if (controlName === 'hex')
		{
			this.colorSet.colorpicker.hexPreview.setActive();
		}
	}

	defineActiveControl(items, styleNode)
	{
		if (!Type.isUndefined(styleNode))
		{
			let oldClass;
			let activeControl;
			const node = styleNode.getNode();
			if (node.length > 0)
			{
				items.forEach((item) => {
					if (Dom.hasClass(node[0], item.value))
					{
						oldClass = item.value;
					}
				})
				if (oldClass)
				{
					const reg = /g-[a-z]+-[a-z0-9-]+/i;
					const found = oldClass.match(reg);
					if (found)
					{
						const reg = /primary/i;
						const found = oldClass.match(reg);
						this.zeroing.unsetActive();
						if (found)
						{
							activeControl = 'primary';
						}
						else
						{
							activeControl = 'hex';
						}
					}
				}
				if (activeControl)
				{
					this.setActiveControl(activeControl);
				}
			}
		}
	}
}
