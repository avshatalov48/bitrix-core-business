import {Type} from 'main.core';

const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function changeNodeName(entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);
			if (!block)
			{
				return Promise.reject();
			}
			block.forceInit();

			const node = block.nodes.getBySelector(entry.selector);
			if (!node)
			{
				return Promise.reject();
			}

			return scrollTo(node.node)
				.then(() => {
					return highlight(node.node);
				})
				.then(() => {
					if (node.onChangeTag)
					{
						node.onChangeTag(entry.params.value, true);
					}

					return true;
				});
		});
}