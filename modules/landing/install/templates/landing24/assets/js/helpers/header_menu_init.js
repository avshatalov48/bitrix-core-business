;(function ()
{
	"use strict";

	BX.addCustomEvent(window, "BX.Landing.Block:init", function (event)
	{
		initHeaderHandler(event);
		initScrollNavHandler(event);
		initNavbarNavHandler(event);
		initModalAlertHandler(event);
		initMenuMultilevelHandler(event);
	});

	// on Cards:update will be called multi Node:update event. Debounce to preserve this
	BX.addCustomEvent("BX.Landing.Block:Node:update", BX.debounce(initNavbarNavHandler, 200));

	BX.addCustomEvent("BX.Landing.Block:Cards:update", function (event)
	{
		initNavbarNavHandler(event);
	});

	BX.addCustomEvent("BX.Landing.Block:Card:add", function (event)
	{
		initNavbarNavHandler(event);
	});

	BX.addCustomEvent("BX.Landing.Block:Card:remove", function (event)
	{
		initNavbarNavHandler(event);
	});


	function initHeaderHandler(event)
	{
		var headerSelector = event.makeRelativeSelector('.u-header');
		if (event.block.querySelectorAll(headerSelector).length > 0)
		{
			// in edit mode menu must be like a usual block
			if (BX.Landing.getMode() === "view")
			{
				$.HSCore.components.HSHeader.init($(headerSelector));
			}
		}
	}

	function initScrollNavHandler(event)
	{
		var scrollNavSelector = event.makeRelativeSelector('.js-scroll-nav');
		if (event.block.querySelectorAll(scrollNavSelector).length > 0)
		{
			$.HSCore.components.HSScrollNav.init($(scrollNavSelector), {
				duration: 400,
				easing: 'easeOutExpo'
			});
		}
	}

	function initNavbarNavHandler(event)
	{
		var navbarNavSelector = event.makeRelativeSelector('.navbar-nav');
		if (event.block.querySelectorAll(navbarNavSelector).length > 0)
		{
			removeAllActive(navbarNavSelector);
			markActive(navbarNavSelector);
		}
	}

	function initModalAlertHandler(event)
	{
		var navbarModal = event.block.querySelector(event.makeRelativeSelector('.navbar.u-navbar-modal'));
		if (navbarModal && BX.Landing.getMode() == "edit")
		{
			BX.adjust(navbarModal,
				{
					children: [
						BX.create("div", {
							props: {className: "u-navbar-modal-alert"},
							html: BX.message('LANDING_NAVBAR_MODAL_ALERT'),
						})
					]
				}
			);
		}
	}

	function initMenuMultilevelHandler(event)
	{
		if (BX.Landing.getMode() !== "edit")
		{
			var menuMultilevel = event.block.querySelector('.g-menu-multilevel');
			if (menuMultilevel)
			{
				addMultilevelToggler(menuMultilevel);
			}
		}
	}

	/**
	 * Find and check active link(s) in navbar
	 * @param selector
	 */
	function markActive(selector)
	{
		if (BX.Landing.getMode() == "edit")
		{
			if (!markActiveByLid(selector))
			{
				// just mark first
				addActive(document.querySelector(selector).querySelector('.nav-item'));
			}
		}
		else
		{
			markActiveByLocation(selector)
		}
	}

	/**
	 * For editor - find by lid
	 * @param selector
	 * @returns {boolean}
	 */
	function markActiveByLid(selector)
	{
		var marked = false;
		var lid = landingParams['LANDING_ID'];
		if (lid === undefined || lid === null)
		{
			return false;
		}

		var nav = document.querySelector(selector);
		var links = [].slice.call(nav.getElementsByTagName('a'));
		if (!!links && links.length)
		{
			var pageLinkMatcher = new RegExp("#landing([0-9]+)");
			links.forEach(function (link)
			{
				var matches = link.href.match(pageLinkMatcher);
				if (matches !== null && matches[1] == lid)
				{
					addActive(BX.findParent(link, {className: "nav-item"}));
					marked = true;
				}
			});
		}

		return marked;
	}

	/**
	 * For public - find by document.location
	 * @param selector
	 * @returns {boolean}
	 */
	function markActiveByLocation(selector)
	{
		var marked = false;
		var pageUrl = document.location;
		var nav = document.querySelector(selector);
		var links = [].slice.call(nav.getElementsByTagName('a'));

		if (!!links && links.length)
		{
			links.forEach(function (link)
			{
				// if href has hash - it link to block and they was be processed by scroll nav
				if (
					link.hasAttribute("href") &&
					link.getAttribute("href") !== "" &&
					link.pathname === pageUrl.pathname &&
					link.hostname === pageUrl.hostname &&
					link.hash === ''
				)
				{
					var navItem = BX.findParent(link, {className: "nav-item"});
					addActive(navItem);

					marked = true;
				}
			});
		}

		return marked;
	}

	/**
	 * @param node Node - LI node
	 */
	function addActive(node)
	{
		node.classList.add('active');
		BX.adjust(node,
			{
				children: [
					BX.create("span", {
						props: {className: "sr-only"},
						text: '(current)'
					})
				]
			}
		);
	}

	/**
	 * Remove all .active and sr-only
	 * @param selector
	 */
	function removeAllActive(selector)
	{
		var nav = document.querySelector(selector);
		var navItems = [].slice.call(nav.querySelectorAll('.nav-item'));
		if (!!navItems && navItems.length)
		{
			navItems.forEach(function (navItem)
			{
				removeActive(navItem)
			});
		}
	}

	/**
	 * Remove once .active
	 * @param node Node - LI node
	 */
	function removeActive(node)
	{
		node.classList.remove('active');
		BX.remove(node.querySelector('span.sr-only'));
	}

	function addMultilevelToggler(menuMultilevel)
	{
		var subMenus = [].slice.call(menuMultilevel.querySelectorAll('.g-menu-sublevel'));
		subMenus.forEach(function (subMenu)
		{
			var parentNavLink = BX.findPreviousSibling(subMenu, {class: 'nav-link'});
			if (!parentNavLink)
			{
				return;
			}
			hideLevel(parentNavLink);
			// open all branch for .active
			if (subMenu.querySelector('.nav-item.active'))
			{
				showLevel(parentNavLink);
			}

			BX.addClass(parentNavLink, 'g-menu-sublevel-toggler--parent');
			BX.adjust(parentNavLink,
				{
					children: [
						BX.create("span", {
							props: {className: "g-menu-sublevel-toggler"},
							html: '<span class="is-hide-text">'
								+ BX.message('LANDING_NAVBAR_TOGGLER_SHOW')
								+ '</span><span class="is-show-text">'
								+ BX.message('LANDING_NAVBAR_TOGGLER_HIDE')
								+ '</span>',
							events: {
								click: function (event)
								{
									event.preventDefault();
									event.stopPropagation();

									toggleLevel(BX.findParent(event.target, {class: 'nav-link'}));
								}
							}
						})
					]
				}
			);
		});
	}

	function toggleLevel(parentNavLink)
	{
		if (BX.hasClass(parentNavLink, 'g-menu-sublevel-toggler--parent-hide'))
		{
			showLevel(parentNavLink);
		}
		else
		{
			hideLevel(parentNavLink);
		}
	}

	function hideLevel(parentNavLink)
	{
		BX.addClass(parentNavLink, 'g-menu-sublevel-toggler--parent-hide');
		var subMenu = BX.findNextSibling(parentNavLink, {class: 'g-menu-sublevel'});
		if (subMenu)
		{
			BX.addClass(subMenu, 'g-menu-sublevel--hide');
		}
	}

	function showLevel(parentNavLink)
	{
		BX.removeClass(parentNavLink, 'g-menu-sublevel-toggler--parent-hide');
		var subMenu = BX.findNextSibling(parentNavLink, {class: 'g-menu-sublevel'});
		if (subMenu)
		{
			BX.removeClass(subMenu, 'g-menu-sublevel--hide');
		}
	}
})();