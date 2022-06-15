import {Event, Dom} from "main.core";
import 'ui.forms';

export class LayoutForm
{
	constructor()
	{
		this.hiddenAttribute = 'data-form-row-hidden';
		this.nodes = null;

		this.init();
	}

	init()
	{
		this.nodes = document.querySelectorAll('[' + this.hiddenAttribute + ']');

		for (let i = 0; i < this.nodes.length; i++) {
			Event.bind(this.nodes[i], "click", this.onClick.bind(this));
			this.nodes[i].querySelector('.ui-ctl-element[type="checkbox"]').style.pointerEvents = 'none';
			this.checkInitialBlockVisibility(this.nodes[i]);
		}
	}

	checkInitialBlockVisibility(hiddenRow)
	{
		let checkbox = hiddenRow.querySelector('.ui-ctl-element[type="checkbox"]');
		if (checkbox.checked)
		{
			let hiddenBlock = hiddenRow.nextElementSibling;
			if (hiddenBlock.scrollHeight > 0)
			{
				hiddenBlock.style.height = hiddenBlock.scrollHeight + 'px';
				Dom.addClass(hiddenBlock, 'ui-form-row-hidden-show');
			}
		}
	}

	onClick(event)
	{
		// event.preventDefault();

		let checkbox = event.currentTarget.querySelector('.ui-ctl-element[type="checkbox"]');
		let hiddenBlock = event.currentTarget.nextElementSibling;
		let height = hiddenBlock.scrollHeight;

		this.toggleHiddenBLock(checkbox, hiddenBlock, height);
	}

	toggleHiddenBLock(checkbox, hiddenBlock, height)
	{
		if (!checkbox.checked)
		{
			checkbox.checked = true;
			hiddenBlock.style.height = height + 'px';
			Dom.addClass(hiddenBlock, 'ui-form-row-hidden-show');
		}
		else
		{
			checkbox.checked = false;
			hiddenBlock.style.height = 0;
			Dom.removeClass(hiddenBlock, 'ui-form-row-hidden-show');
		}
	}
}
