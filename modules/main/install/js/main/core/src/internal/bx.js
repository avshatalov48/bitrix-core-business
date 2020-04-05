var bxTmp = window.BX;

window.BX = function(node)
{
	if (window.BX.type.isNotEmptyString(node))
	{
		return document.getElementById(node);
	}

	if (window.BX.type.isDomNode(node))
	{
		return node;
	}

	if (window.BX.type.isFunction(node))
	{
		return window.BX.ready(node);
	}

	return null;
};

if (bxTmp)
{
	Object.keys(bxTmp).forEach((key) => {
		window.BX[key] = bxTmp[key];
	});
}

exports = window.BX;