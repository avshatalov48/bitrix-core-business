import { Type } from 'main.core';

const getClosestZIndexElement = (target: HTMLElement) => {
	let currentElement = target;

	while (currentElement && currentElement !== document.body)
	{
		const computedStyle = getComputedStyle(currentElement);
		const position = computedStyle.position;

		if (position === 'absolute' || position === 'fixed')
		{
			const zIndex = computedStyle.zIndex;

			if (zIndex !== 'auto')
			{
				const zIndexValue = parseInt(zIndex, 10);

				if (Type.isNumber(zIndexValue))
				{
					return zIndexValue;
				}
			}
		}

		currentElement = currentElement.parentElement;
	}

	return 0;
};

export { getClosestZIndexElement };
