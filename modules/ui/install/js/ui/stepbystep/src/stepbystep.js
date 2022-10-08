import StepByStepItem from './stepbystep.item';
import { Tag, Dom } from 'main.core';
import 'ui.fonts.opensans';


export class StepByStep
{
	constructor(options = {})
	{
		this.target = options.target || null;
		this.content = options.content || null;
		this.contentWrapper = null;
		this.items = [];
		this.counter = 0;
	}

	getItem(item): StepByStepItem
	{
		if (item instanceof StepByStepItem)
		{
			return item;
		}

		this.counter++;

		if (this.counter === 1)
		{
			item.isFirst = '--first';
		}

		if (this.counter === this.content.length)
		{
			item.isLast = '--last';
		}

		item = new StepByStepItem(item, this.counter);

		if (this.items.indexOf(item) === -1)
		{
			this.items.push(item);
		}

		return item;
	}

	getContentWrapper(): HTMLElement
	{
		if (!this.contentWrapper)
		{
			this.contentWrapper = Tag.render`
				<div class="ui-stepbystep__content ui-stepbystep__scope"></div>
			`;

			this.content.map((item)=> {

				item.html.map((itemObj)=> {
					this.contentWrapper.appendChild(this.getItem(itemObj).getContainer());
				});

			});
		}

		return this.contentWrapper;
	}

	init()
	{
		if (this.target && this.content)
		{
			Dom.clean(this.target);
			this.target.appendChild(this.getContentWrapper());
		}
	}
}