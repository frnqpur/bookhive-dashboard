import { debounce } from 'lodash';
import React, { useEffect, useMemo, useRef, useState } from 'react';
import Paginator from './Paginator';
import { CheckCircleIcon, ChevronDownIcon, ChevronUpIcon, EyeIcon, NoSymbolIcon, PencilSquareIcon, XCircleIcon } from '@heroicons/react/20/solid';
import StatusBadge from './StatusBadge.jsx';
import RatingStars from './RatingStars.jsx';
import { Link, router, useForm } from '@inertiajs/react';
import Swal from 'sweetalert2';

const SORT_ASC = 'asc';
const SORT_DESC = 'desc';

function formatHeader(column) {
    return column.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

function formatValue(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    if (Array.isArray(value)) {
        if (value.length === 0) return '-';

        return (
            <div className="flex flex-wrap gap-1">
                {value.map((item, index) => (
                    <span key={`${item}-${index}`} className="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                        {typeof item === 'object' ? item.name || item.title || item.id : item}
                    </span>
                ))}
            </div>
        );
    }

    if (typeof value === 'object') {
        return value.name || value.title || value.email || JSON.stringify(value);
    }

    return value;
}

function formatCell(column, value, row) {
    if (column === 'cover_url') {
        return value ? <img src={value} alt={row?.title || 'Book cover'} className="h-16 w-12 rounded-lg object-cover ring-1 ring-slate-200" /> : <div className="flex h-16 w-12 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-semibold text-slate-400">No cover</div>;
    }

    if (column === 'status') {
        return <StatusBadge status={value} />;
    }

    if (column === 'average_rating') {
        return <RatingStars rating={value} total={row?.total_reviews ?? null} />;
    }

    if (column === 'rating') {
        return <RatingStars rating={value} />;
    }

    return formatValue(value);
}

export default function DataTable({ excludedColumns = [], fetchUrl, columns = [], actionUrls = {}, canEdit = true, canDelete = true, mobileTitleColumn = 'title', mobileSubtitleColumn = null }) {
    const [data, setData] = useState([]);
    const [perPage, setPerPage] = useState(10);
    const [sortColumn, setSortColumn] = useState(columns[0] || 'id');
    const [sortOrder, setSortOrder] = useState(SORT_DESC);
    const [search, setSearch] = useState('');
    const [pagination, setPagination] = useState({});
    const [currentPage, setCurrentPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [errorMessage, setErrorMessage] = useState('');
    const { delete: destroy } = useForm();

    const visibleColumns = useMemo(() => columns.filter((column) => !excludedColumns.includes(column)), [columns, excludedColumns]);
    const showActionsColumn = Boolean(canEdit || canDelete || actionUrls.moderateRouteName || actionUrls.showRouteName || actionUrls.moderationPageRouteName);

    const handleSort = (column) => {
        if (column === sortColumn) {
            setSortOrder(sortOrder === SORT_ASC ? SORT_DESC : SORT_ASC);
            return;
        }

        setSortColumn(column);
        setSortOrder(SORT_ASC);
    };

    const handleSearch = useRef(
        debounce((query) => {
            setSearch(query);
            setCurrentPage(1);
        }, 400)
    ).current;


    const canRunAction = (rule, row, flag) => {
        if (typeof rule === 'function') {
            return Boolean(rule(row));
        }

        return Boolean(rule) && (row?.[flag] ?? true);
    };

    const moderateReview = (row, status) => {
        if (!actionUrls.moderateRouteName) return;

        const action = status === 'approved' ? 'approve' : 'reject';

        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} this review?`,
            text: status === 'approved' ? 'Approved reviews become visible as approved demo content.' : 'Rejected reviews remain visible to managers and the owner.',
            icon: 'question',
            showDenyButton: true,
            confirmButtonText: `Yes, ${action}`,
            denyButtonText: 'Cancel',
            allowOutsideClick: false,
        }).then((result) => {
            if (!result.isConfirmed) return;

            router.patch(route(actionUrls.moderateRouteName, row.id), { status }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: `Review ${status}`,
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    setData((currentRows) => currentRows.map((item) => item.id === row.id ? { ...item, status } : item));
                },
                onError: () => {
                    Swal.fire('Action failed', 'You may not have permission to moderate this review.', 'error');
                },
            });
        });
    };

    const confirmDelete = (e, removeUrl, rowId = null) => {
        e.preventDefault();

        Swal.fire({
            title: 'Delete this record?',
            text: 'Protected default data cannot be removed by demo/public users.',
            icon: 'warning',
            showDenyButton: true,
            confirmButtonText: 'Yes, delete it',
            denyButtonText: 'Cancel',
            allowOutsideClick: false,
        }).then((result) => {
            if (!result.isConfirmed) return;

            destroy(removeUrl, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Record deleted successfully',
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    if (rowId !== null) {
                        setData((currentRows) => currentRows.filter((item) => item.id !== rowId));
                    }
                },
                onError: () => {
                    Swal.fire('Delete failed', 'You may not have permission to delete this protected record.', 'error');
                },
            });
        });
    };

    useEffect(() => {
        const controller = new AbortController();

        const fetchData = async () => {
            setLoading(true);
            setErrorMessage('');

            try {
                const response = await axios.get(fetchUrl, {
                    params: {
                        search,
                        sort_field: sortColumn,
                        sort_order: sortOrder,
                        per_page: perPage,
                        page: currentPage,
                    },
                    signal: controller.signal,
                });

                setData(response.data.data || []);
                setPagination(response.data.meta || {});
            } catch (error) {
                if (error.name !== 'CanceledError') {
                    setData([]);
                    setPagination({});
                    setErrorMessage(error.response?.data?.message || 'Unable to load table data. Please refresh and try again.');
                }
            } finally {
                setLoading(false);
            }
        };

        fetchData();

        return () => controller.abort();
    }, [fetchUrl, perPage, sortColumn, sortOrder, search, currentPage]);

    return (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div className="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <input
                    className="block w-full rounded-xl border-0 py-2 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm"
                    placeholder="Search..."
                    type="search"
                    onChange={(e) => handleSearch(e.target.value)}
                />
                <div className="flex items-center gap-2">
                    <label htmlFor="pagePer" className="text-sm font-medium text-slate-700">Rows</label>
                    <select
                        className="rounded-xl border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
                        value={perPage}
                        id="pagePer"
                        onChange={(e) => {
                            setCurrentPage(1);
                            setPerPage(Number(e.target.value));
                        }}
                    >
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            {errorMessage && <div className="border-b border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{errorMessage}</div>}

            <div className="space-y-3 p-3 md:hidden">
                {loading ? (
                    <div className="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">Loading...</div>
                ) : data.length === 0 ? (
                    <div className="rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500">No items found.</div>
                ) : (
                    data.map((row) => {
                        const rowCanView = Boolean(actionUrls.showRouteName && (row.can_view ?? true));
                        const rowCanEdit = canRunAction(canEdit, row, 'can_edit');
                        const rowCanDelete = canRunAction(canDelete, row, 'can_delete');
                        const rowCanModerationPage = Boolean(actionUrls.moderationPageRouteName && row.can_approve);
                        const rowCanModerate = Boolean(actionUrls.moderateRouteName && row.can_approve);

                        return (
                            <article key={`card-${row.id}`} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="flex gap-3">
                                    {row.cover_url && <img src={row.cover_url} alt={row.title || 'Cover'} className="h-20 w-14 rounded-xl object-cover ring-1 ring-slate-200" />}
                                    <div className="min-w-0 flex-1">
                                        <h3 className="truncate text-sm font-semibold text-slate-900">{formatValue(row[mobileTitleColumn])}</h3>
                                        {mobileSubtitleColumn && <p className="mt-1 truncate text-xs text-slate-500">{formatValue(row[mobileSubtitleColumn])}</p>}
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {row.status && <StatusBadge status={row.status} />}
                                            {row.average_rating !== undefined && <RatingStars rating={row.average_rating} total={row.total_reviews ?? null} />}
                                            {row.rating !== undefined && <RatingStars rating={row.rating} />}
                                        </div>
                                    </div>
                                </div>
                                <dl className="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-600">
                                    {visibleColumns.filter((column) => !['cover_url', mobileTitleColumn, mobileSubtitleColumn, 'status', 'average_rating', 'rating'].includes(column)).slice(0, 6).map((column) => (
                                        <div key={`mobile-${row.id}-${column}`} className="rounded-lg bg-slate-50 p-2">
                                            <dt className="font-semibold text-slate-500">{formatHeader(column)}</dt>
                                            <dd className="mt-0.5 truncate text-slate-800">{formatCell(column, row[column], row)}</dd>
                                        </div>
                                    ))}
                                </dl>
                                {(rowCanView || rowCanEdit || rowCanDelete || rowCanModerate || rowCanModerationPage) && (
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        {rowCanView && <Link className="rounded-lg bg-slate-700 px-2.5 py-1.5 text-xs font-semibold text-white" href={route(actionUrls.showRouteName, row.id)}>View</Link>}
                                        {rowCanModerationPage && <Link className="rounded-lg bg-blue-600 px-2.5 py-1.5 text-xs font-semibold text-white" href={route(actionUrls.moderationPageRouteName, row.id)}>Moderate</Link>}
                                        {rowCanModerate && row.status !== 'approved' && <button type="button" className="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white" onClick={() => moderateReview(row, 'approved')}>Approve</button>}
                                        {rowCanModerate && row.status !== 'rejected' && <button type="button" className="rounded-lg bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white" onClick={() => moderateReview(row, 'rejected')}>Reject</button>}
                                        {rowCanEdit && actionUrls.editRouteName && <Link className="rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white" href={route(actionUrls.editRouteName, row.id)}>Edit</Link>}
                                        {rowCanDelete && actionUrls.removeRouteName && <button type="button" className="rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white" onClick={(e) => confirmDelete(e, route(actionUrls.removeRouteName, row.id), row.id)}>Delete</button>}
                                    </div>
                                )}
                            </article>
                        );
                    })
                )}
            </div>

            <div className="hidden overflow-x-auto md:block">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50">
                        <tr>
                            {visibleColumns.map((column) => (
                                <th key={column} scope="col" className="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    <button type="button" className="group inline-flex items-center gap-1" onClick={() => handleSort(column)}>
                                        {formatHeader(column)}
                                        {column === sortColumn && (
                                            sortOrder === SORT_ASC ? <ChevronUpIcon className="h-4 w-4" /> : <ChevronDownIcon className="h-4 w-4" />
                                        )}
                                    </button>
                                </th>
                            ))}
                            {showActionsColumn && <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 bg-white">
                        {loading ? (
                            <tr>
                                <td className="px-4 py-10 text-center text-sm text-slate-500" colSpan={visibleColumns.length + (showActionsColumn ? 1 : 0)}>Loading...</td>
                            </tr>
                        ) : data.length === 0 ? (
                            <tr>
                                <td className="px-4 py-10 text-center text-sm text-slate-500" colSpan={visibleColumns.length + (showActionsColumn ? 1 : 0)}>No items found.</td>
                            </tr>
                        ) : (
                            data.map((row) => {
                                const rowCanEdit = canRunAction(canEdit, row, 'can_edit');
                                const rowCanDelete = canRunAction(canDelete, row, 'can_delete');
                                const rowCanView = Boolean(actionUrls.showRouteName && (row.can_view ?? true));
                                const rowCanModerationPage = Boolean(actionUrls.moderationPageRouteName && row.can_approve);
                                const rowCanModerate = Boolean(actionUrls.moderateRouteName && row.can_approve);

                                return (
                                <tr key={row.id} className="hover:bg-slate-50">
                                    {visibleColumns.map((column) => (
                                        <td key={`${row.id}-${column}`} className="max-w-xs whitespace-nowrap px-4 py-3 text-sm text-slate-700">
                                            {formatCell(column, row[column], row)}
                                        </td>
                                    ))}
                                    {showActionsColumn && (
                                        <td className="whitespace-nowrap px-4 py-3 text-right text-sm">
                                            {(rowCanView || rowCanEdit || rowCanDelete || rowCanModerate || rowCanModerationPage) ? (
                                            <div className="flex justify-end gap-2">
                                                {rowCanView && (
                                                    <Link
                                                        className="inline-flex items-center gap-1 rounded-lg bg-slate-700 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-slate-600"
                                                        href={route(actionUrls.showRouteName, row.id)}
                                                    >
                                                        <EyeIcon className="h-4 w-4" />
                                                        View
                                                    </Link>
                                                )}
                                                {rowCanModerationPage && (
                                                    <Link
                                                        className="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-500"
                                                        href={route(actionUrls.moderationPageRouteName, row.id)}
                                                    >
                                                        <EyeIcon className="h-4 w-4" />
                                                        Moderate
                                                    </Link>
                                                )}
                                                {rowCanModerate && row.status !== 'approved' && (
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500"
                                                        onClick={() => moderateReview(row, 'approved')}
                                                    >
                                                        <CheckCircleIcon className="h-4 w-4" />
                                                        Approve
                                                    </button>
                                                )}
                                                {rowCanModerate && row.status !== 'rejected' && (
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-500"
                                                        onClick={() => moderateReview(row, 'rejected')}
                                                    >
                                                        <NoSymbolIcon className="h-4 w-4" />
                                                        Reject
                                                    </button>
                                                )}
                                                {rowCanDelete && actionUrls.removeRouteName && (
                                                    <button
                                                        type="button"
                                                        className="inline-flex items-center gap-1 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-500"
                                                        onClick={(e) => confirmDelete(e, route(actionUrls.removeRouteName, row.id), row.id)}
                                                    >
                                                        <XCircleIcon className="h-4 w-4" />
                                                        Delete
                                                    </button>
                                                )}
                                                {rowCanEdit && actionUrls.editRouteName && (
                                                    <Link
                                                        className="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500"
                                                        href={route(actionUrls.editRouteName, row.id)}
                                                    >
                                                        <PencilSquareIcon className="h-4 w-4" />
                                                        Edit
                                                    </Link>
                                                )}
                                            </div>
                                            ) : (
                                                <span className="text-xs text-slate-400">{row.is_protected ? 'Protected' : 'No action'}</span>
                                            )}
                                        </td>
                                    )}
                                </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>

            {data.length > 0 && !loading && (
                <div className="border-t border-slate-200 p-3">
                    <Paginator pagination={pagination} pageChanged={(page) => setCurrentPage(page)} totalItems={data.length} />
                </div>
            )}
        </div>
    );
}
