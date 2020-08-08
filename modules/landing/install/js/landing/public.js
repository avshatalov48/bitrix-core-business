;(function() {
	"use strict";

	BX(function() {
		if (typeof BX.Landing === "undefined" || typeof BX.Landing.Main === "undefined")
		{
			BX.namespace("BX.Landing");

			BX.Landing.getMode = function()
			{
				return window.top === window ? "view" : "design";
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

			// pseudo links
			var pseudoLinks = [].slice.call(document.querySelectorAll("[data-pseudo-url*=\"{\"]"));
			if (pseudoLinks.length)
			{
				pseudoLinks.forEach(function(link) {
					var linkOptions = BX.Landing.Utils.data(link, "data-pseudo-url");

					if (linkOptions.href &&
						linkOptions.target !== "_popup" &&
						linkOptions.enabled)
					{
						link.addEventListener("click", function(event) {
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

			// stop propagation for sub-elements in pseudo-link nodes
			var stopPropagationNodes = [].slice.call(document.querySelectorAll("[data-stop-propagation]"));
			if(stopPropagationNodes.length)
			{
				stopPropagationNodes.forEach(function(node) {
					node.addEventListener("click", function(event) {
						event.stopPropagation();
					});
				});
			}
			
			// scroll-to for #block links
			var blocksLinks = [].slice.call(document.querySelectorAll('a[href*="#"]'))
			if (!!blocksLinks && blocksLinks.length)
			{
				blocksLinks.forEach(function (link)
				{
					if (
						link.getAttribute("href") !== "#" &&
						link.pathname === document.location.pathname &&
						link.hostname === document.location.hostname
					)
					{
						var target = document.querySelector(link.hash);
						if (target)
						{
							link.addEventListener("click", function (event)
							{
								event.preventDefault();
								window.scrollTo({
									top: target.offsetTop,
									behavior: 'smooth'
								});
							});
						}
					}
				});
			}
		}
	});
})();