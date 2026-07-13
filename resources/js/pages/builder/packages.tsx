import { FormEvent, useState } from 'react';
import { Form, Head, Link, router } from '@inertiajs/react';
import {
    Download,
    PackagePlus,
    Plus,
    RefreshCw,
    Search,
    Star,
} from 'lucide-react';

import { store as storeCustomPackage } from '@/actions/App/Http/Controllers/Builder/CustomPackageController';
import { store as storeLoadoutPackage } from '@/actions/App/Http/Controllers/Builder/LoadoutPackageController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { packages as builderPackages } from '@/routes/builder';

type Category = {
    name: string;
    slug: string;
};

type CatalogPackage = {
    id: number;
    name: string;
    composer_name: string | null;
    npm_name: string | null;
    description: string | null;
    category: Category | null;
    tags: string[];
    required_services: string[];
    compatibility_warnings: string[];
    is_official: boolean;
    is_featured: boolean;
    stars: number;
    downloads: number;
    usage_count: number;
    last_source_update_at: string | null;
    stats_refreshed_at: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedPackages = {
    data: CatalogPackage[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
};

type Filters = {
    search?: string | null;
    category?: string | null;
    type?: string | null;
    service?: string | null;
    laravel_version?: string | null;
    sort?: string | null;
};

type CustomLoadoutPackage = {
    id: number;
    display_name: string;
    composer_name: string | null;
    source_url: string | null;
};

type DraftLoadout = {
    id: number;
    title: string;
    selected_package_ids: number[];
    custom_packages: CustomLoadoutPackage[];
};

type Recommendation = {
    composer_name: string;
    title: string;
    reason: string;
};

type Props = {
    packages: PaginatedPackages;
    categories: Category[];
    loadout: DraftLoadout;
    recommendations: Recommendation[];
    filters: Filters;
};

const sortOptions = [
    ['relevance', 'Relevance'],
    ['stars', 'Stars'],
    ['downloads', 'Downloads'],
    ['last_update', 'Last update'],
    ['name', 'Name'],
    ['recently_added', 'Recently added'],
] as const;

function normalizePageLabel(label: string) {
    return label.replace('&laquo;', 'Previous').replace('&raquo;', 'Next');
}

function selectedValue(value: string | null | undefined, fallback = 'all') {
    return value && value !== '' ? value : fallback;
}

export default function BuilderPackages({
    packages,
    categories,
    loadout,
    recommendations,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [category, setCategory] = useState(selectedValue(filters.category));
    const [type, setType] = useState(selectedValue(filters.type));
    const [service, setService] = useState(filters.service ?? '');
    const [sort, setSort] = useState(selectedValue(filters.sort, 'relevance'));
    const [customPackageUrl, setCustomPackageUrl] = useState('');

    function applyFilters(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        router.get(
            builderPackages.url(),
            {
                search: search || undefined,
                category: category === 'all' ? undefined : category,
                type: type === 'all' ? undefined : type,
                service: service || undefined,
                sort: sort === 'relevance' ? undefined : sort,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    }

    return (
        <>
            <Head title="Packages" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 5</span>
                        <span>/</span>
                        <span>Packages</span>
                    </div>
                    <div className="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                        <div className="max-w-3xl">
                            <h1 className="text-3xl font-semibold tracking-normal">
                                Build your package loadout
                            </h1>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Curated Laravel packages, install metadata, and
                                loadout-only custom additions.
                            </p>
                        </div>
                        <div className="flex items-center gap-2 rounded-md border bg-card px-3 py-2 text-sm">
                            <PackagePlus className="size-4 text-muted-foreground" />
                            <span>{packages.total} packages indexed</span>
                        </div>
                    </div>
                </header>

                <section className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Catalog filters</CardTitle>
                            <CardDescription>
                                Search and sort by stored package snapshots.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form
                                className="grid gap-3 md:grid-cols-2 xl:grid-cols-5"
                                onSubmit={applyFilters}
                            >
                                <label className="flex flex-col gap-1.5 xl:col-span-2">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Search
                                    </span>
                                    <div className="relative">
                                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                        <Input
                                            className="pl-9"
                                            value={search}
                                            onChange={(event) =>
                                                setSearch(event.target.value)
                                            }
                                            placeholder="Package name or vendor/package"
                                        />
                                    </div>
                                </label>

                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Category
                                    </span>
                                    <Select
                                        value={category}
                                        onValueChange={setCategory}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                All categories
                                            </SelectItem>
                                            {categories.map((category) => (
                                                <SelectItem
                                                    key={category.slug}
                                                    value={category.slug}
                                                >
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </label>

                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Type
                                    </span>
                                    <Select value={type} onValueChange={setType}>
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                All packages
                                            </SelectItem>
                                            <SelectItem value="official">
                                                Official
                                            </SelectItem>
                                            <SelectItem value="community">
                                                Community
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </label>

                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Sort
                                    </span>
                                    <Select value={sort} onValueChange={setSort}>
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {sortOptions.map(
                                                ([value, label]) => (
                                                    <SelectItem
                                                        key={value}
                                                        value={value}
                                                    >
                                                        {label}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </label>

                                <label className="flex flex-col gap-1.5 md:col-span-2 xl:col-span-2">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Required service
                                    </span>
                                    <Input
                                        value={service}
                                        onChange={(event) =>
                                            setService(event.target.value)
                                        }
                                        placeholder="redis, meilisearch, mailpit"
                                    />
                                </label>

                                <div className="flex items-end gap-2 md:col-span-2 xl:col-span-3">
                                    <Button type="submit">
                                        <Search className="size-4" />
                                        Apply filters
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() =>
                                            router.get(builderPackages.url())
                                        }
                                    >
                                        Reset
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Custom package</CardTitle>
                            <CardDescription>
                                Attach an unlisted package to this loadout.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                className="flex flex-col gap-3"
                                onSuccess={() => setCustomPackageUrl('')}
                                {...storeCustomPackage.form()}
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <Input
                                            name="package_url"
                                            value={customPackageUrl}
                                            onChange={(event) =>
                                                setCustomPackageUrl(
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="https://packagist.org/packages/vendor/name"
                                        />
                                        {errors.package_url && (
                                            <p className="text-sm text-destructive">
                                                {errors.package_url}
                                            </p>
                                        )}
                                        <Button
                                            disabled={
                                                processing || !customPackageUrl
                                            }
                                        >
                                            <Plus className="size-4" />
                                            Add to loadout
                                        </Button>
                                    </>
                                )}
                            </Form>
                            {loadout.custom_packages.length > 0 && (
                                <div className="mt-4 flex flex-col gap-2 border-t pt-4">
                                    {loadout.custom_packages.map((pkg) => (
                                        <div
                                            key={pkg.id}
                                            className="rounded-md bg-muted px-3 py-2 text-sm"
                                        >
                                            <div className="font-medium">
                                                {pkg.display_name}
                                            </div>
                                            <div className="font-mono text-xs text-muted-foreground">
                                                {pkg.composer_name}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </section>

                {recommendations.length > 0 && (
                    <section className="grid gap-3 md:grid-cols-3">
                        {recommendations.map((recommendation) => (
                            <Card
                                key={recommendation.composer_name}
                                className="rounded-lg border-dashed"
                            >
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base">
                                        {recommendation.title}
                                    </CardTitle>
                                    <CardDescription className="font-mono text-xs">
                                        {recommendation.composer_name}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="text-sm text-muted-foreground">
                                    {recommendation.reason}
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                )}

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {packages.data.map((catalogPackage) => (
                        <Card
                            key={catalogPackage.id}
                            className="rounded-lg py-5"
                        >
                            <CardHeader className="gap-3">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <CardTitle className="text-lg leading-tight">
                                            {catalogPackage.name}
                                        </CardTitle>
                                        <CardDescription className="mt-1 font-mono text-xs">
                                            {catalogPackage.composer_name ??
                                                catalogPackage.npm_name}
                                        </CardDescription>
                                    </div>
                                    {catalogPackage.is_featured && (
                                        <Badge variant="secondary">
                                            Featured
                                        </Badge>
                                    )}
                                </div>
                                <p className="line-clamp-2 text-sm text-muted-foreground">
                                    {catalogPackage.description}
                                </p>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <div className="flex flex-wrap gap-2">
                                    {catalogPackage.category && (
                                        <Badge variant="outline">
                                            {catalogPackage.category.name}
                                        </Badge>
                                    )}
                                    {catalogPackage.is_official && (
                                        <Badge>Official</Badge>
                                    )}
                                    {catalogPackage.required_services.map(
                                        (service) => (
                                            <Badge
                                                key={service}
                                                variant="outline"
                                            >
                                                {service}
                                            </Badge>
                                        ),
                                    )}
                                </div>
                                <div className="grid grid-cols-3 gap-2 text-sm">
                                    <div className="flex items-center gap-1.5">
                                        <Star className="size-4 text-muted-foreground" />
                                        {catalogPackage.stars.toLocaleString()}
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <Download className="size-4 text-muted-foreground" />
                                        {catalogPackage.downloads.toLocaleString()}
                                    </div>
                                    <div className="flex items-center gap-1.5">
                                        <RefreshCw className="size-4 text-muted-foreground" />
                                        {catalogPackage.stats_refreshed_at ??
                                            'Pending'}
                                    </div>
                                </div>
                                {catalogPackage.compatibility_warnings.length >
                                    0 && (
                                    <div className="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                                        {catalogPackage.compatibility_warnings.map(
                                            (warning) => (
                                                <p key={warning}>{warning}</p>
                                            ),
                                        )}
                                    </div>
                                )}
                                <Form {...storeLoadoutPackage.form()}>
                                    {({ processing }) => {
                                        const selected =
                                            loadout.selected_package_ids.includes(
                                                catalogPackage.id,
                                            );

                                        return (
                                            <>
                                                <input
                                                    name="package_id"
                                                    type="hidden"
                                                    value={catalogPackage.id}
                                                />
                                                <Button
                                                    className="w-full"
                                                    disabled={
                                                        processing || selected
                                                    }
                                                    variant={
                                                        selected
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    <Plus className="size-4" />
                                                    {selected
                                                        ? 'Selected'
                                                        : 'Select package'}
                                                </Button>
                                            </>
                                        );
                                    }}
                                </Form>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                {packages.data.length === 0 && (
                    <div className="rounded-lg border border-dashed p-10 text-center text-sm text-muted-foreground">
                        No packages match the current filters.
                    </div>
                )}

                <footer className="flex flex-col justify-between gap-3 border-t pt-4 text-sm text-muted-foreground md:flex-row md:items-center">
                    <span>
                        Showing {packages.from ?? 0}-{packages.to ?? 0} of{' '}
                        {packages.total}
                    </span>
                    <div className="flex flex-wrap gap-2">
                        {packages.links.map((link) => (
                            <Button
                                key={`${link.label}-${link.url}`}
                                asChild={Boolean(link.url)}
                                disabled={!link.url}
                                size="sm"
                                variant={link.active ? 'default' : 'outline'}
                            >
                                {link.url ? (
                                    <Link href={link.url} preserveScroll>
                                        {normalizePageLabel(link.label)}
                                    </Link>
                                ) : (
                                    <span>{normalizePageLabel(link.label)}</span>
                                )}
                            </Button>
                        ))}
                    </div>
                </footer>
            </div>
        </>
    );
}

BuilderPackages.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderPackages.url(),
        },
        {
            title: 'Packages',
            href: builderPackages.url(),
        },
    ],
};
