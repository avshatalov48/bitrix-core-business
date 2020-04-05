(function ()
{
	"use strict";
	BX.namespace("BX.Report.Analytics.Config");

	BX.Report.Analytics.Config.Controls = function (options)
	{
		this.configurationButton = options.configurationButton;
		this.boardId = options.boardId;

		this.pageTitle = document.getElementById('pagetitle');
		this.pageTitleWrap = document.querySelector('.pagetitle-wrap');
		this.pageControlsContainer = document.querySelector('.pagetitle-container.pagetitle-align-right-container');

		this.init();
	};

	BX.Report.Analytics.Config.Controls.prototype = {
		init: function ()
		{
			BX.bind(
				this.configurationButton,
				'click',
				this.handleConfigurationButtonClick.bind(this)
			);
			this.loader = new BX.Loader({size: 80});
		},
		handleConfigurationButtonClick: function()
		{
			this.confirmationPopup = new BX.PopupWindow('visualconstructor-dashboard-configuration-popup',  this.configurationButton, {
				title: 'Select Row Layout',
				noAllPaddings: true,
				closeByEsc: true,
				autoHide: true,
				content: this.getConfigurationButtonLayout()
			});

			this.confirmationPopup.show();

		},
		getConfigurationButtonLayout: function()
		{
			var menuPoints = [];
			menuPoints.push(BX.create('div', {
				attrs: {
					className: 'analytic-board-configuration-popup-item'
				},
				children: [
					BX.create('div', {
						text: this.getToggleBoardTitle(),
						attrs: {
							className: 'analytic-board-configuration-popup-item-text'
						}
					})
				],
				events: {
					click: BX.delegate(this.toggleBoard, this)
				}
			}));

			return BX.create('div', {
				attrs: {
					className: 'analytic-board-configuration-popup-container'
				},
				children: menuPoints
			});
		},
		getToggleBoardTitle: function()
		{
			return BX.message('VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT');
		},
		toggleBoard: function ()
		{
			this.contentContainer = document.querySelector('.report-analytics-content');
			this.cleanPageContent();
			this.showLoader();
			BX.Report.VC.Core.ajaxPost('analytics.toggleToDefaultByBoardKey', {
				data: {
					IFRAME: 'Y',
					boardKey: this.boardId
				},
				onFullSuccess: BX.delegate(function (response) {
					this.hideLoader();
					this.changePageTitle(response.additionalParams.pageTitle);
					this.changePageControls(response.additionalParams.pageControlsParams);
					BX.html(this.contentContainer, response.data);
				}, this)
			});
			this.confirmationPopup.close();
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
		}
	}

})();