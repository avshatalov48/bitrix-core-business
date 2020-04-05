;(function() {
	"use strict";

	BX.namespace("BX.Landing.History.Action");

	var scrollTo = BX.Landing.Utils.scrollTo;
	var slice = BX.Landing.Utils.slice;

	/**
	 * @param {string} state
	 * @param {object} entry
	 * @return {Promise}
	 * @static
	 */
	BX.Landing.History.Action.editStyle = function(state, entry)
	{
		return BX.Landing.PageObject.getInstance().blocks()
			.then(function(blocks) {
				var block = blocks.get(entry.block);

				if (!block)
				{
					return Promise.reject();
				}

				block.forceInit();
				block.initStyles();
				return block;
			})
			.then(function(block) {
				return scrollTo(block.node)
					.then(function() {
						return block;
					})
			})
			.then(function(block) {
				var elements = slice(block.node.querySelectorAll(entry.selector));

				if (block.selector === entry.selector)
				{
					elements = [block.content];
				}

				elements.forEach(function(element) {
					element.className = entry[state].className;
					element.style = entry[state].style;
				});
				return block;
			})
			.then(function(block) {
				var form = block.forms.get(entry.selector);

				if (form)
				{
					form.fields.forEach(function(field) {
						field.reset();
						field.onFrameLoad();
					});
				}

				var styleNode = block.styles.get(entry.selector);

				if (styleNode)
				{
					block.onStyleInputWithDebounce({node: styleNode.node, data: styleNode.getValue()});
				}
			})
	};
})();