export default function getMimeType(path): string
{
	let imageExtension = BX.util.getExtension(path);
	if (imageExtension.length > 4)
	{
		imageExtension = imageExtension.split('_').pop();
	}

	return `image/${imageExtension === 'jpg' ? 'jpeg' : imageExtension}`;
}
