import {Tag, Type, Loc} from 'main.core';
import {BaseEvent} from "main.core.events";

import BgColor from './bg_color';
import Image from '../control/image/image';
import ColorSet from '../control/color_set/color_set';

import ColorValue from "../color_value";
import BgImageValue from '../bg_image_value';
import {IColorValue} from '../types/i_color_value';
import Opacity from '../control/opacity/opacity';
import Tabs from '../layout/tabs/tabs';
import GradientValue from '../gradient_value';
import Preset from '../layout/preset/preset';

export default class Bg extends BgColor
{
	static BG_URL_VAR: string = '--bg-url';
	static BG_URL_2X_VAR: string = '--bg-url-2x';
	static BG_OVERLAY_VAR: string = '--bg-overlay';
	static BG_SIZE_VAR: string = '--bg-size';
	static BG_ATTACHMENT_VAR: string = '--bg-attachment';

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.Bg');
		this.parentVariableName = this.variableName;
		this.variableName = [
			this.parentVariableName,
			Bg.BG_URL_VAR,
			Bg.BG_URL_2X_VAR,
			Bg.BG_OVERLAY_VAR,
			Bg.BG_SIZE_VAR,
			Bg.BG_ATTACHMENT_VAR,
		];
		this.parentClassName = this.className;
		this.className = 'g-bg-image';

		this.image = new Image(options);
		this.image.subscribe('onChange', this.onImageChange.bind(this));

		this.overlay = new ColorSet(options);
		this.overlay.subscribe('onChange', this.onOverlayChange.bind(this));
		this.overlayOpacity = new Opacity({defaultOpacity: 0.5});
		this.overlayOpacity.subscribe('onChange', this.onOverlayOpacityChange.bind(this));

		this.imageTabs = new Tabs().appendTab(
			'Overlay',
			Loc.getMessage('LANDING_FIELD_COLOR-BG_OVERLAY'),
			[this.overlay, this.overlayOpacity],
		);

