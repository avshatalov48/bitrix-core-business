import {PageObject} from 'landing.pageobject';

const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {string} state
 * @param {object} entry
 * @return {Promise}
 */
export default function addBlock(state, entry)
{
	return PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry[state].currentBlock);

			return new Promise(((resolve) => {
				if (block)
				{
					block.forceInit();
					return scrollTo(block.node)
						.then(highlight.bind(null, block.node, false, true))
						.then(resolve);
				}

				resolve();
			}))
				.then(() => {
					const landing = BX.Landing.Main.getInstance();
					landing.currentBlock = block;

					return PageObject.getInstance().view().then((iframe) => {
						landing.currentArea = iframe.contentDocument.body.querySelector(`[data-landing="${entry[state].lid}"]`);
						return landing.onAddBlock(entry[state].code, entry.block, true);
					});
				});
		});
}