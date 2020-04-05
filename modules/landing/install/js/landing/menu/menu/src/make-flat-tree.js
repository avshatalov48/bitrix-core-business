import {MenuItem} from 'landing.menu.menuitem';

export default function makeFlatTree(tree: Array<MenuItem>, acc = []): Array<MenuItem>
{
	tree.forEach((item) => {
		acc.push(item);
		makeFlatTree(item.children, acc);
	});

	return acc;
}