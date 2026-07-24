import { Form, Head } from '@inertiajs/react';
import { ExternalLink, LockKeyhole, Share2 } from 'lucide-react';
import { useState } from 'react';

import PublishController from '@/actions/App/Http/Controllers/Builder/PublishController';
import { ArtifactBrowser } from '@/components/artifact-browser';
import { BuilderStepNavigation } from '@/components/builder-step-navigation';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
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
    visibility: 'private' | 'unlisted' | 'public';
    is_locked: boolean;
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
    const [isPublishDialogOpen, setIsPublishDialogOpen] = useState(false);
    const [publishVisibility, setPublishVisibility] = useState<
        'unlisted' | 'public'
    >('unlisted');

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

                <section className="space-y-4">
                    <div className="flex flex-col justify-between gap-4 rounded-lg border bg-card p-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 className="text-sm font-medium">
                                {loadout.is_shareable
                                    ? loadout.visibility === 'public'
                                        ? 'Published in Discover'
                                        : 'Shared loadout'
                                    : 'Ready to share'}
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {loadout.is_shareable
                                    ? loadout.visibility === 'public'
                                        ? 'This loadout is listed in Discover and locked at its current configuration.'
                                        : 'This loadout is locked at its current configuration.'
                                    : 'Create a permanent link for this exact configuration.'}
                            </p>
                        </div>
                        <div className="shrink-0">
                            {loadout.is_shareable && loadout.share_url ? (
                                <Button asChild variant="outline">
                                    <a
                                        href={loadout.share_url}
                                        rel="noreferrer"
                                        target="_blank"
                                    >
                                        <ExternalLink className="size-4" />
                                        Open public page
                                    </a>
                                </Button>
                            ) : (
                                <Dialog
                                    onOpenChange={setIsPublishDialogOpen}
                                    open={isPublishDialogOpen}
                                >
                                    <DialogTrigger asChild>
                                        <Button className="w-full">
                                            <Share2 className="size-4" />
                                            Create share link
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>
                                                Choose how to publish this
                                                loadout
                                            </DialogTitle>
                                            <DialogDescription>
                                                Publishing creates a permanent
                                                URL and locks this exact
                                                configuration. Its name,
                                                foundation, runtime, and
                                                packages cannot be changed
                                                afterwards.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <Form
                                            onSuccess={() =>
                                                setIsPublishDialogOpen(false)
                                            }
                                            {...PublishController.form()}
                                        >
                                            {({ processing }) => (
                                                <>
                                                    <div className="grid gap-2">
                                                        <label
                                                            className={cn(
                                                                'cursor-pointer rounded-lg border p-3 transition-colors',
                                                                publishVisibility ===
                                                                    'unlisted'
                                                                    ? 'border-foreground bg-muted/60'
                                                                    : 'hover:bg-muted/50',
                                                            )}
                                                        >
                                                            <input
                                                                checked={
                                                                    publishVisibility ===
                                                                    'unlisted'
                                                                }
                                                                className="sr-only"
                                                                name="visibility"
                                                                onChange={() =>
                                                                    setPublishVisibility(
                                                                        'unlisted',
                                                                    )
                                                                }
                                                                type="radio"
                                                                value="unlisted"
                                                            />
                                                            <span className="text-sm font-medium">
                                                                Unlisted link
                                                            </span>
                                                            <span className="mt-1 block text-sm text-muted-foreground">
                                                                Anyone with the
                                                                link can view
                                                                it. It will not
                                                                appear in
                                                                Discover.
                                                            </span>
                                                        </label>
                                                        <label
                                                            className={cn(
                                                                'cursor-pointer rounded-lg border p-3 transition-colors',
                                                                publishVisibility ===
                                                                    'public'
                                                                    ? 'border-foreground bg-muted/60'
                                                                    : 'hover:bg-muted/50',
                                                            )}
                                                        >
                                                            <input
                                                                checked={
                                                                    publishVisibility ===
                                                                    'public'
                                                                }
                                                                className="sr-only"
                                                                name="visibility"
                                                                onChange={() =>
                                                                    setPublishVisibility(
                                                                        'public',
                                                                    )
                                                                }
                                                                type="radio"
                                                                value="public"
                                                            />
                                                            <span className="text-sm font-medium">
                                                                Publish to
                                                                Discover
                                                            </span>
                                                            <span className="mt-1 block text-sm text-muted-foreground">
                                                                List it in the
                                                                public catalogue
                                                                where developers
                                                                can find, star,
                                                                and clone it.
                                                            </span>
                                                        </label>
                                                    </div>
                                                    <div className="rounded-lg border bg-muted/50 p-3 text-sm text-muted-foreground">
                                                        Need to revise it later?
                                                        Clone the shared loadout
                                                        to start an editable
                                                        copy.
                                                    </div>
                                                    <DialogFooter>
                                                        <Button
                                                            onClick={() =>
                                                                setIsPublishDialogOpen(
                                                                    false,
                                                                )
                                                            }
                                                            type="button"
                                                            variant="outline"
                                                        >
                                                            Keep editing
                                                        </Button>
                                                        <Button
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            <LockKeyhole className="size-4" />
                                                            {processing
                                                                ? 'Publishing...'
                                                                : publishVisibility ===
                                                                    'public'
                                                                  ? 'Publish and lock'
                                                                  : 'Create link and lock'}
                                                        </Button>
                                                    </DialogFooter>
                                                </>
                                            )}
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            )}
                        </div>
                    </div>

                    <ArtifactBrowser artifacts={artifacts} />
                </section>

                <BuilderStepNavigation
                    next={{
                        href: builderDownload.url(),
                        label: 'Download zip',
                        download: true,
                        external: true,
                    }}
                    previous={{
                        href: builderPackages.url(),
                        label: 'Packages',
                    }}
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
