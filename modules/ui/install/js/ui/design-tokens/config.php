<?

use Bitrix\Main\Config\Configuration;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$postRelations = [];
if (!defined('UI_DESIGN_TOKENS_SKIP_CUSTOM_EXTENSION') || UI_DESIGN_TOKENS_SKIP_CUSTOM_EXTENSION !== true)
{
	$customExtension = \Bitrix\Main\Config\Option::get('ui', 'design_tokens:custom_extension', '');
	if (is_string($customExtension) && !empty($customExtension))
	{
		$postRelations = [$customExtension];
	}
	else
	{
		$configuration = Configuration::getValue('ui');
		if (is_array($configuration) && !empty($configuration['design_tokens']['custom_extension']))
		{
			$postRelations = [$configuration['design_tokens']['custom_extension']];
		}
	}
}

return [
	'css' => 'dist/ui.design-tokens.css',
	'post_rel' => $postRelations,
	'skip_core' => true,
];
