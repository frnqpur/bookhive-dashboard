import { useState } from 'react';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head } from '@inertiajs/react';

const portfolioUrl = 'https://frengkipurba.com/projects/bookhive-dashboard';
const contactUrl = 'https://frengkipurba.com';

const featureCards = [
    {
        title: 'RBAC dashboard',
        descriptionId: 'Menu dan aksi dashboard dibatasi oleh role/permission sehingga setiap pengguna hanya melihat fitur yang relevan.',
        descriptionEn: 'Dashboard menus and actions are restricted by role and permission so each user only sees relevant features.',
    },
    {
        title: 'JWT API',
        descriptionId: 'API client mendukung login token, profil pengguna, katalog buku, dan review endpoint yang dilindungi bearer token.',
        descriptionEn: 'The client API supports token login, user profile, book catalog, and review endpoints protected by bearer tokens.',
    },
    {
        title: 'Audit log',
        descriptionId: 'Aktivitas penting seperti login, CRUD, role, permission, moderasi, dan reset demo dicatat untuk traceability.',
        descriptionEn: 'Important activity such as login, CRUD, roles, permissions, moderation, and demo reset is recorded for traceability.',
    },
    {
        title: 'Moderation workflow',
        descriptionId: 'Review dapat masuk status pending, lalu disetujui atau ditolak oleh user yang memiliki permission moderasi.',
        descriptionEn: 'Reviews can enter a pending state, then be approved or rejected by a user with moderation permission.',
    },
];

const techStack = [
    'Laravel 10',
    'React 18',
    'Inertia.js',
    'Tailwind CSS',
    'MySQL / MariaDB',
    'Spatie Permission',
    'JWT Auth',
    'Vite',
];

const demoFlow = ['Login', 'Dashboard', 'Books', 'Reviews', 'Moderation', 'Users/Roles', 'API'];

const screenshots = [
    {
        title: 'Dashboard overview',
        src: '/images/demo/bookhive-dashboard-overview.webp',
        alt: 'BookHive dashboard overview screenshot',
    },
    {
        title: 'Book catalog',
        src: '/images/demo/bookhive-book-catalog.webp',
        alt: 'BookHive book catalog screenshot',
    },
    {
        title: 'Review moderation',
        src: '/images/demo/bookhive-review-moderation.webp',
        alt: 'BookHive review moderation screenshot',
    },
    {
        title: 'Role management',
        src: '/images/demo/bookhive-role-management.webp',
        alt: 'BookHive role management screenshot',
    },
    {
        title: 'Mobile overview',
        src: '/images/demo/bookhive-mobile-overview.webp',
        alt: 'BookHive mobile dashboard screenshot',
    },
];

function Section({ eyebrow, title, children, className = '' }) {
    return (
        <section className={`rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 ${className}`}>
            {eyebrow && <p className="text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">{eyebrow}</p>}
            <h2 className="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{title}</h2>
            <div className="mt-5 text-sm leading-7 text-slate-600 sm:text-base">{children}</div>
        </section>
    );
}

function ExternalButton({ href, children, variant = 'primary' }) {
    const className = variant === 'primary'
        ? 'rounded-full bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-500'
        : 'rounded-full border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700 hover:border-indigo-500 hover:text-indigo-600';

    return (
        <a href={href} target="_blank" rel="noreferrer" className={className}>
            {children}
        </a>
    );
}

function ScreenshotCard({ screenshot }) {
    const [hasError, setHasError] = useState(false);

    return (
        <figure className="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50">
            {hasError ? (
                <div className="flex aspect-[16/10] w-full flex-col items-center justify-center px-5 text-center">
                    <p className="text-sm font-bold text-slate-800">Screenshot asset pending</p>
                    <p className="mt-2 break-all text-xs leading-5 text-slate-500">{screenshot.src}</p>
                </div>
            ) : (
                <img
                    src={screenshot.src}
                    alt={screenshot.alt}
                    className="aspect-[16/10] w-full object-cover"
                    loading="lazy"
                    onError={() => setHasError(true)}
                />
            )}
            <figcaption className="border-t border-slate-200 px-4 py-3 text-sm font-bold text-slate-800">
                {screenshot.title}
            </figcaption>
        </figure>
    );
}

