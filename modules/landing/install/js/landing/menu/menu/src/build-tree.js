export default function buildTree(root: HTMLElement, selector, parent = null, depth = 0)
{
	return [...root.querySelectorAll(selector)]
		.filter((element) => {
			return element.parentElement.closest(selector) === parent;
		})
		.map((element) => {
			const newDepth = depth + 1;
			return {
				layout: element,
				children: buildTree(element, selector, element, newDepth),
				depth,
			};
		});
}