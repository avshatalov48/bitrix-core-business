;(function ()
{
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_INIT);
	});

	/**
	 * Disable slider before removing block - to correctly work in history
	 */
	BX.addCustomEvent("BX.Landing.Block:remove", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	BX.addCustomEvent("BX.Landing.Block:beforeApplyContentChanges", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	BX.addCustomEvent("BX.Landing.Block:Node:update", BX.debounce(function (event)
	{
		if(!BX.Landing.SliderHelper.isEditorEnable())
		{
			// todo: lazy reinit if now editor enable?
			BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_UPDATE);
		}
	}, 300));

	/**
	 * Destroy slider, clone DOM-element, save content in DB and reinit slider later
	 */
	BX.addCustomEvent("BX.Landing.Block:Card:beforeAdd", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	/**
	 * Reinit slider after add new element in DOM
	 */
	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event)
	{
		BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_ADD);
	});

	/**
	 * Destroy slider, clone DOM-element, save content in DB and reinit slider later
	 */
	BX.addCustomEvent("BX.Landing.Block:Cards:beforeUpdate", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	/**
	 * Reinit slider after add new element in DOM
	 */
	BX.addCustomEvent("BX.Landing.Block:Cards:update", function (event)
	{
		BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_UPDATE);
	});

	/**
	 * Destroy slider, clone DOM-element, save content in DB and reinit slider later
	 */
	BX.addCustomEvent("BX.Landing.Block:Card:beforeRemove", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	/**
	 * Reinit slider after add new element in DOM
	 */
	BX.addCustomEvent("BX.Landing.Block:Card:remove", function (event)
	{
		BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_REMOVE_SLIDE);
	});

	/**
	 * Rebuild slider after style change.
	 * Need if style may change width or height of cards and Slick will be incorrectly slide them
	 */
	BX.addCustomEvent("BX.Landing.Block:updateStyle", BX.debounce(function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		var needUpdate = false;
		sliders.forEach(function (sliderNode)
		{
			// Now need rebuild only verticals sliders, i think.
			if ($(sliderNode).slick('slickGetOption', 'vertical'))
			{
				needUpdate = true;
			}
		});
		if (needUpdate)
		{
			BX.Landing.SliderHelper.init(event, BX.Landing.SliderHelper.ACTION_UPDATE);
		}
	}, 1000));

	/**
	 * Check if current editor in slider-block. If true - stor ALL sliders
	 */
	BX.addCustomEvent("BX.Landing.Editor:enable", function (target)
	{
		var parentBlock = BX.findParent(target, {class:'block-wrapper'});
		if(parentBlock)
		{
			if(parentBlock.querySelector('.' + BX.Landing.SliderHelper.CAROUSEL_CLASS))
			{
				BX.Landing.SliderHelper.setEditorEnable(true);
				$(".js-carousel").slick('slickPause');
			}
		}
	});

	/**
	 * Start ALL sliders. Always
	 */
	BX.addCustomEvent("BX.Landing.Editor:disable", function ()
	{
		BX.Landing.SliderHelper.setEditorEnable(false);
		$(".js-carousel").slick('slickPlay');
	});

	/**
	 * Set correct slider width after lazyload image
	 */
	BX.addCustomEvent("BX.Landing.Lazyload:loadImage", function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		sliders.forEach(function (sliderNode)
		{
			var slickObj = $(sliderNode).slick('getSlick');
			if (slickObj)
			{
				slickObj.setPosition();
			}
		});
	});
})();