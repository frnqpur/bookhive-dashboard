import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import RatingStars from '@/Components/RatingStars.jsx';
import StatusBadge from '@/Components/StatusBadge.jsx';
import EmptyState from '@/Components/EmptyState.jsx';
import Paginator from '@/Components/Paginator.jsx';
import { ArrowLeftIcon, PencilSquareIcon, PlusCircleIcon } from '@heroicons/react/20/solid/index.js';

export default function Detail({ auth, pageTitle, pageDescription, book, reviews, canManage = false, canCreateReview = false, createReviewUrl }) {
    const reviewData = reviews?.data || [];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link href={route('dashboard.be.books.list')} className="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                        <ArrowLeftIcon className="h-4 w-4" /> Back to books
                    </Link>
                    <div className="flex flex-wrap gap-2">
                        {canCreateReview && (
                            <Link href={createReviewUrl} className="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <PlusCircleIcon className="h-5 w-5" /> Write review
                            </Link>
                        )}
                        {canManage && (
                            <Link href={route('dashboard.be.books.edit', book.id)} className="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700">
                                <PencilSquareIcon className="h-5 w-5" /> Edit book
                            </Link>
                        )}
                    </div>
                </div>

                <section className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid grid-cols-1 lg:grid-cols-[280px_1fr]">
                        <div className="bg-slate-100 p-6">
                            {book.cover_url ? (
                                <img src={book.cover_url} alt={book.title} className="mx-auto h-96 w-full max-w-xs rounded-2xl object-cover shadow-sm ring-1 ring-slate-200" />
                            ) : (
                                <div className="mx-auto flex h-96 w-full max-w-xs items-center justify-center rounded-2xl bg-white text-sm font-semibold text-slate-400 ring-1 ring-slate-200">No cover image</div>
                            )}
                        </div>
                        <div className="p-6 lg:p-8">
                            <div className="flex flex-wrap items-center gap-2">
                                <StatusBadge status={book.status} />
                                {book.category && <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">{book.category}</span>}
                                {book.is_protected && <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Protected demo data</span>}
                            </div>
                            <h1 className="mt-4 text-3xl font-bold tracking-tight text-slate-900">{book.title}</h1>
                            <p className="mt-2 text-base text-slate-600">by <span className="font-semibold text-slate-800">{book.author}</span>{book.published_year ? ` · ${book.published_year}` : ''}</p>
                            <div className="mt-4">
                                <RatingStars rating={book.average_rating} total={book.total_reviews} size="lg" />
                            </div>
                            {pageDescription && <p className="mt-5 text-sm leading-6 text-slate-600">{pageDescription}</p>}
                            {book.description && <p className="mt-5 whitespace-pre-line text-sm leading-7 text-slate-700">{book.description}</p>}

                            <dl className="mt-6 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div className="rounded-2xl bg-slate-50 p-4"><dt className="font-semibold text-slate-500">ISBN 10</dt><dd className="mt-1 text-slate-900">{book.ISBN_10 || '-'}</dd></div>
                                <div className="rounded-2xl bg-slate-50 p-4"><dt className="font-semibold text-slate-500">ISBN 13</dt><dd className="mt-1 text-slate-900">{book.ISBN_13 || '-'}</dd></div>
                                <div className="rounded-2xl bg-slate-50 p-4"><dt className="font-semibold text-slate-500">Created by</dt><dd className="mt-1 text-slate-900">{book.created_by_user || '-'}</dd></div>
                                <div className="rounded-2xl bg-slate-50 p-4"><dt className="font-semibold text-slate-500">Last updated</dt><dd className="mt-1 text-slate-900">{book.updated_at || '-'}</dd></div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section className="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 className="text-lg font-semibold text-slate-900">Approved reviews</h2>
                            <p className="mt-1 text-sm text-slate-600">Only approved reviews are counted in average rating and public totals. Pending reviews are visible only to their owner and moderators.</p>
                        </div>
                    </div>
                    <div className="mt-5 space-y-3">
                        {reviewData.length === 0 ? (
                            <EmptyState title="No visible reviews yet" description="Write the first review or wait for moderators to approve pending feedback." />
                        ) : (
                            reviewData.map((review) => (
                                <article key={review.id} className="rounded-2xl border border-slate-200 p-4">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <RatingStars rating={review.rating} />
                                                <StatusBadge status={review.status} />
                                            </div>
                                            <h3 className="mt-2 font-semibold text-slate-900">{review.title}</h3>
                                            <p className="mt-1 text-xs text-slate-500">By {review.created_by_user || 'Reader'} · {review.created_at}</p>
                                        </div>
                                        {review.can_edit && <Link href={route('dashboard.be.bookReviews.edit', review.id)} className="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Edit</Link>}
                                    </div>
                                    <p className="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{review.body}</p>
                                    {review.moderation_note && review.status !== 'approved' && <p className="mt-3 rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Moderation note: {review.moderation_note}</p>}
                                </article>
                            ))
                        )}
                    </div>
                    {reviews?.meta && reviewData.length > 0 && (
                        <div className="mt-4 border-t border-slate-200 pt-4">
                            <Paginator pagination={reviews.meta} pageChanged={(page) => window.location.assign(`${window.location.pathname}?page=${page}`)} totalItems={reviewData.length} />
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
