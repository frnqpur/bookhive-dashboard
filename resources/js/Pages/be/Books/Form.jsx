import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel.jsx';
import TextInput from '@/Components/TextInput.jsx';
import InputError from '@/Components/InputError.jsx';
import PrimaryButton from '@/Components/PrimaryButton.jsx';
import StatusBadge from '@/Components/StatusBadge.jsx';
import PageHeader from '@/Components/PageHeader';
import { XCircleIcon } from '@heroicons/react/20/solid/index.js';
import { useEffect, useMemo, useState } from 'react';

export default function Form({ auth, pageTitle, pageDescription, pageData, formUrl, statuses = ['draft', 'published'] }) {
    const [localPreview, setLocalPreview] = useState(null);
    const { data, setData, patch, processing, errors } = useForm({
        title: pageData?.title || '',
        ISBN_10: pageData?.ISBN_10 || '',
        ISBN_13: pageData?.ISBN_13 || '',
        author: pageData?.author || '',
        category: pageData?.category || '',
        cover_image: pageData?.cover_image || '',
        cover_image_file: null,
        description: pageData?.description || '',
        published_year: pageData?.published_year || '',
        status: pageData?.status || 'published',
    });

    const previewUrl = useMemo(() => localPreview || pageData?.cover_url || data.cover_image || null, [localPreview, pageData?.cover_url, data.cover_image]);

    useEffect(() => () => {
        if (localPreview) URL.revokeObjectURL(localPreview);
    }, [localPreview]);

    const submit = (e) => {
        e.preventDefault();
        patch(formUrl, { forceFormData: true });
    };

    const handleFileChange = (event) => {
        const file = event.target.files[0] || null;
        setData('cover_image_file', file);

        if (localPreview) URL.revokeObjectURL(localPreview);
        setLocalPreview(file ? URL.createObjectURL(file) : null);
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={pageTitle} />
            <div className="p-4 sm:p-6">
                <PageHeader
                    title={pageTitle}
                    description={pageDescription}
                    breadcrumbs={[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Books', href: route('dashboard.be.books.list') }, { label: pageData ? 'Edit' : 'Create' }]}
                    badge={<StatusBadge status={data.status} />}
                />
                <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <form onSubmit={submit} className="mt-6 space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">
                        <div>
                            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                {previewUrl ? (
                                    <img src={previewUrl} alt="Cover preview" className="h-72 w-full object-cover" />
                                ) : (
                                    <div className="flex h-72 items-center justify-center px-6 text-center text-sm font-medium text-slate-400">Cover preview will appear here</div>
                                )}
                            </div>
                            <p className="mt-2 text-xs text-slate-500">Accepted formats: JPG, JPEG, PNG, WebP. Max 2MB.</p>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel htmlFor="title" value="Title" />
                                <TextInput id="title" value={data.title} className="mt-2 block w-full" onChange={(e) => setData('title', e.target.value)} isFocused />
                                <InputError message={errors.title} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="author" value="Author" />
                                <TextInput id="author" value={data.author} className="mt-2 block w-full" onChange={(e) => setData('author', e.target.value)} />
                                <InputError message={errors.author} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="ISBN_10" value="ISBN 10" />
                                <TextInput id="ISBN_10" value={data.ISBN_10} className="mt-2 block w-full" onChange={(e) => setData('ISBN_10', e.target.value)} />
                                <InputError message={errors.ISBN_10} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="ISBN_13" value="ISBN 13" />
                                <TextInput id="ISBN_13" value={data.ISBN_13} className="mt-2 block w-full" onChange={(e) => setData('ISBN_13', e.target.value)} />
                                <InputError message={errors.ISBN_13} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="category" value="Category" />
                                <TextInput id="category" value={data.category} className="mt-2 block w-full" onChange={(e) => setData('category', e.target.value)} />
                                <InputError message={errors.category} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="published_year" value="Published Year" />
                                <TextInput id="published_year" type="number" value={data.published_year} className="mt-2 block w-full" onChange={(e) => setData('published_year', e.target.value)} />
                                <InputError message={errors.published_year} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="status" value="Status" />
                                <select id="status" value={data.status} className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('status', e.target.value)}>
                                    {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                                </select>
                                <InputError message={errors.status} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="cover_image_file" value="Upload Cover" />
                                <input id="cover_image_file" type="file" accept="image/png,image/jpeg,image/webp" className="mt-2 block w-full text-sm text-slate-700" onChange={handleFileChange} />
                                <InputError message={errors.cover_image_file} className="mt-2" />
                            </div>
                            <div className="md:col-span-2">
                                <InputLabel htmlFor="cover_image" value="Cover Image URL / Stored Path" />
                                <TextInput id="cover_image" value={data.cover_image} className="mt-2 block w-full" onChange={(e) => setData('cover_image', e.target.value)} />
                                <InputError message={errors.cover_image} className="mt-2" />
                            </div>
                            <div className="md:col-span-2">
                                <InputLabel htmlFor="description" value="Description" />
                                <textarea id="description" value={data.description} rows="7" className="mt-2 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onChange={(e) => setData('description', e.target.value)} />
                                <InputError message={errors.description} className="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                        <Link href={route('dashboard.be.books.list')} className="inline-flex items-center justify-center gap-1.5 rounded-md bg-slate-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-500">
                            <XCircleIcon className="h-5 w-5" />
                            Cancel
                        </Link>
                        <PrimaryButton disabled={processing}>Save book</PrimaryButton>
                    </div>
                </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
