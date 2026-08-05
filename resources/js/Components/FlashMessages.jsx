import { usePage } from '@inertiajs/react';

export default function FlashMessages({ className = '' }) {
    const { flash = {} } = usePage().props;

    if (!flash.success && !flash.error && !flash.status) {
        return null;
    }

    return (
        <div className={`space-y-3 ${className}`}>
            {flash.success && (
                <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {flash.error}
                </div>
            )}
            {flash.status && (
                <div className="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-800">
                    {flash.status}
                </div>
            )}
        </div>
    );
}
