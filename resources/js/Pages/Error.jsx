import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

const messages = {
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    419: 'Session Expired',
    403: 'Forbidden',
};

export default function Error({ status = 500, message = null }) {
    const title = messages[status] || 'Something went wrong';

    return (
        <PublicLayout>
            <Head title={`${status} - ${title}`} />
            <div className="mx-auto flex min-h-[calc(100vh-153px)] max-w-3xl items-center px-4 py-16 text-center sm:px-6 lg:px-8">
                <div className="relative w-full overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/70">
                    <div className="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-indigo-100 blur-2xl" />
                    <div className="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-emerald-100 blur-2xl" />
                    <div className="relative">
                    <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">{status}</p>
                    <h1 className="mt-4 text-4xl font-bold tracking-tight text-slate-950">{title}</h1>
                    <p className="mt-4 text-slate-600">
                        {message || 'The requested page could not be completed. You can return to the public demo information or go back to login.'}
                    </p>
                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        <Link href="/about-demo" className="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 hover:border-indigo-500 hover:text-indigo-600">
                            About demo
                        </Link>
                        <Link href="/login" className="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500">
                            Login
                        </Link>
                    </div>
                                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
