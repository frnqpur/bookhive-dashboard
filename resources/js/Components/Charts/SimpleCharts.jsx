function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export function ChartCard({ title, description, children, action = null }) {
    return (
        <section className="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 className="text-base font-bold text-slate-950 sm:text-lg">{title}</h2>
                    {description && <p className="mt-1 text-sm leading-6 text-slate-500">{description}</p>}
                </div>
                {action}
            </div>
            <div className="mt-5">{children}</div>
        </section>
    );
}

export function EmptyChart({ message = 'No chart data available yet.' }) {
    return <div className="rounded-2xl bg-slate-50 p-8 text-center text-sm font-medium text-slate-500">{message}</div>;
}

export function BarChart({ data = [], labelKey = 'label', valueKey = 'value', suffix = '' }) {
    const max = Math.max(...data.map((item) => Number(item[valueKey]) || 0), 0);

    if (!data.length || max === 0) return <EmptyChart />;

    return (
        <div className="space-y-3">
            {data.map((item) => {
                const value = Number(item[valueKey]) || 0;
                const width = max > 0 ? Math.max(4, Math.round((value / max) * 100)) : 0;
                return (
                    <div key={item[labelKey]}>
                        <div className="mb-1 flex items-center justify-between gap-3 text-xs font-semibold text-slate-600">
                            <span className="truncate">{item[labelKey]}</span>
                            <span className="shrink-0 text-slate-900">{value}{suffix}</span>
                        </div>
                        <div className="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div className="h-full rounded-full bg-indigo-600" style={{ width: `${width}%` }} />
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

export function DonutChart({ data = [], labelKey = 'label', valueKey = 'value' }) {
    const total = data.reduce((sum, item) => sum + (Number(item[valueKey]) || 0), 0);
    const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#0f172a', '#64748b'];
    let offset = 0;

    if (!data.length || total === 0) return <EmptyChart />;

    const circles = data.map((item, index) => {
        const value = Number(item[valueKey]) || 0;
        const percent = value / total;
        const dash = `${percent * 100} ${100 - percent * 100}`;
        const currentOffset = offset;
        offset -= percent * 100;
        return (
            <circle
                key={item[labelKey]}
                cx="18"
                cy="18"
                r="15.9155"
                fill="transparent"
                stroke={colors[index % colors.length]}
                strokeWidth="4"
                strokeDasharray={dash}
                strokeDashoffset={currentOffset}
            />
        );
    });

    return (
        <div className="grid grid-cols-1 items-center gap-5 sm:grid-cols-[160px,1fr]">
            <div className="relative mx-auto h-40 w-40">
                <svg viewBox="0 0 36 36" className="h-40 w-40 -rotate-90">
                    <circle cx="18" cy="18" r="15.9155" fill="transparent" stroke="#e2e8f0" strokeWidth="4" />
                    {circles}
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-center">
                    <span className="text-2xl font-bold text-slate-950">{total}</span>
                    <span className="text-xs font-semibold uppercase tracking-wide text-slate-400">Total</span>
                </div>
            </div>
            <div className="space-y-2">
                {data.map((item, index) => {
                    const value = Number(item[valueKey]) || 0;
                    const percentage = total ? Math.round((value / total) * 100) : 0;
                    return (
                        <div key={item[labelKey]} className="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 text-sm">
                            <span className="flex min-w-0 items-center gap-2 font-semibold text-slate-700">
                                <span className="h-2.5 w-2.5 shrink-0 rounded-full" style={{ backgroundColor: colors[index % colors.length] }} />
                                <span className="truncate">{item[labelKey]}</span>
                            </span>
                            <span className="shrink-0 font-bold text-slate-950">{value} · {percentage}%</span>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

export function LineChart({ data = [], labelKey = 'label', valueKey = 'value' }) {
    const values = data.map((item) => Number(item[valueKey]) || 0);
    const max = Math.max(...values, 0);
    const width = 500;
    const height = 180;
    const padding = 22;

    if (!data.length || max === 0) return <EmptyChart />;

    const points = data.map((item, index) => {
        const x = data.length === 1 ? width / 2 : padding + (index * (width - padding * 2)) / (data.length - 1);
        const y = height - padding - ((Number(item[valueKey]) || 0) / max) * (height - padding * 2);
        return { x, y, item };
    });

    const path = points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`).join(' ');

    return (
        <div className="overflow-x-auto">
            <svg viewBox={`0 0 ${width} ${height}`} className="h-56 min-w-[520px] rounded-2xl bg-slate-50">
                {[0, 1, 2, 3].map((line) => {
                    const y = padding + (line * (height - padding * 2)) / 3;
                    return <line key={line} x1={padding} x2={width - padding} y1={y} y2={y} stroke="#e2e8f0" strokeWidth="1" />;
                })}
                <path d={path} fill="none" stroke="#4f46e5" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
                {points.map(({ x, y, item }) => (
                    <g key={item[labelKey]}>
                        <circle cx={x} cy={y} r="5" fill="#4f46e5" />
                        <text x={x} y={height - 6} textAnchor="middle" className="fill-slate-500 text-[11px] font-semibold">{item[labelKey]}</text>
                        <text x={x} y={y - 10} textAnchor="middle" className="fill-slate-900 text-[12px] font-bold">{item[valueKey]}</text>
                    </g>
                ))}
            </svg>
        </div>
    );
}

export function MetricCard({ name, value, description, icon: Icon, tone = 'indigo' }) {
    const tones = {
        indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        emerald: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        amber: 'bg-amber-50 text-amber-700 ring-amber-100',
        rose: 'bg-rose-50 text-rose-700 ring-rose-100',
        slate: 'bg-slate-100 text-slate-700 ring-slate-200',
    };

    return (
        <div className="group overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">{name}</p>
                    <p className="mt-2 text-3xl font-bold tracking-tight text-slate-950">{value}</p>
                </div>
                {Icon && (
                    <div className={classNames('rounded-2xl p-3 ring-1', tones[tone] || tones.indigo)}>
                        <Icon className="h-6 w-6" />
                    </div>
                )}
            </div>
            {description && <p className="mt-3 text-sm leading-6 text-slate-500">{description}</p>}
        </div>
    );
}
