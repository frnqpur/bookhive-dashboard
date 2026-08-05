import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import ApiDocumentation from '@/Components/Api/ApiDocumentation';
import { Head } from '@inertiajs/react';

export default function Index({ auth, pageTitle, pageDescription, ...props }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: pageTitle }]}
                />
                <ApiDocumentation {...props} />
            </div>
        </AuthenticatedLayout>
    );
}
