export default function getMimeType(path)
{
	const imageExtension = BX.util.getExtension(path);
	return `image/${imageExtension === 'jpg' ? 'jpeg' : imageExtension}`;
}