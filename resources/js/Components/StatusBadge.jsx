export default function StatusBadge({ status }) {
    const normalized = String(status || 'unknown').toLowerCase();
    const classes = {
        published: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        draft: 'bg-slate-50 text-slate-700 ring-slate-600/20',
        pending: 'bg-amber-50 text-amber-700 ring-amber-600/20',
        approved: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        rejected: 'bg-red-50 text-red-700 ring-red-600/20',
        active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        disabled: 'bg-slate-50 text-slate-700 ring-slate-600/20',
        success: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        failed: 'bg-red-50 text-red-700 ring-red-600/20',
        running: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        archived: 'bg-slate-50 text-slate-700 ring-slate-600/20',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize ring-1 ring-inset ${classes[normalized] || 'bg-slate-50 text-slate-700 ring-slate-600/20'}`}>
            {normalized.replaceAll('_', ' ')}
        </span>
    );
}
