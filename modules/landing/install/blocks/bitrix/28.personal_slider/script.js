;(function () {

	"use strict";

	// set additional slider options after block init for BOTTOM slider
	BX.addCustomEvent("BX.Landing.Block:init", function (event) {
		var relativeSelector = event.makeRelativeSelector("#carouselTEAM001");
		if ($(relativeSelector).length > 0)
		{
			setSliderOptions(relativeSelector);
			// generateNavSlider(event.block.id);
		}
	});

	// we can't add card in slider - need destroy slider, clone DOM-element, save content in DB and reinit slider later
	BX.addCustomEvent("BX.Landing.Block:Card:beforeAdd", function(event) {
		// var bottomSliderId = 'carouselTEAM001';
		// var bottomSliderElementClass = 'landing-block-card-bottom-slider-element';
		// for BOTTOM slider
		var relativeSelector = event.makeRelativeSelector("#" + bottomSliderId);

		// save current slider position
		// event.block.slickCurrentSlide2 = $(relativeSelector).slick("slickCurrentSlide");

		if($(relativeSelector).length > 0)
			$(relativeSelector).slick('unslick');

		// find card from top slider in bottom slider and clone they node
		// var bottomSliderElements = BX.findChildrenByClassName(BX(bottomSliderId), bottomSliderElementClass);
		// dbg: need other method to find index - we have no card object already
		// var bottomSliderEventNode = bottomSliderElements[event.card.getIndex()];
		// BX.insertAfter(BX.clone(bottomSliderEventNode), bottomSliderEventNode);
	});


	// reinit slider after add new element in DOM
	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event) {

		var relativeSelector = event.makeRelativeSelector("#carouselTEAM001");
		if ($(relativeSelector).length > 0)
		{
			setSliderOptions(relativeSelector);
		}
	});


	// set additional slider options after removing card
	BX.addCustomEvent("BX.Landing.Block:Card:remove", function (event) {
		var relativeSelector = event.makeRelativeSelector("#carouselTEAM001");
		if ($(relativeSelector).length > 0)
		{
			setSliderOptions(relativeSelector);
		}
	});


	// additional slider options
	var setSliderOptions = function (selector) {
		if ($(selector).length > 0)
		{
			$(selector).slick('setOption', 'responsive', [{
				breakpoint: 1200,
				settings: {
					slidesToShow: 5
				}
			}, {
				breakpoint: 992,
				settings: {
					slidesToShow: 4
				}
			}, {
				breakpoint: 768,
				settings: {
					slidesToShow: 3
				}
			}, {
				breakpoint: 576,
				settings: {
					slidesToShow: 2
				}
			}, {
				breakpoint: 446,
				settings: {
					slidesToShow: 2
				}
			}], true);
		}
	};

	var navSliderClass = "carContTest";
	var personCardClass = "landing-block-card-person";
	var personCardPhotoClass = "landing-block-node-person-photo";
	var personCardNameClass = "landing-block-node-person-name";

	var generateNavSlider = function(parent)
	{
		var container = BX.findChild(BX(parent), navSliderClass);

		var persons = BX.findChildrenByClassName(BX(parent), personCardClass);
		persons.forEach(function(person) {
			var name = BX.findChildrenByClassName(person, personCardNameClass, false)[0].innerHTML;
			var photo = BX.findChildrenByClassName(person, personCardPhotoClass, false);

			// container.appendChild(BX.create("div", {
			// 	'html': '<div class="js-slide g-opacity-1 g-cursor-pointer g-px-15">' +
			// 				'<img class="img-fluid mb-3" src="' + photo + '">' +
			// 				'<h3 class="h6 g-color-text">' + name + '</h3>' +
			// 			'</div>',
			// 	'props' : {
			// 		'className': 'asd'
			// 	}
			// }));
		});
	}
})();
