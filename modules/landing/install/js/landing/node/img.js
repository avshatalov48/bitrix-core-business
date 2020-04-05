(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var attr = BX.Landing.Utils.attr;
	var data = BX.Landing.Utils.data;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;

	/**
	 * Implements interface for works with image node
	 *
	 * @extends {BX.Landing.Block.Node}
	 * @param {nodeOptions} options
	 * @property node {HTMLImageElement|HTMLElement}
	 *
	 * @constructor
	 */
	BX.Landing.Block.Node.Img = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);
		this.type = "img";
		this.editPanel = null;
		this.lastValue = null;
		this.field = null;
		this.uploadParams = options.uploadParams;

		if (!this.isGrouped())
		{
			this.node.addEventListener("click", this.onClick.bind(this));
		}

		if (this.isAllowInlineEdit())
		{
			this.node.setAttribute("title", BX.Landing.Loc.getMessage("LANDING_TITLE_OF_IMAGE_NODE"));
		}
	};


	/**
	 * Checks that node use backgroundImage
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {boolean}
	 */
	function isBackground(node)
	{
		return node.node.nodeName !== "IMG";
	}


	/**
	 * Checks that node is image
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {boolean}
	 */
	function isImage(node)
	{
		return node.node.nodeName === "IMG";
	}


	/**
	 * Checks that node is icon
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {boolean}
	 */
	function isIcon(node)
	{
		return node.node.nodeName === "SPAN" || node.node.nodeName === "I" || node.node.nodeName === "EM";
	}


	/**
	 * Gets background url
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {boolean}
	 */
	function getBackgroundUrl(node)
	{
		var style = node.node.getAttribute('style');

		if (style)
		{
			var res = style.split(";")[0].match(/url\((.*?)\)/);

			if (res && res[1])
			{
				return res[1].replace(/["|']/g, "");
			}
		}

		return "";
	}

	/**
	 * Gets background url 2x
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {boolean}
	 */
	function getBackgroundUrl2x(node)
	{
		var style = node.node.getAttribute('style');

		if (style)
		{
			var res = style.match(/1x, url\(["|'](.*)["|']\) 2x\); /);

			if (res && res[1])
			{
				return res[1].replace(/["|']/g, "");
			}
		}

		return "";
	}


	/**
	 * Gets file id
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {int}
	 */
	function getFileId(node)
	{
		var fileId = parseInt(node.node.dataset.fileid);
		return fileId === fileId ? fileId : -1;
	}

	/**
	 * Gets file id 2x
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {int}
	 */
	function getFileId2x(node)
	{
		var fileId = parseInt(node.node.dataset.fileid2x);
		return fileId === fileId ? fileId : -1;
	}


	/**
	 * Gets image alt
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {string}
	 */
	function getAlt(node)
	{
		var alt = attr(node.node, "alt");
		return !!alt ? alt : "";
	}

	function getPseudoUrl(node)
	{
		var url = data(node.node, "data-pseudo-url");
		return !!url ? url : "";
	}


	/**
	 * Gets image src
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {string}
	 */
	function getImageSrc(node)
	{
		var src = attr(node.node, "src");
		return !!src ? src : "";
	}

	/**
	 * Gets image src 2x
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {string}
	 */
	function getImageSrc2x(node)
	{
		var src = attr(node.node, "srcset");
		return !!src ? src.replace(" 2x", "") : "";
	}


	/**
	 * Sets image value or converts to image and sets value
	 * @param {BX.Landing.Block.Node.Img} node
	 * @param {object} value
	 */
	function setImageValue(node, value)
	{
		if (!isImage(node))
		{
			var newNode = BX.create("img", {
				attrs: {src: value.src, alt: value.alt, "data-fileid": value.id}
			});

			node.node.parentNode.insertBefore(newNode, node.node);
			BX.remove(node.node);
			node.node = newNode;
		}
		else
		{
			node.node.src = value.src;
			node.node.alt = value.alt;
			node.node.dataset.fileid = value.id || -1;
			node.node.srcset = value.src2x ? value.src2x + " 2x" : "";
			node.node.dataset.fileid2x = value.id2x || -1;
		}
	}


	/**
	 * Sets background value or converts to div and sets value
	 * @param {BX.Landing.Block.Node.Img} node
	 * @param {object} value
	 */
	function setBackgroundValue(node, value)
	{
		if (!isBackground(node))
		{
			var newNode = BX.create("div", {
				attrs: {
					style: "background-image: url(\""+value.src+"\")",
					"data-fileid": value.id
				}
			});

			node.node.parentNode.insertBefore(newNode, node.node);
			BX.remove(node.node);
			node.node = newNode;
		}
		else
		{
			if (value.src)
			{
				node.node.style.backgroundImage = "url(\""+value.src+"\")";

				if (value.src2x)
				{
					var style = [
						"background-image: url(\""+value.src+"\");",
						"background-image: -webkit-image-set(url(\""+value.src+"\") 1x, url(\""+value.src2x+"\") 2x);",
						"background-image: image-set(url(\""+value.src+"\") 1x, url(\""+value.src2x+"\") 2x);"
					].join(' ');

					node.node.setAttribute("style", style);
				}
			}
			else
			{
				if (node.node.style)
				{
					node.node.style.removeProperty("background-image");
				}
			}

			node.node.dataset.fileid = value.id || -1;
			node.node.dataset.fileid2x = value.id2x || -1;
		}
	}


	BX.Landing.Block.Node.Img.prototype = {
		__proto__: BX.Landing.Block.Node.prototype,
		constructor: BX.Landing.Block.Node.Img,


		/**
		 * Click on field - edit mode.
		 * @param {MouseEvent} event
		 */
		onClick: function(event)
		{
			if (this.manifest.allowInlineEdit !== false &&
				BX.Landing.Main.getInstance().isControlsEnabled() &&
				(!BX.Landing.Block.Node.Text.currentNode ||
				!BX.Landing.Block.Node.Text.currentNode.isEditable()) &&
				!BX.Landing.UI.Panel.StylePanel.getInstance().isShown()
			)
			{
				event.preventDefault();
				event.stopPropagation();

				BX.Landing.UI.Button.FontAction.hideAll();
				BX.Landing.UI.Button.ColorAction.hideAll();

				if (!this.editPanel)
				{
					this.editPanel = new BX.Landing.UI.Panel.Content(this.selector, {
						title: BX.Landing.Loc.getMessage("LANDING_IMAGE_PANEL_TITLE"),
						className: "landing-ui-panel-edit-image"
					});

					this.editPanel.appendFooterButton(
						new BX.Landing.UI.Button.BaseButton("save_block_content", {
							text: BX.Landing.Loc.getMessage("BLOCK_SAVE"),
							onClick: this.save.bind(this),
							className: "landing-ui-button-content-save"
						})
					);
					this.editPanel.appendFooterButton(
						new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
							text: BX.Landing.Loc.getMessage("BLOCK_CANCEL"),
							onClick: this.editPanel.hide.bind(this.editPanel),
							className: "landing-ui-button-content-cancel"
						})
					);

					document.body.appendChild(this.editPanel.layout);
				}

				var form = new BX.Landing.UI.Form.BaseForm({title: this.manifest.name});
				form.addField(this.getField());

				this.editPanel.clear();
				this.editPanel.appendForm(form);
				this.editPanel.show();
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			}
		},


		/**
		 * Saves value changes
		 */
		save: function()
		{
			var value = this.editPanel.forms[0].fields[0].getValue();

			if (JSON.stringify(this.getValue()) !== JSON.stringify(value))
			{
				this.setValue(value);
			}

			this.editPanel.hide();
		},


		/**
		 * Gets form field
		 * @return {?BX.Landing.UI.Field.Image}
		 */
		getField: function()
		{
			if (!this.field)
			{
				var description = "";

				if (this.manifest.dimensions)
				{
					var dimensions = this.manifest.dimensions;

					var width = (
						dimensions.width
						|| dimensions.maxWidth
						|| dimensions.minWidth
					);

					var height = (
						dimensions.height
						|| dimensions.maxHeight
						|| dimensions.minHeight
					);

					if (width && !height)
					{
						description = BX.Landing.Loc.getMessage('LANDING_CONTENT_IMAGE_RECOMMENDED_WIDTH') + ' ';
						description += width + 'px';
					}
					else if (height && !width)
					{
						description = BX.Landing.Loc.getMessage('LANDING_CONTENT_IMAGE_RECOMMENDED_HEIGHT') + ' ';
						description += height + 'px';
					}
					else if (width && height)
					{
						description = BX.Landing.Loc.getMessage("LANDING_CONTENT_IMAGE_RECOMMENDED_SIZE") + " ";
						description += width + "px&nbsp;/&nbsp;";
						description += height + "px";
					}
				}

				var value = this.getValue();
				value.url = decodeDataValue(value.url);

				var disableLink = !!this.node.closest("a");

				this.field = new BX.Landing.UI.Field.Image({
					selector: this.selector,
					title: this.manifest.name,
					description: description,
					disableLink: disableLink,
					content: value,
					dimensions: !!this.manifest.dimensions ? this.manifest.dimensions : {},
					disableAltField: isBackground(this),
					uploadParams: this.uploadParams
				});
			}
			else
			{
				this.field.setValue(this.getValue());
				this.field.content = this.getValue();
				requestAnimationFrame(function() {
					this.field.adjustPreviewBackgroundSize();
				}.bind(this));
			}

			return this.field;
		},


		/**
		 * Sets node value
		 * @param value - Path to image
		 * @param {?boolean} [preventSave = false]
		 * @param {?boolean} [preventHistory = false]
		 */
		setValue: function(value, preventSave, preventHistory)
		{
			this.lastValue = this.lastValue || this.getValue();
			this.preventSave(preventSave);

			value.src = decodeURIComponent(value.src);

			if (isImage(this))
			{
				setImageValue(this, value);
			}

			if (isBackground(this))
			{
				setBackgroundValue(this, value);
			}

			if (value.url)
			{
				attr(this.node, "data-pseudo-url", value.url);
			}

			this.onChange();

			if (!preventHistory)
			{
				BX.Landing.History.getInstance().push(
					new BX.Landing.History.Entry({
						block: this.getBlock().id,
						selector: this.selector,
						command: "editImage",
						undo: this.lastValue,
						redo: this.getValue()
					})
				);
			}

			this.lastValue = this.getValue();
		},

		/**
		 * Gets node value
		 * @return {{src: string}}
		 */
		getValue: function()
		{
			var value = {type: "", src: "", src2x: "", id: -1, id2x: -1, alt: "", url: ""};

			if (isBackground(this))
			{
				value.type = "background";
				value.src = getBackgroundUrl(this);
				value.src2x = getBackgroundUrl2x(this);
				value.id = getFileId(this);
				value.id2x = getFileId2x(this);
			}

			if (isImage(this))
			{
				value.type = "image";
				value.src = getImageSrc(this);
				value.src2x = getImageSrc2x(this);
				value.id = getFileId(this);
				value.id2x = getFileId2x(this);
				value.alt = getAlt(this);
			}

			value.url = (
				encodeDataValue(getPseudoUrl(this)) ||
				{text: "", href: "", target: "_self", enabled: false}
			);

			return value;
		}
	};

})();