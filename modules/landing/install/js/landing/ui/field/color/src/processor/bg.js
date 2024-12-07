import {Tag, Type, Loc, Dom} from 'main.core';
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
import Primary from '../layout/primary/primary';
import Zeroing from '../layout/zeroing/zeroing';
import {defaultBgImageSize, defaultBgImageAttachment} from "../types/color_value_options";
import rgbaStringToRgbString from '../internal/rgba-string-to-rgb-string';

export default class Bg extends BgColor
{
	static BG_URL_VAR: string = '--bg-url';
	static BG_URL_2X_VAR: string = '--bg-url-2x';
	static BG_OVERLAY_VAR: string = '--bg-overlay';
	static BG_SIZE_VAR: string = '--bg-size';
	static BG_ATTACHMENT_VAR: string = '--bg-attachment';
	static BG_IMAGE: string = 'background-image';

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.Bg');
		this.styleNode = options.styleNode;
		this.parentVariableName = this.variableName;
		this.variableName = [
			this.parentVariableName,
			Bg.BG_URL_VAR,
			Bg.BG_URL_2X_VAR,
			Bg.BG_OVERLAY_VAR,
			Bg.BG_SIZE_VAR,
			Bg.BG_ATTACHMENT_VAR,
			Bg.BG_IMAGE,
		];
		this.parentClassName = this.className;
		this.className = 'g-bg-image';

		this.image = new Image(options);
		this.image.subscribe('onChange', this.onImageChange.bind(this));

		this.overlay = new ColorSet(options);
		this.overlay.subscribe('onChange', this.onOverlayColorChange.bind(this));
		this.overlayOpacity = new Opacity({defaultOpacity: 0.5});
		this.overlayOpacity.subscribe('onChange', this.onOverlayOpacityChange.bind(this));
		this.overlayPrimary = new Primary();
		this.overlayPrimary.subscribe('onChange', this.onOverlayPrimaryChange.bind(this));
		const overlayZeroingOptions = {
			textCode: 'LANDING_FIELD_COLOR_OVERLAY_ZEROING_TITLE_2',
			styleNode: options.styleNode,
		};
		this.overlayZeroing = new Zeroing(overlayZeroingOptions);
		this.overlayZeroing.subscribe('onChange', this.overlayZeroingChange.bind(this));

		this.imageTabs = new Tabs().appendTab(
			'Overlay',
			Loc.getMessage('LANDING_FIELD_COLOR-BG_OVERLAY'),
			[this.overlay, this.overlayPrimary, this.overlayZeroing, this.overlayOpacity],
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

		this.modifyStyleNode(this.styleNode);
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

		this.modifyStyleNode(this.styleNode);
	}

	onOverlayOpacityChange()
	{
		this.modifyStyleNode(this.styleNode);
	}

	onOverlayColorChange(event: BaseEvent)
	{
		this.overlayPrimary.unsetActive();
		this.overlayZeroing.unsetActive();
		this.onOverlayChange(event);
	}

	onOverlayPrimaryChange(event: BaseEvent)
	{
		this.overlay.unsetActive();
		this.overlayZeroing.unsetActive();
		this.onOverlayChange(event);
	}

	overlayZeroingChange(event: BaseEvent)
	{
		this.overlay.unsetActive();
		this.overlayPrimary.unsetActive();
		this.overlayZeroing.setActive();
		this.onOverlayChange(event);
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

			this.imageTabs.showTab('Overlay');
			if (Bg.BG_OVERLAY_VAR in value)
			{
				const overlayValue = new ColorValue(value[Bg.BG_OVERLAY_VAR]);
				this.overlay.setValue(overlayValue);
				this.overlayOpacity.setValue(overlayValue);
				if (value[Bg.BG_OVERLAY_VAR].startsWith('var(--primary') || value['isPrimaryBasedColor'] === true)
				{
					this.overlayPrimary.setActive();
					this.overlay.unsetActive();
				}
			}
			else
			{
				this.overlayZeroing.setActive();
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
				let overlayValue;
				let isActive = false;
				if (this.overlay.isActive())
				{
					overlayValue = this.overlay.getValue();
					isActive = true;
				}
				if (this.overlayPrimary.isActive())
				{
					overlayValue = this.overlayPrimary.getValue();
					isActive = true;
				}
				if (this.overlayZeroing.isActive())
				{
					overlayValue = null;
				}
				if (imageValue !== null && overlayValue !== null && isActive)
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
		let size = null;
		let attachment = null;
		const backgroundImage = '';
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
			[Bg.BG_URL_2X_VAR]: image2x ? image2x : image,
			[Bg.BG_OVERLAY_VAR]: overlay,
			[Bg.BG_SIZE_VAR]: size,
			[Bg.BG_ATTACHMENT_VAR]: attachment,
			[Bg.BG_IMAGE]: backgroundImage,
		};
	}

	modifyStyleNode(styleNode)
	{
		Dom.style(styleNode.getNode()[0], Bg.BG_IMAGE, '');
		this.onChange();
	}

	prepareProcessorValue(processorValue, defaultValue)
	{
		if (defaultValue && defaultValue.hasOwnProperty(Bg.BG_IMAGE))
		{
			const regUrl = /url\(/i;
			const searchUrl = defaultValue[Bg.BG_IMAGE].match(regUrl);
			if (searchUrl !== null)
			{
				processorValue[Bg.BG_IMAGE] = '';
				processorValue[Bg.BG_SIZE_VAR] = defaultBgImageSize;
				processorValue[Bg.BG_ATTACHMENT_VAR] = defaultBgImageAttachment;
				const regUrl = /image-set\(url\(/i;
				const searchUrl = defaultValue[Bg.BG_IMAGE].match(regUrl);
				if (searchUrl !== null)
				{
					const regSearchUrl = /"(https?:\/)?\/[\S]*"/gi;
					const search = defaultValue[Bg.BG_IMAGE].match(regSearchUrl);
					if (search)
					{
						processorValue[Bg.BG_URL_VAR] = search[0].replaceAll('"', '');
						if (search.length === 2)
						{
							processorValue[Bg.BG_URL_2X_VAR] = search[1].replaceAll('"', '');
						}
						else
						{
							processorValue[Bg.BG_URL_2X_VAR] = search[0].replaceAll('"', '');
						}
					}
				}
				else
				{
					processorValue[Bg.BG_URL_VAR] = defaultValue[Bg.BG_IMAGE];
					processorValue[Bg.BG_URL_2X_VAR] = defaultValue[Bg.BG_IMAGE];
				}
				const computedStyleNode = getComputedStyle(this.styleNode.getNode()[0], ':after');
				if (!processorValue[Bg.BG_OVERLAY_VAR])
				{
					processorValue[Bg.BG_OVERLAY_VAR] = computedStyleNode.backgroundColor;
				}
				const currentColorRgb = rgbaStringToRgbString(computedStyleNode.backgroundColor);
				const primaryColorRgb = rgbaStringToRgbString(computedStyleNode.getPropertyValue('--primary-opacity-0'));
				if (
					currentColorRgb !== null
					&& primaryColorRgb !== null
					&& currentColorRgb === primaryColorRgb
				)
				{
					processorValue['isPrimaryBasedColor'] = true;
				}
			}
		}
		return processorValue;
	}
}
