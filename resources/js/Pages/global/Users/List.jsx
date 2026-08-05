import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';
import { Head, Link } from '@inertiajs/react';
import { PlusCircleIcon } from '@heroicons/react/20/solid';

export default function List({ auth, pageTitle, pageDescription }) {
    const actionUrls = {
        createEditRouteName: 'dashboard.global.users.create',
        removeRouteName: 'dashboard.global.users.remove',
        editRouteName: 'dashboard.global.users.edit',
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Users' }]}
                    actions={(
                        <Link href={route(actionUrls.createEditRouteName)} className="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            <PlusCircleIcon className="h-5 w-5" aria-hidden="true" />
                            Add user
                        </Link>
                    )}
                />

                <DataTable
                    excludedColumns={['id']}
                    fetchUrl={route('fetch.users')}
                    columns={['id', 'name', 'roles', 'email', 'status', 'is_demo', 'is_protected', 'created_at']}
                    actionUrls={actionUrls}
                    mobileTitleColumn="name"
                    mobileSubtitleColumn="email"
                />
            </div>
        </AuthenticatedLayout>
    );
}
