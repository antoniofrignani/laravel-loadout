import { Head, Link, usePage } from '@inertiajs/react';
import { Boxes, PackagePlus, Share2, Sparkles } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { discover, login, register } from '@/routes';
import { project as builderProject } from '@/routes/builder';
import { index as loadoutsIndex } from '@/routes/loadouts';

const features = [
    {
        title: 'Build step by step',
        description:
            'Choose Laravel version, runtime, starter kit, services, and package assumptions in one guided flow.',
        icon: Boxes,
    },
    {
        title: 'Curate dependencies',
        description:
            'Pick from curated packages, add project-only custom packages, and keep notes for install-time decisions.',
        icon: PackagePlus,
    },
    {
        title: 'Share or download',
        description:
            'Export a lean zip with generated files or publish a shareable loadout URL for teammates.',
        icon: Share2,
    },
];

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Laravel Loadout" />
            <main className="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.18),transparent_34%),linear-gradient(135deg,#fff7ed_0%,#f8fafc_46%,#eef2ff_100%)] text-foreground dark:bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.16),transparent_34%),linear-gradient(135deg,#111827_0%,#0f172a_55%,#020617_100%)]">
                <div className="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 md:px-6 lg:py-8">
                    <header className="flex items-center justify-between gap-4">
                        <Link
                            href={discover()}
                            className="text-sm font-semibold tracking-tight"
                        >
                            Laravel Loadout
                        </Link>
                        <nav className="flex items-center gap-2">
                            <Button asChild variant="ghost">
                                <Link href={discover()}>Discover</Link>
                            </Button>
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={loadoutsIndex()}>
                                        My Loadouts
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button asChild variant="ghost">
                                        <Link href={login()}>Log in</Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={register()}>
                                            Register
                                        </Link>
                                    </Button>
                                </>
                            )}
                        </nav>
                    </header>

                    <section className="grid flex-1 items-center gap-8 py-12 lg:grid-cols-[minmax(0,1fr)_420px] lg:py-20">
                        <div>
                            <Badge className="rounded-full" variant="secondary">
                                Free Laravel starter-stack builder
                            </Badge>
                            <h1 className="mt-6 max-w-4xl text-5xl font-semibold tracking-tight md:text-7xl">
                                Build, save and share your Laravel starter
                                stack.
                            </h1>
                            <p className="mt-6 max-w-2xl text-lg leading-8 text-muted-foreground">
                                Laravel Loadout turns project scaffolding
                                choices into generated files, install notes,
                                and shareable templates. Start from the
                                defaults, then add the packages your projects
                                actually need.
                            </p>
                            <div className="mt-8 flex flex-wrap gap-3">
                                <Button asChild size="lg">
                                    <Link href={builderProject.url()}>
                                        <Sparkles className="size-4" />
                                        Start builder
                                    </Link>
                                </Button>
                                <Button asChild size="lg" variant="outline">
                                    <Link href={discover()}>
                                        Browse loadouts
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <Card className="rounded-3xl border-black/10 bg-white/75 shadow-2xl shadow-black/10 backdrop-blur dark:bg-slate-950/70">
                            <CardHeader>
                                <CardTitle>Generated output</CardTitle>
                                <CardDescription>
                                    Lean template files, no dependency folders.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                {[
                                    'composer.json',
                                    'package.json',
                                    'compose.yaml',
                                    '.env.loadout',
                                    'README.md',
                                ].map((file) => (
                                    <div
                                        key={file}
                                        className="flex items-center justify-between rounded-xl border bg-background/80 px-4 py-3"
                                    >
                                        <span className="font-mono">
                                            {file}
                                        </span>
                                        <Badge variant="outline">preview</Badge>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </section>

                    <section className="grid gap-4 pb-8 md:grid-cols-3">
                        {features.map((feature) => (
                            <Card
                                key={feature.title}
                                className="rounded-2xl bg-white/70 backdrop-blur dark:bg-slate-950/60"
                            >
                                <CardHeader>
                                    <feature.icon className="size-5 text-muted-foreground" />
                                    <CardTitle className="text-lg">
                                        {feature.title}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm leading-6 text-muted-foreground">
                                        {feature.description}
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                </div>
            </main>
        </>
    );
}
