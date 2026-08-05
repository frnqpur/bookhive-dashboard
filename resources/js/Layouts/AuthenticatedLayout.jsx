import AppFooter from '@/Components/AppFooter';
import FlashMessages from '@/Components/FlashMessages';
import { Link, usePage } from '@inertiajs/react';
import { Fragment, useMemo, useState } from 'react';
import { Dialog, Transition } from '@headlessui/react';
import {
    ArrowLeftOnRectangleIcon,
    ArrowPathIcon,
    Bars3Icon,
    BookOpenIcon,
    ChatBubbleLeftRightIcon,
    ClipboardDocumentListIcon,
    Cog6ToothIcon,
    CodeBracketSquareIcon,
    HomeIcon,
    KeyIcon,
    ShieldCheckIcon,
    UserCircleIcon,
    UsersIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';

const navigationGroups = [
    {
        label: 'Workspace',
        items: [
            { name: 'Dashboard', href: () => route('dashboard'), icon: HomeIcon, can: 'viewDashboard', match: ['/dashboard'] },
            { name: 'Books', href: () => route('dashboard.be.books.list'), icon: BookOpenIcon, canAny: ['viewBooks', 'manageBooks'], match: ['/dashboard/be/books'] },
            { name: 'Reviews', href: () => route('dashboard.be.bookReviews.list'), icon: ChatBubbleLeftRightIcon, canAny: ['viewReviews', 'manageReviews', 'approveReviews'], match: ['/dashboard/be/bookReviews/list'] },
            { name: 'My Reviews', href: () => route('dashboard.be.bookReviews.my'), icon: ClipboardDocumentListIcon, canAny: ['createReviews', 'updateOwnReviews'], match: ['/dashboard/be/bookReviews/my'] },
            { name: 'Moderation', href: () => route('dashboard.be.bookReviews.moderation'), icon: ShieldCheckIcon, can: 'approveReviews', match: ['/dashboard/be/bookReviews/moderation'] },
        ],
    },
    {
        label: 'Administration',
        items: [
            { name: 'Users', href: () => route('dashboard.global.users.list'), icon: UsersIcon, can: 'manageUsers', match: ['/dashboard/global/users'] },
            { name: 'Roles', href: () => route('dashboard.global.roles.list'), icon: ShieldCheckIcon, can: 'manageRoles', match: ['/dashboard/global/roles'] },
            { name: 'Permissions', href: () => route('dashboard.global.permissions.list'), icon: KeyIcon, can: 'managePermissions', match: ['/dashboard/global/permissions'] },
            { name: 'Settings', href: () => route('dashboard.globalSettings.edit'), icon: Cog6ToothIcon, can: 'manageSettings', match: ['/dashboard/globalSettings'] },
            { name: 'Audit Logs', href: () => route('dashboard.auditLogs.index'), icon: ClipboardDocumentListIcon, can: 'viewAuditLogs', match: ['/dashboard/audit-logs'] },
            { name: 'Demo Reset', href: () => route('dashboard.demoReset.index'), icon: ArrowPathIcon, can: 'manageDemoReset', match: ['/dashboard/demo-reset'] },
            { name: 'API Docs', href: () => route('dashboard.apiDocs.index'), icon: CodeBracketSquareIcon, can: 'accessApiDocs', match: ['/dashboard/api-docs'] },
        ],
    },
];

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

function currentPath() {
    if (typeof window === 'undefined') return '/';

    return window.location.pathname.replace(/\/$/, '') || '/';
}

function canSee(item, can = {}, isSuperAdmin = false) {
    if (isSuperAdmin) return true;
    if (item.can) return Boolean(can[item.can]);
    if (item.canAny) return item.canAny.some((key) => Boolean(can[key]));
    return true;
}

function isActive(item, pathname) {
    const href = typeof item.href === 'function' ? item.href() : item.href;
    const normalizedHref = href.replace(/\/$/, '') || '/';

    if (item.match?.some((path) => pathname === path || pathname.startsWith(`${path}/`))) return true;

    return pathname === normalizedHref;
}

function Sidebar({ groups, pathname, onNavigate = () => {} }) {
    return (
        <div className="flex min-h-full grow flex-col overflow-y-auto border-r border-slate-200 bg-white px-5 pb-4">
            <div className="flex h-16 shrink-0 items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-bold text-white shadow-sm">BH</span>
                <div>
                    <p className="text-sm font-bold uppercase tracking-[0.18em] text-indigo-600">BookHive</p>
                    <p className="text-xs text-slate-500">Dashboard</p>
                </div>
            </div>

            <nav className="flex flex-1 flex-col gap-6">
                {groups.map((group) => (
                    <div key={group.label}>
                        <p className="px-2 text-xs font-bold uppercase tracking-wide text-slate-400">{group.label}</p>
                        <ul role="list" className="mt-2 space-y-1">
                            {group.items.map((item) => {
                                const active = isActive(item, pathname);
                                const href = typeof item.href === 'function' ? item.href() : item.href;
                                return (
                                    <li key={item.name}>
                                        <Link
                                            href={href}
                                            onClick={onNavigate}
                                            className={classNames(
                                                active
                                                    ? 'bg-indigo-50 text-indigo-700'
                                                    : 'text-slate-700 hover:bg-slate-50 hover:text-indigo-700',
                                                'group flex gap-x-3 rounded-xl p-2 text-sm font-semibold leading-6 transition'
                                            )}
                                        >
                                            <item.icon
                                                className={classNames(
                                                    active ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-700',
                                                    'h-6 w-6 shrink-0'
                                                )}
                                                aria-hidden="true"
                                            />
                                            {item.name}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                ))}

                <div className="mt-auto rounded-2xl bg-slate-50 p-4 text-xs leading-5 text-slate-600">
                    <p className="font-semibold text-slate-800">Demo notice</p>
                    <p className="mt-1">Public demo data resets automatically every 6 hours. Protected defaults cannot be removed.</p>
                </div>
            </nav>
        </div>
    );
}

export default function Authenticated({ user, header, children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const { props } = usePage();
    const authUser = user || props.auth?.user;
    const roles = props.auth?.roles || [];
    const can = props.auth?.can || {};
    const isSuperAdmin = Boolean(props.auth?.isSuperAdmin);
    const pathname = currentPath();

    const visibleGroups = useMemo(() => (
        navigationGroups
            .map((group) => ({
                ...group,
                items: group.items.filter((item) => canSee(item, can, isSuperAdmin)),
            }))
            .filter((group) => group.items.length > 0)
    ), [can, isSuperAdmin]);

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Transition.Root show={sidebarOpen} as={Fragment}>
                <Dialog as="div" className="relative z-50 lg:hidden" onClose={setSidebarOpen}>
                    <Transition.Child
                        as={Fragment}
                        enter="transition-opacity ease-linear duration-300"
                        enterFrom="opacity-0"
                        enterTo="opacity-100"
                        leave="transition-opacity ease-linear duration-300"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                    >
                        <div className="fixed inset-0 bg-slate-900/80" />
                    </Transition.Child>

                    <div className="fixed inset-0 flex">
                        <Transition.Child
                            as={Fragment}
                            enter="transition ease-in-out duration-300 transform"
                            enterFrom="-translate-x-full"
                            enterTo="translate-x-0"
                            leave="transition ease-in-out duration-300 transform"
                            leaveFrom="translate-x-0"
                            leaveTo="-translate-x-full"
                        >
                            <Dialog.Panel className="relative mr-16 flex w-full max-w-xs flex-1">
                                <Transition.Child
                                    as={Fragment}
                                    enter="ease-in-out duration-300"
                                    enterFrom="opacity-0"
                                    enterTo="opacity-100"
                                    leave="ease-in-out duration-300"
                                    leaveFrom="opacity-100"
                                    leaveTo="opacity-0"
                                >
                                    <div className="absolute left-full top-0 flex w-16 justify-center pt-5">
                                        <button type="button" className="-m-2.5 p-2.5" onClick={() => setSidebarOpen(false)}>
                                            <span className="sr-only">Close sidebar</span>
                                            <XMarkIcon className="h-6 w-6 text-white" aria-hidden="true" />
                                        </button>
                                    </div>
                                </Transition.Child>
                                <Sidebar groups={visibleGroups} pathname={pathname} onNavigate={() => setSidebarOpen(false)} />
                            </Dialog.Panel>
                        </Transition.Child>
                    </div>
                </Dialog>
            </Transition.Root>

            <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
                <Sidebar groups={visibleGroups} pathname={pathname} />
            </div>

            <div className="lg:pl-72">
                <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                    <button type="button" className="-m-2.5 p-2.5 text-slate-700 lg:hidden" onClick={() => setSidebarOpen(true)}>
                        <span className="sr-only">Open sidebar</span>
                        <Bars3Icon className="h-6 w-6" aria-hidden="true" />
                    </button>
                    <div className="h-6 w-px bg-slate-200 lg:hidden" aria-hidden="true" />
                    <div className="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">
                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold text-slate-900">BookHive Dashboard</p>
                            <p className="hidden truncate text-xs text-slate-500 sm:block">Role-based book review and library management dashboard</p>
                        </div>
                        <div className="relative flex items-center gap-3">
                            <div className="hidden text-right sm:block">
                                <p className="text-sm font-semibold text-slate-900">{authUser?.name}</p>
                                <p className="text-xs text-slate-500">{roles.join(', ') || 'Authenticated user'}</p>
                            </div>
                            <button
                                type="button"
                                className="inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-indigo-500 hover:text-indigo-600"
                                onClick={() => setUserMenuOpen((open) => !open)}
                            >
                                <UserCircleIcon className="h-5 w-5" />
                                <span className="hidden sm:inline">Account</span>
                            </button>
                            {userMenuOpen && (
                                <div className="absolute right-0 top-12 z-50 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                    <div className="border-b border-slate-100 px-3 py-2">
                                        <p className="truncate text-sm font-semibold text-slate-900">{authUser?.name}</p>
                                        <p className="truncate text-xs text-slate-500">{authUser?.email}</p>
                                        <p className="mt-1 text-xs font-semibold text-indigo-600">{roles.join(', ') || 'Authenticated user'}</p>
                                    </div>
                                    <Link href={route('dashboard.profile.edit')} className="mt-2 flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                        <UserCircleIcon className="h-5 w-5 text-slate-400" />
                                        Profile
                                    </Link>
                                    <Link
                                        href={route('logout')}
                                        method="post"
                                        as="button"
                                        className="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm font-semibold text-red-700 hover:bg-red-50"
                                    >
                                        <ArrowLeftOnRectangleIcon className="h-5 w-5 text-red-400" />
                                        Log out
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <FlashMessages className="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8" />

                {header && (
                    <header className="border-b border-slate-200 bg-white">
                        <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                    </header>
                )}

                <main className="min-h-[calc(100vh-138px)]">
                    {children}
                </main>

                <AppFooter />
            </div>
        </div>
    );
}
