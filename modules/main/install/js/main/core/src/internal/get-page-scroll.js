export default function getPageScroll(): {scrollTop: number, scrollLeft: number}
{
	const {documentElement, body} = document;

	const scrollTop = Math.max(
		(window.pageYOffset || 0),
		(documentElement ? documentElement.scrollTop : 0),
		(body ? body.scrollTop : 0),
	);

	const scrollLeft = Math.max(
		(window.pageXOffset || 0),
		(documentElement ? documentElement.scrollLeft : 0),
		(body ? body.scrollLeft : 0),
	);

	return {scrollTop, scrollLeft};
}