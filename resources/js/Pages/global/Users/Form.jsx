import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel.jsx';
import TextInput from '@/Components/TextInput.jsx';
import InputError from '@/Components/InputError.jsx';
import PrimaryButton from '@/Components/PrimaryButton.jsx';
import PageHeader from '@/Components/PageHeader';
import ProtectedNotice from '@/Components/ProtectedNotice';
import { XCircleIcon } from '@heroicons/react/20/solid/index.js';

export default function Form({ auth, pageTitle, pageDescription, pageData, rolesList = [], statuses = ['active', 'disabled'], formUrl }) {
    const { data, setData, patch, processing, errors, reset } = useForm({
        name: pageData?.name || '',
        email: pageData?.email || '',
        password: '',
        password_confirmation: '',
        roles: pageData?.roles?.length > 0 ? pageData.roles[0].name : '',
        status: pageData?.status || 'active',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(formUrl, { onSuccess: () => reset('password', 'password_confirmation') });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Users', href: route('dashboard.global.users.list') }, { label: pageData ? 'Edit' : 'Create' }]}
                />
                <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <ProtectedNotice show={pageData?.is_protected}>This protected account cannot be changed in ways that would break the public demo.</ProtectedNotice>
                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="roles" value="Assign Role" />
                            <select id="roles" value={data.roles} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('roles', e.target.value)}>
                                <option value="">Please select role</option>
                                {rolesList.map((role) => <option key={role.id || role.name} value={role.name}>{role.name}</option>)}
                            </select>
                            <InputError message={errors.roles} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="status" value="Status" />
                            <select id="status" value={data.status} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('status', e.target.value)}>
                                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                            </select>
                            <InputError message={errors.status} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="name" value="Name" />
                            <TextInput id="name" value={data.name} className="mt-2 block w-full" autoComplete="name" isFocused onChange={(e) => setData('name', e.target.value)} />
                            <InputError message={errors.name} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="email" value="Email" />
                            <TextInput id="email" type="email" value={data.email} className="mt-2 block w-full" autoComplete="username" onChange={(e) => setData('email', e.target.value)} />
                            <InputError message={errors.email} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="password" value={pageData ? 'New Password (leave empty to keep current)' : 'Password'} />
                            <TextInput id="password" type="password" value={data.password} className="mt-2 block w-full" autoComplete="new-password" onChange={(e) => setData('password', e.target.value)} />
                            <InputError message={errors.password} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                            <TextInput id="password_confirmation" type="password" value={data.password_confirmation} className="mt-2 block w-full" autoComplete="new-password" onChange={(e) => setData('password_confirmation', e.target.value)} />
                            <InputError message={errors.password_confirmation} className="mt-2" />
                        </div>
                    </div>
                    <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                        <Link href={route('dashboard.global.users.list')} className="inline-flex items-center justify-center gap-1.5 rounded-md bg-slate-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-500">
                            <XCircleIcon className="h-5 w-5" />
                            Cancel
                        </Link>
                        <PrimaryButton disabled={processing}>Submit</PrimaryButton>
                    </div>
                </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
