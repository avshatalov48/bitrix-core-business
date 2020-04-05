;(function() {
	"use strict";

	BX.namespace("BX.Landing.History.Action");

	var scrollTo = BX.Landing.Utils.scrollTo;
	var highlight = BX.Landing.Utils.highlight;

	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 * @static
	 */
	BX.Landing.History.Action.removeCard = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);
				block.forceInit();

				if (!block)
				{
					return Promise.reject();
				}

				var card = block.cards.getBySelector(entry.selector);

				if (!card)
				{
					return Promise.reject();
				}

				return scrollTo(card.node)
					.then(highlight.bind(null, card.node))
					.then(function() {
						return block.removeCard(entry.selector, true);
					});
			});
	};
})();