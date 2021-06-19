<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\Util;
use Bitrix\Main\IO\File;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;

class TopIconForMailTemplate
{
	protected const FILE_TYPE = '.png';
	protected const MODULE_SUB_DIR = 'calendar';
	protected const ICAL_SUB_DIR = 'ical';

	/**
	 * @var Date|null
	 */
	private $date;
	/**
	 * @var string
	 */
	private $path;
	/**
	 * @var false|string|string[]
	 */
	private $fileName;
	/**
	 * @var string
	 */
	private $fontPath;
	/**
	 * @var mixed
	 */
	protected $filePath;
	/**
	 * @var string
	 */
	private $templatePath;

	/**
	 * @param Date $date
	 * @return TopIconForMailTemplate
	 */
	public static function fromDate(Date $date): TopIconForMailTemplate
	{
		$iconObject = new self();
		$iconObject->date = $date;
		$uploadDir = \COption::GetOptionString("main", "upload_dir", "upload");
		$iconObject->path = "/".$uploadDir."/".self::MODULE_SUB_DIR."/".self::ICAL_SUB_DIR;
		$iconObject->fileName = mb_strtolower($date->format('M')).self::FILE_TYPE;
		$iconObject->filePath = $iconObject->path."/".$iconObject->fileName;
		return $iconObject;
	}

	/**
	 * TopIconForMailTemplate constructor.
	 */
	public function __construct()
	{
		$this->fontPath = $_SERVER['DOCUMENT_ROOT'] .BX_ROOT.'/modules/main/install/fonts/opensans-bold.ttf';
		$this->templatePath = $_SERVER["DOCUMENT_ROOT"].BX_ROOT.'/components/bitrix/calendar.ical.mail/templates/.default/images/date-template-top.png';
	}

	/**
	 * @param Date $date
	 * @return $this
	 */
	public function setDate(Date $date): TopIconForMailTemplate
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->filePath;
	}

	/**
	 * @return bool
	 */
	public function createImage(): bool
	{
		if (File::isFileExists($_SERVER["DOCUMENT_ROOT"].$this->path."/".$this->fileName))
		{
			return true;
		}

		$image = $this->getImage();
		$tmpFile = $this->getTmpPath();

		if (
			is_resource($image)
			&& $tmpFile !== ''
			&& imagepng($image, $tmpFile, 0)
		)
		{
			$fileData = $this->getFileData($tmpFile);
			$fileId = \CFile::SaveFile($fileData, 'calendar', false, false, self::ICAL_SUB_DIR);
			$fileArray = \CFile::GetFileArray($fileId);
			if (is_array($fileArray))
			{
				if ($fileArray['SRC'] !== $this->filePath)
				{
					$this->filePath = $fileArray['SRC'];
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	protected function getMonthName(): string
	{
		$month = Util::checkRuZone()
			? mb_strtoupper(FormatDate('M', $this->date->getTimestamp()))
			: mb_strtoupper($this->date->format('M'))
		;

		return is_string($month)
			? $month
			: $this->date->format('M')
		;
	}

	/**
	 * @return string
	 */
	protected function getTmpPath(): string
	{
		$tmpFile = \CTempFile::getFileName($this->fileName);
		return CheckDirPath($tmpFile)
			? $tmpFile
			: ''
		;

	}

	/**
	 * @return false|\GdImage|resource|null
	 */
	protected function getImage()
	{
		if (!\Bitrix\Main\IO\File::isFileExists($this->templatePath))
		{
			return null;
		}

		$image = @imagecreatefrompng($this->templatePath);
		$month = Encoding::convertEncoding(Helper::getShortMonthName($this->date), SITE_CHARSET, "utf-8");
		$color = imagecolorallocate($image, 255, 255, 255);
		imagettftext($image, 30, 0, 55, 57, $color, $this->fontPath, $month);

		return $image;
	}

	/**
	 * @param $tmpFile
	 * @return array
	 */
	protected function getFileData($tmpFile): array
	{
		return [
			'name' => $this->fileName,
			'type' => 'image/png',
			'content' => File::getFileContents($tmpFile),
			'MODULE_ID' => 'calendar',
		];
	}
}