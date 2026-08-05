import { useEffect } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

const passwordTips = [
    'Use at least 8 characters.',
    'Mix letters, numbers, and symbols.',
    'Avoid passwords used on other websites.',
];

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
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

        post(route('password.store'));
    };

    return (
        <GuestLayout>
            <Head>
                <title>Reset Password - BookHive Dashboard</title>
                <meta
                    name="description"
                    content="Create a new password for the BookHive Dashboard portfolio demo account."
                />
            </Head>

            <div className="mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl grid-cols-1 lg:grid-cols-2">
                <section className="flex items-center px-4 py-10 sm:px-6 lg:px-8">
                    <div className="w-full">
                        <div className="mb-8 flex items-center gap-3">
                            <span className="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white shadow-sm">
                                BH
                            </span>
                            <div>
                                <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">BookHive</p>
                                <h1 className="text-2xl font-bold text-slate-950">Secure reset</h1>
                            </div>
                        </div>

                        <h2 className="max-w-xl text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">
                            Create a new password for your BookHive account.
                        </h2>
                        <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            Choose a strong password for your dashboard access. After the reset succeeds, you can sign in with the new password immediately.
                        </p>

                        <div className="mt-8 grid grid-cols-1 gap-3">
                            {passwordTips.map((tip) => (
                                <div key={tip} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div className="flex items-start gap-3">
                                        <span className="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-600" />
                                        <p className="text-sm font-semibold leading-6 text-slate-800">{tip}</p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                            <p className="font-bold">Security notice</p>
                            <p className="mt-1">
                                Password reset links are time-sensitive. Request a new link if this page shows an expired token error.
                            </p>
                        </div>
                    </div>
                </section>

                <section className="flex items-center px-4 py-10 sm:px-6 lg:px-8">
                    <div className="w-full rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                        <div className="mb-6">
                            <p className="text-sm font-bold uppercase tracking-[0.2em] text-indigo-600">New password</p>
                            <h2 className="mt-2 text-2xl font-bold text-slate-950">Reset password</h2>
                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                Confirm your email address and set a new password for your BookHive account.
                            </p>
                        </div>

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
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                                <InputError message={errors.email} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="password" value="New password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    className="mt-1 block w-full"
                                    autoComplete="new-password"
                                    isFocused={true}
                                    onChange={(e) => setData('password', e.target.value)}
                                    required
                                />
                                <InputError message={errors.password} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="password_confirmation" value="Confirm new password" />
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

                            <PrimaryButton
                                className="flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500"
                                disabled={processing}
                            >
                                {processing ? 'Resetting password...' : 'Reset password'}
                            </PrimaryButton>
                        </form>

                        <div className="mt-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            <p className="font-semibold text-slate-900">Need a fresh link?</p>
                            <p className="mt-1">Request another reset email if your token has expired or has already been used.</p>
                        </div>

                        <p className="mt-6 text-center text-sm text-slate-600">
                            Want to sign in instead?{' '}
                            <Link href={route('login')} className="font-bold text-indigo-600 hover:text-indigo-500">
                                Back to login
                            </Link>
                        </p>
                    </div>
                </section>
            </div>
        </GuestLayout>
    );
}
