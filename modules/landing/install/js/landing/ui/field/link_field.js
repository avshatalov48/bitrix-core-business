;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var trim = BX.Landing.Utils.trim;
	var clone = BX.Landing.Utils.clone;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var htmlToElement = BX.Landing.Utils.htmlToElement;
	var style = BX.Landing.Utils.style;
	var escapeText = BX.Landing.Utils.escapeText;

	/**
	 * Implements interface for works with link field in editor
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.Link = function(data)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);

		this.options = data.options || {};

		BX.remove(this.input);

		this.onValueChangeHandler = data.onValueChange ? data.onValueChange : (function() {});
		this.content = isPlainObject(this.content) ? this.content : {};
		this.content = clone(this.content);
		this.content.text = trim(this.content.text);
		this.content.href = trim(escapeText(this.content.href));
		this.content.target = trim(escapeText(this.content.target));
		this.skipContent = data.skipContent;

		if (!this.containsImage() && !this.containsHtml())
		{
			this.content.text = escapeText(this.content.text);
		}

		this.input = new BX.Landing.UI.Field.Text({
			placeholder: BX.message("FIELD_LINK_TEXT_LABEL"),
			selector: this.selector,
			content: this.content.text,
			textOnly: true,
			onValueChange: function() {
				this.onValueChangeHandler(this);
				fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
			}.bind(this)
		});

		if (this.skipContent)
		{
			this.input.layout.hidden = true;
			this.header.hidden = true;
		}

		this.hrefInput = new BX.Landing.UI.Field.LinkURL({
			title: BX.message("FIELD_LINK_HREF_LABEL"),
			placeholder: BX.message("FIELD_LINK_HREF_PLACEHOLDER"),
			selector: this.selector,
			content: this.content.href,
			onInput: this.onHrefInput.bind(this),
			textOnly: true,
			options: this.options,
			disallowType: data.disallowType,
			disableBlocks: data.disableBlocks,
			disableCustomURL: data.disableCustomURL,
			allowedTypes: data.allowedTypes,
			onValueChange: function() {
				this.onValueChangeHandler(this);
				this.noHrefValueChange();
				fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
			}.bind(this)
		});

		this.targetInput = new BX.Landing.UI.Field.DropdownInline({
			title: BX.message("FIELD_LINK_TARGET_LABEL"),
			selector: this.selector,
			className: "landing-ui-field-dropdown-inline",
			content: this.content.target,
			items: {
				"_self": BX.message("FIELD_LINK_TARGET_SELF"),
				"_blank": BX.message("FIELD_LINK_TARGET_BLANK"),
				"_popup": BX.message("FIELD_LINK_TARGET_POPUP")
			},
			onValueChange: function() {
				this.onValueChangeHandler(this);
				fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
			}.bind(this)
		});

		this.mediaButton = new BX.Landing.UI.Button.BaseButton(this.selector + "_media", {
			html: "<span class=\"fa fa-bolt\"></span>&nbsp;" + BX.message("LANDING_CONTENT_URL_MEDIA_BUTTON"),
			className: "landing-ui-field-link-media",
			onClick: this.onMediaClick.bind(this)
		});

		this.mediaLayout = BX.create("div", {props: {className: "landing-ui-field-link-media-layout"}});

		this.mediaHelpButton = new BX.Landing.UI.Button.BaseButton(this.selector + "_media_qa", {
			html: "<span class=\"fa fa-question-circle\"></span>&nbsp;",
			className: "landing-ui-field-link-media-help"
		});

		this.mediaHelpButton.layout.addEventListener("mouseover", this.onMediaHelpButtonMouseover.bind(this));
		this.mediaHelpButton.layout.addEventListener("mouseout", this.onMediaHelpButtonMouseout.bind(this));

		if (this.containsImage() || this.containsHtml())
		{
			this.input.layout.hidden = true;
			this.header.hidden = true;
			this.hrefInput.header.innerHTML = this.header.innerHTML;
		}

		this.wrapper = BX.Landing.UI.Field.Link.createWrapper();
		this.left = BX.Landing.UI.Field.Link.createLeft();
		this.center = BX.Landing.UI.Field.Link.createCenter();
		this.right = BX.Landing.UI.Field.Link.createRight();

		this.left.appendChild(this.input.layout);
		this.center.appendChild(this.hrefInput.layout);
		this.right.appendChild(this.targetInput.layout);
		this.right.appendChild(this.mediaButton.layout);
		this.right.appendChild(this.mediaHelpButton.layout);

		this.wrapper.appendChild(this.left);
		this.wrapper.appendChild(this.center);
		this.wrapper.appendChild(this.right);
		this.layout.appendChild(this.wrapper);
		this.layout.appendChild(this.mediaLayout);

		this.layout.classList.add("landing-ui-field-link");

		this.adjustVideo();
	};


	/**
	 * Creates wrapper element
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.Link.createWrapper = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-link-wrapper"}});
	};


	/**
	 * Creates center column element
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.Link.createCenter = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-link-center"}});
	};


	/**
	 * Creates left column element
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.Link.createLeft = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-link-left"}});
	};


	/**
	 * Creates right column element
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Field.Link.createRight = function()
	{
		return BX.create("div", {props: {className: "landing-ui-field-link-right"}});
	};



	BX.Landing.UI.Field.Link.prototype = {
		constructor: BX.Landing.UI.Field.Link,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,
		superClass: BX.Landing.UI.Field.BaseField,

		noHrefValueChange: function()
		{
			// if (this.hrefInput.containsPlaceholder())
			// {
			// 	this.hrefInput.getPlaceholderData()
			// 		.then(function(data) {
			// 			return this.hrefInput.createPlaceholder({name: data.name});
			// 		}.bind(this))
			// 		.then(function(placeholder) {
			// 			placeholder.setAttribute("contenteditable", "false");
			//
			// 			style(placeholder, {
			// 				"display": "inline-flex",
			// 				"position": "relative",
			// 				"margin-right": "2px",
			// 				"left": "0",
			// 				"top": "0"
			// 			})
			// 			.then(function() {
			// 				this.input.input.innerHTML = this.input.input.innerHTML
			// 					.replace("{{name}}", placeholder.outerHTML);
			// 			}.bind(this));
			//
			//
			// 		}.bind(this));
			// }
		},

		/**
		 * @inheritDoc
		 * @return {boolean}
		 */
		isChanged: function()
		{
			return JSON.stringify(this.content) !== JSON.stringify(this.getValue());
		},


		/**
		 * Checks that node contains image
		 * @return {boolean}
		 */
		containsImage: function()
		{
			return !!BX.create("div", {html: this.content.text}).querySelector("img");
		},

		/**
		 * @return {boolean}
		 */
		containsHtml: function()
		{
			var element = htmlToElement(this.content.text);
			return !!element && !element.matches("br");
		},


		/**
		 * Gets value
		 * @return {{text: (*|string), href: (*|string), target: (*|string)}}
		 */
		getValue: function()
		{
			var value = {
				text: decodeDataValue(trim(this.input.getValue())),
				href: trim(this.hrefInput.getValue()),
				target: trim(this.targetInput.getValue())
			};

			if (this.isAvailableMedia() && this.isEnabledMedia())
			{
				value.attrs = {
					"data-url": trim(this.mediaService.getEmbedURL())
				};
			}

			if (this.hrefInput.getDynamic())
			{
				if (!isPlainObject(value.attrs))
				{
					value.attrs = {};
				}

				if (this.hrefInput.input.firstElementChild)
				{
					value.attrs["data-url"] = this.hrefInput.input.firstElementChild.getAttribute("data-url");
				}

				value.attrs["data-dynamic"] = this.hrefInput.getDynamic();
			}

			if (this.skipContent)
			{
				delete value['text'];
			}

			return value;
		},


		setValue: function(value)
		{
			if (isPlainObject(value))
			{
				this.input.setValue(escapeText(value.text));
				this.hrefInput.setValue(value.href);
				this.targetInput.setValue(escapeText(value.target));
			}
		},


		reset: function()
		{
			this.setValue({text: "", href: "", "target": "_self"});
		},


		enableMedia: function()
		{
			this.mediaButton.enable();
			this.targetInput.disable();
			this.targetInput.closePopup();
			this.targetInput.setValue("_popup");
			this.showMediaPreview();
		},

		disableMedia: function()
		{
			this.mediaButton.disable();
			this.targetInput.enable();
			this.targetInput.closePopup();
			this.targetInput.setValue("_self");
			this.hideMediaPreview();
			this.hideMediaSettings();
		},


		isEnabledMedia: function()
		{
			return this.mediaButton.isEnabled();
		},


		showMediaSettings: function()
		{
			if (this.isAvailableMedia())
			{
				this.hideMediaSettings();

				this.mediaSettings = this.mediaService.getSettingsForm();

				if (this.mediaSettings)
				{
					this.mediaLayout.appendChild(this.mediaSettings.layout);
				}
			}
		},

		hideMediaSettings: function()
		{
			if (this.mediaSettings)
			{
				BX.remove(this.mediaSettings.layout);
			}
		},


		/**
		 * Checks that media is available
		 * @return {boolean}
		 */
		isAvailableMedia: function()
		{
			var ServiceFactory = new BX.Landing.MediaService.Factory();
			return !!ServiceFactory.create(this.hrefInput.getValue());
		},

		onMediaClick: function()
		{
			if (this.isAvailableMedia())
			{
				if (!this.isEnabledMedia())
				{
					this.enableMedia();
				}
				else
				{
					this.disableMedia();
				}
			}
		},

		onMediaHelpButtonMouseover: function(event)
		{
			BX.Landing.UI.Tool.Suggest
				.getInstance()
				.show(this.mediaHelpButton.layout, {
					description: BX.create("div", {
						props: {className: "landing-ui-field-link-media-help-popup-content"},
						children: [
							BX.create("div", {
								props: {className: "landing-ui-field-link-media-help-popup-content-title"},
								html: BX.message("LANDING_CONTENT_URL_MEDIA_HELP_TITLE")
							}),
							BX.create("div", {
								props: {className: "landing-ui-field-link-media-help-popup-content-content"},
								html: BX.message("LANDING_CONTENT_URL_MEDIA_HELP")
							})
						]
					}).outerHTML,
					angleOffset: 53
				});
		},

		onMediaHelpButtonMouseout: function()
		{
			BX.Landing.UI.Tool.Suggest
				.getInstance()
				.hide();
		},

		onVideoPreviewClick: function()
		{
			$.fancybox.open({
				src: this.mediaService.getEmbedURL(),
				type: "iframe",
				afterShow: function(instance, current)
				{
					var iframe = current.$slide.find("iframe")[0];
					void BX.Landing.MediaPlayer.Factory.create(iframe);
				}
			}, {
				iframe: {
					scrolling : "auto"
				}
			});
		},

		showMediaPreview: function()
		{
			// Make and show loader
			var loader = new BX.Loader({
				target: this.mediaLayout,
				mode: "inline",
				offset: {top: "calc(50% - 55px)", left: "calc(50% - 55px)"}
			});
			this.video = loader.layout;
			loader.show();

			this.mediaService.getURLPreviewElement()
				.then(function(element) {
					// Remove loader
					BX.remove(this.video);

					// Make and show URL preview
					this.video = element;
					this.video.title = BX.message("LANDING_CONTENT_URL_PREVIEW_TITLE");
					this.mediaLayout.appendChild(this.video);
					this.video.addEventListener("click", this.onVideoPreviewClick.bind(this));
					this.showMediaSettings();
				}.bind(this), function() {
					this.hideMediaSettings();
					BX.remove(this.video);
				}.bind(this));
		},

		hideMediaPreview: function()
		{
			if (this.video)
			{
				BX.remove(this.video);
			}
		},


		adjustVideo: function()
		{
			var embedURL = "attrs" in this.content && "data-url" in this.content.attrs ? this.content.attrs["data-url"] : "";
			var ServiceFactory = new BX.Landing.MediaService.Factory();
			this.mediaService = ServiceFactory.create(
				this.hrefInput.getValue(),
				BX.Landing.Utils.getQueryParams(embedURL)
			);

			if (this.mediaService)
			{
				this.mediaService.url = this.hrefInput.getValue();

				this.disableMedia();

				if (this.isAvailableMedia())
				{
					this.enableMedia();
				}
			}
			else
			{
				this.disableMedia();
			}
		},

		onHrefInput: function()
		{
			this.adjustVideo();
		}
	}
})();