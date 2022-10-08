<?php

namespace Bitrix\UI\FileUploader;

class PendingFileStatus
{
	const INIT = 'INIT';
	const PENDING = 'PENDING';
	const ERROR = 'ERROR';
	const COMMITTED = 'COMMITTED';
	const REMOVED = 'REMOVED';
}
