import { Form, Head } from '@inertiajs/react';
import {
    ExternalLink,
    PackageCheck,
    Share2,
} from 'lucide-react';

import PublishController from '@/actions/App/Http/Controllers/Builder/PublishController';
import { ArtifactBrowser } from '@/components/artifact-browser';
import { BuilderStepNavigation } from '@/components/builder-step-navigation';
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
    public_id: string;
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
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24 md:p-6 md:pb-24">
                <header>
                    <div>
                        <div className="mb-2 flex items-center gap-2 text-sm text-muted-foreground">
                            <span>Step 6</span>
                            <span>/</span>
                            <span>Review and generate</span>
                        </div>
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Review your loadout
                        </h1>
                        <p className="mt-2 max-w-3xl text-sm text-muted-foreground">
                            Inspect the files before you hand this stack to a
                            project or a teammate. They are built from{' '}
                            <span className="font-medium text-foreground">
                                {loadout.title}
                            </span>
                            .
                        </p>
                    </div>
                </header>

                <section className="grid gap-4 lg:grid-cols-[320px_minmax(0,1fr)]">
                    <Card className="h-fit rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PackageCheck className="size-5" />
                                Loadout summary
                            </CardTitle>
                            <CardDescription>
                                The configuration below is ready to download or
                                publish as an unlisted link.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div>
                                <div className="text-muted-foreground">
                                    Share ID
                                </div>
                                <div className="font-mono">
                                    {loadout.public_id}
                                </div>
                            </div>
                            <div>
                                <div className="text-muted-foreground">
                                    Files in the archive
                                </div>
                                <div>{artifacts.length}</div>
                            </div>
                            <div className="rounded-md bg-muted p-3 text-muted-foreground">
                                The archive contains configuration and setup
                                files only. Install Composer and Yarn
                                dependencies after extracting it.
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
                                            Create share link
                                        </Button>
                                    )}
                                </Form>
                            )}
                        </CardContent>
                    </Card>

                    <ArtifactBrowser artifacts={artifacts} />
                </section>

                <BuilderStepNavigation
                    next={{
                        href: builderDownload.url(),
                        label: 'Download zip',
                        download: true,
                        external: true,
                    }}
                    previous={{ href: builderPackages.url(), label: 'Packages' }}
                    step={6}
                />
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
