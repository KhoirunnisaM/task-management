import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import Sortable from 'sortablejs';

window.Alpine = Alpine;

Alpine.plugin(focus);

Alpine.data('kanbanColumn', ({ status }) => ({
    sortable: null,

    init() {
        this.sortable = new Sortable(this.$el, {
            group: 'kanban',
            animation: 180,
            draggable: '[data-draggable="true"]',
            filter: 'a, button, input, select, textarea',
            preventOnFilter: false,
            ghostClass: 'opacity-40',
            dragClass: 'rotate-1',
            onEnd: (event) => {
                if (event.from === event.to && event.oldIndex === event.newIndex) {
                    return;
                }

                this.$wire.moveTask(
                    Number(event.item.dataset.taskId),
                    status,
                    event.newIndex,
                );
            },
        });
    },

    destroy() {
        this.sortable?.destroy();
    },
}));

Alpine.start();
