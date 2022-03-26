import {Dom, Type} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';

import BaseProcessor from './processor/base_processor';
import Color from './processor/color';
import ColorHover from './processor/color_hover';
import Bg from './processor/bg';
import BorderColor from './processor/border_color';
import BorderColorHover from './processor/border_color_hover';
import BgColor from './processor/bg_color';
import BgColorHover from './processor/bg_color_hover';
import BgColorAfter from './processor/bg_color_after';
import BgColorBefore from './processor/bg_color_before';
import NavbarColor from './processor/navbar_color';
import NavbarColorHover from './processor/navbar_color_hover';
import NavbarColorFixMoment from './processor/navbar_color_fix_moment';
import NavbarColorFixMomentHover from './processor/navbar_color_fix_moment_hover';
import NavbarBgColor from './processor/navbar_bg';
import NavbarBgColorHover from './processor/navbar_bg_hover';
import BorderColorTop from './processor/border_color-top';
import FillColor from './processor/fill_color';
import FillColorSecond from './processor/fill_color_second';
import ButtonColor from './processor/button_color';
import {IColorValue} from './types/i_color_value';
import NavbarCollapseBgColor from './processor/navbar_collapse_bg';

export class ColorField extends BaseField
{
	processor: BaseProcessor;

	constructor(options)
	{
		super(options);
		this.items = ('items' in options && options.items) ? options.items : [];
		this.postfix = (typeof options.postfix === 'string') ? options.postfix : '';
		this.frame = (typeof options.frame === 'object') ? options.frame : null;
		const processorOptions = {
			block: options.block,
			styleNode: options.styleNode,
			selector: options.selector,
			contentRoot: this.contentRoot,
		};

		this.changeHandler = (typeof options.onChange === "function") ? options.onChange : (() => {});
		this.resetHandler = (typeof options.onReset === "function") ? options.onReset : (function () {});

		// todo: rename "subtype"
		switch (options.subtype)
		{
			case 'color':
				this.processor = new Color(processorOptions);
				break;

			case 'color-hover':
				this.processor = new ColorHover(processorOptions);
				break;

			case 'bg':
				this.processor = new Bg(processorOptions);
				break;

			case 'bg-color':
				this.processor = new BgColor(processorOptions);
				break;

			case 'bg-color-hover':
				this.processor = new BgColorHover(processorOptions);
				break;

			case 'bg-color-after':
				this.processor = new BgColorAfter(processorOptions);
				break;

			case 'bg-color-before':
				this.processor = new BgColorBefore(processorOptions);
				break;

			case 'border-color':
				this.processor = new BorderColor(processorOptions);
				break;

			case 'border-color-hover':
				this.processor = new BorderColorHover(processorOptions);
				break;

			case 'border-color-top':
				this.processor = new BorderColorTop(processorOptions);
				break;

			case 'navbar-color':
				this.processor = new NavbarColor(processorOptions);
				break;

			case 'navbar-color-hover':
				this.processor = new NavbarColorHover(processorOptions);
				break;

			case 'navbar-color-fix-moment':
				this.processor = new NavbarColorFixMoment(processorOptions);
				break;

			case 'navbar-color-fix-moment-hover':
				this.processor = new NavbarColorFixMomentHover(processorOptions);
				break;

			case 'navbar-bg-color':
				this.processor = new NavbarBgColor(processorOptions);
				break;

			case 'navbar-bg-color-hover':
				this.processor = new NavbarBgColorHover(processorOptions);
				break;

			case 'navbar-collapse-bg-color':
				this.processor = new NavbarCollapseBgColor(processorOptions);
				break;

			case 'fill-color':
				this.processor = new FillColor(processorOptions);
				break;

			case 'fill-color-second':
				this.processor = new FillColorSecond(processorOptions);
				break;

			case 'button-color':
				this.processor = new ButtonColor(processorOptions);
				break;

			default:
				break;
		}

		this.property = this.processor.getProperty()[this.processor.getProperty().length - 1];
		this.processor.getClassName().forEach(
			item => this.items.push({name: item, value: item}),
		);

		// todo: what a input?
		Dom.remove(this.input);
		this.layout.classList.add("landing-ui-field-color");
		Dom.append(this.processor.getLayout(), this.layout);

		this.processor.subscribe('onChange', this.onChange.bind(this));
		this.processor.subscribe('onReset', this.onReset.bind(this));
	}

	getInlineProperties(): [string]
	{
		return this.processor.getVariableName();
	}

	getComputedProperties(): [string]
	{
		return this.processor.getProperty();
	}

	getPseudoElement(): ?string
	{
		return this.processor.getPseudoClass();
	}

	onChange()
	{
		this.changeHandler(
			{
				className: this.processor.getClassName(),
				style: this.processor.getStyle(),
			},
			this.items,
			this.postfix,
			this.property,
		);

		this.emit('onChange');
	}

	onReset()
	{
		this.resetHandler(this.items, this.postfix, this.property);
	}

	getValue(): IColorValue
	{
		return this.processor.getValue() || this.processor.getNullValue();
	}

	setValue(value: {string: ?string})
	{
		let processorValue = null;
		// now for multiple properties get just last value. Maybe, need object-like values
		this.getInlineProperties().forEach(prop => {
			if (prop in value && !this.processor.isNullValue(value[prop]))
			{
				if (!Type.isObject(processorValue))
				{
					processorValue = {};
				}
				processorValue[prop] = value[prop];
			}
		});

		let defaultValue = null;
		this.getComputedProperties().forEach(prop => {
			if (prop in value && !this.processor.isNullValue(value[prop]))
			{
				if (!Type.isObject(defaultValue))
				{
					defaultValue = {};
				}
				defaultValue[prop] = value[prop];
			}
		});

		if (processorValue !== null)
		{
			this.processor.setProcessorValue(processorValue);
		}
		else
		{
			this.processor.setDefaultValue(defaultValue);
			this.processor.defineActiveControl(this.items, this.data.styleNode);
		}
	}

	onFrameLoad()
	{
		// todo: now not work with "group select", can use just any node from elements. If group - need forEach

		const value = this.data.styleNode.getValue(true);
		this.setValue(value.style);
	}
}