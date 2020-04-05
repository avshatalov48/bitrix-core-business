;(function() {
	"use strict";

	BX.namespace("BX.UI.ImageEditorProvider");

	var LibraryProvider = PhotoEditorSDK.UI.DesktopUI.Library.Provider;
	var LibraryCategory = PhotoEditorSDK.UI.DesktopUI.Library.Category;
	var LibraryImage = PhotoEditorSDK.UI.DesktopUI.Library.Image;


	/**
	 * Example library data provider of BX.UI.ImageEditor
	 *
	 * @extends {LibraryProvider}
	 * @constructor
	 */
	BX.UI.ImageEditorProvider.Unsplash = function()
	{
		LibraryProvider.apply(this, arguments);
		this.data = null;
	};

	BX.UI.ImageEditorProvider.Unsplash.prototype = {
		constructor: BX.UI.ImageEditorProvider.Unsplash,
		__proto__: LibraryProvider.prototype,

		_loadData: function()
		{
			if (this.data)
			{
				return Promise.resolve(this.data)
			}

			return this._loadJSON("http://d3czpaw5gb5xgh.cloudfront.net/v4/unsplash.json")
				.then(function(response) {
					this.data = response;
					return response;
				}.bind(this));
		},


		/**
		 * Implements abstract method from LibraryProvider
		 */
		getCategories: function()
		{
			return this._loadData()
				.then(function(data) {
					return data.categories.map(function(item) {
						return new LibraryCategory({
							name: item.name,
							coverImage: item.coverImage
						});
					});
				})
		},


		/**
		 * Implements abstract method from LibraryProvider
		 */
		searchImages: function(query)
		{
			return this._loadData()
				.then(function(data) {
					return data.images.filter(function(image) {
						var words = query.split(/\s+/);

						for (var i = 0; i < words.length; i++)
						{
							var word = words[i];
							var regexp = new RegExp(word, 'i');

							if (!regexp.test(image.title))
							{
								return false
							}
						}

						return true;
					}).map(function(imageData) {
						return new LibraryImage(imageData);
					});
				});
		}
	}
})();