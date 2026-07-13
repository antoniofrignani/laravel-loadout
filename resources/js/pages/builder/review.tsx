import { Form, Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Download,
    ExternalLink,
    FileText,
    PackageCheck,
    Share2,
} from 'lucide-react';

import PublishController from '@/actions/App/Http/Controllers/Builder/PublishController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    download as builderDownload,
    packages as builderPackages,
    review as builderReview,
} from '@/routes/builder';

type Loadout = {
    id: number;
    title: string;
    slug: string;
    is_shareable: boolean;
    share_url: string | null;
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
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href={builderPackages.url()}>
                                <ArrowLeft className="size-4" />
                                Back to packages
                            </Link>
                        </Button>
                        <Button asChild>
                            <a href={builderDownload.url()}>
                                <Download className="size-4" />
                                Download zip
                            </a>
                        </Button>
                    </div>
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
                                Download contains only generated essential files.
                                Dependencies are installed by the user after
                                extraction.
                            </div>
                            {loadout.is_shareable && loadout.share_url ? (
                                <div className="rounded-md border p-3">
                                    <div className="mb-1 text-muted-foreground">
                                        Share URL
                                    </div>
                                    <a
                                        className="break-all font-mono text-xs underline underline-offset-4"
                                        href={loadout.share_url}
                                    >
                                        {loadout.share_url}
                                    </a>
                                    <Button
                                        asChild
                                        className="mt-3 w-full"
                                        size="sm"
                                        variant="outline"
                                    >
                                        <a href={loadout.share_url}>
                                            <ExternalLink className="size-4" />
                                            Open public page
                                        </a>
                                    </Button>
                                </div>
                            ) : (
                                <Form {...PublishController.form()}>
                                    {({ processing }) => (
                                        <Button
                                            className="w-full"
                                            disabled={processing}
                                        >
                                            <Share2 className="size-4" />
                                            Publish share link
                                        </Button>
                                    )}
                                </Form>
                            )}
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
