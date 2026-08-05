import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import ProtectedNotice from '@/Components/ProtectedNotice';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import EmptyState from '@/Components/EmptyState';
import { Head, useForm } from '@inertiajs/react';

function SettingRow({ setting, formUrl, canEditProtected }) {
    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        key: setting.key,
        value: setting.value ?? '',
    });

    const locked = setting.is_protected && !canEditProtected;

    const submit = (e) => {
        e.preventDefault();
        if (locked) return;
        patch(formUrl, { preserveScroll: true });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="lg:w-1/3">
                    <p className="break-all font-semibold text-slate-900">{setting.key}</p>
                    <p className="mt-1 text-xs text-slate-500">Type: {setting.type} · Public: {setting.is_public ? 'yes' : 'no'} · Protected: {setting.is_protected ? 'yes' : 'no'}</p>
                    {locked && <p className="mt-2 text-xs font-semibold text-amber-700">Locked for Admin/demo accounts.</p>}
                </div>
                <div className="flex-1">
                    {setting.type === 'boolean' ? (
                        <select disabled={locked} value={String(data.value)} onChange={(e) => setData('value', e.target.value)} className="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100">
                            <option value="1">true</option>
                            <option value="0">false</option>
                        </select>
                    ) : setting.type === 'text' || String(data.value).length > 120 ? (
                        <textarea disabled={locked} value={data.value} rows="3" onChange={(e) => setData('value', e.target.value)} className="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100" />
                    ) : (
                        <input disabled={locked} value={data.value} onChange={(e) => setData('value', e.target.value)} className="block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100" />
                    )}
                    <InputError message={errors.value || errors.key} className="mt-2" />
                    {recentlySuccessful && <p className="mt-2 text-xs font-semibold text-emerald-700">Saved.</p>}
                </div>
                <PrimaryButton disabled={processing || locked}>Save</PrimaryButton>
            </div>
        </form>
    );
}

export default function List({ auth, pageTitle, pageDescription, settings = {}, canEditProtected = false, formUrl }) {
    const groups = Object.entries(settings);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Settings' }]}
                />
                <ProtectedNotice show={!canEditProtected}>
                    You can edit non-critical settings only. Protected settings are locked to avoid breaking public demo credentials, reset behavior, footer text, and system identity.
                </ProtectedNotice>
                <div className="mt-6 space-y-8">
                    {groups.length === 0 ? (
                        <EmptyState title="No settings found" description="Run the database seeders to create default BookHive settings." />
                    ) : groups.map(([group, groupSettings]) => (
                        <section key={group}>
                            <h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-indigo-700">{group}</h2>
                            <div className="space-y-3">
                                {groupSettings.map((setting) => <SettingRow key={setting.key} setting={setting} formUrl={formUrl} canEditProtected={canEditProtected} />)}
                            </div>
                        </section>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
