import { Head, Link } from '@inertiajs/react';
import { Boxes, GitFork, PackageCheck, Sparkles, Star, Zap } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { discover } from '@/routes';
import { project } from '@/routes/builder';
import { show as showLoadout } from '@/routes/loadouts';
import { show as showCollection } from '@/routes/package-collections';

type Profile = {
    name: string;
    profile_slug: string | null;
    profile_bio: string | null;
    joined_at: string | null;
};

type PublicLoadout = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    packages_count: number;
    star_count: number;
    clone_count: number;
    published_at: string | null;
};

type PublicCollection = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    items_count: number;
};

type ActivityItem = {
    type: 'loadout' | 'collection';
    title: string;
    slug: string;
    description: string | null;
    occurred_at: string | null;
    metric_label: string;
    metric_value: number;
};

type Props = {
    profile: Profile;
    loadouts: PublicLoadout[];
    collections: PublicCollection[];
    activity: ActivityItem[];
    stats: {
        public_loadouts: number;
        public_collections: number;
        total_stars: number;
        total_clones: number;
    };
};

export default function ProfileShow({
    profile,
    loadouts,
    collections,
    activity,
    stats,
}: Props) {
    return (
        <>
            <Head title={`${profile.name} - Laravel Loadout`} />
            <main className="min-h-screen bg-background">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-6 md:px-6 lg:py-10">
                    <header className="overflow-hidden rounded-3xl border bg-card">
                        <div className="grid gap-8 p-6 md:grid-cols-[1fr_360px] md:p-8 lg:p-10">
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge variant="secondary">
                                        Curator profile
                                    </Badge>
                                    {profile.profile_slug && (
                                        <span className="text-sm text-muted-foreground">
                                            /u/{profile.profile_slug}
                                        </span>
                                    )}
                                </div>
                                <h1 className="mt-5 max-w-3xl text-4xl font-semibold tracking-tight md:text-6xl">
                                    {profile.name}
                                </h1>
                                <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
                                    {profile.profile_bio ??
                                        'Sharing Laravel starting points and the packages behind them.'}
                                </p>
                                {profile.joined_at && (
                                    <p className="mt-3 text-sm text-muted-foreground">
                                        Joined {profile.joined_at}
                                    </p>
                                )}
                                <div className="mt-7 flex flex-wrap gap-3">
                                    <Button asChild>
                                        <Link href={discover()}>
                                            <Boxes className="size-4" />
                                            Discover loadouts
                                        </Link>
                                    </Button>
                                    <Button asChild variant="outline">
                                        <Link href={project.url()}>
                                            <Sparkles className="size-4" />
                                            Build your own
                                        </Link>
                                    </Button>
                                </div>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3 md:grid-cols-1">
                                <StatCard
                                    label="Public loadouts"
                                    value={stats.public_loadouts}
                                    icon={Boxes}
                                />
                                <StatCard
                                    label="Collections"
                                    value={stats.public_collections}
                                    icon={PackageCheck}
                                />
                                <StatCard
                                    label="Stars earned"
                                    value={stats.total_stars}
                                    icon={Star}
                                />
                                <StatCard
                                    label="Clones"
                                    value={stats.total_clones}
                                    icon={GitFork}
                                />
                            </div>
                        </div>
                    </header>

                    {activity.length > 0 && (
                        <section className="grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                            <Card className="rounded-2xl bg-muted/40">
                                <CardHeader>
                                    <Badge variant="secondary" className="w-fit">
                                        Recent activity
                                    </Badge>
                                    <CardTitle className="text-3xl">
                                        Latest additions
                                    </CardTitle>
                                    <CardDescription>
                                        Recently published loadouts and package
                                        collections from this curator.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                            <div className="grid gap-3">
                                {activity.map((item) => (
                                    <ActivityCard key={`${item.type}-${item.slug}`} item={item} />
                                ))}
                            </div>
                        </section>
                    )}

                    {collections.length > 0 && (
                        <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            {collections.map((collection) => (
                                <Card
                                    key={collection.id}
                                    className="rounded-2xl border-dashed"
                                >
                                    <CardHeader>
                                        <Badge
                                            variant="outline"
                                            className="w-fit"
                                        >
                                            Package collection
                                        </Badge>
                                        <CardTitle>
                                            {collection.title}
                                        </CardTitle>
                                        <CardDescription>
                                            {collection.description ??
                                                'A package shelf this curator keeps ready to reuse.'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="flex items-center justify-between gap-3">
                                        <span className="text-sm text-muted-foreground">
                                            {collection.items_count} packages
                                        </span>
                                        <Button asChild variant="outline">
                                            <Link
                                                href={showCollection.url(
                                                    collection.slug,
                                                )}
                                            >
                                                View collection
                                            </Link>
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </section>
                    )}

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
                                                'A Laravel starting point shared with the community.'}
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

                                        <Button asChild>
                                            <Link
                                                href={showLoadout.url(
                                                    loadout.slug,
                                                )}
                                            >
                                                View template
                                            </Link>
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </section>
                    ) : (
                        <Card className="rounded-2xl border-dashed">
                            <CardHeader>
                                <CardTitle>No public loadouts yet</CardTitle>
                                <CardDescription>
                                    This curator has not shared a public
                                    loadout yet.
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    )}
                </div>
            </main>
        </>
    );
}

function ActivityCard({ item }: { item: ActivityItem }) {
    const href =
        item.type === 'loadout'
            ? showLoadout.url(item.slug)
            : showCollection.url(item.slug);

    return (
        <Card className="rounded-2xl">
            <CardContent className="flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center">
                <div className="flex gap-4">
                    <div className="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <Zap className="size-5 text-primary" />
                    </div>
                    <div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">
                                {item.type === 'loadout'
                                    ? 'Published loadout'
                                    : 'Updated collection'}
                            </Badge>
                            {item.occurred_at && (
                                <span className="text-xs text-muted-foreground">
                                    {item.occurred_at}
                                </span>
                            )}
                        </div>
                        <h2 className="mt-2 text-xl font-semibold">
                            {item.title}
                        </h2>
                        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                            {item.description ??
                                'A public Laravel Loadout curation item.'}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-3 sm:justify-end">
                    <div className="rounded-xl bg-muted px-3 py-2 text-sm">
                        <span className="font-semibold">
                            {item.metric_value}
                        </span>{' '}
                        {item.metric_label}
                    </div>
                    <Button asChild variant="outline">
                        <Link href={href}>Open</Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
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
