import {Type, Loc} from 'main.core';

import {
	BgImageValueOptions,
	defaultBgImageValueOptions,
	defaultBgImageSize,
	defaultBgImageAttachment,
} from "./types/color_value_options";
import {IColorValue} from './types/i_color_value';

import isBgImageString, {matcherBgImage} from './internal/is-bg-image-string';
import {matcherGradient, matcherGradientColors} from './internal/is-gradient-string';
import {regexpWoStartEnd} from './internal/regexp';

import ColorValue from './color_value';

export default class BgImageValue implements IColorValue
{
	value: BgImageValueOptions;

	constructor(value: BgImageValueOptions | BgImageValue | string)
	{
		// todo: add 2x, file ids
		this.value = defaultBgImageValueOptions;
		this.setValue(value);
	}

	getName(): string
	{
		return `
			${this.value.url.replace(/[^\w\d]/g, '')}_${this.value.size}_${this.value.attachment}
		`;
	}

	setValue(value: BgImageValueOptions | BgImageValue | string): BgImageValue
	{
		if (Type.isObject(value))
		{
			if (value instanceof BgImageValue)
			{
				// todo: add 2x and file IDs
				this.value.url = value.getUrl();
				this.value.url2x = value.getUrl2x();
				this.value.fileId = value.getFileId();
				this.value.fileId2x = value.getFileId2x();
				this.value.size = value.getSize();
				this.value.attachment = value.getAttachment();
			}
			else
			{
				this.value = {...this.value, ...value};
			}
		}

		if (Type.isString(value) && isBgImageString(value))
		{
			this.parseBgString(value);
		}

		return this;
	}

	parseBgString(string: string): void
	{
		// todo: check matcher for 2x
		const options = defaultBgImageValueOptions;

		const matchesBg = string.trim().match(regexpWoStartEnd(matcherBgImage));
		if (!!matchesBg)
		{
			options.url = matchesBg[1];

			options.size = matchesBg[2].indexOf('auto') === -1
				? defaultBgImageSize
				: 'auto'
			;

			options.attachment = matchesBg[2].indexOf('fixed') === -1
				? defaultBgImageAttachment
				: 'fixed'
			;
		}

		const matchesOverlay = string.trim().match(regexpWoStartEnd(matcherGradientColors));
		if(!!string.trim().match(regexpWoStartEnd(matcherGradient)) && !!matchesOverlay)
		{
			options.overlay = new ColorValue(matchesOverlay[0]);
		}

		this.setValue(options);
	}

	setOpacity(opacity: number): BgImageValue
	{
		// todo: what for image?

		return this;
	}

	setUrl(value: string): BgImageValue
	{
		this.setValue({url: value});
		return this;
	}

	setUrl2x(value: string): BgImageValue
	{
		this.setValue({url2x: value});
		return this;
	}

	setFileId(value: number): BgImageValue
	{
		this.setValue({fileId: value});
		return this;
	}

	setFileId2x(value: number): BgImageValue
	{
		this.setValue({fileId2x: value});
		return this;
	}

	setSize(value: 'cover' | 'auto'): BgImageValue
	{
		this.setValue({size: value});
		return this;
	}

	setAttachment(value: 'scroll' | 'fixed'): BgImageValue
	{
		this.setValue({attachment: value});
		return this;
	}

	setOverlay(value: ColorValue)
	{
		this.setValue({overlay: value});
		return this;
	}

	getUrl(): string
	{
		return this.value.url;
	}

	getUrl2x(): ?string
	{
		return this.value.url2x;
	}

	getFileId(): number
	{
		return this.value.fileId;
	}

	getFileId2x(): ?number
	{
		return this.value.fileId2x;
	}

	getSize(): 'cover' | 'auto'
	{
		return this.value.size;
	}

	getAttachment(needBool: boolean = false): string | boolean
	{
		return needBool
			? this.value.attachment === 'fixed'
			: this.value.attachment;
	}

	getOverlay(): ColorValue
	{
		return this.value.overlay;
	}

	getOpacity(): number
	{
		// todo: how image can have opacity?
		return 1;
	}

	getStyleString(): string
	{
		let style = '';
		if (this.value.overlay !== null)
		{
			style = `linear-gradient(${this.value.overlay.getStyleString()},${this.value.overlay.getStyleString()})`;
		}

		// todo: what if url is null
		const {url, url2x, size, attachment} = this.value;
		const endString = `center / ${size} ${attachment}`;
		if (url !== null)
		{
			style = style.length ? (style + ',') : '';
			if(url2x !== null)
			{
				style += `-webkit-image-set(url('${url}') 1x, url('${url2x}') 2x) ${endString},`;
				style += `image-set(url('${url}') 1x, url('${url2x}') 2x) ${endString},`;
			}
			style += `url('${url}') ${endString}`;
		}

		return style;
	}

	getStyleStringForOpacity(): string
	{
		// todo: how image can have opacity?
		return '';
	}

	static getSizeItemsForButtons(): []
	{
		return [
			{name: Loc.getMessage('LANDING_FIELD_COLOR-BG_COVER'), value: 'cover'},
			{name: Loc.getMessage('LANDING_FIELD_COLOR-BG_MOSAIC'), value: 'auto'},
		];
	}

	static getAttachmentValueByBool(value: boolean): string
	{
		return value ? 'fixed' : 'scroll';
	}
}
