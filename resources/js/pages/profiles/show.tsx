import { Head, Link } from '@inertiajs/react';
import { Boxes, GitFork, PackageCheck, Sparkles, Star } from 'lucide-react';

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

type Props = {
    profile: Profile;
    loadouts: PublicLoadout[];
    stats: {
        public_loadouts: number;
        total_stars: number;
        total_clones: number;
    };
};

export default function ProfileShow({ profile, loadouts, stats }: Props) {
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
                                        'Laravel starter stack curator.'}
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
                                    This curator has not published any public
                                    loadouts.
                                </CardDescription>
                            </CardHeader>
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
