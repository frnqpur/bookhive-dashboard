import { router } from '@inertiajs/react';
import Swal from 'sweetalert2';

const variants = {
    danger: 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
    warning: 'bg-amber-600 text-white hover:bg-amber-500 focus-visible:outline-amber-600',
    success: 'bg-emerald-600 text-white hover:bg-emerald-500 focus-visible:outline-emerald-600',
    primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
    secondary: 'bg-slate-700 text-white hover:bg-slate-600 focus-visible:outline-slate-700',
};

export default function ConfirmActionButton({
    href,
    method = 'post',
    data = {},
    title = 'Confirm this action?',
    text = 'This action will be processed immediately.',
    confirmText = 'Yes, continue',
    cancelText = 'Cancel',
    successTitle = 'Action completed',
    errorTitle = 'Action failed',
    errorText = 'You may not have permission to perform this action.',
    variant = 'primary',
    disabled = false,
    disabledReason = 'This action is disabled for your account or this protected record.',
    confirmationKeyword = null,
    confirmationLabel = null,
    confirmationPlaceholder = null,
    className = '',
    children,
}) {
    const runAction = () => {
        if (disabled) {
            Swal.fire('Action disabled', disabledReason, 'info');
            return;
        }

        Swal.fire({
            title,
            text,
            icon: variant === 'danger' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            allowOutsideClick: false,
            input: confirmationKeyword ? 'text' : undefined,
            inputLabel: confirmationKeyword ? (confirmationLabel || `Type ${confirmationKeyword} to confirm`) : undefined,
            inputPlaceholder: confirmationKeyword ? (confirmationPlaceholder || confirmationKeyword) : undefined,
            inputValidator: confirmationKeyword
                ? (value) => (value === confirmationKeyword ? undefined : `Type ${confirmationKeyword} exactly to continue.`)
                : undefined,
        }).then((result) => {
            if (!result.isConfirmed) return;

            const payload = confirmationKeyword ? { ...data, confirmation: result.value } : data;

            const options = {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: successTitle,
                        showConfirmButton: false,
                        timer: 1600,
                    });
                },
                onError: () => Swal.fire(errorTitle, errorText, 'error'),
            };

            if (method.toLowerCase() === 'delete') {
                router.delete(href, options);
                return;
            }

            if (method.toLowerCase() === 'patch') {
                router.patch(href, payload, options);
                return;
            }

            if (method.toLowerCase() === 'put') {
                router.put(href, payload, options);
                return;
            }

            router.post(href, payload, options);
        });
    };

    return (
        <button
            type="button"
            onClick={runAction}
            className={`inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 ${variants[variant] || variants.primary} ${disabled ? 'cursor-not-allowed opacity-60' : ''} ${className}`}
        >
            {children}
        </button>
    );
}
