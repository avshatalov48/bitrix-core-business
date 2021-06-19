;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var proxy = BX.Landing.Utils.proxy;
	var bind = BX.Landing.Utils.bind;

	/**
	 * Implements interface for works with template preview
	 * @constructor
	 */
	BX.Landing.TemplateTeaser = function(params)
	{
		this.ajaxUrl = params.ajaxUrl;
		this.ajaxParams = {};

		this.texts = params.texts;
		this.counter = 0;

		this.buttonForm = document.querySelector('[data-role="landing-sm-form"]');
		this.createButton = document.querySelector('[data-role="landing-sm-create"]');
		this.loaderTextContainer = document.querySelector('[data-role="landing-sm-teaser-loader"]');
		//this.loaderContainer = document.querySelector('[data-role="landing-sm-teaser-loader-ext"]');
		this.progressBar = null;

		this.onCreateButtonClick = proxy(this.onCreateButtonClick, this);
		this.onRandomLoaderNext = proxy(this.onRandomLoaderNext, this);

		this.init();

		return this;
	};

	/**
	 * Gets instance of BX.Landing.TemplateTeaser
	 * @return {BX.Landing.TemplateTeaser}
	 */
	BX.Landing.TemplateTeaser.getInstance = function(params)
	{
		return (
			BX.Landing.TemplateTeaser.instance ||
			(BX.Landing.TemplateTeaser.instance = new BX.Landing.TemplateTeaser(params))
		);
	};

	BX.Landing.TemplateTeaser.prototype = {
		/**
		 * Initializes template teaser elements
		 */
		init: function()
		{
			this.ajaxParams['start'] = 'Y';
			this.ajaxParams['showcaseId'] = 'fashion';

			bind(this.createButton, 'click', this.onCreateButtonClick);
		},

		/**
		 * Handles click event on create button
		 * @param {MouseEvent} event
		 */
		onCreateButtonClick: function(event)
		{
			event.preventDefault();

/*			this.loaderText = BX.create("div",
				{
					props: { className: "landing-template-preview-loader-text"},
					text: ''
				}
			);
			this.progressBar = new BX.UI.ProgressBar({
				column: true
			});

			this.progressBar.getContainer().classList.add("ui-progressbar-landing-preview");

			this.loaderContainer.appendChild(this.loaderText);
			this.loaderContainer.appendChild(this.progressBar.getContainer()); */

			this.createButton.classList.add('landing-sm-teaser-button--load');
			var loader = new BX.Loader({
				target: document.querySelector('.landing-sm-teaser-button'),
				size: 40
			});
			loader.show();
			this.randomLoader();
			this.createCatalog();
		},

		/**
		 * Base actions for create catalog.
		 */
		createCatalog: function()
		{
			if (this.ajaxUrl === '')
			{
				return;
			}
			BX.ajax({
				'method': 'POST',
				'dataType': 'json',
				'url': this.ajaxUrl,
				'data':  BX.ajax.prepareData(this.ajaxParams),
				'onsuccess': BX.proxy(this.createCatalogResult, this)
			})
		},

		/**
		 * Result step in create catalog.
		 * @param data
		 */
		createCatalogResult: function(data)
		{
			if (data.status === 'continue')
			{
				this.ajaxParams['start'] = 'N';
				//this.progressBar.update(data.progress);
				//this.progressBar.setTextBefore(data.message);
				this.createCatalog();
			}
			else
			{
				this.finalRedirectAjax();
			}
		},

		/**
		 * Redirect to final URL or submit it by ajax and close slider.
		 * @param url
		 */
		finalRedirectAjax: function()
		{
			this.buttonForm.submit();
		},

		randomLoader: function()
		{
			this.counter = 0;

			BX.cleanNode(this.loaderTextContainer);

			this.loaderTextContainer.appendChild(BX.create('span', {
				props: { className: 'landing-sm-teaser-loader--show' },
				text: this.texts[this.counter]
			}));

			setInterval(this.onRandomLoaderNext, 3000)
		},

		onRandomLoaderNext: function()
		{
			this.counter++;
			this.counter === this.texts.length ? this.counter = 0 : null;
			BX.cleanNode(this.loaderTextContainer);

			this.loaderTextContainer.appendChild(BX.create('span', {
				props: {
					className: this.counter === 3 ?
						'landing-sm-teaser-loader--show landing-sm-teaser-loader--without-dotted' :
						'landing-sm-teaser-loader--show'
				},
				text: this.texts[this.counter]
			}));
		}
	};
})();