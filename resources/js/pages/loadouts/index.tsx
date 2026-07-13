import { Form, Head, Link } from '@inertiajs/react';
import { Boxes, GitFork, PackageCheck, Play, Sparkles, Star } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { project } from '@/routes/builder';
import { index as loadoutsIndex, resume, show } from '@/routes/loadouts';
import { index as collaboratorsIndex } from '@/routes/loadouts/collaborators';

type Loadout = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    visibility: 'private' | 'unlisted' | 'public';
    packages_count: number;
    star_count: number;
    clone_count: number;
    source_loadout_id: number | null;
    access_role: 'owner' | 'editor' | 'viewer' | null;
    can_edit: boolean;
    can_manage_collaborators: boolean;
    updated_at: string | null;
    published_at: string | null;
};

type Props = {
    loadouts: Loadout[];
};

export default function LoadoutIndex({ loadouts }: Props) {
    const publishedCount = loadouts.filter(
        (loadout) => loadout.visibility !== 'private',
    ).length;
    const totalPackages = loadouts.reduce(
        (total, loadout) => total + loadout.packages_count,
        0,
    );

    return (
        <>
            <Head title="My Loadouts" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="overflow-hidden rounded-2xl border bg-card">
                    <div className="grid gap-6 p-6 md:grid-cols-[1fr_280px] md:p-8">
                        <div>
                            <Badge variant="secondary">Account library</Badge>
                            <h1 className="mt-4 max-w-2xl text-3xl font-semibold tracking-tight md:text-4xl">
                                Save, resume, and refine your Laravel starter stacks.
                            </h1>
                            <p className="mt-3 max-w-2xl text-muted-foreground">
                                Your cloned and owned loadouts live here. Resume
                                any stack to update choices, package notes, and
                                generated artifacts.
                            </p>
                            <div className="mt-6">
                                <Button asChild>
                                    <Link href={project.url()}>
                                        <Sparkles className="size-4" />
                                        Start a new loadout
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3 md:grid-cols-1">
                            <LibraryStat
                                label="Loadouts"
                                value={loadouts.length}
                                icon={Boxes}
                            />
                            <LibraryStat
                                label="Published"
                                value={publishedCount}
                                icon={GitFork}
                            />
                            <LibraryStat
                                label="Packages"
                                value={totalPackages}
                                icon={PackageCheck}
                            />
                        </div>
                    </div>
                </section>

                {loadouts.length > 0 ? (
                    <section className="grid gap-4 xl:grid-cols-2">
                        {loadouts.map((loadout) => (
                            <Card key={loadout.id} className="rounded-xl">
                                <CardHeader>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge
                                            variant={
                                                loadout.visibility === 'private'
                                                    ? 'outline'
                                                    : 'secondary'
                                            }
                                        >
                                            {loadout.visibility}
                                        </Badge>
                                        {loadout.source_loadout_id && (
                                            <Badge variant="outline">
                                                cloned
                                            </Badge>
                                        )}
                                        {loadout.access_role &&
                                            loadout.access_role !==
                                                'owner' && (
                                                <Badge variant="outline">
                                                    {loadout.access_role}
                                                </Badge>
                                            )}
                                    </div>
                                    <CardTitle className="text-2xl">
                                        {loadout.title}
                                    </CardTitle>
                                    <CardDescription>
                                        {loadout.description ??
                                            'No description yet.'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-5">
                                    <div className="grid gap-3 text-sm text-muted-foreground sm:grid-cols-3">
                                        <Metric
                                            label="Packages"
                                            value={loadout.packages_count}
                                        />
                                        <Metric
                                            label="Stars"
                                            value={loadout.star_count}
                                            icon={Star}
                                        />
                                        <Metric
                                            label="Clones"
                                            value={loadout.clone_count}
                                            icon={GitFork}
                                        />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                        {loadout.updated_at && (
                                            <span>
                                                Updated {loadout.updated_at}
                                            </span>
                                        )}
                                        {loadout.published_at && (
                                            <span>
                                                Published {loadout.published_at}
                                            </span>
                                        )}
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        {loadout.can_edit ? (
                                            <Form {...resume.form(loadout.slug)}>
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        <Play className="size-4" />
                                                        {processing
                                                            ? 'Opening...'
                                                            : 'Resume'}
                                                    </Button>
                                                )}
                                            </Form>
                                        ) : (
                                            <Button disabled variant="secondary">
                                                View access
                                            </Button>
                                        )}
                                        {(loadout.visibility !== 'private' ||
                                            !loadout.can_edit) && (
                                            <Button asChild variant="outline">
                                                <Link
                                                    href={show.url(loadout.slug)}
                                                >
                                                    {loadout.visibility ===
                                                    'private'
                                                        ? 'View details'
                                                        : 'View share page'}
                                                </Link>
                                            </Button>
                                        )}
                                        {loadout.can_manage_collaborators && (
                                            <Button asChild variant="ghost">
                                                <Link
                                                    href={collaboratorsIndex.url(
                                                        loadout.slug,
                                                    )}
                                                >
                                                    Collaborators
                                                </Link>
                                            </Button>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                ) : (
                    <Card className="rounded-xl border-dashed">
                        <CardHeader>
                            <CardTitle>No loadouts yet</CardTitle>
                            <CardDescription>
                                Start a builder draft or clone a shared loadout
                                to populate your account library.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button asChild>
                                <Link href={project.url()}>
                                    <Sparkles className="size-4" />
                                    Build your first loadout
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

function LibraryStat({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="rounded-xl border bg-background p-4">
            <div className="flex items-center justify-between gap-3">
                <span className="text-sm text-muted-foreground">{label}</span>
                <Icon className="size-4 text-muted-foreground" />
            </div>
            <div className="mt-2 text-3xl font-semibold">{value}</div>
        </div>
    );
}

function Metric({
    label,
    value,
    icon: Icon,
}: {
    label: string;
    value: number;
    icon?: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="rounded-lg bg-muted p-3">
            <div className="flex items-center gap-1.5">
                {Icon && <Icon className="size-3.5" />}
                <span>{label}</span>
            </div>
            <div className="mt-1 text-lg font-semibold text-foreground">
                {value}
            </div>
        </div>
    );
}

LoadoutIndex.layout = {
    breadcrumbs: [
        {
            title: 'My Loadouts',
            href: loadoutsIndex(),
        },
    ],
};
