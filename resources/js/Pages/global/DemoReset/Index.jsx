import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import ConfirmActionButton from '@/Components/ConfirmActionButton';
import EmptyState from '@/Components/EmptyState';
import Paginator from '@/Components/Paginator';
import StatusBadge from '@/Components/StatusBadge';
import { Head, router } from '@inertiajs/react';
import { ArrowPathIcon, ClockIcon, CommandLineIcon, ShieldCheckIcon } from '@heroicons/react/20/solid';

function SummaryList({ summary = {} }) {
    const entries = Object.entries(summary || {}).filter(([, value]) => value !== null && value !== undefined && typeof value !== 'object');

    if (entries.length === 0) return null;

    return (
        <dl className="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">
            {entries.slice(0, 6).map(([key, value]) => (
                <div key={key} className="rounded-xl bg-slate-50 px-3 py-2">
                    <dt className="font-semibold capitalize text-slate-500">{key.replaceAll('_', ' ')}</dt>
                    <dd className="mt-1 font-bold text-slate-900">{String(value)}</dd>
                </div>
            ))}
        </dl>
    );
}

export default function Index({
    auth,
    pageTitle,
    pageDescription,
    logs = { data: [] },
    lastReset = null,
    nextReset = null,
    serverTime,
    resetUrl,
    requiredConfirmation = 'RESET',
    artisanCommand = 'php artisan demo:reset',
    cronCommand = 'php /home/USER/path-to-project/artisan schedule:run >> /dev/null 2>&1',
}) {
    const rows = logs.data || [];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'System Tools' }, { label: pageTitle }]}
                    actions={(
                        <ConfirmActionButton
                            href={resetUrl}
                            method="post"
                            variant="danger"
                            title="Reset public demo data?"
                            text="This will remove public-created demo data and restore BookHive protected defaults. Type RESET to confirm."
                            confirmText="Reset Demo Data"
                            successTitle="Demo reset completed"
                            errorTitle="Demo reset failed"
                            confirmationKeyword={requiredConfirmation}
                            confirmationLabel="Type RESET exactly to confirm"
                            confirmationPlaceholder="RESET"
                        >
                            <ArrowPathIcon className="h-5 w-5" />
                            Reset Demo Data
                        </ConfirmActionButton>
                    )}
                />

                <div className="grid grid-cols-1 gap-4 xl:grid-cols-4">
                    <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
                        <div className="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-indigo-600">
                            <ClockIcon className="h-5 w-5" />
                            Reset status
                        </div>
                        <div className="mt-4 space-y-3 text-sm text-slate-600">
                            <div className="rounded-2xl bg-slate-50 p-4">
                                <p className="font-semibold text-slate-900">Last reset</p>
                                {lastReset ? (
                                    <>
                                        <div className="mt-2 flex flex-wrap items-center gap-2">
                                            <StatusBadge status={lastReset.status} />
                                            <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{lastReset.trigger_type}</span>
                                        </div>
                                        <p className="mt-2 text-xs text-slate-500">Finished {lastReset.finished_at || '-'}</p>
                                        <p className="mt-1 text-xs text-slate-500">By {lastReset.triggered_by || 'Scheduler'}</p>
                                        <SummaryList summary={lastReset.summary} />
                                    </>
                                ) : (
                                    <p className="mt-2 text-xs text-slate-500">No successful reset has been recorded yet.</p>
                                )}
                            </div>

                            <div className="rounded-2xl bg-indigo-50 p-4 text-indigo-900">
                                <p className="font-semibold">Next scheduled reset</p>
                                <p className="mt-1 text-sm">{nextReset?.at || 'After the next scheduler run'}</p>
                                <p className="mt-1 text-xs text-indigo-700">{nextReset?.human || 'Laravel scheduler runs every six hours.'}</p>
                            </div>

                            <div className="rounded-2xl bg-slate-900 p-4 text-slate-100">
                                <div className="flex items-center gap-2 font-semibold">
                                    <CommandLineIcon className="h-5 w-5" />
                                    Commands
                                </div>
                                <p className="mt-3 text-xs uppercase tracking-wide text-slate-400">Manual CLI</p>
                                <code className="mt-1 block break-all rounded-lg bg-white/10 p-2 text-xs">{artisanCommand}</code>
                                <p className="mt-3 text-xs uppercase tracking-wide text-slate-400">cPanel cron</p>
                                <code className="mt-1 block break-all rounded-lg bg-white/10 p-2 text-xs">{cronCommand}</code>
                                <p className="mt-2 text-xs text-slate-400">Server time: {serverTime}</p>
                            </div>
                        </div>
                    </section>

                    <div className="space-y-4 xl:col-span-3">
                        <div className="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-800">
                            <div className="flex items-center gap-2 font-bold text-amber-900">
                                <ShieldCheckIcon className="h-5 w-5" />
                                Protected reset rules
                            </div>
                            <div className="mt-3 grid gap-3 md:grid-cols-2">
                                <ul className="list-disc space-y-2 pl-5">
                                    <li>Real Super Admin is never deleted, reset, exposed, or converted into demo data.</li>
                                    <li>Real Super Admin email/password are not changed by <code className="font-semibold">demo:reset</code>.</li>
                                    <li>Protected demo accounts are restored with known public demo credentials.</li>
                                    <li>Core roles, permissions, and role-permission assignments are restored.</li>
                                </ul>
                                <ul className="list-disc space-y-2 pl-5">
                                    <li>Public registered users and public-created non-protected data are removed.</li>
                                    <li>Protected seeded books and reviews are restored to a known safe state.</li>
                                    <li>Uploaded public cover files are cleaned only when no remaining book references them.</li>
                                    <li>Only Super Admin can access this page or trigger a manual reset.</li>
                                </ul>
                            </div>
                        </div>

                        <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 className="text-lg font-semibold text-slate-900">Reset history</h2>
                            <p className="mt-1 text-sm text-slate-600">Latest scheduled and manual reset attempts. The private owner email is never shown here.</p>
                            <div className="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                                {rows.length === 0 ? (
                                    <div className="p-6"><EmptyState title="No reset logs yet" description="Run the manual reset or wait for the scheduled reset to create the first log." /></div>
                                ) : (
                                    <div className="divide-y divide-slate-100">
                                        {rows.map((log) => (
                                            <article key={log.id} className="p-4">
                                                <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                    <div className="min-w-0">
                                                        <div className="flex flex-wrap items-center gap-2">
                                                            <StatusBadge status={log.status} />
                                                            <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{log.trigger_type}</span>
                                                        </div>
                                                        <p className="mt-2 text-sm font-semibold text-slate-900">{log.message}</p>
                                                        <p className="mt-1 text-xs text-slate-500">Triggered by {log.triggered_by || 'Scheduler'} · Started {log.started_at || '-'}</p>
                                                        <SummaryList summary={log.summary} />
                                                    </div>
                                                    <p className="shrink-0 text-xs text-slate-500">Finished {log.finished_at || '-'}</p>
                                                </div>
                                            </article>
                                        ))}
                                    </div>
                                )}
                            </div>
                            {logs.meta && rows.length > 0 && (
                                <div className="mt-4 border-t border-slate-200 pt-4">
                                    <Paginator pagination={logs.meta} pageChanged={(page) => router.get(route('dashboard.demoReset.index'), { page }, { preserveScroll: true })} />
                                </div>
                            )}
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
