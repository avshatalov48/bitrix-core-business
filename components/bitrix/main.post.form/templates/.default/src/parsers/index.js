import Default from './default';
import Spoiler from './spoiler';
import PostUser from './postuser';
import UploadImage from './files/upload-image';
import UploadFile from './files/upload-file';
import DiskFile from './files/disk-file';
import AIImageGenerator from './aiImageGenerator';

function getKnownParser(parserId, editor, htmlEditor): ?Default
{
	if (parserId === 'Spoiler')
	{
		return new Spoiler(editor, htmlEditor);
	}
	if (parserId === 'MentionUser')
	{
		return new PostUser(editor, htmlEditor);
	}
	if (parserId === 'UploadImage')
	{
		return new UploadImage(editor, htmlEditor);
	}
	if (parserId === 'UploadFile')
	{
		return new UploadFile(editor, htmlEditor);
	}
	if (parserId === 'AIImage')
	{
		return new AIImageGenerator(editor, htmlEditor);
	}
	if (typeof parserId === 'object' && parserId['disk_file'])
	{
		return new DiskFile(editor, htmlEditor);
	}

	return null;
}

export default getKnownParser;