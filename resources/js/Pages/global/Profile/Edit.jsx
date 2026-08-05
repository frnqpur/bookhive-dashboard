import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import ProtectedNotice from '@/Components/ProtectedNotice';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { Head } from '@inertiajs/react';

export default function Edit({ auth, mustVerifyEmail, status, isProtectedAccount = false, isDemoAccount = false, canChangeEmail = true, canChangePassword = true, canDeleteAccount = true }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Profile" />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title="Profile"
                    description="Manage your own BookHive profile. Demo credentials are protected so the public portfolio remains usable."
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Profile' }]}
                />

                <ProtectedNotice show={isProtectedAccount || isDemoAccount}>
                    This account is protected. Name updates may be allowed, but demo emails, demo passwords, and protected account deletion are locked.
                </ProtectedNotice>

                <div className="mt-6 space-y-6">
                    <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            canChangeEmail={canChangeEmail}
                            className="max-w-2xl"
                        />
                    </div>

                    <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                        <UpdatePasswordForm canChangePassword={canChangePassword} className="max-w-2xl" />
                    </div>

                    <div className="rounded-3xl border border-red-100 bg-white p-5 shadow-sm sm:p-8">
                        <DeleteUserForm canDeleteAccount={canDeleteAccount} className="max-w-2xl" />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
