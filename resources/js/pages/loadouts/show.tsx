import { Form, Head, Link } from '@inertiajs/react';
import { Copy, Download, FileText, PackageCheck, Star } from 'lucide-react';

import LoadoutCloneController from '@/actions/App/Http/Controllers/LoadoutCloneController';
import { ArtifactContent } from '@/components/artifact-content';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home, login } from '@/routes';
import {
    download as downloadLoadout,
} from '@/routes/loadouts';
import {
    destroy as unstarLoadout,
    store as starLoadout,
} from '@/routes/loadouts/stars';

type Loadout = {
    title: string;
    slug: string;
    description: string | null;
    visibility: string;
    published_at: string | null;
    share_url: string | null;
    star_count: number;
    viewer_has_starred: boolean;
    viewer_authenticated: boolean;
    viewer_is_owner: boolean;
    can_star: boolean;
    can_clone: boolean;
    can_download: boolean;
};

type Package = {
    id: number;
    display_name: string;
    composer_name: string | null;
    is_custom: boolean;
};

type Artifact = {
    name: string;
    language: string;
    content: string;
};

type Props = {
    loadout: Loadout;
    packages: Package[];
    artifacts: Artifact[];
};

export default function LoadoutShow({ loadout, packages, artifacts }: Props) {
    return (
        <>
            <Head title={loadout.title} />
            <main className="min-h-screen bg-background">
                <div className="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 py-8 md:px-6 lg:py-12">
                    <header className="rounded-2xl border bg-card p-6 md:p-8">
                        <div className="mb-4 flex flex-wrap items-center gap-2">
                            <Badge variant="secondary">
                                {loadout.visibility}
                            </Badge>
                            <Badge variant="outline">
                                <Star className="size-3.5 fill-current" />
                                {loadout.star_count}
                            </Badge>
                            {loadout.published_at && (
                                <span className="text-sm text-muted-foreground">
                                    Published {loadout.published_at}
                                </span>
                            )}
                        </div>
                        <h1 className="max-w-3xl text-4xl font-semibold tracking-tight md:text-5xl">
                            {loadout.title}
                        </h1>
                        <p className="mt-4 max-w-2xl text-muted-foreground">
                            {loadout.description ??
                                'A shared Laravel starter stack generated with Laravel Loadout.'}
                        </p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            {loadout.can_star ? (
                                <Form
                                    {...(loadout.viewer_has_starred
                                        ? unstarLoadout.form(loadout.slug)
                                        : starLoadout.form(loadout.slug))}
                                >
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            variant={
                                                loadout.viewer_has_starred
                                                    ? 'outline'
                                                    : 'secondary'
                                            }
                                        >
                                            <Star
                                                className={
                                                    loadout.viewer_has_starred
                                                        ? 'size-4 fill-current'
                                                        : 'size-4'
                                                }
                                            />
                                            {loadout.viewer_has_starred
                                                ? 'Starred'
                                                : 'Star'}
                                        </Button>
                                    )}
                                </Form>
                            ) : loadout.viewer_is_owner ? (
                                <Button disabled variant="secondary">
                                    <Star className="size-4" />
                                    Your loadout
                                </Button>
                            ) : loadout.visibility !== 'private' ? (
                                <Button asChild variant="secondary">
                                    <Link href={login.url()}>
                                        <Star className="size-4" />
                                        Sign in to star
                                    </Link>
                                </Button>
                            ) : (
                                <Button disabled variant="secondary">
                                    Private access
                                </Button>
                            )}
                            {loadout.can_clone && (
                                <Form {...LoadoutCloneController.form(loadout.slug)}>
                                    {({ processing }) => (
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            variant="default"
                                        >
                                            <Copy className="size-4" />
                                            {processing ? 'Cloning...' : 'Clone loadout'}
                                        </Button>
                                    )}
                                </Form>
                            )}
                            {loadout.can_download && (
                                <Button asChild>
                                    <a href={downloadLoadout.url({ loadout: loadout.slug })}>
                                        <Download className="size-4" />
                                        Download zip
                                    </a>
                                </Button>
                            )}
                            <Button asChild variant="outline">
                                <Link href={home.url()}>Build your own</Link>
                            </Button>
                        </div>
                    </header>

                    <section className="grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                        <Card className="h-fit rounded-lg">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <PackageCheck className="size-5" />
                                    Packages
                                </CardTitle>
                                <CardDescription>
                                    Selected dependencies in this loadout.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="max-h-[520px] space-y-2 overflow-y-auto pr-2">
                                {packages.length > 0 ? (
                                    packages.map((pkg) => (
                                        <div
                                            key={pkg.id}
                                            className="rounded-md bg-muted p-3 text-sm"
                                        >
                                            <div className="truncate font-medium">
                                                {pkg.display_name}
                                            </div>
                                            <div className="truncate font-mono text-xs text-muted-foreground">
                                                {pkg.composer_name}
                                            </div>
                                            {pkg.is_custom && (
                                                <Badge
                                                    className="mt-2"
                                                    variant="outline"
                                                >
                                                    Custom
                                                </Badge>
                                            )}
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">
                                        No packages selected.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        <div className="grid gap-4">
                            {artifacts.map((artifact) => (
                                <Card
                                    key={artifact.name}
                                    className="rounded-lg"
                                >
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2 text-lg">
                                            <FileText className="size-5 text-muted-foreground" />
                                            {artifact.name}
                                        </CardTitle>
                                        <CardDescription>
                                            {artifact.language}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <ArtifactContent
                                            artifact={artifact}
                                            maxHeightClassName="max-h-[420px]"
                                        />
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}
