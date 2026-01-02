<?php

namespace Bitrix\Main\Cli\Command\Make\Templates;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class ControllerTemplate implements Template
{
	public function __construct(
		private readonly string $name,
		private readonly string $namespace,
		private readonly ?string $module,
		private readonly ?string $alias,
		private readonly array $actions = [],
	)
	{}

	public function getContent(): string
	{
		return <<<PHP
<?php

namespace {$this->namespace};

use Bitrix\Main\Engine\Controller;

final class {$this->name} extends Controller
{
	protected function init()
	{
		parent::init();

		// initialize services and/or load modules
	}
{$this->renderAutoWiredParameters()}

{$this->renderActionsConfig()}

{$this->renderActions()}
}
PHP;
	}

	private function renderAutoWiredParameters(): string
	{
		return <<<PHP
	public function getAutoWiredParameters(): array
	{
		return [];
	}
PHP;
	}

	private function renderActionsConfig(): string
	{
		if (empty($this->actions))
		{
			return <<<PHP
	public function configureActions(): array
	{
		return [];
	}
PHP;
		}
		$code = [];
		foreach ($this->actions as $action)
		{
			$code []= "'{$action}' => [],";
		}

		$code = implode("\n\t\t\t", $code);

		return <<<PHP
	public function configureActions(): array 
	{
		return [
			{$code}
		];
	}
PHP;
	}

	private function renderActions(): string
	{
		if (empty($this->actions))
		{
			return '';
		}

		$module = $this->module ?? 'module';
		$alias = $this->alias ?? 'alias';

		$code = [];
		if ($this->alias === null)
		{
			$code[] = "\t// replace aliases with alias form settings";
		}

		if ($this->module === null)
		{
			$code[] = "\t// replace module your module";
		}

		foreach ($this->actions as $action)
		{
			$code[] = <<<PHP
	/** @ajaxAction {$module}.{$alias}.{$this->name}.{$action} */
	public function {$action}Action(): ?array
	{
		return null;
	}
PHP;
		}

		return implode("\n\n", $code);
	}
}
