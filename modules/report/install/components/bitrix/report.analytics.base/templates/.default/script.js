(function ()
{
	BX.namespace("BX.Report.Analytics");

	BX.Report.Analytics.Page = function (options)
	{
		this.scope = options.scope;
		this.menuScope = options.menuScope;
		// this.menuItemsContainers = this.menuScope.querySelectorAll('[data-role="report-analytics-menu-items-container"]');
		this.changeBoardButtons = this.menuScope.querySelectorAll('[data-role="report-analytics-menu-item"]');
		this.contentContainer = this.scope.querySelector('.report-analytics-content');
		this.currentItem = this.menuScope.querySelector('.report-analytics-sidebar-submenu-item.report-analytics-sidebar-submenu-item-active');
		this.currentSelectedContainer = this.menuScope.querySelector('.report-analytics-sidebar-menu-item.report-analytics-sidebar-menu-item-active');
		this.pageTitle = document.querySelector('.ui-side-panel-wrap-title-name');
		this.pageTitleWrap = document.querySelector('.ui-side-panel-wrap-title-wrap');
		this.pageControlsContainer = document.querySelector('.pagetitle-container.pagetitle-align-right-container');

		this.defaultBoardKey = options.defaultBoardKey;
		this.defaultBoardTitle = options.defaultBoardTitle;

		this.currentPageTitle = top.document.title;
		this.init();
	};

	BX.Report.Analytics.Page.prototype = {
		init: function ()
		{
			BX.addCustomEvent("SidePanel.Slider:onClose", function() {
				this.sliderCloseHandler();
			}.bind(this));
			// this.menuItemsContainers.forEach(function(button) {
			// 	BX.bind(button, 'click', this.handleContainerClick.bind(this));
			// }.bind(this));
			top.document.title = this.defaultBoardTitle;
			this.changeBoardButtons.forEach(function(button) {
				BX.bind(button, 'click', this.handleItemClick.bind(this));
			}.bind(this));
			this.loader = new BX.Loader({size: 80});
			top.onpopstate = this.handlerOnPopState.bind(this);
			this.openBoardWithKey(this.defaultBoardKey);

			const activeButton = Array.from(this.changeBoardButtons).find(button => {
				return button.dataset.reportBoardKey === this.defaultBoardKey;
			});

			this.tryOpenExternalUrl(activeButton);
		},

		tryOpenExternalUrl(button)
		{
			if (BX.Type.isElementNode(button) && button.dataset.isExternal === 'Y')
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
		},

		handleItemClick: function(event)
		{
			event.preventDefault();
			var button = event.currentTarget;

			this.activateButton(event);
			if (!this.tryOpenExternalUrl(button))
			{
				this.openBoardWithKey(button.dataset.reportBoardKey, button.href);
			}
		},
		openExternalUrl: function(url, loader = 'report:analytics')
		{
			BX.SidePanel.Instance.open(url, {
				cacheable: false,
				loader: loader,
			});
		},
		openExternalUrlInNewTab: function(url)
		{
			window.open(url, '_blank');
		},
		openBoardWithKey: function (boardKey, urlForHistory)
		{
			urlForHistory = urlForHistory || "";
			this.cleanPageContent();
			this.showLoader();
			BX.Report.VC.Core.abortAllRunningRequests();

			BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
				data: {
					IFRAME: 'Y',
					boardKey: boardKey
				},
				analyticsLabel: {
					boardKey: boardKey
				},
				onFullSuccess: function(result)
				{
					this.hideLoader();
					if(urlForHistory != "")
					{
						top.history.pushState(null, result.additionalParams.pageTitle, urlForHistory);
						top.history.replaceState({
							reportBoardKey: boardKey,
							href: urlForHistory
						}, result.additionalParams.pageTitle, urlForHistory);
					}

					this.changePageTitle(result.additionalParams.pageTitle);
					this.changePageControls(result.additionalParams.pageControlsParams);

					BX.html(this.contentContainer, result.data);

				}.bind(this)
			})
		},
		cleanPageContent: function ()
		{
			this.pageTitle.innerText = '';
			this.pageControlsContainer.innerText = '';
			BX.cleanNode(this.contentContainer);
			if (BX.Report.Dashboard)
			{
				BX.Report.Dashboard.BoardRepository.destroyBoards();
			}
			if (BX.VisualConstructor && BX.VisualConstructor.BoardRepository)
			{
				BX.VisualConstructor.BoardRepository.destroyBoards();
			}
		},
		changePageControls: function(controlsContent)
		{
			var config = {};
			config.onFullSuccess = function(result) {
				BX.html(this.pageControlsContainer, result.html);
			}.bind(this);
			BX.Report.VC.Core._successHandler(controlsContent, config);
		},

		changePageTitle: function(title)
		{
			this.pageTitle.innerText = title;
			top.document.title = title;
		},
		showLoader: function()
		{
			this.pageTitleWrap.style.display = 'none';

			var preview = BX.create('img', {
				attrs: {
					'src': '/bitrix/images/report/visualconstructor/preview/view-without-menu.svg',
					height: '1200px',
					width: '100%'
				}
			});
			this.contentContainer.appendChild(preview);
		},
		hideLoader: function()
		{
			this.pageTitleWrap.style.display = 'block';
			this.loader.hide();
		},
		activateButton: function(event)
		{
			var item = event.currentTarget;

			if (!this.currentItem)
			{
				this.currentItem = item;
			}

			this.currentItem.classList.remove("report-analytics-sidebar-submenu-item-active");
			this.currentItem = item;
			this.currentItem.classList.add("report-analytics-sidebar-submenu-item-active");
		},
		handlerOnPopState: function(event) {
			var boardKey = this.defaultBoardKey;
			if (event.state.reportBoardKey !== undefined)
			{
				boardKey = event.state.reportBoardKey;
			}

			this.cleanPageContent();
			this.showLoader();
			BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
				data: {
					IFRAME: 'Y',
					boardKey: boardKey
				},
				analyticsLabel: {
					boardKey: boardKey
				},
				onFullSuccess: function(result)
				{
					this.hideLoader();
					this.cleanPageContent();
					this.changePageTitle(result.additionalParams.pageTitle);
					this.changePageControls(result.additionalParams.pageControlsParams);
					BX.html(this.contentContainer, result.data);
				}.bind(this)
			});
		},
		sliderCloseHandler: function()
		{
			top.document.title = this.currentPageTitle;
		}

	}
})();
