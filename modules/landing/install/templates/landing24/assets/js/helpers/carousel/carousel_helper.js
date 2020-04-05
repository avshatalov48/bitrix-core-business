;(function ()
{
	"use strict";

	BX.namespace("BX.Landing.SliderHelper");

	BX.Landing.SliderHelper.ACTION_INIT = 'init';
	BX.Landing.SliderHelper.ACTION_ADD = 'add';
	BX.Landing.SliderHelper.ACTION_REMOVE = 'remove';
	BX.Landing.SliderHelper.ACTION_UPDATE = 'update';

	BX.Landing.SliderHelper.ACTIVE_CLASS = 'slick-initialized';

	/**
	 * Check activity and init slider if needed
	 */
	// todo: add options
	BX.Landing.SliderHelper.init = function (event, action)
	{
		action = action ? action : BX.Landing.SliderHelper.ACTION_INIT;

		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var nodes = event.block.querySelectorAll(relativeSelector);
		if (nodes.length > 0)
		{
			if(action == BX.Landing.SliderHelper.ACTION_UPDATE && BX.Landing.SliderHelper.isSliderActive(nodes))
			{
				BX.Landing.SliderHelper.destroy(event);
			}

			BX.Landing.SliderHelper.initBase(relativeSelector);

			if(action == BX.Landing.SliderHelper.ACTION_UPDATE && BX.Landing.SliderHelper.isSliderActive(nodes))
			{
				BX.Landing.SliderHelper.goToSlide(relativeSelector, event, action);
				BX.Landing.SliderHelper.setSelection(event);
			}
		}
	};

	BX.Landing.SliderHelper.destroy = function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var nodes = event.block.querySelectorAll(relativeSelector);
		if (nodes.length > 0 && BX.Landing.SliderHelper.isSliderActive(nodes))
		{
			// save current slide number
			event.block.slickCurrentSlide = $(relativeSelector).slick("slickCurrentSlide");

			// cant save range object if node will be broken. Save just params

			if(window.getSelection().rangeCount > 0)
			{
				var range = window.getSelection().getRangeAt(0);
				event.block.savedRange = {
					sCont: range.startContainer,
					sOffset: range.startOffset,
					eCont: range.endContainer,
					eOffset: range.endOffset,
				};
			}

			$(relativeSelector).slick('unslick');
		}
	};


	BX.Landing.SliderHelper.isSliderActive = function (nodes)
	{
		var result = false;
		Object.keys(nodes).forEach(function (name)
		{
			if (BX.hasClass(nodes[name], BX.Landing.SliderHelper.ACTIVE_CLASS))
			{
				result = true;
			}
		});

		return result;
	};


	/**
	 * Base slider initialization slider without options
	 */
	BX.Landing.SliderHelper.initBase = function (selector)
	{
		// some classes conflict with slider markup - remove them
		var excludeClasses = $(selector).data('init-classes-exclude');
		if(excludeClasses && BX.type.isArray(excludeClasses))
		{
			excludeClasses.forEach(function (item)
			{
				if(item.selector && item.class)
				{
					$(selector).parent().find(item.selector).removeClass(item.class);
				}
			})
		}

		var config = {accessibility: false};
		// in editor mode infinity scroll will be create cloned slides - we not need them
		if (BX.Landing.getMode() == 'edit')
		{
			config.infinite = false;
		}
		$.HSCore.components.HSCarousel.init(selector, config);
	};


	/**
	 * Hack to reinit attrs, when $.data give old values
	 */
	// dbg - new function, not worked yet
	// BX.Landing.SliderHelper.initAttrs = function (event)
	// {
	// 	var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
	// 	var nodes = event.block.querySelectorAll(relativeSelector);
	// 	if (nodes.length > 0)
	// 	{
	// 		for (var attr in event.data)
	// 		{
	// 			$(relativeSelector).slick('slickSetOption', attr.replace('data-', ''), event.data[attr], true);
	// 		}
	// 		// nodes.forEach(function(i){
	//
	// 		// });
	// 	}
	// };


	/**
	 * For current event find all parents sliders and return relative (from block id) selector.
	 * If exist some parents sliders - return comma separated selectors.
	 * For sliders get maximal selector (use all classes) to maximum unique.
	 *
	 * @param event
	 * @param carouselClass
	 * @returns {*|string}
	 */
	BX.Landing.SliderHelper.makeCarouselRelativeSelector = function (event, carouselClass)
	{
		// cached value
		if (event.block.carouselRelativeSelector)
		{
			return event.block.carouselRelativeSelector;
		}

		carouselClass = carouselClass || "js-carousel";
		var carouselSelectors = [];

		if (event.block)
		{
			// event may fire on nodes or on card or on selector of deleted card.
			var eventNodes = [];
			if (event.card)
			{
				//card may be outside of the slider (when undo). Find same cards by selector
				eventNodes = event.block.querySelectorAll('.' + event.card.className.split(/\s+/).join('.'));
				// eventNodes = [event.card];
			}
			else if (event.node)
			{
				eventNodes = event.node;
			}
			else if (event.data && event.data.selector)	//selector of deleted card
			{
				eventNodes = event.block.querySelectorAll(event.data.selector.split("@")[0]);
			}

			// convert to array
			if (!BX.type.isArray(eventNodes))
			{
				eventNodes = [eventNodes];
			}

			// fore each event node find parent and take his selector
			eventNodes.forEach(function (n)
			{
				var currCarousel = BX.findParent(n, {className: carouselClass}),
					currSelector = '';
				if (currCarousel)
				{
					// remove slick-classes, because them will may be deleted if slider destroyed
					currCarousel.classList.forEach(function (cl)
					{
						if (cl.indexOf('slick-') == -1)
						{
							currSelector += '.' + cl;
						}
					});

					if (carouselSelectors[carouselSelectors.length - 1] != currSelector)
					{
						carouselSelectors.push(currSelector);
					}
				}
			});
		}

		// if nothing find - use DEFAULT selector
		if (carouselSelectors.length == 0)
		{
			carouselSelectors = ['.' + carouselClass];
		}

		// add BLOCK ID to relative
		carouselSelectors.forEach(function (s, i)
		{
			carouselSelectors[i] = event.makeRelativeSelector(s);
		});

		// todo: make correctly multiply slider selector
		// cache selector
		event.block.carouselRelativeSelector = carouselSelectors.join(',');

		return event.block.carouselRelativeSelector;
	};

	BX.Landing.SliderHelper.setSelection = function (event)
	{
		var savedRange = event.block.savedRange;
		if (savedRange)
		{
			var range = document.createRange();
			range.setStart(savedRange.sCont, savedRange.sOffset);
			range.setEnd(savedRange.eCont, savedRange.eOffset);

			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		}
	};

	BX.Landing.SliderHelper.goToSlide = function (selector, event, action)
	{
		if (!action)
		{
			return;
		}

		var currSlideNumber = parseInt(event.block.slickCurrentSlide);

		// for multiple row sliders need use parent container as slide
		var slideContainer = event.card;
		if(
			event.block.querySelector(selector).dataset.rows &&
			parseInt(event.block.querySelector(selector).dataset.rows) > 1
		)
		{
			slideContainer = BX.findParent(event.card, {className: 'slick-slide'});
		}
		if (slideContainer)
		{
			var newSlideNumber = parseInt(slideContainer.dataset.slickIndex);
		}

		switch (action)
		{
			case BX.Landing.SliderHelper.ACTION_ADD :
				BX.Landing.SliderHelper.goToNewSlideAfterAdd(selector, currSlideNumber, newSlideNumber);
				break;

			case BX.Landing.SliderHelper.ACTION_REMOVE:
				BX.Landing.SliderHelper.goToNewSlideAfterRemove(selector, currSlideNumber, newSlideNumber);
				break;

			case BX.Landing.SliderHelper.ACTION_UPDATE:
				BX.Landing.SliderHelper.goToSlideAfterUpdate(selector, currSlideNumber);
				break;

			default:
		}
	};

	/**
	 * Move slider to new slide after add card.
	 *
	 * @param carouselSelector
	 * @param currSlideNumber
	 * @param newSlideNumber
	 */
	BX.Landing.SliderHelper.goToNewSlideAfterAdd = function (carouselSelector, currSlideNumber, newSlideNumber)
	{

		// BX.Landing.SliderHelper.goToNewSlideAfterAdd(relativeSelector, currSlideNumber, newSlideNumber);
		// currSlideNumber = parseInt(currSlideNumber);
		// newSlideNumber = parseInt(newSlideNumber);
		if (BX.type.isNumber(newSlideNumber) && BX.type.isNumber(currSlideNumber))
		{
			// if new slide in visible area - stay on current slide, else - go to next element (one step)
			var slidesToShow = $(carouselSelector).slick('slickGetOption', 'slidesToShow');
			slidesToShow = slidesToShow === true ? 1 : slidesToShow; //slidesToShow can be 'true'
			if ((newSlideNumber - currSlideNumber) >= slidesToShow)
			{
				$(carouselSelector).slick('slickGoTo', currSlideNumber, true);
				$(carouselSelector).slick('slickGoTo', currSlideNumber + 1, false);
			}
			else
			{
				$(carouselSelector).slick('slickGoTo', currSlideNumber, true);
			}
		}
	};


	/**
	 * Move slider to new slide after remove. Stay on current position or move to previously slide, if was removed last element
	 *
	 * @param carouselSelector
	 * @param currSlideNumber
	 * @param newSlideNumber
	 */
	BX.Landing.SliderHelper.goToNewSlideAfterRemove = function (carouselSelector, currSlideNumber, newSlideNumber)
	{
		if (BX.type.isNumber(newSlideNumber) && BX.type.isNumber(currSlideNumber))
		{
			$(carouselSelector).slick('slickGoTo', Math.min(currSlideNumber, newSlideNumber), true);
		}
	}


	BX.Landing.SliderHelper.goToSlideAfterUpdate = function (carouselSelector, currSlideNumber)
	{
		if (BX.type.isNumber(currSlideNumber))
		{
			$(carouselSelector).slick('slickGoTo', currSlideNumber, true);
		}
	}
})();