(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var attr = BX.Landing.Utils.attr;

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
			this.node.setAttribute("title", BX.message("LANDING_TITLE_OF_IMAGE_NODE"));
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
		var background = node.node.style.backgroundImage;
		background = !!background ? background : "";

		return background.slice(4, -1).replace(/["|']/g, "");
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
	 * Gets image alt
	 * @param {BX.Landing.Block.Node.Img} node
	 * @return {string}
	 */
	function getAlt(node)
	{
		var alt = attr(node.node, "alt");
		return !!alt ? alt : "";
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
			node.node.style.backgroundImage = "url(\""+value.src+"\")";
			node.node.dataset.fileid = value.id || -1;
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
						title: BX.message("LANDING_IMAGE_PANEL_TITLE"),
						className: "landing-ui-panel-edit-image"
					});

					this.editPanel.appendFooterButton(
						new BX.Landing.UI.Button.BaseButton("save_block_content", {
							text: BX.message("BLOCK_SAVE"),
							onClick: this.save.bind(this),
							className: "landing-ui-button-content-save"
						})
					);
					this.editPanel.appendFooterButton(
						new BX.Landing.UI.Button.BaseButton("cancel_block_content", {
							text: BX.message("BLOCK_CANCEL"),
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
				BX.Landing.UI.Panel.SmallEditorPanel.getInstance().hide();
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
					description = BX.message("LANDING_CONTENT_IMAGE_RECOMMENDED_SIZE") + " ";
					description += this.manifest.dimensions.width + "px&nbsp;/&nbsp;";
					description += this.manifest.dimensions.height + "px";
				}

				this.field = new BX.Landing.UI.Field.Image({
					selector: this.selector,
					title: this.manifest.name,
					description: description,
					content: this.getValue(),
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

			this.onChange();

			if (!preventHistory)
			{
				BX.Landing.History.getInstance().push(
					new BX.Landing.History.Entry({
						block: top.BX.Landing.Block.storage.getByChildNode(this.node).id,
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
			var value = {type: "", src: "", id: -1, alt: ""};

			if (isBackground(this))
			{
				value.type = "background";
				value.src = getBackgroundUrl(this);
				value.id = getFileId(this);
			}

			if (isImage(this))
			{
				value.type = "image";
				value.src = getImageSrc(this);
				value.id = getFileId(this);
				value.alt = getAlt(this);
			}

			return value;
		}
	};

})();