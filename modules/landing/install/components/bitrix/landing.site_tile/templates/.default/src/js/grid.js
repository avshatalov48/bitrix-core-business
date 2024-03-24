import { Tag } from 'main.core';
import 'ui.design-tokens';
import Item from './item';
import ItemMarketing from './itemMarketing';
import Scroller from './scroller';
import '../css/landing.site.tile.css';

export class SiteTile
{
	constructor(options)
	{
		this.renderTo = options.renderTo || null;
		this.items = options.items || [];
		this.scrollerText = options.scrollerText || null;
		this.notPublishedText = options.notPublishedText || null;
		this.siteTileItems = [];
		this.$container = null;
		this.scroller = null;
		this.setData(this.items);
		this.init();
	}

	getItems()
	{
		return this.siteTileItems;
	}

	setData(data)
	{
		this.siteTileItems = data.map((item) => {
			if (item.type === 'itemMarketing')
			{
				return new ItemMarketing({
					id: item.id || null,
					title: item.title || null,
					text: item.text || null,
					buttonText: item.buttonText || null,
					onClick: item.onClick || null,
				});
			}

			return new Item({
				id: item.id || null,
				title: item.title || null,
				url: item.url || null,
				fullUrl: item.fullUrl || null,
				domainProvider: item.domainProvider || null,
				pagesUrl: item.pagesUrl || null,
				ordersUrl: item.ordersUrl || null,
				domainUrl: item.domainUrl || null,
				contactsUrl: item.contactsUrl || null,
				indexEditUrl: item.indexEditUrl || null,
				ordersCount: parseInt(item.ordersCount) || null,
				phone: item.phone || null,
				preview: item.preview || null,
				cloudPreview: item.cloudPreview || null,
				published: item.published || null,
				deleted: item.deleted || null,
				domainStatus: item.domainStatus || null,
				domainStatusMessage: item.domainStatusMessage || null,
				menuItems: item.menuItems || null,
				menuBottomItems: item.menuBottomItems || null,
				notPublishedText: this.notPublishedText || null,
				access: item.access || {},
				error: item.error || {},
				articles: item.articles || null,
				grid: this,
			});
		});

		return this.siteTileItems;
	}

	getContainer()
	{
		if (!this.$container)
		{
			this.$container = Tag.render`<div class="landing-sites__grid landing-sites__scope"></div>`;

			for (let i = 0; i < this.siteTileItems.length; i++)
			{
				this.$container.appendChild(this.siteTileItems[i].getContainer());
			}
		}

		return this.$container;
	}

	draw()
	{
		if (this.renderTo)
		{
			this.renderTo.appendChild(this.getContainer());
		}

		this.afterDraw();
	}

	afterDraw()
	{
		if (this.getItems().length > 4)
		{
			if (!this.scroller)
			{
				this.scroller = new Scroller({
					grid: this,
					scrollerText: this.scrollerText,
				});
			}
		}
	}

	init()
	{
		this.draw();
	}
}
