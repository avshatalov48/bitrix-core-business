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
		this.customUrlDisabled = data.disableCustomURL;
		this.detailPageMode = data.detailPageMode === true;

		if (!this.containsImage() && !this.containsHtml())
		{
			this.content.text = escapeText(this.content.text);
		}

		this.input = new BX.Landing.UI.Field.Text({
			placeholder: BX.Landing.Loc.getMessage("FIELD_LINK_TEXT_LABEL"),
			selector: this.selector,
			content: this.content.text,
			textOnly: true,
			onValueChange: function() {
				this.onValueChangeHandler(this);

				var hrefInputValue = this.hrefInput.getValue();
				if (hrefInputValue === '#landing0')
				{
					var value = this.input.getValue();
					var placeholder = this.hrefInput.input.firstElementChild;

					if (placeholder)
					{
						var textNode = placeholder.querySelector('.landing-ui-field-url-placeholder-text');
						textNode.innerText = BX.Text.decode(value.replace(/&nbsp;/g, ' '));
					}
				}

				var event = new BX.Event.BaseEvent({
					data: {value: this.getValue()},
					compatData: [this.getValue()],
				});
				this.emit('change', event);
			}.bind(this)
		});

		if (this.skipContent)
		{
			this.input.layout.hidden = true;
			this.header.hidden = true;
		}

		this.hrefInput = new BX.Landing.UI.Field.LinkURL({
			title: BX.Landing.Loc.getMessage("FIELD_LINK_HREF_LABEL"),
			placeholder: BX.Landing.Loc.getMessage("FIELD_LINK_HREF_PLACEHOLDER"),
			selector: this.selector,
			content: this.content.href,
			onInput: this.onHrefInput.bind(this),
			textOnly: true,
			options: this.options,
			disallowType: data.disallowType,
			disableBlocks: data.disableBlocks,
			disableCustomURL: data.disableCustomURL,
			allowedTypes: data.allowedTypes,
			detailPageMode: data.detailPageMode === true,
			sourceField: data.sourceField,
			onValueChange: function() {
				this.onValueChangeHandler(this);
				this.onHrefValueChange();
				var event = new BX.Event.BaseEvent({
					data: {value: this.getValue()},
					compatData: [this.getValue()],
				});
				this.emit('change', event);
			}.bind(this),
			onNewPage: function()
			{
				var value = this.input.getValue();
				var placeholder = this.hrefInput.input.firstElementChild;

				if (placeholder)
				{
					var textNode = placeholder.querySelector('.landing-ui-field-url-placeholder-text');
					textNode.innerHTML = value.replace(/&nbsp;/g, ' ');
				}
			}.bind(this)
		});

		this.targetInput = new BX.Landing.UI.Field.DropdownInline({
			title: BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_LABEL"),
			selector: this.selector,
			className: "landing-ui-field-dropdown-inline",
			content: this.content.target,
			items: {
				"_self": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_SELF"),
				"_blank": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_BLANK"),
				"_popup": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_POPUP")
			},
			onValueChange: function() {
				this.onValueChangeHandler(this);
				var event = new BX.Event.BaseEvent({
					data: {value: this.getValue()},
					compatData: [this.getValue()],
				});
				this.emit('change', event);
			}.bind(this)
		});

		this.mediaButton = new BX.Landing.UI.Button.BaseButton(this.selector + "_media", {
			html: "<span class=\"fa fa-bolt\"></span>&nbsp;" + BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_BUTTON"),
			className: "landing-ui-field-link-media",
			onClick: this.onMediaClick.bind(this),
			disabled: true,
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

		if (!this.customUrlDisabled)
		{
			this.adjustVideo();
		}
		if (this.content.target === '_popup')
		{
			this.adjustVideo();
		}

		this.adjustEditLink();
		this.adjustTarget();
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

		onHrefValueChange: function()
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

		adjustEditLink: function()
		{
			var type = this.hrefInput.getPlaceholderType();
			var pageType = BX.Landing.Env.getInstance().getType();

			if (type === "PAGE" && pageType !== "KNOWLEDGE" && pageType !== "GROUP")
			{
				var value = this.hrefInput.getValue();

				if (BX.type.isString(value) && value.length > 0)
				{
					this.hrefInput
						.getPageData(value)
						.then(function(result) {
							var urlMask = BX.Landing.Main.getInstance()
								.options.params.sef_url.landing_view;

							var href = urlMask
								.replace("#site_show#", result.siteId)
								.replace("#landing_edit#", result.id);

							[].slice.call(this.layout.querySelectorAll('.landing-ui-field-edit-link'))
								.forEach(BX.remove);

							this.editLink = this.createEditLink(
								BX.Landing.Loc.getMessage("LANDING_LINK_FILED__EDIT_PAGE_LINK_LABEL"),
								href
							);
							this.layout.appendChild(this.editLink);
						}.bind(this));
				}
			}
		},

		createEditLink: function(text, href)
		{
			return BX.create("div", {
				props: {
					className: "landing-ui-field-edit-link"
				},
				children: [
					BX.create("a", {
						attrs: {
							href: href,
							target: "_blank",
							title: BX.Landing.Loc.getMessage("LANDING_LINK_FILED__EDIT_LINK_TITLE")
						},
						text: text
					})
				]
			});
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
			var preparedValue = this.input.getValue().replace(/&nbsp;/g, ' ');
			var value = {
				text: decodeDataValue(trim(preparedValue)),
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

			this.adjustEditLink();
			this.adjustTarget();
		},

		adjustTarget: function()
		{
			if (!this.isAvailableMedia())
			{
				var type = BX.Landing.Env.getInstance().getType();
				var value = this.getValue();

				if (type === 'KNOWLEDGE' || type === 'GROUP')
				{
					this.targetInput.disable();

					if (
						// #landing123 || #block123 || #myAnchor
						/^#(\w+)([0-9])$/.test(value.href)
					)
					{
						this.targetInput.setValue('_self');
					}
					else
					{
						this.targetInput.setValue('_blank');
					}
				}
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
			if (this.isEnabledMedia())
			{
				this.mediaButton.disable();
				this.targetInput.enable();
				this.targetInput.closePopup();
				this.targetInput.setValue("_self");
				this.hideMediaPreview();
				this.hideMediaSettings();
			}
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
					if (!this.mediaService)
					{
						this.adjustVideo();
					}
					else
					{
						this.enableMedia();
					}
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
								html: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_HELP_TITLE")
							}),
							BX.create("div", {
								props: {className: "landing-ui-field-link-media-help-popup-content-content"},
								html: BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_MEDIA_HELP")
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
					this.video.title = BX.Landing.Loc.getMessage("LANDING_CONTENT_URL_PREVIEW_TITLE");
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
			var pageType = BX.Landing.Env.getInstance().getType();
			if (pageType !== 'KNOWLEDGE' && pageType !== 'GROUP')
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
			}
		},

		onHrefInput: function()
		{
			if (!this.customUrlDisabled)
			{
				this.adjustVideo();
			}
			this.adjustEditLink();
			this.adjustTarget();
		}
	}
})();