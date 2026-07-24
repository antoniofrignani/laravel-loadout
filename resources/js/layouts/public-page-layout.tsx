import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

import AppLayout from '@/layouts/app-layout';

export default function PublicPageLayout({
    children,
}: {
    children: ReactNode;
}) {
    const { auth } = usePage().props;

    if (!auth.user) {
        return children;
    }

    return <AppLayout>{children}</AppLayout>;
}
