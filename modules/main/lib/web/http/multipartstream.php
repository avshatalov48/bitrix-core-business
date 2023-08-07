<?php

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\ArgumentException;

class MultipartStream extends FormStream
{
	protected const BUF_LEN = 524288;

	protected $boundary;

	public function getBoundary(): string
    {
        if ($this->boundary === null)
		{
            $this->boundary = 'BXC' . uniqid('', true);
        }

        return $this->boundary;
    }

	protected function build(array $data)
	{
		$boundary = $this->getBoundary();

		foreach ($data as $k => $v)
		{
			$this->write('--' . $boundary . "\r\n");

			if ((is_resource($v) && get_resource_type($v) === 'stream') || is_array($v))
			{
				$filename = $v['filename'] ?? $k;
				$contentType = $v['contentType'] ?? 'application/octet-stream';

				$this->write('Content-Disposition: form-data; name="' . $k . '"; filename="' . $filename . '"' . "\r\n");
				$this->write('Content-Type: ' . $contentType . "\r\n\r\n");

				if (is_array($v))
				{
					if (isset($v['resource']) && is_resource($v['resource']) && get_resource_type($v['resource']) === 'stream')
					{
						fseek($v['resource'], 0);
						while (!feof($v['resource']))
						{
							$this->write(stream_get_contents($v['resource'], static::BUF_LEN));
						}
					}
					else
					{
						if (isset($v['content']))
						{
							$this->write($v['content']);
						}
						else
						{
							throw new ArgumentException("File `{$k}` not found for multipart upload.", 'data');
						}
					}

				}
				else
				{
					fseek($v, 0);
					while (!feof($v))
					{
						$this->write(stream_get_contents($v, static::BUF_LEN));
					}
				}
			}
			else
			{
				$this->write('Content-Disposition: form-data; name="' . $k . '"' . "\r\n\r\n");
				$this->write($v);
			}

			$this->write("\r\n");
		}

		$this->write('--' . $boundary . "--\r\n");
	}
}
