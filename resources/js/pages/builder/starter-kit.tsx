import { Form, Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

import StarterKitController from '@/actions/App/Http/Controllers/Builder/StarterKitController';
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
    project as builderProject,
    runtime as builderRuntime,
    starter as builderStarter,
} from '@/routes/builder';

type Values = {
    frontend_stack: string;
    authentication: string;
    teams: boolean;
    dark_mode: string;
    api_auth: string;
    broadcasting: string;
    search: string;
    queue_monitoring: string;
    observability: string;
};

type Props = {
    is_locked: boolean;
    values: Values;
};

export default function StarterKit({ is_locked: isLocked, values }: Props) {
    return (
        <>
            <Head title="Starter Kit" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24 md:p-6 md:pb-24">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 2</span>
                        <span>/</span>
                        <span>Starter kit</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Choose the application baseline
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Pick the conventions this project should inherit
                            before packages enter the picture.
                        </p>
                    </div>
                </header>

                <section>
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Application conventions</CardTitle>
                            <CardDescription>
                                {isLocked
                                    ? 'This shared loadout is locked. Clone it from its public page to make changes.'
                                    : 'Start with the shape you would normally reach for, then make the exceptions explicit.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                id="starter-kit-form"
                                className="grid"
                                {...StarterKitController.update.form()}
                            >
                                {({ errors }) => (
                                    <fieldset
                                        className="contents"
                                        disabled={isLocked}
                                    >
                                        <div className="grid gap-5 xl:grid-cols-2">
                                            <SettingGroup
                                                description="Choose the framework surface and auth starting point."
                                                title="Application core"
                                            >
                                                <StarterSelect
                                                    defaultValue={
                                                        values.frontend_stack
                                                    }
                                                    disabled={isLocked}
                                                    error={
                                                        errors.frontend_stack
                                                    }
                                                    label="Frontend stack"
                                                    name="frontend_stack"
                                                    options={[
                                                        [
                                                            'react-inertia',
                                                            'React + Inertia',
                                                        ],
                                                        [
                                                            'vue-inertia',
                                                            'Vue + Inertia',
                                                        ],
                                                        [
                                                            'livewire',
                                                            'Livewire',
                                                        ],
                                                        ['blade', 'Blade only'],
                                                        ['api', 'API only'],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={
                                                        values.authentication
                                                    }
                                                    disabled={isLocked}
                                                    error={
                                                        errors.authentication
                                                    }
                                                    label="Authentication"
                                                    name="authentication"
                                                    options={[
                                                        [
                                                            'laravel-auth',
                                                            'Laravel auth',
                                                        ],
                                                        ['none', 'None'],
                                                    ]}
                                                />
                                            </SettingGroup>
                                            <SettingGroup
                                                description="Set the defaults your product should carry from day one."
                                                title="Product defaults"
                                            >
                                                <StarterSelect
                                                    defaultValue={
                                                        values.teams ? '1' : '0'
                                                    }
                                                    disabled={isLocked}
                                                    label="Team support"
                                                    name="teams"
                                                    options={[
                                                        ['0', 'No teams'],
                                                        [
                                                            '1',
                                                            'Include team-ready notes',
                                                        ],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={
                                                        values.dark_mode
                                                    }
                                                    disabled={isLocked}
                                                    label="Dark mode"
                                                    name="dark_mode"
                                                    options={[
                                                        [
                                                            'system',
                                                            'Follow system',
                                                        ],
                                                        ['enabled', 'Enabled'],
                                                        [
                                                            'disabled',
                                                            'Disabled',
                                                        ],
                                                    ]}
                                                />
                                            </SettingGroup>
                                            <SettingGroup
                                                description="First-party building blocks added directly to generated files."
                                                title="Laravel foundation"
                                            >
                                                <StarterSelect
                                                    defaultValue={
                                                        values.api_auth
                                                    }
                                                    disabled={isLocked}
                                                    label="API authentication"
                                                    name="api_auth"
                                                    options={[
                                                        ['none', 'None'],
                                                        [
                                                            'sanctum',
                                                            'Laravel Sanctum',
                                                        ],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={
                                                        values.broadcasting
                                                    }
                                                    disabled={isLocked}
                                                    label="Broadcasting"
                                                    name="broadcasting"
                                                    options={[
                                                        ['none', 'None'],
                                                        [
                                                            'reverb',
                                                            'Laravel Reverb',
                                                        ],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={values.search}
                                                    disabled={isLocked}
                                                    error={errors.search}
                                                    label="Search"
                                                    name="search"
                                                    options={[
                                                        ['none', 'None'],
                                                        [
                                                            'scout',
                                                            'Laravel Scout',
                                                        ],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={
                                                        values.queue_monitoring
                                                    }
                                                    disabled={isLocked}
                                                    label="Queue monitoring"
                                                    name="queue_monitoring"
                                                    options={[
                                                        ['none', 'None'],
                                                        [
                                                            'horizon',
                                                            'Laravel Horizon (Redis)',
                                                        ],
                                                    ]}
                                                />
                                                <StarterSelect
                                                    defaultValue={
                                                        values.observability
                                                    }
                                                    disabled={isLocked}
                                                    label="Observability"
                                                    name="observability"
                                                    options={[
                                                        ['none', 'None'],
                                                        [
                                                            'telescope',
                                                            'Laravel Telescope',
                                                        ],
                                                        [
                                                            'pulse',
                                                            'Laravel Pulse',
                                                        ],
                                                    ]}
                                                />
                                            </SettingGroup>
                                        </div>
                                    </fieldset>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </section>

                <BuilderStepNavigation
                    previous={{ href: builderProject.url(), label: 'Project' }}
                    next={
                        isLocked
                            ? { href: builderRuntime.url(), label: 'Runtime' }
                            : undefined
                    }
                    step={2}
                    submitFormId={isLocked ? undefined : 'starter-kit-form'}
                />
            </div>
        </>
    );
}

function SettingGroup({
    children,
    description,
    title,
}: {
    children: ReactNode;
    description: string;
    title: string;
}) {
    return (
        <section className="overflow-hidden rounded-lg border bg-muted/25">
            <div className="border-b bg-background/60 px-4 py-3">
                <h2 className="text-sm font-medium">{title}</h2>
                <p className="mt-0.5 text-xs text-muted-foreground">
                    {description}
                </p>
            </div>
            <div className="divide-y">{children}</div>
        </section>
    );
}

function StarterSelect({
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
        <div className="grid grid-cols-2 items-center gap-4 px-4 py-3">
            <Label htmlFor={name}>{label}</Label>
            <div className="grid gap-1.5">
                <Select
                    defaultValue={defaultValue}
                    disabled={disabled}
                    name={name}
                >
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

StarterKit.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderProject.url(),
        },
        {
            title: 'Starter',
            href: builderStarter.url(),
        },
        {
            title: 'Runtime',
            href: builderRuntime.url(),
        },
    ],
};
