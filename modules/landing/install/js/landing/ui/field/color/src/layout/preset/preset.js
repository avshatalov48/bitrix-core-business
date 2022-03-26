import {Tag, Dom, Event, Type, Cache} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {IColorValue} from '../../types/i_color_value';
import ColorValue from '../../color_value';
import GradientValue from '../../gradient_value';
import {PresetOptions, defaultType, gradientType} from './types/preset-options';
import Generator from './generator';

import './css/preset.css';

export default class Preset extends EventEmitter
{
	id: string;
	type: 'color' | 'gradient';
	items: [ColorValue | GradientValue];
	activeItem: string | IColorValue | null;

	static ACTIVE_CLASS: string = 'active';

	constructor(options: PresetOptions)
	{
		super();
		this.cache = new Cache.MemoryCache();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Preset');

		this.id = options.id;
		this.type = options.type || defaultType;
		this.items = options.items;
		this.activeItem = null;
	}

	getId(): string
	{
		return this.id;
	}

	getGradientPreset(): Preset
	{
		const options = (this.type === gradientType)
			? {type: gradientType, items: this.items}
			: Generator.getGradientByColorOptions({items: this.items});

		return new Preset(options);
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-preset">
					${this.items.map((item) => {
						return this.getItemLayout(item.getName());
					})}
				</div>
			`;
		});
	}

	getItemLayout(name: string): HTMLDivElement
	{
		return this.cache.remember(name, () => {
			const color = this.getItemByName(name);
			const style = Type.isString(color) ? color : color.getStyleString();
			const item = Tag.render`
				<div
					class="landing-ui-field-color-preset-item"
					style="background: ${style}"
					data-name="${name}"
				></div>
			`;
			Event.bind(item, 'click', this.onItemClick.bind(this));

			return item;
		});
	}

	getItemByName(name: string): string | IColorValue | null
	{
		return this.items.find(item => name === item.getName()) || null;
	}

	isPresetValue(value: ColorValue | GradientValue | null): boolean
	{
		if (value === null)
		{
			return false;
		}
		return this.items.some(item => {
			if (item instanceof ColorValue && value instanceof ColorValue)
			{
				return ColorValue.compare(item, new ColorValue(value).setOpacity(1));
			}
			else if (item instanceof GradientValue && value instanceof GradientValue)
			{
				return GradientValue.compare(item, value, false);
			}
			return false;
		});
	}

	onItemClick(event: MouseEvent)
	{
		this.setActiveItem(event.currentTarget.dataset.name);

		let value = null;
		if (this.activeItem !== null)
		{
			value = this.activeItem instanceof GradientValue
				? new GradientValue(this.activeItem)
				: new ColorValue(this.activeItem);
		}

		this.emit('onChange', {color: value});
	}

	setActiveItem(name: string)
	{
		this.activeItem = this.getItemByName(name);

		this.items.forEach((item) => {
			const itemName = item.getName();
			if (name === itemName)
			{
				Dom.addClass(this.getItemLayout(itemName), Preset.ACTIVE_CLASS);
			}
			else
			{
				Dom.removeClass(this.getItemLayout(itemName), Preset.ACTIVE_CLASS);
			}
		});
	}

	setActiveValue(value: ColorValue | GradientValue | null)
	{
		if (value !== null)
		{
			if (value instanceof GradientValue)
			{
				this.setActiveItem(
					new GradientValue(value)
						.setAngle(GradientValue.DEFAULT_ANGLE)
						.setType(GradientValue.DEFAULT_TYPE)
						.getName()
				);
			}
			else
			{
				this.setActiveItem(
					new ColorValue(value)
						.setOpacity(1)
						.getName()
				);
			}
		}
	}

	unsetActive()
	{
		this.items.forEach(item => {
			Dom.removeClass(this.getItemLayout(item.getName()), Preset.ACTIVE_CLASS);
		});
	}

	isActive(): boolean
	{
		return this.items.some(item => {
			return Dom.hasClass(this.getItemLayout(item.getName()), Preset.ACTIVE_CLASS);
		});
	}
}