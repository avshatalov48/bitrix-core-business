import {PageObject} from 'landing.pageobject';

const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function editAttributes(entry)
{
	return PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);

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
			.then(block => {
				return scrollTo(block.node)
					.then(() => {
						return block.applyAttributeChanges({
							[entry.params.selector]: {
								attrs: {
									[entry.params.attribute]: entry.params.value,
								},
							},
						});
					})
					.then(highlight.bind(null, block.node, false, false))
				;
			});
		});
}
