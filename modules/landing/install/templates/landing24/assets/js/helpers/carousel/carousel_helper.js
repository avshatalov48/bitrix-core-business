;(function ($)
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

		const relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		const sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
		sliders.forEach(function (sliderNode)
		{
			BX.Landing.SliderHelper.applySettings(sliderNode, event.data);
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
		const relativeSelector = BX.Landing.SliderHelper.makeCarouselRelativeSelector(event);
		const sliders = [].slice.call(event.block.querySelectorAll(relativeSelector));
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
		const excludeClasses = $(sliderNode).data('init-classes-exclude');
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

		let config = {accessibility: false};
		// in editor mode infinity scroll will be create cloned slides - we not need them
		if (BX.Landing.getMode() === 'edit')
		{
			config.infinite = false;
		}
		$.HSCore.components.HSCarousel.init(sliderNode, config);
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
		let carouselSelectors = [];

		if (event.block)
		{
			// event may fire on nodes or on card or on selector of deleted card.
			let eventNodes = [];
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
				let currCarousel = BX.findParent(node, {className: carouselClass}),
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
		let result = false;
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
		let savedRange = event.block.savedRange;
		if (savedRange)
		{
			let range = document.createRange();
			range.setStart(savedRange.sCont, savedRange.sOffset);
			range.setEnd(savedRange.eCont, savedRange.eOffset);

			let sel = window.getSelection();
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

	BX.Landing.SliderHelper.applySettings = function(sliderNode) {
		BX.Landing.SliderHelper.setAutoplay(sliderNode);
		BX.Landing.SliderHelper.setAutoplaySpeed(sliderNode);
		BX.Landing.SliderHelper.setPauseOnHover(sliderNode);
		BX.Landing.SliderHelper.setAnimation(sliderNode);
		BX.Landing.SliderHelper.setAmountSlidesShow(sliderNode);
		BX.Landing.SliderHelper.setArrows(sliderNode);
		BX.Landing.SliderHelper.setDotsVisible(sliderNode);
	}

	//autoplay settings
	BX.Landing.SliderHelper.setAutoplay = function(sliderNode) {
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-autoplay') === 0)
		{
			BX.Dom.attr(sliderNode, 'data-autoplay', false);
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-autoplay') === 1)
		{
			BX.Dom.attr(sliderNode, 'data-autoplay', true);
		}
	}

	//autoplay speed settings
	BX.Landing.SliderHelper.setAutoplaySpeed = function(sliderNode) {
		if (BX.Type.isInteger(BX.Dom.attr(sliderNode.parentNode, 'data-slider-autoplay-speed')))
		{
			BX.Dom.attr(sliderNode, 'data-speed', BX.Dom.attr(sliderNode.parentNode, 'data-slider-autoplay-speed'));
		}
	}

	//dots setting
	BX.Landing.SliderHelper.setDotsVisible = function(sliderNode) {
		var dataSlickAttr = BX.Dom.attr(sliderNode, 'data-slick');
		if (!BX.Type.isObject(dataSlickAttr))
		{
			dataSlickAttr = {};
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-dots') === 0)
		{
			dataSlickAttr.dots = false;
			BX.Dom.attr(sliderNode, 'data-slick', dataSlickAttr);
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-dots') === 1)
		{
			dataSlickAttr.dots = true;
			BX.Dom.attr(sliderNode, 'data-slick', dataSlickAttr);
		}
	}

	//pause on hover setting
	BX.Landing.SliderHelper.setPauseOnHover = function(sliderNode) {
		if (BX.Type.isBoolean(BX.Dom.attr(sliderNode.parentNode, 'data-slider-pause-hover')))
		{
			BX.Dom.attr(sliderNode, 'data-pause-hover', BX.Dom.attr(sliderNode.parentNode, 'data-slider-pause-hover'));
		}
	}

	//amount slides show setting
	BX.Landing.SliderHelper.setAmountSlidesShow = function(sliderNode) {
		if (BX.Type.isInteger(BX.Dom.attr(sliderNode.parentNode, 'data-slider-slides-show')))
		{
			BX.Dom.attr(sliderNode, 'data-slides-show', BX.Dom.attr(sliderNode.parentNode, 'data-slider-slides-show'));
		}
	}

	//arrows visible setting
	BX.Landing.SliderHelper.setArrows = function(sliderNode) {
		var dataSlickAttr = BX.Dom.attr(sliderNode, 'data-slick');
		if (!BX.Type.isObject(dataSlickAttr))
		{
			dataSlickAttr = {};
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 0)
		{
			dataSlickAttr.arrows = false;
			BX.Dom.attr(sliderNode, 'data-slick', dataSlickAttr);
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') > 0)
		{
			dataSlickAttr.arrows = true;
			BX.Dom.attr(sliderNode, 'data-slick', dataSlickAttr);
			var setClasses = [
				//set classes, use now in slider
				'g-color-white',
				'g-color-gray',
				'g-color-gray-light-v1',
				'g-color-primary--hover',
				'g-color-white--hover',
				'g-bg-primary',
				'g-bg-gray-light-v2',
				'g-bg-gray-light-v5',
				'g-bg-primary--hover',
				'g-rounded-50x',
				'g-opacity-0_8--hover',
				//set old classes, not use now in slider
				'g-bg-gray-light-v3',
			];
			var newArrowClasses = BX.Dom.attr(sliderNode, 'data-arrows-classes');
			var newArrowClassesArr = newArrowClasses.split(' ');
			setClasses.forEach(function(setClass) {
				if (newArrowClassesArr.includes(setClass))
				{
					var index = newArrowClassesArr.indexOf(setClass);
					newArrowClassesArr.splice(index, 1);
				}
			})

			var addClasses = [];
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 1)
			{
				addClasses = ['g-color-gray', 'g-color-white--hover', 'g-bg-gray-light-v5', 'g-bg-primary--hover'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 2)
			{
				addClasses = ['g-color-gray', 'g-color-white--hover', 'g-bg-gray-light-v5', 'g-bg-primary--hover', 'g-rounded-50x'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 3)
			{
				addClasses = ['g-color-white', 'g-bg-primary', 'g-opacity-0_8--hover'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 4)
			{
				addClasses = ['g-color-white', 'g-bg-primary', 'g-opacity-0_8--hover', 'g-rounded-50x'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 5)
			{
				addClasses = ['g-color-white', 'g-bg-gray-light-v2', 'g-bg-primary--hover'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 6)
			{
				addClasses = ['g-color-white', 'g-bg-gray-light-v2', 'g-bg-primary--hover', 'g-rounded-50x'];
			}
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-arrows') === 7)
			{
				addClasses = ['g-color-gray-light-v1', 'g-color-primary--hover'];
			}

			addClasses.forEach(function(addedClass) {
				newArrowClassesArr.push(addedClass);
			})
			newArrowClasses = newArrowClassesArr.join(' ');
			BX.Dom.attr(sliderNode, 'data-arrows-classes', newArrowClasses);
		}
	}

	//animation setting
	BX.Landing.SliderHelper.setAnimation = function(sliderNode) {
		var dataSlickAttr = BX.Dom.attr(sliderNode, 'data-slick');
		if (!BX.Type.isObject(dataSlickAttr))
		{
			dataSlickAttr = {};
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-animation') === 0)
		{
			dataSlickAttr.animation = 'none';
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-animation') === 1)
		{
			dataSlickAttr.animation = 'ease';
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-animation') === 2)
		{
			dataSlickAttr.animation = 'cubic-bezier(0.600, -0.280, 0.735, 0.045)';
		}
		if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-animation') === 3)
		{
			dataSlickAttr.animation = '';
			BX.Dom.attr(sliderNode, 'data-fade', 'true');
		}
		else
		{
			if (BX.Dom.attr(sliderNode.parentNode, 'data-slider-animation') !== null)
			{
				sliderNode.removeAttribute('data-fade');
			}
		}
		BX.Dom.attr(sliderNode, 'data-slick', dataSlickAttr);
	}
})(window.jQueryLanding || jQuery);