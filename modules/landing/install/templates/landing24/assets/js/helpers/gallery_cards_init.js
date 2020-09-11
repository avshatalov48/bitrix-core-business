;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	BX.Landing.CardGalleryCollection = function ()
	{
		this.galleries = {};
	};

	BX.Landing.CardGalleryCollection.GALLERY_SELECTOR = ".js-gallery-cards";

	/**
	 * Singleton pattern
	 * @returns {BX.Landing.CardGalleryCollection}
	 */
	BX.Landing.CardGalleryCollection.getInstance = function ()
	{
		return (
			BX.Landing.CardGalleryCollection.instance ||
			(BX.Landing.CardGalleryCollection.instance = new BX.Landing.CardGalleryCollection())
		);
	};

	/**
	 * First init
	 * @param {HTMLElement} block
	 */
	BX.Landing.CardGalleryCollection.initBlock = function (block)
	{
		if (this.isGalleryBlock(block))
		{
			var galleryCollection = this.getInstance();
			galleryCollection.add(block);
		}
	};

	/**
	 *
	 * @param {HTMLElement} block
	 * @param {HTMLElement} imgNode
	 */
	BX.Landing.CardGalleryCollection.reinitImg = function (block, imgNode)
	{
		if (this.isGalleryBlock(block))
		{
			var galleryCollection = this.getInstance();
			var gallery = galleryCollection.findGalleryByBlock(block);
			if(gallery)
			{
				gallery.reinitImage(imgNode);
			}
		}
	};

	/**
	 * Check if block has gallery
	 * @param {HTMLElement} block
	 */
	BX.Landing.CardGalleryCollection.isGalleryBlock = function (block)
	{
		var gallery = this.getGalleryNode(block);
		return gallery !== null;
	};

	/**
	 * Find gallery node in block
	 * @param {HTMLElement}block
	 * @returns {HTMLElement|null}
	 */
	BX.Landing.CardGalleryCollection.getGalleryNode = function (block)
	{
		return block.querySelector(this.GALLERY_SELECTOR);
	};

	BX.Landing.CardGalleryCollection.prototype = {
		/**
		 * Add gallery block to collection
		 * @param {HTMLElement} block
		 * @returns {boolean}
		 */
		add: function (block)
		{
			var gallery = new BX.Landing.CardGallery(
				block,
				BX.Landing.CardGalleryCollection.getGalleryNode(block)
			);
			gallery.initImages();
			this.galleries[gallery.getId()] = gallery;
		},

		findGalleryByBlock: function (block)
		{
			return this.galleries[block.id] || null;
		}
	}

	/************
	 Entry object
	 ************/
	BX.Landing.CardGallery = function (block, galleryNode)
	{
		this.gallery = galleryNode;
		this.uniqId = block.id;
	}

	BX.Landing.CardGallery.IMAGES_SELECTOR = '[data-fancybox]';
	BX.Landing.CardGallery.DATA_FANCYBOX_ID = 'fancybox';
	BX.Landing.CardGallery.DATA_FANCYBOX_INITIED = 'fancyboxInitied';
	BX.Landing.CardGallery.DATA_FANCYBOX_TITLE = 'caption';
	BX.Landing.CardGallery.DATA_LINK_CLASSES = 'linkClasses';
	BX.Landing.CardGallery.CAROUSEL_CLONED_CLASSES = 'slick-cloned';

	BX.Landing.CardGallery.prototype = {
		getId: function()
		{
			return this.uniqId;
		},

		initImages: function ()
		{
			var images = [].slice.call(this.gallery.querySelectorAll(BX.Landing.CardGallery.IMAGES_SELECTOR));
			images.forEach(function (image)
			{
				// fix double images trouble in slick carousel cloned sliders
				if (BX.findParent(image, {class: BX.Landing.CardGallery.CAROUSEL_CLONED_CLASSES}))
				{
					return;
				}

				if (!this.isImageInit(image))
				{
					this.addOuterLink(image);
				}
			}, this);
		},


		/**
		 * Check is image initied by image or outer link node
		 * @param image
		 * @returns {boolean|*}
		 */
		isImageInit: function (image)
		{
			return (
				(
					image.tagName === "IMG"
					&& image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_INITIED] === "Y"
				)
				||
				(
					image.tagName === "A"
					&& image.firstChild !== null
					&& this.isImageInit(image.firstChild)
				)
			);
		},

		// create A around the image, need to fancybox popup
		addOuterLink: function (image)
		{
			var src = image.getAttribute('src');
			if (src != null)
			{
				// add outer link to image for fancybox activity
				var parent = image.parentNode;
				var outerLink = BX.create('a', {
					attrs: {
						href: src,
						class: image.dataset[BX.Landing.CardGallery.DATA_LINK_CLASSES] || ''
					},
					children: [].slice.call(parent.children)
				});
				BX.adjust(parent, {'children': [outerLink]});

				// set unique fancybox id
				outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_ID] =
					image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_ID] + '_' + this.uniqId;
				outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_TITLE] =
					BX.Text.encode(image.getAttribute('alt')) || '';

				// remove data-attribute to disable gallery on <img>
				delete image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_ID];
				image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_INITIED] = "Y";

			}
		},

		reinitImage: function (image)
		{
			// only if card has gallery image and image has outer link
			if (this.isImageInit(image))
			{
				var src = image.getAttribute('src');
				if (src !== null)
				{
					// find outer link
					var outerLink = image.parentNode;
					if (
						outerLink.tagName === 'A'
						&& outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_ID]
					)
					{
						outerLink.href = src;
						outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_TITLE] =
							BX.Text.encode(image.getAttribute('alt')) || '';
					}
				}
			}
			// todo: else - init?
		}
	}


	/****************
	 * Event handlers
	 ****************/
	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		if(BX.Landing.getMode() === "edit")
		{
			return;
		}

		BX.Landing.CardGalleryCollection.initBlock(event.block);
	});

	BX.addCustomEvent("BX.Landing.Lazyload:loadImage", function (event)
	{
		if(BX.Landing.getMode() === "edit")
		{
			return;
		}

		BX.Landing.CardGalleryCollection.reinitImg(event.block, event.node);
		// todo: maybe not need in editor?
	});

})();