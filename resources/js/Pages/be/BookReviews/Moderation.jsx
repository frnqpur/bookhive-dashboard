import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import ConfirmActionButton from '@/Components/ConfirmActionButton';
import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import RatingStars from '@/Components/RatingStars';
import StatusBadge from '@/Components/StatusBadge';
import { CheckCircleIcon, NoSymbolIcon } from '@heroicons/react/20/solid';

export default function Moderation({ auth, pageTitle, pageDescription, review, formUrl, statuses = ['pending', 'approved', 'rejected'] }) {
    const { data, setData, patch, processing, errors } = useForm({
        status: review?.status || 'pending',
        moderation_note: review?.moderation_note || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(formUrl, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Reviews', href: route('dashboard.be.bookReviews.list') }, { label: 'Moderation queue', href: route('dashboard.be.bookReviews.moderation') }, { label: 'Moderate' }]}
                    badge={<StatusBadge status={review.status} />}
                    actions={(
                        <div className="flex flex-wrap gap-2">
                            <ConfirmActionButton
                                href={formUrl}
                                method="patch"
                                data={{ status: 'approved', moderation_note: data.moderation_note }}
                                variant="success"
                                title="Approve this review?"
                                text="Approved reviews become visible on the book detail page and count toward the average rating."
                                confirmText="Approve"
                                successTitle="Review approved"
                                disabled={review.is_protected && !auth.isSuperAdmin}
                            >
                                <CheckCircleIcon className="h-5 w-5" />
                                Approve
                            </ConfirmActionButton>
                            <ConfirmActionButton
                                href={formUrl}
                                method="patch"
                                data={{ status: 'rejected', moderation_note: data.moderation_note }}
                                variant="warning"
                                title="Reject this review?"
                                text="Rejected reviews are hidden from public book detail pages but remain visible to the owner and moderators."
                                confirmText="Reject"
                                successTitle="Review rejected"
                                disabled={review.is_protected && !auth.isSuperAdmin}
                            >
                                <NoSymbolIcon className="h-5 w-5" />
                                Reject
                            </ConfirmActionButton>
                        </div>
                    )}
                />

                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    {review.is_protected && (
                        <div className="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            This is protected seeded review data. Destructive or unsafe actions are restricted for demo/public users.
                        </div>
                    )}

                    <article className="rounded-2xl bg-slate-50 p-4">
                        <p className="text-sm font-semibold text-slate-500">{review.book || 'Book'}</p>
                        <h2 className="mt-1 text-xl font-bold text-slate-900">{review.title}</h2>
                        <div className="mt-2"><RatingStars rating={review.rating} /></div>
                        <p className="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{review.body}</p>
                        <p className="mt-4 text-xs text-slate-500">Submitted by {review.created_by_user || 'Reader'} · {review.created_at}</p>
                    </article>

                    <form onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <InputLabel htmlFor="status" value="Decision" />
                            <select id="status" value={data.status} className="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('status', e.target.value)}>
                                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                            </select>
                            <InputError message={errors.status} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="moderation_note" value="Moderation note" />
                            <textarea id="moderation_note" value={data.moderation_note} rows="4" className="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('moderation_note', e.target.value)} placeholder="Optional note for rejected or pending reviews" />
                            <InputError message={errors.moderation_note} className="mt-2" />
                        </div>
                        <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                            <Link href={route('dashboard.be.bookReviews.moderation')} className="inline-flex justify-center rounded-xl bg-slate-600 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-500">Back to queue</Link>
                            <PrimaryButton disabled={processing}>Save moderation</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
