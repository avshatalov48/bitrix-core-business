import { Dom, Event, Runtime, Type } from 'main.core';
import { type BaseEvent, EventEmitter } from 'main.core.events';
import { Loader } from 'main.loader';
import { Toolbar } from 'report.integration.toolbar';

/**
 * @memberOf BX.Report.Analytics
 */
export class Page
{
	scope: HTMLElement;
	menuScope: HTMLElement;
	changeBoardButtons: NodeList;
	contentContainer: HTMLElement;
	currentItem: HTMLElement;
	pageControlsContainer: HTMLElement;

	#toolbar: Toolbar;

	defaultBoardKey: string;
	defaultBoardTitle: string;

	currentPageTitle: string;

	constructor(options: Object)
	{
		this.#toolbar = new Toolbar();

		this.scope = options.scope;
		this.menuScope = options.menuScope;
		this.changeBoardButtons = this.menuScope.querySelectorAll('[data-role="report-analytics-menu-item"]');
		this.contentContainer = this.scope.querySelector('.report-analytics-content');
		this.currentItem = this.menuScope.querySelector(
			'.report-analytics-sidebar-submenu-item.report-analytics-sidebar-submenu-item-active',
		);
		this.pageControlsContainer = this.#toolbar.getRightButtonsContainer();

		this.defaultBoardKey = options.defaultBoardKey;
		this.defaultBoardTitle = options.defaultBoardTitle;

		this.currentPageTitle = top.document.title;

		this.init();
	}

	init(): void
	{
		EventEmitter.subscribe('SidePanel.Slider:onClose', () => {
			this.sliderCloseHandler();
		});
		top.document.title = this.defaultBoardTitle;
		this.changeBoardButtons.forEach((button) => {
			Event.bind(button, 'click', this.handleItemClick.bind(this));
		});
		this.loader = new Loader({ size: 80 });
		top.onpopstate = this.handlerOnPopState.bind(this);
		this.openBoardWithKey(this.defaultBoardKey);

		const activeButton = [...this.changeBoardButtons].find((button) => {
			return button.dataset.reportBoardKey === this.defaultBoardKey;
		});

		this.tryOpenExternalUrl(activeButton);
	}

	tryOpenExternalUrl(button: HTMLElement): boolean
	{
		if (Type.isElementNode(button) && button.dataset.isExternal === 'Y')
		{
			if (button.dataset.isSliderSupport === 'N')
			{
				this.openExternalUrlInNewTab(button.dataset.externalUrl);
			}
			else
			{
				this.openExternalUrl(button.dataset.externalUrl, button.dataset.sliderLoader);
			}

			return true;
		}

		return false;
	}

	handleItemClick(event: PointerEvent): void
	{
		event.preventDefault();
		const button = event.currentTarget;

		this.activateButton(event);
		if (!this.tryOpenExternalUrl(button))
		{
			this.openBoardWithKey(button.dataset.reportBoardKey, button.href);
		}
	}

	openExternalUrl(url: string, loader: string = 'report:analytics'): void
	{
		BX.SidePanel.Instance.open(url, {
			cacheable: false,
			loader,
		});
	}

	openExternalUrlInNewTab(url: string): void
	{
		window.open(url, '_blank');
	}

	openBoardWithKey(boardKey: string, urlForHistory: string): void
	{
		this.cleanPageContent();
		this.showLoader();
		BX.Report.VC.Core.abortAllRunningRequests();

		BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
			data: {
				IFRAME: 'Y',
				boardKey,
			},
			analyticsLabel: {
				boardKey,
			},
			onFullSuccess: function(result)
			{
				this.hideLoader();
				if (String(urlForHistory) !== '')
				{
					top.history.pushState(null, result.additionalParams.pageTitle, urlForHistory);
					top.history.replaceState({
						reportBoardKey: boardKey,
						href: urlForHistory,
					}, result.additionalParams.pageTitle, urlForHistory);
				}

				this.changePageTitle(result.additionalParams.pageTitle);
				this.changePageControls(result.additionalParams.pageControlsParams);

				Runtime.html(this.contentContainer, result.data);
			}.bind(this),
		});
	}

	cleanPageContent(): void
	{
		this.#toolbar.cleanContent();
		Dom.clean(this.contentContainer);
		BX.Report?.Dashboard?.BoardRepository?.destroyBoards();

		BX.VisualConstructor?.BoardRepository?.destroyBoards();
	}

	changePageControls(controlsContent): void
	{
		const config = {};
		config.onFullSuccess = function(result) {
			Runtime.html(this.pageControlsContainer, result.html);
		}.bind(this);
		BX.Report.VC.Core._successHandler(controlsContent, config);
	}

	changePageTitle(title: string): void
	{
		this.#toolbar.setTitle(title);
		top.document.title = title;
	}

	showLoader(): void
	{
		const preview = this.#toolbar.createSkeleton();

		Dom.append(preview, this.contentContainer);
	}

	hideLoader(): void
	{
		this.loader.hide();
	}

	activateButton(event: BaseEvent): void
	{
		const item = event.currentTarget;

		if (!this.currentItem)
		{
			this.currentItem = item;
		}

		Dom.removeClass(this.currentItem, 'report-analytics-sidebar-submenu-item-active');
		this.currentItem = item;
		Dom.addClass(this.currentItem, 'report-analytics-sidebar-submenu-item-active');
	}

	handlerOnPopState(event: BaseEvent): void
	{
		let boardKey = this.defaultBoardKey;
		if (!Type.isUndefined(event.state.reportBoardKey))
		{
			boardKey = event.state.reportBoardKey;
		}

		this.cleanPageContent();
		this.showLoader();
		BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
			data: {
				IFRAME: 'Y',
				boardKey,
			},
			analyticsLabel: {
				boardKey,
			},
			onFullSuccess: function(result)
			{
				this.hideLoader();
				this.cleanPageContent();
				this.changePageTitle(result.additionalParams.pageTitle);
				this.changePageControls(result.additionalParams.pageControlsParams);
				Runtime.html(this.contentContainer, result.data);
			}.bind(this),
		});
	}

	sliderCloseHandler(): void
	{
		top.document.title = this.currentPageTitle;
	}
}
