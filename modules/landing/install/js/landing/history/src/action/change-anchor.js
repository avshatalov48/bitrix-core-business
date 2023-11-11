import {PageObject} from 'landing.pageobject';

const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function changeAnchor(entry)
{
	return PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.params.currentBlock);

			return new Promise((resolve, reject) => {
				if (block)
				{
					block.forceInit();
					resolve(block);
				}
				else
				{
					reject();
				}
			})
			.then((block) => {
				scrollTo(block).then(highlight.bind(null, block, false, false));
			});
		});
}