import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';
import { Head, Link } from '@inertiajs/react';
import { PlusCircleIcon } from '@heroicons/react/20/solid';

export default function List({ auth, pageTitle, pageDescription, canCreate = false }) {
    const actionUrls = {
        createEditRouteName: 'dashboard.be.books.create',
        removeRouteName: 'dashboard.be.books.remove',
        editRouteName: 'dashboard.be.books.edit',
        showRouteName: 'dashboard.be.books.show',
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Books' }]}
                    actions={canCreate && (
                        <Link href={route(actionUrls.createEditRouteName)} className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <PlusCircleIcon className="h-5 w-5" aria-hidden="true" />
                            Add book
                        </Link>
                    )}
                />

                <DataTable
                    excludedColumns={['id', 'slug']}
                    fetchUrl={route('fetch.books')}
                    columns={['id', 'cover_url', 'title', 'author', 'category', 'status', 'average_rating', 'total_reviews', 'published_year', 'created_at']}
                    actionUrls={actionUrls}
                    canEdit={canCreate}
                    canDelete={canCreate}
                    mobileTitleColumn="title"
                    mobileSubtitleColumn="author"
                />
            </div>
        </AuthenticatedLayout>
    );
}
