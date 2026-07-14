import { Form, Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    Download,
    PackageCheck,
    PackagePlus,
    Plus,
    RefreshCw,
    Search,
    Star,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import { store as storeCustomPackage } from '@/actions/App/Http/Controllers/Builder/CustomPackageController';
import {
    store as storeLoadoutPackage,
    update as updateLoadoutPackage,
} from '@/actions/App/Http/Controllers/Builder/LoadoutPackageController';
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
import { Textarea } from '@/components/ui/textarea';
import {
    packages as builderPackages,
    review as builderReview,
    runtime as builderRuntime,
} from '@/routes/builder';

type Category = {
    name: string;
    slug: string;
    packages_count?: number;
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
    per_page?: number | null;
};

type CustomLoadoutPackage = {
    id: number;
    display_name: string;
    composer_name: string | null;
    source_url: string | null;
};

type SelectedLoadoutPackage = {
    id: number;
    display_name: string;
    composer_name: string | null;
    install_options: {
        dev_dependency?: boolean;
    } | null;
    notes: string | null;
};

type DraftLoadout = {
    id: number;
    title: string;
    selected_package_ids: number[];
    selected_packages: SelectedLoadoutPackage[];
    custom_packages: CustomLoadoutPackage[];
};

type Recommendation = {
    composer_name: string;
    title: string;
    reason: string;
    package_id: number | null;
    is_selected: boolean;
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

const perPageOptions = [24, 48, 72, 96] as const;

function normalizePageLabel(label: string) {
    return label.replace('&laquo;', 'Previous').replace('&raquo;', 'Next');
}

function selectedValue(value: string | null | undefined, fallback = 'all') {
    return value && value !== '' ? value : fallback;
}

function selectedNumberValue(value: number | null | undefined, fallback = 24) {
    return value ? String(value) : String(fallback);
}

function filterValue<Key extends keyof Filters>(
    filters: Filters | undefined,
    key: Key,
): Filters[Key] | null {
    if (!filters || !Object.hasOwn(filters, key)) {
        return null;
    }

    return filters[key];
}

function compactNumber(value: number) {
    return new Intl.NumberFormat('en', {
        maximumFractionDigits: 1,
        notation: 'compact',
    }).format(value);
}

export default function BuilderPackages({
    packages,
    categories,
    loadout,
    recommendations,
    filters,
}: Props) {
    'use no memo';

    const catalogPackages = packages?.data ?? [];
    const paginationLinks = packages?.links ?? [];
    const packageCategories = categories ?? [];
    const selectedPackageIds = loadout?.selected_package_ids ?? [];
    const selectedPackages = loadout?.selected_packages ?? [];
    const customPackages = loadout?.custom_packages ?? [];
    const packageRecommendations = recommendations ?? [];
    const [search, setSearch] = useState(filterValue(filters, 'search') ?? '');
    const [category, setCategory] = useState(
        selectedValue(filterValue(filters, 'category')),
    );
    const [type, setType] = useState(
        selectedValue(filterValue(filters, 'type')),
    );
    const [service, setService] = useState(
        filterValue(filters, 'service') ?? '',
    );
    const [sort, setSort] = useState(
        selectedValue(filterValue(filters, 'sort'), 'relevance'),
    );
    const [perPage, setPerPage] = useState(
        selectedNumberValue(filters?.per_page),
    );
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
                per_page: perPage === '24' ? undefined : Number(perPage),
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
                        <div className="flex flex-wrap items-center gap-2">
                            <div className="flex items-center gap-2 rounded-md border bg-card px-3 py-2 text-sm">
                                <PackagePlus className="size-4 text-muted-foreground" />
                                <span>
                                    {packages?.total ?? 0} matching packages
                                </span>
                            </div>
                            <Button asChild variant="outline">
                                <Link href={builderRuntime.url()}>
                                    <ArrowLeft className="size-4" />
                                    Back to runtime
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={builderReview.url()}>
                                    Review
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </header>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <Card className="rounded-lg">
                        <CardHeader className="pb-3">
                            <CardTitle className="text-base">
                                Find packages
                            </CardTitle>
                            <CardDescription>
                                Search, filter, and sort by hourly stats
                                snapshots.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form
                                className="grid gap-3 md:grid-cols-2 2xl:grid-cols-7"
                                onSubmit={applyFilters}
                            >
                                <label className="flex flex-col gap-1.5 md:col-span-2 2xl:col-span-2">
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
                                            {packageCategories.map(
                                                (category) => (
                                                    <SelectItem
                                                        key={category.slug}
                                                        value={category.slug}
                                                    >
                                                        {category.name} (
                                                        {category.packages_count ??
                                                            0}
                                                        )
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                </label>

                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Type
                                    </span>
                                    <Select
                                        value={type}
                                        onValueChange={setType}
                                    >
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
                                    <Select
                                        value={sort}
                                        onValueChange={setSort}
                                    >
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

                                <label className="flex flex-col gap-1.5">
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Page size
                                    </span>
                                    <Select
                                        value={perPage}
                                        onValueChange={setPerPage}
                                    >
                                        <SelectTrigger className="w-full">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {perPageOptions.map((value) => (
                                                <SelectItem
                                                    key={value}
                                                    value={String(value)}
                                                >
                                                    {value} / page
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </label>

                                <label className="flex flex-col gap-1.5 md:col-span-2 2xl:col-span-1">
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

                                <div className="flex items-end gap-2 md:col-span-2 2xl:col-span-1">
                                    <Button type="submit">
                                        <Search className="size-4" />
                                        Apply
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => {
                                            setSearch('');
                                            setCategory('all');
                                            setType('all');
                                            setService('');
                                            setSort('relevance');
                                            setPerPage('24');
                                            router.get(builderPackages.url());
                                        }}
                                    >
                                        Reset
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <Card className="rounded-lg">
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center justify-between gap-3 text-base">
                                Selected loadout
                                <Badge variant="secondary">
                                    {selectedPackages.length +
                                        customPackages.length}
                                </Badge>
                            </CardTitle>
                            <CardDescription>
                                Compact controls for long dependency lists.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="max-h-[360px] space-y-2 overflow-y-auto pr-1">
                                {selectedPackages.length > 0 ? (
                                    selectedPackages.map((selectedPackage) => (
                                        <div
                                            key={selectedPackage.id}
                                            className="rounded-md border bg-background p-3"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <div className="truncate text-sm font-medium">
                                                        {
                                                            selectedPackage.display_name
                                                        }
                                                    </div>
                                                    <div className="truncate font-mono text-xs text-muted-foreground">
                                                        {
                                                            selectedPackage.composer_name
                                                        }
                                                    </div>
                                                </div>
                                                <PackageCheck className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                            </div>
                                            <Form
                                                className="mt-3 grid gap-2"
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                                {...updateLoadoutPackage.form(
                                                    selectedPackage.id,
                                                )}
                                            >
                                                {({ errors, processing }) => (
                                                    <>
                                                        <Select
                                                            defaultValue={
                                                                selectedPackage
                                                                    .install_options
                                                                    ?.dev_dependency
                                                                    ? '1'
                                                                    : '0'
                                                            }
                                                            name="install_as_dev"
                                                        >
                                                            <SelectTrigger className="h-8 w-full text-xs">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="0">
                                                                    Production
                                                                </SelectItem>
                                                                <SelectItem value="1">
                                                                    Dev
                                                                    dependency
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                        {errors.install_as_dev && (
                                                            <p className="text-xs text-destructive">
                                                                {
                                                                    errors.install_as_dev
                                                                }
                                                            </p>
                                                        )}
                                                        <Textarea
                                                            className="min-h-16 text-xs"
                                                            name="notes"
                                                            defaultValue={
                                                                selectedPackage.notes ??
                                                                ''
                                                            }
                                                            placeholder="Package setup notes"
                                                        />
                                                        {errors.notes && (
                                                            <p className="text-xs text-destructive">
                                                                {errors.notes}
                                                            </p>
                                                        )}
                                                        <Button
                                                            className="h-8"
                                                            disabled={
                                                                processing
                                                            }
                                                            size="sm"
                                                            variant="outline"
                                                        >
                                                            Save notes
                                                        </Button>
                                                    </>
                                                )}
                                            </Form>
                                        </div>
                                    ))
                                ) : (
                                    <div className="rounded-md border border-dashed p-4 text-sm text-muted-foreground">
                                        Select curated packages from the
                                        catalog.
                                    </div>
                                )}
                            </div>

                            {customPackages.length > 0 && (
                                <div className="space-y-2 border-t pt-4">
                                    <div className="text-xs font-medium text-muted-foreground">
                                        Custom packages
                                    </div>
                                    <div className="max-h-32 space-y-2 overflow-y-auto pr-1">
                                        {customPackages.map((pkg) => (
                                            <div
                                                key={pkg.id}
                                                className="rounded-md bg-muted px-3 py-2 text-sm"
                                            >
                                                <div className="truncate font-medium">
                                                    {pkg.display_name}
                                                </div>
                                                <div className="truncate font-mono text-xs text-muted-foreground">
                                                    {pkg.composer_name}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            <div className="border-t pt-4">
                                <div className="mb-2 text-xs font-medium text-muted-foreground">
                                    Add custom package
                                </div>
                                <Form
                                    className="flex flex-col gap-3"
                                    onSuccess={() => setCustomPackageUrl('')}
                                    options={{ preserveScroll: true }}
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
                                                <p className="text-xs text-destructive">
                                                    {errors.package_url}
                                                </p>
                                            )}
                                            <Button
                                                disabled={
                                                    processing ||
                                                    !customPackageUrl
                                                }
                                            >
                                                <Plus className="size-4" />
                                                Add to loadout
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {packageRecommendations.length > 0 && (
                    <section className="grid gap-3 md:grid-cols-3">
                        {packageRecommendations.map((recommendation) => (
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
                                <CardContent className="flex flex-col gap-3 text-sm text-muted-foreground">
                                    <p>{recommendation.reason}</p>
                                    <Form
                                        options={{ preserveScroll: true }}
                                        {...storeLoadoutPackage.form()}
                                    >
                                        {({ processing }) => (
                                            <>
                                                {recommendation.package_id ? (
                                                    <input
                                                        name="package_id"
                                                        type="hidden"
                                                        value={
                                                            recommendation.package_id
                                                        }
                                                    />
                                                ) : (
                                                    <input
                                                        name="composer_name"
                                                        type="hidden"
                                                        value={
                                                            recommendation.composer_name
                                                        }
                                                    />
                                                )}
                                                <Button
                                                    className="w-full"
                                                    disabled={
                                                        processing ||
                                                        recommendation.is_selected
                                                    }
                                                    size="sm"
                                                    variant={
                                                        recommendation.is_selected
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    <Plus className="size-4" />
                                                    {recommendation.is_selected
                                                        ? 'Selected'
                                                        : 'Select package'}
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </CardContent>
                            </Card>
                        ))}
                    </section>
                )}

                <section className="grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    {catalogPackages.map((catalogPackage) => (
                        <Card
                            key={catalogPackage.id}
                            className="flex flex-col rounded-lg py-4"
                        >
                            <CardHeader className="gap-2 px-4 pb-3">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <CardTitle className="line-clamp-1 text-base leading-tight">
                                            {catalogPackage.name}
                                        </CardTitle>
                                        <CardDescription className="mt-1 truncate font-mono text-xs">
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
                            <CardContent className="mt-auto flex flex-col gap-3 px-4">
                                <div className="flex flex-wrap gap-2">
                                    {catalogPackage.category && (
                                        <Badge variant="outline">
                                            {catalogPackage.category.name}
                                        </Badge>
                                    )}
                                    {catalogPackage.is_official && (
                                        <Badge>Official</Badge>
                                    )}
                                    {(
                                        catalogPackage.required_services ?? []
                                    ).map((service) => (
                                        <Badge key={service} variant="outline">
                                            {service}
                                        </Badge>
                                    ))}
                                </div>
                                <div className="grid grid-cols-3 gap-2 text-xs text-muted-foreground">
                                    <div className="flex items-center gap-1.5 rounded-md bg-muted px-2 py-1.5">
                                        <Star className="size-3.5" />
                                        {compactNumber(catalogPackage.stars)}
                                    </div>
                                    <div className="flex items-center gap-1.5 rounded-md bg-muted px-2 py-1.5">
                                        <Download className="size-3.5" />
                                        {compactNumber(
                                            catalogPackage.downloads,
                                        )}
                                    </div>
                                    <div className="flex items-center gap-1.5 rounded-md bg-muted px-2 py-1.5">
                                        <RefreshCw className="size-3.5" />
                                        <span className="truncate">
                                            {catalogPackage.stats_refreshed_at ??
                                                'Pending'}
                                        </span>
                                    </div>
                                </div>
                                {(catalogPackage.compatibility_warnings ?? [])
                                    .length > 0 && (
                                    <div className="rounded-md border border-amber-300 bg-amber-50 p-2 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                                        {(
                                            catalogPackage.compatibility_warnings ??
                                            []
                                        ).map((warning) => (
                                            <p key={warning}>{warning}</p>
                                        ))}
                                    </div>
                                )}
                                <Form
                                    options={{ preserveScroll: true }}
                                    {...storeLoadoutPackage.form()}
                                >
                                    {({ processing }) => {
                                        const selected =
                                            selectedPackageIds.includes(
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

                {catalogPackages.length === 0 && (
                    <div className="rounded-lg border border-dashed p-10 text-center text-sm text-muted-foreground">
                        No packages match the current filters.
                    </div>
                )}

                <footer className="flex flex-col justify-between gap-3 border-t pt-4 text-sm text-muted-foreground md:flex-row md:items-center">
                    <span>
                        Showing {packages?.from ?? 0}-{packages?.to ?? 0} of{' '}
                        {packages?.total ?? 0}
                    </span>
                    <div className="flex flex-wrap gap-2">
                        {paginationLinks.map((link) => (
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
                                    <span>
                                        {normalizePageLabel(link.label)}
                                    </span>
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
            title: 'Runtime',
            href: builderRuntime.url(),
        },
        {
            title: 'Packages',
            href: builderPackages.url(),
        },
    ],
};
