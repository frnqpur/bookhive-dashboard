import PublicLayout from '@/Layouts/PublicLayout';
import { Head, Link } from '@inertiajs/react';

function ContactValue({ label, value }) {
    const isLink = ['GitHub', 'LinkedIn', 'Portfolio'].includes(label);
    const isEmail = label === 'Email';
    const href = isEmail ? `mailto:${value}` : value;

    if (isLink || isEmail) {
        return (
            <a href={href} target={isEmail ? undefined : '_blank'} rel={isEmail ? undefined : 'noreferrer'} className="break-all font-semibold text-indigo-600 hover:text-indigo-500">
                {value}
            </a>
        );
    }

    return <span className="break-all text-slate-600">{value}</span>;
}

export default function ContactDeveloper({ developer = {} }) {
    const rows = [
        ['Name', developer.name || 'Frengki Josua Purba'],
        ['GitHub', developer.github || 'https://github.com/frnqpur'],
        ['LinkedIn', developer.linkedin || 'https://www.linkedin.com/in/frengkijosuapurba'],
        ['Portfolio', developer.portfolio || 'https://frengkipurba.com'],
        ['Email', developer.email || 'contact@frengkipurba.com'],
    ];

    return (
        <PublicLayout>
            <Head>
                <title>Contact Developer - BookHive Dashboard</title>
                <meta name="description" content="Contact details for Frengki Josua Purba, developer of BookHive Dashboard." />
                <meta property="og:title" content="Contact the BookHive Dashboard Developer" />
                <meta property="og:description" content="Developer contact details for the BookHive Dashboard portfolio project." />
                <meta property="og:type" content="website" />
            </Head>

            <div className="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
                <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-10">
                    <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">Developer</p>
                    <h1 className="mt-4 text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">Contact Developer</h1>
                    <p className="mt-5 text-base leading-8 text-slate-600">
                        This public contact page is prepared for portfolio deployment. These default values are synchronized with application settings and restored during demo reset.
                    </p>

                    <div className="mt-8 overflow-hidden rounded-3xl border border-slate-200">
                        <dl className="divide-y divide-slate-200">
                            {rows.map(([label, value]) => (
                                <div key={label} className="grid grid-cols-1 gap-2 bg-white px-5 py-4 sm:grid-cols-3">
                                    <dt className="text-sm font-bold text-slate-900">{label}</dt>
                                    <dd className="text-sm sm:col-span-2"><ContactValue label={label} value={value} /></dd>
                                </div>
                            ))}
                        </dl>
                    </div>

                    <div className="mt-8 rounded-3xl bg-slate-50 p-5 text-sm leading-6 text-slate-600">
                        <p className="font-bold text-slate-900">Note</p>
                        <p>{developer.note || 'These default contact details are restored during demo reset.'}</p>
                    </div>

                    <div className="mt-8 flex flex-wrap gap-3">
                        <Link href="/about-demo" className="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-700 hover:border-indigo-500 hover:text-indigo-600">
                            Back to about demo
                        </Link>
                        <Link href="/login" className="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500">
                            Open login
                        </Link>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
