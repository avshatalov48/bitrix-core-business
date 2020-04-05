;(function ()
{
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		var gallery = event.block.querySelector(event.makeRelativeSelector(".js-gallery-cards"));

		if (gallery !== null)
		{
			window["landingGalleresCards" + event.block.id] = new LandingGalleryCards(gallery, event.block);
			window["landingGalleresCards" + event.block.id].initImages();
		}
	});


	BX.addCustomEvent("BX.Landing.Block:Node:update", function (event)
	{
		if (typeof(window["landingGalleresCards" + event.block.id]) != 'undefined')
		{
			var gallery = event.block.querySelector(event.makeRelativeSelector(".js-gallery-cards"));
			if (gallery !== null && gallery.contains(event.node))
			{
				window["landingGalleresCards" + event.block.id].reinitImage(event.node);
			}
		}
	});


	var LandingGalleryCards = function (gallery, block)
	{
		this.block = block;
		this.gallery = gallery;
		this.uniqId = Math.random().toString(16).substr(2, 8);
	};


	LandingGalleryCards.prototype = {
		dataFancybox: 'data-fancybox',
		dataFancyboxInitied: 'data-fancybox-initied',
		dataFancyboxTitle: 'data-caption',

		// create A around the image, need to fancybox popup
		addOuterLink: function (image)
		{
			var src = image.getAttribute('src');
			// BX.Landing.getBackgroundUrl();
			if (src != null)
			{
				var parent = BX.findParent(image);
				// find all children, not only image!
				var childs = BX.findChild(parent,{},false,true);
				var childs = Object.keys(childs).map(function(key) {
					return childs[key];
				});


				var aParams = {
					'attrs': {
						'href': src
					},
					'children': childs
				};
				aParams.attrs[this.dataFancybox] = BX.data(image, this.dataFancybox.replace('data-', '')) + '_' + this.uniqId;

				// add title to link
				var alt = image.getAttribute('alt');
				if (alt != null)
				{
					aParams.attrs[this.dataFancyboxTitle] = alt;
				}

				// remove data-attribute to disable gallery on <img>
				var imageAdjutsParams = {'attrs': {}};
				imageAdjutsParams.attrs[this.dataFancybox] = "";
				imageAdjutsParams.attrs[this.dataFancyboxInitied] = "Y";
				BX.adjust(image, imageAdjutsParams);

				// add img with outer link instead
				BX.adjust(parent, {'children': [BX.create('a', aParams)]});
			}
		},

		initImages: function ()
		{
			var images = BX.findChild(this.gallery, {'attribute': this.dataFancybox}, true, true);
			images.forEach(BX.delegate(function (image)
			{
				if(!this.isImageInitied(image))
				{
					this.addOuterLink(image);
				}
			}, this));
		},

		reinitImage: function (image)
		{
			// only if card has gallery image and image has outer link
			if (this.isImageInitied(image))
			{
				var src = image.getAttribute('src');
				if (src != null)
				{
					// find outer link
					var outer = BX.findParent(image, {
						'tag': 'a',
						'attribute': [this.dataFancybox]
					});

					if (outer !== null)
					{
						var outerParams = {'attrs': {'href': src}};

						// add title to link
						var alt = image.getAttribute('alt');
						if (alt != null)
						{
							outerParams.attrs[this.dataFancyboxTitle] = alt;
						}

						BX.adjust(outer, outerParams);
					}
				}
			}
		},

		isImageInitied: function (image)
		{
			return (
				(
					image.tagName == "IMG" &&
					BX.data(image, this.dataFancyboxInitied.replace('data-', '')) == "Y"
				)
				||
				(
					image.tagName == "A" && image.firstChild != null && this.isImageInitied(image.firstChild)
				)
			);
		}
	};

})();