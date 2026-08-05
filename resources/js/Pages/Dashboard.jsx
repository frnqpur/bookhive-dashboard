import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState.jsx';
import RatingStars from '@/Components/RatingStars.jsx';
import StatusBadge from '@/Components/StatusBadge.jsx';
import { BarChart, ChartCard, DonutChart, LineChart, MetricCard } from '@/Components/Charts/SimpleCharts.jsx';
import {
    ArrowRightIcon,
    BookOpenIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    ClockIcon,
    ShieldCheckIcon,
    SparklesIcon,
    StarIcon,
    UserGroupIcon,
    UsersIcon,
    XCircleIcon,
} from '@heroicons/react/24/outline';

const statIcons = [UsersIcon, BookOpenIcon, ChatBubbleLeftRightIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ShieldCheckIcon, SparklesIcon, StarIcon, UserGroupIcon];

function SectionHeader({ title, description, href = null, actionLabel = 'View all' }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 className="text-base font-bold text-slate-950 sm:text-lg">{title}</h2>
                {description && <p className="mt-1 text-sm leading-6 text-slate-500">{description}</p>}
            </div>
            {href && (
                <Link href={href} className="inline-flex items-center gap-1 text-sm font-bold text-indigo-600 hover:text-indigo-500">
                    {actionLabel}
                    <ArrowRightIcon className="h-4 w-4" />
                </Link>
            )}
        </div>
    );
}

function ReviewCard({ review, showModerate = false }) {
    return (
        <article className="rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-200 hover:shadow-sm">
            <div className="flex flex-wrap items-center gap-2">
                <RatingStars rating={review.rating} />
                <StatusBadge status={review.status} />
            </div>
            <h3 className="mt-3 line-clamp-1 font-bold text-slate-950">{review.title}</h3>
            <p className="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">{review.body_excerpt || review.body}</p>
            <p className="mt-3 text-xs font-medium text-slate-500">{review.book || 'Book'} · {review.created_by_user || 'Reader'}</p>
            {showModerate && review.can_approve && (
                <Link href={route('dashboard.be.bookReviews.moderation.show', review.id)} className="mt-4 inline-flex rounded-xl bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-500">
                    Moderate review
                </Link>
            )}
        </article>
    );
}

function BookListItem({ book }) {
    return (
        <Link href={route('dashboard.be.books.show', book.id)} className="group flex gap-4 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-indigo-200 hover:shadow-sm">
            {book.cover_url ? (
                <img src={book.cover_url} alt={book.title} className="h-24 w-16 shrink-0 rounded-xl object-cover ring-1 ring-slate-200" />
            ) : (
                <div className="flex h-24 w-16 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-400">No cover</div>
            )}
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap gap-2">
                    <StatusBadge status={book.status} />
                    {book.category && <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{book.category}</span>}
                </div>
                <h3 className="mt-2 truncate font-bold text-slate-950 group-hover:text-indigo-700">{book.title}</h3>
                <p className="truncate text-sm text-slate-600">{book.author}</p>
                <div className="mt-3"><RatingStars rating={book.average_rating} total={book.total_reviews} /></div>
            </div>
        </Link>
    );
}

