import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel.jsx';
import TextInput from '@/Components/TextInput.jsx';
import InputError from '@/Components/InputError.jsx';
import PrimaryButton from '@/Components/PrimaryButton.jsx';
import RatingStars from '@/Components/RatingStars.jsx';
import StatusBadge from '@/Components/StatusBadge.jsx';
import PageHeader from '@/Components/PageHeader';
import { XCircleIcon } from '@heroicons/react/20/solid/index.js';

export default function Form({ auth, pageTitle, pageDescription, pageData, selectedBook = null, formUrl, booksList = [], statuses = ['pending', 'approved', 'rejected'], canModerate = false }) {
    const { data, setData, patch, processing, errors } = useForm({
        book_id: selectedBook?.id || pageData?.book_id || '',
        rating: pageData?.rating || '',
        title: pageData?.title || '',
        body: pageData?.body || pageData?.content || '',
        status: pageData?.status || 'pending',
        moderation_note: pageData?.moderation_note || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(formUrl);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Reviews', href: route('dashboard.be.bookReviews.list') }, { label: pageData ? 'Edit' : 'Create' }]}
                    badge={<StatusBadge status={data.status} />}
                />
                <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="book_id" value="Book" />
                            <select id="book_id" value={data.book_id} disabled={Boolean(selectedBook || pageData)} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100" onChange={(e) => setData('book_id', e.target.value)}>
                                <option value="">Choose a book</option>
                                {selectedBook && <option value={selectedBook.id}>{selectedBook.title} — {selectedBook.author}</option>}
                                {booksList.map((book) => <option key={book.id} value={book.id}>{book.title} — {book.author}</option>)}
                            </select>
                            <InputError message={errors.book_id} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel htmlFor="rating" value="Rating" />
                            <div className="mt-2 flex flex-col gap-2">
                                <select id="rating" value={data.rating} className="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('rating', e.target.value)}>
                                    <option value="">Choose rating</option>
                                    {[1, 2, 3, 4, 5].map((rating) => <option key={rating} value={rating}>{rating}/5</option>)}
                                </select>
                                <RatingStars rating={data.rating} />
                            </div>
                            <InputError message={errors.rating} className="mt-2" />
                        </div>
                        <div className="md:col-span-2">
                            <InputLabel htmlFor="title" value="Review Title" />
                            <TextInput id="title" value={data.title} className="mt-2 block w-full" onChange={(e) => setData('title', e.target.value)} isFocused />
                            <InputError message={errors.title} className="mt-2" />
                        </div>
                        <div className="md:col-span-2">
                            <InputLabel htmlFor="body" value="Review" />
                            <textarea id="body" value={data.body} rows="7" className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('body', e.target.value)} />
                            <InputError message={errors.body} className="mt-2" />
                            <p className="mt-2 text-xs text-slate-500">Reviews are saved as pending until approved by a moderator.</p>
                        </div>
                        {canModerate && (
                            <>
                                <div>
                                    <InputLabel htmlFor="status" value="Moderation Status" />
                                    <select id="status" value={data.status} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('status', e.target.value)}>
                                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                                    </select>
                                    <InputError message={errors.status} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel htmlFor="moderation_note" value="Moderation Note" />
                                    <TextInput id="moderation_note" value={data.moderation_note} className="mt-2 block w-full" onChange={(e) => setData('moderation_note', e.target.value)} />
                                    <InputError message={errors.moderation_note} className="mt-2" />
                                </div>
                            </>
                        )}
                    </div>
                    <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                        <Link href={data.book_id ? route('dashboard.be.books.show', data.book_id) : route('dashboard.be.bookReviews.list')} className="inline-flex items-center justify-center gap-1.5 rounded-md bg-slate-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-500">
                            <XCircleIcon className="h-5 w-5" />
                            Cancel
                        </Link>
                        <PrimaryButton disabled={processing}>Submit review</PrimaryButton>
                    </div>
                </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
