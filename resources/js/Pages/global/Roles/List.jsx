import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';
import { Head, Link } from '@inertiajs/react';
import { PlusCircleIcon } from '@heroicons/react/20/solid';

export default function List({ auth, pageTitle, pageDescription }) {
    const actionUrls = {
        createEditRouteName: 'dashboard.global.roles.create',
        removeRouteName: 'dashboard.global.roles.remove',
        editRouteName: 'dashboard.global.roles.edit',
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Roles' }]}
                    actions={(
                        <Link href={route(actionUrls.createEditRouteName)} className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <PlusCircleIcon className="h-5 w-5" aria-hidden="true" />
                            Add role
                        </Link>
                    )}
                />

                <DataTable
                    excludedColumns={['id']}
                    fetchUrl={route('fetch.roles')}
                    columns={['id', 'name', 'slug', 'description', 'is_active', 'is_core', 'is_protected', 'user_type', 'record_access', 'created_at']}
                    actionUrls={actionUrls}
                    mobileTitleColumn="name"
                    mobileSubtitleColumn="description"
                />
            </div>
        </AuthenticatedLayout>
    );
}
