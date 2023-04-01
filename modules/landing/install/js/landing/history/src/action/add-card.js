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

			return block;
		})
		.then((block) => {
			return BX.Landing.PageObject.getInstance().view()
				.then((iframe) => {
					const parentNode = iframe.contentDocument.querySelector(entry.params.selector).parentNode;
					return [
						block,
						parentNode,
					];
				});
		})
		.then((elements) => {
			return scrollTo(elements[1])
				.then(() => {
					return elements;
				});
		})
		.then((elements) => {
			let block = elements[0];
			return block
				.addCard({
					index: entry.params.position,
					container: elements[1],
					content: entry.params.content,
					selector: entry.params.selector,
				}, true)
				.then(() => {
					const card = block.cards.getBySelector(entry.params.selector);
					if (!card)
					{
						return Promise.reject();
					}

					return highlight(card.node);
				})
		})
		.catch(() => {});
}