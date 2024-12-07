import { Event, Type, Tag, Dom } from 'main.core';
import type { AvatarOptions } from './avatar-options';

export default class AvatarBase
{
	constructor(options: AvatarOptions)
	{
		this.options = { ...this.getDefaultOptions(), ...Type.isPlainObject(options) ? options : {} };

		this.node = {
			avatar: null,
			initials: null,
			svgUserpic: null,
			svgMask: null,
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
			this.baseColor = colorCode;
			Dom.style(this.getContainer(), '--ui-avatar-base-color', this.baseColor);
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
		if (Type.isString(text))
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

				this.setInitials(initials);
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

	getUserPicNode(): HTMLElement
	{
		if (!this.node.svgUserpic)
		{
			this.node.svgUserpic = this.getSvgElement('image', { height: 102, width: 102, mask: `url(#${this.getUnicId()}-${this.constructor.name})`, preserveAspectRatio: 'xMidYMid slice' });
		}

		return this.node.svgUserpic;
	}

	setPic(url: string): this
	{
		this.setUserPic(url);
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

			Dom.style(this.getContainer(), '--ui-avatar-base-color', 'var(--ui-avatar-border-inner-color)');
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
		Dom.style(this.getContainer(), '--ui-avatar-base-color', this.baseColor);
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
				<div class="ui-avatar">
					<svg viewBox="0 0 102 102">
						<circle fill="var(--ui-avatar-base-color)" cx="51" cy="51" r="51" />
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
