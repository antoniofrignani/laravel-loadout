import { Form, Head, Link } from '@inertiajs/react';
import { Boxes, PackagePlus, Sparkles } from 'lucide-react';

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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    index as collectionsIndex,
    show as showCollection,
    store,
} from '@/routes/package-collections';

type Collection = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    visibility: 'private' | 'public';
    items_count: number;
    updated_at: string | null;
};

type Props = {
    collections: Collection[];
};

export default function PackageCollectionIndex({ collections }: Props) {
    return (
        <>
            <Head title="Package Collections" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="overflow-hidden rounded-2xl border bg-card p-6 md:p-8">
                    <Badge variant="secondary">
                        <Boxes className="size-3.5" />
                        Package curation
                    </Badge>
                    <h1 className="mt-4 max-w-2xl text-3xl font-semibold tracking-tight md:text-4xl">
                        Curate reusable package shortlists.
                    </h1>
                    <p className="mt-3 max-w-2xl text-muted-foreground">
                        Group high-value Laravel packages by use case and share
                        public collections from your curator profile.
                    </p>
                </section>

                <section className="grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
                    <Card className="h-fit rounded-xl">
                        <CardHeader>
                            <CardTitle>Create collection</CardTitle>
                            <CardDescription>
                                Start with a title and visibility. Add packages
                                from the collection detail page.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                resetOnSuccess
                                className="space-y-4"
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="title">
                                                Title
                                            </Label>
                                            <Input
                                                id="title"
                                                name="title"
                                                placeholder="SaaS essentials"
                                            />
                                            {errors.title && (
                                                <p className="text-sm text-destructive">
                                                    {errors.title}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="description">
                                                Description
                                            </Label>
                                            <Textarea
                                                id="description"
                                                name="description"
                                                placeholder="Packages I reach for when building subscription apps."
                                            />
                                            {errors.description && (
                                                <p className="text-sm text-destructive">
                                                    {errors.description}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="visibility">
                                                Visibility
                                            </Label>
                                            <select
                                                id="visibility"
                                                name="visibility"
                                                defaultValue="private"
                                                className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            >
                                                <option value="private">
                                                    Private
                                                </option>
                                                <option value="public">
                                                    Public
                                                </option>
                                            </select>
                                            {errors.visibility && (
                                                <p className="text-sm text-destructive">
                                                    {errors.visibility}
                                                </p>
                                            )}
                                        </div>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            <PackagePlus className="size-4" />
                                            {processing
                                                ? 'Creating...'
                                                : 'Create collection'}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    {collections.length > 0 ? (
                        <div className="grid gap-4 xl:grid-cols-2">
                            {collections.map((collection) => (
                                <Card
                                    key={collection.id}
                                    className="rounded-xl"
                                >
                                    <CardHeader>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Badge
                                                variant={
                                                    collection.visibility ===
                                                    'public'
                                                        ? 'secondary'
                                                        : 'outline'
                                                }
                                            >
                                                {collection.visibility}
                                            </Badge>
                                            {collection.updated_at && (
                                                <span className="text-xs text-muted-foreground">
                                                    Updated{' '}
                                                    {collection.updated_at}
                                                </span>
                                            )}
                                        </div>
                                        <CardTitle>
                                            {collection.title}
                                        </CardTitle>
                                        <CardDescription>
                                            {collection.description ??
                                                'No description yet.'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="flex flex-wrap items-center justify-between gap-3">
                                        <span className="text-sm text-muted-foreground">
                                            {collection.items_count} packages
                                        </span>
                                        <Button asChild>
                                            <Link
                                                href={showCollection.url(
                                                    collection.slug,
                                                )}
                                            >
                                                Manage
                                            </Link>
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <Card className="rounded-xl border-dashed">
                            <CardHeader>
                                <CardTitle>No collections yet</CardTitle>
                                <CardDescription>
                                    Create your first package shortlist to start
                                    curating.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Sparkles className="size-8 text-muted-foreground" />
                            </CardContent>
                        </Card>
                    )}
                </section>
            </div>
        </>
    );
}

PackageCollectionIndex.layout = {
    breadcrumbs: [
        {
            title: 'Package Collections',
            href: collectionsIndex(),
        },
    ],
};
