import {Reflection} from 'main.core';

/**
 * @memberOf BX.Grid
 */
export class CellActionState
{
	static SHOW_BY_HOVER = 'main-grid-cell-content-action-by-hover';
	static ACTIVE = 'main-grid-cell-content-action-active';
}

const namespace = Reflection.namespace('BX.Grid');
namespace.CellActionState = CellActionState;