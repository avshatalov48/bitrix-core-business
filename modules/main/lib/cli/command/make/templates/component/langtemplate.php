<?php

namespace Bitrix\Main\Cli\Command\Make\Templates\Component;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class LangTemplate implements Template
{
	public function __construct(
		private readonly string $componentTitlePhrase,
		private readonly string $componentTitle,
	)
	{}

	public function getContent(): string
	{
		return <<<PHP
<?php

\$MESS["{$this->componentTitlePhrase}"] = "{$this->componentTitle}";

PHP;
	}
}
