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
	BX.Landing.History.Action.addBlock = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry[state].currentBlock);

				return new Promise(function(resolve) {
					if (block)
					{
						block.forceInit();
						return scrollTo(block.node)
							.then(highlight.bind(null, block.node, false, true))
							.then(resolve);
					}
					else
					{
						resolve();
					}
				})
					.then(function() {
						var landing = BX.Landing.Main.getInstance();
						landing.currentBlock = block;

						return BX.Landing.PageObject.getInstance().view().then(function(iframe) {
							landing.currentArea = iframe.contentDocument.body.querySelector("[data-landing=\""+entry[state].lid+"\"]");
							return landing.onAddBlock(entry[state].code, entry.block, true);
						});
					})
			});
	};
})();