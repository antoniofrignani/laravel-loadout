import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Database, ServerCog } from 'lucide-react';

import RuntimeController from '@/actions/App/Http/Controllers/Builder/RuntimeController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    packages as builderPackages,
    runtime as builderRuntime,
    starter as builderStarter,
} from '@/routes/builder';

type Values = {
    database: string;
    cache: string;
    queue: string;
    mail: string;
    search: string;
    local_runtime: string;
};

type Props = {
    values: Values;
};

export default function Runtime({ values }: Props) {
    return (
        <>
            <Head title="Runtime" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 3</span>
                        <span>/</span>
                        <span>Database and runtime</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Pick the local services
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Runtime choices drive `.env.loadout`, `compose.yaml`,
                            and setup notes.
                        </p>
                    </div>
                </header>

                <section className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Runtime configuration</CardTitle>
                            <CardDescription>
                                Sail is the first-class local runtime for the
                                generated template.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                className="grid gap-5"
                                {...RuntimeController.update.form()}
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <RuntimeSelect
                                                defaultValue={values.database}
                                                error={errors.database}
                                                label="Database"
                                                name="database"
                                                options={[
                                                    ['mysql', 'MySQL'],
                                                    ['pgsql', 'PostgreSQL'],
                                                    ['sqlite', 'SQLite'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={values.cache}
                                                error={errors.cache}
                                                label="Cache"
                                                name="cache"
                                                options={[
                                                    ['database', 'Database'],
                                                    ['redis', 'Redis'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={values.queue}
                                                error={errors.queue}
                                                label="Queue"
                                                name="queue"
                                                options={[
                                                    ['database', 'Database'],
                                                    ['redis', 'Redis'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={values.mail}
                                                error={errors.mail}
                                                label="Mail"
                                                name="mail"
                                                options={[
                                                    ['mailpit', 'Mailpit'],
                                                    ['log', 'Log'],
                                                    ['smtp', 'SMTP placeholder'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={values.search}
                                                error={errors.search}
                                                label="Search"
                                                name="search"
                                                options={[
                                                    ['none', 'None'],
                                                    ['meilisearch', 'Meilisearch'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={
                                                    values.local_runtime
                                                }
                                                error={errors.local_runtime}
                                                label="Local runtime"
                                                name="local_runtime"
                                                options={[
                                                    ['sail', 'Laravel Sail'],
                                                    ['herd', 'Herd / Valet notes'],
                                                ]}
                                            />
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <Button asChild variant="outline">
                                                <Link href={builderStarter.url()}>
                                                    <ArrowLeft className="size-4" />
                                                    Back
                                                </Link>
                                            </Button>
                                            <Button disabled={processing}>
                                                Save and continue
                                                <ArrowRight className="size-4" />
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <Card className="h-fit rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ServerCog className="size-5" />
                                Generated impact
                            </CardTitle>
                            <CardDescription>
                                Service selections become local infrastructure
                                defaults.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <div className="rounded-md bg-muted p-3">
                                <Database className="mb-2 size-4" />
                                Redis and Meilisearch are included in
                                `compose.yaml` only when selected here or
                                required by selected packages.
                            </div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

function RuntimeSelect({
    defaultValue,
    error,
    label,
    name,
    options,
}: {
    defaultValue: string;
    error?: string;
    label: string;
    name: string;
    options: [string, string][];
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <Select defaultValue={defaultValue} name={name}>
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map(([value, optionLabel]) => (
                        <SelectItem key={value} value={value}>
                            {optionLabel}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

Runtime.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderStarter.url(),
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
