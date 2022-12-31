import { Event } from 'main.core';

/**
 * @memberof BX.UI.Uploader
 */
export const DragOverMixin = {
	directives: {
		drop: {
			beforeMount(el, binding, vnode)
			{
				function addClass()
				{
					binding.instance.dragOver = true;
					el.classList.add('--drag-over');
				}

				function removeClass()
				{
					binding.instance.dragOver = false;
					el.classList.remove('--drag-over');
				}

				let lastEnterTarget = null;
				Event.bind(el, 'dragenter', (event) => {
					event.preventDefault();
					event.stopPropagation();

					lastEnterTarget = event.target;
					addClass();
				});

				Event.bind(el, 'dragleave', (event) => {
					event.preventDefault();
					event.stopPropagation();

					if (lastEnterTarget === event.target)
					{
						removeClass();
					}
				});

				Event.bind(el, 'drop', (event) => {
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