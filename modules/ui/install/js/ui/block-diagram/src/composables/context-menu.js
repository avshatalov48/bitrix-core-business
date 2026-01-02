import { ref, toValue, shallowRef } from 'ui.vue3';
import { Type } from 'main.core';
import { MenuItemOptions, MenuOptions, MenuManager } from 'main.popup';
import { useBlockDiagram } from './block-diagram';

export type UseContextMenu = {
	isOpen: boolean,
	showContextMenu: (payload: { clientX: number, clientY: number }) => void,
	closeContextMenu: () => void,
	setOptions: (options: MenuOptions) => void,
};

export function useContextMenu(menuItems: MenuItemOptions[] = []): UseContextMenu
{
	const {
		contextMenuLayerRef,
		targetContainerRef,
		isOpenContextMenu,
		positionContextMenu,
		contextMenuInstance,
		zoom,
	} = useBlockDiagram();

	contextMenuInstance.value = toValue(contextMenuInstance) || shallowRef(MenuManager.create(getMenuOptions()));
	const isOpen = ref(false);
	let options: MenuOptions = { items: menuItems };

	function setOptions(newOptions: MenuOptions): void
	{
		options = getMenuOptions(newOptions);
	}

	function getItems(items: MenuItemOptions[] = []): MenuItemOptions[]
	{
		return items.map((item) => {
			return {
				...item,
				onclick: () => {
					if (Type.isFunction(item.onclick))
					{
						item.onclick();
					}

					closeMenu();
				},
			};
		});
	}

	function getMenuOptions(additionalOptions: MenuOptions): MenuOptions
	{
		return {
			id: 'block-diagram-context-menu',
			bindElement: {
				left: 0,
				top: 0,
			},
			minWidth: 200,
			autoHide: true,
			draggable: false,
			cacheable: false,
			targetContainer: toValue(targetContainerRef),
			...additionalOptions,
			items: getItems(additionalOptions?.items ?? []),
		};
	}

	function setDestroyHandler(handler: () => void): void
	{
		toValue(contextMenuInstance)
			?.popupWindow
			?.subscribeOnce('onDestroy', handler);
	}

	function showMenu(menuOptions: MenuOptions): void
	{
		toValue(contextMenuInstance)?.destroy();
		contextMenuInstance.value = shallowRef(MenuManager.create(
			getMenuOptions(menuOptions),
		));
		toValue(contextMenuInstance).show();
	}

	function closeMenu(): void
	{
		toValue(contextMenuInstance)?.close();
	}

	function showContextMenu(payload: { clientX: number, clientY: number }): void
	{
		const { clientX = 0, clientY = 0 } = payload;
		const { left, top } = toValue(contextMenuLayerRef)?.getBoundingClientRect() ?? { top: 0, left: 0 };
		positionContextMenu.value.top = (clientY - top) / toValue(zoom);
		positionContextMenu.value.left = (clientX - left) / toValue(zoom);

		showMenu(options);
		setDestroyHandler(() => {
			isOpen.value = false;
		});

		isOpen.value = true;
		isOpenContextMenu.value = true;
	}

	function closeContextMenu(): void
	{
		isOpen.value = false;
		isOpenContextMenu.value = false;

		closeMenu();
	}

	return {
		isOpen,
		showContextMenu,
		closeContextMenu,
		setOptions,
	};
}
