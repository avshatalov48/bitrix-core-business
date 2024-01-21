import { Dom, Tag, Event, Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import './style.css';
import { Actions, Icon } from 'ui.icon-set.api.core';
import 'ui.icon-set.actions';
import { Loader } from 'main.loader';

type Option = {
	slider: BX.SidePanel.Slider,
	container: HTMLElement,
	pagesHref: Array<PagesHref>,
	useLoader: boolean,
	pageType: string,
};

type PagesHref = {
	ID: number,
	HREF: string,
};

export class PageSwapper extends EventEmitter
{
	#disableClass = 'ui-swap-btn-disabled';
	btnSize = 20;

	constructor(options: Option)
	{
		super(options);
		this.setEventNamespace('BX.UI.Sidepanel.PageSwapper');
		this.slider = options.slider || null;
		this.container = options.container || null;
		this.pagesHref = options.pagesHref || null;
		this.useLoader = options.useLoader || false;
		this.pageType = options.pageType || 'default';
	}

	init(): void
	{
		if (!this.slider)
		{
			console.warn('BX.UI.SliderPageSwapper.Preview: \'slider\' is not defined');

			return;
		}

		if (!this.container)
		{
			console.warn('BX.UI.SliderPageSwapper.Preview: \'container\' is not defined');

			return;
		}

		this.window = this.slider.getFrameWindow();
		this.curHref = this.slider.url;
		this.pageId = this.slider.getData().get('pageId');

		if (!this.#isAnyPageSet())
		{
			this.#setNeighboursHref();
		}
		this.#setPrevButton();
		this.#setNextButton();
		this.setTitles(this.pageType);
		this.getWrapper();
		this.#renderWrapper();
	}

	setPrevPage(prevPageId: number = null, prevPageHref: string = null): void
	{
		if (prevPageId)
		{
			this.prevPageId = prevPageId;
		}

		if (prevPageHref)
		{
			this.prevPageHref = prevPageHref;
		}

		this.#setButtonHref(this.getPrevButton(), this.prevPageId, this.prevPageHref);
	}

	setNextPage(nextPageId: number = null, nextPageHref: string = null): void
	{
		if (nextPageId)
		{
			this.nextPageId = nextPageId;
		}

		if (nextPageHref)
		{
			this.nextPageHref = nextPageHref;
		}

		this.#setButtonHref(this.getNextButton(), this.nextPageId, this.nextPageHref);
	}

	updatePagesHref(pagesHref: Array<PagesHref>): void
	{
		this.showLoader();
		this.#setNeighboursHref(pagesHref);
		this.setPrevPage();
		this.setNextPage();
		this.hideLoader();
	}

	#setPrevButton(): void
	{
		const icon = new Icon({
			icon: Actions.CHEVRON_LEFT,
			size: this.btnSize,
		});

		this.prevBtn = icon.render();
		Dom.addClass(this.getPrevButton(), 'ui-page-swap-left');
		this.#setButtonHref(this.getPrevButton(), this.prevPageId, this.prevPageHref);
	}

	#setNextButton(): void
	{
		const icon = new Icon({
			icon: Actions.CHEVRON_RIGHT,
			size: this.btnSize,
		});

		this.nextBtn = icon.render();
		Dom.addClass(this.getNextButton(), 'ui-page-swap-right');
		this.#setButtonHref(this.getNextButton(), this.nextPageId, this.nextPageHref);
	}

	getPrevButton(): Node
	{
		return this.prevBtn;
	}

	getNextButton(): Node
	{
		return this.nextBtn;
	}

	#setButtonHref(button: node, pageId: number = null, pageHref: string = null): void
	{
		if (pageId && pageHref)
		{
			this.#addListenerToButton(button, pageId, pageHref);
			this.hideLoader();
		}
		else
		{
			Event.unbindAll(button, 'click');
		}
		this.#toggleButton(button, pageId, pageHref);
	}

	#toggleButton(button: node, pageId: number, pageHref: string): void
	{
		if (Dom.hasClass(button, this.#disableClass) && pageId && pageHref)
		{
			Dom.removeClass(button, this.#disableClass);
			Dom.style(button, 'cursor', 'pointer');
		}
		else if (!Dom.hasClass(button, this.#disableClass) && !(pageId && pageHref))
		{
			Dom.addClass(button, this.#disableClass);
			Dom.style(button, 'cursor', 'not-allowed');
		}
	}

	getWrapper(): Node
	{
		if (!this.wrapper)
		{
			this.wrapper = Tag.render`
				<div class='ui-page-swapper'>
					${this.getPrevButton()}
					${this.getNextButton()}
				</div>
			`;
		}

		return this.wrapper;
	}

	#renderWrapper(): void
	{
		Dom.append(this.getWrapper(), this.container);

		this.loader = new Loader({
			target: this.getWrapper(),
			size: 20,
			mode: 'absolute',

		});

		if (this.useLoader && !this.#isAnyPageSet())
		{
			this.showLoader();
		}
		else
		{
			this.hideLoader();
		}
	}

	#activateOverlay(): void
	{
		const loader = this.slider.layout.loader;
		if (loader)
		{
			Dom.style(loader, 'opacity', 0.5);
			Dom.style(loader, 'display', 'block');
		}
	}

	#setNeighboursHref(pagesHref: Array<PagesHref> = null): void
	{
		if (pagesHref)
		{
			this.pagesHref = pagesHref;
		}

		if (!this.pagesHref)
		{
			return;
		}

		if (!this.pageId)
		{
			this.pagesHref.forEach((page) => {
				if (page.HREF.includes(this.curHref))
				{
					this.pageId = Number(page.ID);
				}
			});
		}

		this.prevPageId = null;
		this.prevPageHref = null;
		this.nextPageId = null;
		this.nextPageHref = null;

		if (!this.pageId)
		{
			return;
		}
		Object.keys(this.pagesHref).forEach((key) => {
			if (Number(this.pagesHref[key].ID) === this.pageId)
			{
				this.prevPageId = Number(this.pagesHref[key - 1]?.ID) || null;
				this.prevPageHref = this.pagesHref[key - 1]?.HREF || null;
				this.nextPageId = Number(this.pagesHref[Number(key) + 1]?.ID) || null;
				this.nextPageHref = this.pagesHref[Number(key) + 1]?.HREF || null;
			}
		});
	}

	#addListenerToButton(button: node, pageId: number, pageHref: string): void
	{
		Event.bind(button, 'click', () => {
			this.slider.getData().set('pageId', pageId);
			const url = new URL(pageHref, window.location);
			url.searchParams.append('IFRAME_TYPE', 'SIDE_SLIDER');
			url.searchParams.append('IFRAME', 'Y');
			this.#activateOverlay();
			this.window.location.href = url;
		});
	}

	hasPagesBeforeEnd(pagesBeforeEnd: number = 0): boolean
	{
		if (pagesBeforeEnd === 0)
		{
			return (!Type.isUndefined(this.nextPageHref) && !Type.isNull(this.nextPageHref));
		}

		if (pagesBeforeEnd > 0 && Type.isNumber(pagesBeforeEnd))
		{
			let check = null;
			Object.keys(this.pagesHref).forEach((key) => {
				if (Number(this.pagesHref[key].ID) === this.pageId)
				{
					check = this.pagesHref[Number(key) + pagesBeforeEnd]?.HREF || null;
				}
			});

			return !Type.isNull(check);
		}

		return false;
	}

	#isAnyPageSet(): boolean
	{
		return (this.prevPageId && this.prevPageHref) || (this.nextPageId && this.nextPageHref);
	}

	showLoader(): void
	{
		if (this.loader && !this.loader.isShown())
		{
			this.loader.show();
			Dom.style(this.getPrevButton(), 'visibility', 'hidden');
			Dom.style(this.getNextButton(), 'visibility', 'hidden');
		}
	}

	hideLoader(): void
	{
		if (this.loader && this.loader.isShown())
		{
			this.loader.hide();
			Dom.style(this.getPrevButton(), 'visibility', 'visible');
			Dom.style(this.getNextButton(), 'visibility', 'visible');
		}
	}

	setTitles(type: string): void {
		if (type === 'mail')
		{
			this.prevBtn.setAttribute('title', Loc.getMessage('UI_SIDEPANEL_PAGE_SWAPPER_PREVIOUS_MAIL_MESSAGE'));
			this.nextBtn.setAttribute('title', Loc.getMessage('UI_SIDEPANEL_PAGE_SWAPPER_NEXT_MAIL_MESSAGE'));
		}
	}
}
