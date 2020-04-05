<?php

namespace Bitrix\Main\Web\WebPacker;

use Bitrix\Main\ArgumentException;

/**
 * Class FileController
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class FileController extends Builder
{
	/** @var Output\File $file File. */
	private $file;

	/** @var Loader $loader Loader. */
	private $loader;

	/**
	 * Delete.
	 *
	 * @return void
	 */
	public function delete()
	{
		$this->getOutputFile()->remove();
	}

	/**
	 * Configure file.
	 *
	 * @param string $id File ID.
	 * @param string $moduleId Bitrix module ID.
	 * @param string $dir File directory.
	 * @param string $name Filename.
	 * @return $this
	 */
	public function configureFile($id, $moduleId, $dir, $name)
	{
		$this->getOutputFile()->setId($id)->setModuleId($moduleId)->setDir($dir)->setName($name);
		return $this;
	}

	/**
	 * @return Loader
	 */
	public function getLoader()
	{
		if (!$this->loader)
		{
			$this->loader = new Loader($this->getOutputFile());
		}

		return $this->loader;
	}

	/**
	 * Get output file.
	 *
	 * @return Output\File
	 */
	protected function getOutputFile()
	{
		if (!$this->file)
		{
			$this->file = new Output\File();
		}

		$this->setOutput($this->file);

		return $this->file;
	}

	/**
	 * Set output.
	 *
	 * @param Output\Base $output Output.
	 * @return $this
	 */
	public function setOutput(Output\Base $output)
	{
		if (! $output instanceof Output\File)
		{
			throw new ArgumentException('Output File expected.');
		}

		parent::setOutput($output);
		return $this;
	}

	/**
	 * Get output.
	 *
	 * @return Output\Base
	 */
	public function getOutput()
	{
		if (!$this->output)
		{
			$this->output = new Output\File();
		}

		return $this->output;
	}
}