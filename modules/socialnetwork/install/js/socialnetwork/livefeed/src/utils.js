class Utils
{
	static setStyle(node, styles)
	{
		Object.entries(styles).forEach(([key, value]) => {
			node.style[key] = value;
		});
	}
}

export {
	Utils
};
