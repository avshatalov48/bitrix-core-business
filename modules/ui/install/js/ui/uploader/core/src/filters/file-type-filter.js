import Filter from './filter';
import isValidFileType from '../helpers/is-valid-file-type';
import UploaderError from '../uploader-error';

import type UploaderFile from '../uploader-file';
import type Uploader from '../uploader';

export default class FileTypeFilter extends Filter
{
	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {
			if (isValidFileType(file.getBinary(), this.getUploader().getAcceptedFileTypes()))
			{
				resolve();
			}
			else
			{
				reject(new UploaderError('FILE_TYPE_NOT_ALLOWED'));
			}
		});
	}
}
