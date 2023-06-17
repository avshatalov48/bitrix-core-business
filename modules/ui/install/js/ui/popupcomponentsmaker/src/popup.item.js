import { EventEmitter } from 'main.core.events';
import { Type, Tag, Dom } from 'main.core';
import { Loader } from 'main.loader';

export default class PopupComponentsMakerItem extends EventEmitter
{
	constructor(options = {})
	{
		super();

		this.html = Type.isDomNode(options?.html) ? options.html : null;
		this.awaitContent = Type.isBoolean(options?.awaitContent) ? options?.awaitContent : null;
		this.flex = Type.isNumber(options?.flex) ? options.flex : null;
		this.withoutBackground = Type.isBoolean(options?.withoutBackground) ? options.withoutBackground : null;
		this.backgroundColor = Type.isString(options?.backgroundColor) ? options.backgroundColor : null;
		this.backgroundImage = Type.isString(options?.backgroundImage) ? options.backgroundImage : null;
		this.marginBottom = Type.isNumber(options?.marginBottom) ? options.marginBottom : null;
		this.disabled = Type.isBoolean(options?.disabled) ? options.disabled : null;
		this.secondary = Type.isBoolean(options?.secondary) ? options.secondary : null;
		this.overflow = Type.isBoolean(options?.overflow) ? options.overflow : null;
		this.displayBlock = Type.isBoolean(options?.displayBlock) ? options.displayBlock : null;
		this.attrs = Type.isPlainObject(options?.attrs) ? options.attrs : null;
		this.minHeight = Type.isString(options?.minHeight) ? options.minHeight : null;
		this.sizeLoader = Type.isNumber(options?.sizeLoader) ? options.sizeLoader : 45;
		this.asyncSecondary = (options?.asyncSecondary instanceof Promise) ? options.asyncSecondary : null;

		this.layout = {
			container: null
		};

		if (this.awaitContent)
		{
			this.await();
		}
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

			if (this.backgroundColor)
			{
				this.layout.container.style.backgroundColor = this.backgroundColor;
			}
			if (this.backgroundImage)
			{
				this.layout.container.style.backgroundImage = this.backgroundImage;
			}

			if (this.withoutBackground && !this.backgroundColor)
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
		}

		return this.layout.container;
	}
}
