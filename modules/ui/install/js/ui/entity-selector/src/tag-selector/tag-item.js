import { Cache, Tag, Type, Dom, Event } from 'main.core';
import Entity from '../entity/entity';
import Animation from '../util/animation';
import type TagSelector from './tag-selector';
import type { TagItemOptions } from './tag-item-options';

export default class TagItem
{
	id: string | number = null;
	entityId: string = null;
	entityType: string = null;
	title: string = '';

	avatar: ?string = null;
	maxWidth: ?number = null;
	textColor: ?string = null;
	bgColor: ?string = null;
	fontWeight: ?string = null;

	link: ?string = null;
	onclick: ?Function = null;

	deselectable: ?boolean = null;
	customData: Map<string, any> = null;

	cache = new Cache.MemoryCache();
	selector: TagSelector = null;
	rendered: ?boolean = false;

	constructor(itemOptions: TagItemOptions)
	{
		const options = Type.isPlainObject(itemOptions) ? itemOptions : {};
		if (!Type.isStringFilled(options.id) && !Type.isNumber(options.id))
		{
			throw new Error('TagSelector.TagItem: "id" parameter is required.');
		}

		if (!Type.isStringFilled(options.entityId))
		{
			throw new Error('TagSelector.TagItem: "entityId" parameter is required.');
		}

		this.id = options.id;
		this.entityId = options.entityId;
		this.entityType = Type.isStringFilled(options.entityType) ? options.entityType : 'default';
		this.customData =
			Type.isPlainObject(options.customData) ? new Map(Object.entries(options.customData)) : new Map()
		;

		this.onclick = Type.isFunction(options.onclick) ? options.onclick : null;
		this.link = Type.isStringFilled(options.link) ? options.link : null;

		this.setTitle(options.title);
		this.setDeselectable(options.deselectable);

		this.setAvatar(options.avatar);
		this.setMaxWidth(options.maxWidth);
		this.setTextColor(options.textColor);
		this.setBgColor(options.bgColor);
		this.setFontWeight(options.fontWeight);
	}

	getId(): string | number
	{
		return this.id;
	}

	getEntityId(): string
	{
		return this.entityId;
	}

	getEntityType(): string
	{
		return this.entityType;
	}

	getSelector(): TagSelector
	{
		return this.selector;
	}

	setSelector(selector: TagSelector)
	{
		this.selector = selector;
	}

	getTitle(): string
	{
		return this.title;
	}

	setTitle(title: string): this
	{
		if (Type.isStringFilled(title))
		{
			this.title = title;
		}

		return this;
	}

	getAvatar(): ?string
	{
		if (this.avatar !== null)
		{
			return this.avatar;
		}
		else if (this.getSelector().getTagAvatar() !== null)
		{
			return this.getSelector().getTagAvatar();
		}
		else if (this.getEntityTagOption('avatar') !== null)
		{
			return this.getEntityTagOption('avatar');
		}

		return this.getEntityItemOption('avatar');
	}

	setAvatar(avatar: ?string): this
	{
		if (Type.isString(avatar) || avatar === null)
		{
			this.avatar = avatar;
		}

		return this;
	}

	getTextColor(): ?string
	{
		if (this.textColor !== null)
		{
			return this.textColor;
		}
		else if (this.getSelector().getTagTextColor() !== null)
		{
			return this.getSelector().getTagTextColor();
		}

		return this.getEntityTagOption('textColor');
	}

