<?php
namespace Bitrix\Main\IO;

interface IFileStream
{
	public function open($mode);
}

class FileStreamOpenMode
{
	const READ = "r";
	const WRITE = "w";
	const APPEND = "a";
}
