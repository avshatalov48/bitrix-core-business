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
	BX.Landing.History.Action.addCard = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);
				block && block.forceInit();

				if (!block)
				{
					return Promise.reject();
				}

				return block;
			})
			.then(function(block) {
				return BX.Landing.PageObject.getInstance().view()
					.then(function(iframe) {
						return [
							block,
							iframe.contentDocument.querySelector(entry[state].container)
						]
					})
			})
			.then(function(params) {
				return scrollTo(params[1])
					.then(function() {
						return params;
					})
			})
			.then(function(params) {
				params[0].addCard({
					index: entry[state].index,
					container: params[1],
					content: entry[state].html,
					selector: entry.selector
				});

				var card = params[0].cards.getBySelector(entry.selector);

				if (!card)
				{
					return Promise.reject();
				}

				return highlight(card.node);
			})
			.catch(function() {});
	};
})();