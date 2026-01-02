<?php

namespace Bitrix\Main\Engine\View;

use Bitrix\Main\Diag\Helper;
use Bitrix\Main\Engine\View\Exception\FileNotExistsException;
use Bitrix\Main\Engine\View\Exception\InvalidPathOnViewException;
use Bitrix\Main\Engine\View\Exception\PathOutsideDocumentRootException;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

final class ViewPathResolver
{
	private string $documentRoot;

	public function __construct(
		private string $viewPath,
		private string $renderFunctionName,
	)
	{
		$this->documentRoot = Loader::getDocumentRoot();
	}

	/**
	 * Gets path on document root to view file.
	 *
	 * @return string
	 */
	public function resolve(): string
	{
		$path = $this->getAbsolutePath();
		if (!empty($path))
		{
			$path = Path::normalize($path);
		}

		if (is_null($path))
		{
			throw new InvalidPathOnViewException($this->viewPath);
		}
		elseif (!file_exists($path))
		{
			throw new FileNotExistsException($path);
		}
		elseif (!str_starts_with($path, $this->documentRoot))
		{
			throw new PathOutsideDocumentRootException($this->viewPath);
		}

		$pathOnDocumentRoot = substr(
			$path,
			strlen($this->documentRoot)
		);

		return $pathOnDocumentRoot;
	}

	private function getAbsolutePath(): ?string
	{
		if (str_starts_with($this->viewPath, '/'))
		{
			return $this->documentRoot . $this->viewPath;
		}

		return $this->getPathToViewOnModule();
	}

	private function getPathToViewOnModule(): ?string
	{
		$moduleId = $this->getModuleId();
		$postfix = '';

		$extension = pathinfo($this->viewPath, PATHINFO_EXTENSION);
		if ($extension === '')
		{
			$postfix = '.php';
		}
		elseif ($extension !== 'php')
		{
			throw new SystemException('Invalid extension for view file: ' . $extension);
		}

		$absolutePath = Loader::getLocal(
			'modules/' . $moduleId . '/views/' . $this->viewPath . $postfix,
		);
		if ($absolutePath !== false)
		{
			return $absolutePath;
		}

		return null;
	}

	private function getModuleId(): string
	{
		$trace = Helper::getBackTrace();
		foreach ($trace as $item)
		{
			if ($item['function'] === $this->renderFunctionName)
			{
				$path = Path::normalize($item['file']);
				$parts = explode('/', $path);
				$prevPart = null;
				foreach ($parts as $part)
				{
					if ($part === 'lib')
					{
						if (isset($prevPart))
						{
							return $prevPart;
						}
					}

					$prevPart = $part;
				}
			}
		}

		throw new SystemException('Cannot parse module id from path');
	}
}
