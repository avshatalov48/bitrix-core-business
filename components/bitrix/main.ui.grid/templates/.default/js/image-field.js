;(function() {
	'use strict';

	BX.Reflection.namespace('BX.Grid');

	BX.Grid.ImageField = function(parent, options) {
		this.parent = parent;
		this.options = options;
		this.cache = new BX.Cache.MemoryCache();
	};

	BX.Grid.ImageField.prototype = {
		getPreview: function()
		{
			return this.cache.remember('preview', function() {
				return BX.create('img', {
					props: {
						className: 'main-grid-image-editor-preview'
					},
					attrs: {
						src: this.options.VALUE
					}
				});
			}.bind(this));
		},

		getFileInput: function()
		{
			return this.cache.remember('fileInput', function() {
				return BX.create("input", {
					props: {
						className: "main-grid-image-editor-file-input"
					},
					attrs: {
						type: "file",
						accept: "image/*",
						name: this.options.NAME
					},
					events: {
						change: function(event) {
							var reader = new FileReader();
							reader.onload = function(event) {
								this.getPreview().src = event.currentTarget.result;
							}.bind(this);

							reader.readAsDataURL(event.target.files[0]);

							BX.Dom.remove(this.getFakeField());
							BX.Dom.append(this.getFileInput(), this.getLayout());
							BX.Dom.removeClass(this.getRemoveButton(), 'ui-btn-disabled');
							BX.Dom.style(this.getPreview(), null);
						}.bind(this)
					}
				})
			}.bind(this));
		},

		getUploadButton: function()
		{
			return this.cache.remember('uploadButton', function() {
				return BX.create('button', {
					props: {
						className: "ui-btn ui-btn-xs"
					},
					text: this.parent.getParam("MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_EDIT"),
					events: {
						click: function(event) {
							event.preventDefault();
							this.getFileInput().click();
						}.bind(this)
					}
				});
			}.bind(this));
		},

		getRemoveButton: function()
		{
			return this.cache.remember('removeButton', function() {
				return BX.create('button', {
					props: {
						className: "ui-btn ui-btn-xs ui-btn-danger"
					},
					events: {
						click: function(event) {
							event.preventDefault();
							BX.Dom.append(this.getFakeField(), this.getLayout());
							BX.Dom.remove(this.getFileInput());
							BX.Dom.addClass(this.getRemoveButton(), 'ui-btn-disabled');
							BX.Dom.style(this.getPreview(), {
								opacity: .4
							});
						}.bind(this)
					},
					text: this.parent.getParam('MAIN_UI_GRID_IMAGE_EDITOR_BUTTON_REMOVE')
				});
			}.bind(this));
		},

		getFakeField: function()
		{
			return this.cache.remember('deleted', function() {
				return BX.create("input", {
					props: {
						className: "main-grid-image-editor-fake-file-input"
					},
					attrs: {
						type: "hidden",
						name: this.options.NAME,
						value: 'null'
					}
				});
			}.bind(this));
		},

		getLayout: function()
		{
			return this.cache.remember('layout', function() {
				return BX.create("div", {
					props: {
						className: "main-grid-image-editor main-grid-editor"
					},
					attrs: {
						name: this.options.NAME
					},
					children: [
						BX.create("div", {
							props: {
								className: "main-grid-image-editor-left"
							},
							children: [
								this.getPreview()
							]
						}),
						BX.create("div", {
							props: {
								className: "main-grid-image-editor-right"
							},
							children: [
								this.getUploadButton(),
								this.getRemoveButton()
							]
						}),
						this.getFileInput()
					]
				});
			}.bind(this));
		}
	};
})();