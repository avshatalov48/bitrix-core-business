// @flow
'use strict';

import 'ui.tilegrid';
import 'ui.forms';
import { Tag } from "main.core";

export default class GridUnit extends BX.TileGrid.Item
{
	constructor(item)
	{
		super({
			id: item.type,
		});
		this.item = item;
	}

	getContent()
	{
		this.gridUnit = Tag.render`<div class="calendar-sync-item ${this.getAdditionalContentClass()}" style="${this.getContentStyles()}">
			<div class="calendar-item-content">
				${this.getImage()}
				${this.getTitle()}
				${(this.isActive() ? this.getStatus() : '')}
			</div>
		</div>`;

		this.gridUnit.addEventListener('click', this.onClick.bind(this));

		return this.gridUnit;
	}

	getTitle()
	{
		if (!this.layout.title)
		{
			this.layout.title = Tag.render `
				<div class="calendar-sync-item-title">${BX.util.htmlspecialchars(this.item.getGridTitle())}</div>`;
		}

		return this.layout.title;
	}

	getImage()
	{
		return Tag.render `
			<div class="calendar-sync-item-image">
				<div class="calendar-sync-item-image-item" style="background-image: ${'url(' + this.item.getGridIcon() + ')'}"></div>
			</div>`;
	}

	getStatus()
	{
		if (this.isActive())
		{
			return Tag.render `
				<div class="calendar-sync-item-status"></div>
			`;
		}

		return '';
	}

	isActive()
	{
		return this.item.getConnectStatus();
	}

	getAdditionalContentClass()
	{
		if (this.isActive())
		{
			if (this.item.getSyncStatus())
			{
				return 'calendar-sync-item-selected';
			}
			else
			{
				return 'calendar-sync-item-failed';
			}
		}
		else
		{
			return '';
		}
	}

	getContentStyles()
	{
		if (this.isActive())
		{
			return 'background-color:' + this.item.getGridColor() + ';';
		}
		else
		{
			return '';
		}
	}

	onClick()
	{
		BX.ajax.runAction('calendar.api.calendarajax.analytical', {
			analyticsLabel: {
				open_connection_slider: 'Y',
				sync_connection_type: this.item.getType(),
				sync_connection_status: this.item.getSyncStatus() ? 'Y' : 'N',
			}
		});

		if (this.item.hasMenu())
		{
			this.item.showMenu(this.gridUnit);
		}
		else if (this.item.getConnectStatus())
		{
			this.item.openActiveConnectionSlider(this.item.getConnection());
		}
		else
		{
			this.item.openInfoConnectionSlider();
		}
	}
}