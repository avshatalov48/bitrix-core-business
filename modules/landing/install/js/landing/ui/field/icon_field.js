;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var clone = BX.Landing.Utils.clone;


	/**
	 * Implements interface for works with Icon field
	 *
	 * @extends {BX.Landing.UI.Field.Image}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.Icon = function(data)
	{
		BX.Landing.UI.Field.Image.apply(this, arguments);
		this.uploadButton.layout.innerText = BX.Landing.Loc.getMessage("LANDING_ICONS_FIELD_BUTTON_REPLACE");
		this.editButton.layout.hidden = true;
		this.clearButton.layout.hidden = true;

		this.dropzone.removeEventListener("dragover", this.onDragOver);
		this.dropzone.removeEventListener("dragleave", this.onDragLeave);
		this.dropzone.removeEventListener("drop", this.onDrop);
		this.preview.removeEventListener("dragenter", this.onImageDragEnter);

		BX.Landing.UI.Panel.IconPanel
			.getLibraries()
			.then(function(libraries) {
				if (libraries.length === 0)
				{
					this.uploadButton.disable();
				}
			}.bind(this));
	};

	BX.Landing.UI.Field.Icon.prototype = {
		constructor: BX.Landing.UI.Field.Icon,
		__proto__: BX.Landing.UI.Field.Image.prototype,

		onUploadClick: function(event)
		{
			event.preventDefault();

			BX.Landing.UI.Panel.IconPanel
				.getInstance()
				.show()
				.then(function(iconClassName) {
					this.setValue({type: "icon", classList: iconClassName.split(" ")});
				}.bind(this));
		},

		/**
		 * @inheritDoc
		 * @return {boolean}
		 */
		isChanged: function()
		{
			return this.getValue().classList.some(function(className) {
				return this.content.classList.indexOf(className) === -1;
			}, this);
		},

		getValue: function()
		{
			var classList = this.classList;

			if (this.selector)
			{
				var selectorClassname = this.selector.split("@")[0].replace(".", "");
				classList = clone(this.classList).concat([selectorClassname]);
				classList = BX.Landing.Utils.arrayUnique(classList);
			}

			return {
				type: "icon",
				src: "",
				id: -1,
				alt: "",
				classList: classList,
				url: Object.assign({}, this.url.getValue(), {enabled: this.urlCheckbox.checked})
			};
		},

		reset: function()
		{
			this.setValue({
				type: "icon",
				src: "",
				id: -1,
				alt: "",
				classList: [],
				url: ''
			})
		}
	};
})();