import { Link } from '@inertiajs/react';

export default function AppFooter({ className = '' }) {
    const currentYear = new Date().getFullYear();
    return (
        <footer className={`border-t border-slate-200 bg-white/80 ${className}`}>
            <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-5 text-center text-sm text-slate-600 sm:px-6 lg:px-8 md:flex-row md:items-center md:justify-between md:text-left">
                <p>© {currentYear} Developed by Frengki Josua Purba</p>
                <div className="flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                    <Link href="/about-demo" className="font-medium text-slate-700 hover:text-indigo-600">
                        About demo
                    </Link>
                    <Link href="/contact-developer" className="font-medium text-slate-700 hover:text-indigo-600">
                        Contact developer
                    </Link>
                </div>
            </div>
        </footer>
    );
}
