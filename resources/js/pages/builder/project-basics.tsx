import { Form, Head } from '@inertiajs/react';

import ProjectBasicsController from '@/actions/App/Http/Controllers/Builder/ProjectBasicsController';
import { BuilderStepNavigation } from '@/components/builder-step-navigation';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import {
    project as builderProject,
    starter as builderStarter,
} from '@/routes/builder';

type Values = {
    project_name: string;
    app_name: string;
    description: string | null;
    php_version: string;
    laravel_version: string;
    package_manager: string;
};

type Props = {
    loadout: {
        is_locked: boolean;
    };
    values: Values;
};

const packageManagers = [
    { value: 'npm', label: 'npm' },
    { value: 'pnpm', label: 'pnpm' },
    { value: 'bun', label: 'Bun' },
    { value: 'yarn', label: 'Yarn' },
] as const;

export default function ProjectBasics({ loadout, values }: Props) {
    const isLocked = loadout.is_locked;

    return (
        <>
            <Head title="Project Basics" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24 md:p-6 md:pb-24">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 1</span>
                        <span>/</span>
                        <span>Project basics</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Give this stack a name
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            This is the identity carried into the README,
                            Composer metadata, and `.env.loadout`.
                        </p>
                    </div>
                </header>

                <section>
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Project brief</CardTitle>
                            <CardDescription>
                                {isLocked
                                    ? 'This shared loadout is locked. Clone it from its public page to make changes.'
                                    : 'Write this for the person who will pick up the loadout later, including future you.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                id="project-basics-form"
                                className="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(22rem,0.8fr)] lg:gap-10"
                                {...ProjectBasicsController.update.form()}
                            >
                                {({ errors }) => (
                                    <fieldset
                                        className="contents"
                                        disabled={isLocked}
                                    >
                                        <div className="grid content-start gap-5">
                                            <div className="grid gap-5 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="project_name">
                                                    Project name
                                                </Label>
                                                <Input
                                                    id="project_name"
                                                    name="project_name"
                                                    defaultValue={
                                                        values.project_name
                                                    }
                                                    placeholder="Agency App Baseline"
                                                />
                                                {errors.project_name && (
                                                    <p className="text-sm text-destructive">
                                                        {errors.project_name}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="app_name">
                                                    App name
                                                </Label>
                                                <Input
                                                    id="app_name"
                                                    name="app_name"
                                                    defaultValue={
                                                        values.app_name
                                                    }
                                                    placeholder="Acme Portal"
                                                />
                                                {errors.app_name && (
                                                    <p className="text-sm text-destructive">
                                                        {errors.app_name}
                                                    </p>
                                                )}
                                            </div>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="description">
                                                    Description
                                                </Label>
                                                <Textarea
                                                    id="description"
                                                    name="description"
                                                    defaultValue={
                                                        values.description ??
                                                        ''
                                                    }
                                                    placeholder="A solid Laravel baseline for client portals and internal tools."
                                                />
                                                {errors.description && (
                                                    <p className="text-sm text-destructive">
                                                        {errors.description}
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="overflow-hidden rounded-lg border bg-muted/25">
                                            <div className="border-b bg-background/60 px-4 py-3">
                                                <p className="text-sm font-medium">
                                                    Foundation
                                                </p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    Runtime and frontend defaults
                                                    for this loadout.
                                                </p>
                                            </div>
                                            <div className="divide-y">
                                            <div className="grid grid-cols-2 items-center gap-4 px-4 py-3">
                                                <Label htmlFor="php_version">
                                                    PHP version
                                                </Label>
                                                <Select
                                                    defaultValue={
                                                        values.php_version
                                                    }
                                                    disabled={isLocked}
                                                    name="php_version"
                                                >
                                                    <SelectTrigger id="php_version">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="8.4">
                                                            PHP 8.4
                                                        </SelectItem>
                                                        <SelectItem value="8.5">
                                                            PHP 8.5
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-2 items-center gap-4 px-4 py-3">
                                                <Label htmlFor="laravel_version">
                                                    Laravel version
                                                </Label>
                                                <Select
                                                    defaultValue={
                                                        values.laravel_version
                                                    }
                                                    disabled={isLocked}
                                                    name="laravel_version"
                                                >
                                                    <SelectTrigger id="laravel_version">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="13.x">
                                                            Laravel 13.x
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="grid grid-cols-2 items-center gap-4 px-4 py-3">
                                                <Label htmlFor="package_manager">
                                                    Package manager
                                                </Label>
                                                <Select
                                                    defaultValue={
                                                        values.package_manager
                                                    }
                                                    disabled={isLocked}
                                                    name="package_manager"
                                                >
                                                    <SelectTrigger id="package_manager">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {packageManagers.map(
                                                            (packageManager) => (
                                                                <SelectItem
                                                                    key={
                                                                        packageManager.value
                                                                    }
                                                                    value={
                                                                        packageManager.value
                                                                    }
                                                                >
                                                                    {
                                                                        packageManager.label
                                                                    }
                                                                </SelectItem>
                                                            ),
                                                        )}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                </section>

                <BuilderStepNavigation
                    next={
                        isLocked
                            ? { href: builderStarter.url(), label: 'Starter' }
                            : undefined
                    }
                    step={1}
                    submitFormId={isLocked ? undefined : 'project-basics-form'}
                />
            </div>
        </>
    );
}

ProjectBasics.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderProject.url(),
        },
        {
            title: 'Project',
            href: builderProject.url(),
        },
    ],
};
