export default function ProtectedNotice({ show = true, children = null }) {
    if (!show) return null;

    return (
        <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800">
            {children || 'This is protected demo/core data. Destructive changes are restricted to keep the public portfolio demo safe.'}
        </div>
    );
}