		this.bigTabs = new Tabs()
			.setBig(true)
			.appendTab(
				'Color',
				Loc.getMessage('LANDING_FIELD_COLOR-BG_COLOR'),
				[this.colorSet, this.primary, this.zeroing, this.tabs],
			)
			.appendTab(
				'Image',
				Loc.getMessage('LANDING_FIELD_COLOR-BG_IMAGE'),
				[this.image, this.imageTabs],
			);
	}

	buildLayout(): HTMLElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-color">
				${this.bigTabs.getLayout()}
			</div>
		`;
	}

	onColorSetChange(event: BaseEvent)
	{
		this.image.unsetActive();
		this.overlay.unsetActive();

		super.onColorSetChange(event);
	}

	onGradientChange(event: BaseEvent)
	{
		this.image.unsetActive();
		this.overlay.unsetActive();

		super.onGradientChange(event);
	}

	onImageChange()
	{
		// todo: can drop image from b_landing_file after change
		this.unsetActive();

		this.activeControl = this.image;
		this.image.setActive();

		this.onChange();
	}

	onOverlayChange(event: BaseEvent)
	{
		const overlayValue = event.getData().color;
		if (overlayValue !== null)
		{
			overlayValue.setOpacity(this.overlayOpacity.getValue().getOpacity());
		}
		this.overlayOpacity.setValue(overlayValue);

		const imageValue = this.image.getValue();
		if (imageValue !== null)
		{
			this.image.setValue(imageValue.setOverlay(overlayValue));
			this.activeControl = this.image;
			this.image.setActive();
			this.colorSet.unsetActive();
			this.gradient.unsetActive();
		}

		this.onChange();
	}

	onOverlayOpacityChange()
	{
		this.onChange();
	}

	unsetActive()
	{
		super.unsetActive();
		this.image.unsetActive();
	}

	/**
	 * Set value by new format
	 */
	setProcessorValue(value: {string: string}): void
	{
		this.cache.delete('value');
		this.setValue(value);
	}

	setValue(value: {string: string} | string | null): void
	{
		this.image.setValue(null);
		this.bigTabs.showTab('Color');

		if (Type.isNull(value))
		{
			super.setValue(value);
		}
		else if (Type.isString(value))
		{
			super.setValue(value);
		}
		else if (this.parentVariableName in value && Type.isString(value[this.parentVariableName]))
		{
			super.setValue(value[this.parentVariableName]);
		}
		else if (Type.isObject(value))
		{
			// todo: super.setValue null?
			const bgValue = new BgImageValue();
			if (Bg.BG_URL_VAR in value)
			{
				bgValue.setUrl(value[Bg.BG_URL_VAR].replace(/url\(["']/i, '').replace(/['"]\)/i, ''));
			}
			if (Bg.BG_URL_2X_VAR in value)
			{
				bgValue.setUrl2x(value[Bg.BG_URL_2X_VAR].replace(/url\(["']/i, '').replace(/['"]\)/i, ''));
			}
			if (Bg.BG_SIZE_VAR in value)
			{
				bgValue.setSize(value[Bg.BG_SIZE_VAR]);
			}
			if (Bg.BG_ATTACHMENT_VAR in value)
			{
				bgValue.setAttachment(value[Bg.BG_ATTACHMENT_VAR]);
			}
			if (Bg.BG_OVERLAY_VAR in value)
			{
				bgValue.setOverlay(new ColorValue(value[Bg.BG_OVERLAY_VAR]));
			}

			this.image.setValue(bgValue);
			this.bigTabs.showTab('Image');
			this.activeControl = this.image;

			if (Bg.BG_OVERLAY_VAR in value)
			{
				const overlayValue = new ColorValue(value[Bg.BG_OVERLAY_VAR]);
				this.overlay.setValue(overlayValue);
				this.overlayOpacity.setValue(overlayValue);
				this.imageTabs.showTab('Overlay');
			}
		}
	}

	// todo: create base value instead interface. In this case can return ALL types, color, grad, bg
	getValue(): ?IColorValue
	{
		return this.cache.remember('value', () => {
			if (this.activeControl === this.image)
			{
				const imageValue = this.image.getValue();
				const overlayValue = this.overlay.getValue();
				if (imageValue !== null && this.overlay.isActive() && overlayValue !== null)
				{
					overlayValue.setOpacity(this.overlayOpacity.getValue().getOpacity());
					imageValue.setOverlay(overlayValue);
				}

				return imageValue;
			}
			else
			{
				return super.getValue();
			}
		});
	}

	getClassName(): [string]
	{
		const value = this.getValue();
		if (value === null || value instanceof ColorValue || value instanceof GradientValue)
		{
			return [this.parentClassName];
		}

		return [this.className];
	}

	// todo: what about fileid?
	getStyle(): {string: ?string}
	{
		if (this.getValue() === null)
		{
			// todo: not null, but what?
			return {
				[this.parentVariableName]: null,
				[Bg.BG_URL_VAR]: null,
				[Bg.BG_URL_2X_VAR]: null,
				[Bg.BG_OVERLAY_VAR]: null,
				[Bg.BG_SIZE_VAR]: null,
				[Bg.BG_ATTACHMENT_VAR]: null,
			};
		}

		const value = this.getValue();
		let color = null;
		let image = null;
		let image2x = null;
		let overlay = null;
		// let size = 'cover';
		let size = null;
		// let attachment = 'scroll';
		let attachment = null;
		if (value instanceof ColorValue || value instanceof GradientValue)
		{
			// todo: need change class if not a image?
			color = value.getStyleString();
		}
		else
		{
			image = value.getUrl() ? `url('${value.getUrl()}')` : '';
			image2x = value.getUrl2x() ? `url('${value.getUrl2x()}')` : '';
			overlay = value.getOverlay() ? value.getOverlay().getStyleString() : 'rgba(0, 0, 0, 0)';
			size = value.getSize();
			attachment = value.getAttachment();
		}

		return {
			[this.parentVariableName]: color,
			[Bg.BG_URL_VAR]: image,
			[Bg.BG_URL_2X_VAR]: image2x,
			[Bg.BG_OVERLAY_VAR]: overlay,
			[Bg.BG_SIZE_VAR]: size,
			[Bg.BG_ATTACHMENT_VAR]: attachment,
		};
	}
}
