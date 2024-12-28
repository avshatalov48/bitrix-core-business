import {Dom, Event, Text, Tag} from "main.core";
import {PopupWindowManager} from "main.popup";
import {EventEmitter} from "main.core.events";
import ColumnItemOptions from '../columnitem';
import Base from "./base";

export default class Title extends Base
{
	static TYPE = 'title';

	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.rightId = options.id;
		this.group = options.group;
		this.groupHead = options.groupHead;
		this.isExpanded = false;
		this.node = null;
		this.toggleIndicator = null;
	}

	render(): HTMLElement
	{
		const node = Tag.render`
			<div 
				class='ui-access-rights-column-item-text ui-access-rights-column-item-title'
				data-id='${this.getId()}'
			>
				 ${Text.encode(this.text)}
			</div>
		`;

		if (this.groupHead)
		{
			this.toggleIndicator = Tag.render`
				<span class="ui-access-rights-column-item-text-toggle-indicator ui-icon-set --chevron-down"></span>
			`;
			Dom.prepend(this.toggleIndicator, node);
		}

		if (this.group)
		{
			Dom.addClass(node, '--group-children');
		}

		Event.bind(node, 'mouseenter', this.adjustPopupHelper.bind(this));

		Event.bind(node, 'mouseleave', () => {
			if (this.popupHelper)
			{
				this.popupHelper.close();
			}
		});

		Event.bind(node, 'click', this.onGroupToggle.bind(this));

		this.node = node;

		return node;
	}

	onGroupToggle(): void
	{
		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:toggleGroup', {
			id: this.rightId,
		});

		if (!this.node || !this.groupHead)
		{
			return;
		}

		if (this.grid.igGroupsExpanded(this.rightId))
		{
			Dom.removeClass(this.toggleIndicator, '--chevron-down');
			Dom.addClass(this.toggleIndicator, '--chevron-up');
		}
		else
		{
			Dom.addClass(this.toggleIndicator, '--chevron-down');
			Dom.removeClass(this.toggleIndicator, '--chevron-up');
		}
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
