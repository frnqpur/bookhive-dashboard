import Checkbox from '@/Components/Checkbox';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

const defaultFeatures = [
    'Role-based dashboard',
    'Book management',
    'Review moderation',
    'User management',
    'Permission management',
    'JWT API',
    'Demo reset every 6 hours',
];

export default function Login({ status, canResetPassword, demoCredentials = [], features = defaultFeatures }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    const useCredential = (credential) => {
        setData({
            ...data,
            email: credential.email,
            password: credential.password,
        });
    };

    return (
        <GuestLayout>
            <Head>
                <title>Login - BookHive Dashboard</title>
                <meta
                    name="description"
                    content="Login to the BookHive Dashboard public portfolio demo for role-based book management, review moderation, permissions, and JWT API practice."
                />
                <meta property="og:title" content="BookHive Dashboard Login" />
                <meta property="og:description" content="Try a production-like Laravel React/Inertia book review dashboard demo." />
                <meta property="og:type" content="website" />
            </Head>

            <div className="mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl grid-cols-1 lg:grid-cols-2">
                <section className="flex items-center px-4 py-10 sm:px-6 lg:px-8">
                    <div className="w-full">
                        <div className="mb-8 flex items-center gap-3">
                            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white shadow-sm">BH</span>
                            <div>
                                <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">BookHive</p>
                                <h1 className="text-2xl font-bold text-slate-950">BookHive Dashboard</h1>
                            </div>
                        </div>

                        <h2 className="max-w-xl text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">
                            A production-like book review and library management dashboard.
                        </h2>
                        <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            BookHive Dashboard is a Laravel + React/Inertia portfolio application for managing books, moderating reviews, handling users, and demonstrating real role-based access control.
                        </p>

                        <div className="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {features.map((feature) => (
                                <div key={feature} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div className="flex items-start gap-3">
                                        <span className="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-600" />
                                        <p className="text-sm font-semibold text-slate-800">{feature}</p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                            <p className="font-bold">Public demo notice</p>
                            <p className="mt-1">This public demo environment resets automatically every 6 hours.</p>
                        </div>

                        <div className="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
                            <Link href="/about-demo" className="rounded-full bg-slate-900 px-5 py-2.5 text-white shadow-sm hover:bg-slate-700">
                                Read about the demo
                            </Link>
                            <Link href="/register" className="rounded-full border border-slate-300 px-5 py-2.5 text-slate-700 hover:border-indigo-500 hover:text-indigo-600">
                                Create public account
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="flex items-center px-4 py-10 sm:px-6 lg:px-8">
                    <div className="w-full rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                        <div className="mb-6">
                            <h2 className="text-2xl font-bold text-slate-950">Sign in</h2>
                            <p className="mt-2 text-sm text-slate-600">Use a demo account below or sign in with your own registered account.</p>
                        </div>

                        {status && <div className="mb-4 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{status}</div>}

                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <InputLabel htmlFor="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    className="mt-1 block w-full"
                                    autoComplete="username"
                                    isFocused={true}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                                <InputError message={errors.email} className="mt-2" />
                            </div>

                            <div>
                                <div className="flex items-center justify-between">
                                    <InputLabel htmlFor="password" value="Password" />
                                    {canResetPassword && (
                                        <Link href={route('password.request')} className="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                                            Forgot password?
                                        </Link>
                                    )}
                                </div>
                                <TextInput
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    className="mt-1 block w-full"
                                    autoComplete="current-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                    required
                                />
                                <InputError message={errors.password} className="mt-2" />
                            </div>

                            <label className="flex items-center gap-2 text-sm text-slate-600">
                                <Checkbox
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                />
                                Remember me
                            </label>

                            <PrimaryButton className="flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500" disabled={processing}>
                                Sign in to dashboard
                            </PrimaryButton>
                        </form>

                        <div className="mt-8 border-t border-slate-200 pt-6">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <h3 className="font-bold text-slate-950">Demo credentials</h3>
                                    <p className="text-sm text-slate-500">Owner credentials are private and never shown publicly.</p>
                                </div>
                            </div>

                            <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                {demoCredentials.map((credential) => (
                                    <button
                                        key={credential.email}
                                        type="button"
                                        onClick={() => useCredential(credential)}
                                        className="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50"
                                    >
                                        <p className="text-sm font-bold text-slate-900">{credential.role}</p>
                                        <p className="mt-1 break-all text-xs text-slate-600">{credential.email}</p>
                                        <p className="mt-1 text-xs text-slate-500">Password: {credential.password}</p>
                                    </button>
                                ))}
                            </div>
                        </div>

                        <p className="mt-6 text-center text-sm text-slate-600">
                            Need your own demo user?{' '}
                            <Link href="/register" className="font-bold text-indigo-600 hover:text-indigo-500">
                                Register here
                            </Link>
                        </p>
                    </div>
                </section>
            </div>
        </GuestLayout>
    );
}
