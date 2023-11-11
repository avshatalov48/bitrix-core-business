export default function renameX(filename, x)
{
	const name = filename.replace(/@[1-9]x/, '');
	let extension = BX.util.getExtension(name);
	if (extension.length > 4)
	{
		extension = extension.split('_').pop();
	}

	return name ? name.replace(/\.[^.]+$/, `@${x}x.${extension}`) : name;
}