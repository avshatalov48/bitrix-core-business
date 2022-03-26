import { Tag } from 'main.core';
import Item from './item';
import ItemMarketing from './itemMarketing';
import Scroller from './scroller';
import '../css/landing.site.tile.css'

export class SiteTile
{
	constructor(options)
	{
		this.renderTo = options.renderTo || null;
		this.items = options.items || [];
		this.scrollerText = options.scrollerText || null
		this.siteTileItems = [];
		this.$container = null;
		this.scroller = null;
		this.setData(this.items);
		this.init();

		setTimeout(this.refreshPreview, 3000);
	}

	getItems()
	{
		return this.siteTileItems;
	}

	setData(data)
	{
		this.siteTileItems = data.map((item)=> {
			if(item.type === 'itemMarketing')
			{
				return new ItemMarketing({
					id: item.id || null,
					title: item.title || null,
					text: item.text || null,
					buttonText: item.buttonText || null,
					onClick: item.onClick || null
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
				ordersCount: parseInt(item.ordersCount) || null,
				phone: item.phone || null,
				preview: item.preview || null,
				published: item.published || null,
				deleted: item.deleted || null,
				domainStatus: item.domainStatus || null,
				domainStatusMessage: item.domainStatusMessage || null,
				menuItems: item.menuItems || null,
				menuBottomItems: item.menuBottomItems || null,
				access: item.access || {},
				articles: item.articles || null,
				grid: this
			});
		});

		return this.siteTileItems;
	}

	getContainer()
	{
		if(!this.$container)
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
		if(this.renderTo)
		{
			this.renderTo.appendChild(this.getContainer());
		}

		this.afterDraw();
	}

	afterDraw()
	{
		if(this.getItems().length > 4)
		{
			if(!this.scroller)
			{
				this.scroller = new Scroller({
					grid: this,
					scrollerText: this.scrollerText
				});
			}
		}
	}

	init()
	{
		this.draw();
	}

	refreshPreview()
	{
		const previews = document.querySelectorAll('.landing-sites__preview-image');

		if (previews)
		{
			[...previews].map(node => {
				let url = node.style.backgroundImage.match(/url\(["']?([^"']*)["']?\)/);
				if (url)
				{
					url = url[1];
					url += (url.indexOf('?') > 0) ? '&' : '?';
					node.style.backgroundImage = 'url(' + url + 'refreshed)';
				}
			});
		}
	}
}
