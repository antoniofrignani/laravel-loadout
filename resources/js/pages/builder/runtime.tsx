import { Form, Head } from '@inertiajs/react';

import RuntimeController from '@/actions/App/Http/Controllers/Builder/RuntimeController';
import { BuilderStepNavigation } from '@/components/builder-step-navigation';
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
    is_locked: boolean;
    values: Values;
};

export default function Runtime({ is_locked: isLocked, values }: Props) {
    return (
        <>
            <Head title="Runtime" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24 md:p-6 md:pb-24">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 3</span>
                        <span>/</span>
                        <span>Database and runtime</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Define the local environment
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Choose the services you want available from the
                            first `sail up`.
                        </p>
                    </div>
                </header>

                <section>
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Local services</CardTitle>
                            <CardDescription>
                                {isLocked
                                    ? 'This shared loadout is locked. Clone it from its public page to make changes.'
                                    : 'Keep the default lean. Add services only when the application will use them.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                id="runtime-form"
                                className="grid"
                                {...RuntimeController.update.form()}
                            >
                                {({ errors }) => (
                                    <fieldset
                                        className="contents"
                                        disabled={isLocked}
                                    >
                                        <div className="grid gap-3 xl:grid-cols-2">
                                            <RuntimeSelect
                                                defaultValue={values.database}
                                                disabled={isLocked}
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
                                                disabled={isLocked}
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
                                                disabled={isLocked}
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
                                                disabled={isLocked}
                                                error={errors.mail}
                                                label="Mail"
                                                name="mail"
                                                options={[
                                                    ['mailpit', 'Mailpit'],
                                                    ['log', 'Log'],
                                                    ['smtp', 'Custom SMTP'],
                                                ]}
                                            />
                                            <RuntimeSelect
                                                defaultValue={values.search}
                                                disabled={isLocked}
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
                                                disabled={isLocked}
                                                error={errors.local_runtime}
                                                label="Local runtime"
                                                name="local_runtime"
                                                options={[
                                                    ['sail', 'Laravel Sail'],
                                                    ['herd', 'Herd / Valet notes'],
                                                ]}
                                            />
                                        </div>

                                    </fieldset>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </section>

                <BuilderStepNavigation
                    previous={{ href: builderStarter.url(), label: 'Starter' }}
                    next={
                        isLocked
                            ? { href: builderPackages.url(), label: 'Packages' }
                            : undefined
                    }
                    step={3}
                    submitFormId={isLocked ? undefined : 'runtime-form'}
                />
            </div>
        </>
    );
}

function RuntimeSelect({
    defaultValue,
    disabled,
    error,
    label,
    name,
    options,
}: {
    defaultValue: string;
    disabled: boolean;
    error?: string;
    label: string;
    name: string;
    options: [string, string][];
}) {
    return (
        <div className="grid grid-cols-2 items-center gap-4 rounded-lg border bg-muted/25 px-4 py-3">
            <Label htmlFor={name}>{label}</Label>
            <div className="grid gap-1.5">
            <Select defaultValue={defaultValue} disabled={disabled} name={name}>
                <SelectTrigger id={name}>
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
            {error && <p className="text-xs text-destructive">{error}</p>}
            </div>
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