export default function AboutDemo({ developer = {} }) {
    return (
        <PublicLayout>
            <Head>
                <title>About Demo - BookHive Dashboard</title>
                <meta
                    name="description"
                    content="Bilingual portfolio demo page for BookHive Dashboard, covering RBAC, JWT API, audit log, review moderation, tech stack, screenshots, and demo flow."
                />
                <meta property="og:title" content="BookHive Dashboard Portfolio Demo" />
                <meta property="og:description" content="Laravel React/Inertia dashboard portfolio project by Frengki Josua Purba." />
                <meta property="og:type" content="website" />
            </Head>

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <section className="relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white px-6 py-12 text-center shadow-xl shadow-slate-200/70 sm:px-10 lg:px-16">
                    <div className="absolute -left-20 -top-20 h-56 w-56 rounded-full bg-indigo-100 blur-3xl" />
                    <div className="absolute -bottom-24 -right-24 h-64 w-64 rounded-full bg-amber-100 blur-3xl" />
                    <div className="relative mx-auto max-w-4xl">
                        <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-600">Public portfolio demo</p>
                        <h1 className="mt-4 text-4xl font-bold tracking-tight text-slate-950 sm:text-6xl">BookHive Dashboard</h1>
                        <p className="mt-6 text-lg leading-8 text-slate-600">
                            <span className="font-semibold text-slate-900">ID:</span> Dashboard manajemen buku dan review yang dirancang sebagai project portfolio full-stack dengan RBAC, JWT API, audit log, dan workflow moderasi.
                        </p>
                        <p className="mt-3 text-lg leading-8 text-slate-600">
                            <span className="font-semibold text-slate-900">EN:</span> A book and review management dashboard prepared as a full-stack portfolio project with RBAC, JWT API, audit logging, and moderation workflow.
                        </p>
                        <div className="mt-8 flex flex-wrap justify-center gap-3">
                            <ExternalButton href={portfolioUrl}>View Portfolio Page</ExternalButton>
                            <ExternalButton href={contactUrl} variant="secondary">Contact Developer</ExternalButton>
                        </div>
                    </div>
                </section>

                <div className="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Section eyebrow="Project overview" title="Ringkasan project / Project overview">
                        <div className="space-y-4">
                            <p>
                                <span className="font-bold text-slate-900">ID:</span> BookHive Dashboard membantu admin mengelola katalog buku, review pembaca, user, role, permission, dan catatan aktivitas pada satu dashboard berbasis Laravel dan React/Inertia.
                            </p>
                            <p>
                                <span className="font-bold text-slate-900">EN:</span> BookHive Dashboard helps admins manage a book catalog, reader reviews, users, roles, permissions, and activity records in a Laravel and React/Inertia dashboard.
                            </p>
                        </div>
                    </Section>

                    <Section eyebrow="My role" title="Full-Stack Web Developer">
                        <div className="space-y-4">
                            <p>
                                <span className="font-bold text-slate-900">ID:</span> Bertanggung jawab pada implementasi backend Laravel, frontend React/Inertia, RBAC, integrasi API JWT, UI dashboard, dan dokumentasi demo portfolio.
                            </p>
                            <p>
                                <span className="font-bold text-slate-900">EN:</span> Responsible for Laravel backend implementation, React/Inertia frontend, RBAC, JWT API integration, dashboard UI, and portfolio demo documentation.
                            </p>
                        </div>
                    </Section>

                    <Section eyebrow="Key features" title="Fitur utama / Key features" className="lg:col-span-2">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {featureCards.map((feature) => (
                                <article key={feature.title} className="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-100">
                                    <h3 className="text-base font-bold text-slate-950">{feature.title}</h3>
                                    <p className="mt-3 text-sm leading-6 text-slate-600"><span className="font-semibold text-slate-800">ID:</span> {feature.descriptionId}</p>
                                    <p className="mt-2 text-sm leading-6 text-slate-600"><span className="font-semibold text-slate-800">EN:</span> {feature.descriptionEn}</p>
                                </article>
                            ))}
                        </div>
                    </Section>

                    <Section eyebrow="Tech stack" title="Teknologi yang digunakan / Tech stack">
                        <div className="flex flex-wrap gap-2">
                            {techStack.map((item) => (
                                <span key={item} className="rounded-full bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-700">
                                    {item}
                                </span>
                            ))}
                        </div>
                    </Section>

                    <Section eyebrow="Suggested demo flow" title="Alur demo yang disarankan">
                        <ol className="space-y-3">
                            {demoFlow.map((step, index) => (
                                <li key={step} className="flex gap-3 rounded-2xl bg-slate-50 p-4">
                                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{index + 1}</span>
                                    <span className="pt-1 font-semibold text-slate-800">{step}</span>
                                </li>
                            ))}
                        </ol>
                    </Section>

                    <Section eyebrow="Screenshots" title="Screenshot assets" className="lg:col-span-2">
                        <div className="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                            {screenshots.map((screenshot) => (
                                <ScreenshotCard key={screenshot.src} screenshot={screenshot} />
                            ))}
                        </div>
                    </Section>

                    <Section eyebrow="Demo notes" title="Catatan demo / Demo notes">
                        <div className="space-y-4">
                            <p>
                                <span className="font-bold text-slate-900">ID:</span> Credential demo dan daftar role sudah disediakan di halaman login, sehingga halaman ini fokus pada konteks project, fitur, dan alur eksplorasi recruiter.
                            </p>
                            <p>
                                <span className="font-bold text-slate-900">EN:</span> Demo credentials and role details are already available on the login page, so this page focuses on project context, features, and the recruiter exploration flow.
                            </p>
                        </div>
                    </Section>

                    <Section eyebrow="Developer note" title={developer.name || 'Frengki Josua Purba'}>
                        <div className="space-y-4">
                            <p>
                                <span className="font-bold text-slate-900">ID:</span> Project ini diposisikan sebagai portfolio full-stack web development. Source code internal, environment variable, dan credential private tidak perlu ditampilkan pada halaman publik.
                            </p>
                            <p>
                                <span className="font-bold text-slate-900">EN:</span> This project is positioned as a full-stack web development portfolio. Internal source code, environment variables, and private credentials should not be displayed on the public page.
                            </p>
                        </div>
                    </Section>
                </div>

                <section className="mt-10 rounded-[2rem] bg-slate-950 p-6 text-center shadow-xl shadow-slate-300/60 sm:p-10">
                    <p className="text-sm font-bold uppercase tracking-[0.24em] text-indigo-300">CTA</p>
                    <h2 className="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Explore the complete portfolio case study</h2>
                    <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        Lihat ringkasan portfolio lengkap atau hubungi developer untuk diskusi lebih lanjut tentang implementasi BookHive Dashboard.
                    </p>
                    <div className="mt-8 flex flex-wrap justify-center gap-3">
                        <ExternalButton href={portfolioUrl}>View Portfolio Page</ExternalButton>
                        <ExternalButton href={contactUrl} variant="secondary">Contact Developer</ExternalButton>
                    </div>
                </section>
            </div>
        </PublicLayout>
    );
}
