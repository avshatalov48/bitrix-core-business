const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {string} state
 * @param {object} entry
 * @return {Promise}
 */
export default function addCard(state, entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);
			if (block)
			{
				block.forceInit();
			}

			if (!block)
			{
				return Promise.reject();
			}

			return block;
		})
		.then((block) => {
			return BX.Landing.PageObject.getInstance().view()
				.then((iframe) => {
					return [
						block,
						iframe.contentDocument.querySelector(entry[state].container),
					];
				});
		})
		.then((params) => {
			return scrollTo(params[1])
				.then(() => {
					return params;
				});
		})
		.then((params) => {
			params[0].addCard({
				index: entry[state].index,
				container: params[1],
				content: entry[state].html,
				selector: entry.selector,
			});

			const card = params[0].cards.getBySelector(entry.selector);

			if (!card)
			{
				return Promise.reject();
			}

			return highlight(card.node);
		})
		.catch(() => {});
}