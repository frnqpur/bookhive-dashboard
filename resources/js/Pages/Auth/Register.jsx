import { useEffect } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

const fallbackDescriptions = {
    Admin: 'Can manage users, roles, permissions, books, reviews, and settings except protected owner actions.',
    Editor: 'Can manage the book catalog and moderate reviews without user/permission administration.',
    Reviewer: 'Can access books, create reviews, and manage review workflows without administrative access.',
    Customer: 'Can explore books, create personal reviews, and update their own profile with customer-level access.',
};

export default function Register({ publicRoles = ['Customer'], defaultRole = 'Customer', roleDescriptions = fallbackDescriptions }) {
    const safeRoles = publicRoles.filter((role) => role !== 'Super Admin');
    const initialRole = safeRoles.includes(defaultRole) ? defaultRole : safeRoles[0] || 'Customer';

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        role: initialRole,
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('register'));
    };

    return (
        <GuestLayout>
            <Head>
                <title>Register - BookHive Dashboard</title>
                <meta
                    name="description"
                    content="Register for the BookHive Dashboard public demo and choose an Admin, Editor, Reviewer, or Customer role."
                />
                <meta property="og:title" content="Register for BookHive Dashboard" />
                <meta property="og:description" content="Create a public demo account for a Laravel React/Inertia portfolio dashboard." />
                <meta property="og:type" content="website" />
            </Head>

            <div className="mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl grid-cols-1 gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                <section className="flex items-center">
                    <div>
                        <Link href="/login" className="mb-8 inline-flex items-center gap-3">
                            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white shadow-sm">BH</span>
                            <div>
                                <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">BookHive</p>
                                <p className="text-xl font-bold text-slate-950">Dashboard</p>
                            </div>
                        </Link>
                        <h1 className="text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">Create a public demo account.</h1>
                        <p className="mt-5 max-w-xl text-base leading-8 text-slate-600 sm:text-lg">
                            Choose a role and test how BookHive changes access across dashboard menus. Protected owner access is disabled publicly and enforced on the backend.
                        </p>

                        <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {safeRoles.map((role) => (
                                <button
                                    key={role}
                                    type="button"
                                    onClick={() => setData('role', role)}
                                    className={`rounded-3xl border p-5 text-left transition ${
                                        data.role === role
                                            ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                            : 'border-slate-200 bg-white hover:border-indigo-300'
                                    }`}
                                >
                                    <p className="font-bold text-slate-950">{role}</p>
                                    <p className="mt-2 text-sm leading-6 text-slate-600">{roleDescriptions[role] || fallbackDescriptions[role]}</p>
                                </button>
                            ))}
                        </div>

                        <div className="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-900">
                            <p className="font-bold">Demo protection</p>
                            <p className="mt-1">Protected demo and owner accounts cannot be deleted, disabled, or changed by public demo users.</p>
                        </div>
                    </div>
                </section>

                <section className="flex items-center">
                    <div className="w-full rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-slate-950">Register</h2>
                            <p className="mt-2 text-sm text-slate-600">
                                Already have an account?{' '}
                                <Link href="/login" className="font-bold text-indigo-600 hover:text-indigo-500">
                                    Sign in
                                </Link>
                            </p>
                        </div>

                        <form onSubmit={submit} className="space-y-5">
                            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <InputLabel htmlFor="name" value="Name" />
                                    <TextInput
                                        id="name"
                                        name="name"
                                        value={data.name}
                                        className="mt-1 block w-full"
                                        autoComplete="name"
                                        isFocused={true}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.name} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        className="mt-1 block w-full"
                                        autoComplete="username"
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.email} className="mt-2" />
                                </div>
                            </div>

                            <div>
                                <InputLabel htmlFor="role" value="Role" />
                                <select
                                    id="role"
                                    name="role"
                                    value={data.role}
                                    className="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    onChange={(e) => setData('role', e.target.value)}
                                    required
                                >
                                    {safeRoles.map((role) => (
                                        <option key={role} value={role}>{role}</option>
                                    ))}
                                </select>
                                <p className="mt-2 text-sm leading-6 text-slate-500">{roleDescriptions[data.role] || fallbackDescriptions[data.role]}</p>
                                <InputError message={errors.role} className="mt-2" />
                            </div>

                            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <InputLabel htmlFor="password" value="Password" />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        name="password"
                                        value={data.password}
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.password} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        value={data.password_confirmation}
                                        className="mt-1 block w-full"
                                        autoComplete="new-password"
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.password_confirmation} className="mt-2" />
                                </div>
                            </div>

                            <PrimaryButton className="flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500" disabled={processing}>
                                Create account and continue
                            </PrimaryButton>
                        </form>

                        <div className="mt-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            <p className="font-semibold text-slate-900">Backend validation active</p>
                            <p>Only Admin, Editor, Reviewer, and Customer are accepted. Protected owner access is blocked even if someone edits the frontend payload.</p>
                        </div>
                    </div>
                </section>
            </div>
        </GuestLayout>
    );
}
