;(function() {
	"use strict";

	if (document.querySelector('.landing-edit-mode') === null)
	{
		BX.addCustomEvent("BX.Landing.Block:init", function(event)
		{
			if (event.block.querySelector("[class^='landing-block-faq-']") !== null)
			{
				initCard(event.block);
			}
		});
	}

	function initCard(block)
	{
		const cards = block.querySelectorAll('.landing-block-card');
		cards.forEach(function(card) {
			if (card.querySelector('.landing-block-faq-visible'))
			{
				card.querySelector('.landing-block-faq-visible').onclick = function() {
					if (!BX.Dom.hasClass(card, 'active'))
					{
						if (BX.Dom.hasClass(card, 'faq-single-mode'))
						{
							cards.forEach(function(card) {
								BX.Dom.removeClass(card, 'active');
							})
						}
						BX.Dom.addClass(card, 'active');
					}
					else
					{
						BX.Dom.removeClass(card, 'active');
					}
				};
			}
		})
	}
})();