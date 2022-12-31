import { Dom, Tag, Type } from 'main.core';
import './style.css';

export class Loader
{
	constructor(options)
	{
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.type = Type.isString(options.type) ? options.type : null;
		this.size = Type.isString(options.size) ? options.size : null;
		this.color = options.color ? options.color : null;

		this.layout = {
			container: null,
			bulletContainer: null,
		};
	}

	#getContainer()
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="ui-loader__container ui-loader__scope">
					${this.type === 'BULLET' ? this.bulletLoader() : ''}
				</div>
			`;
		}

		return this.layout.container;
	}

	bulletLoader()
	{
		const color = this.color ? `background: ${this.color};` : '';

		if (!this.layout.bulletContainer)
		{
			this.layout.bulletContainer = Tag.render`
				<div class="ui-loader__bullet">
					<div style="${color}" class="ui-loader__bullet_item"></div>
					<div style="${color}" class="ui-loader__bullet_item"></div>
					<div style="${color}" class="ui-loader__bullet_item"></div>
					<div style="${color}" class="ui-loader__bullet_item"></div>
					<div style="${color}" class="ui-loader__bullet_item"></div>
				</div>
			`;
		}

		this.layout.container = document.querySelector('.ui-loader__bullet');

		return this.layout.bulletContainer;
	}

	show()
	{
		this.layout.container.style.display = 'block';
	}

	hide()
	{
		this.layout.container.style.display = '';
	}

	render()
	{
		if (!Type.isDomNode(this.target))
		{
			console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');

			return;
		}
		else
		{
			Dom.append(this.#getContainer(), this.target);

			if (this.type === 'BULLET')
			{
				if (this.size)
				{
					if (this.size.toUpperCase() === 'XS')
					{
						Dom.addClass(this.layout.container, 'ui-loader__bullet--xs');
					}

					if (this.size.toUpperCase() === 'S')
					{
						Dom.addClass(this.layout.container, 'ui-loader__bullet--sm');
					}

					if (this.size.toUpperCase() === 'M')
					{
						Dom.addClass(this.layout.container, 'ui-loader__bullet--md');
					}

					if (this.size.toUpperCase() === 'L')
					{
						Dom.addClass(this.layout.container, 'ui-loader__bullet--lg');
					}

					if (this.size.toUpperCase() === 'XL')
					{
						Dom.addClass(this.layout.container, 'ui-loader__bullet--xl');
					}
				}
			}
		}
	}
}
