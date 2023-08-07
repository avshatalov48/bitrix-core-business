;(function ()
{
	"use strict";

	BX.namespace("BX.Landing");

	BX.Landing.CardGalleryCollection = function ()
	{
		this.galleries = {};
	};

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
		if (BX.Landing.CardGallery.isGalleryBlock(block))
		{
			var galleryCollection = this.getInstance();
			galleryCollection.add(block);
		}
	};

	/**
	 * Set block to not activate fancybox
	 * @param {HTMLElement} block
	 */
	BX.Landing.CardGalleryCollection.disableBlock = function (block)
	{
		if (BX.Landing.CardGallery.isGalleryBlock(block))
		{
			var gallery = new BX.Landing.CardGallery(block);
			gallery.disable();
		}
	};

	BX.Landing.CardGalleryCollection.prototype = {
		/**
		 * Add gallery block to collection
		 * @param {HTMLElement} block
		 * @returns {boolean}
		 */
		add: function (block)
		{
			var gallery = new BX.Landing.CardGallery(block);
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
	/**
	 *
	 * @param {HTMLElement} block
	 * @constructor
	 */
	BX.Landing.CardGallery = function (block)
	{
		this.uniqId = block.id;
		this.gallery = BX.Landing.CardGallery.getGalleryNode(block);
	}

	/**
	 * Check if block has gallery
	 * @param {HTMLElement} block
	 */
	BX.Landing.CardGallery.isGalleryBlock = function (block)
	{
		return this.getGalleryNode(block) !== null;
	};

	/**
	 * Find gallery node in block
	 * @param {HTMLElement}block
	 * @returns {HTMLElement|null}
	 */
	BX.Landing.CardGallery.getGalleryNode = function (block)
	{
		return block.querySelector(this.GALLERY_SELECTOR);
	};

	BX.Landing.CardGallery.GALLERY_SELECTOR = ".js-gallery-cards";
	BX.Landing.CardGallery.IMAGES_SELECTOR = '[data-fancybox]';
	BX.Landing.CardGallery.DATA_FANCYBOX = 'fancybox';
	BX.Landing.CardGallery.DATA_FANCYBOX_INITIED = 'fancyboxInitied';
	BX.Landing.CardGallery.DATA_FANCYBOX_TITLE = 'caption';
	BX.Landing.CardGallery.DATA_LINK_CLASSES = 'linkClasses';
	BX.Landing.CardGallery.DATA_LAZY_IMAGE = 'lazyImg';
	BX.Landing.CardGallery.CAROUSEL_CLONED_CLASSES = 'slick-cloned';

	BX.Landing.CardGallery.prototype = {
		getId: function()
		{
			return this.uniqId;
		},

		disable: function()
		{
			var images = [].slice.call(this.gallery.querySelectorAll(BX.Landing.CardGallery.IMAGES_SELECTOR));
			images.forEach(function (image)
			{
				delete image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX];
			}, this);
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
			var src;
			var srcset;
			if(image.dataset[BX.Landing.CardGallery.DATA_LAZY_IMAGE] === 'Y')
			{
				src = image.dataset.src || image.src;
				srcset = image.dataset.srcset || image.srcset;
			}
			else
			{
				src = image.src;
				srcset = image.srcset
			}

			if(!src)
			{
				return;
			}

			// add outer link to image for fancybox activity
			var parent = image.parentNode;
			var outerLink = BX.create('a', {
				attrs: {
					href: src,
					class: image.dataset[BX.Landing.CardGallery.DATA_LINK_CLASSES] || ''
				},
				children: [].slice.call(parent.children)
			});
			if(srcset)
			{
				srcset = src + ' 1x,' + srcset;
				outerLink.dataset.options = '{"image":{"srcset": "'+ srcset + '"}}';
			}
			BX.adjust(parent, {'children': [outerLink]});

			// set unique fancybox id
			outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX] =
				image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX] + '_' + this.uniqId;
			outerLink.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_TITLE] =
				BX.Text.encode(image.getAttribute('alt')) || '';

			// remove data-attribute to disable gallery on <img>
			delete image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX];
			image.dataset[BX.Landing.CardGallery.DATA_FANCYBOX_INITIED] = "Y";
		}
	}

	/****************
	 * Event handlers
	 ****************/
	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		if(BX.Landing.getMode() === "edit")
		{
			BX.Landing.CardGalleryCollection.disableBlock(event.block);
		}
		else
		{
			BX.Landing.CardGalleryCollection.initBlock(event.block);
		}
	});
})();