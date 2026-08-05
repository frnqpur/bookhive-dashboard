import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel.jsx';
import TextInput from '@/Components/TextInput.jsx';
import InputError from '@/Components/InputError.jsx';
import PrimaryButton from '@/Components/PrimaryButton.jsx';
import PageHeader from '@/Components/PageHeader';
import ProtectedNotice from '@/Components/ProtectedNotice';
import { XCircleIcon } from '@heroicons/react/20/solid/index.js';

export default function Form({ auth, pageTitle, pageDescription, pageData, permissionsList = [], formUrl }) {
    const rolePermissions = pageData?.permissions?.map((permission) => permission.name) || [];
    const { data, setData, patch, processing, errors } = useForm({
        name: pageData?.name || '',
        slug: pageData?.slug || '',
        description: pageData?.description || '',
        is_active: pageData?.is_active ?? true,
        guard_name: pageData?.guard_name || 'web',
        user_type: pageData?.user_type || 'customer',
        record_access: pageData?.record_access || 'owned',
        permissions: rolePermissions,
    });

    const togglePermission = (permissionName) => {
        setData('permissions', data.permissions.includes(permissionName)
            ? data.permissions.filter((name) => name !== permissionName)
            : [...data.permissions, permissionName]);
    };

    const submit = (e) => {
        e.preventDefault();
        patch(formUrl);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Roles', href: route('dashboard.global.roles.list') }, { label: pageData ? 'Edit' : 'Create' }]}
                />
                <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <ProtectedNotice show={pageData?.is_protected}>This core role is protected. Unsafe changes are blocked by backend policies.</ProtectedNotice>
                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="name" value="Name" />
                            <TextInput id="name" value={data.name} className="mt-2 block w-full" onChange={(e) => setData('name', e.target.value)} isFocused />
                            <InputError message={errors.name} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="slug" value="Slug" />
                            <TextInput id="slug" value={data.slug} className="mt-2 block w-full" onChange={(e) => setData('slug', e.target.value)} />
                            <InputError message={errors.slug} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="is_active" value="Active" />
                            <select id="is_active" value={data.is_active ? '1' : '0'} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('is_active', e.target.value === '1')}>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                            <InputError message={errors.is_active} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="guard_name" value="Guard" />
                            <TextInput id="guard_name" value={data.guard_name} className="mt-2 block w-full" onChange={(e) => setData('guard_name', e.target.value)} />
                            <InputError message={errors.guard_name} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="user_type" value="User Type" />
                            <select id="user_type" value={data.user_type} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('user_type', e.target.value)}>
                                <option value="internal">internal</option>
                                <option value="customer">customer</option>
                            </select>
                            <InputError message={errors.user_type} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="record_access" value="Record Access" />
                            <select id="record_access" value={data.record_access} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('record_access', e.target.value)}>
                                <option value="all">all</option>
                                <option value="owned">owned</option>
                            </select>
                            <InputError message={errors.record_access} className="mt-2" />
                        </div>
                        <div className="md:col-span-2">
                            <InputLabel htmlFor="description" value="Description" />
                            <textarea id="description" value={data.description} rows="4" className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('description', e.target.value)} />
                            <InputError message={errors.description} className="mt-2" />
                        </div>
                    </div>
                    <div>
                        <h2 className="text-sm font-semibold text-slate-900">Permissions</h2>
                        <InputError message={errors.permissions} className="mt-2" />
                        <div className="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            {permissionsList.map((permission) => (
                                <label key={permission.name} className="flex items-start gap-2 rounded-xl border border-slate-200 p-3 text-sm">
                                    <input type="checkbox" checked={data.permissions.includes(permission.name)} onChange={() => togglePermission(permission.name)} className="mt-1 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    <span><span className="font-medium text-slate-900">{permission.name}</span><span className="block text-xs text-slate-500">{permission.description}</span></span>
                                </label>
                            ))}
                        </div>
                    </div>
                    <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                        <Link href={route('dashboard.global.roles.list')} className="inline-flex items-center justify-center gap-1.5 rounded-md bg-slate-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-500"><XCircleIcon className="h-5 w-5" />Cancel</Link>
                        <PrimaryButton disabled={processing}>Submit</PrimaryButton>
                    </div>
                </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
