import { type BBCodeElementNode, type BBCodeScheme } from 'ui.bbcode.model';

export function normalizeLineBreaks(node: BBCodeElementNode): BBCodeElementNode
{
	const scheme: BBCodeScheme = node.getScheme();

	// To avoid a height collapsing for empty elements we need to add an additional <br> (<p></p> => <p><br></p>).
	// If we end an element with a LineBreakNode, then we need to add an additional <br>.
	// A browser doesn't render the last <br> (<p>text<br></p>).

	node.appendChild(scheme.createNewLine());

	return node;
}
