<?php
/**
 * @var $component \CatalogPropertyCreationFormComponent
 * @var $this \CBitrixComponentTemplate
 * @var $arResult array
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Extension::load([
	'catalog.entity-card',
]);

Loader::includeModule('ui');
Toolbar::deleteFavoriteStar();

$guid = 'property-creator';
$containerId = "{$guid}_container";
?>
<div id="<?=$containerId?>" class="catalog-property-creation-form-wrap"></div>
<script>
	BX(function() {
		var property = null;
		var propertyId = <?=(int)$component->getPropertyId()?>;
		if (propertyId > 0)
		{
			property = BX.UI.EntityEditorControlFactory.create(
				'<?=htmlspecialcharsbx($arResult['PROPERTY_SCHEME_TYPE'])?>',
				'' + propertyId,
				{
					model: BX.UI.EntityModel.create(propertyId, {}),
					schemeElement: BX.UI.EntitySchemeElement.create(<?=CUtil::PhpToJSObject($arResult['PROPERTY_SCHEME'])?>)
				}
			);
		}

		var configurator = BX.Catalog.PropertyCreationForm.create(
			'',
			{
				mode: BX.UI.EntityEditorMode.edit,
				// parent: parent,
				field: property,
				container: BX('<?=$containerId?>'),
				typeId: '<?=htmlspecialcharsbx($component->getPropertyType())?>',
				mandatoryConfigurator: null,
				componentName: '<?=$component->getName()?>',
				signedParameters: '<?=$component->getSignedParameters()?>'
			}
		);
		configurator.layout();
	});
</script>
<div style="clear: both;"></div>