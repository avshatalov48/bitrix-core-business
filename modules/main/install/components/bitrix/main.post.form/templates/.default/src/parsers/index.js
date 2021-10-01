import Default from './default';
import Spoiler from './spoiler';
import PostUser from './postuser';
import UploadImage from './files/upload-image';
import UploadFile from './files/upload-file';
import DiskFile from './files/disk-file';

function getKnownParser(parserId, editor, htmlEditor): ?Default
{
	if (parserId === 'Spoiler')
	{
		return new Spoiler(editor, htmlEditor);
	}
	else if (parserId === 'MentionUser')
	{
		return new PostUser(editor, htmlEditor);
	}
	else if (parserId === 'UploadImage')
	{
		return new UploadImage(editor, htmlEditor);
	}
	else if (parserId === 'UploadFile')
	{
		return new UploadFile(editor, htmlEditor);
	}
	else if (typeof parserId === 'object' && parserId['disk_file'])
	{
		return new DiskFile(editor, htmlEditor);
	}
	return null;
}

export default getKnownParser;