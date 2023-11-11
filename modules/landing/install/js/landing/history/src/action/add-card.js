const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {string} state
 * @param {object} entry
 * @return {Promise}
 */
export default function addCard(entry)
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

			const parentNode = block.node.querySelector(entry.params.selector).parentNode;

			return scrollTo(parentNode)
				.then(() => {
					return block
						.addCard({
							index: entry.params.position,
							container: parentNode,
							content: entry.params.content,
							selector: entry.params.selector,
						}, true)
						.then(() => {
							const cardSelector = entry.params.selector + '@' + entry.params.position;
							const card = block.cards.getBySelector(cardSelector);
							if (!card)
							{
								return Promise.reject();
							}

							return highlight(card.node);
						})
				});
		})
		.catch((err) => {
			console.log("Error in history action addCard", err);
		});
}