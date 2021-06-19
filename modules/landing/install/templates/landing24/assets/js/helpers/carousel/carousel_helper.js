;(function ()
{
	"use strict";

	BX.namespace("BX.Landing.SliderHelper");

	BX.Landing.SliderHelper.ACTION_INIT = 'init';
	BX.Landing.SliderHelper.ACTION_ADD = 'add';
	BX.Landing.SliderHelper.ACTION_REMOVE_SLIDE = 'remove_slide';
	BX.Landing.SliderHelper.ACTION_UPDATE = 'update';

	BX.Landing.SliderHelper.CAROUSEL_CLASS = 'js-carousel';
	BX.Landing.SliderHelper.ACTIVE_CLASS = 'slick-initialized';

	BX.Landing.SliderHelper.editorEnableFlag = false;

	/**
	 * Check activity and init slider if needed
	 */
	// todo: add options
	BX.Landing.SliderHelper.init = function (event, action)
	{
		action = action ? action : BX.Landing.SliderHelper.ACTION_INIT;

		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		sliders.forEach(function (sliderNode)
		{
			if (
				BX.Landing.SliderHelper.isSliderActive(sliderNode)
				&& action === BX.Landing.SliderHelper.ACTION_UPDATE
			)
			{
				BX.Landing.SliderHelper.destroy(event);
			}

			BX.Landing.SliderHelper.initBase(sliderNode);

			if (
				action === BX.Landing.SliderHelper.ACTION_UPDATE
				|| action === BX.Landing.SliderHelper.ACTION_ADD
				|| action === BX.Landing.SliderHelper.ACTION_REMOVE_SLIDE
			)
			{
				BX.Landing.SliderHelper.goToSlide(sliderNode, event, action);
				BX.Landing.SliderHelper.setSelection(event);
			}
		});
	};

	BX.Landing.SliderHelper.destroy = function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		sliders.forEach(function (sliderNode)
		{
			if (BX.Landing.SliderHelper.isSliderActive(sliderNode))
			{
				// save current slide number
				sliderNode.slickCurrentSlide = $(sliderNode).slick("slickCurrentSlide");
				$(sliderNode).slick('unslick');
			}
		});

		BX.Landing.SliderHelper.saveSelection(event);
	};

	/**
	 * Base slider initialization slider without options
	 */
	BX.Landing.SliderHelper.initBase = function (sliderNode)
	{
		// some classes conflict with slider markup - remove them
		var excludeClasses = $(sliderNode).data('init-classes-exclude');
		if (excludeClasses && BX.type.isArray(excludeClasses))
		{
			excludeClasses.forEach(function (excludeClass)
			{
				if (excludeClass.selector && excludeClass.class)
				{
					$(sliderNode).parent().find(excludeClass.selector).removeClass(excludeClass.class);
				}
			})
		}

		var config = {accessibility: false};
		// in editor mode infinity scroll will be create cloned slides - we not need them
		if (BX.Landing.getMode() === 'edit')
		{
			config.infinite = false;
		}
		$.HSCore.components.HSCarousel.init(sliderNode, config);
	};

	BX.Landing.SliderHelper.destroy = function (event)
	{
		var relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		var sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		sliders.forEach(function (sliderNode)
		{
			if (BX.Landing.SliderHelper.isSliderActive(sliderNode))
			{
				// save current slide number
				sliderNode.slickCurrentSlide = $(sliderNode).slick("slickCurrentSlide");
				$(sliderNode).slick('unslick');
			}
		});

		BX.Landing.SliderHelper.saveSelection(event);
	};

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
		carouselClass = carouselClass || BX.Landing.SliderHelper.CAROUSEL_CLASS;
		var carouselSelectors = [];

		if (event.block)
		{
			// event may fire on nodes or on card or on selector of deleted card.
			var eventNodes = [];
			if (event.card)
			{
				//card may be outside of the slider (when undo). Find same cards by selector
				eventNodes = event.block.querySelectorAll('.' + event.card.className.split(/\s+/).join('.'));
			}
			else if (event.node)
			{
				eventNodes = [event.node];
			}
			else if (event.data && event.data.selector)	//selector of deleted card
			{
				eventNodes = event.block.querySelectorAll(event.data.selector.split("@")[0]);
			}

			// fore each event node find parent and take his selector
			eventNodes.forEach(function (node)
			{
				var currCarousel = BX.findParent(node, {className: carouselClass}),
					currSelector = '';
				if (currCarousel)
				{
					// remove slick-classes, because them will may be deleted if slider destroyed
					currCarousel.classList.forEach(function (cl)
					{
						if (cl.indexOf('slick-') === -1)
						{
							currSelector += '.' + cl;
						}
					});

					if (carouselSelectors[carouselSelectors.length - 1] !== currSelector)
					{
						carouselSelectors.push(currSelector);
					}
				}
			});
		}

		// if nothing find - use DEFAULT selector
		if (carouselSelectors.length === 0)
		{
			carouselSelectors = ['.' + carouselClass];
		}

		// add BLOCK ID to relative
		carouselSelectors.forEach(function (s, i)
		{
			carouselSelectors[i] = event.makeRelativeSelector(s);
		});

		return carouselSelectors.join(',');
	};

	BX.Landing.SliderHelper.isSliderActive = function (nodes)
	{
		if (!BX.Type.isArrayLike(nodes))
		{
			nodes = [nodes];
		}
		var result = false;
		nodes.forEach(function (node)
		{
			if (BX.hasClass(node, BX.Landing.SliderHelper.ACTIVE_CLASS))
			{
				result = true;
			}
		});

		return result;
	};

	BX.Landing.SliderHelper.saveSelection = function (event)
	{
		// cant save range object if node will be broken. Save just params
		if (window.getSelection().rangeCount > 0)
		{
			var range = window.getSelection().getRangeAt(0);
			event.block.savedRange = {
				sCont: range.startContainer,
				sOffset: range.startOffset,
				eCont: range.endContainer,
				eOffset: range.endOffset
			};
		}
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

	BX.Landing.SliderHelper.goToSlide = function (sliderNode, event, action)
	{
		if (!action)
		{
			return;
		}

		var currSlideNumber = parseInt(sliderNode.slickCurrentSlide);

		// for multiple row sliders need use parent container as slide
		var slideContainer = action === BX.Landing.SliderHelper.ACTION_REMOVE_SLIDE
			? BX.Landing.SliderHelper.findNewSlideWhenRemove(event)
			: event.card;
		if (
			sliderNode.dataset.rows
			&& parseInt(sliderNode.dataset.rows) > 1
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
				BX.Landing.SliderHelper.goToSlideAfterAdd(sliderNode, currSlideNumber, newSlideNumber);
				break;

			case BX.Landing.SliderHelper.ACTION_REMOVE_SLIDE:
				BX.Landing.SliderHelper.goToSlideAfterRemove(sliderNode, currSlideNumber, newSlideNumber);
				break;

			case BX.Landing.SliderHelper.ACTION_UPDATE:
				BX.Landing.SliderHelper.goToSlideAfterUpdate(sliderNode, currSlideNumber);
				break;

			default:
		}
	};

	BX.Landing.SliderHelper.findNewSlideWhenRemove = function (event)
	{
		var selector = event.data.selector,
			selectorName = selector.split("@")[0],
			selectorIndex = parseInt(selector.split("@")[1]),
			slides = event.block.querySelectorAll(selectorName);
		// if deleted not a last card - new card will be have same index
		// if not - find previously card
		var slideNew = slides[selectorIndex];
		if (!slideNew)
		{
			slideNew = (!BX.type.isNumber(selectorIndex) || selectorIndex === 0)
				? null
				: slides[selectorIndex - 1]
			;
			// if new card is null it means, that all cards was be deleted
		}

		return slideNew;
	}

	/**
	 * Move slider to new slide after add card.
	 *
	 * @param carouselNode
	 * @param currSlideNumber
	 * @param newSlideNumber
	 */
	BX.Landing.SliderHelper.goToSlideAfterAdd = function (carouselNode, currSlideNumber, newSlideNumber)
	{
		if (BX.type.isNumber(newSlideNumber) && BX.type.isNumber(currSlideNumber))
		{
			// if new slide in visible area - stay on current slide, else - go to next element (one step)
			var slidesToShow = $(carouselNode).slick('slickGetOption', 'slidesToShow');
			slidesToShow = slidesToShow === true ? 1 : slidesToShow; //slidesToShow can be 'true'
			if ((newSlideNumber - currSlideNumber) >= slidesToShow)
			{
				$(carouselNode).slick('slickGoTo', currSlideNumber, true);
				$(carouselNode).slick('slickGoTo', currSlideNumber + 1, false);
			}
			else
			{
				$(carouselNode).slick('slickGoTo', currSlideNumber, true);
			}
		}
	};

	/**
	 * Move slider to new slide after remove. Stay on current position or move to previously slide, if was removed last element
	 *
	 * @param carouselNode
	 * @param currSlideNumber
	 * @param newSlideNumber
	 */
	BX.Landing.SliderHelper.goToSlideAfterRemove = function (carouselNode, currSlideNumber, newSlideNumber)
	{
		if (BX.type.isNumber(newSlideNumber) && BX.type.isNumber(currSlideNumber))
		{
			$(carouselNode).slick('slickGoTo', Math.min(currSlideNumber, newSlideNumber), true);
		}
	}

	/**
	 * Move slider to new slide after update. Just set previously position
	 *
	 * @param carouselNode
	 * @param currSlideNumber
	 */
	BX.Landing.SliderHelper.goToSlideAfterUpdate = function (carouselNode, currSlideNumber)
	{
		if (BX.type.isNumber(currSlideNumber))
		{
			$(carouselNode).slick('slickGoTo', currSlideNumber, true);
		}
	}

	BX.Landing.SliderHelper.setEditorEnable = function(value)
	{
		BX.Landing.SliderHelper.editorEnableFlag = !!value;
	}

	BX.Landing.SliderHelper.isEditorEnable = function()
	{
		return !!BX.Landing.SliderHelper.editorEnableFlag;
	}
})();