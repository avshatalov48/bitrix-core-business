import {Reflection} from 'main.core';

/**
 * @memberOf BX.Grid
 */
export class CellActions
{
	static PIN = 'main-grid-cell-content-action-pin';
	static MUTE = 'main-grid-cell-content-action-mute';
}

const namespace = Reflection.namespace('BX.Grid');
namespace.CellActions = CellActions;