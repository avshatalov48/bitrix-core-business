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
		this.marginBottom = Type.isNumber(options?.marginBottom) ? options.marginBottom : null;
		this.disabled = Type.isBoolean(options?.disabled) ? options.disabled : null;
		this.overflow = Type.isBoolean(options?.overflow) ? options.overflow : null;
		this.displayBlock = Type.isBoolean(options?.displayBlock) ? options.displayBlock : null;
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
				size: 45
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

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-qr-popupcomponentmaker__content--section-item">${this.getContent()}</div>
			`;

			if (this.backgroundColor)
			{
				this.layout.container.style.backgroundColor = this.backgroundColor;
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

			if (this.overflow)
			{
				this.layout.container.classList.add('--overflow-hidden');
			}

			if (this.displayBlock)
			{
				this.layout.container.classList.add('--block');
			}
		}

		return this.layout.container;
	}
}
