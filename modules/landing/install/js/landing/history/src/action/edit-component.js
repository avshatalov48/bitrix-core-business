import Entry from './../history-entry';

const {scrollTo, highlight} = BX.Landing.Utils;

const editComponent = (entry: Entry) => {
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			/**
			 * @type {BX.Landing.Block}
			 */
			const block = blocks.get(entry.block);

			if (!block)
			{
				return Promise.reject();
			}

			block.forceInit();
			if (!block.node)
			{
				return Promise.reject();
			}

			return scrollTo(block.node)
				.then(() => {
					return block.applyAttributeChanges(
						{
							[entry.params.selector]: {
								attrs: entry.params.value,
							},
						},
						true,
					);
				})
				.then(block.reload.bind(block))
				.then(highlight.bind(null, block.node, false, false))
			;
		});
};

export default editComponent;
