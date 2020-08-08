;(function ()
{
	"use strict";

	var observerOptions = {
		// rootMargin: document.documentElement.clientHeight + 'px'
	};
	var observer = new IntersectionObserver(onIntersection, observerOptions);
	var loadedMap = new WeakMap();

	var slice = BX.Landing.Utils.slice;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	var bgHideStyleString = "[style*='background-image:']{background-image: none !important;}";

	onCustomEvent("BX.Landing.Block:init", function (event)
	{
		observer.observe(event.block);
	});


	/**
	 * @param {IntersectionObserverEntry[]} entries
	 */
	function onIntersection(entries)
	{
		entries.forEach(function (entry)
		{
			if (entry.isIntersecting && !loadedMap.has(entry.target))
			{
				loadedMap.set(entry.target, true);

				// load <img>
				var observableImgs = slice(entry.target.querySelectorAll('[data-lazy-src]'));
				observableImgs.forEach(function(img) {
					var src = BX.data(img, 'lazy-src');
					BX.adjust(img, {
						attrs: {
							'src': src,
							'data-lazy-src': ""
						}
					});
				});

				//load bg
				var firstChild = entry.target.firstElementChild;
				if(
					firstChild.tagName == 'STYLE' &&
					firstChild.innerText.indexOf(bgHideStyleString) !== 0
				)
				{
					BX.remove(firstChild);
				}
			}
		});
	}
})();