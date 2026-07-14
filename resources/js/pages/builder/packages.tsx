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
    const selectedCount = selectedPackages.length + customPackages.length;
    const devPackageCount = selectedPackages.filter(
        (pkg) => pkg.install_options?.dev_dependency,
    ).length;
    const productionPackageCount = selectedPackages.length - devPackageCount;
    const visibleSelectedPackages = selectedPackages.slice(0, 24);
    const hiddenSelectedPackageCount =
        selectedPackages.length - visibleSelectedPackages.length;
    const categoryHighlights = packageCategories
        .filter((category) => (category.packages_count ?? 0) > 0)
        .slice(0, 8);
    const productionWidth =
        selectedCount > 0 ? (productionPackageCount / selectedCount) * 100 : 0;
    const devWidth =
        selectedCount > 0 ? (devPackageCount / selectedCount) * 100 : 0;
    const customWidth =
        selectedCount > 0 ? (customPackages.length / selectedCount) * 100 : 0;
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
            <div className="flex h-full flex-1 flex-col gap-5 overflow-x-auto p-4 md:p-6">
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

                <section className="grid items-start gap-4 2xl:grid-cols-[minmax(0,1fr)_430px]">
                    <div className="min-w-0 space-y-4">
                        <form
                            className="sticky top-0 z-20 rounded-xl border bg-background/95 p-3 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-background/80"
                            onSubmit={applyFilters}
                        >
                            <div className="grid gap-2 lg:grid-cols-[minmax(260px,1.5fr)_repeat(4,minmax(140px,1fr))_auto]">
                                <label className="relative">
                                    <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        className="h-9 pl-9"
                                        value={search}
                                        onChange={(event) =>
                                            setSearch(event.target.value)
                                        }
                                        placeholder="Search package or vendor/name"
                                    />
                                </label>

                                <Select
                                    value={category}
                                    onValueChange={setCategory}
                                >
                                    <SelectTrigger className="h-9 w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All categories
                                        </SelectItem>
                                        {packageCategories.map((category) => (
                                            <SelectItem
                                                key={category.slug}
                                                value={category.slug}
                                            >
                                                {category.name} (
                                                {category.packages_count ?? 0})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Select value={type} onValueChange={setType}>
                                    <SelectTrigger className="h-9 w-full">
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

                                <Select value={sort} onValueChange={setSort}>
                                    <SelectTrigger className="h-9 w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {sortOptions.map(([value, label]) => (
                                            <SelectItem
                                                key={value}
                                                value={value}
                                            >
                                                {label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Input
                                    className="h-9"
                                    value={service}
                                    onChange={(event) =>
                                        setService(event.target.value)
                                    }
                                    placeholder="Service"
                                />

                                <div className="flex gap-2">
                                    <Select
                                        value={perPage}
                                        onValueChange={setPerPage}
                                    >
                                        <SelectTrigger className="h-9 w-24">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {perPageOptions.map((value) => (
                                                <SelectItem
                                                    key={value}
                                                    value={String(value)}
                                                >
                                                    {value}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Button className="h-9" type="submit">
                                        Apply
                                    </Button>
                                </div>
                            </div>

                            <div className="mt-3 flex flex-col justify-between gap-3 text-xs text-muted-foreground md:flex-row md:items-center">
                                <div className="flex flex-wrap gap-1.5">
                                    {categoryHighlights.map((category) => (
                                        <button
                                            key={category.slug}
                                            className="rounded-full border px-2.5 py-1 transition-colors hover:bg-muted"
                                            onClick={() => {
                                                setCategory(category.slug);
                                                router.get(
                                                    builderPackages.url(),
                                                    {
                                                        search:
                                                            search || undefined,
                                                        category: category.slug,
                                                        type:
                                                            type === 'all'
                                                                ? undefined
                                                                : type,
                                                        service:
                                                            service ||
                                                            undefined,
                                                        sort:
                                                            sort === 'relevance'
                                                                ? undefined
                                                                : sort,
                                                        per_page:
                                                            perPage === '24'
                                                                ? undefined
                                                                : Number(
                                                                      perPage,
                                                                  ),
                                                    },
                                                    {
                                                        preserveState: true,
                                                        replace: true,
                                                    },
                                                );
                                            }}
                                            type="button"
                                        >
                                            {category.name}{' '}
                                            <span className="text-foreground">
                                                {category.packages_count}
                                            </span>
                                        </button>
                                    ))}
                                </div>
                                <button
                                    className="self-start text-xs font-medium text-foreground underline-offset-4 hover:underline md:self-auto"
                                    onClick={() => {
                                        setSearch('');
                                        setCategory('all');
                                        setType('all');
                                        setService('');
                                        setSort('relevance');
                                        setPerPage('24');
                                        router.get(builderPackages.url());
                                    }}
                                    type="button"
                                >
                                    Clear filters
                                </button>
                            </div>
                        </form>

                        <section className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
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
                                                    {
                                                        catalogPackage.category
                                                            .name
                                                    }
                                                </Badge>
                                            )}
                                            {catalogPackage.is_official && (
                                                <Badge>Official</Badge>
                                            )}
                                            {(
                                                catalogPackage.required_services ??
                                                []
                                            ).map((service) => (
                                                <Badge
                                                    key={service}
                                                    variant="outline"
                                                >
                                                    {service}
                                                </Badge>
                                            ))}
                                        </div>
                                        <div className="grid grid-cols-3 gap-2 text-xs text-muted-foreground">
                                            <div className="flex items-center gap-1.5 rounded-md bg-muted px-2 py-1.5">
                                                <Star className="size-3.5" />
                                                {compactNumber(
                                                    catalogPackage.stars,
                                                )}
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
                                        {(
                                            catalogPackage.compatibility_warnings ??
                                            []
                                        ).length > 0 && (
                                            <div className="rounded-md border border-amber-300 bg-amber-50 p-2 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                                                {(
                                                    catalogPackage.compatibility_warnings ??
                                                    []
                                                ).map((warning) => (
                                                    <p key={warning}>
                                                        {warning}
                                                    </p>
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
                                                            value={
                                                                catalogPackage.id
                                                            }
                                                        />
                                                        <Button
                                                            className="w-full"
                                                            disabled={
                                                                processing ||
                                                                selected
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
                                Showing {packages?.from ?? 0}-
                                {packages?.to ?? 0} of {packages?.total ?? 0}
                            </span>
                            <div className="flex flex-wrap gap-2">
                                {paginationLinks.map((link) => (
                                    <Button
                                        key={`${link.label}-${link.url}`}
                                        asChild={Boolean(link.url)}
                                        disabled={!link.url}
                                        size="sm"
                                        variant={
                                            link.active ? 'default' : 'outline'
                                        }
                                    >
                                        {link.url ? (
                                            <Link
                                                href={link.url}
                                                preserveScroll
                                            >
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

                    <aside className="space-y-4 2xl:sticky 2xl:top-4">
                        <Card className="overflow-hidden rounded-xl">
                            <CardHeader className="border-b bg-muted/40 pb-4">
                                <CardTitle className="flex items-center justify-between gap-3 text-base">
                                    Loadout map
                                    <Badge variant="secondary">
                                        {selectedCount} selected
                                    </Badge>
                                </CardTitle>
                                <CardDescription>
                                    A live overview of the dependency stack you
                                    are composing.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4 p-4">
                                <div className="grid grid-cols-4 gap-2">
                                    <div className="rounded-lg border bg-background p-2">
                                        <div className="text-lg font-semibold">
                                            {selectedCount}
                                        </div>
                                        <div className="text-[11px] text-muted-foreground">
                                            Total
                                        </div>
                                    </div>
                                    <div className="rounded-lg border bg-background p-2">
                                        <div className="text-lg font-semibold">
                                            {productionPackageCount}
                                        </div>
                                        <div className="text-[11px] text-muted-foreground">
                                            Prod
                                        </div>
                                    </div>
                                    <div className="rounded-lg border bg-background p-2">
                                        <div className="text-lg font-semibold">
                                            {devPackageCount}
                                        </div>
                                        <div className="text-[11px] text-muted-foreground">
                                            Dev
                                        </div>
                                    </div>
                                    <div className="rounded-lg border bg-background p-2">
                                        <div className="text-lg font-semibold">
                                            {customPackages.length}
                                        </div>
                                        <div className="text-[11px] text-muted-foreground">
                                            Custom
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex justify-between text-xs text-muted-foreground">
                                        <span>Stack composition</span>
                                        <span>
                                            {productionPackageCount} prod /{' '}
                                            {devPackageCount} dev /{' '}
                                            {customPackages.length} custom
                                        </span>
                                    </div>
                                    <div className="flex h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="bg-emerald-500"
                                            style={{
                                                width: `${productionWidth}%`,
                                            }}
                                        />
                                        <div
                                            className="bg-sky-500"
                                            style={{ width: `${devWidth}%` }}
                                        />
                                        <div
                                            className="bg-amber-500"
                                            style={{ width: `${customWidth}%` }}
                                        />
                                    </div>
                                </div>

                                <Form
                                    className="grid gap-2 rounded-lg border bg-background p-3"
                                    onSuccess={() => setCustomPackageUrl('')}
                                    options={{ preserveScroll: true }}
                                    {...storeCustomPackage.form()}
                                >
                                    {({ errors, processing }) => (
                                        <>
                                            <div>
                                                <div className="text-sm font-medium">
                                                    Drop in a custom package
                                                </div>
                                                <p className="text-xs text-muted-foreground">
                                                    Used only in this loadout
                                                    until curated globally.
                                                </p>
                                            </div>
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
                                                className="w-full"
                                                disabled={
                                                    processing ||
                                                    !customPackageUrl
                                                }
                                                size="sm"
                                            >
                                                <Plus className="size-4" />
                                                Add custom package
                                            </Button>
                                        </>
                                    )}
                                </Form>

                                {packageRecommendations.length > 0 && (
                                    <div className="space-y-2">
                                        <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                            Suggested by runtime
                                        </div>
                                        <div className="space-y-2">
                                            {packageRecommendations.map(
                                                (recommendation) => (
                                                    <div
                                                        key={
                                                            recommendation.composer_name
                                                        }
                                                        className="rounded-lg border border-dashed p-3"
                                                    >
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div className="min-w-0">
                                                                <div className="truncate text-sm font-medium">
                                                                    {
                                                                        recommendation.title
                                                                    }
                                                                </div>
                                                                <div className="truncate font-mono text-xs text-muted-foreground">
                                                                    {
                                                                        recommendation.composer_name
                                                                    }
                                                                </div>
                                                            </div>
                                                            <Form
                                                                options={{
                                                                    preserveScroll: true,
                                                                }}
                                                                {...storeLoadoutPackage.form()}
                                                            >
                                                                {({
                                                                    processing,
                                                                }) => (
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
                                                                            className="h-7 px-2"
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
                                                                            {recommendation.is_selected
                                                                                ? 'In'
                                                                                : 'Add'}
                                                                        </Button>
                                                                    </>
                                                                )}
                                                            </Form>
                                                        </div>
                                                        <p className="mt-2 line-clamp-2 text-xs text-muted-foreground">
                                                            {
                                                                recommendation.reason
                                                            }
                                                        </p>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card className="rounded-xl">
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center justify-between gap-3 text-base">
                                    Dependency stack
                                    <PackageCheck className="size-4 text-muted-foreground" />
                                </CardTitle>
                                <CardDescription>
                                    Collapsed by default so long loadouts stay
                                    scannable.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="max-h-[46vh] space-y-2 overflow-y-auto pr-1">
                                    {visibleSelectedPackages.length > 0 ? (
                                        visibleSelectedPackages.map(
                                            (selectedPackage) => (
                                                <details
                                                    key={selectedPackage.id}
                                                    className="group rounded-lg border bg-background p-3 open:bg-muted/30"
                                                >
                                                    <summary className="flex cursor-pointer list-none items-start justify-between gap-3">
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
                                                        <Badge
                                                            variant={
                                                                selectedPackage
                                                                    .install_options
                                                                    ?.dev_dependency
                                                                    ? 'outline'
                                                                    : 'secondary'
                                                            }
                                                        >
                                                            {selectedPackage
                                                                .install_options
                                                                ?.dev_dependency
                                                                ? 'Dev'
                                                                : 'Prod'}
                                                        </Badge>
                                                    </summary>
                                                    <Form
                                                        className="mt-3 grid gap-2"
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                        {...updateLoadoutPackage.form(
                                                            selectedPackage.id,
                                                        )}
                                                    >
                                                        {({
                                                            errors,
                                                            processing,
                                                        }) => (
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
                                                                        {
                                                                            errors.notes
                                                                        }
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
                                                </details>
                                            ),
                                        )
                                    ) : (
                                        <div className="rounded-lg border border-dashed p-4 text-sm text-muted-foreground">
                                            Select curated packages from the
                                            catalog.
                                        </div>
                                    )}
                                </div>

                                {hiddenSelectedPackageCount > 0 && (
                                    <div className="rounded-md bg-muted px-3 py-2 text-xs text-muted-foreground">
                                        {hiddenSelectedPackageCount} more
                                        selected packages are summarized in the
                                        map. Use review for the full generated
                                        artifact.
                                    </div>
                                )}

                                {customPackages.length > 0 && (
                                    <div className="space-y-2 border-t pt-3">
                                        <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                            Custom packages
                                        </div>
                                        <div className="max-h-36 space-y-2 overflow-y-auto pr-1">
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

                                <div className="flex gap-2 pt-1">
                                    <Button
                                        asChild
                                        className="flex-1"
                                        variant="outline"
                                    >
                                        <Link href={builderRuntime.url()}>
                                            <ArrowLeft className="size-4" />
                                            Runtime
                                        </Link>
                                    </Button>
                                    <Button asChild className="flex-1">
                                        <Link href={builderReview.url()}>
                                            Review
                                            <ArrowRight className="size-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </aside>
                </section>
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
