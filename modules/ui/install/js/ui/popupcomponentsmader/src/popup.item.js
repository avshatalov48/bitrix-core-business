import { EventEmitter } from 'main.core.events';
import { Type, Tag, Dom } from 'main.core';
import { Loader } from 'main.loader';

export default class PopupComponentsMaderItem extends EventEmitter
{
	constructor(options = {})
	{
		super();

		this.html = Type.isDomNode(options?.html) ? options.html : null;
		this.awaitContent = Type.isBoolean(options?.awaitContent) ? options?.awaitContent : null;
		this.flex = Type.isNumber(options?.flex) ? options.flex : null;
		this.withoutBackground = Type.isBoolean(options?.withoutBackground) ? options.withoutBackground : null;
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

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-qr-popupcomponentmader__content--section-item">${this.getContent()}</div>
			`;

			if (this.withoutBackground)
			{
				this.layout.container.classList.add('--transparent');
			}

			if (this.flex)
			{
				this.layout.container.style.flex = this.flex;
			}
		}

		return this.layout.container;
	}
}
