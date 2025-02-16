import { CellLayout } from '../../layout/cell-layout';
import '../../../css/section/menu-cell.css';

export const MenuCell = {
	name: 'MenuCell',
	components: { CellLayout },
	template: `
		<CellLayout class="ui-access-rights-v2-menu-cell"/>
	`,
};
