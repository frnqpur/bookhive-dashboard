import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import Paginator from '@/Components/Paginator';
import StatusBadge from '@/Components/StatusBadge.jsx';
import { Head, router, useForm } from '@inertiajs/react';
import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';

function JsonPreview({ value }) {
    if (!value || Object.keys(value).length === 0) {
        return <span className="text-slate-400">-</span>;
    }

    return <pre className="max-h-36 overflow-auto rounded-xl bg-slate-950 p-3 text-xs text-slate-100">{JSON.stringify(value, null, 2)}</pre>;
}

function actionTone(action = '') {
    if (action.includes('delete') || action.includes('reject') || action.includes('failed')) return 'rejected';
    if (action.includes('approve') || action.includes('login') || action.includes('create')) return 'approved';
    if (action.includes('reset') || action.includes('update') || action.includes('edit')) return 'pending';
    return action;
}

export default function Index({ auth, pageTitle, pageDescription, logs = { data: [] }, filters = {}, actions = [], users = [] }) {
    const rows = logs.data || [];
    const { data, setData, get, processing, reset } = useForm({
        search: filters.search || '',
        action: filters.action || '',
        user_id: filters.user_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('dashboard.auditLogs.index'), { preserveState: true, preserveScroll: true });
    };

    const clearFilters = () => {
        reset();
        router.get(route('dashboard.auditLogs.index'), {}, { preserveScroll: true });
    };

    const pageChanged = (page) => {
        router.get(route('dashboard.auditLogs.index'), { ...data, page }, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="space-y-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: pageTitle }]}
                />

                <section className="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <form onSubmit={submit} className="grid grid-cols-1 gap-3 lg:grid-cols-6">
                        <div className="lg:col-span-2">
                            <label className="text-xs font-bold uppercase tracking-wide text-slate-500">Search</label>
                            <div className="relative mt-1">
                                <MagnifyingGlassIcon className="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-slate-400" />
                                <input
                                    value={data.search}
                                    onChange={(e) => setData('search', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2 pl-10 text-sm text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600"
                                    placeholder="Action, description, entity, IP..."
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-bold uppercase tracking-wide text-slate-500">Action</label>
                            <select value={data.action} onChange={(e) => setData('action', e.target.value)} className="mt-1 w-full rounded-xl border-0 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                                <option value="">All actions</option>
                                {actions.map((action) => <option key={action} value={action}>{action}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="text-xs font-bold uppercase tracking-wide text-slate-500">User</label>
                            <select value={data.user_id} onChange={(e) => setData('user_id', e.target.value)} className="mt-1 w-full rounded-xl border-0 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
                                <option value="">All users</option>
                                {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="text-xs font-bold uppercase tracking-wide text-slate-500">From</label>
                            <input type="date" value={data.date_from} onChange={(e) => setData('date_from', e.target.value)} className="mt-1 w-full rounded-xl border-0 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        </div>
                        <div>
                            <label className="text-xs font-bold uppercase tracking-wide text-slate-500">To</label>
                            <input type="date" value={data.date_to} onChange={(e) => setData('date_to', e.target.value)} className="mt-1 w-full rounded-xl border-0 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                        </div>
                        <div className="flex flex-wrap items-end gap-2 lg:col-span-6">
                            <button type="submit" disabled={processing} className="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60">Apply filters</button>
                            <button type="button" onClick={clearFilters} className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:border-indigo-400 hover:text-indigo-600">Clear</button>
                        </div>
                    </form>
                </section>

                <section className="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    {rows.length === 0 ? (
                        <EmptyState title="No audit logs found" description="Audit records will appear here after login, registration, CRUD, moderation, settings, and reset actions are logged." />
                    ) : (
                        <div className="space-y-4">
                            {rows.map((log) => (
                                <article key={log.id} className="rounded-2xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:shadow-sm">
                                    <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <StatusBadge status={actionTone(log.action)} />
                                                <p className="text-sm font-black capitalize text-slate-950">{log.action}</p>
                                            </div>
                                            <p className="mt-2 text-sm leading-6 text-slate-600">{log.description || 'No description provided.'}</p>
                                            <p className="mt-2 text-xs text-slate-500">
                                                {log.entity_type || 'Record'} #{log.entity_id || '-'} · {log.created_at}
                                            </p>
                                            <p className="mt-1 text-xs text-slate-500">User: {log.user || 'System'} · IP: {log.ip_address || '-'}</p>
                                        </div>
                                    </div>
                                    <div className="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                                        <div>
                                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Old values</p>
                                            <JsonPreview value={log.old_values} />
                                        </div>
                                        <div>
                                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">New values</p>
                                            <JsonPreview value={log.new_values} />
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}

                    {logs.meta && rows.length > 0 && (
                        <div className="mt-4 border-t border-slate-200 pt-4">
                            <Paginator pagination={logs.meta} pageChanged={pageChanged} />
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
