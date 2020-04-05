;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	/**
	 * Implements interface for works with detail page panel
	 * Implements singleton patter
	 * @extends {BX.Landing.UI.Panel.URLList}
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.DetailPage = function(id, data)
	{
		BX.Landing.UI.Panel.URLList.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-detail-page");
		this.overlay.classList.add("landing-ui-panel-detail-page");

		this.cache = new BX.Cache.MemoryCache();

		document.body.appendChild(this.layout);
	};


	/**
	 * Stores instance of BX.Landing.UI.Panel.DetailPage
	 * @type {BX.Landing.UI.Panel.DetailPage}
	 */
	BX.Landing.UI.Panel.DetailPage.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Panel.DetailPage
	 * @return {BX.Landing.UI.Panel.DetailPage}
	 */
	BX.Landing.UI.Panel.DetailPage.getInstance = function()
	{
		if (!BX.Landing.UI.Panel.DetailPage.instance)
		{
			BX.Landing.UI.Panel.DetailPage.instance = (
				new BX.Landing.UI.Panel.DetailPage("detail_page_panel", {
					title: BX.Landing.Loc.getMessage("LANDING_BLOCK__DETAIL_PAGE_PANEL_TITLE")
				})
			);
		}

		return BX.Landing.UI.Panel.DetailPage.instance;
	};


	BX.Landing.UI.Panel.DetailPage.prototype = {
		constructor: BX.Landing.UI.Panel.DetailPage,
		__proto__: BX.Landing.UI.Panel.URLList.prototype,

		getSources: function()
		{
			return this.cache.remember('sources', function() {
				var rootWindow = BX.Landing.PageObject.getRootWindow();
				return rootWindow.BX.Landing.Main.getInstance().options.sources;
			});
		},

		onSourceClick: function(source)
		{
			this.sidebarButtons.forEach(function(button) {
				if (button.id === source.id)
				{
					button.activate();
					return;
				}

				button.deactivate();
			});

			this.content.innerHTML = "";
			this.showLoader();

			this.content.appendChild(BX.create('div', {
				props: {className: 'ui-alert ui-alert-warning landing-ui-panel-list-description'},
				children: [
					BX.create('span', {
						props: {className: 'ui-alert-message'},
						html: BX.Landing.Loc.getMessage('LANDING_BLOCK__DETAIL_PAGE_LIST_DESCRIPTION')
					})
				]
			}));

			BX.Landing.Backend.getInstance()
				.getDynamicTemplates(source.id)
				.then(function(templates) {
					templates.forEach(function(template) {
						this.appendCard(this.createTemplatePreview(template));
					}, this);

					this.loader.hide();
				}.bind(this));
		},

		createTemplatePreview: function(template)
		{
			return new BX.Landing.UI.Card.LandingPreviewCard({
				title: template.TITLE,
				description: template.DESCRIPTION,
				preview: template.PREVIEW2X,
				onClick: this.onTemplateClick.bind(this, template)
			});
		},

		onTemplateClick: function(template)
		{
			this.loader.show();

			var backend = BX.Landing.Backend.getInstance();

			backend
				.action(
					'Utils::checkMultiFeature',
					{code: ['create_page', 'publication_page']}
				)
				.then(function(response) {
					if (!response || response === 'false')
					{
						var rootWindow = BX.Landing.PageObject.getRootWindow();
						rootWindow.BX.Landing.PaymentAlertShow({
							message: BX.Landing.Loc.getMessage('LANDING_PUBLIC_PAGE_REACHED')
						});

						this.loader.hide();
					}
					else
					{
						backend
							.action('Landing::addByTemplate', {
								siteId: backend.getSiteId(),
								code: template.ID
							})
							.then(function(response) {
								this.loader.hide();
								this.onChange({type: 'landing', id: response, name: template.TITLE});

								var activeButton = this.sidebarButtons.getActive();
								if (activeButton)
								{
									var sourceId = activeButton.id;
									var env = BX.Landing.Env.getInstance();
									var envOptions = env.getOptions();

									var source = envOptions.sources.find(function(currentSource) {
										return currentSource.id === sourceId;
									});

									if (source)
									{
										source.default.detail = '#landing' + response;
									}

									env.setOptions(envOptions);
								}
							}.bind(this));
					}
				}.bind(this));
		},

		showSourcesButtons: function()
		{
			this.appendSidebarButton(
				new BX.Landing.UI.Button.SidebarButton('templates', {
					text: BX.Landing.Loc.getMessage('LANDING_BLOCK__DETAIL_PAGE_PANEL_TEMPLATES')
				})
			);

			this.getSources().forEach(function(source) {
				if (source.settings.detailPage)
				{
					this.appendSidebarButton(
						new BX.Landing.UI.Button.SidebarButton(source.id, {
							text: source.name,
							child: true,
							onClick: this.onSourceClick.bind(this, source)
						})
					);
				}
			}, this);
		},

		showSitesButtons: function()
		{
			this.appendSidebarButton(
				new BX.Landing.UI.Button.SidebarButton("my_sites", {
					text: BX.Landing.Loc.getMessage("LANDING_LINKS_PANEL_MY_SITES")
				})
			);

			return BX.Landing.Backend.getInstance()
				.getSites()
				.then(function(sites) {
					sites.forEach(function(site) {
						this.appendSidebarButton(
							new BX.Landing.UI.Button.SidebarButton(site.ID, {
								text: site.TITLE,
								onClick: this.onSiteClick.bind(this, site.ID, false),
								child: true
							})
						);
					}, this);
				}.bind(this));
		},

		showCreatePageButton: function()
		{
			this.appendSidebarButton(
				new BX.Landing.UI.Button.SidebarButton("feedback_button", {
					className: "landing-ui-button-sidebar-feedback",
					text: BX.Landing.Loc.getMessage("LANDING_BLOCK__DETAIL_PAGE_PANEL_ADD_PAGE_BUTTON"),
					onClick: function() {
						var options = BX.Landing.Main.getInstance().options;
						var urlMask = options.params.sef_url.site_show;
						var siteId = options.site_id;
						var url = urlMask.replace('#site_show#', siteId) + '#createPage';

						window.open(url, '_blank');
					}
				})
			);
		},

		buildSidebar: function()
		{
			return Promise.all([
				this.showSourcesButtons(),
				this.showSitesButtons()
					.then(function() {
						this.showCreatePageButton();
					}.bind(this))
			]);
		},

		show: function(options)
		{
			BX.Landing.UI.Panel.Content.prototype.show.call(this);

			this.clear();
			this.showLoader();

			this.buildSidebar();

			var button = this.sidebarButtons.find(function(button) {
				return button.id === options.source;
			});

			if (button)
			{
				button.layout.click();
			}

			return new Promise(function(resolve) {
				this.promiseResolve = resolve;
			}.bind(this));
		}
	};
})();