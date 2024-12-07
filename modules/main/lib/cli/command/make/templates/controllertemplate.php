<?php

namespace Bitrix\Main\Cli\Command\Make\Templates;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class ControllerTemplate implements Template
{
	public function __construct(
		private readonly string $name,
		private readonly string $namespace,
	)
	{}

	public function getContent(): string
	{
		return <<<PHP
<?php

namespace {$this->namespace};

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;

final class {$this->name} extends Controller
{
	protected function init()
	{
		parent::init();

		# initialize services and/or load modules
	}

	public function configureActions()
	{
		return [
			'index' => [],
		];
	}

	public function indexAction(string \$inputArg): bool
	{
		if (empty(\$inputArg))
		{
			\$this->addError(
				new Error('Invalid argument')
			);

			return false;
		}

		# ...

		return true;
	}
}
PHP;
	}
}
