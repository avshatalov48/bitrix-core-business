;(function() {
	"use strict";

	BX(function() {
		if (typeof BX.Landing === "undefined" || typeof BX.Landing.Main === "undefined")
		{
			// region INIT
			BX.namespace("BX.Landing");

			const blocks = [].slice.call(document.getElementsByClassName("block-wrapper"));
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
			// endregion

			// region PSEUDO LINKS
			const pseudoLinks = [].slice.call(document.querySelectorAll("[data-pseudo-url*=\"{\"]"));
			if (pseudoLinks.length)
			{
				pseudoLinks.forEach(link => {
					const linkOptions = BX.Landing.Utils.data(link, "data-pseudo-url");
					if (
						linkOptions.href
						&& linkOptions.enabled
						&& linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') !== 0
					)
					{
						if (linkOptions.target === "_self" || linkOptions.target === "_blank")
						{
							link.addEventListener("click", event => {
								event.preventDefault();
								let url;
								try {
									url = new URL(linkOptions.href);
								} catch (e) {
									url = null;
								}
								if (
									url
									&& url.hostname === window.location.host
									&& url.pathname !== window.location.pathname
									&& url.searchParams.get('IFRAME') !== "Y"
								)
								{
									BX.addClass(document.body, "landing-page-transition");
									linkOptions.href = url.href;
									setTimeout(() => {
										openPseudoLinks(linkOptions, event);
									}, 400);
									setTimeout(() => {
										BX.removeClass(document.body, "landing-page-transition");
									}, 3000);
								}
								else
								{
									openPseudoLinks(linkOptions, event);
								}
							});
						}

						// stop click from children
						const childLinks = link.getElementsByTagName('a');
						if (childLinks.length > 0)
						{
							[].slice.call(childLinks).map(function (node)
							{
								stopPropagation(node);
							});
						}

						if (BX.hasClass(link, 'g-bg-cover'))
						{
							const child = link.firstElementChild;
							if (child)
							{
								stopPropagation(child);
							}
						}
					}
				});
			}
			// endregion

			// region STOP PROPAGATION for sub-elements in pseudo-link nodes - old variant
			const stopPropagationNodes = [].slice.call(document.querySelectorAll("[data-stop-propagation]"));
			if (stopPropagationNodes.length)
			{
				stopPropagationNodes.forEach(function(node) {
					stopPropagation(node);
				});
			}
			// endregion

			// region all LINKS FOR MOBILE
			if (typeof BXMobileApp !== 'undefined')
			{
				const allLinks = [].slice.call(document.querySelectorAll('a'));
				if (allLinks.length)
				{
					allLinks.forEach(function(link) {
						//file links
						if (link.href && link.href.indexOf('file:') === 0)
						{
							link.addEventListener('click', function(event) {
								event.preventDefault();
								BXMobileApp.PageManager.loadPageBlank({
									url: link.href,
									cache: false,
									bx24ModernStyle: true,
								});
							});
						}
					});
				}
			}
			// endregion

			// region SCROLL-TO for #block links
			if (typeof BXMobileApp === "undefined")
			{
				const blocksLinks = [].slice.call(document.querySelectorAll('a[href*="#"]'))
				if (!!blocksLinks && blocksLinks.length)
				{
					blocksLinks.forEach(link => {
						const href = link.getAttribute("href");
						if (link.target === '_self' || link.target === '')
						{
							if (isBlockLink(href))
							{
								link.addEventListener("click", onBlockLinkClick);
							}
						}
					});
				}
			}
			// endregion

			const setLinks = [].slice.call(document.querySelectorAll("a"));
			setLinks.forEach(function(link) {
				const href = link.getAttribute("href");
				if (link.target === '_self' && !isBlockLink(href))
				{
					link.addEventListener("click", event => {
						const url = new URL(link.href);
						if (
							url.hostname === window.location.host
							&& url.pathname !== window.location.pathname
							&& url.searchParams.get('IFRAME') !== "Y"
						)
						{
							event.preventDefault();
							BX.addClass(document.body, "landing-page-transition");
							setTimeout(() => {
								top.open(url.href, link.target);
							}, 400);
							setTimeout(() => {
								BX.removeClass(document.body, "landing-page-transition");
							}, 3000);
						}
					});
				}
			});

			// region FUNCTIONS
			function stopPropagation(node)
			{
				node.addEventListener("click", function(event) {
					event.stopPropagation();
				});
			}

			/**
			 * Check if url move to block at current page
			 * @param {string} url
			 * @returns {boolean}
			 */
			function isBlockLink(url)
			{
				if (url !== null)
				{
					if (url === '#' || url.startsWith('#/'))
					{
						return false;
					}
				}
				const urlObj = new URL(url, document.location);
				return urlObj.hash !== ''
					&& urlObj.pathname === document.location.pathname
					&& urlObj.hostname === document.location.hostname;
			}

			// height of float header
			let headerOffset = 0;
			const headerFix = document.querySelector('.u-header.u-header--sticky');
			if (!!headerFix)
			{
				const navbar = headerFix.querySelector('.navbar');
				if (!!navbar)
				{
					const navSection = BX.findParent(navbar, {class: 'u-header__section'});
					headerOffset = !!navSection
						? navSection.offsetHeight
						: navbar.offsetHeight;
				}
			}

			// scroll correction if open page by link with hash (to block)
			if (headerOffset && isBlockLink(document.URL))
			{
				document.addEventListener('DOMContentLoaded', () => {
					if (window.pageYOffset > 0)
					{
						window.scrollTo({
							top: window.pageYOffset - headerOffset,
						});
					}
				});
			}

			function onBlockLinkClick(event)
			{
				try
				{
					event.preventDefault();

					let targetSelector = null;
					let urlForHistory = null;
					const link = event.currentTarget;
					// hash/anchor can be not valid for various reasons
					if (link.tagName === 'A')
					{
						targetSelector = link.hash;
						urlForHistory = link.href;
					}
					else if (link.hasAttribute('data-pseudo-url'))
					{
						const linkOptions = BX.Landing.Utils.data(link, "data-pseudo-url");
						const urlObj = new URL(linkOptions.href);
						targetSelector = urlObj.hash;
						urlForHistory = urlObj.href;
					}

					if (!targetSelector || !urlForHistory)
					{
						return;
					}

					const target = document.querySelector(targetSelector);
					window.scrollTo({
						top: target.offsetTop - headerOffset,
						behavior: 'smooth'
					});
					link.blur(); // disable :focus after click
					history.pushState({}, '', urlForHistory);
				}
				catch (e) {}
			}

			function openPseudoLinks(linkOptions, event)
			{
				if (linkOptions.href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0)
				{
					return;
				}
				// mobile device
				if (typeof BXMobileApp !== "undefined")
				{
					BXMobileApp.PageManager.loadPageBlank({
						url: linkOptions.href,
						cache: false,
						bx24ModernStyle: true,
					});
				}
				// desktop
				else
				{
					if (
						linkOptions.target === '_self'
						&& isBlockLink(linkOptions.href)
					)
					{
						onBlockLinkClick(event);
					}
					else
					{
						if (linkOptions.query)
						{
							linkOptions.href += (linkOptions.href.indexOf('?') === -1) ? '?' : '&';
							linkOptions.href += linkOptions.query;
						}
						top.open(linkOptions.href, linkOptions.target);
					}
				}
			}
			// endregion
		}
	});
})();