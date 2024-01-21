import { Tag } from 'main.core';

export default function applyUserSelect(element, value)
{
	void Tag.style(element)`
		webkitUserSelect: ${value};
		mozUserSelect: ${value};
		msUserSelect: ${value};
		oUserSelect: ${value};
		userSelect: ${value};
	`;
}
