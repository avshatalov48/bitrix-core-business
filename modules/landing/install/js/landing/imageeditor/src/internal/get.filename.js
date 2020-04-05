export default function getFilename(path)
{
	return path.split('\\').pop().split('/').pop();
}