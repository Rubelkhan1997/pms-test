/**
 * Confirmation Dialog Plugin
 * Simple confirmation dialog for Vue 3
 */

import { App, ref, readonly, h, createVNode, render } from 'vue';

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmClass?: string;
    cancelClass?: string;
}

interface ConfirmDialog {
    id: number;
    title: string;
    message: string;
    confirmText: string;
    cancelText: string;
    confirmClass: string;
    cancelClass: string;
    resolve: (value: boolean) => void;
}

const dialogs = ref<ConfirmDialog[]>([]);
let dialogId = 0;

const DEFAULT_TITLE = 'Are you sure?';
const DEFAULT_CONFIRM_TEXT = 'Yes';
const DEFAULT_CANCEL_TEXT = 'No';
const DEFAULT_CONFIRM_CLASS = 'bg-red-600 hover:bg-red-700';
const DEFAULT_CANCEL_CLASS = 'bg-slate-200 hover:bg-slate-300 text-slate-800';

/**
 * Show confirmation dialog
 * Returns a promise that resolves to true (confirmed) or false (cancelled)
 */
function showConfirm(options: ConfirmOptions): Promise<boolean> {
    const {
        title = DEFAULT_TITLE,
        message,
        confirmText = DEFAULT_CONFIRM_TEXT,
        cancelText = DEFAULT_CANCEL_TEXT,
        confirmClass = DEFAULT_CONFIRM_CLASS,
        cancelClass = DEFAULT_CANCEL_CLASS
    } = options;

    return new Promise((resolve) => {
        const id = ++dialogId;

        dialogs.value.push({
            id,
            title,
            message,
            confirmText,
            cancelText,
            confirmClass,
            cancelClass,
            resolve
        });
    });
}

/**
 * Close dialog by ID
 */
function closeDialog(id: number, confirmed: boolean): void {
    const index = dialogs.value.findIndex(d => d.id === id);
    if (index !== -1) {
        const dialog = dialogs.value[index];
        dialog.resolve(confirmed);
        dialogs.value.splice(index, 1);
    }
}

/**
 * Clear all dialogs
 */
function clearDialogs(): void {
    dialogs.value.forEach(dialog => dialog.resolve(false));
    dialogs.value = [];
}

const confirm = {
    show: showConfirm,
    close: closeDialog,
    clear: clearDialogs,
    useDialogs: () => readonly(dialogs)
};

/**
 * Confirm Dialog Component
 */
const ConfirmDialogComponent = {
    name: 'ConfirmDialogComponent',
    setup() {
        return () => h('div', { class: 'fixed inset-0 z-50 flex items-center justify-center' }, [
            // Backdrop
            h('div', {
                class: 'absolute inset-0 bg-black bg-opacity-50 transition-opacity',
                onClick: () => clearDialogs()
            }),

            // Dialogs
            ...dialogs.value.map(d => h('div', {
                key: d.id,
                class: 'relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 animate-scale-in'
            }, [
                h('div', { class: 'p-6' }, [
                    // Title
                    h('h3', { class: 'text-lg font-semibold text-slate-900 mb-2' }, d.title),

                    // Message
                    h('p', { class: 'text-slate-600 mb-6' }, d.message),

                    // Actions
                    h('div', { class: 'flex justify-end gap-3' }, [
                        h('button', {
                            class: `px-4 py-2 rounded-lg transition ${d.cancelClass}`,
                            onClick: () => closeDialog(d.id, false)
                        }, d.cancelText),

                        h('button', {
                            class: `px-4 py-2 rounded-lg text-white transition ${d.confirmClass}`,
                            onClick: () => closeDialog(d.id, true)
                        }, d.confirmText)
                    ])
                ])
            ]))
        ]);
    }
};

/**
 * Install plugin
 */
export function install(app: App): void {
    app.config.globalProperties.$confirm = confirm;
    app.provide('confirm', confirm);

    if (typeof document !== 'undefined') {
        const container = document.createElement('div');
        container.id = 'confirm-dialog-container';
        document.body.appendChild(container);
        render(createVNode(ConfirmDialogComponent), container);
    }
}

// Type declarations
declare module 'vue' {
    interface ComponentCustomProperties {
        $confirm: typeof confirm;
    }
}

export { confirm, ConfirmDialogComponent };
export default { install };