export default function Dashboard({
    auth,
    stats = [],
    latestBooks = { data: [] },
    latestReviews = { data: [] },
    latestUsers = { data: [] },
    pendingReviews = { data: [] },
    latestActivities = [],
    quickActions = [],
    chartData = {},
}) {
    const books = latestBooks.data || [];
    const reviews = latestReviews.data || [];
    const users = latestUsers.data || [];
    const pending = pendingReviews.data || [];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />
            <div className="space-y-6 p-4 sm:p-6 lg:p-8">
                <PageHeader
                    title="Dashboard"
                    description="A production-like BookHive workspace with analytics, role-aware actions, and demo-safe activity tracking."
                    breadcrumbs={[{ label: 'Dashboard' }]}
                />

                <section className="relative overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-sm sm:p-8">
                    <div className="absolute right-0 top-0 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl" />
                    <div className="absolute bottom-0 left-1/3 h-56 w-56 rounded-full bg-emerald-400/10 blur-3xl" />
                    <div className="relative grid grid-cols-1 gap-8 lg:grid-cols-[1.2fr,0.8fr] lg:items-center">
                        <div>
                            <p className="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.22em] text-indigo-100 ring-1 ring-white/10">BookHive Dashboard</p>
                            <h1 className="mt-5 max-w-3xl text-3xl font-black tracking-tight sm:text-5xl">Book reviews, catalog management, and moderation in one polished dashboard.</h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                Track users, books, reviews, role permissions, demo reset safety, and audit activity through a responsive Laravel + React/Inertia portfolio app.
                            </p>
                            <div className="mt-6 flex flex-wrap gap-3">
                                <Link href={route('dashboard.be.books.list')} className="rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-950 shadow-sm hover:bg-indigo-50">Browse books</Link>
                                {auth.can?.createReviews && <Link href={route('dashboard.be.bookReviews.create')} className="rounded-full border border-white/20 px-5 py-3 text-sm font-bold text-white hover:bg-white/10">Write review</Link>}
                                {auth.can?.approveReviews && <Link href={route('dashboard.be.bookReviews.moderation')} className="rounded-full border border-white/20 px-5 py-3 text-sm font-bold text-white hover:bg-white/10">Moderation queue</Link>}
                            </div>
                        </div>
                        <div className="rounded-[1.5rem] border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <p className="text-sm font-bold uppercase tracking-wide text-slate-300">Signed in as</p>
                            <p className="mt-2 text-2xl font-black">{auth.user?.name}</p>
                            <p className="mt-1 text-sm text-slate-300">{(auth.roles || []).join(', ') || 'Dashboard user'}</p>
                            <div className="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div className="rounded-2xl bg-white/10 p-4">
                                    <p className="text-slate-300">Access</p>
                                    <p className="mt-1 font-bold">Role-based</p>
                                </div>
                                <div className="rounded-2xl bg-white/10 p-4">
                                    <p className="text-slate-300">Demo reset</p>
                                    <p className="mt-1 font-bold">Every 6 hours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    {stats.map((item, index) => {
                        const Icon = statIcons[index % statIcons.length];
                        return (
                            <MetricCard
                                key={item.name}
                                name={item.name}
                                value={item.stat}
                                description={item.description}
                                icon={Icon}
                                tone={item.tone || 'indigo'}
                            />
                        );
                    })}
                </section>

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <ChartCard title="Reviews per month" description="Review submissions over the latest six-month window.">
                        <LineChart data={chartData.reviewsPerMonth || []} />
                    </ChartCard>
                    <ChartCard title="Books per category" description="Catalog distribution by book category.">
                        <BarChart data={chartData.booksByCategory || []} />
                    </ChartCard>
                    <ChartCard title="Users by role" description="Registered account distribution across BookHive roles.">
                        <DonutChart data={chartData.usersByRole || []} />
                    </ChartCard>
                    <ChartCard title="Review status distribution" description="Pending, approved, and rejected reviews visible to your role.">
                        <DonutChart data={chartData.reviewStatusDistribution || []} />
                    </ChartCard>
                </section>

                {quickActions.length > 0 && (
                    <section className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <SectionHeader title="Quick actions" description="Shortcuts are shown only when your role has permission to use them." />
                        <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            {quickActions.map((action) => (
                                <Link key={action.href} href={action.href} className="group rounded-2xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                    <p className="font-bold text-slate-950 group-hover:text-indigo-700">{action.label}</p>
                                    <p className="mt-1 text-sm leading-6 text-slate-500">{action.description}</p>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <div className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <SectionHeader title="Latest books" description="Recently added catalog items." href={route('dashboard.be.books.list')} />
                        <div className="mt-5 space-y-3">
                            {books.length === 0 ? <EmptyState title="No books yet" /> : books.map((book) => <BookListItem key={book.id} book={book} />)}
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <SectionHeader title="Latest reviews" description="Recent review activity based on your role access." href={route('dashboard.be.bookReviews.list')} />
                        <div className="mt-5 grid grid-cols-1 gap-3">
                            {reviews.length === 0 ? <EmptyState title="No reviews yet" /> : reviews.map((review) => <ReviewCard key={review.id} review={review} />)}
                        </div>
                    </div>
                </section>

                <section className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    {auth.can?.manageUsers && (
                        <div className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                            <SectionHeader title="Latest users" description="Recently registered dashboard accounts." href={route('dashboard.global.users.list')} />
                            <div className="mt-5 space-y-3">
                                {users.length === 0 ? <EmptyState title="No users yet" description="New registrations and created users will appear here." /> : users.map((user) => (
                                    <article key={user.id} className="rounded-2xl border border-slate-200 p-4">
                                        <p className="font-bold text-slate-950">{user.name}</p>
                                        <p className="mt-1 break-all text-sm text-slate-500">{user.email}</p>
                                        <div className="mt-2 flex flex-wrap gap-1">
                                            {(user.roles || []).map((role) => <span key={role} className="rounded-full bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700">{role}</span>)}
                                        </div>
                                    </article>
                                ))}
                            </div>
                        </div>
                    )}

                    {auth.can?.approveReviews && (
                        <div className="rounded-[1.75rem] border border-amber-200 bg-amber-50/40 p-5 shadow-sm sm:p-6 xl:col-span-1">
                            <SectionHeader title="Pending moderation" description="Reviews waiting for approval or rejection." href={route('dashboard.be.bookReviews.moderation')} />
                            <div className="mt-5 space-y-3">
                                {pending.length === 0 ? <EmptyState title="No pending reviews" description="The moderation queue is clear." /> : pending.map((review) => <ReviewCard key={review.id} review={review} showModerate />)}
                            </div>
                        </div>
                    )}

                    {auth.can?.viewAuditLogs && (
                        <div className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-1">
                            <SectionHeader title="Latest activities" description="Recent security and administrative audit events." href={route('dashboard.auditLogs.index')} />
                            <div className="mt-5 space-y-3">
                                {latestActivities.length === 0 ? <EmptyState title="No audit activity yet" description="Login, register, CRUD, moderation, settings, and reset actions will appear here." /> : latestActivities.map((activity) => (
                                    <article key={activity.id} className="rounded-2xl border border-slate-200 p-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="font-bold capitalize text-slate-950">{activity.action}</p>
                                                <p className="mt-1 text-sm leading-6 text-slate-500">{activity.description || `${activity.entity_type || 'Record'} #${activity.entity_id || '-'}`}</p>
                                            </div>
                                            <span className="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">{activity.created_at}</span>
                                        </div>
                                        <p className="mt-2 text-xs text-slate-500">By {activity.user}</p>
                                    </article>
                                ))}
                            </div>
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