	setTextColor(textColor: ?string): this
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}

		return this;
	}

	getBgColor(): ?string
	{
		if (this.bgColor !== null)
		{
			return this.bgColor;
		}
		else if (this.getSelector().getTagBgColor() !== null)
		{
			return this.getSelector().getTagBgColor();
		}

		return this.getEntityTagOption('bgColor');
	}

	setBgColor(bgColor: ?string): this
	{
		if (Type.isString(bgColor) || bgColor === null)
		{
			this.bgColor = bgColor;
		}

		return this;
	}

	getFontWeight(): ?string
	{
		if (this.fontWeight !== null)
		{
			return this.fontWeight;
		}
		else if (this.getSelector().getTagFontWeight() !== null)
		{
			return this.getSelector().getTagFontWeight();
		}

		return this.getEntityTagOption('fontWeight');
	}

	setFontWeight(fontWeight: ?string): this
	{
		if (Type.isString(fontWeight) || fontWeight === null)
		{
			this.fontWeight = fontWeight;
		}

		return this;
	}

	getMaxWidth(): ?number
	{
		if (this.maxWidth !== null)
		{
			return this.maxWidth;
		}
		else if (this.getSelector().getTagMaxWidth() !== null)
		{
			return this.getSelector().getTagMaxWidth();
		}

		return this.getEntityTagOption('maxWidth');
	}

	setMaxWidth(width: ?number)
	{
		if ((Type.isNumber(width) && width >= 0) || width === null)
		{
			this.maxWidth = width;
		}
	}

	setDeselectable(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.deselectable = flag;
		}

		return this;
	}

	isDeselectable(): boolean
	{
		return this.deselectable !== null ? this.deselectable : this.getSelector().isDeselectable();
	}

	getLink(): ?string
	{
		return this.link;
	}

	getOnclick(): ?Function
	{
		return this.onclick;
	}

	render()
	{
		this.getTitleContainer().textContent = this.getTitle();
		Dom.attr(this.getContentContainer(), 'title', this.getTitle());

		const avatar = this.getAvatar();
		if (Type.isStringFilled(avatar))
		{
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
			Dom.style(this.getAvatarContainer(), 'background-image', `url('${avatar}')`);
		}
		else
		{
			Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
			Dom.style(this.getAvatarContainer(), 'background-image', null);
		}

		const maxWidth = this.getMaxWidth();
		if (maxWidth > 0)
		{
			Dom.style(this.getContainer(), 'max-width', `${maxWidth}px`);
		}
		else
		{
			Dom.style(this.getContainer(), 'max-width', null);
		}

		if (this.isDeselectable())
		{
			Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
		}
		else
		{
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
		}

		Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
		Dom.style(this.getTitleContainer(), 'font-weight', this.getFontWeight());
		Dom.style(this.getContainer(), 'background-color', this.getBgColor());

		this.rendered = true;
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {
			return Tag.render`
				<div class="ui-tag-selector-item ui-tag-selector-tag">
					${this.getContentContainer()}
					${this.getRemoveIcon()}
				</div>`
			;
		});
	}

	getContentContainer()
	{
		return this.cache.remember('content-container', () => {
			if (Type.isStringFilled(this.getLink()))
			{
				return Tag.render`
					<a
						class="ui-tag-selector-tag-content"
						onclick="${this.handleContainerClick.bind(this)}"
						href="${this.getLink()}"
						target="_blank"
					>
						${this.getAvatarContainer()}
						${this.getTitleContainer()}
					</a>
				`;
			}
			else
			{
				const className = Type.isFunction(this.getOnclick()) ? ' ui-tag-selector-tag-content--clickable' : '';
				return Tag.render`
					<div 
						class="ui-tag-selector-tag-content${className}" 
						onclick="${this.handleContainerClick.bind(this)}"
					>
						${this.getAvatarContainer()}
						${this.getTitleContainer()}
					</div>
					
				`;
			}
		});
	}

	getAvatarContainer()
	{
		return this.cache.remember('avatar', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-avatar"></div>
			`;
		});
	}

	getTitleContainer(): HTMLElement
	{
		return this.cache.remember('title', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-title"></div>
			`;
		});
	}

	getRemoveIcon(): HTMLElement
	{
		return this.cache.remember('remove-icon', () => {
			return Tag.render`
				<div class="ui-tag-selector-tag-remove" onclick="${this.handleRemoveIconClick.bind(this)}"></div>
			`;
		});
	}

	getEntityTagOption(option: string): any
	{
		return Entity.getTagOption(this.getEntityId(), this.getEntityType(), option);
	}

	getEntityItemOption(option: string): any
	{
		return Entity.getItemOption(this.getEntityId(), this.getEntityType(), option);
	}

	isRendered()
	{
		return this.rendered && this.getSelector() && this.getSelector().isRendered();
	}

	remove(animate: boolean = true): Promise
	{
		if (animate === false)
		{
			Dom.remove(this.getContainer());
			return Promise.resolve();
		}

		return new Promise(resolve => {
			Dom.style(this.getContainer(), 'width', `${this.getContainer().offsetWidth}px`);
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--remove');
			Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-remove').then(() => {
				Dom.remove(this.getContainer());
				resolve();
			});
		});
	}

	show(): Promise
	{
		return new Promise(resolve => {
			Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--show');
			Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-show').then(() => {
				Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--show');
				resolve();
			});
		});
	}

	handleContainerClick()
	{
		const fn = this.getOnclick();
		if (Type.isFunction(fn))
		{
			fn(this);
		}
	}

	handleRemoveIconClick(event: MouseEvent)
	{
		event.stopPropagation();
		if (this.isDeselectable())
		{
			this.getSelector().removeTag(this);
		}
	}
}