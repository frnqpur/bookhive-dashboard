import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';
import { Head, Link } from '@inertiajs/react';
import { PlusCircleIcon } from '@heroicons/react/20/solid';

export default function List({ auth, pageTitle, pageDescription, canCreate = false, fetchUrl = null, listMode = 'all' }) {
    const actionUrls = {
        createEditRouteName: 'dashboard.be.bookReviews.create',
        removeRouteName: 'dashboard.be.bookReviews.remove',
        editRouteName: 'dashboard.be.bookReviews.edit',
        moderateRouteName: 'dashboard.be.bookReviews.moderate',
        moderationPageRouteName: 'dashboard.be.bookReviews.moderation.show',
    };

    const tabs = [
        { label: 'All visible', href: route('dashboard.be.bookReviews.list'), active: listMode === 'all', show: true },
        { label: 'My reviews', href: route('dashboard.be.bookReviews.my'), active: listMode === 'mine', show: auth.can?.createReviews || auth.can?.updateOwnReviews || auth.can?.manageReviews },
        { label: 'Moderation queue', href: route('dashboard.be.bookReviews.moderation'), active: listMode === 'moderation', show: auth.can?.approveReviews },
    ].filter((tab) => tab.show);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Reviews' }]}
                    actions={canCreate && (
                        <Link href={route(actionUrls.createEditRouteName)} className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <PlusCircleIcon className="h-5 w-5" aria-hidden="true" />
                            Add review
                        </Link>
                    )}
                />

                <div className="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                    {tabs.map((tab) => (
                        <Link key={tab.href} href={tab.href} className={`rounded-xl px-3 py-2 text-sm font-semibold ${tab.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'}`}>
                            {tab.label}
                        </Link>
                    ))}
                </div>

                <DataTable
                    excludedColumns={['id']}
                    fetchUrl={fetchUrl || route('fetch.bookReviews')}
                    columns={['id', 'title', 'rating', 'status', 'book', 'created_by_user', 'approved_by_user', 'created_at']}
                    actionUrls={actionUrls}
                    mobileTitleColumn="title"
                    mobileSubtitleColumn="book"
                />
            </div>
        </AuthenticatedLayout>
    );
}
