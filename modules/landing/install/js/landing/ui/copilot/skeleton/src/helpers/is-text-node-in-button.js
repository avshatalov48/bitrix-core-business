import { Dom } from 'main.core';

export const isTextNodeInButton = (textNode: Text) => {
	return Dom.hasClass(textNode?.parentElement, 'btn');
};
