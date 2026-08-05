import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';

const recoverySteps = [
    'Enter the email address registered in BookHive.',
    'Open the reset link from your inbox or spam folder.',
    'Create a new password and sign in again.',
];

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head>
                <title>Forgot Password - BookHive Dashboard</title>
                <meta
                    name="description"
                    content="Request a secure password reset link for the BookHive Dashboard portfolio demo."
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
                                <h1 className="text-2xl font-bold text-slate-950">Account recovery</h1>
                            </div>
                        </div>

                        <h2 className="max-w-xl text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">
                            Reset your access to the BookHive Dashboard securely.
                        </h2>
                        <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            Use the registered email address for your demo account. BookHive will send a reset link so you can create a new password safely.
                        </p>

                        <div className="mt-8 grid grid-cols-1 gap-3">
                            {recoverySteps.map((step, index) => (
                                <div key={step} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div className="flex items-start gap-3">
                                        <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                            {index + 1}
                                        </span>
                                        <p className="text-sm font-semibold leading-6 text-slate-800">{step}</p>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="mt-8 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                            <p className="font-bold">Demo account notice</p>
                            <p className="mt-1">
                                Public demo data resets automatically. Use a real inbox only when you want to test the password reset flow.
                            </p>
                        </div>
                    </div>
                </section>

                <section className="flex items-center px-4 py-10 sm:px-6 lg:px-8">
                    <div className="w-full rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                        <div className="mb-6">
                            <p className="text-sm font-bold uppercase tracking-[0.2em] text-indigo-600">Password help</p>
                            <h2 className="mt-2 text-2xl font-bold text-slate-950">Forgot your password?</h2>
                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                Enter your email address. We will send a reset link if that email belongs to a BookHive account.
                            </p>
                        </div>

                        {status && (
                            <div className="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                                {status}
                            </div>
                        )}

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

                            <PrimaryButton
                                className="flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500"
                                disabled={processing}
                            >
                                {processing ? 'Sending reset link...' : 'Email password reset link'}
                            </PrimaryButton>
                        </form>

                        <div className="mt-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            <p className="font-semibold text-slate-900">Did not receive the email?</p>
                            <p className="mt-1">Check your spam folder, then confirm that the email exists in the BookHive users table.</p>
                        </div>

                        <p className="mt-6 text-center text-sm text-slate-600">
                            Remember your password?{' '}
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
