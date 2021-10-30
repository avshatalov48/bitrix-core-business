;(function() {
	"use strict";

	BX(function() {
		if (typeof BX.Landing === "undefined" || typeof BX.Landing.Main === "undefined")
		{
			BX.namespace("BX.Landing");

			BX.Landing.getMode = function()
			{
				return "view";
			};

			var blocks = [].slice.call(document.getElementsByClassName("block-wrapper"));
			if (!!blocks && blocks.length)
			{
				blocks.forEach(function(block) {
					var event = new BX.Landing.Event.Block({block: block});
					BX.onCustomEvent("BX.Landing.Block:init", [event]);
				});
			}

			if (BX.Landing.EventTracker)
			{
				BX.Landing.EventTracker.getInstance().run();
			}

			// emulate browser back button
			var backLinks = [].slice.call(document.querySelectorAll('.js-link-back'));
			if (backLinks.length > 0)
			{
				backLinks.forEach(function(link)
				{
					var referrer = document.referrer;
					if (
						window.history.length > 1
						&& referrer !== ""
						&& referrer.includes(location.hostname)
					)
					{
						link.addEventListener('click', function (event)
						{
							event.preventDefault();
							window.history.back();
						})
						link.href = '#';
					}
				});
			}


			// pseudo links
			var pseudoLinks = [].slice.call(document.querySelectorAll("[data-pseudo-url*=\"{\"]"));
			if (pseudoLinks.length)
			{
				pseudoLinks.forEach(function(link) {
					var linkOptions = BX.Landing.Utils.data(link, "data-pseudo-url");

					if (linkOptions.href && linkOptions.enabled)
					{
						if(linkOptions.target !== "_popup")
						{
							link.addEventListener("click", function (event)
							{
								event.preventDefault();
								// mobile device
								if (typeof BXMobileApp !== "undefined")
								{
									BXMobileApp.PageManager.loadPageBlank({
										url: linkOptions.href,
										cache: false,
										bx24ModernStyle: true
									});
								}
								// desktop
								else
								{
									if (window.top === window)
									{
										if (linkOptions.query)
										{
											linkOptions.href += (linkOptions.href.indexOf('?') === -1) ? '?' : '&';
											linkOptions.href += linkOptions.query;
										}
										top.open(linkOptions.href, linkOptions.target);
									}
								}
							});
						}

						// stop click from children
						var childLinks = link.getElementsByTagName('a');
						if(childLinks.length > 0)
						{
							[].slice.call(childLinks).map(function(node){
								stopPropagation(node);
							})
						}
					}
				});
			}

			function stopPropagation(node)
			{
				node.addEventListener("click", function(event) {
					event.stopPropagation();
				});
			}

			// stop propagation for sub-elements in pseudo-link nodes - old variant
			var stopPropagationNodes = [].slice.call(document.querySelectorAll("[data-stop-propagation]"));
			if(stopPropagationNodes.length)
			{
				stopPropagationNodes.forEach(function(node) {
					stopPropagation(node);
				});
			}

			// all links for mobile
			if (typeof BXMobileApp !== "undefined")
			{
				var allLinks = [].slice.call(document.querySelectorAll("a"));
				if (allLinks.length)
				{
					allLinks.forEach(function(link) {
						if (link.href)
						{
							link.addEventListener("click", function(event) {
								event.preventDefault();
								BXMobileApp.PageManager.loadPageBlank({
									url: link.href,
									cache: false,
									bx24ModernStyle: true
								});
							});
						}
					});
				}
			}


			
			// scroll-to for #block links
			var blocksLinks = [].slice.call(document.querySelectorAll('a[href*="#"]'))
			if (!!blocksLinks && blocksLinks.length)
			{
				var headerOffset = 0;
				var headerFix = document.querySelector('.u-header.u-header--sticky');
				if (!!headerFix)
				{
					var navbar = headerFix.querySelector('.navbar');
					if(!!navbar)
					{
						var navSection = BX.findParent(navbar, {class: 'u-header__section'});
						headerOffset = !!navSection
							? navSection.offsetHeight
							: navbar.offsetHeight;
					}
				}

				blocksLinks.forEach(function (link)
				{
					if (
						link.getAttribute("href") !== '#' &&
						link.hash !== '' &&
						link.pathname === document.location.pathname &&
						link.hostname === document.location.hostname
					)
					{
						// hash can be not valid for various reasons
						try
						{
							var target = document.querySelector(link.hash);
						}
						catch (e) {}
						if (target)
						{
							link.addEventListener("click", function (event)
							{
								event.preventDefault();
								window.scrollTo({
									top: target.offsetTop - headerOffset,
									behavior: 'smooth'
								});
								// disable :focus after click
								event.target.blur();
								history.pushState({}, '', link.href);
							});
						}
					}
				});
			}
		}
	});
})();