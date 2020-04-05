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
	BX.Landing.History.Action.editImage = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);

				if (!block)
				{
					return Promise.reject();
				}

				block.forceInit();
				var node = block.nodes.getBySelector(entry.selector);

				if (!node)
				{
					return Promise.reject();
				}

				return scrollTo(node.node)
					.then(highlight.bind(null, node.node))
					.then(function() {
						entry[state].id = 0;
						return node.setValue(entry[state], false, true);
					});
			});
	};
})();