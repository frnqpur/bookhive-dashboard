const methodClasses = {
    GET: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    POST: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    PATCH: 'bg-amber-50 text-amber-700 ring-amber-600/20',
    PUT: 'bg-blue-50 text-blue-700 ring-blue-600/20',
    DELETE: 'bg-red-50 text-red-700 ring-red-600/20',
};

function CodeBlock({ children }) {
    return (
        <pre className="overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100 shadow-inner">
            <code>{typeof children === 'string' ? children : JSON.stringify(children, null, 2)}</code>
        </pre>
    );
}

function EndpointCard({ endpoint }) {
    return (
        <article className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset ${methodClasses[endpoint.method] || 'bg-slate-50 text-slate-700 ring-slate-600/20'}`}>
                            {endpoint.method}
                        </span>
                        <code className="break-all rounded-xl bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-900">{endpoint.path}</code>
                    </div>
                    <p className="mt-4 text-sm leading-6 text-slate-600">{endpoint.description}</p>
                </div>
                <span className="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{endpoint.auth}</span>
            </div>

            {endpoint.body && (
                <div className="mt-5">
                    <p className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Request body</p>
                    <CodeBlock>{endpoint.body}</CodeBlock>
                </div>
            )}
        </article>
    );
}

export default function ApiDocumentation({ basePath, demoCredentials = [], authNotes = [], endpoints = [], responseExamples = {}, curlExamples = {}, showHero = true }) {
    return (
        <div className="space-y-8">
            {showHero && (
                <section className="overflow-hidden rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl sm:p-8 lg:p-10">
                    <div className="max-w-3xl">
                        <p className="text-sm font-bold uppercase tracking-[0.22em] text-indigo-300">JWT API Reference</p>
                        <h1 className="mt-4 text-3xl font-bold tracking-tight sm:text-5xl">BookHive Dashboard API</h1>
                        <p className="mt-5 text-base leading-8 text-slate-300 sm:text-lg">
                            A public-safe API for browsing published books and a JWT-protected API for account-based review workflows.
                        </p>
                    </div>
                    <div className="mt-8 rounded-2xl bg-white/10 p-4 text-sm text-slate-100 ring-1 ring-white/10">
                        <p className="text-xs font-bold uppercase tracking-wide text-slate-400">Base URL</p>
                        <code className="mt-2 block break-all text-indigo-200">{basePath}</code>
                    </div>
                </section>
            )}

            <section className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                    <h2 className="text-xl font-bold text-slate-950">Authentication</h2>
                    <div className="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        {authNotes.map((note) => (
                            <p key={note} className="rounded-2xl bg-slate-50 p-4">{note}</p>
                        ))}
                    </div>
                </div>
                <div className="rounded-3xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
                    <h2 className="text-xl font-bold text-slate-950">Demo credentials</h2>
                    <p className="mt-2 text-sm leading-6 text-slate-600">Owner credentials are private and are not included here.</p>
                    <div className="mt-4 space-y-3">
                        {demoCredentials.map((credential) => (
                            <div key={credential.email} className="rounded-2xl bg-white p-3 text-sm shadow-sm">
                                <p className="font-bold text-slate-950">{credential.role}</p>
                                <p className="break-all text-slate-600">{credential.email}</p>
                                <p className="text-slate-500">Password: {credential.password}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section>
                <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">Endpoints</p>
                        <h2 className="mt-1 text-2xl font-bold text-slate-950">Public and protected routes</h2>
                    </div>
                    <span className="text-sm font-semibold text-slate-500">Repository file: docs/API.md</span>
                </div>
                <div className="grid grid-cols-1 gap-4">
                    {endpoints.map((endpoint) => (
                        <EndpointCard key={`${endpoint.method}-${endpoint.path}`} endpoint={endpoint} />
                    ))}
                </div>
            </section>

            <section className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-xl font-bold text-slate-950">Success response format</h2>
                    <div className="mt-4"><CodeBlock>{responseExamples.success}</CodeBlock></div>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-xl font-bold text-slate-950">Validation error</h2>
                    <div className="mt-4"><CodeBlock>{responseExamples.validation}</CodeBlock></div>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-xl font-bold text-slate-950">Unauthorized error</h2>
                    <div className="mt-4"><CodeBlock>{responseExamples.unauthorized}</CodeBlock></div>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-xl font-bold text-slate-950">Forbidden / not found</h2>
                    <div className="mt-4 space-y-4">
                        <CodeBlock>{responseExamples.forbidden}</CodeBlock>
                        <CodeBlock>{responseExamples.notFound}</CodeBlock>
                    </div>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 className="text-xl font-bold text-slate-950">cURL examples</h2>
                <div className="mt-4 grid grid-cols-1 gap-4">
                    {Object.entries(curlExamples).map(([name, command]) => (
                        <div key={name}>
                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">{name}</p>
                            <CodeBlock>{command}</CodeBlock>
                        </div>
                    ))}
                </div>
            </section>
        </div>
    );
}
