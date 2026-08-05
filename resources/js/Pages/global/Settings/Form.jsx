import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Form({ auth }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Settings" />
            <div className="m-4 rounded-2xl bg-white p-6 shadow-sm sm:m-6">
                <h1 className="text-lg font-semibold text-slate-900">Settings</h1>
                <p className="mt-2 text-sm text-slate-600">Settings are now managed from the main settings page.</p>
                <Link href={route('dashboard.globalSettings.edit')} className="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Go to settings
                </Link>
            </div>
        </AuthenticatedLayout>
    );
}
