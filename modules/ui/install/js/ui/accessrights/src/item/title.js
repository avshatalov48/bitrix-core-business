import {Dom, Event, Text, Tag} from "main.core";
import {PopupWindowManager} from "main.popup";
import Base from "./base";

export default class Title extends Base
{
	static TYPE = 'title';

	render(): HTMLElement
	{
		const node = Tag.render`
			<div 
				class='ui-access-rights-column-item-text'
				data-id='${this.getId()}'
			>
				${Text.encode(this.text)}
			</div>
		`;

		Event.bind(node, 'mouseenter', this.adjustPopupHelper.bind(this));

		Event.bind(node, 'mouseleave', () => {
			if (this.popupHelper)
			{
				this.popupHelper.close();
			}
		});

		return node;
	}

	adjustPopupHelper(): void
	{
		const set = this.parentContainer.cloneNode(true);

		Dom.style(set, 'position', 'absolute');
		Dom.style(set, 'display', 'inline');
		Dom.style(set, 'visibility', 'hidden');
		Dom.style(set, 'height', '0');

		Dom.append(set, document.body);

		setTimeout(() => {
			Dom.remove(set);
		});

		if (set.offsetWidth > this.parentContainer.offsetWidth)
		{
			Dom.style(set, 'visibility', 'visible');
			this.getPopupHelper().show();
		}
	}

	getPopupHelper(): Popup
	{
		if (!this.popupHelper)
		{
			this.popupHelper = PopupWindowManager.create(
				null,
				this.parentContainer,
				{
					autoHide: true,
					darkMode: true,
					content: this.text,
					maxWidth: this.parentContainer.offsetWidth,
					offsetTop: -9,
					offsetLeft: 5,
					animation: 'fading-slide'
				}
			);
		}

		return this.popupHelper;
	}
}