;(function ()
{
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
	{
		BX.Landing.SliderHelper.init(event, 'init');
	});

	BX.addCustomEvent("BX.Landing.Block:beforeApplyContentChanges", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	BX.addCustomEvent("BX.Landing.Block:Node:update", function (event)
	{
		BX.Landing.SliderHelper.init(event, 'update');
	});

	// we can't add card in slider - need destroy slider, clone DOM-element,
	// save content in DB and reinit slider later
	BX.addCustomEvent("BX.Landing.Block:Card:beforeAdd", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});


	// reinit slider after add new element in DOM
	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event)
	{
		BX.Landing.SliderHelper.init(event, 'add');
	});


	// NEW CARDS
	// we can't add card in slider - need destroy slider, clone DOM-element,
	// save content in DB and reinit slider later
	BX.addCustomEvent("BX.Landing.Block:Cards:beforeUpdate", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});

	// NEW CARDS
	// reinit slider after add new element in DOM
	BX.addCustomEvent("BX.Landing.Block:Cards:update", function (event)
	{
		BX.Landing.SliderHelper.init(event);
	});


	// we can't remove card in slider - need destroy slider, remove DOM-element,
	// save content in DB and reinit slider later
	BX.addCustomEvent("BX.Landing.Block:Card:beforeRemove", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});


	// reinit slider after remove new element in DOM
	BX.addCustomEvent("BX.Landing.Block:Card:remove", function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		if ($(relativeSelector).length > 0)
		{
			var selector = event.data.selector,
				selectorName = selector.split("@")[0],
				selectorIndex = parseInt(selector.split("@")[1]),
				cards = event.block.querySelectorAll(selectorName);
			// if deleted not a last card - new card will be have same index
			// if not - find previously card
			var cardNew = cards[selectorIndex];
			if (!cardNew)
			{
				if (!BX.type.isNumber(selectorIndex) || selectorIndex === 0)
					cardNew = null;
				else
					cardNew = cards[selectorIndex - 1];
			}
			// if new card == null it means, that all cards was be deleted - do nothing
			if (cardNew)
			{
				event.card = cardNew;
				BX.Landing.SliderHelper.initBase(relativeSelector);
				BX.Landing.SliderHelper.goToSlide(event, 'remove');
			}
		}
	});


	// disable slider before removing block - to correctly work in history
	BX.addCustomEvent("BX.Landing.Block:remove", function (event)
	{
		BX.Landing.SliderHelper.destroy(event);
	});


	// stop ALL SLIDERS if editing
	BX.addCustomEvent("BX.Landing.Editor:enable", function (event)
	{
		try
		{
			$(".js-carousel").slick('slickPause');
		}
		catch (e)
		{
		}
	});


	// play ALL SLIDERS if editing end
	BX.addCustomEvent("BX.Landing.Editor:disable", function (event)
	{
		try
		{
			$(".js-carousel").slick('slickPlay');
		}
		catch (e)
		{
		}
	});
})();