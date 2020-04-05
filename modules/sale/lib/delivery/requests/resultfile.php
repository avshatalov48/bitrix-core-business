<?
namespace Bitrix\Sale\Delivery\Requests;

class ResultFile extends Result
{
	protected $fileName = '';
	protected $fileContent = '';

	/**
	 * ResultFile constructor.
	 * @param string $fileName
	 * @param string $fileContent
	 */
	public function __construct($fileName = '', $fileContent = '')
	{
		$this->fileName = $fileName;
		$this->fileContent = $fileContent;
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * @return string
	 */
	public function getFileContent()
	{
		return $this->fileContent;
	}


	/**
	 * @param string $fileName
	 */
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
	}

	/**
	 * @param string $fileContent
	 */
	public function setFileContent($fileContent)
	{
		$this->fileContent = $fileContent;
	}
}