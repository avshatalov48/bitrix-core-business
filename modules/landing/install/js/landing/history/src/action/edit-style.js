const {scrollTo, slice} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function editStyle(entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);

			if (!block)
			{
				return Promise.reject();
			}

			block.forceInit();
			block.initStyles();
			return block;
		})
		.then((block) => {
			return scrollTo(block.node)
				.then(() => {
					return block;
				});
		})
		.then((block) => {
			let elements = slice(block.node.querySelectorAll(entry.selector));

			if (block.selector === entry.selector)
			{
				elements = [block.content];
			}

			elements.forEach((element) => {
				element.className = entry.params.value.className;
				if (entry.params.value.style && entry.params.value.style !== '')
				{
					element.style = entry.params.value.style;
				}
				else
				{
					element.removeAttribute('style');
				}
			});
			return block;
		})
		.then((block) => {
			const form = block.forms.find((currentForm) => {
				return (
					currentForm.selector === entry.selector
					|| currentForm.relativeSelector === entry.selector
				);
			});

			if (form)
			{
				form.fields.forEach((field) => {
					field.reset();
					field.onFrameLoad();
				});
			}

			// todo: relative selector? position?
			const styleNode = block.styles.find((style) => {
				return (
					style.selector === entry.selector
					|| style.relativeSelector === entry.selector
				);
			});

			if (styleNode)
			{
				if (entry.params.affect && entry.params.affect.length > 0)
				{
					styleNode.setAffects(entry.params.affect);
				}
				block.onStyleInputWithDebounce({node: styleNode.node, data: styleNode.getValue()}, true);
			}
		});
}