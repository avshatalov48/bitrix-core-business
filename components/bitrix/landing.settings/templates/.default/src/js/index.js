import {Event, ajax as Ajax, Tag, Dom} from 'main.core';
import {Loader} from "main.loader";

type PageOption = {
	page: string,
	name: string,
	link: ?string,
	linkToSave: ?string,
	current: ?boolean,
	container: ?HTMLDivElement,
	form: ?HTMLFormElement,
}

export class LandingSettings
{
	siteId: number;
	landingId: number;
	pages: {
		[code: string]: PageOption
	};
	container: HTMLDivElement;
	links: [HTMLAnchorElement];
	saveButton: HTMLButtonElement;
	loader: Loader;
	loadingPages: [string];

	/**
	 * Constructor.
	 */
	constructor(options: {
		siteId: number,
		landingId: number,
		pages: {
			[code: string]: PageOption
		},
		menuId: string,
		containerId: string,
		saveButtonId: string,
	})
	{
		this.siteId = options.siteId;
		this.landingId = options.landingId;

		// pages
		this.pages = options.pages;
		this.container = document.getElementById(options.containerId);
		this.loader = new Loader({target: this.container});
		for (let page in this.pages)
		{
			this.pages[page].container = Tag.render`<div class="landing-settings-page-container"></div>`;
			Dom.append(this.pages[page].container, this.container);
		}
		this.loadingPages = [];

		// links
		this.links = document.getElementById(options.menuId).querySelectorAll('li a');
		let currentLink = this.links[0];
		this.links.forEach(link =>
		{
			Event.bind(link, 'click', (event) =>
			{
				event.preventDefault();
				event.stopPropagation();
				this.onLinkClick(link)
			});

			if (
				link.dataset.page
				&& this.pages[link.dataset.page]
				&& this.pages[link.dataset.page].current === true
			)
			{
				currentLink = link;
			}
		});
		if (currentLink)
		{
			this.onLinkClick(currentLink);
		}

		// save
		this.saveButton = document.getElementById(options.saveButtonId);
		this.onSave = this.onSave.bind(this);
		Event.bind(this.saveButton, 'click', this.onSave);
	}

	onLinkClick(link: HTMLAnchorElement)
	{
		if (link.dataset.page)
		{
			this.onPageChange(link.dataset.page);
		}
		else if (link.dataset.placement)
		{
			// for open app pages in slider
			if (
				typeof BX.rest !== 'undefined' &&
				typeof BX.rest.Marketplace !== 'undefined'
			)
			{
				BX.rest.Marketplace.bindPageAnchors({});
			}
			BX.rest.AppLayout.openApplication(
				link.dataset.appId,
				{
					SITE_ID: this.siteId,
					LID: this.landingId,
				},
				{
					PLACEMENT: link.dataset.placement,
					PLACEMENT_ID: link.dataset.placementId,
				},
			);
		}
	}

	onPageChange(page: string)
	{
		const currPage = this.pages[page];
		if (currPage)
		{
			if (currPage.container.childNodes.length === 0)
			{
				this.loader.show();
				this.loadingPages.push(page);
				Ajax.get(currPage.link, result =>
				{
					currPage.container.innerHTML = result;
					this.loadingPages.splice(this.loadingPages.indexOf(page), 1);
					if (this.loadingPages.length === 0)
					{
						this.loader.hide();
					}
					const form = currPage.container.querySelector('.ui-form');
					if (form)
					{
						currPage.form = form;
					}
				});
			}

			for (let page in this.pages)
			{
				this.pages[page].container.hidden = true;
			}
			currPage.container.hidden = false;
		}
	}

	onSave()
	{
		this.loader.show()

		const submits = [];
		for (let page in this.pages)
		{
			const currPage = this.pages[page];
			if (currPage.form)
			{
				submits.push(
					fetch(currPage.linkToSave, {
						method: 'POST',
						body: new FormData(currPage.form),
						headers: {
							'Bx-ajax': true,
						},
					}),
				);
			}
		}
		Promise.all(submits)
			.then((results: [Response]) =>
			{
				let all = true;
				results.forEach(result => {
					all = all && result.ok;
				});
				if (all)
				{
					top.window['landingSettingsSaved'] = true;
					top.BX.onCustomEvent('BX.Landing.Filter:apply');
					this.loader.hide()
					top.window.location.reload();
					BX.SidePanel.Instance.close();
				}
			})
			.catch(err =>
			{
				console.error(err);
			});
	}
}