import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, FileText, PackageCheck } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { packages as builderPackages, review as builderReview } from '@/routes/builder';

type Loadout = {
    id: number;
    title: string;
    slug: string;
};

type Artifact = {
    name: string;
    language: string;
    content: string;
};

type Props = {
    loadout: Loadout;
    artifacts: Artifact[];
};

export default function BuilderReview({ loadout, artifacts }: Props) {
    return (
        <>
            <Head title="Review and Generate" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
                <header className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div className="mb-2 flex items-center gap-2 text-sm text-muted-foreground">
                            <span>Step 6</span>
                            <span>/</span>
                            <span>Review and generate</span>
                        </div>
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Generated starter preview
                        </h1>
                        <p className="mt-2 max-w-3xl text-sm text-muted-foreground">
                            Review the essential files that will be included in
                            the downloadable Laravel Loadout template for{' '}
                            <span className="font-medium text-foreground">
                                {loadout.title}
                            </span>
                            .
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={builderPackages.url()}>
                            <ArrowLeft className="size-4" />
                            Back to packages
                        </Link>
                    </Button>
                </header>

                <section className="grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                    <Card className="h-fit rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PackageCheck className="size-5" />
                                Template summary
                            </CardTitle>
                            <CardDescription>
                                This preview is generated from the current draft
                                loadout session.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div>
                                <div className="text-muted-foreground">
                                    Loadout slug
                                </div>
                                <div className="font-mono">{loadout.slug}</div>
                            </div>
                            <div>
                                <div className="text-muted-foreground">
                                    Generated files
                                </div>
                                <div>{artifacts.length}</div>
                            </div>
                            <div className="rounded-md bg-muted p-3 text-muted-foreground">
                                Zip download comes next; this page locks down
                                the generated file contract first.
                            </div>
                        </CardContent>
                    </Card>

                    <div className="grid gap-4">
                        {artifacts.map((artifact) => (
                            <Card key={artifact.name} className="rounded-lg">
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
                                    <pre className="max-h-[520px] overflow-auto rounded-md bg-zinc-950 p-4 text-xs leading-relaxed text-zinc-100">
                                        <code>{artifact.content}</code>
                                    </pre>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}

BuilderReview.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderPackages.url(),
        },
        {
            title: 'Review',
            href: builderReview.url(),
        },
    ],
};
