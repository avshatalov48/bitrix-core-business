/**
 * @module ui
 * @version 1.0
 * @copyright 2001-2019 Bitrix
 */

BX.namespace("BX.UI");

//region ENTITY CONFIGURATION SCOPE
if(typeof BX.UI.EntityConfigScope === "undefined")
{
	BX.UI.EntityConfigScope =
	{
		undefined: '',
		personal:  'P',
		common: 'C',
		custom: 'CUSTOM'
	};

	BX.UI.EntityConfigScope.getCaption = function(scope)
	{
		/*
		* Messages are used:
		* UI_ENTITY_EDITOR_CONFIG_SCOPE_COMMON
		*
		*/
		return BX.message(
			"UI_ENTITY_EDITOR_CONFIG_SCOPE_" + (scope === BX.UI.EntityConfigScope.common ? "COMMON" : "PERSONAL") + "_MSGVER_1"
		);
	};
}
//endregion