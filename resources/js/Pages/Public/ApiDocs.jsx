import PublicLayout from '@/Layouts/PublicLayout';
import ApiDocumentation from '@/Components/Api/ApiDocumentation';
import { Head, Link } from '@inertiajs/react';

export default function ApiDocs({ pageTitle, pageDescription, ...props }) {
    return (
        <PublicLayout>
            <Head>
                <title>{pageTitle}</title>
                <meta name="description" content={pageDescription} />
                <meta property="og:title" content="BookHive Dashboard API Docs" />
                <meta property="og:description" content="JWT API documentation for the BookHive Dashboard portfolio demo." />
                <meta property="og:type" content="website" />
            </Head>
            <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <Link href="/about-demo" className="text-sm font-semibold text-slate-600 hover:text-indigo-600">← About demo</Link>
                    <Link href="/login" className="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-500">Try the dashboard</Link>
                </div>
                <ApiDocumentation {...props} />
            </div>
        </PublicLayout>
    );
}
