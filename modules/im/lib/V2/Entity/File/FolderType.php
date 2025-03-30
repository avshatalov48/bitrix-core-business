<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Disk\Folder;

enum FolderType: string
{
	/** @see Folder::CODE_FOR_SAVED_FILES */
	case SavedFiles = 'FOR_SAVED_FILES';

	/** @see Folder::CODE_FOR_CREATED_FILES */
	case CreatedFiles = 'FOR_CREATED_FILES';

	/** @see Folder::CODE_FOR_UPLOADED_FILES*/
	case UploadedFiles = 'FOR_UPLOADED_FILES';
}
