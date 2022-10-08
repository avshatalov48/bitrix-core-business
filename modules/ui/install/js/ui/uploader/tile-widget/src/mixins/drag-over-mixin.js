import { Event } from 'main.core';

export const DragOverMixin = {
	directives: {
		drop: {
			bind(el, binding, vnode)
			{
				function addClass()
				{
					vnode.context.dragOver = true;
					el.classList.add('--drag-over');
				}

				function removeClass()
				{
					vnode.context.dragOver = false;
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

			unbind(el, binding, vnode)
			{
				vnode.context.dragOver = false;
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