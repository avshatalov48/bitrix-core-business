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
	BX.Landing.History.Action.removeBlock = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);
				block.forceInit();

				return scrollTo(block.node)
					.then(function() {
						highlight(block.node);
						return block.deleteBlock(true);
					})
			});
	};
})();