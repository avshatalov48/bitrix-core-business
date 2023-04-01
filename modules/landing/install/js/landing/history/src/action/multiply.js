const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function multiply(entry)
{
	let blockId = null;
	const updateBlockStateData = {};
	entry.params.forEach(singleAction => {
		if (!blockId && singleAction.params.block)
		{
			blockId = singleAction.params.block;
		}

		if (
			singleAction.command === 'editText'
			|| singleAction.command === 'editImage'
			|| singleAction.command === 'editEmbed'
			|| singleAction.command === 'editMap'
			|| singleAction.command === 'editIcon'
			|| singleAction.command === 'editLink'
		)
		{
			updateBlockStateData[singleAction.params.selector] = singleAction.params.value;
		}

		if (singleAction.command === 'updateDynamic')
		{
			updateBlockStateData.dynamicParams = singleAction.params.dynamicParams;
			updateBlockStateData.dynamicState = singleAction.params.dynamicState;
		}

		if (singleAction.command === 'changeAnchor')
		{
			updateBlockStateData.settings = {id: singleAction.params.value};
		}
	});

	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(blockId);
			if (block)
			{
				block.forceInit();

				return scrollTo(block.node)
					.then(() =>
					{
						void highlight(block.node);
						if (Object.keys(updateBlockStateData).length > 0)
						{
							block.updateBlockState(updateBlockStateData, true);
						}
					});
			}
		});
}