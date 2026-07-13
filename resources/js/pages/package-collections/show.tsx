import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Boxes, PackageCheck, Plus, Trash2 } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { show as showProfile } from '@/routes/profiles';
import {
    index as collectionsIndex,
} from '@/routes/package-collections';
import {
    destroy as destroyItem,
    store as storeItem,
} from '@/routes/package-collections/items';

type Collection = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    visibility: 'private' | 'public';
    can_manage: boolean;
    curator: {
        name: string | null;
        profile_slug: string | null;
    };
};

type CollectionItem = {
    id: number;
    notes: string | null;
    package: {
        id: number;
        name: string;
        composer_name: string | null;
        description: string | null;
        stars: number;
        downloads: number;
        category: string | null;
    };
};

type AvailablePackage = {
    id: number;
    name: string;
    composer_name: string | null;
};

type Props = {
    collection: Collection;
    items: CollectionItem[];
    available_packages: AvailablePackage[];
};

export default function PackageCollectionShow({
    collection,
    items,
    available_packages,
}: Props) {
    return (
        <>
            <Head title={collection.title} />
            <main className="min-h-screen bg-background">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-6 md:px-6 lg:py-10">
                    <header className="rounded-3xl border bg-card p-6 md:p-8">
                        {collection.can_manage && (
                            <Button asChild variant="ghost" className="-ml-3 mb-4">
                                <Link href={collectionsIndex()}>
                                    <ArrowLeft className="size-4" />
                                    Back to collections
                                </Link>
                            </Button>
                        )}
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge
                                variant={
                                    collection.visibility === 'public'
                                        ? 'secondary'
                                        : 'outline'
                                }
                            >
                                {collection.visibility}
                            </Badge>
                            {collection.curator.profile_slug && (
                                <Link
                                    href={showProfile.url(
                                        collection.curator.profile_slug,
                                    )}
                                    className="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                                >
                                    By {collection.curator.name}
                                </Link>
                            )}
                        </div>
                        <h1 className="mt-4 max-w-3xl text-4xl font-semibold tracking-tight md:text-5xl">
                            {collection.title}
                        </h1>
                        <p className="mt-4 max-w-2xl text-muted-foreground">
                            {collection.description ??
                                'A curated package shortlist for Laravel projects.'}
                        </p>
                    </header>

                    <section className="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
                        {collection.can_manage && (
                            <Card className="h-fit rounded-xl">
                                <CardHeader>
                                    <CardTitle>Add package</CardTitle>
                                    <CardDescription>
                                        Add a curated catalog package to this
                                        collection.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Form
                                        {...storeItem.form(collection.slug)}
                                        resetOnSuccess
                                        className="space-y-4"
                                    >
                                        {({ errors, processing }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <select
                                                        name="package_id"
                                                        className="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                    >
                                                        <option value="">
                                                            Choose package
                                                        </option>
                                                        {available_packages.map(
                                                            (pkg) => (
                                                                <option
                                                                    key={pkg.id}
                                                                    value={
                                                                        pkg.id
                                                                    }
                                                                >
                                                                    {pkg.name}{' '}
                                                                    {pkg.composer_name
                                                                        ? `(${pkg.composer_name})`
                                                                        : ''}
                                                                </option>
                                                            ),
                                                        )}
                                                    </select>
                                                    {errors.package_id && (
                                                        <p className="text-sm text-destructive">
                                                            {errors.package_id}
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="grid gap-2">
                                                    <Textarea
                                                        name="notes"
                                                        placeholder="Why this package belongs here"
                                                    />
                                                    {errors.notes && (
                                                        <p className="text-sm text-destructive">
                                                            {errors.notes}
                                                        </p>
                                                    )}
                                                </div>

                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                >
                                                    <Plus className="size-4" />
                                                    {processing
                                                        ? 'Adding...'
                                                        : 'Add package'}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        )}

                        <div className="grid gap-4">
                            {items.length > 0 ? (
                                items.map((item) => (
                                    <Card key={item.id} className="rounded-xl">
                                        <CardHeader>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Badge variant="outline">
                                                    <PackageCheck className="size-3.5" />
                                                    {item.package.category ??
                                                        'Package'}
                                                </Badge>
                                            </div>
                                            <CardTitle>
                                                {item.package.name}
                                            </CardTitle>
                                            <CardDescription>
                                                {item.package.description ??
                                                    item.package.composer_name}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="grid gap-2 text-sm text-muted-foreground sm:grid-cols-3">
                                                <Metric
                                                    label="Composer"
                                                    value={
                                                        item.package
                                                            .composer_name ??
                                                        'N/A'
                                                    }
                                                />
                                                <Metric
                                                    label="Stars"
                                                    value={String(
                                                        item.package.stars,
                                                    )}
                                                />
                                                <Metric
                                                    label="Downloads"
                                                    value={String(
                                                        item.package.downloads,
                                                    )}
                                                />
                                            </div>
                                            {item.notes && (
                                                <p className="rounded-lg bg-muted p-3 text-sm">
                                                    {item.notes}
                                                </p>
                                            )}
                                            {collection.can_manage && (
                                                <Form
                                                    {...destroyItem.form({
                                                        package_collection:
                                                            collection.slug,
                                                        item: item.id,
                                                    })}
                                                >
                                                    {({ processing }) => (
                                                        <Button
                                                            type="submit"
                                                            variant="ghost"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            <Trash2 className="size-4" />
                                                            Remove
                                                        </Button>
                                                    )}
                                                </Form>
                                            )}
                                        </CardContent>
                                    </Card>
                                ))
                            ) : (
                                <Card className="rounded-xl border-dashed">
                                    <CardHeader>
                                        <CardTitle>
                                            No packages yet
                                        </CardTitle>
                                        <CardDescription>
                                            Add packages to make this collection
                                            useful.
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                            )}
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg bg-muted p-3">
            <div className="text-xs">{label}</div>
            <div className="mt-1 font-medium text-foreground">{value}</div>
        </div>
    );
}
