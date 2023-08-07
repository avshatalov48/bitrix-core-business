import { Event } from 'main.core';
import { hasDataTransferOnlyFiles } from 'ui.uploader.core';
/**
 * @memberof BX.UI.Uploader
 */
export const DragOverMixin = {
	directives: {
		drop: {
			beforeMount(el, binding, vnode): void
			{
				function addClass(): void
				{
					binding.instance.dragOver = true;
					el.classList.add('--drag-over');
				}

				function removeClass(): void
				{
					binding.instance.dragOver = false;
					el.classList.remove('--drag-over');
				}

				let lastEnterTarget = null;
				Event.bind(el, 'dragenter', (event: DragEvent): void => {
					hasDataTransferOnlyFiles(event.dataTransfer, false).then((success): void => {
						if (success)
						{
							event.preventDefault();
							event.stopPropagation();

							lastEnterTarget = event.target;
							addClass();
						}
					});
				});

				Event.bind(el, 'dragleave', (event: DragEvent): void => {
					event.preventDefault();
					event.stopPropagation();

					if (lastEnterTarget === event.target)
					{
						removeClass();
					}
				});

				Event.bind(el, 'drop', (event: DragEvent): void => {
					removeClass();
				});
			},

			unmounted(el, binding, vnode)
			{
				binding.instance.dragOver = false;
				Event.unbindAll(el, 'dragenter');
				Event.unbindAll(el, 'dragleave');
				Event.unbindAll(el, 'drop');
			}
		},
	},
	data()
	{
		return {
			dragOver: false,
		}
	},
 };