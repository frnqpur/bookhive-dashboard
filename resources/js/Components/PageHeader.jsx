import { Link } from '@inertiajs/react';

export default function PageHeader({
    title,
    description = '',
    breadcrumbs = [],
    actions = null,
    badge = null,
}) {
    return (
        <div className="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            {breadcrumbs.length > 0 && (
                <nav className="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500" aria-label="Breadcrumb">
                    {breadcrumbs.map((item, index) => (
                        <span key={`${item.label}-${index}`} className="flex items-center gap-2">
                            {item.href ? (
                                <Link href={item.href} className="hover:text-indigo-600">{item.label}</Link>
                            ) : (
                                <span className="text-slate-800">{item.label}</span>
                            )}
                            {index < breadcrumbs.length - 1 && <span className="text-slate-300">/</span>}
                        </span>
                    ))}
                </nav>
            )}

            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-3">
                        <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{title}</h1>
                        {badge}
                    </div>
                    {description && <p className="mt-2 max-w-4xl text-sm leading-6 text-slate-600">{description}</p>}
                </div>
                {actions && <div className="flex shrink-0 flex-wrap gap-2">{actions}</div>}
            </div>
        </div>
    );
}
