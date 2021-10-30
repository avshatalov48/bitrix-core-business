;(function ()
{
	"use strict";

	// native lazyload
	var isNative = 'loading' in HTMLImageElement.prototype;
	BX(function ()
	{
		if (isNative)
		{
			var images = document.querySelectorAll('img[data-lazy-img]');
			images.forEach(function (img)
			{
				img.src = img.dataset.src;
				img.removeAttribute('data-src');
				if (img.dataset.srcset !== undefined)
				{
					img.srcset = img.dataset.srcset;
					img.removeAttribute('data-srcset');
				}
			});
		}
	});

	var observerOptions = {
		rootMargin: (document.documentElement.clientHeight) / 2 + 'px'
	};
	var observer = new IntersectionObserver(onIntersection, observerOptions);

	BX.addCustomEvent("BX.Landing.Block:init", function (event)
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
			// todo: why animation first? need set lazy src before anim
			if (entry.isIntersecting)
			{
				// load <img>
				var observableImages = [].slice.call(entry.target.querySelectorAll('[data-lazy-img]'));
				observableImages.forEach(function (img)
				{
					if (!isNative)
					{
						var origSrc = BX.data(img, 'src');
						var origSrcset = BX.data(img, 'srcset');
						BX.create("img", {
							attrs: {
								src: origSrc,
								srcset: origSrcset ? origSrcset : ''
							},
							events: {
								load: function ()
								{
									BX.adjust(img, {
										attrs: {
											src: origSrc,
											srcset: origSrcset ? origSrcset : '',
											'data-lazy-src': '',
											'data-src': '',
											'data-srcset': ''
										}
									});
									BX.remove(this);
									var event = new BX.Landing.Event.Block({
										block: entry.target,
										node: img,
										data: {src: origSrc}
									});
									BX.onCustomEvent("BX.Landing.Lazyload:loadImage", [event]);
								}
							}
						});
					}
					// native lazy
					else
					{
						img.addEventListener('load', function(){
							var event = new BX.Landing.Event.Block({
								block: entry.target,
								node: img,
								data: {src: img.src}
							});
							BX.onCustomEvent("BX.Landing.Lazyload:loadImage", [event]);
						});
					}
				});


				// bg
				var observableBg = [].slice.call(entry.target.querySelectorAll('[data-lazy-bg]'));
				observableBg.forEach(function (bg)
				{
					var origBg = BX.data(bg, 'bg');
					var origStyle = BX.data(bg, 'style');
					var origSrc = BX.data(bg, 'src');
					var origSrc2x = BX.data(bg, 'src2x');
					if (origSrc2x)
					{
						var origSrcset = origSrc2x + ' 2x';
					}

					BX.create("img", {
						attrs: {
							src: origSrc,
							srcset: origSrcset ? origSrcset : ''
						},
						events: {
							load: function ()
							{
								var newBgStyle = bg.getAttribute('style');
								if (origBg)
								{
									var origBgStyle = [];
									origBg.split('|').forEach(function (bgVal)
									{
										origBgStyle.push('background-image:' + bgVal);
									});
									newBgStyle += origBgStyle.join(';');
									bg.style.setProperty('background-image', null);
								}
								else if (origStyle)
								{
									// compatibility with old format of lazyload attributes (before color control)
									newBgStyle = origStyle;
								}

								BX.adjust(bg, {
									attrs: {
										'style': newBgStyle,
										'data-style': '',
										'data-src': '',
										'data-src2x': ''
									}
								});
								BX.remove(this);
								var event = new BX.Landing.Event.Block({
									block: entry.target,
									node: bg,
									data: {src: origSrc}
								});
								BX.onCustomEvent("BX.Landing.Lazyload:loadImage", [event]);
							}
						}
					});
				});

				observer.unobserve(entry.target);
			}

		});

		// todo: show all after time
	}
})();