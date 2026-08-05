export default function EmptyState({ title = 'No data yet', description = 'Create a new record or adjust your filters to see content here.', action = null }) {
    return (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <h3 className="text-base font-semibold text-slate-900">{title}</h3>
            <p className="mx-auto mt-2 max-w-md text-sm text-slate-600">{description}</p>
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}
