import { Dom, Event, Tag, Type } from 'main.core';
import type { AvatarOptions } from './avatar-options';

export default class AvatarBase
{
	constructor(options: AvatarOptions)
	{
		this.options = { ...this.getDefaultOptions(), ...Type.isPlainObject(options) ? options : {} };

		this.node = {
			avatar: null,
			initials: null,
			svgUserPic: null,
			svgMask: null,
			svgDefaultUserPic: null,
		};

		this.events = null;
		this.title = null;
		this.userName = this.options.userName ?? this.title;

		this.initials = Type.isString(this.options.initials) ? this.options.initials : null;
		this.picPath = null;
		this.userpicPath = Type.isString(this.options.userpicPath) ?? this.picPath;
		this.unicId = null;
		this.events = {};
		this.size = null;
		this.baseColor = null;
		this.borderColor = null;
		this.borderInnerColor = null;

		this.setMask();
		this.setBaseColor(this.options.baseColor);
		this.setBorderColor(this.options.borderColor);
		this.setBorderInnerColor(this.options.borderInnerColor);
		this.setTitle(this.options.title ?? this.options.userName);

		if (this.initials)
		{
			this.setInitials(this.initials);
		}

		this.setSize(this.options.size);
		this.setPic(this.options.picPath ?? this.options.userpicPath);
		this.setEvents(this.options.events);

		if (!this.title && !this.initials && !this.picPath)
		{
			this.setDefaultUserPic();
		}
	}

	setEvents(events: {[key: string]: Function}): this
	{
		if (Type.isObject(events))
		{
			this.events = events;
			const eventKeys = Object.keys(this.events);

			for (const event of eventKeys)
			{
				Event.bind(this.getContainer(), event, () => {
					this.events[event]();
				});

				Dom.addClass(this.getContainer(), '--cursor-pointer');
			}
		}

		return this;
	}

