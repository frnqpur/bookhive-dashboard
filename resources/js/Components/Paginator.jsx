import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/20/solid';

function pageRange(currentPage = 1, lastPage = 1) {
    const radius = 2;
    const start = Math.max(1, currentPage - radius);
    const end = Math.min(lastPage, currentPage + radius);
    const pages = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
}

export default function Paginator({ pagination = {}, pageChanged = () => {} }) {
    const currentPage = Number(pagination.current_page || 1);
    const lastPage = Number(pagination.last_page || 1);

    if (!pagination.total || lastPage <= 0) {
        return null;
    }

    const goToPage = (page) => {
        if (page < 1 || page > lastPage || page === currentPage) return;
        pageChanged(page);
    };

    return (
        <div className="flex flex-col gap-3 bg-white px-2 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-slate-600">
                Showing <span className="font-semibold text-slate-900">{pagination.from}</span> to{' '}
                <span className="font-semibold text-slate-900">{pagination.to}</span> of{' '}
                <span className="font-semibold text-slate-900">{pagination.total}</span> results
            </p>

            <div className="flex flex-wrap items-center gap-1">
                <button
                    type="button"
                    className="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    onClick={() => goToPage(currentPage - 1)}
                    disabled={currentPage <= 1}
                >
                    <ChevronLeftIcon className="h-4 w-4" />
                    Previous
                </button>

                {currentPage > 3 && (
                    <button type="button" className="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:inline-flex" onClick={() => goToPage(1)}>1</button>
                )}

                {pageRange(currentPage, lastPage).map((page) => (
                    <button
                        type="button"
                        key={page}
                        className={page === currentPage
                            ? 'rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm'
                            : 'rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50'}
                        onClick={() => goToPage(page)}
                    >
                        {page}
                    </button>
                ))}

                {currentPage < lastPage - 2 && (
                    <button type="button" className="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:inline-flex" onClick={() => goToPage(lastPage)}>{lastPage}</button>
                )}

                <button
                    type="button"
                    className="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    onClick={() => goToPage(currentPage + 1)}
                    disabled={currentPage >= lastPage}
                >
                    Next
                    <ChevronRightIcon className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
