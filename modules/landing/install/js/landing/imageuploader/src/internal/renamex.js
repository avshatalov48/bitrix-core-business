export default function renameX(filename, x)
{
	const name = filename.replace(/@[1-9]x/, '');
	return name ? name.replace(/\.[^.]+$/, `@${x}x.${BX.util.getExtension(name)}`) : name;
}