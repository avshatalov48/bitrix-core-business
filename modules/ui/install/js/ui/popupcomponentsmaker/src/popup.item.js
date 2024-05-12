import { EventEmitter } from 'main.core.events';
import { Type, Tag, Dom } from 'main.core';
import { Loader } from 'main.loader';

export default class PopupComponentsMakerItem extends EventEmitter
{
	constructor(options = {})
	{
		super();

		this.html = null;
		this.awaitContent = null;
		this.flex = null;
		this.withoutBackground = null;
		this.backgroundColor = null;
		this.backgroundImage = null;
		this.background = null;
		this.marginBottom = null;
		this.disabled = null;
		this.secondary = null;
		this.overflow = null;
		this.displayBlock = null;
		this.attrs = null;
		this.minHeight = null;
		this.sizeLoader = 45;
		this.asyncSecondary = null;
		this.margin = null;

		this.setParams(options);

		this.layout = {
			container: null,
		};

		if (this.awaitContent)
		{
			this.await();
		}
	}

	setParams(options = {})
	{
		this.html = Type.isDomNode(options?.html) ? options.html : this.html;
		this.awaitContent = Type.isBoolean(options?.awaitContent) ? options?.awaitContent : this.awaitContent;
		this.flex = Type.isNumber(options?.flex) ? options.flex : this.flex;
		this.withoutBackground = Type.isBoolean(options?.withoutBackground)
			? options.withoutBackground
			: this.withoutBackground
		;
		this.background = Type.isString(options?.background)
			? options.background
			: this.background
		;
		this.backgroundColor = Type.isString(options?.backgroundColor)
			? options.backgroundColor
			: this.backgroundColor
		;
		this.backgroundImage = Type.isString(options?.backgroundImage)
			? options.backgroundImage
			: this.backgroundImage
		;
		this.marginBottom = Type.isNumber(options?.marginBottom) ? options.marginBottom : this.marginBottom;
		this.disabled = Type.isBoolean(options?.disabled) ? options.disabled : this.disabled;
		this.secondary = Type.isBoolean(options?.secondary) ? options.secondary : this.secondary;
		this.overflow = Type.isBoolean(options?.overflow) ? options.overflow : this.overflow;
		this.displayBlock = Type.isBoolean(options?.displayBlock) ? options.displayBlock : this.displayBlock;
		this.attrs = Type.isPlainObject(options?.attrs) ? options.attrs : this.attrs;
		this.minHeight = Type.isString(options?.minHeight) ? options.minHeight : this.minHeight;
		this.margin = Type.isString(options.margin) ? options.margin : this.margin;
		this.sizeLoader = Type.isNumber(options?.sizeLoader) ? options.sizeLoader : this.sizeLoader;
		this.asyncSecondary = (options?.asyncSecondary instanceof Promise)
			? options.asyncSecondary
			: this.asyncSecondary
		;
	}

	getLoader(): Loader
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				target: this.getContainer(),
				size: this.sizeLoader
			});
		}

		return this.loader;
	}

	await()
	{
		this.getContainer().classList.add('--awaiting');
		this.showLoader();
	}

	stopAwait()
	{
		this.getContainer().classList.remove('--awaiting');
		this.hideLoader();
	}

	showLoader(): void
	{
		void this.getLoader().show();
	}

	hideLoader(): void
	{
		void this.getLoader().hide();
	}

	getContent()
	{
		if (this.html)
		{
			return this.html;
		}

		return '';
	}

	updateContent(node: HTMLElement)
	{
		if (Type.isDomNode(node))
		{
			Dom.clean(this.getContainer());
			this.getContainer().appendChild(node);
		}
	}

	setBackgroundColor(color: string)
	{
		if (Type.isString(color))
		{
			this.getContainer().style.backgroundColor = color;
		}
	}

	getMarginBottom()
	{
		return this.marginBottom;
	}

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-popupcomponentmaker__content--section-item">${this.getContent()}</div>
			`;
		}

		if (this.background)
		{
			this.layout.container.style.background = this.background;
		}

		if (this.backgroundColor)
		{
			this.layout.container.style.backgroundColor = this.backgroundColor;
		}

		if (this.backgroundImage)
		{
			this.layout.container.style.backgroundImage = this.backgroundImage;
		}

		if (this.withoutBackground && !this.backgroundColor && !this.background)
		{
			this.layout.container.classList.add('--transparent');
		}

		if (this.flex)
		{
			this.layout.container.style.flex = this.flex;
		}

		if (this.disabled)
		{
			this.layout.container.classList.add('--disabled');
		}

		if (this.secondary)
		{
			Dom.addClass(this.layout.container, '--secondary');
		}

		if (this.overflow)
		{
			this.layout.container.classList.add('--overflow-hidden');
		}

		if (this.displayBlock)
		{
			this.layout.container.classList.add('--block');
		}

		if (this.attrs)
		{
			Dom.adjust(this.layout.container, {attrs: this.attrs});
		}

		if (this.minHeight)
		{
			Dom.style(this.layout.container, 'min-height', this.minHeight);
		}

		if (this.margin)
		{
			Dom.style(this.layout.container, 'margin', this.margin);
		}

		if (this.asyncSecondary)
		{
			this.asyncSecondary.then((secondary) => {
				if (secondary === false)
				{
					Dom.removeClass(this.layout.container, '--secondary');
				}
				else
				{
					Dom.addClass(this.layout.container, '--secondary');
				}
			});
		}

		return this.layout.container;
	}
}
