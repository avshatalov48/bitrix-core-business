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
	static PAGE_LINK_SELECTOR = 'a[data-page], a[data-placement]';

	siteId: number;
	landingId: number;
	pages: {
		[code: string]: PageOption
	};
	currentPage: PageOption;
	menu: HTMLDivElement;
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
		type: string,
	})
	{
		this.siteId = options.siteId;
		this.landingId = options.landingId;
		this.type = options.type;

		// pages
		this.pages = options.pages;
		this.container = document.getElementById(options.containerId);
		this.menu = document.getElementById(options.menuId);

		for (let page in this.pages)
		{
			this.pages[page].container = Tag.render`<div class="landing-settings-page-container"></div>`;
			Dom.append(this.pages[page].container, this.container);
		}
		this.loadingPages = [];

		this.loaderContainer = Tag.render`<div class="landing-settings-loader-container"></div>`;
		Dom.insertAfter(this.loaderContainer, this.container);
		this.loader = new Loader({target: this.loaderContainer});

		// links
		this.links = [].slice.call(this.menu.querySelectorAll(LandingSettings.PAGE_LINK_SELECTOR));
		let currentLink = this.links[0];
		this.links.forEach(link => {
			this.bindMenuLink(link);

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
			this.onMenuLinkClick(currentLink);
		}

		// save
		this.saveButton = document.getElementById(options.saveButtonId);
		this.onSave = this.onSave.bind(this);
		Event.bind(this.saveButton, 'click', this.onSave);
	}

	showLoader()
	{
		this.loader.show();
		Dom.show(this.loaderContainer);
	}

	hideLoader()
	{
		this.loader.hide();
		Dom.hide(this.loaderContainer);
	}

	bindMenuLink(link: HTMLAnchorElement)
	{
		Event.bind(link, 'click', event => {
			event.preventDefault();
			event.stopPropagation();
			this.onMenuLinkClick(link)
		});
	}

	bindPageLink(pageLink: HTMLAnchorElement)
	{
		if (pageLink.dataset.page)
		{
			const currentMenuLink = this.links.find(menuLink => menuLink.dataset.page === pageLink.dataset.page);
			if (currentMenuLink)
			{
				Event.bind(pageLink, 'click', event => {
					event.preventDefault();
					event.stopPropagation();
					currentMenuLink.click();
				});
			}
		}
	}

	onMenuLinkClick(link: HTMLAnchorElement)
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

	onPageChange(pageId: string)
	{
		const pageToLoad = this.pages[pageId];
		if (pageToLoad)
		{
			if (pageToLoad.container.childNodes.length === 0)
			{
				this.showLoader();
				this.loadingPages.push(pageId);
				Ajax.get(pageToLoad.link, result =>
				{
					pageToLoad.container.innerHTML = result;
					this.loadingPages.splice(this.loadingPages.indexOf(pageId), 1);
					if (this.loadingPages.length === 0)
					{
						this.hideLoader();
					}
					const form = pageToLoad.container.querySelector('form.landing-form');
					if (form)
					{
						pageToLoad.form = form;
					}

					const pageLinks = pageToLoad.container.querySelectorAll(LandingSettings.PAGE_LINK_SELECTOR);
					if (pageLinks.length > 0)
					{
						pageLinks.forEach(link => this.bindPageLink(link));
					}

					if (this.currentPage)
					{
						this.currentPage.container.hidden = true;
					}
					this.currentPage = pageToLoad;
					this.currentPage.container.hidden = false;
				});
			}
			else
			{
				if (this.currentPage)
				{
					this.currentPage.container.hidden = true;
				}
				this.currentPage = pageToLoad;
				this.currentPage.container.hidden = false;
			}
		}
	}

	onSave()
	{
		this.showLoader()

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
					this.hideLoader();
					if (this.type === 'KNOWLEDGE' || this.type === 'GROUP')
					{
						BX.SidePanel.Instance.close();
						BX.SidePanel.Instance.reload();
					}
					else
					{
						top.window.location.reload();
						BX.SidePanel.Instance.close();
					}
				}
			})
			.catch(err =>
			{
				console.error(err);
			});
	}
}