	hexToRgb(hex: string): string
	{
		if (!/^#([\dA-Fa-f]{3}){1,2}$/.test(hex))
		{
			return hex;
		}

		const color = hex.length === 4
			? [hex[1], hex[1], hex[2], hex[2], hex[3], hex[3]]
			// eslint-disable-next-line unicorn/no-useless-spread
			: [...hex.slice(1)];
		const rgb = parseInt(color.join(''), 16);

		return `${(rgb >> 16) & 255}, ${(rgb >> 8) & 255}, ${rgb & 255}`;
	}

	setBorderColor(colorCode: string): this
	{
		if (Type.isString(colorCode))
		{
			this.borderColor = colorCode;
			Dom.style(this.getContainer(), '--ui-avatar-border-color', this.borderColor);
		}

		return this;
	}

	setBorderInnerColor(colorCode: string): this
	{
		if (Type.isString(colorCode))
		{
			this.borderInnerColor = colorCode;
			Dom.style(this.getContainer(), '--ui-avatar-border-inner-color', this.borderInnerColor);
		}
	}

	setBaseColor(colorCode: string): this
	{
		if (Type.isString(colorCode))
		{
			this.baseColor = this.hexToRgb(colorCode);
			Dom.style(this.getContainer(), '--ui-avatar-base-color-rgb', this.baseColor);
		}

		return this;
	}

	getUnicId(): string
	{
		if (!this.unicId)
		{
			this.unicId = `ui-avatar-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`;
		}

		return this.unicId;
	}

	getDefaultOptions(): Object
	{
		return {};
	}

	setTitle(text: string): this
	{
		if (Type.isString(text) && text.trim().length > 0)
		{
			this.title = text;

			if (this.title.length > 0)
			{
				this.getContainer().setAttribute('title', this.title);

				const validSymbolsPattern = /[\p{L}\p{N} ]/u;
				const words = this.title.split(/[\s,]/).filter((word) => {
					const firstLetter = word.charAt(0);

					return validSymbolsPattern.test(firstLetter);
				});

				let initials = '';

				if (words.length > 0)
				{
					initials = words.length > 1
						? words[0].charAt(0) + words[1].charAt(0)
						: initials = words[0].charAt(0);
				}

				this.setInitials(initials.toUpperCase());
			}
		}

		return this;
	}

	getInitialsNode(): HTMLElement
	{
		if (!this.node.initials)
		{
			this.node.initials = Tag.render`
				<div class="ui-avatar__text" style="font-size: calc(var(--ui-avatar-size) / 2.6)"></div>
			`;
		}

		return this.node.initials;
	}

	setInitials(text: string): this
	{
		if (this.picPath)
		{
			return this;
		}

		if (Type.isString(text))
		{
			this.getInitialsNode().textContent = text;

			if (!this.getInitialsNode().parentNode)
			{
				this.node.initials = Tag.render`
					<div class="ui-avatar__text"></div>
				`;

				Dom.append(this.getInitialsNode(), this.getContainer());
				Dom.style(this.getInitialsNode(), 'font-size', 'calc(var(--ui-avatar-size) / 2.6)');
			}

			this.getInitialsNode().textContent = text;
		}

		return this;
	}

	getSvgElement(tag: string, attr: Object): SVGElement
	{
		if (Type.isString(tag) || Type.isObject(attr))
		{
			const svg = document.createElementNS('http://www.w3.org/2000/svg', tag);

			Object.keys(attr).forEach((attrSingle) => {
				if (Object.prototype.hasOwnProperty.call(attr, attrSingle))
				{
					svg.setAttributeNS(null, attrSingle, attr[attrSingle]);
				}
			});

			return svg;
		}

		return null;
	}

	getMaskNode(): SVGElement
	{
		if (!this.node.svgMask)
		{
			this.node.svgMask = this.getSvgElement('circle', { cx: 51, cy: 51, r: 51, fill: 'white' });
		}

		return this.node.svgMask;
	}

	setMask()
	{
		const mask = this.getSvgElement('mask', { id: `${this.getUnicId()}-${this.constructor.name}` });
		Dom.append(this.getMaskNode(), mask);
		Dom.prepend(mask, this.getContainer().querySelector('svg'));
	}

	getDefaultUserPic(): SVGElement
	{
		if (!this.node.svgDefaultUserPic)
		{
			this.node.svgDefaultUserPic = this.getSvgElement(
				'svg',
				{ width: 56, height: 64, viewBox: '0 0 28 32', x: 23, y: 20 },
			);
			this.node.svgDefaultUserPic.innerHTML = `
				<path fill="#fff" d="M25.197 29.5091C26.5623 29.0513 27.3107 27.5994 27.0337 26.1625L26.6445 24.143C26.4489 22.8806 25.0093 21.4633 21.7893 20.6307C20.6983 20.3264 19.6613 19.8546 18.7152 19.232C18.5082 19.1138 18.5397 18.0214 18.5397 18.0214L17.5026 17.8636C17.5026 17.7749 17.4139 16.4649 17.4139 16.4649C18.6548 16.048 18.5271 13.5884 18.5271 13.5884C19.3151 14.0255 19.8283 12.0791 19.8283 12.0791C20.7604 9.37488 19.3642 9.53839 19.3642 9.53839C19.6085 7.88753 19.6085 6.20972 19.3642 4.55887C18.7435 -0.917471 9.39785 0.569216 10.506 2.35777C7.77463 1.85466 8.39788 8.06931 8.39788 8.06931L8.99031 9.67863C8.16916 10.2112 8.33041 10.8225 8.51054 11.5053C8.58564 11.7899 8.66401 12.087 8.67586 12.396C8.73309 13.9469 9.68211 13.6255 9.68211 13.6255C9.7406 16.1851 11.0028 16.5184 11.0028 16.5184C11.2399 18.1258 11.0921 17.8523 11.0921 17.8523L9.9689 17.9881C9.9841 18.3536 9.95432 18.7197 9.88022 19.078C9.2276 19.3688 8.82806 19.6003 8.43247 19.8294C8.0275 20.064 7.62666 20.2962 6.9627 20.5873C4.42693 21.6985 1.8838 22.3205 1.39387 24.2663C1.28119 24.7138 1.1185 25.4832 0.962095 26.2968C0.697567 27.673 1.44264 29.0328 2.74873 29.4755C5.93305 30.5548 9.46983 31.1912 13.2024 31.2728H14.843C18.5367 31.192 22.0386 30.5681 25.197 29.5091Z"/>
			`;
		}

		return this.node.svgDefaultUserPic;
	}

	getUserPicNode(): SVGElement
	{
		if (!this.node.svgUserPic)
		{
			this.node.svgUserPic = this.getSvgElement(
				'image',
				{
					height: 102,
					width: 102,
					mask: `url(#${this.getUnicId()}-${this.constructor.name})`,
					preserveAspectRatio: 'xMidYMid slice',
				},
			);
		}

		return this.node.svgUserPic;
	}

	setDefaultUserPic(): this
	{
		if (!this.getDefaultUserPic().parentNode)
		{
			Dom.append(this.getDefaultUserPic(), this.getContainer().querySelector('svg'));
		}

		Dom.addClass(this.getContainer(), '--default-user-pic');
		Dom.remove(this.getInitialsNode());
		this.node.initials = null;

		return this;
	}

	removeDefaultUserPic(): this
	{
		Dom.remove(this.getDefaultUserPic());
		Dom.removeClass(this.getContainer(), '--default-user-pic');
		this.node.svgDefaultUserPic = null;

		return this;
	}

	setPic(url: string): this
	{
		this.setUserPic(url);
	}

	removePic(): this
	{
		this.removeUserPic();
	}

	setUserPic(url: string): this
	{
		if (Type.isString(url) && url !== '')
		{
			this.picPath = url;

			if (!this.getUserPicNode().parentNode)
			{
				Dom.append(this.getUserPicNode(), this.getContainer().querySelector('svg'));
			}

			this.getUserPicNode().setAttributeNS('http://www.w3.org/1999/xlink', 'href', url);

			Dom.removeClass(this.getContainer(), '--default-user-pic');
			Dom.remove(this.getInitialsNode());
			this.node.initials = null;
		}

		return this;
	}

	removeUserPic()
	{
		Dom.remove(this.getUserPicNode());
		this.picPath = null;
		this.setInitials(this.title);
		Dom.style(this.getContainer(), '--ui-avatar-base-color-rgb', this.baseColor);
	}

	setSize(size: number): this
	{
		if (Type.isNumber(size) && size > 0)
		{
			this.size = size;
			Dom.style(this.getContainer(), '--ui-avatar-size', `${this.size}px`);
		}

		return this;
	}

	getContainer(): HTMLElement
	{
		if (!this.node.avatar)
		{
			this.node.avatar = Tag.render`
				<div class="ui-avatar --base">
					<svg viewBox="0 0 102 102">
						<circle class="ui-avatar-base" cx="51" cy="51" r="51" />
					</svg>
				</div>
			`;
		}

		return this.node.avatar;
	}

	renderTo(node: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(node))
		{
			Dom.append(this.getContainer(), node);
		}

		return null;
	}
}
