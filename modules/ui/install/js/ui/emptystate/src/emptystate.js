import { Type, Dom, Tag } from 'main.core';
import './style.css';

export default class EmptyState
{
	constructor({ target, size, type })
	{
		this.target = Type.isDomNode(target) ? target : null;
		this.size = Type.isNumber(size) ? size : null;
		this.type = Type.isString(type) ? type : null;
		this.container = null;
	}

	getContainer()
	{
		if (!this.container)
		{
			this.container = Tag.render`
				<div class="ui-emptystate ${this.type ? '--' + this.type.toLowerCase() : ''}">
					<i></i>
				</div>
			`;

			if (this.size)
			{
				this.container.style.setProperty('height', this.size + 'px');
				this.container.style.setProperty('width', this.size + 'px');
			}
		}

		return this.container;
	}

	hide()
	{
		Dom.clean(this.target);
	}

	show()
	{
		if (this.target)
		{
			Dom.clean(this.target);
			Dom.append(this.getContainer(), this.target);
		}
	}
}