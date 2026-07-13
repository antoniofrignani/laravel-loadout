import { Link } from '@inertiajs/react';
import { Boxes, PackagePlus, Search } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { discover } from '@/routes';
import { index as loadoutsIndex } from '@/routes/loadouts';
import { index as packageCollectionsIndex } from '@/routes/package-collections';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'My Loadouts',
        href: loadoutsIndex(),
        icon: Boxes,
    },
    {
        title: 'Package Collections',
        href: packageCollectionsIndex(),
        icon: PackagePlus,
    },
    {
        title: 'Discover',
        href: discover(),
        icon: Search,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={loadoutsIndex()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
