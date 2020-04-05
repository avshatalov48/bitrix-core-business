;(function ()
{
	"use strict";

	/**
	 * @todo Refactoring
	 */
	BX.addCustomEvent(window, "BX.Landing.Block:init", function (event)
	{
		var headerSelector = event.makeRelativeSelector('.u-header');
		if (event.block.querySelectorAll(headerSelector).length > 0)
		{
			// in edit mode menu must be like a usual block
			if (BX.Landing.getMode() == "view")
			{
				$.HSCore.components.HSHeader.init($(headerSelector));
			}
		}

		var scrollNavSelector = event.makeRelativeSelector('.js-scroll-nav');
		if (event.block.querySelectorAll(scrollNavSelector).length > 0)
		{
			checkActive(scrollNavSelector);

			$.HSCore.components.HSScrollNav.init($('.js-scroll-nav'), {
				duration: 400,
				easing: 'easeOutExpo'
			});
		}
	});


	// remove all ".active"
	function removeAllActive(selector)
	{
		removeActive($(selector).find('.nav-item.active'));
	}

	/**
	 * @param node - may be Node or selector
	 */
	function removeActive(node)
	{
		$(node)
		.removeClass('active')
		.find('span.sr-only').remove();
	}

	/**
	 * @param node - may be Node or selector
	 */
	function addActive(node)
	{
		$(node)
		.addClass('active')
		.find('a.nav-link').after('<span class="sr-only">(current)</span>');
	}


	// unset not actual @active@ class, set true
	function checkActive(selector)
	{
		removeAllActive(selector);

		// in editor - set first element as active, for example
		if (BX.Landing.getMode() == "edit")
		{
			addActive($(selector).find('a').parent('.nav-item').eq(0));
		}
		// in viewer - set active by curr URL
		else
		{
			var pageUrl = document.location.pathname;

			$(selector).find('a').each(function (i)
			{
				var currNode = $(this).get()[0];
				// if href has hash - it link to block and they was be processed by scroll nav
				if (
					currNode.pathname == pageUrl &&
					currNode.hash == ''
				)
				{
					addActive($(this).parent('.nav-item'));
				}
			});
		}
	}


	//unset ACTIVE on menu link
	BX.addCustomEvent("BX.Landing.Block:Card:beforeAdd", function (event)
	{
		var scrollNavSelector = event.makeRelativeSelector('.js-scroll-nav');
		if (event.block.querySelectorAll(scrollNavSelector).length > 0)
		{
			if (event.card && BX.hasClass(event.card, 'active'))
			{
				removeActive(event.card);
				BX.addCustomEvent("BX.Landing.Block:Card:add", returnActiveClass);
			}
		}
	});


	// run only after clone active card - return active class for parent card
	function returnActiveClass(event)
	{
		var scrollNavSelector = event.makeRelativeSelector('.js-scroll-nav');
		if (event.block.querySelectorAll(scrollNavSelector).length > 0)
		{
			if (event.card)
			{
				var prevCard = BX.findPreviousSibling(event.card);
				if (prevCard)
				{
					addActive(prevCard);
				}
			}
			BX.removeCustomEvent("BX.Landing.Block:Card:add", returnActiveClass)
		}
	}
})();