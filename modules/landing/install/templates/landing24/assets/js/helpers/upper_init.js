;(function ()
{
	"use strict";

	BX.ready(function ()
	{
		var offsetTop = 555;
		var animatedClass = 'animated';
		var hideClass = 'opacity-0';
		var showEffect = 'zoomIn';
		var selector = '.js-go-to';

		// add button
		document.body.appendChild(
			BX.create("A", {
				props: {
					'className': 'js-go-to u-go-to-v1 opacity-0 g-bg-white-opacity-0_7 g-bg-primary--hover g-brd-main g-brd-white--hover'
				},
				attrs: {
					'data-show-effect': 'zoomIn'
				},
				events: {
					click: toTop
				}
			})
		);

		function toTop(e)
		{
			e.preventDefault();
			window.scrollTo({
				top: 0,
				left: 0,
				behavior: 'smooth'
			});
		}

		BX.bind(window, 'scroll', BX.debounce(checkVisibility, 333));

		function checkVisibility()
		{
			var el = document.querySelector(selector);
			if (window.pageYOffset >= offsetTop)
			{
				BX.removeClass(el, hideClass);
				BX.addClass(el, animatedClass);
				BX.addClass(el, showEffect);
			}
			else
			{
				BX.addClass(el, hideClass);
				BX.removeClass(el, animatedClass);
				BX.removeClass(el, showEffect);
			}
		}
	});

})();