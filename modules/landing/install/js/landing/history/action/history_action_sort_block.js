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
	BX.Landing.History.Action.sortBlock = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);
				block.forceInit();

				return scrollTo(block.node)
					.then(highlight.bind(null, block.node))
					.then(function() {
						return block[entry[state]](true);
					})
			});
	};
})();