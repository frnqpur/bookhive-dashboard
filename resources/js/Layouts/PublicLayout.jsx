import AppFooter from '@/Components/AppFooter';
import { Link } from '@inertiajs/react';

export default function PublicLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <header className="border-b border-slate-200 bg-white/90 backdrop-blur">
                <nav className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <Link href="/login" className="flex items-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-bold text-white shadow-sm">BH</span>
                        <div>
                            <p className="text-sm font-bold uppercase tracking-[0.2em] text-indigo-600">BookHive</p>
                            <p className="text-xs text-slate-500">Dashboard Demo</p>
                        </div>
                    </Link>
                    <div className="flex items-center gap-3 text-sm font-semibold">
                        <Link href="/about-demo" className="hidden text-slate-600 hover:text-indigo-600 sm:inline-block">
                            About Demo
                        </Link>
                        <Link href="/contact-developer" className="hidden text-slate-600 hover:text-indigo-600 sm:inline-block">
                            Contact
                        </Link>
                        <Link href="/api-docs" className="hidden text-slate-600 hover:text-indigo-600 md:inline-block">
                            API Docs
                        </Link>
                        <Link href="/login" className="rounded-full border border-slate-300 px-4 py-2 text-slate-700 hover:border-indigo-500 hover:text-indigo-600">
                            Login
                        </Link>
                        <Link href="/register" className="rounded-full bg-indigo-600 px-4 py-2 text-white shadow-sm hover:bg-indigo-500">
                            Register
                        </Link>
                    </div>
                </nav>
            </header>
            <main>{children}</main>
            <AppFooter />
        </div>
    );
}
