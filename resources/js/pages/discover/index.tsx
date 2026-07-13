import { Head, Link } from '@inertiajs/react';
import { Boxes, GitFork, PackageCheck, Search, Sparkles, Star } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { discover, home } from '@/routes';
import { project } from '@/routes/builder';
import { clone, show } from '@/routes/loadouts';
import { show as showProfile } from '@/routes/profiles';

type DiscoveryLoadout = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    author_name: string | null;
    author_profile_slug: string | null;
    packages_count: number;
    star_count: number;
    clone_count: number;
    published_at: string | null;
};

type Props = {
    loadouts: DiscoveryLoadout[];
    filters: {
        search: string | null;
        sort: 'popular' | 'stars' | 'clones' | 'recent';
    };
    stats: {
        public_loadouts: number;
        total_stars: number;
    };
};

const sortOptions = [
    { value: 'popular', label: 'Popular' },
    { value: 'stars', label: 'Most starred' },
    { value: 'clones', label: 'Most cloned' },
    { value: 'recent', label: 'Recently published' },
] as const;

export default function DiscoverIndex({ loadouts, filters, stats }: Props) {
    return (
        <>
            <Head title="Discover Loadouts" />
            <main className="min-h-screen bg-background">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-6 md:px-6 lg:py-10">
                    <header className="overflow-hidden rounded-3xl border bg-card">
                        <div className="grid gap-8 p-6 md:grid-cols-[1fr_360px] md:p-8 lg:p-10">
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge variant="secondary">
                                        Public discovery
                                    </Badge>
                                    <Link
                                        href={home()}
                                        className="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                                    >
                                        Laravel Loadout
                                    </Link>
                                </div>
                                <h1 className="mt-5 max-w-3xl text-4xl font-semibold tracking-tight md:text-6xl">
                                    Clone proven Laravel starter stacks.
                                </h1>
                                <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
                                    Browse public loadouts from the community,
                                    inspect their generated files, then clone
                                    one into your builder session to fine-tune
                                    it for your next project.
                                </p>
                                <div className="mt-7 flex flex-wrap gap-3">
                                    <Button asChild>
                                        <Link href={project.url()}>
                                            <Sparkles className="size-4" />
                                            Build from scratch
                                        </Link>
                                    </Button>
                                    <Button asChild variant="outline">
                                        <a href="#loadouts">
                                            <Boxes className="size-4" />
                                            Browse templates
                                        </a>
                                    </Button>
                                </div>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                                <StatCard
                                    label="Public loadouts"
                                    value={stats.public_loadouts}
                                    icon={Boxes}
                                />
                                <StatCard
                                    label="Community stars"
                                    value={stats.total_stars}
                                    icon={Star}
                                />
                            </div>
                        </div>
                    </header>

                    <section
                        id="loadouts"
                        className="grid gap-4 rounded-2xl border bg-card p-4 md:grid-cols-[1fr_220px_auto] md:items-end"
                    >
                        <form
                            action={discover.url()}
                            method="get"
                            className="contents"
                        >
                            <label className="grid gap-2">
                                <span className="text-sm font-medium">
                                    Search
                                </span>
                                <div className="relative">
                                    <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        name="search"
                                        defaultValue={filters.search ?? ''}
                                        placeholder="Agency, API, SaaS..."
                                        className="pl-9"
                                    />
                                </div>
                            </label>

                            <label className="grid gap-2">
                                <span className="text-sm font-medium">
                                    Sort
                                </span>
                                <select
                                    name="sort"
                                    defaultValue={filters.sort}
                                    className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    {sortOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <div className="flex gap-2">
                                <Button type="submit">Apply</Button>
                                {(filters.search || filters.sort !== 'popular') && (
                                    <Button asChild variant="ghost">
                                        <Link href={discover()}>Reset</Link>
                                    </Button>
                                )}
                            </div>
                        </form>
                    </section>

                    {loadouts.length > 0 ? (
                        <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            {loadouts.map((loadout) => (
                                <Card
                                    key={loadout.id}
                                    className="flex rounded-2xl"
                                >
                                    <CardHeader>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Badge variant="secondary">
                                                Public
                                            </Badge>
                                            {loadout.published_at && (
                                                <span className="text-xs text-muted-foreground">
                                                    Published{' '}
                                                    {loadout.published_at}
                                                </span>
                                            )}
                                        </div>
                                        <CardTitle className="text-2xl">
                                            {loadout.title}
                                        </CardTitle>
                                        <CardDescription>
                                            {loadout.description ??
                                                'A Laravel starter stack shared with the community.'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="mt-auto space-y-5">
                                        <div className="grid grid-cols-3 gap-2 text-sm">
                                            <MiniMetric
                                                label="Packages"
                                                value={loadout.packages_count}
                                                icon={PackageCheck}
                                            />
                                            <MiniMetric
                                                label="Stars"
                                                value={loadout.star_count}
                                                icon={Star}
                                            />
                                            <MiniMetric
                                                label="Clones"
                                                value={loadout.clone_count}
                                                icon={GitFork}
                                            />
                                        </div>

                                        <div className="flex items-center justify-between gap-3 text-sm text-muted-foreground">
                                            {loadout.author_profile_slug ? (
                                                <Link
                                                    href={showProfile.url(
                                                        loadout.author_profile_slug,
                                                    )}
                                                    className="underline-offset-4 hover:text-foreground hover:underline"
                                                >
                                                    By {loadout.author_name}
                                                </Link>
                                            ) : (
                                                <span>
                                                    By{' '}
                                                    {loadout.author_name ??
                                                        'Anonymous builder'}
                                                </span>
                                            )}
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <Button asChild>
                                                <Link
                                                    href={show.url(
                                                        loadout.slug,
                                                    )}
                                                >
                                                    View template
                                                </Link>
                                            </Button>
                                            <Button asChild variant="outline">
                                                <Link
                                                    href={clone.url(
                                                        loadout.slug,
                                                    )}
                                                    method="post"
                                                    as="button"
                                                >
                                                    Clone
                                                </Link>
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </section>
                    ) : (
                        <Card className="rounded-2xl border-dashed">
                            <CardHeader>
                                <CardTitle>No matching loadouts</CardTitle>
                                <CardDescription>
                                    Try a broader search or reset the filters.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild variant="outline">
                                    <Link href={discover()}>Reset filters</Link>
                                </Button>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </main>
        </>
    );
}

function StatCard({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="rounded-2xl border bg-background p-5">
            <div className="flex items-center justify-between gap-3">
                <span className="text-sm text-muted-foreground">{label}</span>
                <Icon className="size-4 text-muted-foreground" />
            </div>
            <div className="mt-3 text-4xl font-semibold">{value}</div>
        </div>
    );
}

function MiniMetric({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="rounded-lg bg-muted p-3">
            <div className="flex items-center gap-1 text-xs text-muted-foreground">
                <Icon className="size-3.5" />
                {label}
            </div>
            <div className="mt-1 font-semibold">{value}</div>
        </div>
    );
}
