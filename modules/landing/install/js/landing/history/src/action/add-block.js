import {PageObject} from 'landing.pageobject';

const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function addBlock(entry)
{
	return PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.params.currentBlock);

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
						landing.currentArea = iframe.contentDocument.body.querySelector(`[data-landing="${entry.params.lid}"]`);
						landing.insertBefore = entry.params.insertBefore;

						return landing.onAddBlock(entry.params.code, entry.block, true);
					});
				});
		});
}