import AppFooter from '@/Components/AppFooter';

export default function Guest({ children }) {
    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <main className="min-h-[calc(100vh-73px)]">
                {children}
            </main>
            <AppFooter />
        </div>
    );
}
