;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");


	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var isString = BX.Landing.Utils.isString;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var create = BX.Landing.Utils.create;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;


	/**
	 * Implements interface for works with image field in editor
	 *
	 * @extends {BX.Landing.UI.Field.Text}
	 *
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Field.Image = function(data)
	{
		BX.Landing.UI.Field.Text.apply(this, arguments);

		this.dimensions = typeof data.dimensions === "object" ? data.dimensions : null;
		this.uploadParams = typeof data.uploadParams === "object" ? data.uploadParams : {};
		this.onValueChangeHandler = data.onValueChange ? data.onValueChange : (function() {});
 		this.layout.classList.add("landing-ui-field-image");
		this.type = this.content.type || "image";
		this.input.innerText = this.content.src;
		this.input.hidden = true;
		this.disableAltField = typeof data.disableAltField === "boolean" ? data.disableAltField : false;

		this.fileInput = createFileInput(this.selector);
		this.fileInput.addEventListener("change", this.onFileInputChange.bind(this));

		this.linkInput = createLinkInput();
		this.linkInput.onInputHandler = this.onLinkInput.bind(this);

		this.dropzone = createDropzone(this.selector);
		this.dropzone.hidden = true;
		this.dropzone.insertBefore(this.fileInput, this.dropzone.firstElementChild);
		this.dropzone.addEventListener("dragover", this.onDragOver.bind(this));
		this.dropzone.addEventListener("dragleave", this.onDragLeave.bind(this));
		this.dropzone.addEventListener("drop", this.onDrop.bind(this));

		this.clearButton = createClearButton();
		this.clearButton.on("click", this.onClearClick.bind(this));

		this.preview = createImagePreview();
		this.preview.appendChild(this.clearButton.layout);
		this.preview.style.backgroundImage = "url("+this.input.innerText.trim()+")";
		this.preview.addEventListener("dragenter", this.onImageDragEnter.bind(this));

		this.icon = createIcon();

		this.image = createImageLayout();
		this.image.appendChild(this.preview);
		this.image.appendChild(this.icon);
		this.image.dataset.fileid = this.content.id;

		this.hiddenImage = create("img", {
			props: {className: "landing-ui-field-image-hidden"}
		});

		if (isPlainObject(this.content) && "src" in this.content)
		{
			this.hiddenImage.src = this.content.src;
		}

		this.altField = createAltField();
		this.altField.setValue(this.content.alt);

		this.loader = createLoader();

		this.left = createLeftLayout();
		this.left.appendChild(this.dropzone);
		this.left.appendChild(this.image);
		this.left.appendChild(this.hiddenImage);

		if (this.description)
		{
			this.left.appendChild(this.description);
		}

		this.left.appendChild(this.altField.layout);
		this.left.appendChild(this.linkInput.layout);
		this.left.appendChild(this.loader.layout);

		this.uploadButton = createUploadButton();
		this.uploadButton.on("click", this.onUploadClick.bind(this));

		this.editButton = createEditButton();
		this.editButton.on("click", this.onEditClick.bind(this));

		this.right = createRightLayout();
		this.right.appendChild(this.uploadButton.layout);
		this.right.appendChild(this.editButton.layout);


		this.form = createForm();
		this.form.appendChild(this.left);
		this.form.appendChild(this.right);

		this.layout.appendChild(this.form);

		this.enableTextOnly();

		if (!this.input.innerText.trim() || this.input.innerText.trim() === window.location.toString())
		{
			this.showDropzone();
		}

		if (this.disableAltField)
		{
			this.altField.layout.hidden = true;
		}

		if (this.content.type === "icon")
		{
			this.type = "icon";
			this.classList = this.content.classList;
			var sourceClassList = this.content.classList;
			var newClassList = [];

			BX.Landing.UI.Panel.Icon.getInstance().libraries.forEach(function(library) {
				library.categories.forEach(function(category) {
					category.items.forEach(function(item) {
						var classList = item.split(" ");
						classList.forEach(function(className) {
							if (sourceClassList.indexOf(className) !== -1 && newClassList.indexOf(className) === -1)
							{
								newClassList.push(className);
							}
						});
					});
				});
			});


			this.icon.innerHTML = "<span class=\""+newClassList.join(" ")+"\"></span>";
			this.showPreview();
			this.altField.layout.hidden = true;
		}

		this.content = this.getValue();
		BX.DOM.write(function() {
			this.adjustPreviewBackgroundSize();
		}.bind(this));

		// Force init Image Editor
		// BX.Landing.UI.Tool.ImageEditor.getInstance();
	};


	/**
	 * Creates file input
	 * @return {Element}
	 */
	function createFileInput(id)
	{
		return BX.create("input", {
			props: {className: "landing-ui-field-image-dropzone-input"},
			attrs: {accept: "image/*", type: "file", id: "file_" + id, name: "picture"}
		});
	}


	/**
	 * Creates link input field
	 * @return {BX.Landing.UI.Field.Text}
	 */
	function createLinkInput()
	{
		var field = new BX.Landing.UI.Field.Text({
			id: "path_to_image",
			placeholder: BX.message("LANDING_IMAGE_UPLOAD_MENU_LINK_LABEL")
		});
		field.enableTextOnly();
		field.layout.hidden = true;
		return field;
	}


	/**
	 * Creates dropzone
	 * @param {string} id
	 * @return {Element}
	 */
	function createDropzone(id)
	{
		return BX.create("label", {
			props: {className: "landing-ui-field-image-dropzone"},
			children: [
				BX.create("div", {
					props: {className: "landing-ui-field-image-dropzone-text"},
					html: (
						"<div class=\"landing-ui-field-image-dropzone-title\">"+BX.message("LANDING_IMAGE_DROPZONE_TITLE")+"</div>" +
						"<div class=\"landing-ui-field-image-dropzone-subtitle\">"+BX.message("LANDING_IMAGE_DROPZONE_SUBTITLE")+"</div>"
					)
				})
			],
			attrs: {"for": "file_" + id}
		});
	}


	/**
	 * Creates clear button
	 * @return {BX.Landing.UI.Button.BaseButton}
	 */
	function createClearButton()
	{
		return new BX.Landing.UI.Button.BaseButton("clear", {
			className: "landing-ui-field-image-action-button-clear"
		});
	}


	/**
	 * Creates image preview
	 * @return {Element}
	 */
	function createImagePreview()
	{
		return BX.create("div", {
			props: {className: "landing-ui-field-image-preview-inner"}
		});
	}


	/**
	 * Creates icon layout
	 * @return {Element}
	 */
	function createIcon()
	{
		return BX.create("span", {
			props: {className: "landing-ui-field-image-preview-icon"}
		});
	}


	/**
	 * Creates image layout
	 * @return {Element}
	 */
	function createImageLayout()
	{
		return BX.create("div", {
			props: {className: "landing-ui-field-image-preview"}
		});
	}


	/**
	 * Creates alt field
	 * @return {BX.Landing.UI.Field.Text}
	 */
	function createAltField()
	{
		var field = new BX.Landing.UI.Field.Text({
			placeholder: BX.message("LANDING_FIELD_IMAGE_ALT_PLACEHOLDER"),
			className: "landing-ui-field-image-alt",
			textOnly: true
		});
		return field;
	}


	/**
	 * Creates loader
	 * @return {BX.Landing.UI.Card.Loader}
	 */
	function createLoader()
	{
		var loader = new BX.Landing.UI.Card.Loader();
		loader.layout.hidden = true;
		loader.layout.classList.add("landing-ui-loader-image");
		return loader;
	}


	/**
	 * Creates left layout
	 * @return {Element}
	 */
	function createLeftLayout()
	{
		return BX.create("div", {
			props: {className: "landing-ui-field-image-left"}
		});
	}


	/**
	 * Creates upload button
	 * @return {BX.Landing.UI.Button.BaseButton}
	 */
	function createUploadButton()
	{
		return new BX.Landing.UI.Button.BaseButton("upload", {
			text: BX.message("LANDING_FIELD_IMAGE_UPLOAD_BUTTON"),
			className: "landing-ui-field-image-action-button"
		});
	}


	/**
	 * Creates edit button
	 * @return {BX.Landing.UI.Button.BaseButton}
	 */
	function createEditButton()
	{
		var field = new BX.Landing.UI.Button.BaseButton("edit", {
			text: BX.message("LANDING_FIELD_IMAGE_EDIT_BUTTON"),
			className: "landing-ui-field-image-action-button",
			disabled: true
		});

		field.layout.disabled = true;

		return field;
	}


	/**
	 * Creates right layout
	 * @return {Element}
	 */
	function createRightLayout()
	{
		return BX.create("div", {
			props: {className: "landing-ui-field-image-right"}
		});
	}


	/**
	 * Creates form
	 * @return {Element}
	 */
	function createForm()
	{
		return BX.create("form", {
			props: {className: "landing-ui-field-image-container"},
			attrs: {method: "post", enctype: "multipart/form-data"},
			events: {
				submit: function(event) {
					event.preventDefault();
				}
			}
		});
	}


	BX.Landing.UI.Field.Image.prototype = {
		constructor: BX.Landing.UI.Field.Image,
		__proto__: BX.Landing.UI.Field.Text.prototype,
		superClass: BX.Landing.UI.Field.Text,
		/**
		 * Handles input event on input field
		 */
		onInputInput: function()
		{
			this.preview.src = this.input.innerText.trim();
		},

		onImageDragEnter: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			if (!this.imageHidden)
			{
				this.showDropzone();
				this.imageHidden = true;
			}
		},

		onDragOver: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.dropzone.classList.add("landing-ui-active");
		},

		onDragLeave: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.dropzone.classList.remove("landing-ui-active");

			if (this.imageHidden)
			{
				this.imageHidden = false;
				this.showPreview();
			}
		},

		onDrop: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.dropzone.classList.remove("landing-ui-active");
			this.onFileChange(event.dataTransfer.files[0]);
			this.imageHidden = false;
		},

		onFileChange: function(file)
		{
			this.showLoader();
			BX.Landing.Backend.getInstance()
				.uploadImage(this.form, file, this.dimensions, this.uploadParams)
				.then(function(response) {
					this.setValue(response);
					this.edit();
					this.hideLoader();
				}.bind(this));
		},

		onFileInputChange: function(event)
		{
			this.onFileChange(event.currentTarget.files[0]);
		},

		onUploadClick: function(event)
		{
			this.bindElement = event.currentTarget;

			event.preventDefault();

			if (!this.uploadMenu)
			{
				this.uploadMenu = BX.PopupMenu.create(
					"upload_" + this.selector + (+new Date()),
					this.bindElement,
					[
						{
							text: BX.message("LANDING_IMAGE_UPLOAD_MENU_UNSPLASH"),
							onclick: this.onUnsplashShow.bind(this)
						},
						{
							text: BX.message("LANDING_IMAGE_UPLOAD_MENU_GOOGLE"),
							onclick: this.onGoogleShow.bind(this)
						},
						{
							text: BX.message("LANDING_IMAGE_UPLOAD_MENU_PARTNER"),
							className: "landing-ui-disabled"
						},
						{
							text: BX.message("LANDING_IMAGE_UPLOAD_MENU_UPLOAD"),
							onclick: this.onUploadShow.bind(this)
						},
						{
							text: BX.message("LANDING_IMAGE_UPLOAD_MENU_LINK"),
							onclick: this.onLinkShow.bind(this)
						}
					],
					{
						events: {
							onPopupClose: function() {
								this.bindElement.classList.remove("landing-ui-active");
								this.uploadMenu.destroy();
								this.uploadMenu = null;
							}.bind(this)
						}
					}
				);
				this.bindElement.parentNode.appendChild(this.uploadMenu.popupWindow.popupContainer);
			}

			this.bindElement.classList.add("landing-ui-active");
			this.uploadMenu.show();

			var rect = BX.pos(this.bindElement, this.bindElement.parentNode);
			this.uploadMenu.popupWindow.popupContainer.style.top = rect.bottom + "px";
			this.uploadMenu.popupWindow.popupContainer.style.left = "auto";
			this.uploadMenu.popupWindow.popupContainer.style.right = "5px";
		},

		onUnsplashShow: function()
		{
			this.uploadMenu.close();
			BX.Landing.UI.Panel.Image.getInstance().show("unsplash", this.dimensions, this.loader, this.uploadParams).then(function(path) {
				this.setValue(path);
				this.edit();
			}.bind(this));
		},

		onGoogleShow: function()
		{
			this.uploadMenu.close();

			var self = this;
			BX.Landing.UI.Panel.Image.getInstance().show(
				"google",
				self.dimensions,
				self.loader,
				self.uploadParams
			).then(function(path) {
				self.setValue(path);
				self.edit();
			});
		},

		onUploadShow: function()
		{
			this.uploadMenu.close();
			this.fileInput.click();
		},

		onLinkShow: function()
		{
			this.uploadMenu.close();
			this.showLinkField();
			this.linkInput.setValue("");
		},

		onEditClick: function(event)
		{
			event.preventDefault();
			this.edit();
		},

		onClearClick: function(event)
		{
			event.preventDefault();
			this.setValue({src: ""});
			this.fileInput.value = "";
			this.showDropzone();
		},

		showDropzone: function()
		{
			this.dropzone.hidden = false;
			this.image.hidden = true;
			this.altField.layout.hidden = true;
			this.linkInput.layout.hidden = true;
		},

		showPreview: function()
		{
			this.dropzone.hidden = true;
			this.image.hidden = false;
			this.altField.layout.hidden = false;
			this.linkInput.layout.hidden = true;
		},

		showLinkField: function()
		{
			this.dropzone.hidden = true;
			this.image.hidden = true;
			this.altField.layout.hidden = true;
			this.linkInput.layout.hidden = false;
		},


		onLinkInput: function(value)
		{
			var tmpImage = BX.create("img");
			tmpImage.src = value;
			tmpImage.onload = function() {
				this.showPreview();
				this.setValue({src: value});
				this.edit();
			}.bind(this);
		},

		showLoader: function()
		{
			this.loader.show();
		},


		hideLoader: function()
		{
			this.loader.hide();
		},


		/**
		 * Handles click event on input field
		 * @param {MouseEvent} event
		 */
		onInputClick: function(event)
		{
			event.preventDefault();
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
		 * Adjusts preview background image size
		 */
		adjustPreviewBackgroundSize: function()
		{
			var img = BX.create("img", {attrs: {src: this.getValue().src}});

			img.onload = function()
			{
				var preview = this.preview.getBoundingClientRect();
				var position = "cover";

				if (img.width > preview.width || img.height > preview.height)
				{
					position = "contain";
				}

				if (img.width < preview.width && img.height < preview.height)
				{
					position = "auto";
				}

				BX.DOM.write(function() {
					this.preview.style.backgroundSize = position;
				}.bind(this));
			}.bind(this);
		},


		/**
		 * @param {object} value
		 */
		setValue: function(value)
		{
			if (value.type !== "icon")
			{
				if (!value || !value.src)
				{
					this.input.innerText = "";
					this.preview.removeAttribute("style");
					this.input.dataset.ext = "";
				}
				else
				{
					this.input.innerText = value.src;
					this.preview.style.backgroundImage = "url(\""+value.src+"\")";
					this.preview.id = BX.util.getRandomString();
					this.hiddenImage.src = value.src;
					this.showPreview();
				}

				if (!value || !value.id)
				{
					this.image.dataset.fileid = -1;
				}
				else
				{
					this.image.dataset.fileid = value.id;
				}
				this.classList = [];
			}
			else
			{
				this.preview.style.backgroundImage = null;
				this.classList = value.classList;
				this.icon.innerHTML = "<span class=\""+value.classList.join(" ")+"\"></span>";
				this.showPreview();
				this.type = "icon";
				this.altField.layout.hidden = true;
				this.altField.setValue("");
				this.input.innerText = "";
			}

			this.adjustPreviewBackgroundSize();
			this.hideLoader();

			this.onValueChangeHandler(this);
			BX.fireEvent(this.layout, "input");
			fireCustomEvent(this, "BX.Landing.UI.Field:change", [this.getValue()]);
		},


		reset: function()
		{
			this.setValue({
				type: this.getValue().type,
				id: -1,
				src: "",
				alt: ""
			});
		},


		/**
		 * Gets field value
		 * @return {{src, [alt]: string, [title]: string}}
		 */
		getValue: function()
		{
			var fileId = parseInt(this.image.dataset.fileid);
			fileId = fileId === fileId ? fileId : -1;
			var value = {type: "", src: "", id: fileId, alt: ""};

			if (this.type === "background")
			{
				value.type = "background";
				value.src = this.input.innerText.trim();
				value.id = fileId;
			}

			if (this.type === "image")
			{
				value.type = "image";
				value.src = this.input.innerText.trim();
				value.id = fileId;
				value.alt = this.altField.getValue();
			}

			if (this.type === "icon")
			{
				value.type = "icon";
				value.classList = this.classList;
			}

			return value;
		},

		edit: function()
		{
			// BX.Landing.UI.Tool.ImageEditor.getInstance()
			// 	.edit({
			// 		image: this.hiddenImage
			// 	})
			// 	.then(function(url) {
			// 		var data = BX.clone(this.getValue());
			// 		data.picture = url;
			// 		BX.Landing.Backend.getInstance()
			// 			.action("Block::uploadFile", data, {}, this.uploadParams)
			// 			.then(this.setValue.bind(this));
			// 	}.bind(this));
		}
	}
})();